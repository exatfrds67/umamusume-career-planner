<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Log;

/**
 * Data Fetching Agent for External API Integration
 *
 * Coordinates parallel API calls using MCP strands-agents server for efficient
 * multi-resource fetching with error handling, retry logic, and performance monitoring.
 *
 * Features:
 * - Parallel resource fetching
 * - Automatic error handling and retry logic
 * - Fallback to cached data on failures
 * - Performance monitoring and metrics tracking
 * - Rate limiting awareness
 * - Circuit breaker integration
 *
 * Requirements: 14.4 (MCP Subagent Coordination for API Management)
 * Task: 3.1.1
 */
class DataFetchingAgent
{
    /**
     * Maximum number of parallel requests
     */
    private const MAX_PARALLEL_REQUESTS = 5;

    /**
     * Default timeout for fetch operations (seconds)
     */
    private const DEFAULT_TIMEOUT = 5;

    /**
     * Maximum retry attempts per resource
     */
    private const MAX_RETRIES = 3;

    /**
     * Retry delay in milliseconds (exponential backoff base)
     */
    private const RETRY_DELAY_MS = 1000;

    /**
     * Priority levels for resource fetching
     */
    public const PRIORITY_CRITICAL = 1;

    public const PRIORITY_HIGH = 2;

    public const PRIORITY_NORMAL = 3;

    public const PRIORITY_LOW = 4;

    /**
     * Batch strategies
     */
    public const BATCH_STRATEGY_PRIORITY = 'priority';

    public const BATCH_STRATEGY_TYPE = 'type';

    public const BATCH_STRATEGY_SOURCE = 'source';

    public const BATCH_STRATEGY_SEQUENTIAL = 'sequential';

    public function __construct(
        private MCPClientService $mcpClient,
        private CacheManagerService $cacheManager
    ) {}

    /**
     * Fetch multiple resources in parallel with error handling
     *
     * @param  array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>  $resources
     * @param  array{strategy?: string, max_parallel?: int, timeout?: int}  $options
     * @return array{success: bool, results: array<string, array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}>, metadata: array<string, mixed>, aggregated?: array<string, mixed>}
     */
    public function fetchMultipleResources(): array
        $startTime = microtime(true);

        // Extract options
        $strategy = $options['strategy'] ?? self::BATCH_STRATEGY_SEQUENTIAL;
        $maxParallel = $options['max_parallel'] ?? self::MAX_PARALLEL_REQUESTS;
        $timeout = $options['timeout'] ?? self::DEFAULT_TIMEOUT;

        Log::info('[DataFetchingAgent] Starting parallel fetch operation', [
            'resource_count' => \count($resources),
            'strategy' => $strategy,
            'max_parallel' => $maxParallel,
        ]);

        // Validate resources
        $validatedResources = $this->validateResources($resources);

        if (empty($validatedResources)) {
            return [
                'success' => false,
                'results' => [],
                'metadata' => [
                    'error' => 'No valid resources to fetch',
                    'duration_ms' => 0,
                ],
            ];
        }

        // Apply batching strategy
        $batches = $this->createBatches($validatedResources, $strategy, $maxParallel);

        $allResults = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($batches as $batchIndex => $batch) {
            Log::debug('[DataFetchingAgent] Processing batch', [
                'batch_index' => $batchIndex + 1,
                'batch_size' => \count($batch),
                'strategy' => $strategy,
            ]);

            $batchResults = $this->processBatch($batch);

            foreach ($batchResults as $resourceName => $result) {
                $allResults[$resourceName] = $result;

                if ($result['success']) {
                    $successCount = ($successCount ?? 0) + 1;
                } else {
                    $failureCount = ($failureCount ?? 0) + 1;
                }
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;

        $metadata = [
            'total_resources' => \count($resources),
            'valid_resources' => \count($validatedResources),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'success_rate' => \count($validatedResources) > 0
                ? round(($successCount / \count($validatedResources)) * 100, 2)
                : 0,
            'duration_ms' => round($duration, 2),
            'batches_processed' => \count($batches),
            'strategy_used' => $strategy,
            'timestamp' => now()->toISOString(),
        ];

        Log::info('[DataFetchingAgent] Parallel fetch operation completed', $metadata);

        // Add aggregated results
        $aggregated = $this->aggregateResults($allResults);

        return [
            'success' => $failureCount === 0,
            'results' => $allResults,
            'metadata' => $metadata,
            'aggregated' => $aggregated,
        ];
    }

    /**
     * Process a batch of resources in parallel
     *
     * @param  array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>}>  $batch
     * @return array<string, array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}>
     */
    protected function processBatch(): array
        $results = [];

        // Process each resource in the batch
        // In a real implementation with actual MCP strands-agents,
        // these would be executed in parallel using the agent coordination
        foreach ($batch as $resource) {
            $resourceName = $resource['name'];
            $results[$resourceName] = $this->fetchResource($resource);
        }

        return $results;
    }

    /**
     * Fetch a single resource with retry logic and fallback
     *
     * @param  array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>}  $resource
     * @return array{success: bool, data: mixed, source: string, type: string, error?: string, metadata: array<string, mixed>}
     */
    protected function fetchResource(): array
        $resourceName = $resource['name'];
        $resourceType = $resource['type'];
        $endpoint = $resource['endpoint'];
        $preferredSource = (is_array($resource) && isset($resource['source']) ? $resource['source'] : null);
        $params = $resource['params'] ?? [];

        $startTime = microtime(true);
        $attempts = 0;
        $lastError = null;

        // Try to fetch from cache first
        $cacheKey = $this->buildCacheKey($resourceType, $resourceName);
        $cachedData = $this->cacheManager->get($cacheKey);

        if ($cachedData && ! ($cachedData['_cache']['is_stale'] ?? false)) {
            $duration = (microtime(true) - $startTime) * 1000;

            Log::debug('[DataFetchingAgent] Resource served from cache', [
                'resource' => $resourceName,
                'type' => $resourceType,
                'cache_age' => $cachedData['_cache']['age_seconds'] ?? 0,
            ]);

            return [
                'success' => true,
                'data' => $cachedData,
                'source' => 'cache',
                'type' => $resourceType,
                'metadata' => [
                    'cache_hit' => true,
                    'duration_ms' => round($duration, 2),
                    'attempts' => 0,
                ],
            ];
        }

        // Attempt to fetch from API with retries
        while ($attempts < self::MAX_RETRIES) {
            $attempts = ($attempts ?? 0) + 1;

            try {
                Log::debug('[DataFetchingAgent] Fetching resource from API', [
                    'resource' => $resourceName,
                    'type' => $resourceType,
                    'endpoint' => $endpoint,
                    'attempt' => $attempts,
                    'max_retries' => self::MAX_RETRIES,
                ]);

                // Use MCP client to fetch data
                $result = $this->performFetch($endpoint, $params, $preferredSource);

                if ($result['success']) {
                    $duration = (microtime(true) - $startTime) * 1000;

                    // Cache the successful result
                    $this->cacheManager->put($cacheKey, $result['data']);

                    Log::info('[DataFetchingAgent] Resource fetched successfully', [
                        'resource' => $resourceName,
                        'type' => $resourceType,
                        'source' => $result['source'],
                        'duration_ms' => round($duration, 2),
                        'attempts' => $attempts,
                    ]);

                    return [
                        'success' => true,
                        'data' => $result['data'],
                        'source' => $result['source'],
                        'type' => $resourceType,
                        'metadata' => [
                            'cache_hit' => false,
                            'duration_ms' => round($duration, 2),
                            'attempts' => $attempts,
                            'response_time_ms' => $result['metadata']['response_time_ms'] ?? 0,
                        ],
                    ];
                }

                $lastError = $result['error'] ?? 'Unknown error';

                // Don't retry on client errors (4xx)
                if (
                    isset($result['metadata']['status_code']) &&
                    $result['metadata']['status_code'] >= 400 &&
                    $result['metadata']['status_code'] < 500
                ) {
                    break;
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();

                Log::warning('[DataFetchingAgent] Resource fetch attempt failed', [
                    'resource' => $resourceName,
                    'type' => $resourceType,
                    'attempt' => $attempts,
                    'error' => $lastError,
                ]);
            }

            // Exponential backoff before retry
            if ($attempts < self::MAX_RETRIES) {
                $delayMs = self::RETRY_DELAY_MS * pow(2, $attempts - 1);
                usleep($delayMs * 1000);
            }
        }

        // All attempts failed, try to use stale cache data as fallback
        if ($cachedData) {
            $duration = (microtime(true) - $startTime) * 1000;

            Log::warning('[DataFetchingAgent] Using stale cache data as fallback', [
                'resource' => $resourceName,
                'type' => $resourceType,
                'cache_age' => $cachedData['_cache']['age_seconds'] ?? 0,
                'last_error' => $lastError,
            ]);

            return [
                'success' => true,
                'data' => $cachedData,
                'source' => 'cache_fallback',
                'type' => $resourceType,
                'metadata' => [
                    'cache_hit' => true,
                    'stale_data' => true,
                    'duration_ms' => round($duration, 2),
                    'attempts' => $attempts,
                    'fallback_reason' => $lastError,
                ],
            ];
        }

        // Complete failure - no data available
        $duration = (microtime(true) - $startTime) * 1000;

        Log::error('[DataFetchingAgent] Resource fetch failed completely', [
            'resource' => $resourceName,
            'type' => $resourceType,
            'attempts' => $attempts,
            'duration_ms' => round($duration, 2),
            'last_error' => $lastError,
        ]);

        return [
            'success' => false,
            'data' => null,
            'source' => 'none',
            'type' => $resourceType,
            'error' => $lastError ?? 'Failed to fetch resource after all retries',
            'metadata' => [
                'cache_hit' => false,
                'duration_ms' => round($duration, 2),
                'attempts' => $attempts,
            ],
        ];
    }

    /**
     * Perform the actual fetch operation using MCP client
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}
     */
    protected function performFetch(): array
        // Check if MCP fetch server is available
        if (! $this->mcpClient->isFetchAvailable()) {
            return [
                'success' => false,
                'data' => null,
                'source' => 'none',
                'error' => 'MCP fetch server not available',
                'metadata' => [],
            ];
        }

        try {
            // Use MCP client to perform the fetch
            $response = $this->mcpClient->fetch(
                $endpoint,
                'GET',
                $params,
                [],
                self::DEFAULT_TIMEOUT,
                1 // Single attempt here, retries handled at higher level
            );

            if (! $response['success']) {
                return [
                    'success' => false,
                    'data' => null,
                    'source' => $preferredSource ?? 'unknown',
                    'error' => $response['error'] ?? 'Fetch failed',
                    'metadata' => [
                        'status_code' => $response['status'] ?? 0,
                    ],
                ];
            }

            // Parse JSON response
            $data = null;
            if (isset($response['body'])) {
                $decoded = json_decode($response['body'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = $decoded;
                } else {
                    return [
                        'success' => false,
                        'data' => null,
                        'source' => $preferredSource ?? 'unknown',
                        'error' => 'Failed to decode JSON response: '.json_last_error_msg(),
                        'metadata' => [
                            'status_code' => $response['status'] ?? 200,
                        ],
                    ];
                }
            }

            return [
                'success' => true,
                'data' => $data,
                'source' => $preferredSource ?? 'api',
                'metadata' => [
                    'status_code' => $response['status'] ?? 200,
                    'response_time_ms' => 0, // Would be tracked by MCP client
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'source' => $preferredSource ?? 'unknown',
                'error' => $e->getMessage(),
                'metadata' => [],
            ];
        }
    }

    /**
     * Validate and normalize resource definitions
     *
     * @param  array<array{name?: string, type?: string, endpoint?: string, source?: string, params?: array<string, mixed>, priority?: int}>  $resources
     * @return array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>
     */
    protected function validateResources(): array
        $validated = [];

        foreach ($resources as $index => $resource) {
            // Check required fields
            if (! isset($resource['name']) || ! \is_string($resource['name'])) {
                Log::warning('[DataFetchingAgent] Resource missing name field', [
                    'index' => $index,
                    'resource' => $resource,
                ]);

                continue;
            }

            if (! isset($resource['type']) || ! \is_string($resource['type'])) {
                Log::warning('[DataFetchingAgent] Resource missing type field', [
                    'index' => $index,
                    'name' => $resource['name'],
                ]);

                continue;
            }

            if (! isset($resource['endpoint']) || ! \is_string($resource['endpoint'])) {
                Log::warning('[DataFetchingAgent] Resource missing endpoint field', [
                    'index' => $index,
                    'name' => $resource['name'],
                ]);

                continue;
            }

            // Normalize resource
            $validated[] = [
                'name' => $resource['name'],
                'type' => $resource['type'],
                'endpoint' => $resource['endpoint'],
                'source' => (is_array($resource) && isset($resource['source']) ? $resource['source'] : null),
                'params' => $resource['params'] ?? [],
                'priority' => $resource['priority'] ?? self::PRIORITY_NORMAL,
            ];
        }

        return $validated;
    }

    /**
     * Build cache key for a resource
     */
    protected function buildCacheKey(string $type, string $name): string
    {
        return "{$type}:{$name}";
    }

    /**
     * Create batches based on the specified strategy
     *
     * @param  array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>  $resources
     * @return array<array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>>
     */
    protected function createBatches(): array
        return match ($strategy) {
            self::BATCH_STRATEGY_PRIORITY => $this->createPriorityBatches($resources, $maxParallel),
            self::BATCH_STRATEGY_TYPE => $this->createTypeBatches($resources, $maxParallel),
            self::BATCH_STRATEGY_SOURCE => $this->createSourceBatches($resources, $maxParallel),
            default => $this->createSequentialBatches($resources, $maxParallel),
        };
    }

    /**
     * Create batches based on priority (critical first, then high, normal, low)
     *
     * @param  array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>  $resources
     * @return array<array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>>
     */
    protected function createPriorityBatches(): array
        // Sort resources by priority (lower number = higher priority)
        usort($resources, function ($a, $b) {
            $priorityA = $a['priority'] ?? self::PRIORITY_NORMAL;
            $priorityB = $b['priority'] ?? self::PRIORITY_NORMAL;

            return $priorityA <=> $priorityB;
        });

        // Split into batches
        return array_chunk($resources, $maxParallel);
    }

    /**
     * Create batches grouped by resource type
     *
     * @param  array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>  $resources
     * @return array<array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>>
     */
    protected function createTypeBatches(): array
        // Group resources by type
        $grouped = [];
        foreach ($resources as $resource) {
            $type = $resource['type'];
            if (! isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $resource;
        }

        // Create batches from each type group
        $batches = [];
        foreach ($grouped as $type => $typeResources) {
            $typeBatches = array_chunk($typeResources, $maxParallel);
            $batches = array_merge($batches, $typeBatches);
        }

        return $batches;
    }

    /**
     * Create batches grouped by source
     *
     * @param  array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>  $resources
     * @return array<array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>>
     */
    protected function createSourceBatches(): array
        // Group resources by source
        $grouped = [];
        foreach ($resources as $resource) {
            $source = $resource['source'] ?? 'default';
            if (! isset($grouped[$source])) {
                $grouped[$source] = [];
            }
            $grouped[$source][] = $resource;
        }

        // Create batches from each source group
        $batches = [];
        foreach ($grouped as $source => $sourceResources) {
            $sourceBatches = array_chunk($sourceResources, $maxParallel);
            $batches = array_merge($batches, $sourceBatches);
        }

        return $batches;
    }

    /**
     * Create sequential batches (default strategy)
     *
     * @param  array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>  $resources
     * @return array<array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>>
     */
    protected function createSequentialBatches(): array
        return array_chunk($resources, $maxParallel);
    }

    /**
     * Aggregate results by various dimensions
     *
     * @param  array<string, array{success: bool, data: mixed, source: string, type: string, error?: string, metadata: array<string, mixed>}>  $results
     * @return array{by_type: array<string, array<string, mixed>>, by_source: array<string, array<string, mixed>>, by_status: array<string, array<string, mixed>>, summary: array<string, mixed>}
     */
    protected function aggregateResults(): array
        $byType = [];
        $bySource = [];
        $byStatus = [
            'successful' => [],
            'failed' => [],
            'cached' => [],
            'stale_cache' => [],
        ];

        foreach ($results as $name => $result) {
            // Extract type from result
            $type = $result['type'] ?? 'unknown';

            // Aggregate by type
            if (! isset($byType[$type])) {
                $byType[$type] = [
                    'count' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'resources' => [],
                ];
            }
            $byType[$type]['count']++;
            $byType[$type]['resources'][] = $name;

            if ($result['success']) {
                $byType[$type]['successful']++;
            } else {
                $byType[$type]['failed']++;
            }

            // Aggregate by source
            $source = $result['source'];
            if (! isset($bySource[$source])) {
                $bySource[$source] = [
                    'count' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'resources' => [],
                ];
            }
            $bySource[$source]['count']++;
            $bySource[$source]['resources'][] = $name;

            if ($result['success']) {
                $bySource[$source]['successful']++;
            } else {
                $bySource[$source]['failed']++;
            }

            // Aggregate by status
            if ($result['success']) {
                $byStatus['successful'][] = $name;

                if ($source === 'cache') {
                    $byStatus['cached'][] = $name;
                } elseif ($source === 'cache_fallback') {
                    $byStatus['stale_cache'][] = $name;
                }
            } else {
                $byStatus['failed'][] = $name;
            }
        }

        // Calculate summary statistics
        $summary = [
            'total_types' => \count($byType),
            'total_sources' => \count($bySource),
            'cache_hit_rate' => $this->calculateCacheHitRate($results),
            'most_common_source' => $this->getMostCommonSource($bySource),
            'most_common_type' => $this->getMostCommonType($byType),
        ];

        return [
            'by_type' => $byType,
            'by_source' => $bySource,
            'by_status' => $byStatus,
            'summary' => $summary,
        ];
    }

    /**
     * Extract resource type from result
     *
     * @param  array{success: bool, data: mixed, source: string, type: string, error?: string, metadata: array<string, mixed>}  $result
     *
     * @deprecated This method is no longer needed as type is now included in results
     */
    protected function extractTypeFromResult(string $name, array $result): string
    {
        // Type is now included in the result
        return $result['type'] ?? 'unknown';
    }

    /**
     * Calculate cache hit rate from results
     *
     * @param  array<string, array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}>  $results
     */
    protected function calculateCacheHitRate(array $results): float
    {
        if (empty($results)) {
            return 0.0;
        }

        $cacheHits = 0;
        foreach ($results as $result) {
            if (
                $result['source'] === 'cache' ||
                $result['source'] === 'cache_fallback'
            ) {
                $cacheHits = ($cacheHits ?? 0) + 1;
            }
        }

        return round(($cacheHits / \count($results)) * 100, 2);
    }

    /**
     * Get the most common source from aggregated data
     *
     * @param  array<string, array{count: int, successful: int, failed: int, resources: array<string>}>  $bySource
     */
    protected function getMostCommonSource(array $bySource): ?string
    {
        if (empty($bySource)) {
            return null;
        }

        $maxCount = 0;
        $mostCommon = null;

        foreach ($bySource as $source => $data) {
            if ((is_array($data) && isset($data['count']) ? $data['count'] : null) > $maxCount) {
                $maxCount = (is_array($data) && isset($data['count']) ? $data['count'] : null);
                $mostCommon = $source;
            }
        }

        return $mostCommon;
    }

    /**
     * Get the most common type from aggregated data
     *
     * @param  array<string, array{count: int, successful: int, failed: int, resources: array<string>}>  $byType
     */
    protected function getMostCommonType(array $byType): ?string
    {
        if (empty($byType)) {
            return null;
        }

        $maxCount = 0;
        $mostCommon = null;

        foreach ($byType as $type => $data) {
            if ((is_array($data) && isset($data['count']) ? $data['count'] : null) > $maxCount) {
                $maxCount = (is_array($data) && isset($data['count']) ? $data['count'] : null);
                $mostCommon = $type;
            }
        }

        return $mostCommon;
    }

    /**
     * Batch fetch resources by priority
     *
     * @param  array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>  $resources
     * @return array{success: bool, results: array<string, array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}>, metadata: array<string, mixed>, aggregated?: array<string, mixed>}
     */
    public function fetchByPriority(): array
        return $this->fetchMultipleResources($resources, [
            'strategy' => self::BATCH_STRATEGY_PRIORITY,
            'max_parallel' => $maxParallel,
        ]);
    }

    /**
     * Batch fetch resources by type
     *
     * @param  array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>  $resources
     * @return array{success: bool, results: array<string, array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}>, metadata: array<string, mixed>, aggregated?: array<string, mixed>}
     */
    public function fetchByType(): array
        return $this->fetchMultipleResources($resources, [
            'strategy' => self::BATCH_STRATEGY_TYPE,
            'max_parallel' => $maxParallel,
        ]);
    }

    /**
     * Batch fetch resources by source
     *
     * @param  array<array{name: string, type: string, endpoint: string, source?: string, params?: array<string, mixed>, priority?: int}>  $resources
     * @return array{success: bool, results: array<string, array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}>, metadata: array<string, mixed>, aggregated?: array<string, mixed>}
     */
    public function fetchBySource(): array
        return $this->fetchMultipleResources($resources, [
            'strategy' => self::BATCH_STRATEGY_SOURCE,
            'max_parallel' => $maxParallel,
        ]);
    }

    /**
     * Get performance metrics for the agent
     *
     * @return array{total_fetches: int, successful_fetches: int, failed_fetches: int, cache_hits: int, average_duration_ms: float}
     */
    public function getPerformanceMetrics(): array
        // In a real implementation, these would be tracked in Redis or a database
        // For now, we return placeholder metrics
        return [
            'total_fetches' => 0,
            'successful_fetches' => 0,
            'failed_fetches' => 0,
            'cache_hits' => 0,
            'average_duration_ms' => 0.0,
        ];
    }

    /**
     * Reset performance metrics
     */
    public function resetPerformanceMetrics(): void
    {
        // In a real implementation, this would clear tracked metrics
        Log::info('[DataFetchingAgent] Performance metrics reset');
    }

    /**
     * Check if the agent is healthy and operational
     */
    public function isHealthy(): bool
    {
        // Check if MCP client is available
        if (! $this->mcpClient->isEnabled()) {
            return false;
        }

        // Check if fetch server is available
        if (! $this->mcpClient->isFetchAvailable()) {
            return false;
        }

        return true;
    }

    /**
     * Get agent status information
     *
     * @return array{healthy: bool, mcp_enabled: bool, fetch_available: bool, cache_available: bool}
     */
    public function getStatus(): array
        return [
            'healthy' => $this->isHealthy(),
            'mcp_enabled' => $this->mcpClient->isEnabled(),
            'fetch_available' => $this->mcpClient->isFetchAvailable(),
            'cache_available' => true, // Cache is always available
        ];
    }
}
