<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdVideoController extends Controller
{
    public function index()
    {
        $videos = AdVideo::latest()->get();
        return view('admin.ad_videos.index', compact('videos'));
    }

    public function create()
    {
        return view('admin.ad_videos.form', ['video' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:150',
            'description'  => 'nullable|string|max:1000',
            'app_target'   => 'required|in:pointage,securite,both',
            'video_url'    => 'nullable|url|max:500',
            'video_file'   => 'nullable|mimes:mp4,mov,webm|max:204800',
            'thumbnail'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active'    => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date|after_or_equal:published_at',
        ]);

        $data['is_active']    = $request->boolean('is_active');
        $data['published_at'] = $request->filled('published_at') ? $request->input('published_at') : null;
        $data['expires_at']   = $request->filled('expires_at')   ? $request->input('expires_at')   : null;

        if ($request->hasFile('video_file')) {
            $data['video_path'] = $request->file('video_file')->store('ad_videos', 'public');
        }
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_path'] = $request->file('thumbnail')->store('ad_thumbs', 'public');
        }
        unset($data['video_file'], $data['thumbnail']);

        AdVideo::create($data);

        return redirect()->route('admin.ad-videos.index')
            ->with('success', 'Vidéo publicitaire créée.');
    }

    public function edit(AdVideo $adVideo)
    {
        return view('admin.ad_videos.form', ['video' => $adVideo]);
    }

    public function update(Request $request, AdVideo $adVideo)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:150',
            'description'  => 'nullable|string|max:1000',
            'app_target'   => 'required|in:pointage,securite,both',
            'video_url'    => 'nullable|url|max:500',
            'video_file'   => 'nullable|mimes:mp4,mov,webm|max:204800',
            'thumbnail'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active'    => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date|after_or_equal:published_at',
        ]);

        $data['is_active']    = $request->boolean('is_active');
        $data['published_at'] = $request->filled('published_at') ? $request->input('published_at') : null;
        $data['expires_at']   = $request->filled('expires_at')   ? $request->input('expires_at')   : null;

        if ($request->hasFile('video_file')) {
            if ($adVideo->video_path) Storage::disk('public')->delete($adVideo->video_path);
            $data['video_path'] = $request->file('video_file')->store('ad_videos', 'public');
        }
        if ($request->hasFile('thumbnail')) {
            if ($adVideo->thumbnail_path) Storage::disk('public')->delete($adVideo->thumbnail_path);
            $data['thumbnail_path'] = $request->file('thumbnail')->store('ad_thumbs', 'public');
        }
        unset($data['video_file'], $data['thumbnail']);

        $adVideo->update($data);

        return redirect()->route('admin.ad-videos.index')
            ->with('success', 'Vidéo publicitaire mise à jour.');
    }

    public function destroy(AdVideo $adVideo)
    {
        if ($adVideo->video_path)     Storage::disk('public')->delete($adVideo->video_path);
        if ($adVideo->thumbnail_path) Storage::disk('public')->delete($adVideo->thumbnail_path);
        $adVideo->delete();

        return back()->with('success', 'Vidéo supprimée.');
    }

    public function toggle(AdVideo $adVideo)
    {
        $adVideo->update(['is_active' => !$adVideo->is_active]);
        return back()->with('success', $adVideo->is_active ? 'Vidéo publiée.' : 'Vidéo désactivée.');
    }
}
