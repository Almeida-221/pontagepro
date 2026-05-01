<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Module;

class Company extends Model
{
    protected $fillable = [
        'name',
        'address',
        'owner_first_name',
        'owner_last_name',
        'owner_email',
        'owner_phone',
        'owner_address',
        'status',
        'owner_user_id',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /** Propriétaire web (role=client) de cette entreprise */
    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** Module associé via l'abonnement actif */
    public function getModuleAttribute(): ?Module
    {
        return $this->active_subscription?->plan?->module;
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getFullOwnerNameAttribute(): string
    {
        return $this->owner_first_name . ' ' . $this->owner_last_name;
    }

    public function getActiveSubscriptionAttribute(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('end_date', '>', now()->toDateString())
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('trial_ends_at')
                         ->where('trial_ends_at', '>=', now()->toDateString());
                  });
            })
            ->latest()
            ->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
