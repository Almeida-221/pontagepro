<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::with('module')->withCount('subscriptions')->orderBy('module_id')->orderBy('price')->get();

        return response()->json($plans->map(fn($p) => $this->format($p)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_id'   => 'required|exists:modules,id',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'max_workers' => 'required|integer|min:-1',
            'price'       => 'required|numeric|min:0',
            'is_active'   => 'boolean',
        ]);
        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $plan = Plan::create($validated);
        $plan->load('module');
        return response()->json($this->format($plan), 201);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'module_id'   => 'required|exists:modules,id',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'max_workers' => 'required|integer|min:-1',
            'price'       => 'required|numeric|min:0',
            'is_active'   => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $plan->update($validated);
        $plan->load('module');
        return response()->json($this->format($plan));
    }

    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions()->exists()) {
            return response()->json(['message' => 'Ce plan a des abonnements actifs, impossible de le supprimer.'], 422);
        }
        $plan->delete();
        return response()->json(['message' => 'Plan supprimé.']);
    }

    public function toggle(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        return response()->json(['message' => $plan->is_active ? 'Plan activé.' : 'Plan désactivé.', 'is_active' => $plan->is_active]);
    }

    private function format(Plan $p): array
    {
        return [
            'id'                  => $p->id,
            'name'                => $p->name,
            'description'         => $p->description,
            'max_workers'         => $p->max_workers,
            'price'               => (float) $p->price,
            'is_active'           => (bool) $p->is_active,
            'module_id'           => $p->module_id,
            'module_name'         => $p->module?->name,
            'module_icon'         => $p->module?->icon,
            'subscriptions_count' => $p->subscriptions_count ?? 0,
        ];
    }
}
