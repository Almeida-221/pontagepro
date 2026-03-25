<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\SecPoste;
use App\Models\SecZone;
use Illuminate\Http\Request;

class SecPosteController extends Controller
{
    /** List postes — admin sees all, gérant sees only their zone. */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = SecPoste::where('company_id', $user->company_id)
            ->with(['zone:id,name'])
            ->withCount(['affectations' => fn($q) => $q->where('is_active', true)]);

        if ($user->role === 'gerant_securite') {
            $query->whereHas('zone', fn($q) => $q->where('id', $user->zone_id));
        }

        return response()->json($query->orderBy('name')->get());
    }

    /** Create a poste (admin or gérant of that zone). */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'zone_id'  => 'required|exists:sec_zones,id',
            'name'     => 'required|string|max:200',
            'address'  => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude'=> 'nullable|numeric|between:-180,180',
        ]);

        $this->authorizeZone($request, $validated['zone_id']);

        $poste = SecPoste::create(array_merge($validated, [
            'company_id'    => $request->user()->company_id,
            'radius_meters' => 100,
        ]));

        return response()->json($poste->load('zone:id,name'), 201);
    }

    /** Update a poste. */
    public function update(Request $request, SecPoste $poste)
    {
        $this->requireSameCompany($poste, $request->user()->company_id);
        $this->authorizeZone($request, $poste->zone_id);

        $validated = $request->validate([
            'name'          => 'sometimes|string|max:200',
            'address'       => 'nullable|string|max:500',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'sometimes|integer|min:50|max:1000',
            'is_active'     => 'sometimes|boolean',
        ]);

        $poste->update($validated);

        return response()->json($poste->load('zone:id,name'));
    }

    /** Delete a poste. */
    public function destroy(Request $request, SecPoste $poste)
    {
        $this->requireSameCompany($poste, $request->user()->company_id);
        $this->authorizeZone($request, $poste->zone_id);

        if ($poste->affectations()->where('is_active', true)->exists()) {
            return response()->json(['message' => 'Ce poste a des agents affectés. Réaffectez-les d\'abord.'], 422);
        }

        $poste->delete();

        return response()->json(['message' => 'Poste supprimé.']);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function requireSameCompany(SecPoste $poste, int $companyId): void
    {
        if ($poste->company_id !== $companyId) {
            abort(403, 'Accès non autorisé.');
        }
    }

    private function authorizeZone(Request $request, int $zoneId): void
    {
        $user = $request->user();

        if ($user->role === 'company_admin') return;

        // Gérant can only manage postes in their own zone
        if ($user->role === 'gerant_securite' && $user->zone_id === $zoneId) return;

        abort(403, 'Vous n\'êtes pas responsable de cette zone.');
    }
}
