<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecRemplacement extends Model
{
    protected $table = 'sec_remplacements';

    protected $fillable = [
        'company_id',
        'agent_sortant_id',
        'agent_entrant_id',
        'poste_id',
        'zone_id',
        'date',
        'heure_entree',
        'heure_sortie',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function company()       { return $this->belongsTo(Company::class); }
    public function agentSortant()  { return $this->belongsTo(User::class, 'agent_sortant_id'); }
    public function agentEntrant()  { return $this->belongsTo(User::class, 'agent_entrant_id'); }
    public function poste()         { return $this->belongsTo(SecPoste::class); }
    public function zone()          { return $this->belongsTo(SecZone::class); }
}
