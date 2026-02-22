# CacheManagementService Documentation

## Overview

The `CacheManagementService` provides intelligent caching capabilities for external API data with staleness indicators, configurable TTL by data type, and comprehensive cache statistics tracking.

## Features

- **Configurable TTL by Data Type**: Different cache durations for different types of data
- **Staleness Indicators**: Automatic tracking of data age with staleness warnings
- **Cache Statistics**: Hit/miss tracking and performance metrics
- **Metadata Management**: Automatic storage and retrieval of cache metadata
- **Offline Support**: Serves cached data with age indicators when APIs are unavailable
- **Cache Warming**: Support for preloading frequently accessed data
- **Cache Invalidation**: Manual and automatic cache clearing capabilities

## Requirements

This service implements **Requirement 14.2: Intelligent Caching and Offline Functionality** from the External API Integration specification.

## Installation

The service is automatically registered in the Laravel service container and can be injected via dependency injection:

```php
use App\Services\ExternalAPI\CacheManagementService;

class MyController extends Controller
{
    public function __construct(
        private CacheManagementService $cacheManager
    ) {}
}
```

## Configuration

### TTL Configuration

The service uses predefined TTL values for different data types:

| Data Type | TTL | Duration |
|-----------|-----|----------|
| `character_data` | 86400 seconds | 24 hours |
| `support_cards` | 43200 seconds | 12 hours |
| `meta_rankings` | 21600 seconds | 6 hours |
| `race_data` | 172800 seconds | 48 hours |
| `skills` | 86400 seconds | 24 hours |
| `news` | 3600 seconds | 1 hour |
| `game_mechanics` | 604800 seconds | 7 days |
| Default (unknown types) | 3600 seconds | 1 hour |

### Staleness Threshold

Data is considered "stale" when its age exceeds 80% of its TTL. This threshold is configurable via the `STALENESS_THRESHOLD` constant.

## Usage

### Basic Operations

#### Storing Data

```php
$key = 'character_data:Silence Suzuka';
$data = [
    'name' => 'Silence Suzuka',
    'speed' => 100,
    'stamina' => 90,
];

$cacheManager->put($key, $data);
```

#### Retrieving Data

```php
$cached = $cacheManager->get('character_data:Silence Suzuka');

if ($cached) {
    echo "Character: " . $cached['name'];
    echo "Cached at: " . $cached['_cache']['cached_at'];
    echo "Age: " . $cached['_cache']['age_seconds'] . " seconds";
    echo "Is stale: " . ($cached['_cache']['is_stale'] ? 'Yes' : 'No');
}
```

#### Deleting Data

```php
$cacheManager->delete('character_data:Silence Suzuka');
```

#### Checking Existence

```php
if ($cacheManager->has('character_data:Silence Suzuka')) {
    // Key exists in cache
}
```

### Cache Metadata

Every cached item includes metadata:

```php
$cached = $cacheManager->get('character_data:test');

// Access cache metadata
$metadata = $cached['_cache'];
// [
//     'cached_at' => Carbon instance,
//     'age_seconds' => 120,
//     'ttl_seconds' => 86400,
//     'is_stale' => false,
//     'staleness_percentage' => 0.14,
//     'source' => 'cache'
// ]
```

### Custom TTL

Override the default TTL for specific cache entries:

```php
$customTtl = 7200; // 2 hours
$cacheManager->put($key, $data, $customTtl);
```

### Cache Statistics

Track cache performance:

```php
$stats = $cacheManager->getStatistics();
// [
//     'hits' => 150,
//     'misses' => 25,
//     'hit_rate' => 85.71,
//     'total_requests' => 175,
//     'last_reset' => '2024-01-15T10:30:00Z'
// ]
```

Reset statistics:

```php
$cacheManager->resetStatistics();
```

### Cache Information

Get comprehensive cache information for monitoring:

```php
$info = $cacheManager->getCacheInfo();
// [
//     'statistics' => [...],
//     'redis' => [
//         'available' => true,
//         'version' => '7.0.0',
//         'used_memory' => '2.5M',
//         'connected_clients' => 5
//     ],
//     'ttl_config' => [...],
//     'staleness_threshold' => 0.8
// ]
```

### Cache Size

Monitor cache size:

```php
$size = $cacheManager->getCacheSize();
// [
//     'total_keys' => 150,
//     'estimated_size_bytes' => 524288
// ]
```

### Cache Warming

Preload frequently accessed data:

```php
$popularCharacters = [
    'Silence Suzuka',
    'Tokai Teio',
    'Gold Ship',
];

foreach ($popularCharacters as $character) {
    $key = "character_data:{$character}";
    $data = $apiService->fetchCharacterData($character);
    $cacheManager->put($key, $data);
}
```

### Cache Invalidation

Flush all external API cache:

```php
$cacheManager->flush();
```

## Integration with External API Service

The `CacheManagementService` is designed to work seamlessly with the `ExternalAPIService`:

```php
use App\Services\ExternalAPI\ExternalAPIService;
use App\Services\ExternalAPI\CacheManagementService;

class UmapyoiApiClient extends ExternalAPIService
{
    public function __construct(
        MCPClientService $mcpClient,
        private CacheManagementService $cacheManager
    ) {
        parent::__construct($mcpClient);
    }

    public function fetchCharacterData(string $characterName): array
    {
        $cacheKey = "character_data:{$characterName}";
        
        // Try cache first
        if ($cached = $this->cacheManager->get($cacheKey)) {
            return $cached;
        }

        // Fetch from API
        $result = $this->fetchWithFallback("/characters/{$characterName}");
        
        if ($result['success']) {
            // Cache the result
            $this->cacheManager->put($cacheKey, $result['data']);
            return $result['data'];
        }

        throw new \RuntimeException('Failed to fetch character data');
    }
}
```

## Offline Functionality

When APIs are unavailable, the service provides cached data with staleness indicators:

```php
$cached = $cacheManager->get('character_data:Silence Suzuka');

if ($cached) {
    if ($cached['_cache']['is_stale']) {
        // Show warning to user
        echo "Warning: This data is " . $cached['_cache']['age_seconds'] . " seconds old";
        echo "Staleness: " . $cached['_cache']['staleness_percentage'] . "%";
    }
    
    // Use cached data
    return $cached;
}
```

## Monitoring and Debugging

### Get All Cached Keys

```php
$keys = $cacheManager->getCachedKeys();
// ['character_data:Silence Suzuka', 'support_cards:123', ...]
```

### Get Cache Metadata

```php
$metadata = $cacheManager->getCacheMetadata('character_data:test');
// [
//     'cached_at' => Carbon instance,
//     'ttl' => 86400,
//     'data_type' => 'character_data',
//     'key' => 'character_data:test'
// ]
```

## Best Practices

1. **Use Descriptive Keys**: Follow the pattern `{data_type}:{identifier}`
   - Good: `character_data:Silence Suzuka`
   - Bad: `char_1`

2. **Check Cache First**: Always check cache before making API calls

3. **Handle Staleness**: Show appropriate warnings when serving stale data

4. **Monitor Performance**: Regularly check cache statistics to optimize hit rates

5. **Cache Warming**: Preload frequently accessed data during application startup

6. **Invalidate Appropriately**: Clear cache when game updates are detected

## Testing

The service includes comprehensive unit and integration tests:

```bash
# Run unit tests
php artisan test --filter=CacheManagerServiceTest

# Run integration tests
php artisan test --filter=CacheManagerIntegrationTest

# Run all cache-related tests
php artisan test --filter=CacheManager
```

## Performance Considerations

- **Redis Recommended**: The service works best with Redis as the cache driver
- **Memory Usage**: Monitor cache size to prevent excessive memory consumption
- **TTL Tuning**: Adjust TTL values based on data update frequency
- **Cache Warming**: Balance between preloading and memory usage

## Error Handling

The service handles errors gracefully:

- Returns `null` for non-existent keys
- Logs errors when cache operations fail
- Continues operation even if Redis is unavailable (falls back to array cache)

## Related Services

- `ExternalAPIService`: Base class for API integration
- `UmapyoiApiClient`: Primary API client using cache
- `BackgroundSyncService`: Handles background cache updates

## Support

For issues or questions, refer to:

- Task: 2.1.1 in `.kiro/specs/external-api-integration/tasks.md`
- Design: Section 3 in `.kiro/specs/external-api-integration/design.md`
- Requirements: Requirement 14.2 in `.kiro/specs/external-api-integration/requirements.md`
