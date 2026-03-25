<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\SecZone;
use App\Models\User;
use Illuminate\Http\Request;

class SecZoneController extends Controller
{
    /** List all zones of the company. */
    public function index(Request $request)
    {
        $zones = SecZone::where('company_id', $request->user()->company_id)
            ->with('responsable:id,name,phone')
            ->withCount(['postes', 'agents'])
            ->orderBy('name')
            ->get();

        return response()->json($zones);
    }

    /** Create a new zone (admin only). */
    public function store(Request $request)
    {
        $this->requireAdmin($request);

        $validated = $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'nullable|string|max:500',
            'responsable_id' => 'nullable|exists:users,id',
        ]);

        // Ensure responsable belongs to same company and is gerant_securite
        if (!empty($validated['responsable_id'])) {
            $this->validateResponsable($validated['responsable_id'], $request->user()->company_id);
        }

        $zone = SecZone::create(array_merge($validated, [
            'company_id' => $request->user()->company_id,
        ]));

        // If a responsable is assigned, link the zone to their profile
        if (!empty($validated['responsable_id'])) {
            User::where('id', $validated['responsable_id'])->update(['zone_id' => $zone->id]);
        }

        return response()->json($zone->load('responsable:id,name,phone'), 201);
    }

    /** Update a zone (admin only). */
    public function update(Request $request, SecZone $zone)
    {
        $this->requireAdmin($request);
        $this->requireSameCompany($zone, $request->user()->company_id);

        $validated = $request->validate([
            'name'           => 'sometimes|string|max:150',
            'description'    => 'nullable|string|max:500',
            'responsable_id' => 'nullable|exists:users,id',
            'is_active'      => 'sometimes|boolean',
        ]);

        if (isset($validated['responsable_id'])) {
            // Unlink old responsable
            User::where('zone_id', $zone->id)->where('role', 'gerant_securite')
                ->update(['zone_id' => null]);

            if ($validated['responsable_id']) {
                $this->validateResponsable($validated['responsable_id'], $request->user()->company_id);
                User::where('id', $validated['responsable_id'])->update(['zone_id' => $zone->id]);
            }
        }

        $zone->update($validated);

        return response()->json($zone->load('responsable:id,name,phone'));
    }

    /** Delete a zone (admin only). */
    public function destroy(Request $request, SecZone $zone)
    {
        $this->requireAdmin($request);
        $this->requireSameCompany($zone, $request->user()->company_id);

        if ($zone->postes()->exists()) {
            return response()->json(['message' => 'Cette zone contient des postes. Supprimez-les d\'abord.'], 422);
        }

        $zone->delete();

        return response()->json(['message' => 'Zone supprimée.']);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function requireAdmin(Request $request): void
    {
        if ($request->user()->role !== 'company_admin') {
            abort(403, 'Seul l\'administrateur peut effectuer cette action.');
        }
    }

    private function requireSameCompany(SecZone $zone, int $companyId): void
    {
        if ($zone->company_id !== $companyId) {
            abort(403, 'Accès non autorisé.');
        }
    }

    private function validateResponsable(int $userId, int $companyId): void
    {
        $user = User::where('id', $userId)
            ->where('company_id', $companyId)
            ->where('role', 'gerant_securite')
            ->first();

        if (!$user) {
            abort(422, 'Le responsable doit être un gérant de sécurité de votre entreprise.');
        }
    }
}
