<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecRapportJournalier extends Model
{
    protected $table = 'sec_rapports_journaliers';

    protected $fillable = [
        'company_id', 'zone_id', 'date', 'generated_by',
        'total_agents', 'agents_presents', 'agents_absents',
        'anomalies', 'notes', 'validated_at', 'notified_at',
    ];

    protected $casts = [
        'date'         => 'date',
        'validated_at' => 'datetime',
        'notified_at'  => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(SecZone::class, 'zone_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getIsValidatedAttribute(): bool
    {
        return !is_null($this->validated_at);
    }
}
