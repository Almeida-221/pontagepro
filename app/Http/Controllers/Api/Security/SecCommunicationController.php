<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\SecAffectation;
use App\Models\SecCommunication;
use Illuminate\Http\Request;

class SecCommunicationController extends Controller
{
    // GET /securite/communications
    // Retourne uniquement les communications ciblant le poste/zone/tour de l'agent
    public function index(Request $request)
    {
        $user = $request->user();

        // Affectation active de l'agent
        $affectation = SecAffectation::where('agent_id', $user->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        $agentPosteId = $affectation?->poste_id;
        $agentZoneId  = $user->zone_id;
        $agentTours   = collect($affectation?->tours ?? [])->pluck('type')->toArray();

        $communications = SecCommunication::where('company_id', $user->company_id)
            ->active()
            ->with('creator:id,name')
            // Filtre par poste : null = tous les postes, sinon vérifier inclusion
            ->where(function ($q) use ($agentPosteId) {
                $q->whereNull('poste_ids');
                if ($agentPosteId) {
                    $q->orWhereJsonContains('poste_ids', $agentPosteId);
                }
            })
            // Filtre par zone : null = toutes les zones
            ->where(function ($q) use ($agentZoneId) {
                $q->whereNull('zone_ids');
                if ($agentZoneId) {
                    $q->orWhereJsonContains('zone_ids', $agentZoneId);
                }
            })
            // Filtre par tour : null = tous les tours
            ->where(function ($q) use ($agentTours) {
                $q->whereNull('tour_ids');
                foreach ($agentTours as $tour) {
                    $q->orWhereJsonContains('tour_ids', $tour);
                }
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'title'      => $c->title,
                'message'    => $c->message,
                'audio_url'  => $c->audio_path
                    ? url('storage/' . $c->audio_path)
                    : null,
                'created_by' => $c->creator?->name,
                'created_at' => $c->created_at->toIso8601String(),
                'expires_at' => $c->expires_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $communications]);
    }

    // DELETE /securite/communications/{id}
    public function destroy(Request $request, SecCommunication $communication)
    {
        $user = $request->user();

        if ($communication->company_id !== $user->company_id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($user->role === 'agent_securite') {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $communication->delete();

        return response()->json(['message' => 'Communication supprimée.']);
    }
}
