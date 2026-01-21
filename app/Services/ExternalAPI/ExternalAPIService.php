<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * External API Service Base Class
 *
 * Provides a base class for integrating with multiple external APIs.
 * Features include:
 * - API source configuration with priority ordering
 * - Request/response logging
 * - Rate limiting per API source
 * - Timeout handling with configurable limits
 * - Automatic failover to secondary sources
 * - Circuit breaker pattern for failed sources
 *
 * Requirements: 14.1, 14.2, 14.5
 * Task: 1.2.1
 */
abstract class ExternalAPIService
{
    /**
     * API source configurations
     * Override in child classes to define specific API sources
     *
     * @var array<string, array{priority: int, base_url: string, timeout: int, rate_limit: int, enabled: bool}>
     */
    protected array $apiSources = [];

    /**
     * Default timeout in seconds
     */
    protected const DEFAULT_TIMEOUT = 5;

    /**
     * Default rate limit (requests per minute)
     */
    protected const DEFAULT_RATE_LIMIT = 60;

    /**
     * Maximum retry attempts
     */
    protected const MAX_RETRIES = 3;

    /**
     * Circuit breaker failure threshold
     */
    protected const CIRCUIT_BREAKER_THRESHOLD = 5;

    /**
     * Circuit breaker reset time in seconds
     */
    protected const CIRCUIT_BREAKER_RESET_TIME = 60;

    public function __construct(
        protected MCPClientService $mcpClient,
        protected APIPerformanceMetricsService $metricsService
    ) {
        $this->initializeApiSources();
    }

    /**
     * Initialize API sources configuration
     * Override in child classes to set up specific API sources
     */
    abstract protected function initializeApiSources(): void;

    /**
     * Get API sources sorted by priority
     *
     * @return array<string, array{priority: int, base_url: string, timeout: int, rate_limit: int, enabled: bool}>
     */
    protected function getSortedApiSources(): array
    {
        $sources = array_filter($this->apiSources, fn ($source) => $source['enabled'] ?? true);

        uasort($sources, fn ($a, $b) => ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999));

        return $sources;
    }

    /**
     * Fetch data from API with automatic fallback
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}
     */
    protected function fetchWithFallback(
        string $endpoint,
        string $method = 'GET',
        array $params = [],
        ?string $preferredSource = null
    ): array {
        $sources = $this->getSortedApiSources();

        // Try preferred source first if specified
        if ($preferredSource && isset($sources[$preferredSource])) {
            $result = $this->fetchFromSource($preferredSource, $endpoint, $method, $params);
            if ($result['success']) {
                return $result;
            }

            Log::warning('[ExternalAPIService] Preferred source failed, trying fallbacks', [
                'preferred_source' => $preferredSource,
                'endpoint' => $endpoint,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
        }

        // Try each source in priority order
        $errors = [];
        foreach ($sources as $sourceName => $sourceConfig) {
            // Skip if already tried as preferred source
            if ($sourceName === $preferredSource) {
                continue;
            }

            // Check circuit breaker
            if ($this->isCircuitBreakerOpen($sourceName)) {
                Log::debug('[ExternalAPIService] Circuit breaker open, skipping source', [
                    'source' => $sourceName,
                ]);

                continue;
            }

            $result = $this->fetchFromSource($sourceName, $endpoint, $method, $params);

            if ($result['success']) {
                return $result;
            }

            $errors[$sourceName] = $result['error'] ?? 'Unknown error';
        }

        // All sources failed
        Log::error('[ExternalAPIService] All API sources failed', [
            'endpoint' => $endpoint,
            'method' => $method,
            'errors' => $errors,
        ]);

        return [
            'success' => false,
            'data' => null,
            'source' => 'none',
            'error' => 'All API sources failed: '.json_encode($errors),
            'metadata' => [
                'attempted_sources' => array_keys($errors),
                'errors' => $errors,
            ],
        ];
    }

    /**
     * Fetch data from a specific API source
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}
     */
    protected function fetchFromSource(
        string $sourceName,
        string $endpoint,
        string $method = 'GET',
        array $params = []
    ): array {
        $sourceConfig = $this->apiSources[$sourceName] ?? null;

        if (! $sourceConfig) {
            return [
                'success' => false,
                'data' => null,
                'source' => $sourceName,
                'error' => "API source '{$sourceName}' not configured",
                'metadata' => [],
            ];
        }

        // Check rate limiting
        if (! $this->checkRateLimit($sourceName, $sourceConfig['rate_limit'] ?? self::DEFAULT_RATE_LIMIT)) {
            $this->logRequest($sourceName, $endpoint, $method, 'rate_limited');

            return [
                'success' => false,
                'data' => null,
                'source' => $sourceName,
                'error' => 'Rate limit exceeded',
                'metadata' => [
                    'rate_limited' => true,
                ],
            ];
        }

        $url = $sourceConfig['base_url'].$endpoint;
        $timeout = $sourceConfig['timeout'] ?? self::DEFAULT_TIMEOUT;

        $startTime = microtime(true);

        try {
            // Log request
            $this->logRequest($sourceName, $endpoint, $method, 'started');

            // Make request using MCP client
            $response = $this->makeRequest($url, $method, $params, $timeout);

            $duration = (microtime(true) - $startTime) * 1000;

            // Log successful response
            $this->logResponse($sourceName, $endpoint, $method, 'success', $duration, $response['status'] ?? 200);

            // Record metrics
            $this->metricsService->recordResponseTime($sourceName, $endpoint, $duration, true);

            // Reset circuit breaker on success
            $this->resetCircuitBreaker($sourceName);

            return [
                'success' => true,
                'data' => $response['data'] ?? $response['body'] ?? null,
                'source' => $sourceName,
                'metadata' => [
                    'response_time_ms' => round($duration, 2),
                    'status_code' => $response['status'] ?? 200,
                    'fetched_at' => now()->toISOString(),
                ],
            ];
        } catch (\Exception $e) {
            $duration = (microtime(true) - $startTime) * 1000;

            // Log failed response
            $this->logResponse($sourceName, $endpoint, $method, 'failed', $duration, 0, $e->getMessage());

            // Record metrics
            $this->metricsService->recordResponseTime($sourceName, $endpoint, $duration, false);
            $this->metricsService->recordError($sourceName, get_class($e), $e->getMessage());

            // Increment circuit breaker
            $this->incrementCircuitBreaker($sourceName);

            return [
                'success' => false,
                'data' => null,
                'source' => $sourceName,
                'error' => $e->getMessage(),
                'metadata' => [
                    'response_time_ms' => round($duration, 2),
                    'error_type' => get_class($e),
                ],
            ];
        }
    }

    /**
     * Make HTTP request using MCP client
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, status: int, headers: array<string, string>, body: string, data?: mixed}
     */
    protected function makeRequest(
        string $url,
        string $method = 'GET',
        array $params = [],
        int $timeout = self::DEFAULT_TIMEOUT
    ): array {
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'UmamusumeCareerPlanner/1.0',
        ];

        // Use MCP fetch if available
        if ($this->mcpClient->isFetchAvailable()) {
            try {
                $response = $this->mcpClient->fetch(
                    $url,
                    $method,
                    $params,
                    $headers,
                    $timeout,
                    self::MAX_RETRIES
                );

                // Parse JSON response if available
                $data = null;
                if (isset($response['body'])) {
                    $decoded = json_decode($response['body'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $decoded;
                    }
                }

                return [
                    'success' => $response['success'] ?? false,
                    'status' => $response['status'] ?? 200,
                    'headers' => $response['headers'] ?? [],
                    'body' => $response['body'] ?? '',
                    'data' => $data,
                ];
            } catch (\Exception $e) {
                throw new \RuntimeException("MCP fetch failed: {$e->getMessage()}", 0, $e);
            }
        }

        throw new \RuntimeException('MCP fetch server not available');
    }

    /**
     * Check rate limit for API source
     */
    protected function checkRateLimit(string $sourceName, int $maxAttempts): bool
    {
        $key = "api_rate_limit:{$sourceName}";

        return RateLimiter::attempt(
            $key,
            $maxAttempts,
            function () {
                // Rate limit check passed
            },
            60 // 1 minute decay
        );
    }

    /**
     * Log API request
     */
    protected function logRequest(
        string $source,
        string $endpoint,
        string $method,
        string $status
    ): void {
        Log::info('[ExternalAPIService] API Request', [
            'source' => $source,
            'endpoint' => $endpoint,
            'method' => $method,
            'status' => $status,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log API response
     */
    protected function logResponse(
        string $source,
        string $endpoint,
        string $method,
        string $status,
        float $durationMs,
        int $statusCode = 0,
        ?string $error = null
    ): void {
        $logData = [
            'source' => $source,
            'endpoint' => $endpoint,
            'method' => $method,
            'status' => $status,
            'duration_ms' => round($durationMs, 2),
            'status_code' => $statusCode,
            'timestamp' => now()->toISOString(),
        ];

        if ($error) {
            $logData['error'] = $error;
        }

        if ($status === 'success') {
            Log::info('[ExternalAPIService] API Response Success', $logData);
        } else {
            Log::warning('[ExternalAPIService] API Response Failed', $logData);
        }
    }

    /**
     * Check if circuit breaker is open for a source
     */
    protected function isCircuitBreakerOpen(string $sourceName): bool
    {
        $key = "circuit_breaker:{$sourceName}";
        $failures = Cache::get($key, 0);

        return $failures >= self::CIRCUIT_BREAKER_THRESHOLD;
    }

    /**
     * Increment circuit breaker failure count
     */
    protected function incrementCircuitBreaker(string $sourceName): void
    {
        $key = "circuit_breaker:{$sourceName}";
        $failures = Cache::get($key, 0);
        $failures++;

        Cache::put($key, $failures, self::CIRCUIT_BREAKER_RESET_TIME);

        if ($failures >= self::CIRCUIT_BREAKER_THRESHOLD) {
            Log::warning('[ExternalAPIService] Circuit breaker opened', [
                'source' => $sourceName,
                'failures' => $failures,
                'threshold' => self::CIRCUIT_BREAKER_THRESHOLD,
            ]);
        }
    }

    /**
     * Reset circuit breaker for a source
     */
    protected function resetCircuitBreaker(string $sourceName): void
    {
        $key = "circuit_breaker:{$sourceName}";
        Cache::forget($key);
    }

    /**
     * Get circuit breaker status for all sources
     *
     * @return array<string, array{failures: int, is_open: bool}>
     */
    public function getCircuitBreakerStatus(): array
    {
        $status = [];

        foreach (array_keys($this->apiSources) as $sourceName) {
            $key = "circuit_breaker:{$sourceName}";
            $failures = Cache::get($key, 0);

            $status[$sourceName] = [
                'failures' => $failures,
                'is_open' => $failures >= self::CIRCUIT_BREAKER_THRESHOLD,
            ];
        }

        return $status;
    }

    /**
     * Get API source configuration
     *
     * @return array<string, array{priority: int, base_url: string, timeout: int, rate_limit: int, enabled: bool}>
     */
    public function getApiSources(): array
    {
        return $this->apiSources;
    }

    /**
     * Enable an API source
     */
    public function enableSource(string $sourceName): bool
    {
        if (! isset($this->apiSources[$sourceName])) {
            return false;
        }

        $this->apiSources[$sourceName]['enabled'] = true;

        Log::info('[ExternalAPIService] API source enabled', [
            'source' => $sourceName,
        ]);

        return true;
    }

    /**
     * Disable an API source
     */
    public function disableSource(string $sourceName): bool
    {
        if (! isset($this->apiSources[$sourceName])) {
            return false;
        }

        $this->apiSources[$sourceName]['enabled'] = false;

        Log::info('[ExternalAPIService] API source disabled', [
            'source' => $sourceName,
        ]);

        return true;
    }

    /**
     * Get health status of all API sources
     *
     * @return array<string, array{enabled: bool, circuit_breaker_open: bool, priority: int}>
     */
    public function getHealthStatus(): array
    {
        $status = [];

        foreach ($this->apiSources as $sourceName => $config) {
            $status[$sourceName] = [
                'enabled' => $config['enabled'] ?? true,
                'circuit_breaker_open' => $this->isCircuitBreakerOpen($sourceName),
                'priority' => $config['priority'] ?? 999,
            ];
        }

        return $status;
    }
}
