<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\SiteSetting;

class HomeController extends Controller
{
    public function index()
    {
        $modules = Module::active()->with(['plans' => function ($q) {
            $q->where('is_active', true)->orderBy('price');
        }])->get();

        $settings = SiteSetting::all_settings();

        return view('welcome', compact('modules', 'settings'));
    }
}
