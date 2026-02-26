<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
        'notification_preferences',
        'last_notified_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notification_preferences' => 'array',
            'last_notified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, PushSubscription>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
