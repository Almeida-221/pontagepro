<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profession;
use App\Models\WorkerCategory;
use Illuminate\Http\Request;

class ProfessionController extends Controller
{
    private function companyId(Request $request): int
    {
        return $request->user()->company_id;
    }

    // ── Professions ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $professions = Profession::where('company_id', $this->companyId($request))
            ->with('categories')
            ->orderBy('name')
            ->get();

        return response()->json(['professions' => $professions->map(fn($p) => [
            'id'         => $p->id,
            'name'       => $p->name,
            'categories' => $p->categories->map(fn($c) => [
                'id'         => $c->id,
                'name'       => $c->name,
                'daily_rate' => $c->daily_rate,
            ]),
        ])]);
    }

    public function storeProfession(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $exists = Profession::where('company_id', $this->companyId($request))
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Cette profession existe déjà'], 422);
        }

        $profession = Profession::create([
            'company_id' => $this->companyId($request),
            'name'       => $validated['name'],
        ]);

        return response()->json([
            'profession' => [
                'id'         => $profession->id,
                'name'       => $profession->name,
                'categories' => [],
            ],
        ], 201);
    }

    public function destroyProfession(Request $request, Profession $profession)
    {
        abort_if($profession->company_id !== $this->companyId($request), 403);
        $profession->delete();
        return response()->json(['message' => 'Supprimée']);
    }

    // ── Categories ───────────────────────────────────────────────────────────

    public function storeCategory(Request $request, Profession $profession)
    {
        abort_if($profession->company_id !== $this->companyId($request), 403);

        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'daily_rate' => 'required|numeric|min:0',
        ]);

        $exists = WorkerCategory::where('profession_id', $profession->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Cette catégorie existe déjà'], 422);
        }

        $category = WorkerCategory::create([
            'profession_id' => $profession->id,
            'name'          => $validated['name'],
            'daily_rate'    => $validated['daily_rate'],
        ]);

        return response()->json([
            'category' => [
                'id'         => $category->id,
                'name'       => $category->name,
                'daily_rate' => $category->daily_rate,
            ],
        ], 201);
    }

    public function updateCategory(Request $request, Profession $profession, WorkerCategory $category)
    {
        abort_if($profession->company_id !== $this->companyId($request), 403);
        abort_if($category->profession_id !== $profession->id, 403);

        $validated = $request->validate([
            'name'       => 'sometimes|string|max:100',
            'daily_rate' => 'sometimes|numeric|min:0',
        ]);

        $category->update($validated);

        return response()->json([
            'category' => [
                'id'         => $category->id,
                'name'       => $category->name,
                'daily_rate' => $category->daily_rate,
            ],
        ]);
    }

    public function destroyCategory(Request $request, Profession $profession, WorkerCategory $category)
    {
        abort_if($profession->company_id !== $this->companyId($request), 403);
        abort_if($category->profession_id !== $profession->id, 403);
        $category->delete();
        return response()->json(['message' => 'Supprimée']);
    }
}
