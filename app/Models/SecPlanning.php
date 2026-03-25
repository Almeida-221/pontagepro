<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecPlanning extends Model
{
    protected $table = 'sec_plannings';

    protected $fillable = ['agent_id', 'off_days', 'rest_days', 'tours'];

    protected $casts = [
        'off_days'  => 'array',
        'rest_days' => 'array',
        'tours'     => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /** Check if a given day-of-month (1-31) is a vacation day. */
    public function isOffDay(int $day): bool
    {
        return in_array($day, $this->off_days ?? []);
    }

    /** Check if a given weekday (1=Lun … 7=Dim) is a rest day. */
    public function isRestDay(int $weekday): bool
    {
        return in_array($weekday, $this->rest_days ?? []);
    }
}
