<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MCP Agent Model
 *
 * Represents an MCP agent deployed via AgentCore for advanced
 * agent orchestration and management.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $agent_id
 * @property string $name
 * @property string $type
 * @property string $model
 * @property string $instructions
 * @property array<string, mixed> $tools
 * @property array<string, mixed> $memory_config
 * @property array<string, mixed> $guardrails
 * @property array<string, mixed> $metadata
 * @property string $status
 * @property string $health_status
 * @property float $deployment_time
 * @property array<string, mixed>|null $performance_metrics
 * @property \Illuminate\Support\Carbon|null $last_health_check
 * @property \Illuminate\Support\Carbon|null $terminated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\MCPAgentFactory>
 */
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class MCPAgent extends Model
{
    /** @use HasFactory<\Database\Factories\MCPAgentFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_mcp_agents';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'agent_id',
        'name',
        'type',
        'model',
        'instructions',
        'tools',
        'memory_config',
        'guardrails',
        'metadata',
        'status',
        'health_status',
        'deployment_time',
        'performance_metrics',
        'last_health_check',
        'terminated_at',
        // Old fields for backward compatibility
        'agent_name',
        'agent_type',
        'agent_config',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'agent_config' => '{}',
        'status' => 'active',
        'health_status' => 'healthy',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tools' => 'array',
            'memory_config' => 'array',
            'guardrails' => 'array',
            'metadata' => 'array',
            'performance_metrics' => 'array',
            'deployment_time' => 'float',
            'last_health_check' => 'datetime',
            'terminated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the agent.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if agent is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if agent is healthy.
     */
    public function isHealthy(): bool
    {
        return $this->health_status === 'healthy';
    }

    /**
     * Get agent uptime in seconds.
     *
     * @return Attribute<int, never>
     */
    protected function uptimeSeconds(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                // Access dates from $this if model is hydrated to ensure Carbon casting
                $createdAt = $this->created_at ? $this->asDateTime($this->created_at) : null;
                $terminatedAt = $this->terminated_at ? $this->asDateTime($this->terminated_at) : null;

                if (! $createdAt) {
                    return 0;
                }

                if ($terminatedAt) {
                    return $terminatedAt->diffInSeconds($createdAt);
                }

                return now()->diffInSeconds($createdAt);
            }
        );
    }

    public function getUptimeSeconds(): int
    {
        return (int) $this->uptime_seconds;
    }

    /**
     * Scope a query to only include active agents.
     *
     * @param  Builder<MCPAgent>  $query
     * @return Builder<MCPAgent>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include terminated agents.
     *
     * @param  Builder<MCPAgent>  $query
     * @return Builder<MCPAgent>
     */
    public function scopeTerminated(Builder $query): Builder
    {
        return $query->where('status', 'terminated');
    }

    /**
     * Scope a query to only include healthy agents.
     *
     * @param  Builder<MCPAgent>  $query
     * @return Builder<MCPAgent>
     */
    public function scopeHealthy(Builder $query): Builder
    {
        return $query->where('health_status', 'healthy');
    }
}
