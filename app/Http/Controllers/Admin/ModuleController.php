<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::withCount('plans')->orderBy('id')->get();
        return view('admin.modules.index', compact('modules'));
    }

    public function toggle(Module $module)
    {
        $module->update(['is_active' => !$module->is_active]);

        $status = $module->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Module « {$module->name} » {$status}.");
    }
}
