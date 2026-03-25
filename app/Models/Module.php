<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Tailwind color classes mapped by color name.
     * Used in views to render colored cards/badges.
     */
    public function getColorClassesAttribute(): array
    {
        $map = [
            'blue'   => ['bg' => 'bg-blue-600',   'light' => 'bg-blue-50',   'text' => 'text-blue-600',   'border' => 'border-blue-600',   'ring' => 'ring-blue-600'],
            'green'  => ['bg' => 'bg-green-600',  'light' => 'bg-green-50',  'text' => 'text-green-600',  'border' => 'border-green-600',  'ring' => 'ring-green-600'],
            'red'    => ['bg' => 'bg-red-600',    'light' => 'bg-red-50',    'text' => 'text-red-600',    'border' => 'border-red-600',    'ring' => 'ring-red-600'],
            'purple' => ['bg' => 'bg-purple-600', 'light' => 'bg-purple-50', 'text' => 'text-purple-600', 'border' => 'border-purple-600', 'ring' => 'ring-purple-600'],
            'orange' => ['bg' => 'bg-orange-600', 'light' => 'bg-orange-50', 'text' => 'text-orange-600', 'border' => 'border-orange-600', 'ring' => 'ring-orange-600'],
        ];

        return $map[$this->color] ?? $map['blue'];
    }
}
