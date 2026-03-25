<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    const FREE_PRICE = 0;

    protected $fillable = [
        'module_id',
        'name',
        'slug',
        'description',
        'max_workers',
        'price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'max_workers' => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price == 0) {
            return 'Gratuit';
        }
        return number_format($this->price, 0, ',', ' ') . ' FCFA';
    }

    public function isUnlimited(): bool
    {
        return $this->max_workers === -1;
    }

    public function getMaxWorkersLabelAttribute(): string
    {
        if ($this->isUnlimited()) {
            return 'Illimité';
        }
        return $this->max_workers . ' ouvriers';
    }
}
