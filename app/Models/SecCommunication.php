<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecCommunication extends Model
{
    protected $table = 'sec_communications';

    protected $fillable = [
        'company_id',
        'type',
        'title',
        'message',
        'audio_path',
        'poste_ids',
        'zone_ids',
        'tour_ids',
        'created_by',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'poste_ids'  => 'array',
        'zone_ids'   => 'array',
        'tour_ids'   => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Active = pas encore expirée */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}
