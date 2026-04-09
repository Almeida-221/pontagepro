<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AdVideo extends Model
{
    protected $fillable = [
        'title', 'description', 'app_target',
        'video_url', 'video_path', 'thumbnail_path',
        'duration_seconds', 'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'duration_seconds' => 'integer',
    ];

    /** URL publique du fichier vidéo (si uploadé). */
    public function getVideoPublicUrlAttribute(): ?string
    {
        return $this->video_path
            ? Storage::disk('public')->url($this->video_path)
            : null;
    }

    /** URL publique de la miniature. */
    public function getThumbnailPublicUrlAttribute(): ?string
    {
        return $this->thumbnail_path
            ? Storage::disk('public')->url($this->thumbnail_path)
            : null;
    }

    /** Scope : vidéos actives pour une cible donnée. */
    public function scopeActiveFor($query, string $target)
    {
        return $query->where('is_active', true)
                     ->where(fn ($q) => $q->where('app_target', $target)
                                          ->orWhere('app_target', 'both'));
    }

    /** Tableau retourné à l'API mobile. */
    public function toApiArray(): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'description'      => $this->description,
            'video_url'        => $this->video_url,
            'video_file_url'   => $this->video_public_url,
            'thumbnail_url'    => $this->thumbnail_public_url,
            'duration_seconds' => $this->duration_seconds,
        ];
    }
}
