<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecPointage extends Model
{
    protected $fillable = [
        'company_id', 'initiated_by', 'zone_id', 'poste_id', 'poste_ids',
        'tour', 'type', 'status', 'date', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'date'       => 'date',
            'expires_at' => 'datetime',
            'poste_ids'  => 'array',
        ];
    }

    public function company()    { return $this->belongsTo(Company::class); }
    public function initiator()  { return $this->belongsTo(User::class, 'initiated_by'); }
    public function zone()       { return $this->belongsTo(SecZone::class); }
    public function poste()      { return $this->belongsTo(SecPoste::class); }
    public function responses()  { return $this->hasMany(SecPointageResponse::class, 'pointage_id'); }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }
}
