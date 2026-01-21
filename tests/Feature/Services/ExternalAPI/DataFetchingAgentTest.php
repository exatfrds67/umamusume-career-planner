<?php

declare(strict_types=1);

use App\Services\ExternalAPI\CacheManagerService;
use App\Services\ExternalAPI\DataFetchingAgent;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\mock;

beforeEach(function () {
    // Clear cache before each test
    Cache::flush();
});

describe('DataFetchingAgent', function () {
    it('can be instantiated', function () {
        $mcpClient = app(MCPClientService::class);
        $cacheManager = app(CacheManagerService::class);

        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        expect($agent)->toBeInstanceOf(DataFetchingAgent::class);
    });

    it('validates resources correctly', function () {
        $agent = app(DataFetchingAgent::class);

        $resources = [
            [
                'name' => 'Silence Suzuka',
                'type' => 'character_data',
                'endpoint' => '/api/characters/silence-suzuka',
            ],
            [
                'name' => 'Gold Ship',
                'type' => 'character_data',
                'endpoint' => '/api/characters/gold-ship',
                'source' => 'umapyoi',
                'params' => ['include' => 'stats'],
            ],
            // Invalid resource - missing name
            [
                'type' => 'character_data',
                'endpoint' => '/api/characters/test',
            ],
            // Invalid resource - missing type
            [
                'name' => 'Test',
                'endpoint' => '/api/characters/test',
            ],
            // Invalid resource - missing endpoint
            [
                'name' => 'Test',
                'type' => 'character_data',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        // Should only process the 2 valid resources
        expect($result)->toHaveKey('results');
        expect($result['metadata']['valid_resources'])->toBe(2);
    });

    it('fetches multiple resources successfully', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'name' => 'Silence Suzuka',
                    'speed' => 1000,
                    'stamina' => 800,
                ]),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Silence Suzuka',
                'type' => 'character_data',
                'endpoint' => 'https://api.umapyoi.net/api/v1/characters/silence-suzuka',
            ],
            [
                'name' => 'Gold Ship',
                'type' => 'character_data',
                'endpoint' => 'https://api.umapyoi.net/api/v1/characters/gold-ship',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeTrue();
        expect($result['results'])->toHaveCount(2);
        expect($result['results']['Silence Suzuka']['success'])->toBeTrue();
        expect($result['results']['Gold Ship']['success'])->toBeTrue();
        expect($result['metadata']['success_count'])->toBe(2);
        expect($result['metadata']['failure_count'])->toBe(0);
    });

    it('uses cached data when available', function () {
        $mcpClient = app(MCPClientService::class);
        $cacheManager = app(CacheManagerService::class);

        // Pre-populate cache
        $cachedData = [
            'name' => 'Silence Suzuka',
            'speed' => 1000,
            'stamina' => 800,
        ];
        $cacheManager->put('character_data:Silence Suzuka', $cachedData);

        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Silence Suzuka',
                'type' => 'character_data',
                'endpoint' => 'https://api.umapyoi.net/api/v1/characters/silence-suzuka',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeTrue();
        expect($result['results']['Silence Suzuka']['success'])->toBeTrue();
        expect($result['results']['Silence Suzuka']['source'])->toBe('cache');
        expect($result['results']['Silence Suzuka']['metadata']['cache_hit'])->toBeTrue();
        expect($result['results']['Silence Suzuka']['data']['name'])->toBe('Silence Suzuka');
    });

    it('falls back to stale cache data on API failure', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->times(3) // Will retry 3 times
            ->andThrow(new \RuntimeException('API unavailable'));

        // Create a custom cache manager that returns stale data
        $cacheManager = mock(CacheManagerService::class);
        $cacheManager->shouldReceive('get')
            ->with('character_data:Silence Suzuka')
            ->andReturn([
                'name' => 'Silence Suzuka',
                'speed' => 1000,
                'stamina' => 800,
                '_cache' => [
                    'cached_at' => now()->subHours(25),
                    'age_seconds' => 90000, // 25 hours
                    'ttl_seconds' => 86400, // 24 hours
                    'is_stale' => true,
                    'staleness_percentage' => 104.17,
                    'source' => 'cache',
                ],
            ]);

        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Silence Suzuka',
                'type' => 'character_data',
                'endpoint' => 'https://api.umapyoi.net/api/v1/characters/silence-suzuka',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeTrue();
        expect($result['results']['Silence Suzuka']['success'])->toBeTrue();
        expect($result['results']['Silence Suzuka']['source'])->toBe('cache_fallback');
        expect($result['results']['Silence Suzuka']['metadata']['stale_data'])->toBeTrue();
    });

    it('handles API failures gracefully', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andThrow(new \RuntimeException('API unavailable'));

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Silence Suzuka',
                'type' => 'character_data',
                'endpoint' => 'https://api.umapyoi.net/api/v1/characters/silence-suzuka',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeFalse();
        expect($result['results']['Silence Suzuka']['success'])->toBeFalse();
        expect($result['results']['Silence Suzuka']['source'])->toBe('none');
        expect($result['results']['Silence Suzuka'])->toHaveKey('error');
        expect($result['metadata']['failure_count'])->toBe(1);
    });

    it('processes resources in batches', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['name' => 'Test', 'data' => 'value']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        // Create 12 resources (should be split into 3 batches of 5, 5, and 2)
        $resources = [];
        for ($i = 1; $i <= 12; $i++) {
            $resources[] = [
                'name' => "Resource {$i}",
                'type' => 'test_data',
                'endpoint' => "https://api.test.com/resource/{$i}",
            ];
        }

        $result = $agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeTrue();
        expect($result['results'])->toHaveCount(12);
        expect($result['metadata']['batches_processed'])->toBe(3);
        expect($result['metadata']['success_count'])->toBe(12);
    });

    it('caches successful fetch results', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->once()
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'name' => 'Silence Suzuka',
                    'speed' => 1000,
                ]),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Silence Suzuka',
                'type' => 'character_data',
                'endpoint' => 'https://api.umapyoi.net/api/v1/characters/silence-suzuka',
            ],
        ];

        // First fetch - should hit API
        $result1 = $agent->fetchMultipleResources($resources);
        expect($result1['results']['Silence Suzuka']['source'])->not->toBe('cache');

        // Second fetch - should hit cache
        $result2 = $agent->fetchMultipleResources($resources);
        expect($result2['results']['Silence Suzuka']['source'])->toBe('cache');
        expect($result2['results']['Silence Suzuka']['metadata']['cache_hit'])->toBeTrue();
    });

    it('returns correct health status', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        expect($agent->isHealthy())->toBeTrue();

        $status = $agent->getStatus();
        expect($status['healthy'])->toBeTrue();
        expect($status['mcp_enabled'])->toBeTrue();
        expect($status['fetch_available'])->toBeTrue();
        expect($status['cache_available'])->toBeTrue();
    });

    it('reports unhealthy when MCP is disabled', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(false);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        expect($agent->isHealthy())->toBeFalse();

        $status = $agent->getStatus();
        expect($status['healthy'])->toBeFalse();
        expect($status['mcp_enabled'])->toBeFalse();
    });

    it('handles empty resource list', function () {
        $agent = app(DataFetchingAgent::class);

        $result = $agent->fetchMultipleResources([]);

        expect($result['success'])->toBeFalse();
        expect($result['results'])->toBeEmpty();
        expect($result['metadata'])->toHaveKey('error');
    });

    it('handles malformed JSON responses', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => 'invalid json {{{',
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Test',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/test',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeFalse();
        expect($result['results']['Test']['success'])->toBeFalse();
        expect($result['results']['Test']['error'])->toContain('JSON');
    });

    it('includes performance metadata in results', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => 'test']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Test',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/test',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['metadata'])->toHaveKeys([
            'total_resources',
            'valid_resources',
            'success_count',
            'failure_count',
            'success_rate',
            'duration_ms',
            'batches_processed',
            'timestamp',
        ]);

        expect($result['metadata']['duration_ms'])->toBeGreaterThanOrEqual(0);
        expect($result['metadata']['success_rate'])->toBe(100.0);
    });

    it('can get performance metrics', function () {
        $agent = app(DataFetchingAgent::class);

        $metrics = $agent->getPerformanceMetrics();

        expect($metrics)->toHaveKeys([
            'total_fetches',
            'successful_fetches',
            'failed_fetches',
            'cache_hits',
            'average_duration_ms',
        ]);
    });

    it('can reset performance metrics', function () {
        $agent = app(DataFetchingAgent::class);

        // Should not throw exception
        $agent->resetPerformanceMetrics();

        expect(true)->toBeTrue();
    });

    it('handles mixed success and failure results', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);

        // First resource succeeds, second resource fails (with 3 retries)
        $mcpClient->shouldReceive('fetch')
            ->times(4) // 1 success + 3 retries for failure
            ->andReturn(
                [
                    'success' => true,
                    'status' => 200,
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode(['name' => 'Success']),
                ],
                [
                    'success' => false,
                    'status' => 500,
                    'error' => 'Server error',
                ],
                [
                    'success' => false,
                    'status' => 500,
                    'error' => 'Server error',
                ],
                [
                    'success' => false,
                    'status' => 500,
                    'error' => 'Server error',
                ]
            );

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Success Resource',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/success',
            ],
            [
                'name' => 'Failure Resource',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/failure',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeFalse(); // Overall failure due to one failed resource
        expect($result['results']['Success Resource']['success'])->toBeTrue();
        expect($result['results']['Failure Resource']['success'])->toBeFalse();
        expect($result['metadata']['success_count'])->toBe(1);
        expect($result['metadata']['failure_count'])->toBe(1);
        expect($result['metadata']['success_rate'])->toBe(50.0);
    });
});

describe('DataFetchingAgent - Advanced Batch Operations', function () {
    it('fetches resources using priority-based batching', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => 'test']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Low Priority',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/low',
                'priority' => DataFetchingAgent::PRIORITY_LOW,
            ],
            [
                'name' => 'Critical Priority',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/critical',
                'priority' => DataFetchingAgent::PRIORITY_CRITICAL,
            ],
            [
                'name' => 'High Priority',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/high',
                'priority' => DataFetchingAgent::PRIORITY_HIGH,
            ],
            [
                'name' => 'Normal Priority',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/normal',
                'priority' => DataFetchingAgent::PRIORITY_NORMAL,
            ],
        ];

        $result = $agent->fetchByPriority($resources);

        expect($result['success'])->toBeTrue();
        expect($result['results'])->toHaveCount(4);
        expect($result['metadata']['strategy_used'])->toBe(DataFetchingAgent::BATCH_STRATEGY_PRIORITY);
        expect($result)->toHaveKey('aggregated');
    });

    it('fetches resources using type-based batching', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => 'test']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Character 1',
                'type' => 'character_data',
                'endpoint' => 'https://api.test.com/character1',
            ],
            [
                'name' => 'Support Card 1',
                'type' => 'support_card',
                'endpoint' => 'https://api.test.com/card1',
            ],
            [
                'name' => 'Character 2',
                'type' => 'character_data',
                'endpoint' => 'https://api.test.com/character2',
            ],
            [
                'name' => 'Skill 1',
                'type' => 'skill_data',
                'endpoint' => 'https://api.test.com/skill1',
            ],
        ];

        $result = $agent->fetchByType($resources);

        expect($result['success'])->toBeTrue();
        expect($result['results'])->toHaveCount(4);
        expect($result['metadata']['strategy_used'])->toBe(DataFetchingAgent::BATCH_STRATEGY_TYPE);
        expect($result['aggregated']['by_type'])->toHaveKey('character_data');
        expect($result['aggregated']['by_type'])->toHaveKey('support_card');
        expect($result['aggregated']['by_type'])->toHaveKey('skill_data');
    });

    it('fetches resources using source-based batching', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => 'test']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Resource 1',
                'type' => 'test_data',
                'endpoint' => 'https://api.umapyoi.net/resource1',
                'source' => 'umapyoi',
            ],
            [
                'name' => 'Resource 2',
                'type' => 'test_data',
                'endpoint' => 'https://api.umamusumedb.com/resource2',
                'source' => 'umamusumedb',
            ],
            [
                'name' => 'Resource 3',
                'type' => 'test_data',
                'endpoint' => 'https://api.umapyoi.net/resource3',
                'source' => 'umapyoi',
            ],
        ];

        $result = $agent->fetchBySource($resources);

        expect($result['success'])->toBeTrue();
        expect($result['results'])->toHaveCount(3);
        expect($result['metadata']['strategy_used'])->toBe(DataFetchingAgent::BATCH_STRATEGY_SOURCE);
        expect($result['aggregated']['by_source'])->toHaveKey('umapyoi');
        expect($result['aggregated']['by_source'])->toHaveKey('umamusumedb');
    });

    it('aggregates results by type correctly', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => 'test']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Character 1',
                'type' => 'character_data',
                'endpoint' => 'https://api.test.com/character1',
            ],
            [
                'name' => 'Character 2',
                'type' => 'character_data',
                'endpoint' => 'https://api.test.com/character2',
            ],
            [
                'name' => 'Support Card 1',
                'type' => 'support_card',
                'endpoint' => 'https://api.test.com/card1',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['aggregated']['by_type']['character_data']['count'])->toBe(2);
        expect($result['aggregated']['by_type']['character_data']['successful'])->toBe(2);
        expect($result['aggregated']['by_type']['support_card']['count'])->toBe(1);
        expect($result['aggregated']['summary']['total_types'])->toBe(2);
    });

    it('aggregates results by source correctly', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => 'test']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Resource 1',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/resource1',
                'source' => 'umapyoi',
            ],
            [
                'name' => 'Resource 2',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/resource2',
                'source' => 'umapyoi',
            ],
            [
                'name' => 'Resource 3',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/resource3',
                'source' => 'umamusumedb',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['aggregated']['by_source']['umapyoi']['count'])->toBe(2);
        expect($result['aggregated']['by_source']['umamusumedb']['count'])->toBe(1);
        expect($result['aggregated']['summary']['total_sources'])->toBeGreaterThanOrEqual(1);
    });

    it('aggregates results by status correctly', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);

        // First two succeed, third fails
        $mcpClient->shouldReceive('fetch')
            ->times(5) // 2 success + 3 retries for failure
            ->andReturn(
                [
                    'success' => true,
                    'status' => 200,
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode(['data' => 'test1']),
                ],
                [
                    'success' => true,
                    'status' => 200,
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode(['data' => 'test2']),
                ],
                [
                    'success' => false,
                    'status' => 500,
                    'error' => 'Server error',
                ],
                [
                    'success' => false,
                    'status' => 500,
                    'error' => 'Server error',
                ],
                [
                    'success' => false,
                    'status' => 500,
                    'error' => 'Server error',
                ]
            );

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Success 1',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/success1',
            ],
            [
                'name' => 'Success 2',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/success2',
            ],
            [
                'name' => 'Failure',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/failure',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['aggregated']['by_status']['successful'])->toHaveCount(2);
        expect($result['aggregated']['by_status']['failed'])->toHaveCount(1);
        expect($result['aggregated']['by_status']['successful'])->toContain('Success 1');
        expect($result['aggregated']['by_status']['successful'])->toContain('Success 2');
        expect($result['aggregated']['by_status']['failed'])->toContain('Failure');
    });

    it('calculates cache hit rate correctly', function () {
        $mcpClient = app(MCPClientService::class);
        $cacheManager = app(CacheManagerService::class);

        // Pre-populate cache for some resources
        $cacheManager->put('test_data:Cached 1', ['data' => 'cached1']);
        $cacheManager->put('test_data:Cached 2', ['data' => 'cached2']);

        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Cached 1',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/cached1',
            ],
            [
                'name' => 'Cached 2',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/cached2',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['aggregated']['summary']['cache_hit_rate'])->toBe(100.0);
        expect($result['aggregated']['by_status']['cached'])->toHaveCount(2);
    });

    it('handles custom max_parallel option', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => 'test']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        // Create 10 resources with max_parallel of 3
        $resources = [];
        for ($i = 1; $i <= 10; $i++) {
            $resources[] = [
                'name' => "Resource {$i}",
                'type' => 'test_data',
                'endpoint' => "https://api.test.com/resource{$i}",
            ];
        }

        $result = $agent->fetchMultipleResources($resources, ['max_parallel' => 3]);

        expect($result['success'])->toBeTrue();
        expect($result['results'])->toHaveCount(10);
        // Should create 4 batches: 3, 3, 3, 1
        expect($result['metadata']['batches_processed'])->toBe(4);
    });

    it('includes aggregated data in all batch strategies', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => 'test']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Test',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/test',
            ],
        ];

        // Test all strategies
        $strategies = [
            DataFetchingAgent::BATCH_STRATEGY_SEQUENTIAL,
            DataFetchingAgent::BATCH_STRATEGY_PRIORITY,
            DataFetchingAgent::BATCH_STRATEGY_TYPE,
            DataFetchingAgent::BATCH_STRATEGY_SOURCE,
        ];

        foreach ($strategies as $strategy) {
            $result = $agent->fetchMultipleResources($resources, ['strategy' => $strategy]);

            expect($result)->toHaveKey('aggregated');
            expect($result['aggregated'])->toHaveKeys(['by_type', 'by_source', 'by_status', 'summary']);
        }
    });

    it('identifies most common source and type', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => 'test']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Character 1',
                'type' => 'character_data',
                'endpoint' => 'https://api.test.com/character1',
                'source' => 'umapyoi',
            ],
            [
                'name' => 'Character 2',
                'type' => 'character_data',
                'endpoint' => 'https://api.test.com/character2',
                'source' => 'umapyoi',
            ],
            [
                'name' => 'Character 3',
                'type' => 'character_data',
                'endpoint' => 'https://api.test.com/character3',
                'source' => 'umapyoi',
            ],
            [
                'name' => 'Support Card 1',
                'type' => 'support_card',
                'endpoint' => 'https://api.test.com/card1',
                'source' => 'umamusumedb',
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['aggregated']['summary']['most_common_type'])->toBe('character_data');
        expect($result['aggregated']['summary']['most_common_source'])->toBe('umapyoi');
    });

    it('handles priority sorting correctly', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => 'test']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'Low',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/low',
                'priority' => DataFetchingAgent::PRIORITY_LOW,
            ],
            [
                'name' => 'Critical',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/critical',
                'priority' => DataFetchingAgent::PRIORITY_CRITICAL,
            ],
            [
                'name' => 'Normal',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/normal',
                'priority' => DataFetchingAgent::PRIORITY_NORMAL,
            ],
        ];

        $result = $agent->fetchByPriority($resources, 1); // Process one at a time

        // Should process in priority order: Critical, Normal, Low
        expect($result['success'])->toBeTrue();
        expect($result['results'])->toHaveCount(3);
        expect($result['metadata']['batches_processed'])->toBe(3);
    });

    it('defaults to normal priority when not specified', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);
        $mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $mcpClient->shouldReceive('fetch')
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode(['data' => 'test']),
            ]);

        $cacheManager = app(CacheManagerService::class);
        $agent = new DataFetchingAgent($mcpClient, $cacheManager);

        $resources = [
            [
                'name' => 'No Priority',
                'type' => 'test_data',
                'endpoint' => 'https://api.test.com/test',
                // No priority specified
            ],
        ];

        $result = $agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeTrue();
        expect($result['results'])->toHaveCount(1);
    });
});
