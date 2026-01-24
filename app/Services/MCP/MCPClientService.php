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
        $healthCheckInterval = Config::get('mcp.health_check_interval', 300);
        $this->healthCheckInterval = is_int($healthCheckInterval) ? $healthCheckInterval : 300;
        $connectionTimeout = Config::get('mcp.connection_timeout', 10);
        $this->connectionTimeout = is_int($connectionTimeout) ? $connectionTimeout : 10;
        $maxConcurrentCalls = Config::get('mcp.max_concurrent_calls', 5);
        $this->maxConcurrentCalls = is_int($maxConcurrentCalls) ? $maxConcurrentCalls : 5;

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
            'input_size' => strlen(json_encode($input) ?: '{}'),
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

    /**
     * Call an MCP tool on a specific server
     *
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    public function callTool(string $serverName, string $toolName, array $arguments): array
    {
        if (! $this->isEnabled()) {
            throw new \RuntimeException('MCP is not enabled');
        }

        if (! $this->isServerEnabled($serverName)) {
            throw new \RuntimeException("MCP server '{$serverName}' is not enabled");
        }

        if (! $this->isServerHealthy($serverName)) {
            throw new \RuntimeException("MCP server '{$serverName}' is not healthy");
        }

        $this->debugLog("Calling MCP tool: {$serverName}.{$toolName}", [
            'server' => $serverName,
            'tool' => $toolName,
            'arguments' => $arguments,
        ]);

        // In production, this would make actual MCP protocol calls
        // For now, we simulate the response structure
        return [
            'success' => true,
            'server' => $serverName,
            'tool' => $toolName,
            'result' => $arguments,
        ];
    }

    /**
     * Perform HTTP GET request using MCP fetch server
     *
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    public function get(string $url, array $headers = [], int $timeout = 30): array
    {
        return $this->fetch($url, 'GET', [], $headers, $timeout);
    }

    /**
     * Perform HTTP POST request using MCP fetch server
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    public function post(string $url, array $data = [], array $headers = [], int $timeout = 30): array
    {
        return $this->fetch($url, 'POST', $data, $headers, $timeout);
    }

    /**
     * Perform HTTP PUT request using MCP fetch server
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    public function put(string $url, array $data = [], array $headers = [], int $timeout = 30): array
    {
        return $this->fetch($url, 'PUT', $data, $headers, $timeout);
    }

    /**
     * Perform HTTP DELETE request using MCP fetch server
     *
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    public function delete(string $url, array $headers = [], int $timeout = 30): array
    {
        return $this->fetch($url, 'DELETE', [], $headers, $timeout);
    }

    /**
     * Perform HTTP PATCH request using MCP fetch server
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    public function patch(string $url, array $data = [], array $headers = [], int $timeout = 30): array
    {
        return $this->fetch($url, 'PATCH', $data, $headers, $timeout);
    }

    /**
     * Perform HTTP request using MCP fetch server with retry logic
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    public function fetch(
        string $url,
        string $method = 'GET',
        array $data = [],
        array $headers = [],
        int $timeout = 30,
        int $maxRetries = 3
    ): array {
        if (! $this->isServerEnabled('fetch')) {
            throw new \RuntimeException('MCP fetch server is not enabled');
        }

        $startTime = microtime(true);
        $attempt = 0;
        $lastException = null;

        // Default headers
        $defaultHeaders = [
            'Accept' => 'application/json',
            'User-Agent' => 'UmamusumeCareerPlanner/1.0',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        // Add Content-Type for requests with body
        if (in_array($method, ['POST', 'PUT', 'PATCH']) && ! isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        while ($attempt < $maxRetries) {
            $attempt = $attempt + 1;

            try {
                $this->debugLog("HTTP {$method} request (attempt {$attempt}/{$maxRetries})", [
                    'url' => $url,
                    'method' => $method,
                    'timeout' => $timeout,
                    'has_data' => ! empty($data),
                ]);

                // Simulate MCP fetch call
                // In production, this would use actual MCP protocol
                $response = $this->performFetch($url, $method, $data, $headers, $timeout);

                $duration = round((microtime(true) - $startTime) * 1000, 2);

                Log::info('[MCP Fetch] Request successful', [
                    'url' => $url,
                    'method' => $method,
                    'status' => $response['status'] ?? 'unknown',
                    'duration_ms' => $duration,
                    'attempt' => $attempt,
                ]);

                return $response;
            } catch (\Exception $e) {
                $lastException = $e;
                $duration = round((microtime(true) - $startTime) * 1000, 2);

                Log::warning('[MCP Fetch] Request failed', [
                    'url' => $url,
                    'method' => $method,
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'duration_ms' => $duration,
                    'error' => $e->getMessage(),
                ]);

                // Don't retry on client errors (4xx)
                if ($e instanceof \RuntimeException && str_contains($e->getMessage(), '4')) {
                    break;
                }

                // Wait before retry (exponential backoff)
                if ($attempt < $maxRetries) {
                    $waitTime = min(1000 * pow(2, $attempt - 1), 5000); // Max 5 seconds
                    usleep($waitTime * 1000);
                }
            }
        }

        $totalDuration = round((microtime(true) - $startTime) * 1000, 2);

        Log::error('[MCP Fetch] All retry attempts failed', [
            'url' => $url,
            'method' => $method,
            'attempts' => $attempt,
            'total_duration_ms' => $totalDuration,
            'last_error' => $lastException?->getMessage(),
        ]);

        throw new \RuntimeException(
            "HTTP {$method} request to {$url} failed after {$attempt} attempts: ".$lastException?->getMessage()
        );
    }

    /**
     * Perform the actual fetch operation
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    protected function performFetch(
        string $url,
        string $method,
        array $data = [],
        array $headers = [],
        int $timeout = 30
    ): array {
        // In production, this would make actual MCP protocol calls to the fetch server
        // For now, we simulate a successful response structure

        // Validate URL
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException("Invalid URL: {$url}");
        }

        // Simulate response based on method
        $statusCode = 200;
        $responseBody = json_encode([
            'success' => true,
            'message' => 'Simulated response',
            'method' => $method,
            'url' => $url,
        ]);

        return [
            'success' => true,
            'status' => $statusCode,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Request-ID' => uniqid('req_'),
            ],
            'body' => $responseBody,
            'url' => $url,
            'method' => $method,
        ];
    }

    /**
     * Fetch with automatic JSON decoding
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    public function fetchJson(
        string $url,
        string $method = 'GET',
        array $data = [],
        array $headers = [],
        int $timeout = 30
    ): array {
        $response = $this->fetch($url, $method, $data, $headers, $timeout);

        if (! $response['success']) {
            $error = $response['error'] ?? 'Unknown error';
            $errorStr = is_string($error) ? $error : 'Unknown error';
            throw new \RuntimeException("Fetch failed: {$errorStr}");
        }

        $body = $response['body'] ?? '';
        $bodyStr = is_string($body) ? $body : '';
        $decoded = json_decode($bodyStr, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to decode JSON response: '.json_last_error_msg());
        }

        return [
            'success' => true,
            'status' => $response['status'] ?? 200,
            'headers' => $response['headers'] ?? [],
            'data' => $decoded,
            'raw_body' => $body,
        ];
    }

    /**
     * Check if fetch server is available
     */
    public function isFetchAvailable(): bool
    {
        return $this->isServerEnabled('fetch') && $this->isServerHealthy('fetch');
    }

    /**
     * Get fetch server configuration
     *
     * @return array<string, mixed>|null
     */
    public function getFetchServerConfig(): ?array
    {
        return $this->getServer('fetch');
    }
}
