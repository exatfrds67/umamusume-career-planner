<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConversationMessage extends Model
{
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
     */
    protected function casts(): array
    {
        return [
            'message_metadata' => 'array',
            'agent_context' => 'array',
            'tools_used' => 'array',
            'tool_results' => 'array',
            'tool_call_count' => 'integer',
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
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AIConversation::class, 'conversation_id');
    }

    /**
     * Get the user that owns the message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent message for branching.
     */
    public function parentMessage(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'parent_message_id');
    }

    /**
     * Get the child messages (branches).
     */
    public function childMessages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class, 'parent_message_id');
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
     * Scope a query to only include messages from a specific agent.
     */
    public function scopeFromAgent($query, string $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope a query to only include branch points.
     */
    public function scopeBranchPoints($query)
    {
        return $query->where('is_branch_point', true);
    }

    /**
     * Scope a query to only include messages in a specific branch.
     */
    public function scopeInBranch($query, string $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope a query to only include visible messages.
     */
    public function scopeVisible($query)
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
     */
    public function getBranches()
    {
        return $this->childMessages()->where('is_branch_point', false)->get();
    }

    /**
     * Mark message as helpful.
     */
    public function markAsHelpful(): void
    {
        $this->update(['is_helpful' => true]);
    }

    /**
     * Mark message as unhelpful.
     */
    public function markAsUnhelpful(): void
    {
        $this->update(['is_helpful' => false]);
    }

    /**
     * Add user feedback.
     */
    public function addFeedback(string $feedback, ?int $rating = null): void
    {
        $updates = ['user_feedback' => $feedback];

        if ($rating !== null) {
            $updates['quality_rating'] = $rating;
        }

        $this->update($updates);
    }

    /**
     * Pin the message.
     */
    public function pin(): void
    {
        $this->update(['is_pinned' => true]);
    }

    /**
     * Unpin the message.
     */
    public function unpin(): void
    {
        $this->update(['is_pinned' => false]);
    }

    /**
     * Bookmark the message.
     */
    public function bookmark(): void
    {
        $this->update(['is_bookmarked' => true]);
    }

    /**
     * Remove bookmark.
     */
    public function unbookmark(): void
    {
        $this->update(['is_bookmarked' => false]);
    }
}
