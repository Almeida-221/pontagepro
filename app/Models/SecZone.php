<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecZone extends Model
{
    protected $table = 'sec_zones';

    protected $fillable = ['company_id', 'name', 'description', 'responsable_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function postes(): HasMany
    {
        return $this->hasMany(SecPoste::class, 'zone_id');
    }

    public function agents(): HasMany
    {
        return $this->hasMany(User::class, 'zone_id');
    }

    public function presenceSessions(): HasMany
    {
        return $this->hasMany(SecPresenceSession::class, 'zone_id');
    }

    public function rapports(): HasMany
    {
        return $this->hasMany(SecRapportJournalier::class, 'zone_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
