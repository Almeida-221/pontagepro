<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\SecAffectation;
use App\Models\SecNotification;
use App\Models\SecPoste;
use App\Models\SecPresenceConfirmation;
use App\Models\SecPresenceSession;
use App\Models\SecTour;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\Request;

class SecPresenceController extends Controller
{
    private const GPS_RADIUS = 100; // metres

    // ─── Agent: Mon Poste ────────────────────────────────────────────────────

    /** Return the current agent's assigned post + coordinates. */
    public function monPoste(Request $request)
    {
        $agent = $request->user();

        if ($agent->role !== 'agent_securite') {
            return response()->json(['message' => 'Cette action est réservée aux agents.'], 403);
        }

        $affectation = SecAffectation::where('agent_id', $agent->id)
            ->where('is_active', true)
            ->with('poste.zone:id,name')
            ->latest()
            ->first();

        if (!$affectation) {
            return response()->json(['message' => 'Aucun poste affecté pour le moment.'], 404);
        }

        $poste = $affectation->poste;

        return response()->json([
            'affectation_id'   => $affectation->id,
            'is_validated'     => $affectation->is_validated,
            'rest_days'        => $affectation->rest_days ?? [],
            'off_days'         => $affectation->off_days  ?? [],
            'tours'            => $affectation->tours     ?? [],
            'poste' => [
                'id'        => $poste->id,
                'name'      => $poste->name,
                'address'   => $poste->address,
                'zone'      => $poste->zone?->name,
                'latitude'  => $poste->latitude,
                'longitude' => $poste->longitude,
            ],
        ]);
    }

    // ─── Agent: Valider Arrivée ───────────────────────────────────────────────

    /**
     * First-time arrival: agent sends GPS → confirms presence on post.
     * Also updates/confirms the post's GPS coordinates.
     */
    public function validerArrivee(Request $request)
    {
        $agent = $request->user();

        if ($agent->role !== 'agent_securite') {
            return response()->json(['message' => 'Cette action est réservée aux agents.'], 403);
        }

        $validated = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $affectation = SecAffectation::where('agent_id', $agent->id)
            ->where('is_active', true)
            ->with('poste')
            ->latest()
            ->first();

        if (!$affectation) {
            return response()->json(['message' => 'Aucun poste affecté.'], 404);
        }

        if ($affectation->is_validated) {
            return response()->json(['message' => 'Arrivée déjà validée pour ce poste.'], 422);
        }

        $poste = $affectation->poste;

        // Record agent GPS as confirmed post coordinates if not yet GPS-confirmed
        if (!$poste->gps_confirmed) {
            $poste->update([
                'latitude'      => $validated['latitude'],
                'longitude'     => $validated['longitude'],
                'gps_confirmed' => true,
            ]);
        }

        // Validate the affectation
        $affectation->update([
            'validated_at'         => now(),
            'validation_latitude'  => $validated['latitude'],
            'validation_longitude' => $validated['longitude'],
        ]);

        // Notify gérant / admin
        $this->notifyManagers(
            $agent->company_id,
            $agent->zone_id,
            'agent_confirmed',
            'Arrivée confirmée',
            "{$agent->name} a validé son arrivée au poste : {$poste->name}.",
            ['agent_id' => $agent->id, 'poste_id' => $poste->id]
        );

        return response()->json([
            'message' => 'Arrivée validée avec succès.',
            'poste'   => ['id' => $poste->id, 'name' => $poste->name, 'address' => $poste->address],
        ]);
    }

    // ─── Gérant/Admin: Lancer Session de Présence ─────────────────────────────

    /**
     * Launch a presence check for a zone (or all agents if admin without zone_id).
     * Creates one SecPresenceConfirmation per active agent with status=pending.
     */
    public function lancerSession(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'agent_securite') {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $validated = $request->validate([
            'zone_id' => 'nullable|exists:sec_zones,id',
        ]);

        // Gérant can only launch for their own zone
        $zoneId = $user->role === 'gerant_securite'
            ? $user->zone_id
            : ($validated['zone_id'] ?? null);

        // Find all active agents in scope
        $agentsQuery = User::where('company_id', $user->company_id)
            ->where('role', 'agent_securite')
            ->where('is_active', true)
            ->whereHas('secAffectationActive'); // has active affectation

        if ($zoneId) {
            $agentsQuery->where('zone_id', $zoneId);
        }

        $agents = $agentsQuery->get();

        if ($agents->isEmpty()) {
            return response()->json(['message' => 'Aucun agent actif affecté dans cette zone.'], 422);
        }

        // Create session (deadline = now + 30 min)
        $session = SecPresenceSession::create([
            'company_id'  => $user->company_id,
            'zone_id'     => $zoneId,
            'launched_by' => $user->id,
            'launched_at' => now(),
            'deadline_at' => now()->addMinutes(30),
            'status'      => 'pending',
        ]);

        // Create one confirmation record per agent
        foreach ($agents as $agent) {
            $affectation = SecAffectation::where('agent_id', $agent->id)
                ->where('is_active', true)
                ->latest()
                ->first();

            if (!$affectation) continue;

            SecPresenceConfirmation::create([
                'session_id' => $session->id,
                'agent_id'   => $agent->id,
                'poste_id'   => $affectation->poste_id,
                'status'     => 'pending',
            ]);

            // In-app notification to agent
            SecNotification::notifier(
                $agent->id,
                'presence_check',
                'Confirmation de présence',
                'Confirmez votre présence sur le poste dans les 30 minutes.',
                ['session_id' => $session->id]
            );
        }

        // ── Push FCM notifications ───────────────────────────────────────────
        $fcmTokens = $agents->pluck('fcm_token')->filter()->values()->toArray();
        FcmService::sendToTokens($fcmTokens,
            '🔔 Confirmation de présence — Action requise',
            'Confirmez votre présence sur le poste dans les 30 minutes.',
            [
                'type'       => 'presence_check',
                'session_id' => (string) $session->id,
            ]
        );

        return response()->json([
            'message'        => 'Session de présence lancée.',
            'session_id'     => $session->id,
            'agents_count'   => $agents->count(),
            'deadline_at'    => $session->deadline_at,
        ], 201);
    }

    /** List presence sessions for the company/zone. */
    public function sessions(Request $request)
    {
        $user  = $request->user();
        $query = SecPresenceSession::where('company_id', $user->company_id)
            ->with(['zone:id,name', 'launchedBy:id,name'])
            ->withCount([
                'confirmations',
                'confirmations as confirmed_count' => fn($q) => $q->where('status', 'confirmed')->where('is_on_post', true),
                'confirmations as absent_count'    => fn($q) => $q->where('status', 'absent'),
            ])
            ->orderByDesc('launched_at');

        if ($user->role === 'gerant_securite') {
            $query->where('zone_id', $user->zone_id);
        }

        return response()->json($query->paginate(20));
    }

    /** Detail of one session with all agent statuses. */
    public function sessionDetail(Request $request, SecPresenceSession $session)
    {
        if ($session->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $session->load([
            'zone:id,name',
            'launchedBy:id,name',
            'confirmations.agent:id,name,phone',
            'confirmations.poste:id,name,address',
        ]);

        return response()->json([
            'session' => [
                'id'          => $session->id,
                'zone'        => $session->zone?->name,
                'launched_by' => $session->launchedBy?->name,
                'launched_at' => $session->launched_at,
                'deadline_at' => $session->deadline_at,
                'status'      => $session->status,
                'is_expired'  => $session->is_expired,
            ],
            'confirmations' => $session->confirmations->map(fn($c) => [
                'agent'          => $c->agent?->name,
                'poste'          => $c->poste?->name,
                'status'         => $c->status,
                'is_on_post'     => $c->is_on_post,
                'distance_meters'=> $c->distance_meters,
                'confirmed_at'   => $c->confirmed_at,
            ]),
        ]);
    }

    // ─── Agent: Confirmer Présence ────────────────────────────────────────────

    /**
     * Agent confirms presence with their GPS coordinates.
     * System compares with post GPS and determines if on-post.
     */
    public function confirmerPresence(Request $request)
    {
        $agent = $request->user();

        if ($agent->role !== 'agent_securite') {
            return response()->json(['message' => 'Cette action est réservée aux agents.'], 403);
        }

        $validated = $request->validate([
            'session_id' => 'required|exists:sec_presence_sessions,id',
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
        ]);

        $confirmation = SecPresenceConfirmation::where('session_id', $validated['session_id'])
            ->where('agent_id', $agent->id)
            ->where('status', 'pending')
            ->with('session', 'poste')
            ->first();

        if (!$confirmation) {
            return response()->json(['message' => 'Aucune confirmation en attente pour cette session.'], 404);
        }

        if ($confirmation->session->is_expired) {
            $confirmation->update(['status' => 'absent']);
            return response()->json(['message' => 'Le délai de confirmation est dépassé.'], 422);
        }

        $poste    = $confirmation->poste;
        $distance = null;
        $isOnPost = false;

        if ($poste && $poste->latitude && $poste->longitude) {
            $distance = $this->haversineDistance(
                $validated['latitude'], $validated['longitude'],
                $poste->latitude, $poste->longitude
            );
            $isOnPost = $distance <= ($poste->radius_meters ?? self::GPS_RADIUS);
        }

        $confirmation->update([
            'status'          => 'confirmed',
            'confirmed_at'    => now(),
            'latitude'        => $validated['latitude'],
            'longitude'       => $validated['longitude'],
            'is_on_post'      => $isOnPost,
            'distance_meters' => $distance,
        ]);

        // Notify gérant/admin of the result
        $statusMsg = $isOnPost
            ? "Présence confirmée — {$agent->name} est sur le poste : {$poste?->name}."
            : "Attention — {$agent->name} n'est PAS sur le poste : {$poste?->name}. Distance : {$distance}m.";

        $this->notifyManagers(
            $agent->company_id,
            $agent->zone_id,
            $isOnPost ? 'agent_confirmed' : 'agent_absent',
            $isOnPost ? 'Agent présent' : 'Agent hors poste',
            $statusMsg,
            ['session_id' => $confirmation->session_id, 'agent_id' => $agent->id]
        );

        // Check if all confirmations in session are done
        $this->checkSessionCompletion($confirmation->session);

        return response()->json([
            'message'         => $isOnPost ? 'Présence confirmée. Vous êtes sur votre poste.' : 'Position enregistrée. Vous n\'êtes pas sur votre poste.',
            'is_on_post'      => $isOnPost,
            'distance_meters' => $distance,
        ]);
    }

    /** Mark expired pending confirmations as absent (called by scheduler or manually). */
    public function expireAbsents(Request $request)
    {
        $updated = SecPresenceConfirmation::whereHas('session', fn($q) =>
            $q->where('status', 'pending')->where('deadline_at', '<', now())
        )->where('status', 'pending')->get();

        foreach ($updated as $confirmation) {
            $confirmation->update(['status' => 'absent']);

            $agent = $confirmation->agent;
            if ($agent) {
                $this->notifyManagers(
                    $agent->company_id,
                    $agent->zone_id,
                    'agent_absent',
                    'Agent absent',
                    "{$agent->name} n'a pas confirmé sa présence dans le délai imparti.",
                    ['session_id' => $confirmation->session_id, 'agent_id' => $agent->id]
                );
            }
        }

        // Mark sessions as expired
        SecPresenceSession::where('status', 'pending')
            ->where('deadline_at', '<', now())
            ->update(['status' => 'expired']);

        return response()->json(['message' => "{$updated->count()} agent(s) marqué(s) absent(s)."]);
    }

    // ─── Agent: Mon Planning ─────────────────────────────────────────────────

    public function monPlanning(Request $request)
    {
        $agent = $request->user();
        $affectations = \App\Models\SecAffectation::where('agent_id', $agent->id)
            ->with('poste:id,name,address')
            ->orderByDesc('started_at')
            ->get();

        return response()->json([
            'affectations' => $affectations->map(fn($aff) => [
                'id'            => $aff->id,
                'poste_name'    => $aff->poste?->name,
                'poste_address' => $aff->poste?->address,
                'started_at'    => $aff->started_at?->toDateString(),
                'ended_at'      => $aff->ended_at?->toDateString(),
                'is_active'     => $aff->is_active,
                'rest_days'     => $aff->rest_days ?? [],
                'off_days'      => $aff->off_days  ?? [],
                'tours'         => $aff->tours     ?? [],
            ]),
        ]);
    }

    // ─── Activités récentes ───────────────────────────────────────────────────

    public function activitesRecentes(Request $request)
    {
        $user  = $request->user();
        $limit = (int) ($request->query('limit', 50));
        $activities = [];

        if ($user->role === 'agent_securite') {
            // Pointage responses
            $responses = \App\Models\SecPointageResponse::where('agent_id', $user->id)
                ->with('pointage:id,tour,type,date')
                ->whereNotNull('responded_at')
                ->orderByDesc('responded_at')
                ->limit(20)
                ->get();
            foreach ($responses as $r) {
                $activities[] = [
                    'type'   => $r->status === 'present' ? 'presence' : 'absence',
                    'label'  => $r->status === 'present' ? 'Présence confirmée' : 'Absence enregistrée',
                    'detail' => 'Tour ' . ucfirst($r->pointage?->tour ?? 'local') . ' — ' . ($r->pointage?->date?->toDateString() ?? ''),
                    'date'   => $r->responded_at?->toIso8601String(),
                ];
            }
            // Planning / affectations
            $affs = \App\Models\SecAffectation::where('agent_id', $user->id)
                ->with('poste:id,name')->orderByDesc('started_at')->limit(5)->get();
            foreach ($affs as $aff) {
                $activities[] = [
                    'type'   => 'planning',
                    'label'  => 'Planning chargé',
                    'detail' => 'Affecté au poste : ' . ($aff->poste?->name ?? 'Inconnu'),
                    'date'   => $aff->started_at?->toIso8601String(),
                ];
            }
            // Remplacements (agent sortant ou entrant)
            $remplacements = \App\Models\SecRemplacement::where(fn($q) =>
                    $q->where('agent_sortant_id', $user->id)
                      ->orWhere('agent_entrant_id', $user->id)
                )
                ->where('company_id', $user->company_id)
                ->with(['agentSortant:id,name', 'agentEntrant:id,name', 'poste:id,name'])
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
            foreach ($remplacements as $r) {
                $estSortant = $r->agent_sortant_id === $user->id;
                $autreAgent = $estSortant ? $r->agentEntrant?->name : $r->agentSortant?->name;
                $heure      = \Carbon\Carbon::parse($r->heure_entree)->format('H\hi');
                $activities[] = [
                    'type'   => 'remplacement',
                    'label'  => $estSortant
                        ? "{$autreAgent} vous a remplacé"
                        : "Vous avez remplacé {$autreAgent}",
                    'detail' => ($r->poste?->name ?? '') . ' — ' . ($estSortant ? 'Sortie' : 'Entrée') . " à $heure",
                    'date'   => $r->created_at?->toIso8601String(),
                ];
            }
        } else {
            // Gérant / admin : sessions lancées
            $sessions = \App\Models\SecPresenceSession::where('initiated_by', $user->id)
                ->with('zone:id,name')->orderByDesc('created_at')->limit(10)->get();
            foreach ($sessions as $s) {
                $activities[] = [
                    'type'   => 'session',
                    'label'  => 'Session de présence lancée',
                    'detail' => $s->zone?->name ?? 'Zone inconnue',
                    'date'   => $s->created_at?->toIso8601String(),
                ];
            }
            // Scans locaux
            $scanResponses = \App\Models\SecPointageResponse::whereHas('pointage', fn($q) =>
                    $q->where('initiated_by', $user->id)->where('type', 'local')
                )
                ->with(['agent:id,name', 'pointage:id,type'])
                ->where('status', 'present')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
            foreach ($scanResponses as $r) {
                $activities[] = [
                    'type'   => 'scan',
                    'label'  => ($r->agent?->name ?? 'Agent') . ' — Badge scanné',
                    'detail' => 'Présence enregistrée (scan local)',
                    'date'   => $r->created_at?->toIso8601String(),
                ];
            }
            // Rapports générés
            $rapports = \App\Models\SecRapport::where('created_by', $user->id)
                ->with('zone:id,name')->orderByDesc('created_at')->limit(5)->get();
            foreach ($rapports as $r) {
                $activities[] = [
                    'type'   => 'rapport',
                    'label'  => 'Rapport généré',
                    'detail' => ($r->zone?->name ?? '') . ' — ' . $r->date,
                    'date'   => $r->created_at?->toIso8601String(),
                ];
            }
            // Remplacements confirmés par ce gérant
            $remplacements = \App\Models\SecRemplacement::where('agent_entrant_id', $user->id)
                ->where('company_id', $user->company_id)
                ->with(['agentSortant:id,name', 'poste:id,name'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
            foreach ($remplacements as $r) {
                $heure = \Carbon\Carbon::parse($r->heure_entree)->format('H\hi');
                $activities[] = [
                    'type'   => 'remplacement',
                    'label'  => "Vous avez remplacé " . ($r->agentSortant?->name ?? ''),
                    'detail' => ($r->poste?->name ?? '') . " — Entrée à $heure",
                    'date'   => $r->created_at?->toIso8601String(),
                ];
            }
        }

        usort($activities, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

        return response()->json([
            'activities' => array_slice($activities, 0, $limit),
            'total'      => count($activities),
        ]);
    }

    // ─── Stats pointages (compteur mensuel + historique annuel) ────────────

    public function statsPointages(Request $request)
    {
        $user  = $request->user();
        $year  = now()->year;
        $month = now()->month;

        $monthNames = [
            1 => 'Janvier', 2 => 'Février',  3 => 'Mars',     4 => 'Avril',
            5 => 'Mai',     6 => 'Juin',      7 => 'Juillet',  8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        if ($user->role === 'agent_securite') {
            // Agent : jours distincts où il a confirmé (présent) un pointage
            $countForMonth = fn(int $m) => \DB::table('sec_pointage_responses')
                ->join('sec_pointages', 'sec_pointages.id', '=', 'sec_pointage_responses.pointage_id')
                ->where('sec_pointage_responses.agent_id', $user->id)
                ->where('sec_pointage_responses.status', 'present')
                ->whereYear('sec_pointages.date', $year)
                ->whereMonth('sec_pointages.date', $m)
                ->distinct('sec_pointages.date')
                ->count('sec_pointages.date');
        } else {
            // Gérant / admin : jours distincts où un pointage a été lancé dans leur zone
            $countForMonth = fn(int $m) => \DB::table('sec_pointages')
                ->where('company_id', $user->company_id)
                ->when($user->role === 'gerant_securite' && $user->zone_id,
                    fn($q) => $q->where('zone_id', $user->zone_id))
                ->whereYear('date', $year)
                ->whereMonth('date', $m)
                ->distinct('date')
                ->count('date');
        }

        $joursMois  = $countForMonth($month);
        $historique = collect(range(1, 12))->map(fn($m) => [
            'mois'  => $m,
            'label' => $monthNames[$m],
            'jours' => $countForMonth($m),
        ])->values();

        return response()->json([
            'mois_courant' => [
                'mois'  => $month,
                'label' => $monthNames[$month],
                'jours' => $joursMois,
            ],
            'historique' => $historique,
            'annee'      => $year,
        ]);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    /** Haversine formula — returns distance in metres between two GPS points. */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000; // metres
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }

    /** Check if all confirmations in a session are resolved → mark session completed. */
    private function checkSessionCompletion(SecPresenceSession $session): void
    {
        $pendingCount = $session->confirmations()->where('status', 'pending')->count();

        if ($pendingCount === 0) {
            $session->update(['status' => 'completed']);
        }
    }

    /** Send notification to all gérants of a zone + the company admin. */
    private function notifyManagers(int $companyId, ?int $zoneId, string $type, string $title, string $message, array $data = []): void
    {
        $managers = User::where('company_id', $companyId)
            ->where(function ($q) use ($zoneId) {
                $q->where('role', 'company_admin')
                  ->orWhere(fn($q2) => $q2->where('role', 'gerant_securite')->where('zone_id', $zoneId));
            })
            ->where('is_active', true)
            ->pluck('id');

        foreach ($managers as $managerId) {
            SecNotification::notifier($managerId, $type, $title, $message, $data);
        }
    }

    // ── Tours de l'entreprise ─────────────────────────────────────────────────
    public function getTours(Request $request)
    {
        $company = $request->user()->company;
        $tours   = SecTour::where('company_id', $company->id)
            ->orderBy('ordre')
            ->get(['id', 'nom', 'emoji', 'heure_debut', 'heure_fin', 'ordre']);

        // Fallback si aucun tour configuré
        if ($tours->isEmpty()) {
            return response()->json([
                'tours' => [
                    ['id' => 0, 'nom' => 'Matin', 'emoji' => '🌅', 'heure_debut' => null, 'heure_fin' => null, 'ordre' => 1],
                    ['id' => 0, 'nom' => 'Soir',  'emoji' => '🌆', 'heure_debut' => null, 'heure_fin' => null, 'ordre' => 2],
                    ['id' => 0, 'nom' => 'Nuit',  'emoji' => '🌙', 'heure_debut' => null, 'heure_fin' => null, 'ordre' => 3],
                ],
            ]);
        }

        return response()->json(['tours' => $tours]);
    }
}
