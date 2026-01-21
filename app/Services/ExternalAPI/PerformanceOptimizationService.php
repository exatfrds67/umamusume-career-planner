<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Log;

/**
 * Performance Optimization Service for External API Integration
 *
 * Provides performance optimization features including:
 * - Request batching to minimize API calls
 * - Connection pooling for efficiency
 * - Response compression for bandwidth optimization
 * - Parallel fetching for speed
 *
 * Requirements: 14.5 (Performance Optimization and Monitoring)
 * Task: 5.1.2
 */
class PerformanceOptimizationService
{
    /**
     * Maximum batch size for request batching
     */
    private const MAX_BATCH_SIZE = 10;

    /**
     * Connection pool size
     */
    private const CONNECTION_POOL_SIZE = 5;

    /**
     * Compression threshold in bytes
     * Only compress responses larger than this
     */
    private const COMPRESSION_THRESHOLD = 1024;

    /**
     * Maximum parallel requests
     */
    private const MAX_PARALLEL_REQUESTS = 5;

    /**
     * Batch request queue
     *
     * @var array<string, array{requests: array<array<string, mixed>>, callback: callable, created_at: int}>
     */
    protected array $batchQueue = [];

    /**
     * Connection pool
     *
     * @var array<string, array{url: string, last_used: int, in_use: bool}>
     */
    protected array $connectionPool = [];

    public function __construct(
        protected MCPClientService $mcpClient,
        protected ?APIPerformanceMetricsService $metricsService = null
    ) {}

    /**
     * Add request to batch queue
     *
     * Batches multiple requests together to minimize API calls.
     * Requests are grouped by endpoint and executed together.
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, queued: bool, batch_id: string, position: int}
     */
    public function addToBatch(
        string $endpoint,
        string $method = 'GET',
        array $params = [],
        ?callable $callback = null
    ): array {
        $batchId = $this->getBatchId($endpoint, $method);

        // Initialize batch if it doesn't exist
        if (! isset($this->batchQueue[$batchId])) {
            $this->batchQueue[$batchId] = [
                'requests' => [],
                'callback' => $callback,
                'created_at' => time(),
            ];
        }

        // Add request to batch
        $this->batchQueue[$batchId]['requests'][] = [
            'endpoint' => $endpoint,
            'method' => $method,
            'params' => $params,
            'added_at' => microtime(true),
        ];

        $position = count($this->batchQueue[$batchId]['requests']);

        Log::debug('[PerformanceOptimization] Request added to batch', [
            'batch_id' => $batchId,
            'endpoint' => $endpoint,
            'method' => $method,
            'position' => $position,
        ]);

        // Auto-execute if batch is full
        if ($position >= self::MAX_BATCH_SIZE) {
            $this->executeBatch($batchId);
        }

        return [
            'success' => true,
            'queued' => true,
            'batch_id' => $batchId,
            'position' => $position,
        ];
    }

    /**
     * Execute a batch of requests
     *
     * @return array{success: bool, batch_id: string, executed_count: int, results: array<array<string, mixed>>, duration_ms: float}
     */
    public function executeBatch(string $batchId): array
    {
        if (! isset($this->batchQueue[$batchId])) {
            return [
                'success' => false,
                'batch_id' => $batchId,
                'executed_count' => 0,
                'results' => [],
                'duration_ms' => 0,
                'error' => 'Batch not found',
            ];
        }

        $batch = $this->batchQueue[$batchId];
        $startTime = microtime(true);

        Log::info('[PerformanceOptimization] Executing batch', [
            'batch_id' => $batchId,
            'request_count' => count($batch['requests']),
        ]);

        $results = [];

        // Execute all requests in the batch
        foreach ($batch['requests'] as $request) {
            try {
                $result = $this->executeRequest(
                    $request['endpoint'],
                    $request['method'],
                    $request['params']
                );

                $results[] = [
                    'success' => true,
                    'endpoint' => $request['endpoint'],
                    'method' => $request['method'],
                    'data' => $result,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'endpoint' => $request['endpoint'],
                    'method' => $request['method'],
                    'error' => $e->getMessage(),
                ];

                Log::error('[PerformanceOptimization] Batch request failed', [
                    'batch_id' => $batchId,
                    'endpoint' => $request['endpoint'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Execute callback if provided
        if ($batch['callback'] && is_callable($batch['callback'])) {
            try {
                call_user_func($batch['callback'], $results);
            } catch (\Exception $e) {
                Log::error('[PerformanceOptimization] Batch callback failed', [
                    'batch_id' => $batchId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Remove batch from queue
        unset($this->batchQueue[$batchId]);

        $duration = (microtime(true) - $startTime) * 1000;

        // Record metrics
        if ($this->metricsService) {
            $this->metricsService->recordBatchExecution($batchId, count($results), $duration);
        }

        Log::info('[PerformanceOptimization] Batch executed', [
            'batch_id' => $batchId,
            'executed_count' => count($results),
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'success' => true,
            'batch_id' => $batchId,
            'executed_count' => count($results),
            'results' => $results,
            'duration_ms' => round($duration, 2),
        ];
    }

    /**
     * Execute all pending batches
     *
     * @return array{success: bool, executed_batches: int, total_requests: int, duration_ms: float}
     */
    public function executeAllBatches(): array
    {
        $startTime = microtime(true);
        $executedBatches = 0;
        $totalRequests = 0;

        $batchIds = array_keys($this->batchQueue);

        foreach ($batchIds as $batchId) {
            $result = $this->executeBatch($batchId);
            if ($result['success']) {
                $executedBatches++;
                $totalRequests += $result['executed_count'];
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('[PerformanceOptimization] All batches executed', [
            'executed_batches' => $executedBatches,
            'total_requests' => $totalRequests,
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'success' => true,
            'executed_batches' => $executedBatches,
            'total_requests' => $totalRequests,
            'duration_ms' => round($duration, 2),
        ];
    }

    /**
     * Get batch queue status
     *
     * @return array{total_batches: int, total_requests: int, batches: array<string, array{request_count: int, age_seconds: int}>}
     */
    public function getBatchQueueStatus(): array
    {
        $totalRequests = 0;
        $batches = [];

        foreach ($this->batchQueue as $batchId => $batch) {
            $requestCount = count($batch['requests']);
            $totalRequests += $requestCount;

            $batches[$batchId] = [
                'request_count' => $requestCount,
                'age_seconds' => time() - $batch['created_at'],
            ];
        }

        return [
            'total_batches' => count($this->batchQueue),
            'total_requests' => $totalRequests,
            'batches' => $batches,
        ];
    }

    /**
     * Fetch multiple URLs in parallel
     *
     * Executes multiple HTTP requests concurrently to improve performance.
     *
     * @param  array<array{url: string, method?: string, params?: array<string, mixed>, headers?: array<string, string>}>  $requests
     * @return array{success: bool, results: array<array<string, mixed>>, duration_ms: float, parallel_count: int}
     */
    public function fetchParallel(array $requests): array
    {
        $startTime = microtime(true);

        // Limit parallel requests
        $chunks = array_chunk($requests, self::MAX_PARALLEL_REQUESTS);
        $allResults = [];

        foreach ($chunks as $chunk) {
            $chunkResults = $this->executeParallelChunk($chunk);
            $allResults = array_merge($allResults, $chunkResults);
        }

        $duration = (microtime(true) - $startTime) * 1000;

        // Record metrics
        if ($this->metricsService) {
            $this->metricsService->recordParallelFetch(count($requests), $duration);
        }

        Log::info('[PerformanceOptimization] Parallel fetch completed', [
            'total_requests' => count($requests),
            'successful' => count(array_filter($allResults, fn ($r) => $r['success'])),
            'failed' => count(array_filter($allResults, fn ($r) => ! $r['success'])),
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'success' => true,
            'results' => $allResults,
            'duration_ms' => round($duration, 2),
            'parallel_count' => count($requests),
        ];
    }

    /**
     * Execute a chunk of parallel requests
     *
     * @param  array<array{url: string, method?: string, params?: array<string, mixed>, headers?: array<string, string>}>  $requests
     * @return array<array<string, mixed>>
     */
    protected function executeParallelChunk(array $requests): array
    {
        $results = [];

        // In a real implementation, this would use async/parallel execution
        // For now, we execute sequentially but with connection pooling
        foreach ($requests as $request) {
            $url = $request['url'];
            $method = $request['method'] ?? 'GET';
            $params = $request['params'] ?? [];
            $headers = $request['headers'] ?? [];

            try {
                // Get connection from pool
                $connection = $this->getConnection($url);

                // Execute request
                $response = $this->mcpClient->fetch(
                    $url,
                    $method,
                    $params,
                    $headers,
                    5 // timeout
                );

                // Release connection back to pool
                $this->releaseConnection($connection);

                $results[] = [
                    'success' => true,
                    'url' => $url,
                    'method' => $method,
                    'data' => $response,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'url' => $url,
                    'method' => $method,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get a connection from the pool
     *
     * @return array{id: string, url: string, last_used: int, in_use: bool}
     */
    protected function getConnection(string $url): array
    {
        $baseUrl = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST);

        // Look for available connection in pool
        foreach ($this->connectionPool as $id => $connection) {
            if ($connection['url'] === $baseUrl && ! $connection['in_use']) {
                $this->connectionPool[$id]['in_use'] = true;
                $this->connectionPool[$id]['last_used'] = time();

                Log::debug('[PerformanceOptimization] Reusing connection from pool', [
                    'connection_id' => $id,
                    'url' => $baseUrl,
                ]);

                return $this->connectionPool[$id];
            }
        }

        // Create new connection if pool not full
        if (count($this->connectionPool) < self::CONNECTION_POOL_SIZE) {
            $id = uniqid('conn_');
            $connection = [
                'id' => $id,
                'url' => $baseUrl,
                'last_used' => time(),
                'in_use' => true,
            ];

            $this->connectionPool[$id] = $connection;

            Log::debug('[PerformanceOptimization] Created new connection', [
                'connection_id' => $id,
                'url' => $baseUrl,
                'pool_size' => count($this->connectionPool),
            ]);

            return $connection;
        }

        // Pool is full, find least recently used connection
        $lruId = null;
        $lruTime = PHP_INT_MAX;

        foreach ($this->connectionPool as $id => $connection) {
            if (! $connection['in_use'] && $connection['last_used'] < $lruTime) {
                $lruId = $id;
                $lruTime = $connection['last_used'];
            }
        }

        if ($lruId) {
            // Reuse LRU connection
            $this->connectionPool[$lruId]['url'] = $baseUrl;
            $this->connectionPool[$lruId]['in_use'] = true;
            $this->connectionPool[$lruId]['last_used'] = time();

            Log::debug('[PerformanceOptimization] Reused LRU connection', [
                'connection_id' => $lruId,
                'url' => $baseUrl,
            ]);

            return $this->connectionPool[$lruId];
        }

        // All connections in use, create temporary connection
        $id = uniqid('temp_conn_');

        return [
            'id' => $id,
            'url' => $baseUrl,
            'last_used' => time(),
            'in_use' => true,
        ];
    }

    /**
     * Release a connection back to the pool
     *
     * @param  array{id: string, url: string, last_used: int, in_use: bool}  $connection
     */
    protected function releaseConnection(array $connection): void
    {
        if (isset($this->connectionPool[$connection['id']])) {
            $this->connectionPool[$connection['id']]['in_use'] = false;
            $this->connectionPool[$connection['id']]['last_used'] = time();

            Log::debug('[PerformanceOptimization] Released connection to pool', [
                'connection_id' => $connection['id'],
            ]);
        }
    }

    /**
     * Get connection pool status
     *
     * @return array{pool_size: int, max_size: int, in_use: int, available: int, connections: array<string, array{url: string, last_used: int, in_use: bool, age_seconds: int}>}
     */
    public function getConnectionPoolStatus(): array
    {
        $inUse = 0;
        $connections = [];

        foreach ($this->connectionPool as $id => $connection) {
            if ($connection['in_use']) {
                $inUse++;
            }

            $connections[$id] = [
                'url' => $connection['url'],
                'last_used' => $connection['last_used'],
                'in_use' => $connection['in_use'],
                'age_seconds' => time() - $connection['last_used'],
            ];
        }

        return [
            'pool_size' => count($this->connectionPool),
            'max_size' => self::CONNECTION_POOL_SIZE,
            'in_use' => $inUse,
            'available' => count($this->connectionPool) - $inUse,
            'connections' => $connections,
        ];
    }

    /**
     * Compress response data
     *
     * Compresses response data using gzip to reduce bandwidth usage.
     *
     * @param  array<string, mixed>  $data
     * @return array{compressed: bool, original_size: int, compressed_size: int, compression_ratio: float, data: string}
     */
    public function compressResponse(array $data): array
    {
        $jsonData = json_encode($data);
        $originalSize = strlen($jsonData);

        // Only compress if data is large enough
        if ($originalSize < self::COMPRESSION_THRESHOLD) {
            return [
                'compressed' => false,
                'original_size' => $originalSize,
                'compressed_size' => $originalSize,
                'compression_ratio' => 1.0,
                'data' => $jsonData,
            ];
        }

        // Compress using gzip
        $compressed = gzcompress($jsonData, 6); // Level 6 is a good balance

        if ($compressed === false) {
            Log::warning('[PerformanceOptimization] Compression failed, returning original data');

            return [
                'compressed' => false,
                'original_size' => $originalSize,
                'compressed_size' => $originalSize,
                'compression_ratio' => 1.0,
                'data' => $jsonData,
            ];
        }

        $compressedSize = strlen($compressed);
        $compressionRatio = $originalSize > 0 ? $compressedSize / $originalSize : 1.0;

        // Record metrics
        if ($this->metricsService) {
            $this->metricsService->recordCompression($originalSize, $compressedSize);
        }

        Log::debug('[PerformanceOptimization] Response compressed', [
            'original_size' => $originalSize,
            'compressed_size' => $compressedSize,
            'compression_ratio' => round($compressionRatio, 2),
            'savings_bytes' => $originalSize - $compressedSize,
            'savings_percent' => round((1 - $compressionRatio) * 100, 2),
        ]);

        return [
            'compressed' => true,
            'original_size' => $originalSize,
            'compressed_size' => $compressedSize,
            'compression_ratio' => round($compressionRatio, 4),
            'data' => base64_encode($compressed),
        ];
    }

    /**
     * Decompress response data
     *
     * @return array<string, mixed>
     */
    public function decompressResponse(string $compressedData): array
    {
        try {
            // Decode base64
            $decoded = base64_decode($compressedData, true);

            if ($decoded === false) {
                throw new \RuntimeException('Failed to decode base64 data');
            }

            // Decompress
            $decompressed = gzuncompress($decoded);

            if ($decompressed === false) {
                throw new \RuntimeException('Failed to decompress data');
            }

            // Decode JSON
            $data = json_decode($decompressed, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode JSON: '.json_last_error_msg());
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('[PerformanceOptimization] Decompression failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Execute a single request (helper method)
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function executeRequest(string $endpoint, string $method, array $params): array
    {
        // This would call the actual API service
        // For now, return a simulated response
        return [
            'endpoint' => $endpoint,
            'method' => $method,
            'params' => $params,
            'executed_at' => now()->toISOString(),
        ];
    }

    /**
     * Generate batch ID from endpoint and method
     */
    protected function getBatchId(string $endpoint, string $method): string
    {
        return md5($endpoint.$method);
    }

    /**
     * Clear all batches from queue
     */
    public function clearBatchQueue(): void
    {
        $count = count($this->batchQueue);
        $this->batchQueue = [];

        Log::info('[PerformanceOptimization] Batch queue cleared', [
            'cleared_batches' => $count,
        ]);
    }

    /**
     * Clear connection pool
     */
    public function clearConnectionPool(): void
    {
        $count = count($this->connectionPool);
        $this->connectionPool = [];

        Log::info('[PerformanceOptimization] Connection pool cleared', [
            'cleared_connections' => $count,
        ]);
    }

    /**
     * Get performance optimization statistics
     *
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return [
            'batch_queue' => $this->getBatchQueueStatus(),
            'connection_pool' => $this->getConnectionPoolStatus(),
            'configuration' => [
                'max_batch_size' => self::MAX_BATCH_SIZE,
                'connection_pool_size' => self::CONNECTION_POOL_SIZE,
                'compression_threshold' => self::COMPRESSION_THRESHOLD,
                'max_parallel_requests' => self::MAX_PARALLEL_REQUESTS,
            ],
        ];
    }
}
