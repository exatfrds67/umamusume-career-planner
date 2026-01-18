<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIConversation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_ai_conversations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'character_id',
        'conversation_id',
        'message_type',
        'message_content',
        'ai_model_used',
        'processing_time',
        'tokens_used',
        'cost_estimate',
        'metadata',
        'quality_rating',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'processing_time' => 'float',
            'tokens_used' => 'integer',
            'cost_estimate' => 'decimal:6',
            'metadata' => 'array',
            'quality_rating' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the character associated with the conversation.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Scope a query to only include user messages.
     */
    public function scopeUserMessages($query)
    {
        return $query->where('message_type', 'user');
    }

    /**
     * Scope a query to only include AI messages.
     */
    public function scopeAiMessages($query)
    {
        return $query->where('message_type', 'ai');
    }

    /**
     * Scope a query to filter by conversation ID.
     */
    public function scopeConversation($query, string $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    /**
     * Get the total cost for this conversation.
     */
    public function getTotalCostAttribute(): float
    {
        return (float) $this->cost_estimate;
    }

    /**
     * Check if this is a user message.
     */
    public function isUserMessage(): bool
    {
        return $this->message_type === 'user';
    }

    /**
     * Check if this is an AI message.
     */
    public function isAiMessage(): bool
    {
        return $this->message_type === 'ai';
    }
}
