<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerCategory extends Model
{
    protected $fillable = ['profession_id', 'name', 'daily_rate'];

    protected function casts(): array
    {
        return ['daily_rate' => 'float'];
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class);
    }
}
