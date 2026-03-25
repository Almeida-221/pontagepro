<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\SecNotification;
use App\Models\SecPresenceConfirmation;
use App\Models\SecRapportJournalier;
use App\Models\SecZone;
use App\Models\User;
use Illuminate\Http\Request;

class SecRapportController extends Controller
{
    /** List daily reports for the company/zone. */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = SecRapportJournalier::where('company_id', $user->company_id)
            ->with(['zone:id,name', 'generatedBy:id,name'])
            ->orderByDesc('date');

        if ($user->role === 'gerant_securite') {
            $query->where('zone_id', $user->zone_id);
        }

        return response()->json($query->paginate(30));
    }

    /**
     * Generate the daily report for a zone.
     * Aggregates all presence confirmations for today in that zone.
     */
    public function generer(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'agent_securite') {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $validated = $request->validate([
            'zone_id' => 'required|exists:sec_zones,id',
            'date'    => 'sometimes|date|before_or_equal:today',
            'notes'   => 'nullable|string|max:1000',
        ]);

        $zoneId = $validated['zone_id'];
        $date   = $validated['date'] ?? today()->toDateString();

        // Gérant can only generate report for their zone
        if ($user->role === 'gerant_securite' && $user->zone_id !== (int) $zoneId) {
            return response()->json(['message' => 'Vous n\'êtes pas responsable de cette zone.'], 403);
        }

        $zone = SecZone::where('id', $zoneId)
            ->where('company_id', $user->company_id)
            ->firstOrFail();

        // Already generated today for this zone?
        if (SecRapportJournalier::where('zone_id', $zoneId)->where('date', $date)->exists()) {
            return response()->json(['message' => 'Un rapport existe déjà pour cette zone et cette date.'], 422);
        }

        // Count agents & confirmations for this zone today
        $confirmations = SecPresenceConfirmation::whereHas('session', fn($q) =>
            $q->where('zone_id', $zoneId)
              ->whereDate('launched_at', $date)
        )->with('agent:id,name', 'poste:id,name')->get();

        $agentsZone   = User::where('company_id', $user->company_id)
            ->where('zone_id', $zoneId)
            ->where('role', 'agent_securite')
            ->where('is_active', true)
            ->count();

        $presents = $confirmations->where('status', 'confirmed')->where('is_on_post', true)->count();
        $absents  = $confirmations->where('status', 'absent')->count()
                  + $confirmations->where('status', 'confirmed')->where('is_on_post', false)->count();

        // Detect anomalies (agents confirmed but not on post)
        $anomalies = $confirmations
            ->where('status', 'confirmed')
            ->where('is_on_post', false)
            ->map(fn($c) => "{$c->agent?->name} — hors poste ({$c->distance_meters}m)")
            ->implode('; ');

        $rapport = SecRapportJournalier::create([
            'company_id'      => $user->company_id,
            'zone_id'         => $zoneId,
            'date'            => $date,
            'generated_by'    => $user->id,
            'total_agents'    => $agentsZone,
            'agents_presents' => $presents,
            'agents_absents'  => $absents,
            'anomalies'       => $anomalies ?: null,
            'notes'           => $validated['notes'] ?? null,
        ]);

        return response()->json($rapport->load(['zone:id,name', 'generatedBy:id,name']), 201);
    }

    /**
     * Validate report and notify the company admin (responsable principal).
     */
    public function valider(Request $request, SecRapportJournalier $rapport)
    {
        $user = $request->user();

        if ($rapport->company_id !== $user->company_id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($rapport->is_validated) {
            return response()->json(['message' => 'Rapport déjà validé.'], 422);
        }

        $rapport->update(['validated_at' => now()]);

        // Notify the company_admin
        $admin = User::where('company_id', $user->company_id)
            ->where('role', 'company_admin')
            ->where('is_active', true)
            ->first();

        if ($admin) {
            $statusTxt  = $rapport->agents_absents === 0
                ? "Tous les agents sont présents sur leurs postes."
                : "{$rapport->agents_presents} présent(s), {$rapport->agents_absents} absent(s).";

            SecNotification::notifier(
                $admin->id,
                'rapport_ready',
                'Rapport journalier — ' . $rapport->zone->name,
                "Rapport du {$rapport->date->format('d/m/Y')} — Zone {$rapport->zone?->name} : {$statusTxt}",
                ['rapport_id' => $rapport->id, 'zone_id' => $rapport->zone_id]
            );

            $rapport->update(['notified_at' => now()]);
        }

        return response()->json([
            'message' => 'Rapport validé et responsable principal notifié.',
            'rapport' => $rapport->load(['zone:id,name', 'generatedBy:id,name']),
        ]);
    }
}
