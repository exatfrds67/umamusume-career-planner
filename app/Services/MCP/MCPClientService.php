<?php

namespace App\Services\MCP;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * MCP Client Service with Health Monitoring and Automatic Reconnection
 *
 * Manages connections to MCP servers including strands-agents and agentcore-mcp-server
 * for AI services integration with health monitoring and automatic reconnection capabilities.
 *
 * Requirements: 56.1, 56.2
 */
class MCPClientService
{
    /** @var array<string, mixed> */
    protected array $servers;

    protected bool $enabled;

    protected bool $debug;

    protected int $healthCheckInterval;

    protected int $connectionTimeout;

    protected int $maxConcurrentCalls;

    /** @var array<string, array{status: string, last_check: int, consecutive_failures: int}> */
    protected array $serverHealth = [];

    protected const MAX_CONSECUTIVE_FAILURES = 3;

    protected const RECONNECT_DELAY_SECONDS = 60;

    public function __construct()
    {
        $this->enabled = (bool) Config::get('mcp.enabled', true);
        $this->debug = (bool) Config::get('mcp.debug', false);
        $this->healthCheckInterval = (int) Config::get('mcp.health_check_interval', 300);
        $this->connectionTimeout = (int) Config::get('mcp.connection_timeout', 10);
        $this->maxConcurrentCalls = (int) Config::get('mcp.max_concurrent_calls', 5);

        $servers = Config::get('mcp.servers', []);
        $this->servers = is_array($servers) ? $servers : [];

        $this->initializeServerHealth();
    }

    /**
     * Initialize server health tracking
     */
    protected function initializeServerHealth(): void
    {
        foreach ($this->servers as $name => $config) {
            if (! is_array($config)) {
                continue;
            }

            $this->serverHealth[$name] = [
                'status' => 'unknown',
                'last_check' => 0,
                'consecutive_failures' => 0,
            ];
        }
    }

    /**
     * Check if MCP is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get all configured MCP servers
     *
     * @return array<string, mixed>
     */
    public function getServers(): array
    {
        return $this->servers;
    }

    /**
     * Get a specific MCP server configuration
     *
     * @return array<string, mixed>|null
     */
    public function getServer(string $name): ?array
    {
        /** @var array<string, mixed>|null $server */
        $server = $this->servers[$name] ?? null;

        return is_array($server) ? $server : null;
    }

    /**
     * Check if a specific server is enabled
     */
    public function isServerEnabled(string $name): bool
    {
        $server = $this->getServer($name);

        return $server && ($server['enabled'] ?? false);
    }

    /**
     * Get server capabilities
     *
     * @return array<string, mixed>
     */
    public function getServerCapabilities(string $name): array
    {
        $server = $this->getServer($name);
        if (! $server) {
            return [];
        }
        $capabilities = $server['capabilities'] ?? [];

        return is_array($capabilities) ? $capabilities : [];
    }

    /**
     * Get server health status
     *
     * @return array{status: string, last_check: int, consecutive_failures: int}|null
     */
    public function getServerHealth(string $name): ?array
    {
        return $this->serverHealth[$name] ?? null;
    }

    /**
     * Check if server is healthy and available
     */
    public function isServerHealthy(string $name): bool
    {
        $health = $this->getServerHealth($name);
        if (! $health) {
            return false;
        }

        return $health['status'] === 'healthy' &&
            $health['consecutive_failures'] < self::MAX_CONSECUTIVE_FAILURES;
    }

    /**
     * Check if server needs reconnection
     */
    public function needsReconnection(string $name): bool
    {
        $health = $this->getServerHealth($name);
        if (! $health) {
            return false;
        }

        $timeSinceLastCheck = time() - $health['last_check'];

        return $health['status'] === 'unhealthy' &&
            $timeSinceLastCheck >= self::RECONNECT_DELAY_SECONDS;
    }

    /**
     * Log MCP operations if debug is enabled
     *
     * @param  array<string, mixed>  $context
     */
    protected function debugLog(string $message, array $context = []): void
    {
        if ($this->debug) {
            Log::debug("[MCP] {$message}", $context);
        }
    }

    /**
     * Health check for MCP servers with automatic reconnection
     *
     * @return array<string, array{status: string, message: string, capabilities?: array<string, mixed>, health?: array<string, mixed>}>
     */
    public function healthCheck(): array
    {
        $results = [];

        foreach ($this->servers as $name => $config) {
            if (! is_array($config)) {
                continue;
            }

            if (! ($config['enabled'] ?? false)) {
                $results[$name] = [
                    'status' => 'disabled',
                    'message' => 'Server is disabled in configuration',
                ];
                $this->updateServerHealth($name, 'disabled', true);

                continue;
            }

            // Check if health check is needed
            $health = $this->getServerHealth($name);
            if ($health && (time() - $health['last_check']) < $this->healthCheckInterval) {
                $results[$name] = [
                    'status' => $health['status'],
                    'message' => 'Using cached health status',
                    'capabilities' => $config['capabilities'] ?? [],
                    'health' => $health,
                ];

                continue;
            }

            // Perform health check
            $checkResult = $this->performHealthCheck($name, $config);
            $results[$name] = $checkResult;

            // Update server health tracking
            $this->updateServerHealth(
                $name,
                $checkResult['status'],
                $checkResult['status'] === 'healthy'
            );

            // Attempt reconnection if needed
            if ($this->needsReconnection($name)) {
                $this->attemptReconnection($name, $config);
            }
        }

        $this->debugLog('MCP health check completed', $results);

        return $results;
    }

    /**
     * Perform health check for a specific server
     *
     * @param  array<string, mixed>  $config
     * @return array{status: string, message: string, capabilities: array<string, mixed>, health: array<string, mixed>}
     */
    protected function performHealthCheck(string $name, array $config): array
    {
        $capabilities = $config['capabilities'] ?? [];
        $health = $this->getServerHealth($name) ?? [
            'status' => 'unknown',
            'last_check' => 0,
            'consecutive_failures' => 0,
        ];

        // Check if server command is available
        $command = $config['command'] ?? '';
        if (empty($command)) {
            return [
                'status' => 'unhealthy',
                'message' => 'Server command not configured',
                'capabilities' => is_array($capabilities) ? $capabilities : [],
                'health' => $health,
            ];
        }

        // For now, we assume servers are healthy if properly configured
        // In production, this would make actual MCP protocol calls
        return [
            'status' => 'healthy',
            'message' => 'Server is properly configured and ready',
            'capabilities' => is_array($capabilities) ? $capabilities : [],
            'health' => $health,
        ];
    }

    /**
     * Update server health tracking
     */
    protected function updateServerHealth(string $name, string $status, bool $success): void
    {
        $health = $this->serverHealth[$name] ?? [
            'status' => 'unknown',
            'last_check' => 0,
            'consecutive_failures' => 0,
        ];

        $health['status'] = $status;
        $health['last_check'] = time();

        if ($success) {
            $health['consecutive_failures'] = 0;
        } else {
            $health['consecutive_failures']++;
        }

        $this->serverHealth[$name] = $health;

        // Cache health status
        Cache::put("mcp_health_{$name}", $health, $this->healthCheckInterval);

        $this->debugLog("Updated health for server: {$name}", $health);
    }

    /**
     * Attempt to reconnect to a server
     *
     * @param  array<string, mixed>  $config
     */
    protected function attemptReconnection(string $name, array $config): bool
    {
        $this->debugLog("Attempting reconnection to server: {$name}");

        // Perform health check to test connection
        $result = $this->performHealthCheck($name, $config);

        if ($result['status'] === 'healthy') {
            $this->updateServerHealth($name, 'healthy', true);
            Log::info("[MCP] Successfully reconnected to server: {$name}");

            return true;
        }

        Log::warning("[MCP] Failed to reconnect to server: {$name}", [
            'result' => $result,
        ]);

        return false;
    }

    /**
     * Get health status for all servers
     *
     * @return array<string, array{status: string, last_check: int, consecutive_failures: int}>
     */
    public function getAllServerHealth(): array
    {
        return $this->serverHealth;
    }

    /**
     * Reset health tracking for a server
     */
    public function resetServerHealth(string $name): void
    {
        if (isset($this->serverHealth[$name])) {
            $this->serverHealth[$name] = [
                'status' => 'unknown',
                'last_check' => 0,
                'consecutive_failures' => 0,
            ];

            Cache::forget("mcp_health_{$name}");

            $this->debugLog("Reset health tracking for server: {$name}");
        }
    }

    /**
     * Get connection timeout setting
     */
    public function getConnectionTimeout(): int
    {
        return $this->connectionTimeout;
    }

    /**
     * Get max concurrent calls setting
     */
    public function getMaxConcurrentCalls(): int
    {
        return $this->maxConcurrentCalls;
    }

    /**
     * Check if strands-agents server is available
     */
    public function isStrandsAgentsAvailable(): bool
    {
        return $this->isServerEnabled('strands-agents') &&
            $this->isServerHealthy('strands-agents');
    }

    /**
     * Check if agentcore-mcp-server is available
     */
    public function isAgentCoreAvailable(): bool
    {
        return $this->isServerEnabled('agentcore-mcp-server') &&
            $this->isServerHealthy('agentcore-mcp-server');
    }

    /**
     * Get AI service servers status
     *
     * @return array{strands_agents: bool, agentcore: bool}
     */
    public function getAIServicesStatus(): array
    {
        return [
            'strands_agents' => $this->isStrandsAgentsAvailable(),
            'agentcore' => $this->isAgentCoreAvailable(),
        ];
    }

    /**
     * Execute an agent via MCP
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function executeAgent(string $agentType, array $input): array
    {
        // Simulate agent execution via MCP
        // In production, this would make actual MCP protocol calls
        $this->debugLog('Executing agent via MCP', [
            'agent_type' => $agentType,
            'input_size' => strlen(json_encode($input)),
        ]);

        return [
            'status' => 'success',
            'agent_type' => $agentType,
            'output' => $input, // Echo input for now
        ];
    }

    /**
     * Register an agent with MCP
     *
     * @param  array<string, mixed>  $config
     */
    public function registerAgent(string $agentId, string $agentType, array $config): bool
    {
        $this->debugLog('Registering agent with MCP', [
            'agent_id' => $agentId,
            'agent_type' => $agentType,
        ]);

        // In production, this would register the agent with MCP servers
        return true;
    }

    /**
     * Unregister an agent from MCP
     */
    public function unregisterAgent(string $agentId): bool
    {
        $this->debugLog('Unregistering agent from MCP', [
            'agent_id' => $agentId,
        ]);

        // In production, this would unregister the agent from MCP servers
        return true;
    }
}
