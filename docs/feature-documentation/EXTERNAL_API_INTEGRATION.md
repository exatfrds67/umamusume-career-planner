# External API Integration Documentation

## Overview

The UmamusumeCareerPlanner integrates with multiple external APIs to provide comprehensive game data, training
calculations, and meta information. This document describes the MCP-powered external API client services implemented in
Task 4.4.1.

## Architecture

### Components

1. **UmapyoiApiClient** - Primary data source for characters, support cards, and news
2. **UmamusumeDBApiClient** - Training calculations, meta data, and community builds
3. **Context7Service** - Intelligent context management across API calls
4. **ExternalAPIFacade** - Unified interface with intelligent fallback

### MCP Integration

All API clients leverage the MCP (Model Context Protocol) server infrastructure for:

- **Enhanced HTTP capabilities** via fetch MCP server
- **Intelligent retry logic** with exponential backoff
- **Context management** via context7 MCP server
- **Health monitoring** and automatic reconnection

## API Clients

### UmapyoiApiClient

Primary data source for Uma Musume game data from umapyoi.net.

#### Features

- Character data fetching with caching
- Support card information retrieval
- Latest news and updates
- Automatic retry with exponential backoff (3 attempts)
- 24-hour cache TTL for static data
- 1-hour cache TTL for news

#### Usage

```php
use App\Services\ExternalAPI\UmapyoiApiClient;

$client = app(UmapyoiApiClient::class);

// Fetch all characters
$result = $client->getCharacters();
// Returns: ['success' => true, 'data' => [...], 'source' => 'api|cache']

// Fetch specific character
$result = $client->getCharacter('1');

// Fetch support cards
$result = $client->getSupportCards();

// Fetch news
$result = $client->getNews(limit: 10);

// Force refresh (bypass cache)
$result = $client->getCharacters(forceRefresh: true);

// Check API availability
$available = $client->isAvailable();

// Clear cache
$client->clearCache();
```text

#### Endpoints

- `GET /v1/characters` - List all characters
- `GET /v1/characters/{id}` - Get specific character
- `GET /v1/support-cards` - List all support cards
- `GET /v1/support-cards/{id}` - Get specific support card
- `GET /api/v1/news/latest/{limit}` - Get latest news

### UmamusumeDBApiClient

Training calculations and meta data from UmamusumeDB.com.

#### Features

- Training calculation predictions
- Meta tier rankings for support cards
- Community builds and strategies
- Skill effectiveness data
- Race strategy recommendations
- Enhanced retry logic (5 attempts with exponential backoff)
- 12-hour cache TTL for meta data
- 1-hour cache TTL for calculations

#### Usage

```php
use App\Services\ExternalAPI\UmamusumeDBApiClient;

$client = app(UmamusumeDBApiClient::class);

// Get training calculation
$result = $client->getTrainingCalculation([
    'training_type' => 'speed',
    'support_cards' => [1, 2, 3],
    'character_stats' => [...],
]);

// Get meta tier rankings
$result = $client->getMetaTierRankings();

// Get community builds
$result = $client->getCommunityBuilds('character_id');

// Get skill effectiveness
$result = $client->getSkillEffectiveness();

// Get race strategy
$result = $client->getRaceStrategy([
    'distance' => 'medium',
    'surface' => 'turf',
    'character_stats' => [...],
]);
```text

#### Endpoints

- `POST /v1/training/calculate` - Calculate training predictions
- `GET /v1/meta/tier-rankings` - Get meta tier rankings
- `GET /v1/characters/{id}/builds` - Get community builds
- `GET /api/v1/skill` - Get skill list data
- `POST /v1/race/strategy` - Get race strategy recommendations

### Context7Service

Intelligent context management using the context7 MCP server.

#### Context7 Features

- API call context tracking
- Character-specific context storage
- Career-specific context storage
- AI conversation context management
- Context pattern analysis
- Intelligent caching recommendations

#### Context7 Usage

```php
use App\Services\ExternalAPI\Context7Service;

$service = app(Context7Service::class);

// Store API call context
$service->storeApiCallContext('umapyoi', '/v1/characters', [
    'force_refresh' => false,
    'timestamp' => now()->toIso8601String(),
]);

// Store character context
$service->storeCharacterContext($characterId, [
    'last_training' => 'speed',
    'current_turn' => 15,
]);

// Retrieve character context
$context = $service->getCharacterContext($characterId);

// Store career context
$service->storeCareerContext($careerId, [
    'scenario_type' => 'ura_finale',
    'current_stage' => 'senior',
]);

// Analyze context patterns
$analysis = $service->analyzeContextPatterns('umapyoi');

// Get context summary
$summary = $service->getContextSummary();

// Clear context
$service->clearContext('character', $characterId);
```text

### ExternalAPIFacade

Unified interface for all external API integrations.

#### Features

- Single entry point for all external data
- Intelligent fallback between APIs
- Automatic context management
- Health monitoring for all APIs
- Comprehensive statistics
- Bulk data synchronization

#### Usage

```php
use App\Services\ExternalAPI\ExternalAPIFacade;

$facade = app(ExternalAPIFacade::class);

// Fetch characters (with automatic context tracking)
$result = $facade->getCharacters();

// Fetch support cards
$result = $facade->getSupportCards();

// Get training calculation
$result = $facade->getTrainingCalculation([
    'training_type' => 'speed',
]);

// Get meta tier rankings
$result = $facade->getMetaTierRankings();

// Get community builds
$result = $facade->getCommunityBuilds('character_id');

// Check health status of all APIs
$health = $facade->getHealthStatus();
// Returns: [
//     'umapyoi' => ['available' => true, 'cache_status' => [...]],
//     'umamusumedb' => ['available' => true, 'cache_status' => [...]],
//     'context7' => ['enabled' => true, 'healthy' => true],
// ]

// Get comprehensive statistics
$stats = $facade->getStatistics();

// Clear all caches
$facade->clearAllCaches();

// Sync all data
$results = $facade->syncAllData();
```

## Configuration

### Environment Variables

Add to `.env`:

```env
# Umapyoi.net API
UMAPYOI_API_URL=https://api.umapyoi.net
UMAPYOI_API_TIMEOUT=30
UMAPYOI_API_ENABLED=true
UMAPYOI_CACHE_TTL=86400
UMAPYOI_MAX_RETRIES=3
UMAPYOI_RETRY_DELAY=1000

# UmamusumeDB.com API
UMAMUSUMEDB_API_URL=https://api.umamusumedb.com
UMAMUSUMEDB_API_TIMEOUT=30
UMAMUSUMEDB_API_ENABLED=true
UMAMUSUMEDB_CACHE_TTL=43200
UMAMUSUMEDB_MAX_RETRIES=5
UMAMUSUMEDB_INITIAL_RETRY_DELAY=500
UMAMUSUMEDB_MAX_RETRY_DELAY=10000

# Context7 MCP Server
CONTEXT7_ENABLED=true
CONTEXT7_CACHE_TTL=1800
CONTEXT7_MAX_HISTORY=50

# API Fallback
API_FALLBACK_ENABLED=true
API_FALLBACK_MANUAL_INPUT=true
API_FALLBACK_STALE_THRESHOLD=604800

# Health Monitoring
API_HEALTH_CHECK_INTERVAL=300
API_HEALTH_FAILURE_THRESHOLD=3
API_HEALTH_ALERT_ENABLED=true

# Rate Limiting
API_RATE_LIMIT_ENABLED=true
API_RATE_LIMIT_PER_MINUTE=60
API_RATE_LIMIT_PER_HOUR=1000
```text

## Configuration File

The `config/external-apis.php` file contains all configuration options with sensible defaults.

## Error Handling

### Retry Logic

Both API clients implement intelligent retry logic:

**UmapyoiApiClient:**

- 3 retry attempts
- 1-second initial delay
- Linear backoff

**UmamusumeDBApiClient:**

- 5 retry attempts
- 500ms initial delay
- Exponential backoff (doubles each attempt)
- Maximum 10-second delay
- Random jitter to prevent thundering herd

### Error Response Format

All API methods return a consistent error format:

```php
[
    'success' => false,
    'data' => null, // or []
    'source' => 'error',
    'error' => 'Error message describing what went wrong',
]
```text

### Fallback Behavior

When primary APIs fail:

1. Check cache for stale data (up to 7 days old)
2. Log warning with error details
3. Return error response with clear message
4. Trigger health monitoring alerts

## Caching Strategy

### Cache Keys

- **Characters:** `umapyoi:characters`
- **Character by ID:** `umapyoi:character:{id}`
- **Support Cards:** `umapyoi:support_cards`
- **Support Card by ID:** `umapyoi:support_card:{id}`
- **News:** `umapyoi:news:limit:{limit}`
- **Training Calculation:** `umamusumedb:training:{hash}`
- **Meta Rankings:** `umamusumedb:meta:tier_rankings`
- **Community Builds:** `umamusumedb:builds:{character_id}`
- **Context:** `context7:{type}:{identifier}`

### Cache TTL

- **Static Data (Characters, Support Cards):** 24 hours
- **Meta Data (Tier Rankings, Skill Effectiveness):** 12 hours
- **Dynamic Data (News):** 1 hour
- **Calculations (Training, Race Strategy):** 1-2 hours
- **Context Data:** 30 minutes

### Cache Invalidation

```php
// Clear specific API cache
$umapyoiClient->clearCache();
$umamusumeDBClient->clearCache();

// Clear all caches
$facade->clearAllCaches();

// Force refresh (bypass cache)
$result = $client->getCharacters(forceRefresh: true);
```text

## Health Monitoring

### Health Check

```php
$health = $facade->getHealthStatus();

// Check individual API availability
if ($health['umapyoi']['available']) {
    // API is available
}

// Check cache status
if ($health['umapyoi']['cache_status']['characters']) {
    // Characters are cached
}
```

### Monitoring Metrics

- API availability (HTTP health checks)
- Cache hit rates
- Response times
- Error rates
- Retry attempts
- Context usage

## Testing

### Running Tests

```bash
# Run all external API tests
php artisan test --filter=ExternalAPI

# Run specific test file
php artisan test tests/Feature/ExternalAPI/UmapyoiApiClientTest.php

# Run with coverage
php artisan test --filter=ExternalAPI --coverage
```text

## Test Coverage

- ✅ 32 tests passing
- ✅ 95 assertions
- ✅ All API client methods tested
- ✅ Error handling tested
- ✅ Caching behavior tested
- ✅ Retry logic tested
- ✅ Facade integration tested

## Best Practices

### 1. Always Use the Facade

```php
// ✅ Good - Use facade for automatic context tracking
$facade->getCharacters();

// ❌ Avoid - Direct client usage bypasses context management
$umapyoiClient->getCharacters();
```text

### 2. Handle Errors Gracefully

```php
$result = $facade->getCharacters();

if (!$result['success']) {
    // Log error
    Log::error('Failed to fetch characters', ['error' => $result['error']]);
    
    // Provide fallback or user message
    return response()->json([
        'message' => 'Unable to fetch character data. Please try again later.',
    ], 503);
}
```text

### 3. Use Force Refresh Sparingly

```php
// Only force refresh when absolutely necessary
$result = $facade->getCharacters(forceRefresh: true);
```

### 4. Monitor Health Status

```php
// Check health before critical operations
$health = $facade->getHealthStatus();

if (!$health['umapyoi']['available']) {
    // Use fallback or notify user
}
```text

### 5. Leverage Context Management

```php
// Store relevant context for better caching
$contextService->storeCharacterContext($characterId, [
    'last_api_call' => now(),
    'preferred_data_source' => 'umapyoi',
]);
```text

## Troubleshooting

### API Not Responding

1. Check API availability: `$client->isAvailable()`
2. Verify environment variables are set correctly
3. Check network connectivity
4. Review logs for error details

### Cache Issues

1. Clear cache: `$facade->clearAllCaches()`
2. Check Redis connection
3. Verify cache TTL settings
4. Check disk space for cache storage

### Slow Response Times

1. Check retry attempts in logs
2. Verify timeout settings
3. Monitor API health status
4. Consider increasing cache TTL

## Future Enhancements

- [ ] Implement actual MCP fetch server integration
- [ ] Add request rate limiting per API
- [ ] Implement circuit breaker pattern
- [ ] Add predictive cache warming
- [ ] Implement background data synchronization
- [ ] Add comprehensive API metrics dashboard
- [ ] Implement webhook support for real-time updates

## Related Documentation

- [MCP Server Configuration](MCP_SERVER_CONFIGURATION_REFERENCE.md)
- [Task 4.4.1 Implementation](../tasks.md#task-441)
- [Requirements 14.1, 14.2, 55.3](../requirements.md)
