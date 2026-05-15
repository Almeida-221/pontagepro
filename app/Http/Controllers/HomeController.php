<?php

namespace App\Http\Controllers;

use App\Models\Company;
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

        $partners = collect();
        if (!empty($settings['show_partners'])) {
            $partners = Company::where('status', 'active')
                ->whereHas('subscriptions', fn($q) => $q->where('status', 'active')->where('end_date', '>', now()))
                ->get(['id', 'name']);
        }

        return view('welcome', compact('modules', 'settings', 'partners'));
    }
}
