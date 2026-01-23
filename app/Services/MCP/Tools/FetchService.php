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
        $this->cacheTTL = (int) Config::get('mcp.tools.fetch.cache_ttl', 3600);
        $this->timeout = (int) Config::get('mcp.tools.fetch.timeout', 30);
        $this->maxRetries = (int) Config::get('mcp.tools.fetch.max_retries', 3);
        $this->retryDelay = (int) Config::get('mcp.tools.fetch.retry_delay', 1000);
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
     * @return array{
     *     success: bool,
     *     data: mixed,
     *     status_code: int,
     *     headers: array<string, string>,
     *     cached: bool,
     *     attempts: int,
     *     response_time: float
     * }
     */
    public function fetch(): array
        $startTime = microtime(true);
        $cacheKey = $this->generateCacheKey($url, $method, $options);

        // Check cache if enabled
        if ($options['cache'] ?? true) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return [
                    ...$cached,
                    'cached' => true,
                    'response_time' => microtime(true) - $startTime,
                ];
            }
        }

        // Perform fetch with retry logic
        $attempts = 0;
        $lastError = null;

        while ($attempts < $this->maxRetries) {
            $attempts = ($attempts ?? 0) + 1;

            try {
                $response = $this->performFetch($url, $method, $options);

                // Cache successful responses
                if ($response['success'] && ($options['cache'] ?? true)) {
                    $cacheTTL = $options['cache_ttl'] ?? $this->cacheTTL;
                    Cache::put($cacheKey, $response, $cacheTTL);
                }

                $response['cached'] = false;
                $response['attempts'] = $attempts;
                $response['response_time'] = microtime(true) - $startTime;

                return $response;
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

        return [
            'success' => false,
            'data' => null,
            'status_code' => 0,
            'headers' => [],
            'cached' => false,
            'attempts' => $attempts,
            'response_time' => microtime(true) - $startTime,
            'error' => $lastError?->getMessage() ?? 'Unknown error',
        ];
    }

    /**
     * Fetch data from umapyoi.net API
     *
     * @param  array<string, mixed>  $params
     * @return array{
     *     success: bool,
     *     data: mixed,
     *     cached: bool
     * }
     */
    public function fetchUmapyoiData(): array
        $baseUrl = Config::get('external_apis.umapyoi.base_url', 'https://api.umapyoi.net');
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
     * @return array<int, array{
     *     success: bool,
     *     data: mixed,
     *     status_code: int,
     *     cached: bool
     * }>
     */
    public function batchFetch(): array
        $results = [];

        foreach ($requests as $index => $request) {
            $url = $request['url'];
            $method = $request['method'] ?? 'GET';
            $options = $request['options'] ?? [];

            $results[$index] = $this->fetch($url, $method, $options);
        }

        return $results;
    }

    /**
     * Fetch with circuit breaker pattern
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function fetchWithCircuitBreaker(): array
        $circuitKey = "fetch_circuit_{$url}";
        $circuitState = Cache::get($circuitKey, ['state' => 'closed', 'failures' => 0]);

        // Check if circuit is open
        if ($circuitState['state'] === 'open') {
            $timeSinceOpen = time() - ($circuitState['opened_at'] ?? 0);

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
                    'error' => 'Circuit breaker is open',
                ];
            }
        }

        // Perform fetch
        $response = $this->fetch($url, $method, $options);

        // Update circuit state
        if ($response['success']) {
            // Reset circuit on success
            Cache::put($circuitKey, ['state' => 'closed', 'failures' => 0], 300);
        } else {
            // Increment failures
            $circuitState['failures']++;

            // Open circuit if threshold exceeded
            if ($circuitState['failures'] >= 5) {
                $circuitState['state'] = 'open';
                $circuitState['opened_at'] = time();
            }

            Cache::put($circuitKey, $circuitState, 300);
        }

        return $response;
    }

    /**
     * Perform actual HTTP fetch
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function performFetch(): array
        $headers = $options['headers'] ?? [];
        $body = (is_array($options) && isset($options['body']) ? $options['body'] : null);
        $timeout = $options['timeout'] ?? $this->timeout;

        // Build HTTP request
        $request = Http::timeout($timeout);

        // Add headers
        if (! empty($headers)) {
            $request = $request->withHeaders($headers);
        }

        // Perform request
        $response = match (strtoupper($method)) {
            'GET' => $request->get($url),
            'POST' => $request->post($url, $body),
            'PUT' => $request->put($url, $body),
            'PATCH' => $request->patch($url, $body),
            'DELETE' => $request->delete($url),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        // Parse response
        $success = $response->successful();
        $statusCode = $response->status();
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
        // TODO: Implement actual statistics tracking
        return [
            'total_requests' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'failed_requests' => 0,
            'average_response_time' => 0.0,
        ];
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
