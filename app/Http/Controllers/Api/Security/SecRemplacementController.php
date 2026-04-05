<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Jobs\SendFcmNotifications;
use App\Models\SecAffectation;
use App\Models\SecNotification;
use App\Models\SecRemplacement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SecRemplacementController extends Controller
{
    // ── Scan du QR code de l'agent sortant ───────────────────────────────────
    // POST /securite/remplacements/scan
    // Body: { qr_data: "sb_agent_42" }
    // Retourne les infos de l'agent sortant + son poste actuel
    public function scan(Request $request)
    {
        $agentEntrant = $request->user();

        $validated = $request->validate([
            'qr_data' => 'required|string',
        ]);

        // Parser le QR : format "sb_agent_{id}"
        $qr = trim($validated['qr_data']);
        if (!str_starts_with($qr, 'sb_agent_')) {
            return response()->json(['message' => 'QR code invalide.'], 422);
        }

        $agentSortantId = (int) substr($qr, strlen('sb_agent_'));
        if ($agentSortantId <= 0) {
            return response()->json(['message' => 'QR code invalide.'], 422);
        }

        // L'agent entrant ne peut pas se scanner lui-même
        if ($agentSortantId === $agentEntrant->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous scanner vous-même.'], 422);
        }

        $agentSortant = User::find($agentSortantId);

        if (!$agentSortant || $agentSortant->company_id !== $agentEntrant->company_id) {
            return response()->json(['message' => 'Agent non trouvé dans cette entreprise.'], 404);
        }

        if (!$agentSortant->is_active) {
            return response()->json(['message' => 'Cet agent est inactif.'], 422);
        }

        // Récupérer l'affectation active de l'agent sortant
        $affectationSortant = SecAffectation::where('agent_id', $agentSortant->id)
            ->where('is_active', true)
            ->with(['poste', 'poste.zone'])
            ->latest()
            ->first();

        // Récupérer l'affectation active de l'agent entrant (celui qui scanne)
        $affectationEntrant = SecAffectation::where('agent_id', $agentEntrant->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        // ── Règle métier : même poste obligatoire ────────────────────────────
        $posteSortant = $affectationSortant?->poste_id;
        $posteEntrant = $affectationEntrant?->poste_id;

        if (!$posteSortant || !$posteEntrant) {
            return response()->json([
                'message' => 'Remplacement impossible : un des agents n\'est pas affecté à un poste actif.',
            ], 422);
        }

        if ($posteSortant !== $posteEntrant) {
            return response()->json([
                'message' => 'Remplacement refusé : vous n\'êtes pas affecté au même poste que ' . $agentSortant->name . '.',
                'poste_sortant' => $affectationSortant->poste?->name,
                'poste_entrant' => $affectationEntrant?->poste?->name,
            ], 422);
        }
        // ─────────────────────────────────────────────────────────────────────

        $affectation = $affectationSortant;

        return response()->json([
            'agent_sortant' => [
                'id'    => $agentSortant->id,
                'name'  => $agentSortant->name,
                'phone' => $agentSortant->phone,
                'photo' => $agentSortant->photo,
                'role'  => $agentSortant->role,
            ],
            'poste' => $affectation?->poste ? [
                'id'      => $affectation->poste->id,
                'name'    => $affectation->poste->name,
                'address' => $affectation->poste->address,
            ] : null,
            'zone' => $affectation?->poste?->zone ? [
                'id'   => $affectation->poste->zone->id,
                'name' => $affectation->poste->zone->name,
            ] : null,
            'affectation_id' => $affectation?->id,
        ]);
    }

    // ── Confirmer le remplacement ─────────────────────────────────────────────
    // POST /securite/remplacements/confirmer
    // Body: { agent_sortant_id: 42 }
    public function confirmer(Request $request)
    {
        $agentEntrant = $request->user();

        $validated = $request->validate([
            'agent_sortant_id' => 'required|integer|exists:users,id',
        ]);

        $agentSortant = User::find($validated['agent_sortant_id']);

        if ($agentSortant->company_id !== $agentEntrant->company_id) {
            return response()->json(['message' => 'Agent non trouvé dans cette entreprise.'], 403);
        }

        if ($agentSortant->id === $agentEntrant->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous remplacer vous-même.'], 422);
        }

        // Récupérer l'affectation active de l'agent sortant
        $affectation = SecAffectation::where('agent_id', $agentSortant->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        // ── Double sécurité : même poste ─────────────────────────────────────
        $affectationEntrant = SecAffectation::where('agent_id', $agentEntrant->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        if (!$affectation?->poste_id || !$affectationEntrant?->poste_id) {
            return response()->json([
                'message' => 'Remplacement impossible : un des agents n\'est pas affecté à un poste actif.',
            ], 422);
        }

        if ($affectation->poste_id !== $affectationEntrant->poste_id) {
            return response()->json([
                'message' => 'Remplacement refusé : les deux agents doivent être affectés au même poste.',
            ], 422);
        }
        // ─────────────────────────────────────────────────────────────────────

        $posteId = $affectation?->poste_id;
        $zoneId  = $affectation
            ? ($affectation->poste?->zone_id ?? null)
            : null;

        // Charger le poste pour récupérer la zone si besoin
        if ($posteId && !$zoneId) {
            $affectation->load('poste');
            $zoneId = $affectation->poste?->zone_id;
        }

        $heureActuelle = now()->format('H:i:s');

        $remplacement = SecRemplacement::create([
            'company_id'       => $agentEntrant->company_id,
            'agent_sortant_id' => $agentSortant->id,
            'agent_entrant_id' => $agentEntrant->id,
            'poste_id'         => $posteId,
            'zone_id'          => $zoneId,
            'date'             => today(),
            'heure_entree'     => $heureActuelle,
            'heure_sortie'     => $heureActuelle,
            'statut'           => 'confirme',
        ]);

        $heure        = now()->format('H\hi');
        $posteName    = $affectation?->poste?->name ?? 'votre poste';
        $fcmTokens    = [];

        // ── Notification Agent Sortant ────────────────────────────────────────
        SecNotification::notifier(
            $agentSortant->id,
            'remplacement',
            'Remplacement',
            "{$agentEntrant->name} vous a remplacé\nSortie enregistrée à $heure",
            [
                'remplacement_id'  => $remplacement->id,
                'agent_entrant_id' => $agentEntrant->id,
                'heure'            => $heure,
                'poste'            => $posteName,
            ]
        );
        if ($agentSortant->fcm_token) {
            $fcmTokens['sortant'] = $agentSortant->fcm_token;
        }

        // ── Notification Agent Entrant ────────────────────────────────────────
        SecNotification::notifier(
            $agentEntrant->id,
            'remplacement',
            'Remplacement',
            "Vous avez remplacé {$agentSortant->name}\nEntrée enregistrée à $heure",
            [
                'remplacement_id'  => $remplacement->id,
                'agent_sortant_id' => $agentSortant->id,
                'heure'            => $heure,
                'poste'            => $posteName,
            ]
        );
        if ($agentEntrant->fcm_token) {
            $fcmTokens['entrant'] = $agentEntrant->fcm_token;
        }

        // ── Push FCM ─────────────────────────────────────────────────────────
        if (!empty($fcmTokens['sortant'])) {
            SendFcmNotifications::dispatch(
                [$fcmTokens['sortant']],
                'Remplacement',
                "{$agentEntrant->name} vous a remplacé — Sortie à $heure",
                ['type' => 'remplacement', 'remplacement_id' => (string) $remplacement->id]
            );
        }
        if (!empty($fcmTokens['entrant'])) {
            SendFcmNotifications::dispatch(
                [$fcmTokens['entrant']],
                'Remplacement',
                "Vous avez remplacé {$agentSortant->name} — Entrée à $heure",
                ['type' => 'remplacement', 'remplacement_id' => (string) $remplacement->id]
            );
        }

        $remplacement->load(['agentSortant', 'agentEntrant', 'poste', 'zone']);

        return response()->json([
            'message'      => 'Remplacement confirmé.',
            'remplacement' => $this->format($remplacement),
        ], 201);
    }

    // ── Historique des remplacements ──────────────────────────────────────────
    // GET /securite/remplacements
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = SecRemplacement::where('company_id', $user->company_id)
            ->with(['agentSortant', 'agentEntrant', 'poste', 'zone'])
            ->orderByDesc('date')
            ->orderByDesc('heure_entree');

        // Filtre par date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        // Filtre par zone
        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }
        // Filtre par poste
        if ($request->filled('poste_id')) {
            $query->where('poste_id', $request->poste_id);
        }
        // Filtre par agent (sortant ou entrant)
        if ($request->filled('agent_id')) {
            $id = (int) $request->agent_id;
            $query->where(fn($q) =>
                $q->where('agent_sortant_id', $id)
                  ->orWhere('agent_entrant_id', $id)
            );
        }

        // Si agent : seulement ses propres remplacements
        if ($user->role === 'agent_securite') {
            $query->where(fn($q) =>
                $q->where('agent_sortant_id', $user->id)
                  ->orWhere('agent_entrant_id', $user->id)
            );
        }

        $remplacements = $query->paginate(20);

        return response()->json([
            'data'  => $remplacements->getCollection()->map(fn($r) => $this->format($r)),
            'meta'  => [
                'current_page' => $remplacements->currentPage(),
                'last_page'    => $remplacements->lastPage(),
                'total'        => $remplacements->total(),
            ],
        ]);
    }

    // ── Supprimer ─────────────────────────────────────────────────────────────
    // DELETE /securite/remplacements/{id}
    public function destroy(Request $request, SecRemplacement $remplacement)
    {
        $user = $request->user();

        if ($remplacement->company_id !== $user->company_id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        // Seuls gérant et admin peuvent supprimer
        if ($user->role === 'agent_securite') {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $remplacement->delete();

        return response()->json(['message' => 'Remplacement supprimé.']);
    }

    // ── Détail ────────────────────────────────────────────────────────────────
    // GET /securite/remplacements/{id}
    public function show(Request $request, SecRemplacement $remplacement)
    {
        $user = $request->user();
        if ($remplacement->company_id !== $user->company_id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $remplacement->load(['agentSortant', 'agentEntrant', 'poste', 'zone']);

        return response()->json(['remplacement' => $this->format($remplacement)]);
    }

    // ── Helper format ─────────────────────────────────────────────────────────
    private function format(SecRemplacement $r): array
    {
        return [
            'id'            => $r->id,
            'date'          => $r->date->toDateString(),
            'heure_entree'  => $r->heure_entree,
            'heure_sortie'  => $r->heure_sortie,
            'statut'        => $r->statut,
            'agent_sortant' => $r->agentSortant ? [
                'id'    => $r->agentSortant->id,
                'name'  => $r->agentSortant->name,
                'photo' => $r->agentSortant->photo,
            ] : null,
            'agent_entrant' => $r->agentEntrant ? [
                'id'    => $r->agentEntrant->id,
                'name'  => $r->agentEntrant->name,
                'photo' => $r->agentEntrant->photo,
            ] : null,
            'poste' => $r->poste ? ['id' => $r->poste->id, 'name' => $r->poste->name] : null,
            'zone'  => $r->zone  ? ['id' => $r->zone->id,  'name' => $r->zone->name]  : null,
            'created_at' => $r->created_at->toIso8601String(),
        ];
    }
}
