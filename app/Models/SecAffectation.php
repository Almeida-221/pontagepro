<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecAffectation extends Model
{
    protected $table = 'sec_affectations';

    protected $fillable = [
        'agent_id', 'poste_id', 'assigned_by',
        'started_at', 'ended_at', 'is_active',
        'validated_at', 'validation_latitude', 'validation_longitude',
        'rest_days', 'off_days', 'tours',
    ];

    protected $casts = [
        'started_at'           => 'datetime',
        'ended_at'             => 'datetime',
        'validated_at'         => 'datetime',
        'is_active'            => 'boolean',
        'validation_latitude'  => 'float',
        'validation_longitude' => 'float',
        'rest_days'            => 'array',
        'off_days'             => 'array',
        'tours'                => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function poste(): BelongsTo
    {
        return $this->belongsTo(SecPoste::class, 'poste_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getIsValidatedAttribute(): bool
    {
        return !is_null($this->validated_at);
    }
}
