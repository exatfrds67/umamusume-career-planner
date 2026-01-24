<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Chat Message model for Neuron AI chat history.
 *
 * Stores conversation messages for AI agents using the EloquentChatHistory component.
 *
 * @property int $id
 * @property string $thread_id Thread identifier for grouping related messages
 * @property string $role Message role (user, assistant, system)
 * @property array<string, mixed>|null $content Message content (can be text or structured data)
 * @property array<string, mixed>|null $meta Additional metadata for the message
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ChatMessage extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'chat_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'thread_id',
        'role',
        'content',
        'meta',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'meta' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
