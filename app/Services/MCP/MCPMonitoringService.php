<?php

namespace App\Services\MCP;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * MCP Monitoring Service
 *
 * Real-time health monitoring for MCP servers with automatic reconnection logic.
 * Tracks server status, response times, and failure patterns.
 *
 * Requirements: 56.4, 57.5
 */
class MCPMonitoringService
{
    protected MCPClientService $mcpClient;

    protected int $healthCheckInterval;

    protected int $reconnectDelay;

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
        $this->healthCheckInterval = 300; // 5 minutes
        $this->reconnectDelay = 60; // 1 minute
    }

    /**
     * Perform comprehensive health check on all MCP servers
     *
     * @return array<string, array{
     *     server_name: string,
     *     status: string,
     *     is_connected: bool,
     *     response_time: float|null,
     *     consecutive_failures: int,
     *     last_success_at: string|null,
     *     last_failure_at: string|null,
     *     last_error: string|null,
     *     capabilities: array<string, mixed>,
     *     needs_reconnection: bool
     * }>
     */
    public function performHealthCheck(): array
        $healthCheck = $this->mcpClient->healthCheck();
        $results = [];

        foreach ($healthCheck as $serverName => $checkResult) {
            $health = $this->mcpClient->getServerHealth($serverName);
            $needsReconnection = $this->mcpClient->needsReconnection($serverName);

            $results[$serverName] = [
                'server_name' => $serverName,
                'status' => $checkResult['status'],
                'is_connected' => $checkResult['status'] === 'healthy',
                'response_time' => $this->measureResponseTime($serverName),
                'consecutive_failures' => $health['consecutive_failures'] ?? 0,
                'last_success_at' => $this->getLastSuccessTime($serverName),
                'last_failure_at' => $this->getLastFailureTime($serverName),
                'last_error' => $this->getLastError($serverName),
                'capabilities' => $checkResult['capabilities'] ?? [],
                'needs_reconnection' => $needsReconnection,
            ];

            // Log health check result to database
            $this->logHealthCheck($serverName, $results[$serverName]);

            // Attempt reconnection if needed
            if ($needsReconnection) {
                $this->attemptReconnection($serverName);
            }
        }

        return $results;
    }

    /**
     * Get server health history
     *
     * @return array<int, array{
     *     server_name: string,
     *     status: string,
     *     is_connected: bool,
     *     response_time: float|null,
     *     consecutive_failures: int,
     *     created_at: string
     * }>
     */
    public function getServerHealthHistory(): array
        $since = now()->subHours($hours);

        $history = DB::table('ucp_mcp_server_health')
            ->where('server_name', $serverName)
            ->where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($record) => [
                'server_name' => $record->server_name,
                'status' => $record->status,
                'is_connected' => (bool) $record->is_connected,
                'response_time' => $record->response_time,
                'consecutive_failures' => $record->consecutive_failures,
                'created_at' => $record->created_at,
            ])
            ->toArray();

        return $history;
    }

    /**
     * Get server uptime statistics
     *
     * @return array{
     *     uptime_percentage: float,
     *     total_checks: int,
     *     successful_checks: int,
     *     failed_checks: int,
     *     avg_response_time: float,
     *     max_consecutive_failures: int
     * }
     */
    public function getServerUptimeStats(): array
        $since = now()->subHours($hours);

        $stats = DB::table('ucp_mcp_server_health')
            ->where('server_name', $serverName)
            ->where('created_at', '>=', $since)
            ->selectRaw('
                COUNT(*) as total_checks,
                SUM(CASE WHEN status = "healthy" THEN 1 ELSE 0 END) as successful_checks,
                SUM(CASE WHEN status = "unhealthy" THEN 1 ELSE 0 END) as failed_checks,
                AVG(response_time) as avg_response_time,
                MAX(consecutive_failures) as max_consecutive_failures
            ')
            ->first();

        $totalChecks = $stats->total_checks ?? 0;
        $successfulChecks = $stats->successful_checks ?? 0;
        $uptimePercentage = $totalChecks > 0 ? ($successfulChecks / $totalChecks) * 100 : 0;

        return [
            'uptime_percentage' => round($uptimePercentage, 2),
            'total_checks' => $totalChecks,
            'successful_checks' => $successfulChecks,
            'failed_checks' => $stats->failed_checks ?? 0,
            'avg_response_time' => round($stats->avg_response_time ?? 0.0, 3),
            'max_consecutive_failures' => $stats->max_consecutive_failures ?? 0,
        ];
    }

    /**
     * Get all servers uptime summary
     *
     * @return array<string, array{
     *     server_name: string,
     *     current_status: string,
     *     uptime_percentage: float,
     *     avg_response_time: float,
     *     last_check_at: string|null
     * }>
     */
    public function getAllServersUptimeSummary(): array
        $servers = $this->mcpClient->getServers();
        $summary = [];

        foreach ($servers as $serverName => $config) {
            if (! is_array($config)) {
                continue;
            }

            $stats = $this->getServerUptimeStats($serverName, $hours);
            $health = $this->mcpClient->getServerHealth($serverName);

            $lastCheck = DB::table('ucp_mcp_server_health')
                ->where('server_name', $serverName)
                ->orderBy('created_at', 'desc')
                ->value('created_at');

            $summary[$serverName] = [
                'server_name' => $serverName,
                'current_status' => $health['status'] ?? 'unknown',
                'uptime_percentage' => $stats['uptime_percentage'],
                'avg_response_time' => $stats['avg_response_time'],
                'last_check_at' => $lastCheck,
            ];
        }

        return $summary;
    }

    /**
     * Get active MCP tool usage
     *
     * @return array<int, array{tool: string, status: string, last_used_at: string|null}>
     */
    public function getActiveTools(): array
        return [];
    }

    /**
     * Attempt to reconnect to a server
     */
    protected function attemptReconnection(string $serverName): bool
    {
        Log::info("[MCPMonitoring] Attempting reconnection to server: {$serverName}");

        try {
            // Perform health check to test connection
            $healthCheck = $this->mcpClient->healthCheck();

            if (isset($healthCheck[$serverName]) && $healthCheck[$serverName]['status'] === 'healthy') {
                Log::info("[MCPMonitoring] Successfully reconnected to server: {$serverName}");

                // Log successful reconnection
                $this->logReconnectionSuccess($serverName);

                return true;
            }

            Log::warning("[MCPMonitoring] Failed to reconnect to server: {$serverName}");

            return false;
        } catch (\Exception $e) {
            Log::error("[MCPMonitoring] Reconnection attempt failed for server: {$serverName}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Log health check result to database
     *
     * @param  array<string, mixed>  $healthData
     */
    protected function logHealthCheck(string $serverName, array $healthData): void
    {
        try {
            DB::table('ucp_mcp_server_health')->insert([
                'server_name' => $serverName,
                'status' => $healthData['status'],
                'is_connected' => $healthData['is_connected'],
                'response_time' => $healthData['response_time'],
                'consecutive_failures' => $healthData['consecutive_failures'],
                'last_success_at' => $healthData['last_success_at'],
                'last_failure_at' => $healthData['last_failure_at'],
                'last_error' => $healthData['last_error'],
                'capabilities' => json_encode($healthData['capabilities']),
                'metadata' => json_encode([
                    'needs_reconnection' => $healthData['needs_reconnection'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("[MCPMonitoring] Failed to log health check for server: {$serverName}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log successful reconnection
     */
    protected function logReconnectionSuccess(string $serverName): void
    {
        try {
            DB::table('ucp_mcp_server_health')->insert([
                'server_name' => $serverName,
                'status' => 'healthy',
                'is_connected' => true,
                'response_time' => null,
                'consecutive_failures' => 0,
                'last_success_at' => now(),
                'last_failure_at' => null,
                'last_error' => null,
                'capabilities' => null,
                'metadata' => json_encode([
                    'reconnection_successful' => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("[MCPMonitoring] Failed to log reconnection success for server: {$serverName}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get monitoring dashboard data
     *
     * @return array{
     *     overall_health: string,
     *     healthy_servers: int,
     *     unhealthy_servers: int,
     *     total_servers: int,
     *     servers: array<string, mixed>,
     *     alerts: array<int, array<string, mixed>>
     * }
     */
    public function getMonitoringDashboard(): array
        $healthCheck = $this->performHealthCheck();

        $healthyCount = collect($healthCheck)->filter(fn ($s) => $s['status'] === 'healthy')->count();
        $unhealthyCount = collect($healthCheck)->filter(fn ($s) => $s['status'] === 'unhealthy')->count();
        $totalCount = count($healthCheck);

        $overallHealth = match (true) {
            $unhealthyCount === 0 => 'healthy',
            $unhealthyCount < $totalCount / 2 => 'degraded',
            default => 'critical',
        };

        // Generate alerts for unhealthy servers
        $alerts = [];
        foreach ($healthCheck as $serverName => $health) {
            if ($health['status'] === 'unhealthy') {
                $alerts[] = [
                    'level' => 'warning',
                    'server' => $serverName,
                    'message' => "Server {$serverName} is unhealthy",
                    'consecutive_failures' => $health['consecutive_failures'],
                    'needs_reconnection' => $health['needs_reconnection'],
                ];
            }

            if ($health['consecutive_failures'] >= 3) {
                $alerts[] = [
                    'level' => 'danger',
                    'server' => $serverName,
                    'message' => "Server {$serverName} has {$health['consecutive_failures']} consecutive failures",
                    'consecutive_failures' => $health['consecutive_failures'],
                    'needs_reconnection' => $health['needs_reconnection'],
                ];
            }
        }

        return [
            'overall_health' => $overallHealth,
            'healthy_servers' => $healthyCount,
            'unhealthy_servers' => $unhealthyCount,
            'total_servers' => $totalCount,
            'servers' => $healthCheck,
            'alerts' => $alerts,
        ];
    }

    /**
     * Measure response time for a server
     */
    protected function measureResponseTime(string $serverName): ?float
    {
        try {
            $startTime = microtime(true);
            $this->mcpClient->healthCheck()[$serverName] ?? null;
            $endTime = microtime(true);

            return round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get last success time from database
     */
    protected function getLastSuccessTime(string $serverName): ?string
    {
        $record = DB::table('ucp_mcp_server_health')
            ->where('server_name', $serverName)
            ->where('status', '=', 'healthy')
            ->orderBy('created_at', 'desc')
            ->first();

        return $record?->created_at;
    }

    /**
     * Get last failure time from database
     */
    protected function getLastFailureTime(string $serverName): ?string
    {
        $record = DB::table('ucp_mcp_server_health')
            ->where('server_name', $serverName)
            ->where('status', '=', 'unhealthy')
            ->orderBy('created_at', 'desc')
            ->first();

        return $record?->created_at;
    }

    /**
     * Get last error from database
     */
    protected function getLastError(string $serverName): ?string
    {
        $record = DB::table('ucp_mcp_server_health')
            ->where('server_name', $serverName)
            ->whereNotNull('last_error')
            ->orderBy('created_at', 'desc')
            ->first();

        return $record?->last_error;
    }
}
