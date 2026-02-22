# External API Performance Optimization

## Overview

The Performance Optimization Service provides advanced features to improve the efficiency and speed of external API
integration. It implements request batching, connection pooling, response compression, and parallel fetching to minimize
API calls, reduce bandwidth usage, and improve response times.

**Requirements**: 14.5 (Performance Optimization and Monitoring)  
**Task**: 5.1.2  
**Service**: `App\Services\ExternalAPI\PerformanceOptimizationService`

## Features

### 1. Request Batching

Request batching groups multiple API requests together to minimize the number of API calls. Requests are automatically
grouped by endpoint and method, and can be executed manually or automatically when the batch reaches its maximum size.

#### Configuration

- **Maximum Batch Size**: 10 requests per batch
- **Auto-execution**: Batches are automatically executed when full
- **Manual execution**: Batches can be executed on-demand

#### Usage

```php
use App\Services\ExternalAPI\PerformanceOptimizationService;

$service = app(PerformanceOptimizationService::class);

// Add requests to batch
$result1 = $service->addToBatch('/api/characters', 'GET', ['id' => 1]);
$result2 = $service->addToBatch('/api/characters', 'GET', ['id' => 2]);
$result3 = $service->addToBatch('/api/characters', 'GET', ['id' => 3]);

// Execute batch manually
$batchResult = $service->executeBatch($result1['batch_id']);

// Or execute all pending batches
$allResults = $service->executeAllBatches();

// Check batch queue status
$status = $service->getBatchQueueStatus();
```text

#### Response Format

```php
[
    'success' => true,
    'batch_id' => 'abc123...',
    'executed_count' => 3,
    'results' => [
        [
            'success' => true,
            'endpoint' => '/api/characters',
            'method' => 'GET',
            'data' => [...]
        ],
        // ... more results
    ],
    'duration_ms' => 123.45
]
```

### 2. Connection Pooling

Connection pooling reuses HTTP connections to improve efficiency and reduce connection overhead. The service maintains a
pool of active connections that can be reused for subsequent requests to the same host.

#### Pool Configuration

- **Pool Size**: 5 connections maximum
- **Connection Reuse**: Connections are automatically reused for the same host
- **LRU Eviction**: Least recently used connections are evicted when pool is full

#### Pool Usage

Connection pooling is automatically used when making parallel requests:

```php
$requests = [
    ['url' => 'https://api.example.com/1'],
    ['url' => 'https://api.example.com/2'],
    ['url' => 'https://api.example.com/3'],
];

$result = $service->fetchParallel($requests);

// Check connection pool status
$poolStatus = $service->getConnectionPoolStatus();
```text

#### Pool Status

```php
[
    'pool_size' => 2,
    'max_size' => 5,
    'in_use' => 0,
    'available' => 2,
    'connections' => [
        'conn_abc123' => [
            'url' => 'https://api.example.com',
            'last_used' => 1234567890,
            'in_use' => false,
            'age_seconds' => 45
        ],
        // ... more connections
    ]
]
```

### 3. Response Compression

Response compression uses gzip to reduce bandwidth usage for large API responses. Compression is automatically applied
to responses larger than 1KB.

#### Compression Configuration

- **Compression Threshold**: 1024 bytes (1KB)
- **Compression Level**: 6 (balanced between speed and compression ratio)
- **Algorithm**: gzip

#### Compression Usage

```php
// Compress response data
$data = [
    'characters' => [...], // Large dataset
    'metadata' => [...]
];

$compressed = $service->compressResponse($data);

// Response includes compression statistics
[
    'compressed' => true,
    'original_size' => 5000,
    'compressed_size' => 1200,
    'compression_ratio' => 0.24,
    'data' => 'base64_encoded_compressed_data...'
]

// Decompress when needed
$decompressed = $service->decompressResponse($compressed['data']);
```text

#### Compression Statistics

The service tracks compression metrics:

- Total compression operations
- Bytes saved
- Original vs compressed sizes
- Compression ratio
- Savings percentage

### 4. Parallel Fetching

Parallel fetching executes multiple HTTP requests concurrently to improve performance. Requests are automatically
chunked to respect the maximum parallel request limit.

#### Configuration

- **Maximum Parallel Requests**: 5 concurrent requests
- **Automatic Chunking**: Large request sets are automatically split into chunks
- **Connection Pooling**: Automatically uses connection pool for efficiency

#### Usage

```php
// Define multiple requests
$requests = [
    [
        'url' => 'https://api.example.com/characters/1',
        'method' => 'GET',
        'params' => [],
        'headers' => []
    ],
    [
        'url' => 'https://api.example.com/characters/2',
        'method' => 'GET'
    ],
    [
        'url' => 'https://api.example.com/support-cards/1',
        'method' => 'GET'
    ],
    // ... more requests
];

// Execute in parallel
$result = $service->fetchParallel($requests);
```

#### Response Format

```php
[
    'success' => true,
    'results' => [
        [
            'success' => true,
            'url' => 'https://api.example.com/characters/1',
            'method' => 'GET',
            'data' => [...]
        ],
        [
            'success' => false,
            'url' => 'https://api.example.com/characters/2',
            'method' => 'GET',
            'error' => 'Request timeout'
        ],
        // ... more results
    ],
    'duration_ms' => 234.56,
    'parallel_count' => 3
]
```text

## Performance Metrics

The service integrates with `APIPerformanceMetricsService` to track performance metrics:

### Batch Execution Metrics

- Total batch executions
- Total requests processed
- Average batch size
- Average execution duration

```php
$metrics = app(APIPerformanceMetricsService::class);
$batchStats = $metrics->getBatchExecutionStats();
```

### Parallel Fetch Metrics

- Total parallel fetches
- Total requests processed
- Average parallel size
- Average execution duration

```php
$parallelStats = $metrics->getParallelFetchStats();
```text

### Compression Metrics

- Total compression operations
- Bytes saved
- Original vs compressed bytes
- Compression ratio
- Savings percentage

```php
$compressionStats = $metrics->getCompressionStats();
```

## Best Practices

### Request Batching

1. **Group Similar Requests**: Batch requests to the same endpoint for maximum efficiency
2. **Set Appropriate Batch Size**: Balance between latency and throughput
3. **Handle Failures Gracefully**: Individual request failures don't affect the entire batch
4. **Use Callbacks**: Implement callbacks for asynchronous batch processing

### Connection Pooling

1. **Reuse Connections**: Make multiple requests to the same host to benefit from pooling
2. **Monitor Pool Status**: Check pool utilization to optimize pool size
3. **Clear When Needed**: Clear the pool when switching between different API sources

### Response Compression

1. **Compress Large Responses**: Only compress responses larger than 1KB
2. **Cache Compressed Data**: Store compressed data in cache to save bandwidth
3. **Decompress On-Demand**: Only decompress when data is actually needed
4. **Monitor Savings**: Track compression statistics to measure bandwidth savings

### Parallel Fetching

1. **Limit Concurrency**: Respect API rate limits by limiting parallel requests
2. **Handle Failures**: Implement proper error handling for individual request failures
3. **Use Connection Pooling**: Combine with connection pooling for maximum efficiency
4. **Monitor Performance**: Track parallel fetch metrics to optimize concurrency

## Integration Example

Here's a complete example integrating all performance optimization features:

```php
use App\Services\ExternalAPI\PerformanceOptimizationService;
use App\Services\ExternalAPI\CacheManagerService;

class CharacterDataService
{
    public function __construct(
        private PerformanceOptimizationService $perfService,
        private CacheManagerService $cacheService
    ) {}

    public function fetchMultipleCharacters(array $characterIds): array
    {
        // Check cache first
        $results = [];
        $uncachedIds = [];

        foreach ($characterIds as $id) {
            $cacheKey = "character_data:{$id}";
            $cached = $this->cacheService->get($cacheKey);

            if ($cached) {
                $results[$id] = $cached;
            } else {
                $uncachedIds[] = $id;
            }
        }

        // Fetch uncached characters in parallel
        if (!empty($uncachedIds)) {
            $requests = array_map(
                fn($id) => [
                    'url' => "https://api.example.com/characters/{$id}",
                    'method' => 'GET'
                ],
                $uncachedIds
            );

            $fetchResult = $this->perfService->fetchParallel($requests);

            foreach ($fetchResult['results'] as $result) {
                if ($result['success']) {
                    $id = $this->extractIdFromUrl($result['url']);
                    $data = $result['data'];

                    // Compress large responses before caching
                    if (strlen(json_encode($data)) > 1024) {
                        $compressed = $this->perfService->compressResponse($data);
                        $this->cacheService->put(
                            "character_data:{$id}:compressed",
                            $compressed,
                            86400
                        );
                    } else {
                        $this->cacheService->put(
                            "character_data:{$id}",
                            $data,
                            86400
                        );
                    }

                    $results[$id] = $data;
                }
            }
        }

        return $results;
    }

    private function extractIdFromUrl(string $url): string
    {
        return basename(parse_url($url, PHP_URL_PATH));
    }
}
```text

## Monitoring and Debugging

### Performance Statistics

Get comprehensive performance statistics:

```php
$stats = $service->getStatistics();

// Returns:
[
    'batch_queue' => [
        'total_batches' => 2,
        'total_requests' => 15,
        'batches' => [...]
    ],
    'connection_pool' => [
        'pool_size' => 3,
        'max_size' => 5,
        'in_use' => 1,
        'available' => 2,
        'connections' => [...]
    ],
    'configuration' => [
        'max_batch_size' => 10,
        'connection_pool_size' => 5,
        'compression_threshold' => 1024,
        'max_parallel_requests' => 5
    ]
]
```

### Logging

The service logs all operations at appropriate levels:

- **DEBUG**: Detailed operation logs (batch additions, connection reuse, compression details)
- **INFO**: Important events (batch execution, parallel fetch completion)
- **WARNING**: Potential issues (compression failures)
- **ERROR**: Failures (request errors, decompression failures)

### Metrics Dashboard

View performance metrics in the monitoring dashboard:

```php
$metrics = app(APIPerformanceMetricsService::class);
$dashboard = $metrics->getDashboardMetrics();

// Includes batch, parallel, and compression statistics
```text

## Testing

Comprehensive tests are available in:

- `tests/Feature/Services/ExternalAPI/PerformanceOptimizationServiceTest.php`

Run tests:

```bash
php artisan test --filter=PerformanceOptimizationServiceTest
```

## Configuration Reference

Performance optimization settings can be adjusted in the service constants:

```php
// In PerformanceOptimizationService.php
private const MAX_BATCH_SIZE = 10;
private const CONNECTION_POOL_SIZE = 5;
private const COMPRESSION_THRESHOLD = 1024;
private const MAX_PARALLEL_REQUESTS = 5;
```text

## Performance Impact

Expected performance improvements:

- **Request Batching**: 50-70% reduction in API calls for bulk operations
- **Connection Pooling**: 20-30% reduction in connection overhead
- **Response Compression**: 60-80% bandwidth savings for large responses
- **Parallel Fetching**: 3-5x faster for multiple independent requests

## Troubleshooting

### Batch Not Executing

- Check if batch size has reached MAX_BATCH_SIZE
- Manually execute batch using `executeBatch()`
- Check batch queue status with `getBatchQueueStatus()`

### Connection Pool Full

- Increase CONNECTION_POOL_SIZE if needed
- Clear pool with `clearConnectionPool()`
- Monitor pool status with `getConnectionPoolStatus()`

### Compression Failures

- Check if data is JSON-serializable
- Verify data size exceeds COMPRESSION_THRESHOLD
- Check logs for compression errors

### Parallel Fetch Timeouts

- Reduce number of parallel requests
- Increase timeout values
- Check network connectivity

## Related Documentation

- [External API Integration](./external-api-integration.md)
- [API Performance Monitoring](./api-performance-monitoring.md)
- [Cache Management](./cache-management.md)
- [MCP Integration](./mcp-integration.md)

