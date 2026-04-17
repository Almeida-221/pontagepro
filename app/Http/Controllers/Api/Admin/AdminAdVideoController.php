<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdVideo;
use Illuminate\Http\Request;

class AdminAdVideoController extends Controller
{
    public function index()
    {
        $videos = AdVideo::latest()->get();
        return response()->json($videos->map(fn($v) => $this->format($v)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:150',
            'description'      => 'nullable|string',
            'app_target'       => 'required|in:pointage,securite,both',
            'video_url'        => 'nullable|url|max:500',
            'duration_seconds' => 'nullable|integer|min:1',
            'is_active'        => 'boolean',
            'published_at'     => 'nullable|date',
            'expires_at'       => 'nullable|date|after:published_at',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        $video = AdVideo::create($validated);
        return response()->json($this->format($video), 201);
    }

    public function update(Request $request, AdVideo $adVideo)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:150',
            'description'      => 'nullable|string',
            'app_target'       => 'required|in:pointage,securite,both',
            'video_url'        => 'nullable|url|max:500',
            'duration_seconds' => 'nullable|integer|min:1',
            'is_active'        => 'boolean',
            'published_at'     => 'nullable|date',
            'expires_at'       => 'nullable|date',
        ]);
        $adVideo->update($validated);
        return response()->json($this->format($adVideo));
    }

    public function toggle(AdVideo $adVideo)
    {
        $adVideo->update(['is_active' => !$adVideo->is_active]);
        return response()->json([
            'message'   => $adVideo->is_active ? 'Vidéo activée.' : 'Vidéo désactivée.',
            'is_active' => $adVideo->is_active,
        ]);
    }

    public function destroy(AdVideo $adVideo)
    {
        $adVideo->delete();
        return response()->json(['message' => 'Vidéo supprimée.']);
    }

    private function format(AdVideo $v): array
    {
        return [
            'id'               => $v->id,
            'title'            => $v->title,
            'description'      => $v->description,
            'app_target'       => $v->app_target,
            'video_url'        => $v->video_url,
            'video_file_url'   => $v->video_public_url,
            'thumbnail_url'    => $v->thumbnail_public_url,
            'duration_seconds' => $v->duration_seconds,
            'is_active'        => (bool) $v->is_active,
            'published_at'     => $v->published_at?->format('d/m/Y'),
            'expires_at'       => $v->expires_at?->format('d/m/Y'),
            'created_at'       => $v->created_at->format('d/m/Y'),
        ];
    }
}
