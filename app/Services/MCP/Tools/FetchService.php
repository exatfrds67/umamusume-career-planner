<?php

namespace App\Services\MCP\Tools;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetch Service via MCP
 *
 * Integrates with fetch MCP server for enhanced HTTP capabilities,
 * external API integration, and data retrieval with retry logic.
 *
 * Requirements: 13.4, 56.2, 14.1
 */
class FetchService
{
    protected MCPClientService $mcpClient;

    protected bool $enabled;

    protected int $cacheTTL;

    protected string $serverName = 'fetch';

    protected int $timeout;

    protected int $maxRetries;

    protected int $retryDelay;

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
        $this->enabled = (bool) Config::get('mcp.tools.fetch.enabled', true);
        $configTTL = Config::get('mcp.tools.fetch.cache_ttl', 3600);
        $this->cacheTTL = is_numeric($configTTL) ? (int) $configTTL : 3600;
        $configTimeout = Config::get('mcp.tools.fetch.timeout', 30);
        $this->timeout = is_numeric($configTimeout) ? (int) $configTimeout : 30;
        $configMaxRetries = Config::get('mcp.tools.fetch.max_retries', 3);
        $this->maxRetries = is_numeric($configMaxRetries) ? (int) $configMaxRetries : 3;
        $configRetryDelay = Config::get('mcp.tools.fetch.retry_delay', 1000);
        $this->retryDelay = is_numeric($configRetryDelay) ? (int) $configRetryDelay : 1000;
    }

    /**
     * Check if Fetch service is available
     */
    public function isAvailable(): bool
    {
        return $this->enabled &&
            $this->mcpClient->isServerEnabled($this->serverName) &&
            $this->mcpClient->isServerHealthy($this->serverName);
    }

    /**
     * Fetch data from external API with retry logic
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, data: mixed, status_code: int, headers: array<string, string>, cached: bool, attempts: int, response_time: float}
     */
    public function fetch(string $url, string $method = 'GET', array $options = []): array
    {
        $startTime = microtime(true);
        $cacheKey = $this->generateCacheKey($url, $method, $options);
        $shouldCache = isset($options['cache']) ? (bool) $options['cache'] : true;

        // Check cache if enabled
        if ($shouldCache) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                /** @var array{success: bool, data: mixed, status_code: int, headers: array<string, string>} $cached */
                $responseTime = microtime(true) - $startTime;
                $result = [
                    'success' => $cached['success'] ?? false,
                    'data' => $cached['data'] ?? null,
                    'status_code' => $cached['status_code'] ?? 0,
                    'headers' => $cached['headers'] ?? [],
                    'cached' => true,
                    'attempts' => 0,
                    'response_time' => $responseTime,
                ];

                // Track statistics
                $this->trackStatistics(true, $result['success'], $responseTime);

                return $result;
            }
        }

        // Perform fetch with retry logic
        $attempts = 0;
        $lastError = null;

        while ($attempts < $this->maxRetries) {
            $attempts++;

            try {
                $response = $this->performFetch($url, $method, $options);

                // Cache successful responses
                $success = isset($response['success']) && $response['success'];
                $cacheTTL = isset($options['cache_ttl']) && is_numeric($options['cache_ttl']) ? (int) $options['cache_ttl'] : $this->cacheTTL;
                if ($success && $shouldCache) {
                    Cache::put($cacheKey, $response, $cacheTTL);
                }

                /** @var array{success: bool, data: mixed, status_code: int, headers: array<string, string>} $response */
                $responseTime = microtime(true) - $startTime;
                $result = [
                    'success' => $success,
                    'data' => $response['data'] ?? null,
                    'status_code' => isset($response['status_code']) && is_int($response['status_code']) ? $response['status_code'] : 0,
                    'headers' => isset($response['headers']) && is_array($response['headers']) ? $response['headers'] : [],
                    'cached' => false,
                    'attempts' => $attempts,
                    'response_time' => $responseTime,
                ];

                // Track statistics
                $this->trackStatistics(false, $success, $responseTime);

                return $result;
            } catch (\Exception $e) {
                $lastError = $e;

                if ($attempts < $this->maxRetries) {
                    // Wait before retry with exponential backoff
                    usleep($this->retryDelay * $attempts * 1000);
                }
            }
        }

        // All retries failed
        Log::error('[Fetch] All fetch attempts failed', [
            'url' => $url,
            'method' => $method,
            'attempts' => $attempts,
            'error' => $lastError?->getMessage(),
        ]);

        $responseTime = microtime(true) - $startTime;
        $result = [
            'success' => false,
            'data' => null,
            'status_code' => 0,
            'headers' => [],
            'cached' => false,
            'attempts' => $attempts,
            'response_time' => $responseTime,
        ];

        // Track statistics
        $this->trackStatistics(false, false, $responseTime);

        return $result;
    }

    /**
     * Fetch data from umapyoi.net API
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: mixed, cached: bool}
     */
    public function fetchUmapyoiData(string $endpoint, array $params = []): array
    {
        $baseUrlConfig = Config::get('external_apis.umapyoi.base_url', 'https://api.umapyoi.net');
        $baseUrl = is_string($baseUrlConfig) ? $baseUrlConfig : 'https://api.umapyoi.net';
        $url = "{$baseUrl}/{$endpoint}";

        if (! empty($params)) {
            $url .= '?'.http_build_query($params);
        }

        $response = $this->fetch($url, 'GET', [
            'cache' => true,
            'cache_ttl' => 86400, // Cache for 24 hours
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'UmamusumeCareerPlanner/1.0',
            ],
        ]);

        return [
            'success' => $response['success'],
            'data' => $response['data'],
            'cached' => $response['cached'],
        ];
    }

    /**
     * Batch fetch multiple URLs
     *
     * @param  array<int, array{url: string, method?: string, options?: array<string, mixed>}>  $requests
     * @return array<int, array{success: bool, data: mixed, status_code: int, headers: array<string, string>, cached: bool, attempts: int, response_time: float}>
     */
    public function batchFetch(array $requests): array
    {
        /** @var array<int, array{success: bool, data: mixed, status_code: int, headers: array<string, string>, cached: bool, attempts: int, response_time: float}> $results */
        $results = [];

        foreach ($requests as $index => $request) {
            $url = $request['url'];
            $method = $request['method'] ?? 'GET';
            /** @var array<string, mixed> $options */
            $options = $request['options'] ?? [];

            $results[$index] = $this->fetch($url, $method, $options);
        }

        return $results;
    }

    /**
     * Fetch with circuit breaker pattern
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, data: mixed, status_code: int, headers: array<string, string>, cached: bool, attempts: int, response_time: float}
     */
    public function fetchWithCircuitBreaker(string $url, string $method = 'GET', array $options = []): array
    {
        $circuitKey = "fetch_circuit_{$url}";
        $defaultState = ['state' => 'closed', 'failures' => 0, 'opened_at' => 0];
        $circuitStateRaw = Cache::get($circuitKey);
        /** @var array{state: string, failures: int, opened_at: int} $circuitState */
        $circuitState = is_array($circuitStateRaw) ? array_merge($defaultState, $circuitStateRaw) : $defaultState;

        // Check if circuit is open
        if ($circuitState['state'] === 'open') {
            $openedAt = is_numeric($circuitState['opened_at']) ? (int) $circuitState['opened_at'] : 0;
            $timeSinceOpen = time() - $openedAt;

            // Try to close circuit after timeout
            if ($timeSinceOpen > 60) {
                $circuitState['state'] = 'half-open';
                Cache::put($circuitKey, $circuitState, 300);
            } else {
                return [
                    'success' => false,
                    'data' => null,
                    'status_code' => 503,
                    'headers' => [],
                    'cached' => false,
                    'attempts' => 0,
                    'response_time' => 0.0,
                ];
            }
        }

        // Perform fetch
        $response = $this->fetch($url, $method, $options);

        // Update circuit state
        if ($response['success']) {
            // Reset circuit on success
            Cache::put($circuitKey, ['state' => 'closed', 'failures' => 0, 'opened_at' => 0], 300);
        } else {
            // Increment failures
            $failures = is_int($circuitState['failures']) ? $circuitState['failures'] + 1 : 1;

            // Open circuit if threshold exceeded
            if ($failures >= 5) {
                $circuitState['state'] = 'open';
                $circuitState['opened_at'] = time();
            }
            $circuitState['failures'] = $failures;

            Cache::put($circuitKey, $circuitState, 300);
        }

        return $response;
    }

    /**
     * Perform actual HTTP fetch
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, data: mixed, status_code: int, headers: array<string, array<int, string>>}
     */
    protected function performFetch(string $url, string $method, array $options = []): array
    {
        /** @var array<string, string> $headers */
        $headers = isset($options['headers']) && is_array($options['headers']) ? $options['headers'] : [];
        /** @var array<string, mixed>|null $body */
        $body = isset($options['body']) && is_array($options['body']) ? $options['body'] : null;
        $timeout = isset($options['timeout']) && is_numeric($options['timeout']) ? (int) $options['timeout'] : $this->timeout;

        // Build HTTP request
        $request = Http::timeout($timeout);

        // Add headers
        if (\count($headers) > 0) {
            $request = $request->withHeaders($headers);
        }

        // Perform request
        $response = match (strtoupper($method)) {
            'GET' => $request->get($url),
            'POST' => $request->post($url, $body ?? []),
            'PUT' => $request->put($url, $body ?? []),
            'PATCH' => $request->patch($url, $body ?? []),
            'DELETE' => $request->delete($url),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        // Parse response
        $success = $response->successful();
        $statusCode = $response->status();
        /** @var array<string, array<int, string>> $responseHeaders */
        $responseHeaders = $response->headers();

        // Try to parse JSON, fallback to raw body
        try {
            $data = $response->json();
        } catch (\Exception $e) {
            $data = $response->body();
        }

        return [
            'success' => $success,
            'data' => $data,
            'status_code' => $statusCode,
            'headers' => $responseHeaders,
        ];
    }

    /**
     * Generate cache key
     *
     * @param  array<string, mixed>  $options
     */
    protected function generateCacheKey(string $url, string $method, array $options): string
    {
        $key = "{$method}_{$url}";

        if (isset($options['body'])) {
            $key .= '_'.md5(json_encode($options['body']) ?: '');
        }

        return 'fetch_'.md5($key);
    }

    /**
     * Clear cache for URL
     */
    public function clearCache(string $url, string $method = 'GET'): bool
    {
        $cacheKey = $this->generateCacheKey($url, $method, []);

        return Cache::forget($cacheKey);
    }

    /**
     * Get fetch statistics
     *
     * @return array{
     *     total_requests: int,
     *     cache_hits: int,
     *     cache_misses: int,
     *     failed_requests: int,
     *     average_response_time: float
     * }
     */
    public function getStatistics(): array
    {
        $rawStats = Cache::get('mcp.fetch.statistics', [
            'total_requests' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'failed_requests' => 0,
            'total_response_time' => 0.0,
        ]);

        // Ensure proper typing from cache
        $stats = \is_array($rawStats) ? $rawStats : [];
        $totalRequests = isset($stats['total_requests']) && is_numeric($stats['total_requests']) ? (int) $stats['total_requests'] : 0;
        $totalResponseTime = isset($stats['total_response_time']) && is_numeric($stats['total_response_time']) ? (float) $stats['total_response_time'] : 0.0;
        $cacheHits = isset($stats['cache_hits']) && is_numeric($stats['cache_hits']) ? (int) $stats['cache_hits'] : 0;
        $cacheMisses = isset($stats['cache_misses']) && is_numeric($stats['cache_misses']) ? (int) $stats['cache_misses'] : 0;
        $failedRequests = isset($stats['failed_requests']) && is_numeric($stats['failed_requests']) ? (int) $stats['failed_requests'] : 0;

        return [
            'total_requests' => $totalRequests,
            'cache_hits' => $cacheHits,
            'cache_misses' => $cacheMisses,
            'failed_requests' => $failedRequests,
            'average_response_time' => $totalRequests > 0 ? $totalResponseTime / $totalRequests : 0.0,
        ];
    }

    /**
     * Track fetch statistics
     */
    protected function trackStatistics(bool $cached, bool $success, float $responseTime): void
    {
        $rawStats = Cache::get('mcp.fetch.statistics', [
            'total_requests' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'failed_requests' => 0,
            'total_response_time' => 0.0,
        ]);

        // Ensure proper typing from cache
        $stats = \is_array($rawStats) ? $rawStats : [];
        $totalRequests = isset($stats['total_requests']) && is_numeric($stats['total_requests']) ? (int) $stats['total_requests'] : 0;
        $cacheHits = isset($stats['cache_hits']) && is_numeric($stats['cache_hits']) ? (int) $stats['cache_hits'] : 0;
        $cacheMisses = isset($stats['cache_misses']) && is_numeric($stats['cache_misses']) ? (int) $stats['cache_misses'] : 0;
        $failedRequests = isset($stats['failed_requests']) && is_numeric($stats['failed_requests']) ? (int) $stats['failed_requests'] : 0;
        $totalResponseTime = isset($stats['total_response_time']) && is_numeric($stats['total_response_time']) ? (float) $stats['total_response_time'] : 0.0;

        $totalRequests++;

        if ($cached) {
            $cacheHits++;
        } else {
            $cacheMisses++;
        }

        if (! $success) {
            $failedRequests++;
        }

        $totalResponseTime += $responseTime;

        // Store statistics for 30 days
        Cache::put('mcp.fetch.statistics', [
            'total_requests' => $totalRequests,
            'cache_hits' => $cacheHits,
            'cache_misses' => $cacheMisses,
            'failed_requests' => $failedRequests,
            'total_response_time' => $totalResponseTime,
        ], now()->addDays(30));
    }

    /**
     * Get service status
     *
     * @return array{
     *     enabled: bool,
     *     available: bool,
     *     server_healthy: bool,
     *     cache_ttl: int,
     *     timeout: int,
     *     max_retries: int
     * }
     */
    public function getStatus(): array
    {
        return [
            'enabled' => $this->enabled,
            'available' => $this->isAvailable(),
            'server_healthy' => $this->mcpClient->isServerHealthy($this->serverName),
            'cache_ttl' => $this->cacheTTL,
            'timeout' => $this->timeout,
            'max_retries' => $this->maxRetries,
        ];
    }
}
