<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MCP Server Model
 *
 * Represents an MCP server for AI services and infrastructure management.
 *
 * @property int $id
 * @property string $server_name
 * @property string $server_type
 * @property string $server_version
 * @property array $server_config
 * @property array|null $connection_params
 * @property string|null $endpoint_url
 * @property array|null $authentication_config
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $last_health_check
 * @property array|null $health_status
 * @property int $consecutive_failures
 * @property array|null $supported_tools
 * @property array|null $supported_resources
 * @property array|null $server_capabilities
 * @property array|null $api_schema
 * @property int $total_requests
 * @property int $successful_requests
 * @property int $failed_requests
 * @property float|null $average_response_time
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property array|null $recent_errors
 * @property string|null $last_error_message
 * @property \Illuminate\Support\Carbon|null $last_error_at
 * @property array|null $debug_information
 * @property bool $auto_start
 * @property bool $auto_restart
 * @property int $max_restart_attempts
 * @property int $restart_count
 * @property array|null $data_sources
 * @property array|null $resource_usage
 * @property int|null $memory_usage_mb
 * @property float|null $cpu_usage_percent
 * @property array|null $dependent_servers
 * @property array|null $dependent_services
 * @property array|null $integration_points
 * @property array|null $access_permissions
 * @property array|null $security_settings
 * @property bool $requires_authentication
 * @property array|null $allowed_users
 * @property string|null $description
 * @property array|null $tags
 * @property array|null $custom_metadata
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class MCPServer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_mcp_servers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'server_name',
        'server_type',
        'server_version',
        'server_config',
        'connection_params',
        'endpoint_url',
        'authentication_config',
        'status',
        'last_health_check',
        'health_status',
        'consecutive_failures',
        'supported_tools',
        'supported_resources',
        'server_capabilities',
        'api_schema',
        'total_requests',
        'successful_requests',
        'failed_requests',
        'average_response_time',
        'last_used_at',
        'recent_errors',
        'last_error_message',
        'last_error_at',
        'debug_information',
        'auto_start',
        'auto_restart',
        'max_restart_attempts',
        'restart_count',
        'data_sources',
        'resource_usage',
        'memory_usage_mb',
        'cpu_usage_percent',
        'dependent_servers',
        'dependent_services',
        'integration_points',
        'access_permissions',
        'security_settings',
        'requires_authentication',
        'allowed_users',
        'description',
        'tags',
        'custom_metadata',
        'notes',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'inactive',
        'consecutive_failures' => 0,
        'total_requests' => 0,
        'successful_requests' => 0,
        'failed_requests' => 0,
        'auto_start' => true,
        'auto_restart' => true,
        'max_restart_attempts' => 3,
        'restart_count' => 0,
        'requires_authentication' => false,
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'server_config' => 'array',
            'connection_params' => 'array',
            'authentication_config' => 'array',
            'last_health_check' => 'datetime',
            'health_status' => 'array',
            'consecutive_failures' => 'integer',
            'supported_tools' => 'array',
            'supported_resources' => 'array',
            'server_capabilities' => 'array',
            'api_schema' => 'array',
            'total_requests' => 'integer',
            'successful_requests' => 'integer',
            'failed_requests' => 'integer',
            'average_response_time' => 'float',
            'last_used_at' => 'datetime',
            'recent_errors' => 'array',
            'last_error_at' => 'datetime',
            'debug_information' => 'array',
            'auto_start' => 'boolean',
            'auto_restart' => 'boolean',
            'max_restart_attempts' => 'integer',
            'restart_count' => 'integer',
            'data_sources' => 'array',
            'resource_usage' => 'array',
            'memory_usage_mb' => 'integer',
            'cpu_usage_percent' => 'float',
            'dependent_servers' => 'array',
            'dependent_services' => 'array',
            'integration_points' => 'array',
            'access_permissions' => 'array',
            'security_settings' => 'array',
            'requires_authentication' => 'boolean',
            'allowed_users' => 'array',
            'tags' => 'array',
            'custom_metadata' => 'array',
        ];
    }

    /**
     * Get the agents using this server.
     */
    public function agents(): HasMany
    {
        return $this->hasMany(MCPAgent::class, 'server_name', 'server_name');
    }

    /**
     * Check if server is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if server is healthy.
     */
    public function isHealthy(): bool
    {
        return $this->isActive() && $this->consecutive_failures === 0;
    }

    /**
     * Check if server needs restart.
     */
    public function needsRestart(): bool
    {
        return $this->status === 'error' &&
            $this->auto_restart &&
            $this->restart_count < $this->max_restart_attempts;
    }

    /**
     * Get success rate percentage.
     *
     * @return Attribute<float, never>
     */
    protected function successRate(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if ($attributes['total_requests'] === 0) {
                    return 0.0;
                }

                return round(($attributes['successful_requests'] / $attributes['total_requests']) * 100, 2);
            }
        );
    }

    /**
     * Get failure rate percentage.
     *
     * @return Attribute<float, never>
     */
    protected function failureRate(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if ($attributes['total_requests'] === 0) {
                    return 0.0;
                }

                return round(($attributes['failed_requests'] / $attributes['total_requests']) * 100, 2);
            }
        );
    }

    /**
     * Get uptime percentage (based on health checks).
     *
     * @return Attribute<float, never>
     */
    protected function uptimePercentage(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                // health_status is cast to array, so we can access it directly if model is hydrated,
                // but $attributes['health_status'] might be a JSON string or null.
                // Safest to rely on the model instance properties if accessing cast attributes,
                // but inside Attribute::make, $attributes is usually raw.
                // However, accessing $this inside Closure works in PHP 5.4+ if bound, but Attribute::make closures are static-like usually?
                // Actually, accessing $this->health_status is safer for cast attributes.
                $healthStatus = $this->health_status;

                if (! $healthStatus || ! isset($healthStatus['uptime_checks'])) {
                    return 0.0;
                }

                $checks = $healthStatus['uptime_checks'];
                $successful = $healthStatus['successful_checks'] ?? 0;

                if ($checks === 0) {
                    return 0.0;
                }

                return round(($successful / $checks) * 100, 2);
            }
        );
    }

    /**
     * Record a successful request.
     */
    public function recordSuccess(float $responseTime): void
    {
        $this->increment('total_requests', 1);
        $this->increment('successful_requests', 1);
        $this->consecutive_failures = 0;
        $this->last_used_at = now();

        // Update average response time
        $this->updateAverageResponseTime($responseTime);

        $this->save();
    }

    /**
     * Record a failed request.
     */
    public function recordFailure(string $errorMessage): void
    {
        $this->increment('total_requests', 1);
        $this->increment('failed_requests', 1);
        $this->increment('consecutive_failures', 1);
        $this->last_error_message = $errorMessage;
        $this->last_error_at = now();

        // Add to recent errors
        $recentErrors = $this->recent_errors ?? [];
        array_unshift($recentErrors, [
            'message' => $errorMessage,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Keep only last 10 errors
        $this->recent_errors = array_slice($recentErrors, 0, 10);

        // Update status if too many failures
        if ($this->consecutive_failures >= 3) {
            $this->status = 'error';
        }

        $this->save();
    }

    /**
     * Update average response time.
     */
    protected function updateAverageResponseTime(float $newTime): void
    {
        if ($this->average_response_time === null) {
            $this->average_response_time = $newTime;
        } else {
            // Exponential moving average
            $this->average_response_time = ($this->average_response_time * 0.9) + ($newTime * 0.1);
        }
    }

    /**
     * Update health status.
     */
    public function updateHealthStatus(array $healthData): void
    {
        $this->health_status = $healthData;
        $this->last_health_check = now();

        if ($healthData['status'] === 'healthy') {
            $this->consecutive_failures = 0;
            if ($this->status === 'error') {
                $this->status = 'active';
            }
        }

        $this->save();
    }

    /**
     * Attempt to restart the server.
     */
    public function attemptRestart(): bool
    {
        if (! $this->needsRestart()) {
            return false;
        }

        $this->increment('restart_count', 1);
        $this->status = 'inactive';
        $this->save();

        return true;
    }

    /**
     * Reset restart count.
     */
    public function resetRestartCount(): void
    {
        $this->restart_count = 0;
        $this->save();
    }

    /**
     * Scope a query to only include active servers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive servers.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope a query to only include servers with errors.
     */
    public function scopeWithErrors($query)
    {
        return $query->where('status', 'error');
    }

    /**
     * Scope a query to only include servers by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('server_type', $type);
    }

    /**
     * Scope a query to only include servers that auto-start.
     */
    public function scopeAutoStart($query)
    {
        return $query->where('auto_start', true);
    }
}
