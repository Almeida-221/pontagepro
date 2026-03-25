<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'company_id', 'worker_id', 'date',
        'entry_time', 'exit_time', 'daily_rate', 'amount_earned', 'is_paid',
    ];

    protected $casts = [
        'date'         => 'date',
        'entry_time'   => 'datetime',
        'exit_time'    => 'datetime',
        'daily_rate'   => 'float',
        'amount_earned'=> 'float',
        'is_paid'      => 'boolean',
    ];

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function toApiArray(): array
    {
        return [
            'id'            => $this->id,
            'worker_id'     => $this->worker_id,
            'worker_name'   => $this->worker?->name ?? '',
            'company_id'    => $this->company_id,
            'date'          => $this->date->toDateString(),
            'entry_time'    => $this->entry_time?->toIso8601String(),
            'exit_time'     => $this->exit_time?->toIso8601String(),
            'daily_rate'    => $this->daily_rate !== null ? (float) $this->daily_rate : null,
            'amount_earned' => $this->amount_earned !== null ? (float) $this->amount_earned : null,
            'is_paid'       => $this->is_paid,
        ];
    }
}
