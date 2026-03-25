<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SecPoste extends Model
{
    protected $table = 'sec_postes';

    protected $fillable = [
        'company_id', 'zone_id', 'name', 'address',
        'latitude', 'longitude', 'radius_meters', 'gps_confirmed', 'is_active',
    ];

    protected $casts = [
        'latitude'      => 'float',
        'longitude'     => 'float',
        'radius_meters' => 'integer',
        'gps_confirmed' => 'boolean',
        'is_active'     => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(SecZone::class, 'zone_id');
    }

    public function affectations(): HasMany
    {
        return $this->hasMany(SecAffectation::class, 'poste_id');
    }

    public function currentAffectation(): HasOne
    {
        return $this->hasOne(SecAffectation::class, 'poste_id')->where('is_active', true)->latest();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
