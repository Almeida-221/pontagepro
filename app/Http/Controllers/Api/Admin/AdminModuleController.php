<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;

class AdminModuleController extends Controller
{
    public function index()
    {
        $modules = Module::withCount(['plans', 'plans as active_plans_count' => fn($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return response()->json($modules->map(fn($m) => $this->format($m)));
    }

    public function toggle(Module $module)
    {
        $module->update(['is_active' => !$module->is_active]);
        return response()->json([
            'message'   => $module->is_active ? 'Module activé.' : 'Module désactivé.',
            'is_active' => $module->is_active,
        ]);
    }

    private function format(Module $m): array
    {
        return [
            'id'                => $m->id,
            'name'              => $m->name,
            'slug'              => $m->slug,
            'description'       => $m->description,
            'icon'              => $m->icon,
            'color'             => $m->color,
            'is_active'         => (bool) $m->is_active,
            'plans_count'       => $m->plans_count ?? 0,
            'active_plans_count'=> $m->active_plans_count ?? 0,
        ];
    }
}
