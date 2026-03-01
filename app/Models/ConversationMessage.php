<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $conversation_id
 * @property int|null $user_id
 * @property string $message_type
 * @property string|null $message_content
 * @property array<string, mixed>|null $message_metadata
 * @property string|null $agent_id
 * @property string|null $agent_type
 * @property string|null $agent_name
 * @property array<string, mixed>|null $agent_context
 * @property array<string, mixed>|null $tools_used
 * @property array<string, mixed>|null $tool_results
 * @property int|null $tool_call_count
 * @property int|null $parent_message_id
 * @property string|null $branch_id
 * @property int|null $branch_depth
 * @property bool $is_branch_point
 * @property array<string, mixed>|null $branch_metadata
 * @property string|null $ai_model_used
 * @property float|null $processing_time
 * @property int|null $tokens_used
 * @property float|null $cost_estimate
 * @property array<string, mixed>|null $model_parameters
 * @property int|null $quality_rating
 * @property bool|null $is_helpful
 * @property string|null $user_feedback
 * @property array<string, mixed>|null $quality_metrics
 * @property string|null $status
 * @property bool $is_visible
 * @property bool $is_pinned
 * @property bool $is_bookmarked
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $edited_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\ConversationMessageFactory>
 *
 * @mixin \Illuminate\Database\Eloquent\Builder<ConversationMessage>
 * @mixin \Illuminate\Database\Query\Builder
 */
class ConversationMessage extends Model
{
    /** @use HasFactory<\Database\Factories\ConversationMessageFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_conversation_messages';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'conversation_id',
        'user_id',
        'message_type',
        'message_content',
        'message_metadata',
        'agent_id',
        'agent_type',
        'agent_name',
        'agent_context',
        'tools_used',
        'tool_results',
        'tool_call_count',
        'parent_message_id',
        'branch_id',
        'branch_depth',
        'is_branch_point',
        'branch_metadata',
        'ai_model_used',
        'processing_time',
        'tokens_used',
        'cost_estimate',
        'model_parameters',
        'quality_rating',
        'is_helpful',
        'user_feedback',
        'quality_metrics',
        'status',
        'is_visible',
        'is_pinned',
        'is_bookmarked',
        'sent_at',
        'edited_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'conversation_id' => 'integer',
            'user_id' => 'integer',
            'message_metadata' => 'array',
            'agent_context' => 'array',
            'tools_used' => 'array',
            'tool_results' => 'array',
            'tool_call_count' => 'integer',
            'parent_message_id' => 'integer',
            'branch_depth' => 'integer',
            'is_branch_point' => 'boolean',
            'branch_metadata' => 'array',
            'processing_time' => 'float',
            'tokens_used' => 'integer',
            'cost_estimate' => 'decimal:6',
            'model_parameters' => 'array',
            'quality_rating' => 'integer',
            'is_helpful' => 'boolean',
            'quality_metrics' => 'array',
            'is_visible' => 'boolean',
            'is_pinned' => 'boolean',
            'is_bookmarked' => 'boolean',
            'sent_at' => 'datetime',
            'edited_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the conversation that owns the message.
     *
     * @return BelongsTo<AIConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AIConversation::class, 'conversation_id');
    }

    /**
     * Get the user that owns the message.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent message for branching.
     *
     * @return BelongsTo<ConversationMessage, $this>
     */
    public function parentMessage(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'parent_message_id');
    }

    /**
     * Get the child messages (branches).
     *
     * @return HasMany<ConversationMessage, $this>
     */
    public function childMessages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class, 'parent_message_id');
    }

    /**
     * Scope a query to only include user messages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<ConversationMessage>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ConversationMessage>
     */
    public function scopeUserMessages(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('message_type', 'user');
    }

    /**
     * Scope a query to only include AI messages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<ConversationMessage>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ConversationMessage>
     */
    public function scopeAiMessages(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('message_type', 'ai');
    }

    /**
     * Scope a query to only include messages from a specific agent.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<ConversationMessage>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ConversationMessage>
     */
    public function scopeFromAgent(\Illuminate\Database\Eloquent\Builder $query, string $agentId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope a query to only include branch points.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<ConversationMessage>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ConversationMessage>
     */
    public function scopeBranchPoints(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_branch_point', true);
    }

    /**
     * Scope a query to only include messages in a specific branch.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<ConversationMessage>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ConversationMessage>
     */
    public function scopeInBranch(\Illuminate\Database\Eloquent\Builder $query, string $branchId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope a query to only include visible messages.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<ConversationMessage>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ConversationMessage>
     */
    public function scopeVisible(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_visible', true);
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

    /**
     * Check if this message has branches.
     */
    public function hasBranches(): bool
    {
        return $this->is_branch_point && $this->childMessages()->count() > 0;
    }

    /**
     * Get all branches from this message.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ConversationMessage>
     */
    public function getBranches(): \Illuminate\Database\Eloquent\Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ConversationMessage> */
        return $this->childMessages()->where('is_branch_point', false)->get();
    }

    /**
     * Mark message as helpful.
     */
    public function markAsHelpful(): void
    {
        $this->is_helpful = true;
        $this->save();
    }

    /**
     * Mark message as unhelpful.
     */
    public function markAsUnhelpful(): void
    {
        $this->is_helpful = false;
        $this->save();
    }

    /**
     * Add user feedback.
     */
    public function addFeedback(string $feedback, ?int $rating = null): void
    {
        $this->user_feedback = $feedback;
        if ($rating !== null) {
            $this->quality_rating = $rating;
        }

        $this->save();
    }

    /**
     * Pin the message.
     */
    public function pin(): void
    {
        $this->is_pinned = true;
        $this->save();
    }

    /**
     * Unpin the message.
     */
    public function unpin(): void
    {
        $this->is_pinned = false;
        $this->save();
    }

    /**
     * Bookmark the message.
     */
    public function bookmark(): void
    {
        $this->is_bookmarked = true;
        $this->save();
    }

    /**
     * Remove bookmark.
     */
    public function unbookmark(): void
    {
        $this->is_bookmarked = false;
        $this->save();
    }
}
