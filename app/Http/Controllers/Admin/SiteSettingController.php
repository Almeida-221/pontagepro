<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::all_settings();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name'       => 'required|string|max:100',
            'site_address'    => 'nullable|string|max:255',
            'site_email'      => 'nullable|email|max:100',
            'site_phone'      => 'nullable|string|max:30',
            'whatsapp_number' => 'nullable|string|max:30',
            'video_url'       => 'nullable|url|max:500',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,gif,svg,webp|max:2048',
            'slide1'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'slide2'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'slide3'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'slide4'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        // Simple text settings
        foreach (['site_name', 'site_address', 'site_email', 'site_phone', 'whatsapp_number', 'video_url'] as $key) {
            SiteSetting::set($key, $request->input($key, ''));
        }

        // Logo upload
        if ($request->hasFile('logo')) {
            $old = SiteSetting::get('logo_path');
            if ($old) Storage::disk('public')->delete($old);
            $path = $request->file('logo')->store('site', 'public');
            SiteSetting::set('logo_path', $path);
        }

        // Slider images
        for ($i = 1; $i <= 4; $i++) {
            $field = 'slide' . $i;
            $key   = 'slide' . $i . '_path';
            if ($request->hasFile($field)) {
                $old = SiteSetting::get($key);
                if ($old) Storage::disk('public')->delete($old);
                $path = $request->file($field)->store('slides', 'public');
                SiteSetting::set($key, $path);
            }
            // Delete slide if requested
            if ($request->boolean('delete_slide' . $i)) {
                $old = SiteSetting::get($key);
                if ($old) Storage::disk('public')->delete($old);
                SiteSetting::set($key, '');
            }
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }
}
