# External API Integration - Design Document

## Architecture Overview

This document defines the technical implementation of external API integration using MCP servers and intelligent subagents for the UmamusumeCareerPlanner application.

### System Components

```text
┌─────────────────────────────────────────────────────────────────┐
│                 EXTERNAL API INTEGRATION LAYER                  │
├─────────────────────────────────────────────────────────────────┤
│  MCP Server Layer                                               │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   Fetch MCP     │ │   Context7 MCP  │ │  Strands-Agents │   │
│  │   (HTTP Client) │ │   (Context Mgmt)│ │  (Coordination) │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│  Subagent Layer                                                 │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │ Data Fetching   │ │ Data Validation │ │ Sync Coord      │   │
│  │ Agent           │ │ Agent           │ │ Agent           │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
│  ┌─────────────────┐ ┌─────────────────┐                       │
│  │ Cache Mgmt      │ │ Fallback Orch   │                       │
│  │ Agent           │ │ Agent           │                       │
│  └─────────────────┘ └─────────────────┘                       │
├─────────────────────────────────────────────────────────────────┤
│  Service Layer                                                  │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │ ExternalAPI     │ │ CacheManager    │ │ DataReconciler  │   │
│  │ Service         │ │ Service         │ │ Service         │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
├─────────────────────────────────────────────────────────────────┤
│  External APIs                                                  │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │ umapyoi.net     │ │ UmamusumeDB.com │ │ Umalator.com    │   │
│  │ (Primary)       │ │ (Secondary)     │ │ (Race Data)     │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

## Implementation Details

### 1. MCP-Enhanced API Client Service

See continuation in design document...

```php
<?php

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExternalAPIService
{
    private const API_SOURCES = [
        'umapyoi' => [
            'priority' => 1,
            'base_url' => 'https://api.umapyoi.net/api/v1',
            'timeout' => 5,
            'rate_limit' => 100, // requests per minute
        ],
        'umamusumedb' => [
            'priority' => 2,
            'base_url' => 'https://umamusumedb.com/api',
            'timeout' => 5,
            'rate_limit' => 60,
        ],
        'umalator' => [
            'priority' => 3,
            'base_url' => 'https://umalator.com/api',
            'timeout' => 5,
            'rate_limit' => 30,
        ],
    ];

    public function __construct(
        private MCPClientService $mcpClient,
        private CacheManagerService $cacheManager,
        private DataReconciliationService $reconciler
    ) {}

    /**
     * Fetch character data with MCP-enhanced fallback
     */
    public function fetchCharacterData(string $characterName): array
    {
        $cacheKey = "character_data:{$characterName}";
        
        // Try cache first
        if ($cached = $this->cacheManager->get($cacheKey)) {
            return $this->addMetadata($cached, 'cache');
        }

        // Try primary source with MCP fetch
        try {
            $data = $this->fetchFromSource('umapyoi', "/characters/{$characterName}");
            $this->cacheManager->put($cacheKey, $data, 86400); // 24 hours
            return $this->addMetadata($data, 'umapyoi');
        } catch (\Exception $e) {
            Log::warning('Primary API failed, trying fallback', [
                'character' => $characterName,
                'error' => $e->getMessage()
            ]);
        }

        // Fallback to secondary sources
        foreach (['umamusumedb', 'umalator'] as $source) {
            try {
                $data = $this->fetchFromSource($source, "/characters/{$characterName}");
                $this->cacheManager->put($cacheKey, $data, 86400);
                return $this->addMetadata($data, $source);
            } catch (\Exception $e) {
                continue;
            }
        }

        throw new \RuntimeException("Failed to fetch character data from all sources");
    }

    /**
     * Fetch using MCP fetch server
     */
    private function fetchFromSource(string $source, string $endpoint): array
    {
        $config = self::API_SOURCES[$source];
        $url = $config['base_url'] . $endpoint;

        // Use MCP fetch tool for enhanced HTTP capabilities
        $response = $this->mcpClient->callTool('fetch', 'fetch', [
            'url' => $url,
            'method' => 'GET',
            'timeout' => $config['timeout'],
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'UmamusumeCareerPlanner/1.0'
            ]
        ]);

        if (!$response['success']) {
            throw new \RuntimeException("API request failed: {$response['error']}");
        }

        return json_decode($response['body'], true);
    }

    private function addMetadata(array $data, string $source): array
    {
        return array_merge($data, [
            '_metadata' => [
                'source' => $source,
                'fetched_at' => now()->toISOString(),
                'cache_status' => $source === 'cache' ? 'hit' : 'miss'
            ]
        ]);
    }
}
```

### 2. MCP Subagent Coordination

```php
<?php

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;

class DataFetchingAgent
{
    public function __construct(private MCPClientService $mcpClient) {}

    /**
     * Coordinate parallel API calls using MCP agents
     */
    public function fetchMultipleResources(array $resources): array
    {
        // Create agent via strands-agents MCP server
        $agent = $this->mcpClient->createAgent([
            'name' => 'DataFetchingAgent',
            'model' => 'gpt-4',
            'tools' => ['fetch', 'context_management'],
            'instructions' => 'Fetch multiple resources in parallel with error handling'
        ]);

        $results = [];
        foreach ($resources as $resource) {
            try {
                $result = $agent->execute([
                    'action' => 'fetch_resource',
                    'resource' => $resource,
                    'timeout' => 5
                ]);
                $results[$resource['name']] = $result;
            } catch (\Exception $e) {
                $results[$resource['name']] = [
                    'error' => $e->getMessage(),
                    'fallback' => $this->getFallbackData($resource)
                ];
            }
        }

        return $results;
    }

    private function getFallbackData(array $resource): ?array
    {
        // Return cached data if available
        return Cache::get("fallback:{$resource['name']}");
    }
}
```

### 3. Intelligent Caching Strategy

```php
<?php

namespace App\Services\ExternalAPI;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class CacheManagerService
{
    private const TTL_CONFIG = [
        'character_data' => 86400,      // 24 hours
        'support_cards' => 43200,       // 12 hours
        'meta_rankings' => 21600,       // 6 hours
        'race_data' => 172800,          // 48 hours
        'skills' => 86400,              // 24 hours
    ];

    /**
     * Get data with staleness indicator
     */
    public function get(string $key): ?array
    {
        $data = Cache::get($key);
        
        if (!$data) {
            return null;
        }

        // Add staleness metadata
        $cachedAt = Cache::get("{$key}:timestamp");
        $age = now()->diffInSeconds($cachedAt);
        
        return array_merge($data, [
            '_cache' => [
                'cached_at' => $cachedAt,
                'age_seconds' => $age,
                'is_stale' => $age > $this->getTTL($key) * 0.8
            ]
        ]);
    }

    /**
     * Cache warming on application startup
     */
    public function warmCache(): void
    {
        $warmingTasks = [
            'top_characters' => fn() => $this->warmTopCharacters(),
            'top_support_cards' => fn() => $this->warmTopSupportCards(),
            'race_definitions' => fn() => $this->warmRaceDefinitions(),
        ];

        foreach ($warmingTasks as $task => $callback) {
            try {
                $callback();
                Log::info("Cache warming completed: {$task}");
            } catch (\Exception $e) {
                Log::error("Cache warming failed: {$task}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function warmTopCharacters(): void
    {
        // Fetch top 50 most popular characters
        $characters = ['Silence Suzuka', 'Tokai Teio', 'Gold Ship', /* ... */];
        
        foreach ($characters as $character) {
            $this->fetchAndCache("character_data:{$character}");
        }
    }

    private function getTTL(string $key): int
    {
        foreach (self::TTL_CONFIG as $pattern => $ttl) {
            if (str_contains($key, $pattern)) {
                return $ttl;
            }
        }
        return 3600; // Default 1 hour
    }
}
```

### 4. Data Reconciliation Service

```php
<?php

namespace App\Services\ExternalAPI;

class DataReconciliationService
{
    /**
     * Reconcile data from multiple sources
     */
    public function reconcile(array $sources): array
    {
        if (count($sources) === 1) {
            return reset($sources);
        }

        $reconciled = [];
        $conflicts = [];

        // Merge data with conflict detection
        foreach ($sources as $source => $data) {
            foreach ($data as $key => $value) {
                if (!isset($reconciled[$key])) {
                    $reconciled[$key] = [
                        'value' => $value,
                        'source' => $source,
                        'confidence' => $this->calculateConfidence($source)
                    ];
                } else {
                    // Conflict detected
                    if ($reconciled[$key]['value'] !== $value) {
                        $conflicts[$key] = [
                            'existing' => $reconciled[$key],
                            'new' => [
                                'value' => $value,
                                'source' => $source,
                                'confidence' => $this->calculateConfidence($source)
                            ]
                        ];

                        // Use higher confidence source
                        if ($this->calculateConfidence($source) > $reconciled[$key]['confidence']) {
                            $reconciled[$key] = [
                                'value' => $value,
                                'source' => $source,
                                'confidence' => $this->calculateConfidence($source)
                            ];
                        }
                    }
                }
            }
        }

        return [
            'data' => array_map(fn($item) => $item['value'], $reconciled),
            'conflicts' => $conflicts,
            'sources_used' => array_unique(array_column($reconciled, 'source'))
        ];
    }

    private function calculateConfidence(string $source): float
    {
        $confidenceMap = [
            'umapyoi' => 0.95,
            'umamusumedb' => 0.85,
            'umalator' => 0.80,
            'cache' => 0.70,
        ];

        return $confidenceMap[$source] ?? 0.50;
    }
}
```

### 5. Background Synchronization

```php
<?php

namespace App\Jobs;

use App\Services\ExternalAPI\ExternalAPIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncExternalDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;

    public function __construct(
        private string $dataType,
        private array $identifiers
    ) {}

    public function handle(ExternalAPIService $apiService): void
    {
        foreach ($this->identifiers as $identifier) {
            try {
                match($this->dataType) {
                    'character' => $apiService->fetchCharacterData($identifier),
                    'support_card' => $apiService->fetchSupportCardData($identifier),
                    'race' => $apiService->fetchRaceData($identifier),
                    default => throw new \InvalidArgumentException("Unknown data type: {$this->dataType}")
                };

                Log::info("Synced {$this->dataType}: {$identifier}");
            } catch (\Exception $e) {
                Log::error("Sync failed for {$this->dataType}: {$identifier}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
```

## Testing Strategy

### Unit Tests

- API client service methods
- Cache manager operations
- Data reconciliation logic
- Fallback mechanisms

### Integration Tests

- MCP server connectivity
- External API integration
- Cache warming process
- Background synchronization

### Performance Tests

- API response times
- Cache hit rates
- Concurrent request handling
- Failover speed

## Monitoring and Alerting

### Key Metrics

- API response times (p50, p95, p99)
- Cache hit/miss ratios
- Error rates by source
- Failover frequency
- Data staleness indicators

### Alerts

- API source unavailable > 5 minutes
- Cache hit rate < 80%
- Error rate > 5%
- Response time > 2 seconds
- Data staleness > 48 hours
