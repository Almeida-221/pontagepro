<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::with('module')->withCount('subscriptions')->orderBy('module_id')->orderBy('price')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        $modules = Module::active()->get();
        return view('admin.plans.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_id'   => ['required', 'exists:modules,id'],
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'max_workers' => ['required', 'integer', 'min:-1'],
            'price'       => ['required', 'numeric', 'min:0'],
            'is_active'   => ['boolean'],
        ]);

        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        Plan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan créé avec succès.');
    }

    public function edit(Plan $plan)
    {
        $modules = Module::active()->get();
        return view('admin.plans.edit', compact('plan', 'modules'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'module_id'   => ['required', 'exists:modules,id'],
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'max_workers' => ['required', 'integer', 'min:-1'],
            'price'       => ['required', 'numeric', 'min:0'],
            'is_active'   => ['boolean'],
        ]);

        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan mis à jour avec succès.');
    }

    public function destroy(Plan $plan)
    {
        if ($plan->subscriptions()->exists()) {
            return back()->withErrors(['error' => 'Ce plan ne peut pas être supprimé car il a des abonnements actifs.']);
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan supprimé avec succès.');
    }
}
