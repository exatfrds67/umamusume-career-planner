<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'conversation_type',
        'conversation_title',
        'context_entities',
        'status',
        'message_count',
        'last_activity_at',
        'started_at',
        'ended_at',
        'ai_model',
        'ai_version',
        'ai_configuration',
        'system_prompt',
        'conversation_summary',
        'key_topics',
        'recommendations_made',
        'user_feedback',
        'user_satisfaction_rating',
        'helpful_responses',
        'unhelpful_responses',
        'quality_metrics',
        'contains_sensitive_data',
        'data_retention_policy',
        'user_consented_storage',
        'scheduled_deletion_at',
        'workflow_state',
        'action_items',
        'follow_up_tasks',
        'requires_human_review',
        'tags',
        'custom_metadata',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'context_entities' => 'array',
            'message_count' => 'integer',
            'last_activity_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'ai_configuration' => 'array',
            'system_prompt' => 'array',
            'conversation_summary' => 'array',
            'key_topics' => 'array',
            'recommendations_made' => 'array',
            'user_feedback' => 'array',
            'user_satisfaction_rating' => 'decimal:2',
            'helpful_responses' => 'integer',
            'unhelpful_responses' => 'integer',
            'quality_metrics' => 'array',
            'contains_sensitive_data' => 'boolean',
            'data_retention_policy' => 'array',
            'user_consented_storage' => 'boolean',
            'scheduled_deletion_at' => 'datetime',
            'workflow_state' => 'array',
            'action_items' => 'array',
            'follow_up_tasks' => 'array',
            'requires_human_review' => 'boolean',
            'tags' => 'array',
            'custom_metadata' => 'array',
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
     * Get the messages for this conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class, 'conversation_id');
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
