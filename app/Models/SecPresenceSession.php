<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecPresenceSession extends Model
{
    protected $table = 'sec_presence_sessions';

    protected $fillable = [
        'company_id', 'zone_id', 'launched_by',
        'launched_at', 'deadline_at', 'status',
    ];

    protected $casts = [
        'launched_at' => 'datetime',
        'deadline_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(SecZone::class, 'zone_id');
    }

    public function launchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'launched_by');
    }

    public function confirmations(): HasMany
    {
        return $this->hasMany(SecPresenceConfirmation::class, 'session_id');
    }

    public function getIsExpiredAttribute(): bool
    {
        return now()->isAfter($this->deadline_at);
    }

    public function getMinutesRemainingAttribute(): int
    {
        return max(0, (int) now()->diffInMinutes($this->deadline_at, false));
    }
}
