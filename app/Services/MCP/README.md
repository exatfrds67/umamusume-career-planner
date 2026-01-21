# MCP Client Service

## Overview

The `MCPClientService` provides a comprehensive wrapper for interacting with MCP (Model Context Protocol) servers, with enhanced support for HTTP operations via the MCP fetch server. This service is designed for external API integration with automatic retry logic, error handling, and comprehensive logging.

## Features

- **Health Monitoring**: Automatic health checks for all MCP servers
- **Automatic Reconnection**: Intelligent reconnection logic with exponential backoff
- **HTTP Operations**: Full support for GET, POST, PUT, DELETE, PATCH requests
- **Retry Logic**: Automatic retry with exponential backoff for failed requests
- **Error Handling**: Comprehensive error handling with detailed logging
- **JSON Support**: Automatic JSON encoding/decoding
- **Timeout Management**: Configurable timeouts for all operations
- **Connection Pooling**: Efficient connection management

## Configuration

MCP servers are configured in `config/mcp.php`:

```php
'servers' => [
    'fetch' => [
        'enabled' => true,
        'command' => 'uvx',
        'args' => ['mcp-server-fetch'],
        'capabilities' => ['http_client', 'external_api_integration'],
    ],
    // ... other servers
],
```

## Usage

### Basic HTTP Operations

```php
use App\Services\MCP\MCPClientService;

$mcpClient = app(MCPClientService::class);

// GET request
$response = $mcpClient->get('https://api.example.com/data');

// POST request
$response = $mcpClient->post('https://api.example.com/data', [
    'name' => 'Test',
    'value' => 123,
]);

// PUT request
$response = $mcpClient->put('https://api.example.com/data/1', [
    'name' => 'Updated',
]);

// DELETE request
$response = $mcpClient->delete('https://api.example.com/data/1');

// PATCH request
$response = $mcpClient->patch('https://api.example.com/data/1', [
    'status' => 'active',
]);
```

### JSON Operations

```php
// Fetch with automatic JSON decoding
$response = $mcpClient->fetchJson('https://api.example.com/data');

// Access decoded data
$data = $response['data'];
$status = $response['status'];
```

### Custom Headers and Timeout

```php
// With custom headers
$response = $mcpClient->get('https://api.example.com/data', [
    'Authorization' => 'Bearer token123',
    'X-Custom-Header' => 'value',
]);

// With custom timeout (in seconds)
$response = $mcpClient->get('https://api.example.com/data', [], 10);
```

### Advanced Fetch Options

```php
// Full control over fetch operation
$response = $mcpClient->fetch(
    url: 'https://api.example.com/data',
    method: 'POST',
    data: ['key' => 'value'],
    headers: ['Authorization' => 'Bearer token'],
    timeout: 5,
    maxRetries: 3
);
```

## Response Format

All HTTP operations return an array with the following structure:

```php
[
    'success' => true,           // Boolean indicating success
    'status' => 200,             // HTTP status code
    'headers' => [...],          // Response headers
    'body' => '...',             // Raw response body
    'url' => '...',              // Request URL
    'method' => 'GET',           // HTTP method used
]
```

For `fetchJson()`, the response includes:

```php
[
    'success' => true,
    'status' => 200,
    'headers' => [...],
    'data' => [...],             // Decoded JSON data
    'raw_body' => '...',         // Raw JSON string
]
```

## Error Handling

The service implements comprehensive error handling:

```php
try {
    $response = $mcpClient->get('https://api.example.com/data');
} catch (\RuntimeException $e) {
    // Handle error
    Log::error('API request failed', [
        'error' => $e->getMessage(),
    ]);
}
```

### Automatic Retry Logic

- Failed requests are automatically retried up to 3 times (configurable)
- Exponential backoff between retries (1s, 2s, 4s, max 5s)
- Client errors (4xx) are not retried
- All retry attempts are logged

## Health Monitoring

```php
// Check if fetch server is available
if ($mcpClient->isFetchAvailable()) {
    $response = $mcpClient->get('https://api.example.com/data');
}

// Get fetch server configuration
$config = $mcpClient->getFetchServerConfig();

// Perform health check on all servers
$healthStatus = $mcpClient->healthCheck();

// Check specific server health
$isHealthy = $mcpClient->isServerHealthy('fetch');
```

## Logging

All requests and responses are automatically logged:

- **Info Level**: Successful requests with duration
- **Warning Level**: Failed requests with retry information
- **Error Level**: All retry attempts exhausted

Log entries include:

- URL and HTTP method
- Request duration in milliseconds
- Attempt number
- Error messages (if any)

## Best Practices

1. **Always check server availability** before making requests:

   ```php
   if ($mcpClient->isFetchAvailable()) {
       // Make request
   }
   ```

2. **Use appropriate timeouts** based on expected response time:

   ```php
   // Quick endpoint
   $response = $mcpClient->get($url, [], 2);
   
   // Slow endpoint
   $response = $mcpClient->get($url, [], 10);
   ```

3. **Handle errors gracefully**:

   ```php
   try {
       $response = $mcpClient->fetchJson($url);
       return $response['data'];
   } catch (\RuntimeException $e) {
       Log::error('Failed to fetch data', ['error' => $e->getMessage()]);
       return null;
   }
   ```

4. **Use fetchJson() for JSON APIs**:

   ```php
   // Automatically decodes JSON
   $response = $mcpClient->fetchJson($url);
   $data = $response['data'];
   ```

5. **Monitor logs** for performance issues and errors

## Testing

The service includes comprehensive unit tests covering:

- All HTTP methods (GET, POST, PUT, DELETE, PATCH)
- JSON operations
- Error handling
- Retry logic
- Health monitoring
- Server availability checks

Run tests with:

```bash
php artisan test --filter=MCPClientServiceTest
```

## Integration with External API Service

The MCPClientService is designed to be used by the ExternalAPIService for integrating with external game data APIs:

```php
use App\Services\MCP\MCPClientService;

class ExternalAPIService
{
    public function __construct(
        private MCPClientService $mcpClient
    ) {}

    public function fetchCharacterData(string $characterName): array
    {
        $url = "https://api.umapyoi.net/api/v1/characters/{$characterName}";
        
        try {
            $response = $this->mcpClient->fetchJson($url);
            return $response['data'];
        } catch (\RuntimeException $e) {
            // Handle error or fallback to cache
            throw $e;
        }
    }
}
```

## Requirements Satisfied

This implementation satisfies **Requirement 1.2** from the external-api-integration spec:

- ✅ MCP fetch server integration for HTTP operations
- ✅ Automatic retry logic with exponential backoff
- ✅ Timeout handling (configurable, default 5 seconds)
- ✅ Connection pooling via MCP server
- ✅ Request/response logging for debugging
- ✅ Error handling with detailed error messages
- ✅ Helper methods for common HTTP operations (GET, POST, PUT, DELETE, PATCH)
- ✅ JSON encoding/decoding support
- ✅ Comprehensive unit tests

## Related Services

- `ExternalAPIService`: Uses MCPClientService for external API integration
- `CacheManagerService`: Caches API responses
- `DataReconciliationService`: Reconciles data from multiple sources

## Future Enhancements

- Circuit breaker pattern for failing endpoints
- Request/response caching at the client level
- Metrics collection for monitoring
- Rate limiting support
- Request batching capabilities
