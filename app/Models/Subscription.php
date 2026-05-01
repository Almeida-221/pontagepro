<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'start_date',
        'end_date',
        'trial_ends_at',
        'status',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'trial_ends_at' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date->isPast();
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->is_expired) {
            return 0;
        }
        return (int) now()->diffInDays($this->end_date, false);
    }

    public function getIsInTrialAttribute(): bool
    {
        return $this->trial_ends_at !== null && !$this->trial_ends_at->isPast();
    }

    public function getTrialDaysRemainingAttribute(): int
    {
        if (!$this->is_in_trial) {
            return 0;
        }
        return (int) now()->diffInDays($this->trial_ends_at, false);
    }

    public function isFree(): bool
    {
        return $this->plan && $this->plan->price == 0;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'Actif',
            'pending'   => 'En attente de paiement',
            'suspended' => 'Suspendu',
            'expired'   => 'Expiré',
            'cancelled' => 'Annulé',
            default     => $this->status,
        };
    }
}
