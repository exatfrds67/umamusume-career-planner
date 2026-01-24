<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MCP Tool Usage Model
 *
 * Tracks MCP tool execution for cost tracking and performance analysis.
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $agent_id
 * @property int|null $conversation_id
 * @property int|null $message_id
 * @property string $server_name
 * @property string $tool_name
 * @property string|null $tool_category
 * @property array<string, mixed>|null $tool_parameters
 * @property array<string, mixed>|null $tool_result
 * @property string $execution_status
 * @property string|null $error_message
 * @property float $execution_time
 * @property int|null $tokens_used
 * @property float|null $cost_estimate
 * @property string|null $cost_model
 * @property string|null $request_id
 * @property array<string, mixed>|null $context_data
 * @property array<string, mixed>|null $performance_metrics
 * @property array<string, mixed>|null $custom_metadata
 * @property int|null $quality_rating
 * @property bool|null $was_helpful
 * @property string|null $user_feedback
 * @property \Illuminate\Support\Carbon $executed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\MCPToolUsageFactory>
 */
class MCPToolUsage extends Model
{
    /** @use HasFactory<\Database\Factories\MCPToolUsageFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_mcp_tool_usage';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'agent_id',
        'conversation_id',
        'message_id',
        'server_name',
        'tool_name',
        'tool_category',
        'tool_parameters',
        'tool_result',
        'execution_status',
        'error_message',
        'execution_time',
        'tokens_used',
        'cost_estimate',
        'cost_model',
        'request_id',
        'context_data',
        'performance_metrics',
        'custom_metadata',
        'quality_rating',
        'was_helpful',
        'user_feedback',
        'executed_at',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'execution_status' => 'success',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tool_parameters' => 'array',
            'tool_result' => 'array',
            'execution_time' => 'float',
            'tokens_used' => 'integer',
            'cost_estimate' => 'float',
            'context_data' => 'array',
            'performance_metrics' => 'array',
            'custom_metadata' => 'array',
            'quality_rating' => 'integer',
            'was_helpful' => 'boolean',
            'executed_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the tool usage.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the agent that executed the tool.
     *
     * @return BelongsTo<MCPAgent, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(MCPAgent::class);
    }

    /**
     * Get the conversation associated with the tool usage.
     *
     * @return BelongsTo<AIConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AIConversation::class, 'conversation_id');
    }

    /**
     * Get the message associated with the tool usage.
     *
     * @return BelongsTo<ConversationMessage, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'message_id');
    }

    /**
     * Check if execution was successful.
     */
    public function wasSuccessful(): bool
    {
        return $this->execution_status === 'success';
    }

    /**
     * Check if execution failed.
     */
    public function hasFailed(): bool
    {
        return $this->execution_status === 'failure';
    }

    /**
     * Get cost in cents.
     *
     * @return Attribute<int, never>
     */
    protected function costInCents(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $rawCost = $attributes['cost_estimate'] ?? null;
                $cost = is_numeric($rawCost) ? (float) $rawCost : 0.0;

                return (int) round($cost * 100);
            }
        );
    }

    /**
     * Scope a query to only include successful executions.
     *
     * @param  Builder<MCPToolUsage>  $query
     * @return Builder<MCPToolUsage>
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('execution_status', 'success');
    }

    /**
     * Scope a query to only include failed executions.
     *
     * @param  Builder<MCPToolUsage>  $query
     * @return Builder<MCPToolUsage>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('execution_status', 'failure');
    }

    /**
     * Scope a query to filter by server name.
     *
     * @param  Builder<MCPToolUsage>  $query
     * @return Builder<MCPToolUsage>
     */
    public function scopeForServer(Builder $query, string $serverName): Builder
    {
        return $query->where('server_name', $serverName);
    }

    /**
     * Scope a query to filter by tool name.
     *
     * @param  Builder<MCPToolUsage>  $query
     * @return Builder<MCPToolUsage>
     */
    public function scopeForTool(Builder $query, string $toolName): Builder
    {
        return $query->where('tool_name', $toolName);
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param  Builder<MCPToolUsage>  $query
     * @return Builder<MCPToolUsage>
     */
    public function scopeBetweenDates(Builder $query, \Illuminate\Support\Carbon $startDate, \Illuminate\Support\Carbon $endDate): Builder
    {
        return $query->whereBetween('executed_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by user.
     *
     * @param  Builder<MCPToolUsage>  $query
     * @return Builder<MCPToolUsage>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by agent.
     *
     * @param  Builder<MCPToolUsage>  $query
     * @return Builder<MCPToolUsage>
     */
    public function scopeForAgent(Builder $query, int $agentId): Builder
    {
        return $query->where('agent_id', $agentId);
    }
}
