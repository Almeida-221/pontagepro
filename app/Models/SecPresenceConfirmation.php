<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecPresenceConfirmation extends Model
{
    protected $table = 'sec_presence_confirmations';

    protected $fillable = [
        'session_id', 'agent_id', 'poste_id',
        'status', 'confirmed_at',
        'latitude', 'longitude', 'is_on_post', 'distance_meters',
    ];

    protected $casts = [
        'confirmed_at'   => 'datetime',
        'latitude'       => 'float',
        'longitude'      => 'float',
        'is_on_post'     => 'boolean',
        'distance_meters'=> 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SecPresenceSession::class, 'session_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function poste(): BelongsTo
    {
        return $this->belongsTo(SecPoste::class, 'poste_id');
    }
}
