<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $character_id
 * @property string $conversation_id
 * @property string $conversation_type
 * @property string|null $conversation_title
 * @property array<string, mixed>|null $context_entities
 * @property string $status
 * @property int $message_count
 * @property \Illuminate\Support\Carbon|null $last_activity_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property string|null $ai_model
 * @property string|null $ai_version
 * @property array<string, mixed>|null $ai_configuration
 * @property array<string, mixed>|null $system_prompt
 * @property array<string, mixed>|null $conversation_summary
 * @property array<string, mixed>|null $key_topics
 * @property array<string, mixed>|null $recommendations_made
 * @property array<string, mixed>|null $user_feedback
 * @property float|null $user_satisfaction_rating
 * @property int|null $helpful_responses
 * @property int|null $unhelpful_responses
 * @property array<string, mixed>|null $quality_metrics
 * @property bool $contains_sensitive_data
 * @property array<string, mixed>|null $data_retention_policy
 * @property bool $user_consented_storage
 * @property \Illuminate\Support\Carbon|null $scheduled_deletion_at
 * @property array<string, mixed>|null $workflow_state
 * @property array<string, mixed>|null $action_items
 * @property array<string, mixed>|null $follow_up_tasks
 * @property bool $requires_human_review
 * @property array<string, mixed>|null $tags
 * @property array<string, mixed>|null $custom_metadata
 * @property string|null $notes
 * @property string|null $message_type
 * @property float|null $cost_estimate
 * @property string|null $ai_model_used
 * @property float|null $processing_time
 * @property float|null $cost
 * @property int|null $token_count
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\AIConversationFactory>
 */
class AIConversation extends Model
{
    /** @use HasFactory<\Database\Factories\AIConversationFactory> */
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
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'character_id' => 'integer',
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
            'cost_estimate' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the conversation.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the character associated with the conversation.
     *
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the messages for this conversation.
     *
     * @return HasMany<ConversationMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class, 'conversation_id');
    }

    /**
     * Scope a query to only include user messages.
     */
    /**
     * @param  Builder<AIConversation>  $query
     * @return Builder<AIConversation>
     */
    public function scopeUserMessages(Builder $query): Builder
    {
        return $query->where('message_type', 'user');
    }

    /**
     * Scope a query to only include AI messages.
     */
    /**
     * @param  Builder<AIConversation>  $query
     * @return Builder<AIConversation>
     */
    public function scopeAiMessages(Builder $query): Builder
    {
        return $query->where('message_type', 'ai');
    }

    /**
     * Scope a query to filter by conversation ID.
     */
    /**
     * @param  Builder<AIConversation>  $query
     * @return Builder<AIConversation>
     */
    public function scopeConversation(Builder $query, string $conversationId): Builder
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
