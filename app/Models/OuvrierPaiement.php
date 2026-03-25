<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OuvrierPaiement extends Model
{
    protected $fillable = ['company_id', 'user_id', 'montant', 'date', 'note', 'created_by'];

    protected $casts = ['date' => 'date', 'montant' => 'decimal:2'];

    public function user() { return $this->belongsTo(User::class); }
}
