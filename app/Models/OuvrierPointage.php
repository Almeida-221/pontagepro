<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OuvrierPointage extends Model
{
    protected $fillable = ['company_id', 'user_id', 'date', 'statut', 'initiated_by', 'note'];

    protected $casts = ['date' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
}
