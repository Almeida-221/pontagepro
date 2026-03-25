<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecPointageResponse extends Model
{
    protected $fillable = [
        'pointage_id', 'agent_id', 'zone_id', 'poste_id',
        'status', 'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function pointage() { return $this->belongsTo(SecPointage::class); }
    public function agent()    { return $this->belongsTo(User::class, 'agent_id'); }
    public function zone()     { return $this->belongsTo(SecZone::class); }
    public function poste()    { return $this->belongsTo(SecPoste::class); }
}
