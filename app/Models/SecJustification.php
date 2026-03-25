<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecJustification extends Model
{
    protected $fillable = [
        'company_id', 'agent_id', 'motif', 'description',
        'date_absence', 'document_path', 'status',
        'reviewer_id', 'reviewer_comment', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_absence' => 'date',
            'reviewed_at'  => 'datetime',
        ];
    }

    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function reviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getMotifLabelAttribute(): string
    {
        return match($this->motif) {
            'maladie'  => 'Maladie',
            'voyage'   => 'Voyage',
            'mariage'  => 'Mariage',
            'bapteme'  => 'Baptême',
            'deces'    => 'Décès',
            'visite'   => 'Visite',
            default    => 'Autre',
        };
    }
}
