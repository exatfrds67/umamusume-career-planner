<?php

declare(strict_types=1);

use App\Services\ExternalAPI\CacheManagerService;
use App\Services\ExternalAPI\DataFetchingAgent;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    Cache::flush();

    $this->mcpClient = Mockery::mock(MCPClientService::class);
    $this->cacheManager = Mockery::mock(CacheManagerService::class);
    $this->agent = new DataFetchingAgent($this->mcpClient, $this->cacheManager);
});

afterEach(function () {
    Mockery::close();
});

describe('DataFetchingAgent - Resource Validation', function () {
    it('validates resources with all required fields', function () {
        $resources = [
            [
                'name' => 'character_1',
                'type' => 'character',
                'endpoint' => '/api/characters/1',
            ],
            [
                'name' => 'character_2',
                'type' => 'character',
                'endpoint' => '/api/characters/2',
            ],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['metadata']['total_resources'])->toBe(2);
    });

    it('filters out resources missing name field', function () {
        $resources = [
            [
                // Missing 'name'
                'type' => 'character',
                'endpoint' => '/api/characters/1',
            ],
            [
                'name' => 'character_2',
                'type' => 'character',
                'endpoint' => '/api/characters/2',
            ],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('warning')->once()->withArgs(fn ($message) => str_contains($message, 'Resource missing name field'));
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['metadata']['total_resources'])->toBe(2);
    });

    it('filters out resources missing type field', function () {
        $resources = [
            [
                'name' => 'character_1',
                // Missing 'type'
                'endpoint' => '/api/characters/1',
            ],
        ];

        Log::shouldReceive('info')->once();
        Log::shouldReceive('warning')->once()->withArgs(fn ($message) => str_contains($message, 'Resource missing type field'));

        $this->cacheManager->shouldReceive('get')->andReturn(null);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeFalse()
            ->and($result['metadata']['error'])->toBe('No valid resources to fetch');
    });

    it('filters out resources missing endpoint field', function () {
        $resources = [
            [
                'name' => 'character_1',
                'type' => 'character',
                // Missing 'endpoint'
            ],
        ];

        Log::shouldReceive('info')->once();
        Log::shouldReceive('warning')->once()->withArgs(fn ($message) => str_contains($message, 'Resource missing endpoint field'));

        $this->cacheManager->shouldReceive('get')->andReturn(null);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeFalse()
            ->and($result['metadata']['error'])->toBe('No valid resources to fetch');
    });

    it('normalizes resources with default values', function () {
        $resources = [
            [
                'name' => 'character_1',
                'type' => 'character',
                'endpoint' => '/api/characters/1',
                // No source, params, or priority specified
            ],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['metadata']['total_resources'])->toBe(1);
    });
});

describe('DataFetchingAgent - Cache Operations', function () {
    it('serves data from cache when available and fresh', function () {
        $resources = [
            [
                'name' => 'character_1',
                'type' => 'character',
                'endpoint' => '/api/characters/1',
            ],
        ];

        $cachedData = [
            'id' => 1,
            'name' => 'Test Character',
            '_cache' => [
                'cached_at' => now()->toISOString(),
                'age_seconds' => 10,
                'is_stale' => false,
            ],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')
            ->with('character:character_1')
            ->andReturn($cachedData);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeTrue()
            ->and($result['results']['character_1']['success'])->toBeTrue()
            ->and($result['results']['character_1']['source'])->toBe('cache')
            ->and($result['results']['character_1']['metadata']['cache_hit'])->toBeTrue();
    });

    it('fetches from API when cache is stale', function () {
        $resources = [
            [
                'name' => 'character_1',
                'type' => 'character',
                'endpoint' => '/api/characters/1',
            ],
        ];

        $staleCachedData = [
            'id' => 1,
            'name' => 'Test Character',
            '_cache' => [
                'cached_at' => now()->subDays(2)->toISOString(),
                'age_seconds' => 172800,
                'is_stale' => true,
            ],
        ];

        $freshData = [
            'id' => 1,
            'name' => 'Updated Character',
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')
            ->with('character:character_1')
            ->andReturn($staleCachedData);

        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')->andReturn([
            'success' => true,
            'status' => 200,
            'body' => json_encode($freshData),
        ]);

        $this->cacheManager->shouldReceive('put')
            ->with('character:character_1', $freshData)
            ->once();

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeTrue()
            ->and($result['results']['character_1']['metadata']['cache_hit'])->toBeFalse();
    });

    it('uses stale cache as fallback when API fails', function () {
        $resources = [
            [
                'name' => 'character_1',
                'type' => 'character',
                'endpoint' => '/api/characters/1',
            ],
        ];

        $staleCachedData = [
            'id' => 1,
            'name' => 'Test Character',
            '_cache' => [
                'cached_at' => now()->subDays(2)->toISOString(),
                'age_seconds' => 172800,
                'is_stale' => true,
            ],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('warning')->zeroOrMoreTimes();

        $this->cacheManager->shouldReceive('get')
            ->with('character:character_1')
            ->andReturn($staleCachedData);

        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')
            ->times(3)
            ->andThrow(new \RuntimeException('API failed'));

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeTrue()
            ->and($result['results']['character_1']['source'])->toBe('cache_fallback')
            ->and($result['results']['character_1']['metadata']['stale_data'])->toBeTrue();
    });
});

describe('DataFetchingAgent - Retry Logic', function () {
    it('retries failed requests up to max retries', function () {
        $resources = [
            [
                'name' => 'character_1',
                'type' => 'character',
                'endpoint' => '/api/characters/1',
            ],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('warning')->zeroOrMoreTimes();
        Log::shouldReceive('error')->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);

        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')
            ->times(3) // MAX_RETRIES
            ->andThrow(new \RuntimeException('Connection failed'));

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeFalse()
            ->and($result['results']['character_1']['success'])->toBeFalse()
            ->and($result['results']['character_1']['metadata']['attempts'])->toBe(3);
    });

    it('stops retrying on client errors (4xx)', function () {
        $resources = [
            [
                'name' => 'character_1',
                'type' => 'character',
                'endpoint' => '/api/characters/1',
            ],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);

        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')
            ->once() // Should not retry on 4xx
            ->andReturn([
                'success' => false,
                'status' => 404,
                'error' => 'Not found',
                'metadata' => [
                    'status_code' => 404,
                ],
            ]);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeFalse()
            ->and($result['results']['character_1']['metadata']['attempts'])->toBe(1);
    });

    it('succeeds on retry after initial failure', function () {
        $resources = [
            [
                'name' => 'character_1',
                'type' => 'character',
                'endpoint' => '/api/characters/1',
            ],
        ];

        $successData = ['id' => 1, 'name' => 'Test'];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('warning')->zeroOrMoreTimes();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->cacheManager->shouldReceive('put')->once();

        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')
            ->once()
            ->andThrow(new \RuntimeException('Temporary failure'));
        $this->mcpClient->shouldReceive('fetch')
            ->once()
            ->andReturn([
                'success' => true,
                'status' => 200,
                'body' => json_encode($successData),
            ]);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeTrue()
            ->and($result['results']['character_1']['success'])->toBeTrue()
            ->and($result['results']['character_1']['metadata']['attempts'])->toBe(2);
    });
});

describe('DataFetchingAgent - Batch Strategies', function () {
    it('processes resources sequentially by default', function () {
        $resources = [
            ['name' => 'char_1', 'type' => 'character', 'endpoint' => '/api/1'],
            ['name' => 'char_2', 'type' => 'character', 'endpoint' => '/api/2'],
            ['name' => 'char_3', 'type' => 'character', 'endpoint' => '/api/3'],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['metadata']['strategy_used'])->toBe('sequential');
    });

    it('processes resources by priority when specified', function () {
        $resources = [
            ['name' => 'low', 'type' => 'character', 'endpoint' => '/api/1', 'priority' => 4],
            ['name' => 'critical', 'type' => 'character', 'endpoint' => '/api/2', 'priority' => 1],
            ['name' => 'high', 'type' => 'character', 'endpoint' => '/api/3', 'priority' => 2],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchByPriority($resources);

        expect($result['metadata']['strategy_used'])->toBe('priority');
    });

    it('processes resources by type when specified', function () {
        $resources = [
            ['name' => 'char_1', 'type' => 'character', 'endpoint' => '/api/1'],
            ['name' => 'card_1', 'type' => 'support_card', 'endpoint' => '/api/2'],
            ['name' => 'char_2', 'type' => 'character', 'endpoint' => '/api/3'],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchByType($resources);

        expect($result['metadata']['strategy_used'])->toBe('type');
    });

    it('processes resources by source when specified', function () {
        $resources = [
            ['name' => 'char_1', 'type' => 'character', 'endpoint' => '/api/1', 'source' => 'umapyoi'],
            ['name' => 'char_2', 'type' => 'character', 'endpoint' => '/api/2', 'source' => 'cache'],
            ['name' => 'char_3', 'type' => 'character', 'endpoint' => '/api/3', 'source' => 'umapyoi'],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchBySource($resources);

        expect($result['metadata']['strategy_used'])->toBe('source');
    });
});

describe('DataFetchingAgent - Result Aggregation', function () {
    it('aggregates results by type', function () {
        $resources = [
            ['name' => 'char_1', 'type' => 'character', 'endpoint' => '/api/1'],
            ['name' => 'char_2', 'type' => 'character', 'endpoint' => '/api/2'],
            ['name' => 'card_1', 'type' => 'support_card', 'endpoint' => '/api/3'],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['aggregated']['by_type'])->toHaveKey('character')
            ->and($result['aggregated']['by_type'])->toHaveKey('support_card')
            ->and($result['aggregated']['by_type']['character']['count'])->toBe(2)
            ->and($result['aggregated']['by_type']['support_card']['count'])->toBe(1);
    });

    it('aggregates results by source', function () {
        $resources = [
            ['name' => 'char_1', 'type' => 'character', 'endpoint' => '/api/1'],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['aggregated']['by_source'])->toHaveKey('none')
            ->and($result['aggregated']['by_source']['none']['count'])->toBe(1);
    });

    it('aggregates results by status', function () {
        $resources = [
            ['name' => 'char_1', 'type' => 'character', 'endpoint' => '/api/1'],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['aggregated']['by_status'])->toHaveKey('successful')
            ->and($result['aggregated']['by_status'])->toHaveKey('failed')
            ->and($result['aggregated']['by_status'])->toHaveKey('cached')
            ->and($result['aggregated']['by_status'])->toHaveKey('stale_cache');
    });

    it('calculates cache hit rate in summary', function () {
        $resources = [
            ['name' => 'char_1', 'type' => 'character', 'endpoint' => '/api/1'],
            ['name' => 'char_2', 'type' => 'character', 'endpoint' => '/api/2'],
        ];

        $cachedData = [
            'id' => 1,
            'name' => 'Cached',
            '_cache' => ['is_stale' => false],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')
            ->with('character:char_1')
            ->andReturn($cachedData);
        $this->cacheManager->shouldReceive('get')
            ->with('character:char_2')
            ->andReturn(null);

        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['aggregated']['summary']['cache_hit_rate'])->toBe(50.0);
    });
});

describe('DataFetchingAgent - Performance Metrics', function () {
    it('tracks duration for fetch operations', function () {
        $resources = [
            ['name' => 'char_1', 'type' => 'character', 'endpoint' => '/api/1'],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['metadata'])->toHaveKey('duration_ms')
            ->and($result['metadata']['duration_ms'])->toBeGreaterThanOrEqual(0);
    });

    it('includes success rate in metadata', function () {
        $resources = [
            ['name' => 'char_1', 'type' => 'character', 'endpoint' => '/api/1'],
            ['name' => 'char_2', 'type' => 'character', 'endpoint' => '/api/2'],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['metadata'])->toHaveKey('success_rate')
            ->and($result['metadata']['success_rate'])->toBeFloat()
            ->and($result['metadata']['success_count'])->toBe(0)
            ->and($result['metadata']['failure_count'])->toBe(2);
    });
});

describe('DataFetchingAgent - MCP Client Integration', function () {
    it('handles MCP fetch server unavailable', function () {
        $resources = [
            ['name' => 'char_1', 'type' => 'character', 'endpoint' => '/api/1'],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeFalse()
            ->and($result['results']['char_1']['error'])->toContain('MCP fetch server not available');
    });

    it('handles JSON decode errors', function () {
        $resources = [
            ['name' => 'char_1', 'type' => 'character', 'endpoint' => '/api/1'],
        ];

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')->andReturn([
            'success' => true,
            'status' => 200,
            'body' => 'invalid json{',
        ]);

        $result = $this->agent->fetchMultipleResources($resources);

        expect($result['success'])->toBeFalse()
            ->and($result['results']['char_1']['error'])->toContain('Failed to decode JSON');
    });
});

describe('DataFetchingAgent - Edge Cases', function () {
    it('handles empty resource array', function () {
        Log::shouldReceive('info')->once();

        $result = $this->agent->fetchMultipleResources([]);

        expect($result['success'])->toBeFalse()
            ->and($result['metadata']['error'])->toBe('No valid resources to fetch');
    });

    it('handles max parallel limit', function () {
        $resources = array_map(fn ($i) => [
            'name' => "char_{$i}",
            'type' => 'character',
            'endpoint' => "/api/{$i}",
        ], range(1, 10));

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->atLeast()->once();
        Log::shouldReceive('error')->atLeast()->once();

        $this->cacheManager->shouldReceive('get')->andReturn(null);
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(false);

        $result = $this->agent->fetchMultipleResources($resources, [
            'max_parallel' => 3,
        ]);

        expect($result['metadata']['batches_processed'])->toBeGreaterThan(1);
    });
});
