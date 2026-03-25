<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\SecAffectation;
use App\Models\SecNotification;
use App\Models\SecPoste;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SecAgentController extends Controller
{
    /** List agents (and gérants if admin). */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = User::where('company_id', $user->company_id)
            ->whereIn('role', ['gerant_securite', 'agent_securite'])
            ->with(['zone:id,name']);

        // Gérant sees only agents in their zone
        if ($user->role === 'gerant_securite') {
            $query->where('role', 'agent_securite')->where('zone_id', $user->zone_id);
        }

        $agents = $query->orderBy('name')->get()->map(fn($a) => $this->formatAgent($a));

        return response()->json($agents);
    }

    /** Create a gérant (admin only) or agent (admin or gérant). */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:150',
            'phone'          => 'required|string|max:20|unique:users,phone',
            'role'           => 'required|in:gerant_securite,agent_securite',
            'gender'         => 'nullable|in:m,f',
            'zone_id'        => 'nullable|exists:sec_zones,id',
            'poste_id'       => 'nullable|exists:sec_postes,id',
            'off_days'       => 'nullable|array',
            'off_days.*'     => 'integer|min:1|max:31',
            'rest_days'        => 'nullable|array',
            'rest_days.*'      => 'integer|min:1|max:7',
            'tours'            => 'nullable|array',
            'tours.*.type'     => 'required_with:tours|in:matin,soir,nuit',
            'tours.*.start'    => 'required_with:tours|date_format:H:i',
            'tours.*.end'      => 'required_with:tours|date_format:H:i',
            'photo'          => 'nullable|image|max:4096',
            'id_photo_front' => 'nullable|image|max:4096',
            'id_photo_back'  => 'nullable|image|max:4096',
            // Informations financières / contrat
            'is_employed'    => 'nullable|boolean',
            'salary'         => 'nullable|numeric|min:0',
            'contract_type'  => 'nullable|in:CDI,CDD,prestataire',
            'contract_start' => 'nullable|date',
            'contract_end'   => 'nullable|date|after:contract_start',
        ]);

        // Only admin can create a gérant
        if ($validated['role'] === 'gerant_securite' && $request->user()->role !== 'company_admin') {
            return response()->json(['message' => 'Seul l\'administrateur peut créer un gérant.'], 403);
        }

        // Gérant can only create agents in their own zone
        if ($request->user()->role === 'gerant_securite') {
            $validated['zone_id'] = $request->user()->zone_id;
        }

        // Store uploaded photos
        $photoPath        = $request->hasFile('photo')          ? $request->file('photo')->store('agents/photos', 'public')          : null;
        $idFrontPath      = $request->hasFile('id_photo_front') ? $request->file('id_photo_front')->store('agents/id_cards', 'public') : null;
        $idBackPath       = $request->hasFile('id_photo_back')  ? $request->file('id_photo_back')->store('agents/id_cards', 'public')  : null;

        $user = User::create([
            'name'           => $validated['name'],
            'phone'          => $validated['phone'],
            'role'           => $validated['role'],
            'gender'         => $validated['gender'] ?? null,
            'company_id'     => $request->user()->company_id,
            'zone_id'        => $validated['zone_id'] ?? null,
            'password'       => Hash::make(Str::random(16)),
            'is_active'      => true,
            'is_employed'    => filter_var($validated['is_employed'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'salary'         => $validated['salary'] ?? null,
            'contract_type'  => $validated['contract_type'] ?? null,
            'contract_start' => $validated['contract_start'] ?? null,
            'contract_end'   => $validated['contract_end'] ?? null,
            'photo'          => $photoPath,
            'id_photo_front' => $idFrontPath,
            'id_photo_back'  => $idBackPath,
        ]);

        // Affecter l'agent + planning dans la même affectation
        if ($validated['role'] === 'agent_securite' && !empty($validated['poste_id'])) {
            $poste = SecPoste::where('id', $validated['poste_id'])
                ->where('company_id', $request->user()->company_id)
                ->first();

            if ($poste) {
                SecAffectation::create([
                    'agent_id'    => $user->id,
                    'poste_id'    => $poste->id,
                    'assigned_by' => $request->user()->id,
                    'started_at'  => now(),
                    'is_active'   => true,
                    'rest_days'   => isset($validated['rest_days'])
                        ? array_values(array_unique(array_map('intval', $validated['rest_days'])))
                        : [],
                    'off_days'    => isset($validated['off_days'])
                        ? array_values(array_unique(array_map('intval', $validated['off_days'])))
                        : [],
                    'tours'       => $validated['tours'] ?? [],
                ]);
            }
        }

        return response()->json($this->formatAgent($user->load('zone:id,name')), 201);
    }

    /** Update an agent or gérant. */
    public function update(Request $request, User $agent)
    {
        $this->requireSameCompany($agent, $request->user()->company_id);

        $validated = $request->validate([
            'name'    => 'sometimes|string|max:150',
            'phone'   => 'sometimes|string|max:20|unique:users,phone,' . $agent->id,
            'zone_id' => 'nullable|exists:sec_zones,id',
        ]);

        $agent->update($validated);

        return response()->json($this->formatAgent($agent->load('zone:id,name')));
    }

    /** Toggle agent active status. */
    public function toggle(Request $request, User $agent)
    {
        $this->requireSameCompany($agent, $request->user()->company_id);

        $agent->update(['is_active' => !$agent->is_active]);

        return response()->json([
            'message'   => $agent->is_active ? 'Agent activé.' : 'Agent désactivé.',
            'is_active' => $agent->is_active,
        ]);
    }

    /** Delete an agent. */
    public function destroy(Request $request, User $agent)
    {
        $this->requireSameCompany($agent, $request->user()->company_id);

        if ($request->user()->role !== 'company_admin' && $agent->role === 'gerant_securite') {
            return response()->json(['message' => 'Seul l\'administrateur peut supprimer un gérant.'], 403);
        }

        // Close active affectations
        SecAffectation::where('agent_id', $agent->id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'ended_at' => now()]);

        $agent->delete();

        return response()->json(['message' => 'Compte supprimé.']);
    }

    /** Assign agent to a poste. */
    public function affecter(Request $request, User $agent)
    {
        $this->requireSameCompany($agent, $request->user()->company_id);

        if ($agent->role !== 'agent_securite') {
            return response()->json(['message' => 'Seuls les agents peuvent être affectés à un poste.'], 422);
        }

        $validated = $request->validate([
            'poste_id' => 'required|exists:sec_postes,id',
        ]);

        $poste = SecPoste::where('id', $validated['poste_id'])
            ->where('company_id', $request->user()->company_id)
            ->where('is_active', true)
            ->firstOrFail();

        // Close previous affectation
        SecAffectation::where('agent_id', $agent->id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'ended_at' => now()]);

        // Create new affectation
        $affectation = SecAffectation::create([
            'agent_id'    => $agent->id,
            'poste_id'    => $poste->id,
            'assigned_by' => $request->user()->id,
            'started_at'  => now(),
            'is_active'   => true,
        ]);

        // Notify the agent
        SecNotification::notifier(
            $agent->id,
            'affectation_changed',
            'Nouvelle affectation',
            "Vous avez été affecté au poste : {$poste->name}.",
            ['poste_id' => $poste->id, 'poste_name' => $poste->name, 'address' => $poste->address]
        );

        return response()->json([
            'message'     => 'Agent affecté avec succès.',
            'affectation' => $affectation->load('poste:id,name,address,latitude,longitude'),
        ], 201);
    }

    /** History of all postes an agent has been assigned to. */
    public function historique(Request $request, User $agent)
    {
        $this->requireSameCompany($agent, $request->user()->company_id);

        $history = SecAffectation::where('agent_id', $agent->id)
            ->with('poste:id,name,address,zone_id', 'poste.zone:id,name', 'assignedBy:id,name')
            ->orderByDesc('started_at')
            ->get()
            ->map(fn($a) => [
                'id'           => $a->id,
                'poste'        => $a->poste?->name,
                'zone'         => $a->poste?->zone?->name,
                'address'      => $a->poste?->address,
                'assigned_by'  => $a->assignedBy?->name,
                'started_at'   => $a->started_at,
                'ended_at'     => $a->ended_at,
                'is_active'    => $a->is_active,
                'is_validated' => $a->is_validated,
                'validated_at' => $a->validated_at,
            ]);

        return response()->json($history);
    }

    /** Update agent schedule (closes old affectation, opens new one with new planning). */
    public function updateCalendrier(Request $request, User $agent)
    {
        $this->requireSameCompany($agent, $request->user()->company_id);

        if ($agent->role !== 'agent_securite') {
            return response()->json(['message' => 'Seuls les agents peuvent avoir une affectation.'], 422);
        }

        $validated = $request->validate([
            'poste_id'   => 'nullable|exists:sec_postes,id',
            'off_days'   => 'nullable|array',
            'off_days.*' => 'integer|min:1|max:31',
            'rest_days'  => 'nullable|array',
            'rest_days.*'=> 'integer|min:1|max:7',
            'tours'      => 'nullable|array',
            'tours.*.type'  => 'required_with:tours|in:matin,soir,nuit',
            'tours.*.start' => 'required_with:tours|date_format:H:i',
            'tours.*.end'   => 'required_with:tours|date_format:H:i',
        ]);

        // Resolve poste (keep current if not provided)
        $posteId = null;
        if (!empty($validated['poste_id'])) {
            $poste = SecPoste::where('id', $validated['poste_id'])
                ->where('company_id', $request->user()->company_id)
                ->where('is_active', true)
                ->firstOrFail();
            $posteId = $poste->id;
        } else {
            $current = SecAffectation::where('agent_id', $agent->id)->where('is_active', true)->latest()->first();
            $posteId = $current?->poste_id;
        }

        // Close current affectation
        SecAffectation::where('agent_id', $agent->id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'ended_at' => now()]);

        // Create new affectation with updated planning
        $affectation = SecAffectation::create([
            'agent_id'    => $agent->id,
            'poste_id'    => $posteId,
            'assigned_by' => $request->user()->id,
            'started_at'  => now(),
            'is_active'   => true,
            'rest_days'   => isset($validated['rest_days'])
                ? array_values(array_unique(array_map('intval', $validated['rest_days'])))
                : [],
            'off_days'    => isset($validated['off_days'])
                ? array_values(array_unique(array_map('intval', $validated['off_days'])))
                : [],
            'tours'       => $validated['tours'] ?? [],
        ]);

        return response()->json([
            'message'     => 'Planning mis à jour.',
            'affectation' => $affectation->load('poste:id,name,address'),
            'agent'       => $this->formatAgent($agent->load('zone:id,name')),
        ]);
    }

    /** Affectation history for an agent. */
    public function affectationHistory(Request $request, User $agent)
    {
        $this->requireSameCompany($agent, $request->user()->company_id);

        $history = SecAffectation::where('agent_id', $agent->id)
            ->with('poste:id,name,address,zone_id', 'poste.zone:id,name', 'assignedBy:id,name')
            ->orderByDesc('started_at')
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'poste_id'    => $a->poste_id,
                'poste_name'  => $a->poste?->name,
                'zone_name'   => $a->poste?->zone?->name,
                'address'     => $a->poste?->address,
                'assigned_by' => $a->assignedBy?->name,
                'started_at'  => $a->started_at,
                'ended_at'    => $a->ended_at,
                'is_active'   => $a->is_active,
                'rest_days'   => $a->rest_days ?? [],
                'off_days'    => $a->off_days  ?? [],
                'tours'       => $a->tours     ?? [],
            ]);

        return response()->json($history);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function requireSameCompany(User $agent, int $companyId): void
    {
        if ($agent->company_id !== $companyId) {
            abort(403, 'Accès non autorisé.');
        }
    }

    private function formatAgent(User $user): array
    {
        $affectation = SecAffectation::where('agent_id', $user->id)
            ->where('is_active', true)
            ->with('poste:id,name,address,latitude,longitude')
            ->latest()
            ->first();

        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'phone'          => $user->phone,
            'role'           => $user->role,
            'gender'         => $user->gender,
            'zone_id'        => $user->zone_id,
            'zone_name'      => $user->zone?->name,
            'is_active'      => $user->is_active,
            'needs_setup'    => is_null($user->pin_code),
            'photo'          => $user->photo ? asset('storage/' . $user->photo) : null,
            'id_photo_front' => $user->id_photo_front ? asset('storage/' . $user->id_photo_front) : null,
            'id_photo_back'  => $user->id_photo_back  ? asset('storage/' . $user->id_photo_back)  : null,
            'is_employed'    => (bool) $user->is_employed,
            'salary'         => $user->salary,
            'contract_type'  => $user->contract_type,
            'contract_start' => $user->contract_start,
            'contract_end'   => $user->contract_end,
            'poste_actuel' => $affectation ? [
                'affectation_id' => $affectation->id,
                'poste_id'       => $affectation->poste?->id,
                'poste_name'     => $affectation->poste?->name,
                'address'        => $affectation->poste?->address,
                'latitude'       => $affectation->poste?->latitude,
                'longitude'      => $affectation->poste?->longitude,
                'is_validated'   => $affectation->is_validated,
            ] : null,
            'rest_days' => $affectation?->rest_days ?? [],
            'off_days'  => $affectation?->off_days  ?? [],
            'tours'     => $affectation?->tours     ?? [],
        ];
    }
}
