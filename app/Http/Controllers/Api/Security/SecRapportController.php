<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\SecAffectation;
use App\Models\SecNotification;
use App\Models\SecPointageResponse;
use App\Models\SecPresenceConfirmation;
use App\Models\SecRapportJournalier;
use App\Models\SecRemplacement;
use App\Models\SecZone;
use App\Models\User;
use Carbon\Carbon;
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

    // ─────────────────────────────────────────────────────────────────────────
    //  Rapport avancé — Remplacements
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Rapport agrégé par agent basé sur les remplacements.
     * Filtres: annee, mois, jour, zone_id, poste_id
     */
    public function rapportRemplacements(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'agent_securite') {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        [$startDate, $endDate, $periodeLabel] = $this->parsePeriode($request);
        $companyId = $user->company_id;

        $agentsQuery = User::where('company_id', $companyId)
            ->where('role', 'agent_securite')
            ->where('is_active', true)
            ->with([
                'zone:id,name',
                'secAffectationActive.poste:id,name,zone_id',
                'secAffectationActive.poste.zone:id,name',
            ]);

        if ($request->filled('zone_id')) {
            $agentsQuery->where('zone_id', $request->zone_id);
        }
        if ($request->filled('poste_id')) {
            $agentsQuery->whereHas('secAffectationActive', fn($q) =>
                $q->where('poste_id', $request->poste_id)
            );
        }

        $agents = $agentsQuery->orderBy('name')->get();
        $data   = [];

        foreach ($agents as $agent) {
            $aff      = $agent->secAffectationActive;
            $restDays = $aff ? (array) $aff->rest_days : [];
            $offDays  = $aff ? (array) $aff->off_days  : [];

            [$workDays, $reposDays] = $this->computeWorkAndRestDays($startDate, $endDate, $restDays, $offDays);

            $joursRemplaces = SecRemplacement::where('company_id', $companyId)
                ->where('statut', 'confirme')
                ->where(fn($q) =>
                    $q->where('agent_sortant_id', $agent->id)
                      ->orWhere('agent_entrant_id', $agent->id)
                )
                ->whereBetween('date', [$startDate, $endDate])
                ->distinct('date')
                ->count('date');

            $data[] = [
                'agent'            => ['id' => $agent->id, 'name' => $agent->name],
                'zone'             => $aff?->poste?->zone?->name ?? $agent->zone?->name ?? '—',
                'poste'            => $aff?->poste?->name ?? '—',
                'jours_travailles' => $joursRemplaces,
                'jours_absents'    => max(0, $workDays - $joursRemplaces),
                'jours_repos'      => $reposDays,
                'statut'           => '—',
            ];
        }

        return response()->json([
            'data'    => $data,
            'periode' => ['debut' => $startDate, 'fin' => $endDate, 'label' => $periodeLabel],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Rapport avancé — Pointages
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Rapport agrégé par agent basé sur les pointages (remote + local).
     * Filtres: annee, mois, jour, zone_id, poste_id
     */
    public function rapportPointages(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'agent_securite') {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        [$startDate, $endDate, $periodeLabel] = $this->parsePeriode($request);
        $companyId = $user->company_id;

        $agentsQuery = User::where('company_id', $companyId)
            ->where('role', 'agent_securite')
            ->where('is_active', true)
            ->with([
                'zone:id,name',
                'secAffectationActive.poste:id,name,zone_id',
                'secAffectationActive.poste.zone:id,name',
            ]);

        if ($request->filled('zone_id')) {
            $agentsQuery->where('zone_id', $request->zone_id);
        }
        if ($request->filled('poste_id')) {
            $agentsQuery->whereHas('secAffectationActive', fn($q) =>
                $q->where('poste_id', $request->poste_id)
            );
        }

        $agents = $agentsQuery->orderBy('name')->get();
        $data   = [];

        foreach ($agents as $agent) {
            $aff      = $agent->secAffectationActive;
            $restDays = $aff ? (array) $aff->rest_days : [];
            $offDays  = $aff ? (array) $aff->off_days  : [];

            [$workDays, $reposDays] = $this->computeWorkAndRestDays($startDate, $endDate, $restDays, $offDays);

            $jours = SecPointageResponse::where('sec_pointage_responses.agent_id', $agent->id)
                ->where('sec_pointage_responses.status', 'present')
                ->join('sec_pointages', 'sec_pointages.id', '=', 'sec_pointage_responses.pointage_id')
                ->where('sec_pointages.company_id', $companyId)
                ->whereBetween('sec_pointages.date', [$startDate, $endDate])
                ->selectRaw('COUNT(DISTINCT DATE(sec_pointages.date)) as cnt')
                ->value('cnt') ?? 0;

            $data[] = [
                'agent'            => ['id' => $agent->id, 'name' => $agent->name],
                'zone'             => $aff?->poste?->zone?->name ?? $agent->zone?->name ?? '—',
                'poste'            => $aff?->poste?->name ?? '—',
                'jours_travailles' => (int) $jours,
                'jours_absents'    => max(0, $workDays - (int) $jours),
                'jours_repos'      => $reposDays,
                'statut'           => '—',
            ];
        }

        return response()->json([
            'data'    => $data,
            'periode' => ['debut' => $startDate, 'fin' => $endDate, 'label' => $periodeLabel],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function parsePeriode(Request $request): array
    {
        if ($request->filled('jour')) {
            $d = Carbon::parse($request->jour);
            return [$d->toDateString(), $d->toDateString(), $d->translatedFormat('d F Y')];
        }

        $year  = (int) ($request->annee ?? now()->year);
        $month = (int) ($request->mois  ?? now()->month);

        if ($request->filled('mois')) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = $start->copy()->endOfMonth();
            return [$start->toDateString(), $end->toDateString(), $start->translatedFormat('F Y')];
        }

        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end   = Carbon::create($year, 12, 31)->endOfYear();
        return [$start->toDateString(), $end->toDateString(), (string) $year];
    }

    private function computeWorkAndRestDays(string $start, string $end, array $restDays, array $offDays): array
    {
        $current  = Carbon::parse($start);
        $endDate  = Carbon::parse($end);
        $workDays = 0;
        $reposDays = 0;

        while ($current->lte($endDate)) {
            $dow = $current->dayOfWeekIso; // 1=Lundi … 7=Dimanche
            $dom = $current->day;

            if (in_array($dow, array_map('intval', $restDays))
                || in_array($dom, array_map('intval', $offDays))) {
                $reposDays++;
            } else {
                $workDays++;
            }
            $current->addDay();
        }

        return [$workDays, $reposDays];
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
