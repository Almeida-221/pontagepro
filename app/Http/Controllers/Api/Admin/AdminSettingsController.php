<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    private array $allowedKeys = [
        'site_name', 'site_email', 'site_phone', 'site_address',
        'trial_days', 'grace_period_days',
        'fcm_super_admin_token',
        'maintenance_mode', 'maintenance_message',
    ];

    public function index()
    {
        $settings = SiteSetting::all_settings();
        $filtered = array_intersect_key($settings, array_flip($this->allowedKeys));
        return response()->json($filtered);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name'           => 'sometimes|string|max:100',
            'site_email'          => 'sometimes|email|max:150',
            'site_phone'          => 'sometimes|string|max:30',
            'site_address'        => 'sometimes|string|max:255',
            'trial_days'          => 'sometimes|integer|min:0',
            'grace_period_days'   => 'sometimes|integer|min:0',
            'maintenance_mode'    => 'sometimes|boolean',
            'maintenance_message' => 'sometimes|string|max:500',
        ]);

        foreach ($data as $key => $value) {
            SiteSetting::set($key, $value);
        }

        return response()->json(['message' => 'Paramètres mis à jour.', 'settings' => SiteSetting::all_settings()]);
    }
}
