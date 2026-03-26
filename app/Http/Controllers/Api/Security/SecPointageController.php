<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\SecNotification;
use App\Models\SecPointage;
use App\Models\SecPointageResponse;
use App\Models\SecTour;
use App\Models\User;
use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SecPointageController extends Controller
{
    // ── Lancer un pointage à distance (multi-postes) ─────────────────────────
    public function launch(Request $request)
    {
        $user    = $request->user();
        $company = $user->company;

        $tourNoms = SecTour::where('company_id', $company->id)->pluck('nom')->toArray();
        if (empty($tourNoms)) $tourNoms = ['matin', 'soir', 'nuit'];

        $validated = $request->validate([
            'zone_id'    => 'nullable|exists:sec_zones,id',
            'poste_ids'  => 'nullable|array',
            'poste_ids.*'=> 'exists:sec_postes,id',
            'tour'       => ['required', Rule::in($tourNoms)],
        ]);

        $today   = Carbon::today();
        $weekday = $today->dayOfWeekIso; // 1=Lun … 7=Dim

        $posteIds = !empty($validated['poste_ids']) ? array_map('intval', $validated['poste_ids']) : [];

        // ── Construire la query des agents ───────────────────────────────────
        $query = User::where('company_id', $company->id)
            ->where('role', 'agent_securite')
            ->where('is_active', true);

        if (!empty($validated['zone_id'])) {
            $query->where('zone_id', $validated['zone_id']);
        }

        $agents = $query->with(['planning', 'affectation'])->get();

        // ── Filtrer selon congé / repos / tour ───────────────────────────────
        $tourDemande = mb_strtolower($validated['tour']);
        $concerned = $agents->filter(function (User $agent) use ($tourDemande, $today, $weekday) {
            $affectation = $agent->affectation;
            $planning    = $affectation ?? $agent->planning;
            if (!$planning) return true;

            $restDays = is_array($planning->rest_days) ? $planning->rest_days : [];
            if (in_array($weekday, array_map('intval', $restDays))) return false;

            $offDays = is_array($planning->off_days) ? $planning->off_days : [];
            if (in_array((int) $today->day, array_map('intval', $offDays))) return false;

            $tours = is_array($planning->tours) ? $planning->tours : [];
            if (!empty($tours)) {
                $hasTour = collect($tours)->contains(
                    fn($t) => mb_strtolower($t['type'] ?? '') === $tourDemande
                );
                if (!$hasTour) return false;
            }

            return true;
        });

        // ── Filtrer par postes sélectionnés ──────────────────────────────────
        if (!empty($posteIds)) {
            $concerned = $concerned->filter(function (User $agent) use ($posteIds) {
                return $agent->affectation && in_array($agent->affectation->poste_id, $posteIds);
            });
        }

        if ($concerned->isEmpty()) {
            return response()->json([
                'message' => 'Aucun agent concerné par ce pointage.',
            ], 422);
        }

        // ── Exclure les agents déjà pointés présents pour ce tour aujourd'hui ─
        $dejaPointesIds = SecPointageResponse::where('status', 'present')
            ->whereHas('pointage', fn($q) => $q
                ->where('company_id', $company->id)
                ->whereDate('date', $today)
                ->where('tour', $validated['tour'])
            )
            ->pluck('agent_id')
            ->toArray();

        $concerned = $concerned->reject(fn($agent) => in_array($agent->id, $dejaPointesIds));

        if ($concerned->isEmpty()) {
            return response()->json([
                'message' => 'Tous les agents ont déjà été pointés pour le tour « ' . $validated['tour'] . ' » aujourd\'hui.',
            ], 422);
        }

        // ── Vérifier unicité zone/tour/jour ──────────────────────────────────
        $duplicate = SecPointage::where('company_id', $company->id)
            ->where('type', 'remote')
            ->whereDate('date', $today)
            ->where('tour', $validated['tour'])
            ->when(!empty($validated['zone_id']), fn($q) => $q->where('zone_id', $validated['zone_id']))
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'Un pointage a déjà été effectué pour cette zone et ce tour aujourd\'hui.',
            ], 422);
        }

        // ── Créer la session de pointage ─────────────────────────────────────
        $pointage = SecPointage::create([
            'company_id'   => $company->id,
            'initiated_by' => $user->id,
            'zone_id'      => $validated['zone_id'] ?? null,
            'poste_ids'    => !empty($posteIds) ? $posteIds : null,
            'tour'         => $validated['tour'],
            'type'         => 'remote',
            'status'       => 'pending',
            'date'         => $today,
            'expires_at'   => now()->addMinutes(15),
        ]);

        // ── Créer une réponse + notification par agent ───────────────────────
        $tourLabel     = ucfirst($validated['tour']);
        $agentsToNotify = collect();

        foreach ($concerned as $agent) {
            $positionOk = $agent->affectation && $agent->affectation->isValidated;

            SecPointageResponse::create([
                'pointage_id'  => $pointage->id,
                'agent_id'     => $agent->id,
                'zone_id'      => $agent->zone_id,
                'poste_id'     => $agent->affectation?->poste_id,
                'status'       => $positionOk ? 'pending' : 'absent',
                'responded_at' => $positionOk ? null : now(),
            ]);

            if ($positionOk) {
                SecNotification::create([
                    'user_id' => $agent->id,
                    'type'    => 'pointage',
                    'title'   => "🔔 Pointage {$tourLabel} — Action requise",
                    'message' => 'Confirmez votre présence dans les 15 minutes.',
                    'data'    => ['pointage_id' => $pointage->id, 'tour' => $validated['tour']],
                ]);
                $agentsToNotify->push($agent);
            } else {
                SecNotification::create([
                    'user_id' => $agent->id,
                    'type'    => 'affectation',
                    'title'   => '⚠️ Position requise',
                    'message' => 'Veuillez envoyer votre position pour participer aux prochains pointages.',
                    'data'    => [],
                ]);
            }
        }

        // ── Push FCM — seulement les agents avec position validée ────────────
        $fcmTokens = $agentsToNotify->pluck('fcm_token')->filter()->values()->toArray();
        FcmService::sendToTokens($fcmTokens,
            "🔔 Pointage {$tourLabel} — Action requise",
            "Confirmez votre présence dans les 15 minutes.",
            [
                'type'        => 'pointage',
                'pointage_id' => (string) $pointage->id,
                'tour'        => $validated['tour'],
            ]
        );

        return response()->json([
            'pointage'     => $this->formatPointage($pointage),
            'agents_count' => $concerned->count(),
        ], 201);
    }

    // ── Démarrer une session de pointage local ────────────────────────────────
    public function startLocal(Request $request)
    {
        $user    = $request->user();
        $company = $user->company;

        $tourNoms = SecTour::where('company_id', $company->id)->pluck('nom')->toArray();
        if (empty($tourNoms)) $tourNoms = ['matin', 'soir', 'nuit'];

        $validated = $request->validate([
            'zone_id'  => 'nullable|exists:sec_zones,id',
            'poste_id' => 'nullable|exists:sec_postes,id',
            'tour'     => ['nullable', Rule::in($tourNoms)],
        ]);

        // Bloquer si une session locale active existe déjà pour les mêmes paramètres
        $dupQuery = SecPointage::where('company_id', $company->id)
            ->where('type', 'local')
            ->where('status', 'pending')
            ->whereDate('date', today());
        if (!empty($validated['zone_id']))  $dupQuery->where('zone_id',  $validated['zone_id']);
        if (!empty($validated['poste_id'])) $dupQuery->where('poste_id', $validated['poste_id']);
        if (!empty($validated['tour']))     $dupQuery->where('tour',     $validated['tour']);

        if ($dupQuery->exists()) {
            return response()->json([
                'message' => 'Une session de pointage locale est déjà active pour ces paramètres. Clôturez-la avant d\'en relancer une.',
            ], 422);
        }

        $pointage = SecPointage::create([
            'company_id'   => $company->id,
            'initiated_by' => $user->id,
            'zone_id'      => $validated['zone_id']  ?? null,
            'poste_id'     => $validated['poste_id'] ?? null,
            'tour'         => $validated['tour']     ?? null,
            'type'         => 'local',
            'status'       => 'pending',
            'date'         => today(),
            'expires_at'   => now()->addHours(8),
        ]);

        return response()->json([
            'pointage' => $this->formatPointage($pointage),
        ], 201);
    }

    // ── Scanner un agent (QR code local) ─────────────────────────────────────
    public function scanAgent(Request $request, SecPointage $pointage)
    {
        $admin = $request->user();

        if ($pointage->company_id !== $admin->company_id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($pointage->type !== 'local') {
            return response()->json(['message' => 'Ce pointage n\'est pas de type local.'], 422);
        }

        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
        ]);

        $agent = User::find($validated['agent_id']);

        if ($agent->company_id !== $admin->company_id) {
            return response()->json(['message' => 'Agent non trouvé dans cette entreprise.'], 404);
        }

        $agent->load(['zone', 'affectation.poste']);
        $zoneName  = $agent->zone?->name;
        $posteName = $agent->affectation?->poste?->name;

        // Vérifier si déjà scanné dans cette session
        $existing = SecPointageResponse::where('pointage_id', $pointage->id)
            ->where('agent_id', $agent->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message'        => 'Agent déjà enregistré dans cette session.',
                'already_scanned' => true,
                'agent'          => ['id' => $agent->id, 'name' => $agent->name, 'zone' => $zoneName, 'poste' => $posteName],
            ], 409);
        }

        // Vérifier si déjà pointé pour le même tour aujourd'hui (toute session confondue)
        if ($pointage->tour) {
            $dejaPointeTour = SecPointageResponse::where('agent_id', $agent->id)
                ->where('status', 'present')
                ->whereHas('pointage', fn($q) => $q
                    ->where('company_id', $admin->company_id)
                    ->whereDate('date', today())
                    ->where('tour', $pointage->tour)
                )
                ->exists();

            if ($dejaPointeTour) {
                return response()->json([
                    'message'         => "Cet agent a déjà été pointé pour le tour « {$pointage->tour} » aujourd'hui.",
                    'already_scanned' => true,
                    'tour_duplicate'  => true,
                    'agent'           => ['id' => $agent->id, 'name' => $agent->name, 'zone' => $zoneName, 'poste' => $posteName],
                ], 409);
            }
        }

        SecPointageResponse::create([
            'pointage_id'  => $pointage->id,
            'agent_id'     => $agent->id,
            'zone_id'      => $agent->zone_id,
            'poste_id'     => $agent->affectation?->poste_id,
            'status'       => 'present',
            'responded_at' => now(),
        ]);

        // Notifier l'agent que sa présence a été enregistrée par scan
        $tourLabel = $pointage->tour ? ucfirst($pointage->tour) : '';
        $msg = $tourLabel
            ? "Votre présence a été enregistrée par scan. Tour : $tourLabel."
            : "Votre présence a été enregistrée par scan.";
        SecNotification::notifier(
            $agent->id,
            'scan',
            '✅ Présence enregistrée',
            $msg,
            ['pointage_id' => $pointage->id, 'tour' => $pointage->tour ?? '']
        );
        if ($agent->fcm_token) {
            FcmService::sendToTokens([$agent->fcm_token], '✅ Présence enregistrée', $msg, [
                'type'        => 'scan',
                'pointage_id' => (string) $pointage->id,
            ]);
        }

        return response()->json([
            'message'         => 'Présence enregistrée.',
            'already_scanned' => false,
            'agent'           => ['id' => $agent->id, 'name' => $agent->name, 'zone' => $zoneName, 'poste' => $posteName],
        ]);
    }

    // ── Clôturer une session locale ───────────────────────────────────────────
    public function closeLocal(Request $request, SecPointage $pointage)
    {
        $admin = $request->user();

        if ($pointage->company_id !== $admin->company_id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $pointage->update(['status' => 'completed']);

        $count = $pointage->responses()->where('status', 'present')->count();

        return response()->json([
            'message' => "Session clôturée. $count agent(s) enregistré(s).",
            'pointage' => $this->formatPointage($pointage->refresh()),
        ]);
    }

    // ── Statut live du pointage (polling toutes 5s) ───────────────────────────
    public function status(Request $request, SecPointage $pointage)
    {
        $user = $request->user();
        if ($pointage->company_id !== $user->company_id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        // Auto-marquer absent si expiré (distance uniquement)
        if ($pointage->type === 'remote' && $pointage->status === 'pending' && $pointage->isExpired()) {
            $pointage->responses()->where('status', 'pending')->update(['status' => 'absent']);
            $pointage->update(['status' => 'completed']);
            $pointage->refresh();
        }

        $responses = $pointage->responses()->with(['agent', 'zone', 'poste'])->get();

        return response()->json([
            'pointage'  => $this->formatPointage($pointage),
            'responses' => $responses->map(fn($r) => $this->formatResponse($r)),
            'summary'   => [
                'total'   => $responses->count(),
                'present' => $responses->where('status', 'present')->count(),
                'absent'  => $responses->where('status', 'absent')->count(),
                'pending' => $responses->where('status', 'pending')->count(),
            ],
        ]);
    }

    // ── L'agent confirme sa présence (distance) ───────────────────────────────
    public function respond(Request $request, SecPointage $pointage)
    {
        $agent = $request->user();

        $response = SecPointageResponse::where('pointage_id', $pointage->id)
            ->where('agent_id', $agent->id)
            ->first();

        if (!$response) {
            return response()->json(['message' => "Vous n'êtes pas concerné par ce pointage."], 404);
        }

        if ($pointage->isExpired()) {
            return response()->json(['message' => 'Ce pointage a expiré.'], 422);
        }

        // Vérifier que l'agent a bien envoyé sa position
        $agent->load('affectation');
        if (!$agent->affectation || !$agent->affectation->isValidated) {
            return response()->json([
                'message' => 'Veuillez envoyer votre position avant de confirmer votre présence.',
            ], 422);
        }

        // Valider la position GPS actuelle
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $refLat = $agent->affectation->validation_latitude;
        $refLng = $agent->affectation->validation_longitude;
        $curLat = (float) $request->latitude;
        $curLng = (float) $request->longitude;

        $distance = $this->haversineDistance($refLat, $refLng, $curLat, $curLng);

        if ($distance > 100) {
            return response()->json([
                'message' => sprintf(
                    'Position trop éloignée de votre poste (%.0f m). Vous devez être à moins de 100 m.',
                    $distance
                ),
            ], 422);
        }

        $response->update([
            'status'       => 'present',
            'responded_at' => now(),
        ]);

        // Notifier l'agent que sa confirmation a bien été enregistrée
        $tourLabel = ucfirst($pointage->tour ?? '');
        $confirmMsg = $tourLabel
            ? "Votre présence pour le tour $tourLabel a été confirmée."
            : "Votre présence a été confirmée.";
        SecNotification::notifier(
            $agent->id,
            'pointage_confirme',
            '✅ Présence confirmée',
            $confirmMsg,
            ['pointage_id' => $pointage->id, 'tour' => $pointage->tour ?? '']
        );
        if ($agent->fcm_token) {
            FcmService::sendToTokens([$agent->fcm_token], '✅ Présence confirmée', $confirmMsg, [
                'type'        => 'pointage_confirme',
                'pointage_id' => (string) $pointage->id,
            ]);
        }

        return response()->json(['message' => 'Présence confirmée.']);
    }

    // ── Supprimer une session locale vide ─────────────────────────────────────
    public function destroy(Request $request, SecPointage $pointage)
    {
        $admin = $request->user();

        if ($pointage->company_id !== $admin->company_id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($pointage->type !== 'local') {
            return response()->json(['message' => 'Seules les sessions locales peuvent être supprimées.'], 422);
        }

        if ($pointage->responses()->where('status', 'present')->exists()) {
            return response()->json(['message' => 'Impossible de supprimer une session avec des présences enregistrées.'], 422);
        }

        $pointage->responses()->delete();
        $pointage->delete();

        return response()->json(['message' => 'Session supprimée.']);
    }

    // ── Rapport du jour ───────────────────────────────────────────────────────
    public function todayReport(Request $request)
    {
        $user = $request->user();

        $pointages = SecPointage::where('company_id', $user->company_id)
            ->whereDate('date', today())
            ->with(['zone', 'poste', 'initiator', 'responses.agent', 'responses.zone', 'responses.poste'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'date'      => today()->toDateString(),
            'pointages' => $pointages->map(function ($p) {
                $r = $p->responses;
                return [
                    'pointage' => $this->formatPointage($p),
                    'summary'  => [
                        'total'   => $r->count(),
                        'present' => $r->where('status', 'present')->count(),
                        'absent'  => $r->where('status', 'absent')->count(),
                        'pending' => $r->where('status', 'pending')->count(),
                    ],
                    'responses' => $r->map(fn($res) => $this->formatResponse($res)),
                ];
            }),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Distance en mètres entre deux coordonnées GPS (formule de Haversine). */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R    = 6_371_000; // rayon terrestre en mètres
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function formatPointage(SecPointage $p): array
    {
        return [
            'id'           => $p->id,
            'tour'         => $p->tour,
            'type'         => $p->type,
            'status'       => $p->status,
            'date'         => $p->date->toDateString(),
            'expires_at'   => $p->expires_at->toIso8601String(),
            'zone'         => $p->zone  ? ['id' => $p->zone->id,  'name' => $p->zone->name]  : null,
            'poste_ids'    => $p->poste_ids ?? [],
            'initiated_by' => $p->initiator?->name,
            'created_at'   => $p->created_at->toIso8601String(),
        ];
    }

    private function formatResponse(SecPointageResponse $r): array
    {
        return [
            'id'           => $r->id,
            'status'       => $r->status,
            'responded_at' => $r->responded_at?->toIso8601String(),
            'agent'        => [
                'id'    => $r->agent->id,
                'name'  => $r->agent->name,
                'photo' => $r->agent->photo,
            ],
            'zone'  => $r->zone  ? ['id' => $r->zone->id,  'name' => $r->zone->name]  : null,
            'poste' => $r->poste ? ['id' => $r->poste->id, 'name' => $r->poste->name] : null,
        ];
    }
}
