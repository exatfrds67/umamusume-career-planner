<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
 * @property array<string, mixed> $server_config
 * @property array<string, mixed>|null $connection_params
 * @property string|null $endpoint_url
 * @property array<string, mixed>|null $authentication_config
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $last_health_check
 * @property array<string, mixed>|null $health_status
 * @property int $consecutive_failures
 * @property array<string, mixed>|null $supported_tools
 * @property array<string, mixed>|null $supported_resources
 * @property array<string, mixed>|null $server_capabilities
 * @property array<string, mixed>|null $api_schema
 * @property int $total_requests
 * @property int $successful_requests
 * @property int $failed_requests
 * @property float|null $average_response_time
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property array<int|string, mixed>|null $recent_errors
 * @property string|null $last_error_message
 * @property \Illuminate\Support\Carbon|null $last_error_at
 * @property array<string, mixed>|null $debug_information
 * @property bool $auto_start
 * @property bool $auto_restart
 * @property int $max_restart_attempts
 * @property int $restart_count
 * @property array<string, mixed>|null $data_sources
 * @property array<string, mixed>|null $resource_usage
 * @property int|null $memory_usage_mb
 * @property float|null $cpu_usage_percent
 * @property array<string, mixed>|null $dependent_servers
 * @property array<string, mixed>|null $dependent_services
 * @property array<string, mixed>|null $integration_points
 * @property array<string, mixed>|null $access_permissions
 * @property array<string, mixed>|null $security_settings
 * @property bool $requires_authentication
 * @property array<string, mixed>|null $allowed_users
 * @property string|null $description
 * @property array<string, mixed>|null $tags
 * @property array<string, mixed>|null $custom_metadata
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\MCPServerFactory>
 */
class MCPServer extends Model
{
    /** @use HasFactory<\Database\Factories\MCPServerFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_mcp_servers';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
     *
     * @return HasMany<MCPAgent, $this>
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
                $totalRequests = 0;
                $successfulRequests = 0;

                $rawTotalRequests = $attributes['total_requests'] ?? null;
                $rawSuccessfulRequests = $attributes['successful_requests'] ?? null;

                if (is_int($rawTotalRequests) || is_float($rawTotalRequests) || (is_string($rawTotalRequests) && is_numeric($rawTotalRequests))) {
                    $totalRequests = (int) $rawTotalRequests;
                }

                if (is_int($rawSuccessfulRequests) || is_float($rawSuccessfulRequests) || (is_string($rawSuccessfulRequests) && is_numeric($rawSuccessfulRequests))) {
                    $successfulRequests = (int) $rawSuccessfulRequests;
                }

                if ($totalRequests === 0) {
                    return 0.0;
                }

                return round(($successfulRequests / $totalRequests) * 100, 2);
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
                $totalRequests = 0;
                $failedRequests = 0;

                $rawTotalRequests = $attributes['total_requests'] ?? null;
                $rawFailedRequests = $attributes['failed_requests'] ?? null;

                if (is_int($rawTotalRequests) || is_float($rawTotalRequests) || (is_string($rawTotalRequests) && is_numeric($rawTotalRequests))) {
                    $totalRequests = (int) $rawTotalRequests;
                }

                if (is_int($rawFailedRequests) || is_float($rawFailedRequests) || (is_string($rawFailedRequests) && is_numeric($rawFailedRequests))) {
                    $failedRequests = (int) $rawFailedRequests;
                }

                if ($totalRequests === 0) {
                    return 0.0;
                }

                return round(($failedRequests / $totalRequests) * 100, 2);
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

                $checks = 0;
                $successful = 0;

                $rawChecks = $healthStatus['uptime_checks'];
                $rawSuccessful = $healthStatus['successful_checks'] ?? null;

                if (is_int($rawChecks) || is_float($rawChecks) || (is_string($rawChecks) && is_numeric($rawChecks))) {
                    $checks = (int) $rawChecks;
                }

                if (is_int($rawSuccessful) || is_float($rawSuccessful) || (is_string($rawSuccessful) && is_numeric($rawSuccessful))) {
                    $successful = (int) $rawSuccessful;
                }

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
        $recentErrors = is_array($this->recent_errors) ? $this->recent_errors : [];
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
    /** @param array<string, mixed> $healthData */
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
     *
     * @param  Builder<MCPServer>  $query
     * @return Builder<MCPServer>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive servers.
     *
     * @param  Builder<MCPServer>  $query
     * @return Builder<MCPServer>
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope a query to only include servers with errors.
     *
     * @param  Builder<MCPServer>  $query
     * @return Builder<MCPServer>
     */
    public function scopeWithErrors(Builder $query): Builder
    {
        return $query->where('status', 'error');
    }

    /**
     * Scope a query to only include servers by type.
     *
     * @param  Builder<MCPServer>  $query
     * @return Builder<MCPServer>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('server_type', $type);
    }

    /**
     * Scope a query to only include servers that auto-start.
     *
     * @param  Builder<MCPServer>  $query
     * @return Builder<MCPServer>
     */
    public function scopeAutoStart(Builder $query): Builder
    {
        return $query->where('auto_start', true);
    }
}
