<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ouvrier extends Model
{
    protected $fillable = [
        'company_id', 'name', 'phone', 'role', 'taux_journalier',
        'is_active', 'poste', 'created_by',
    ];

    protected $casts = [
        'taux_journalier' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function company()   { return $this->belongsTo(Company::class); }
    public function pointages() { return $this->hasMany(OuvrierPointage::class); }
    public function paiements() { return $this->hasMany(OuvrierPaiement::class); }

    /** Nombre de jours travaillés sur une période (present = 1, demi = 0.5) */
    public function joursTravailles(?\Carbon\Carbon $debut = null, ?\Carbon\Carbon $fin = null): float
    {
        $q = $this->pointages()->where('statut', '!=', 'absent');
        if ($debut) $q->where('date', '>=', $debut->toDateString());
        if ($fin)   $q->where('date', '<=', $fin->toDateString());
        $rows = $q->get(['statut']);
        return $rows->sum(fn($r) => $r->statut === 'demi' ? 0.5 : 1.0);
    }

    /** Montant gagné sur une période */
    public function montantGagne(?\Carbon\Carbon $debut = null, ?\Carbon\Carbon $fin = null): float
    {
        return $this->joursTravailles($debut, $fin) * (float) $this->taux_journalier;
    }

    /** Total déjà payé sur une période */
    public function totalPaye(?\Carbon\Carbon $debut = null, ?\Carbon\Carbon $fin = null): float
    {
        $q = $this->paiements();
        if ($debut) $q->where('date', '>=', $debut->toDateString());
        if ($fin)   $q->where('date', '<=', $fin->toDateString());
        return (float) $q->sum('montant');
    }

    /** Solde restant à payer (global, toutes périodes) */
    public function solde(): float
    {
        return $this->montantGagne() - $this->totalPaye();
    }
}
