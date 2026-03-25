<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecNotification extends Model
{
    protected $table = 'sec_notifications';

    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'data', 'is_read', 'read_at',
    ];

    protected $casts = [
        'data'     => 'array',
        'is_read'  => 'boolean',
        'read_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Helper: create a notification for a user.
     */
    public static function notifier(int $userId, string $type, string $title, string $message, array $data = []): self
    {
        return self::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'data'    => $data,
            'is_read' => false,
        ]);
    }
}
