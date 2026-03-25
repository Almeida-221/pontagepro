<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecTour extends Model
{
    protected $table    = 'sec_tours';
    protected $fillable = ['company_id', 'nom', 'emoji', 'heure_debut', 'heure_fin', 'ordre'];

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
