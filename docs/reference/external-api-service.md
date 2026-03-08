# ExternalAPIService Documentation

## Overview

The `ExternalAPIService` is a base class for integrating with multiple external APIs. It provides:

- **API Source Configuration**: Configure multiple API sources with priority ordering
- **Automatic Failover**: Automatically switch to backup sources when primary fails
- **Rate Limiting**: Per-source rate limiting to prevent API abuse
- **Timeout Handling**: Configurable timeouts for each API source
- **Request/Response Logging**: Comprehensive logging for debugging
- **Circuit Breaker Pattern**: Automatically disable failing sources temporarily
- **MCP Integration**: Uses MCPClientService for enhanced HTTP capabilities

## Requirements

- **Requirement 14.1**: Multi-Source API Integration with MCP Enhancement
- **Requirement 14.2**: Intelligent Caching and Offline Functionality
- **Requirement 14.5**: Performance Optimization and Monitoring
- **Task 1.2.1**: Create ExternalAPIService base class

## Architecture

```text
┌─────────────────────────────────────────────────────────────┐
│                   ExternalAPIService                        │
│                     (Base Class)                            │
├─────────────────────────────────────────────────────────────┤
│  Features:                                                  │
│  • API source configuration with priority                   │
│  • Automatic failover to secondary sources                  │
│  • Rate limiting per source                                 │
│  • Timeout handling                                         │
│  • Request/response logging                                 │
│  • Circuit breaker pattern                                  │
│  • MCP fetch integration                                    │
└─────────────────────────────────────────────────────────────┘
                            ▲
                            │ extends
                            │
        ┌───────────────────┴───────────────────┐
        │                                       │
┌───────────────────┐               ┌───────────────────┐
│ UmapyoiApiClient  │               │ ExampleAPIService │
│                   │               │                   │
│ • Character data  │               │ • Custom methods  │
│ • Support cards   │               │ • Specific logic  │
│ • News data       │               │                   │
└───────────────────┘               └───────────────────┘
```

## Usage

### 1. Create a Service Class

Extend `ExternalAPIService` and implement `initializeApiSources()`:

```php
<?php

namespace App\Services\ExternalAPI;

class MyAPIService extends ExternalAPIService
{
    protected function initializeApiSources(): void
    {
        $this->apiSources = [
            'primary' => [
                'priority' => 1,
                'base_url' => 'https://api.primary.com',
                'timeout' => 5,
                'rate_limit' => 100, // requests per minute
                'enabled' => true,
            ],
            'secondary' => [
                'priority' => 2,
                'base_url' => 'https://api.secondary.com',
                'timeout' => 5,
                'rate_limit' => 60,
                'enabled' => true,
            ],
        ];
    }

    public function fetchData(string $id): array
    {
        return $this->fetchWithFallback("/data/{$id}");
    }
}
```text

### 2. Register as Service Provider

Add to `config/app.php` or use dependency injection:

```php
use App\Services\ExternalAPI\MyAPIService;
use App\Services\MCP\MCPClientService;

$service = new MyAPIService(app(MCPClientService::class));
```

### 3. Use the Service

```php
// Fetch with automatic fallback
$result = $service->fetchData('123');

if ($result['success']) {
    $data = $result['data'];
    $source = $result['source']; // Which API source was used
    $responseTime = $result['metadata']['response_time_ms'];
} else {
    $error = $result['error'];
}
```text

## API Source Configuration

Each API source requires the following configuration:

| Field | Type | Description | Required |
| --- | --- | --- | --- |
| `priority` | int | Priority order (1 = highest) | Yes |
| `base_url` | string | Base URL for the API | Yes |
| `timeout` | int | Request timeout in seconds | Yes |
| `rate_limit` | int | Max requests per minute | Yes |
| `enabled` | bool | Whether source is active | Yes |

### Example Configuration

```php
$this->apiSources = [
    'umapyoi' => [
        'priority' => 1,
        'base_url' => 'https://api.umapyoi.net',
        'timeout' => 5,
        'rate_limit' => 100,
        'enabled' => true,
    ],
    'backup' => [
        'priority' => 2,
        'base_url' => 'https://backup-api.com',
        'timeout' => 10,
        'rate_limit' => 30,
        'enabled' => true,
    ],
];
```

## Features

### Automatic Failover

When a request fails, the service automatically tries the next priority source:

```php
// Tries primary first, then secondary, then tertiary
$result = $this->fetchWithFallback('/endpoint');
```text

### Rate Limiting

Each API source has independent rate limiting:

```php
// Rate limit: 100 requests per minute
'rate_limit' => 100,
```

When rate limit is exceeded:

- Request is blocked
- Error is logged
- Response includes `rate_limited: true` in metadata

### Timeout Handling

Each source can have a different timeout:

```php
// Timeout after 5 seconds
'timeout' => 5,
```text

### Circuit Breaker

Automatically disables failing sources:

- **Threshold**: 5 consecutive failures
- **Reset Time**: 60 seconds
- **Behavior**: Skips source during failover until reset

```php
// Check circuit breaker status
$status = $service->getCircuitBreakerStatus();
// Returns: ['primary' => ['failures' => 3, 'is_open' => false]]
```

### Request/Response Logging

All requests and responses are logged:

```php
// Request log
[ExternalAPIService] API Request
{
    "source": "primary",
    "endpoint": "/characters/123",
    "method": "GET",
    "status": "started",
    "timestamp": "2024-01-15T10:30:00Z"
}

// Response log (success)
[ExternalAPIService] API Response Success
{
    "source": "primary",
    "endpoint": "/characters/123",
    "method": "GET",
    "status": "success",
    "duration_ms": 245.67,
    "status_code": 200,
    "timestamp": "2024-01-15T10:30:00Z"
}

// Response log (failure)
[ExternalAPIService] API Response Failed
{
    "source": "primary",
    "endpoint": "/characters/123",
    "method": "GET",
    "status": "failed",
    "duration_ms": 5002.34,
    "status_code": 0,
    "error": "Request timeout",
    "timestamp": "2024-01-15T10:30:05Z"
}
```text

## Protected Methods

### `fetchWithFallback()`

Fetch data with automatic failover to secondary sources.

```php
protected function fetchWithFallback(
    string $endpoint,
    string $method = 'GET',
    array $params = [],
    ?string $preferredSource = null
): array
```

**Parameters:**

- `$endpoint`: API endpoint (e.g., `/characters/123`)
- `$method`: HTTP method (GET, POST, PUT, DELETE, PATCH)
- `$params`: Request parameters
- `$preferredSource`: Try this source first (optional)

**Returns:**

```php
[
    'success' => true,
    'data' => [...], // Response data
    'source' => 'primary', // Which source was used
    'metadata' => [
        'response_time_ms' => 245.67,
        'status_code' => 200,
        'fetched_at' => '2024-01-15T10:30:00Z',
    ],
]
```text

### `fetchFromSource()`

Fetch from a specific source (no fallback).

```php
protected function fetchFromSource(
    string $sourceName,
    string $endpoint,
    string $method = 'GET',
    array $params = []
): array
```

## Public Methods

### `getApiSources()`

Get all configured API sources.

```php
$sources = $service->getApiSources();
```text

### `enableSource()` / `disableSource()`

Enable or disable an API source.

```php
$service->disableSource('secondary');
$service->enableSource('secondary');
```

### `getCircuitBreakerStatus()`

Get circuit breaker status for all sources.

```php
$status = $service->getCircuitBreakerStatus();
// Returns: ['primary' => ['failures' => 0, 'is_open' => false]]
```text

### `getHealthStatus()`

Get health status of all API sources.

```php
$health = $service->getHealthStatus();
// Returns:
// [
//     'primary' => [
//         'enabled' => true,
//         'circuit_breaker_open' => false,
//         'priority' => 1,
//     ],
// ]
```

## Response Format

All fetch methods return a standardized response:

### Success Response

```php
[
    'success' => true,
    'data' => [...], // Parsed response data
    'source' => 'primary', // Which API source was used
    'metadata' => [
        'response_time_ms' => 245.67,
        'status_code' => 200,
        'fetched_at' => '2024-01-15T10:30:00Z',
    ],
]
```text

### Error Response

```php
[
    'success' => false,
    'data' => null,
    'source' => 'primary', // Last attempted source
    'error' => 'Connection timeout',
    'metadata' => [
        'response_time_ms' => 5002.34,
        'error_type' => 'RuntimeException',
    ],
]
```

### All Sources Failed

```php
[
    'success' => false,
    'data' => null,
    'source' => 'none',
    'error' => 'All API sources failed: {...}',
    'metadata' => [
        'attempted_sources' => ['primary', 'secondary', 'tertiary'],
        'errors' => [
            'primary' => 'Connection timeout',
            'secondary' => 'Rate limit exceeded',
            'tertiary' => 'Service unavailable',
        ],
    ],
]
```text

## Testing

The service includes comprehensive unit tests:

```bash
php artisan test --filter=ExternalAPIServiceTest
```

### Test Coverage

- ✅ API source configuration
- ✅ Rate limiting per source
- ✅ Circuit breaker pattern
- ✅ Request/response logging
- ✅ Timeout handling
- ✅ Automatic failover
- ✅ Health status monitoring
- ✅ Response metadata

## Best Practices

### 1. Configure Multiple Sources

Always configure at least 2 API sources for redundancy:

```php
$this->apiSources = [
    'primary' => [...],
    'secondary' => [...],
];
```text

### 2. Set Appropriate Timeouts

Balance between responsiveness and reliability:

```php
'timeout' => 5, // 5 seconds is a good default
```

### 3. Monitor Circuit Breakers

Check circuit breaker status regularly:

```php
$status = $service->getCircuitBreakerStatus();
if ($status['primary']['is_open']) {
    // Alert: Primary API source is down
}
```text

### 4. Use Preferred Sources

For critical operations, specify a preferred source:

```php
$result = $this->fetchWithFallback('/critical-data', 'GET', [], 'primary');
```

### 5. Handle Errors Gracefully

Always check the `success` field:

```php
$result = $service->fetchData('123');

if (!$result['success']) {
    Log::error('API fetch failed', [
        'error' => $result['error'],
        'attempted_sources' => $result['metadata']['attempted_sources'] ?? [],
    ]);
    
    // Use cached data or show error to user
}
```text

## Configuration

Add API source URLs to `config/services.php`:

```php
return [
    'umapyoi' => [
        'url' => env('UMAPYOI_API_URL', 'https://api.umapyoi.net'),
    ],
    'umamusumedb' => [
        'url' => env('UMAMUSUMEDB_API_URL', 'https://umamusumedb.com/api'),
    ],
];
```

Add to `.env`:

```env
UMAPYOI_API_URL=https://api.umapyoi.net
UMAMUSUMEDB_API_URL=https://umamusumedb.com/api
```text

## Troubleshooting

### Rate Limit Exceeded

**Symptom**: Requests return `rate_limited: true`

**Solution**:

- Increase rate limit in configuration
- Implement request batching
- Add caching layer

### Circuit Breaker Open

**Symptom**: Source is skipped during failover

**Solution**:

- Check API source availability
- Review error logs
- Wait for automatic reset (60 seconds)
- Manually reset: `$service->resetCircuitBreaker('source')`

### All Sources Failed (Troubleshooting)

**Symptom**: All API sources return errors

**Solution**:

- Check network connectivity
- Verify API source URLs
- Check API source status pages
- Implement offline mode with cached data

## Related Documentation

- [MCP Client Service](./mcp-client-service.md)
- [Cache Management Service](./cache-management-service.md)
- [External API Integration Spec](./.kiro/specs/external-api-integration/)

## Support

For issues or questions:

1. Check the test suite for examples
2. Review the design document
3. Check application logs
4. Contact the development team
