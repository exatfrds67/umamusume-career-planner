<?php

declare(strict_types=1);

namespace App\Models;

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
 * @property array|null $tool_parameters
 * @property array|null $tool_result
 * @property string $execution_status
 * @property string|null $error_message
 * @property float $execution_time
 * @property int|null $tokens_used
 * @property float|null $cost_estimate
 * @property string|null $cost_model
 * @property string|null $request_id
 * @property array|null $context_data
 * @property array|null $performance_metrics
 * @property array|null $custom_metadata
 * @property int|null $quality_rating
 * @property bool|null $was_helpful
 * @property string|null $user_feedback
 * @property \Illuminate\Support\Carbon $executed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class MCPToolUsage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_mcp_tool_usage';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the agent that executed the tool.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(MCPAgent::class);
    }

    /**
     * Get the conversation associated with the tool usage.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AIConversation::class, 'conversation_id');
    }

    /**
     * Get the message associated with the tool usage.
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
            get: fn (mixed $value, array $attributes) => (int) round(($attributes['cost_estimate'] ?? 0) * 100)
        );
    }

    /**
     * Scope a query to only include successful executions.
     */
    public function scopeSuccessful(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('execution_status', 'success');
    }

    /**
     * Scope a query to only include failed executions.
     */
    public function scopeFailed(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('execution_status', 'failure');
    }

    /**
     * Scope a query to filter by server name.
     */
    public function scopeForServer($query, string $serverName)
    {
        return $query->where('server_name', $serverName);
    }

    /**
     * Scope a query to filter by tool name.
     */
    public function scopeForTool($query, string $toolName)
    {
        return $query->where('tool_name', $toolName);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('executed_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by agent.
     */
    public function scopeForAgent($query, int $agentId)
    {
        return $query->where('agent_id', $agentId);
    }
}
