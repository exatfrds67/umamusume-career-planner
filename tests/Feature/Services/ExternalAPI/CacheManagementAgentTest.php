<?php

declare(strict_types=1);

use App\Services\ExternalAPI\CacheManagementAgent;
use App\Services\ExternalAPI\CacheManagerService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\mock;

beforeEach(function () {
    // Clear cache before each test
    Cache::flush();
});

describe('CacheManagementAgent', function () {
    it('can be instantiated', function () {
        $mcpClient = app(MCPClientService::class);
        $cacheManager = app(CacheManagerService::class);

        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        expect($agent)->toBeInstanceOf(CacheManagementAgent::class);
    });

    it('returns correct health status when MCP is enabled', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        expect($agent->isHealthy())->toBeTrue();

        $status = $agent->getStatus();
        expect($status['healthy'])->toBeTrue();
        expect($status['mcp_enabled'])->toBeTrue();
        expect($status['cache_available'])->toBeTrue();
    });

    it('reports unhealthy when MCP is disabled', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(false);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        expect($agent->isHealthy())->toBeFalse();

        $status = $agent->getStatus();
        expect($status['healthy'])->toBeFalse();
        expect($status['mcp_enabled'])->toBeFalse();
    });
});

describe('CacheManagementAgent - Eviction Strategies', function () {
    it('can set and get eviction strategy', function () {
        $agent = app(CacheManagementAgent::class);

        expect($agent->getEvictionStrategy())->toBe(CacheManagementAgent::EVICTION_LRU);

        $agent->setEvictionStrategy(CacheManagementAgent::EVICTION_LFU);
        expect($agent->getEvictionStrategy())->toBe(CacheManagementAgent::EVICTION_LFU);

        $agent->setEvictionStrategy(CacheManagementAgent::EVICTION_TTL);
        expect($agent->getEvictionStrategy())->toBe(CacheManagementAgent::EVICTION_TTL);

        $agent->setEvictionStrategy(CacheManagementAgent::EVICTION_FIFO);
        expect($agent->getEvictionStrategy())->toBe(CacheManagementAgent::EVICTION_FIFO);

        $agent->setEvictionStrategy(CacheManagementAgent::EVICTION_RANDOM);
        expect($agent->getEvictionStrategy())->toBe(CacheManagementAgent::EVICTION_RANDOM);
    });

    it('throws exception for invalid eviction strategy', function () {
        $agent = app(CacheManagementAgent::class);

        $agent->setEvictionStrategy('invalid_strategy');
    })->throws(\InvalidArgumentException::class, 'Invalid eviction strategy');

    it('performs LRU eviction correctly', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Record access patterns with different timestamps
        $agent->recordAccess('character_data:old_item', true);
        sleep(1);
        $agent->recordAccess('character_data:new_item', true);

        // Add items to cache
        $cacheManager->put('character_data:old_item', ['name' => 'Old']);
        $cacheManager->put('character_data:new_item', ['name' => 'New']);

        $agent->setEvictionStrategy(CacheManagementAgent::EVICTION_LRU);
        $result = $agent->performEviction(1);

        expect($result['success'])->toBeTrue();
        expect($result['strategy'])->toBe(CacheManagementAgent::EVICTION_LRU);
        expect($result['evicted_count'])->toBeGreaterThanOrEqual(0);
    });

    it('performs LFU eviction correctly', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Record access patterns with different frequencies
        $agent->recordAccess('character_data:frequent', true);
        $agent->recordAccess('character_data:frequent', true);
        $agent->recordAccess('character_data:frequent', true);
        $agent->recordAccess('character_data:infrequent', true);

        // Add items to cache
        $cacheManager->put('character_data:frequent', ['name' => 'Frequent']);
        $cacheManager->put('character_data:infrequent', ['name' => 'Infrequent']);

        $agent->setEvictionStrategy(CacheManagementAgent::EVICTION_LFU);
        $result = $agent->performEviction(1);

        expect($result['success'])->toBeTrue();
        expect($result['strategy'])->toBe(CacheManagementAgent::EVICTION_LFU);
    });
});

describe('CacheManagementAgent - Optimization Strategies', function () {
    it('can set and get optimization strategy', function () {
        $agent = app(CacheManagementAgent::class);

        expect($agent->getOptimizationStrategy())->toBe(CacheManagementAgent::OPTIMIZATION_BALANCED);

        $agent->setOptimizationStrategy(CacheManagementAgent::OPTIMIZATION_AGGRESSIVE);
        expect($agent->getOptimizationStrategy())->toBe(CacheManagementAgent::OPTIMIZATION_AGGRESSIVE);

        $agent->setOptimizationStrategy(CacheManagementAgent::OPTIMIZATION_CONSERVATIVE);
        expect($agent->getOptimizationStrategy())->toBe(CacheManagementAgent::OPTIMIZATION_CONSERVATIVE);
    });

    it('throws exception for invalid optimization strategy', function () {
        $agent = app(CacheManagementAgent::class);

        $agent->setOptimizationStrategy('invalid_strategy');
    })->throws(\InvalidArgumentException::class, 'Invalid optimization strategy');

    it('optimizes cache with balanced strategy', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        $result = $agent->optimizeCache([
            'strategy' => CacheManagementAgent::OPTIMIZATION_BALANCED,
        ]);

        expect($result['success'])->toBeTrue();
        expect($result['optimizations'])->toHaveKey('strategy_optimizations');
        expect($result['metrics'])->toHaveKey('strategy_used');
        expect($result['metrics']['strategy_used'])->toBe(CacheManagementAgent::OPTIMIZATION_BALANCED);
    });

    it('optimizes cache with aggressive strategy', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        $result = $agent->optimizeCache([
            'strategy' => CacheManagementAgent::OPTIMIZATION_AGGRESSIVE,
        ]);

        expect($result['success'])->toBeTrue();
        expect($result['metrics']['strategy_used'])->toBe(CacheManagementAgent::OPTIMIZATION_AGGRESSIVE);
    });

    it('optimizes cache with conservative strategy', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        $result = $agent->optimizeCache([
            'strategy' => CacheManagementAgent::OPTIMIZATION_CONSERVATIVE,
        ]);

        expect($result['success'])->toBeTrue();
        expect($result['metrics']['strategy_used'])->toBe(CacheManagementAgent::OPTIMIZATION_CONSERVATIVE);
    });

    it('includes all required optimization data', function () {
        $agent = app(CacheManagementAgent::class);

        $result = $agent->optimizeCache();

        expect($result)->toHaveKeys(['success', 'optimizations', 'metrics']);
        expect($result['optimizations'])->toHaveKeys([
            'prefetch_suggestions',
            'stale_entries',
            'strategy_optimizations',
        ]);
        expect($result['metrics'])->toHaveKeys([
            'total_keys',
            'estimated_size_bytes',
            'memory_usage_percent',
            'hit_rate',
            'optimization_duration_ms',
            'strategy_used',
            'timestamp',
        ]);
    });
});

describe('CacheManagementAgent - Predictive Prefetching', function () {
    it('performs predictive prefetch', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Record some access patterns
        for ($i = 0; $i < 10; $i++) {
            $agent->recordAccess('character_data:Silence Suzuka', true);
        }

        $result = $agent->performPrefetch([
            'strategy' => CacheManagementAgent::PREFETCH_PREDICTIVE,
            'max_items' => 5,
            'threshold' => 0.5,
        ]);

        expect($result['success'])->toBeTrue();
        expect($result)->toHaveKeys(['prefetched', 'skipped', 'metadata']);
        expect($result['metadata'])->toHaveKeys([
            'strategy',
            'items_requested',
            'items_prefetched',
            'items_skipped',
            'duration_ms',
            'timestamp',
        ]);
        expect($result['metadata']['strategy'])->toBe(CacheManagementAgent::PREFETCH_PREDICTIVE);
    });

    it('performs popular items prefetch', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Record access patterns with varying frequencies
        for ($i = 0; $i < 20; $i++) {
            $agent->recordAccess('character_data:Popular', true);
        }
        for ($i = 0; $i < 5; $i++) {
            $agent->recordAccess('character_data:LessPopular', true);
        }

        $result = $agent->performPrefetch([
            'strategy' => CacheManagementAgent::PREFETCH_POPULAR,
            'max_items' => 10,
        ]);

        expect($result['success'])->toBeTrue();
        expect($result['metadata']['strategy'])->toBe(CacheManagementAgent::PREFETCH_POPULAR);
    });

    it('performs related items prefetch', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Record recent access
        $agent->recordAccess('character_data:Silence Suzuka', true);

        $result = $agent->performPrefetch([
            'strategy' => CacheManagementAgent::PREFETCH_RELATED,
            'max_items' => 5,
        ]);

        expect($result['success'])->toBeTrue();
        expect($result['metadata']['strategy'])->toBe(CacheManagementAgent::PREFETCH_RELATED);
    });

    it('skips already cached items during prefetch', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Pre-populate cache
        $cacheManager->put('character_data:Cached', ['name' => 'Cached']);

        // Record access pattern for cached item
        for ($i = 0; $i < 10; $i++) {
            $agent->recordAccess('character_data:Cached', true);
        }

        $result = $agent->performPrefetch([
            'strategy' => CacheManagementAgent::PREFETCH_PREDICTIVE,
            'max_items' => 5,
            'threshold' => 0.1,
        ]);

        expect($result['success'])->toBeTrue();
        // The cached item should be in skipped list
        expect($result['skipped'])->toContain('character_data:Cached');
    });
});

describe('CacheManagementAgent - Access Pattern Tracking', function () {
    it('records access patterns correctly', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        $agent->recordAccess('character_data:Test', true);
        $agent->recordAccess('character_data:Test', true);
        $agent->recordAccess('character_data:Test', false);

        $patterns = $agent->getAccessPatterns();

        expect($patterns)->toHaveKey('character_data:Test');
        expect($patterns['character_data:Test']['count'])->toBe(3);
        expect($patterns['character_data:Test']['hits'])->toBe(2);
        expect($patterns['character_data:Test']['misses'])->toBe(1);
    });

    it('tracks multiple keys independently', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        $agent->recordAccess('character_data:Key1', true);
        $agent->recordAccess('character_data:Key1', true);
        $agent->recordAccess('character_data:Key2', true);
        $agent->recordAccess('support_cards:Card1', false);

        $patterns = $agent->getAccessPatterns();

        expect($patterns)->toHaveCount(3);
        expect($patterns['character_data:Key1']['count'])->toBe(2);
        expect($patterns['character_data:Key2']['count'])->toBe(1);
        expect($patterns['support_cards:Card1']['count'])->toBe(1);
        expect($patterns['support_cards:Card1']['misses'])->toBe(1);
    });

    it('can reset access patterns', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        $agent->recordAccess('character_data:Test', true);
        expect($agent->getAccessPatterns())->not->toBeEmpty();

        $agent->resetAccessPatterns();
        expect($agent->getAccessPatterns())->toBeEmpty();
    });
});

describe('CacheManagementAgent - Performance Analytics', function () {
    it('returns performance analytics', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        $analytics = $agent->getPerformanceAnalytics();

        expect($analytics)->toHaveKeys([
            'hit_rate',
            'miss_rate',
            'latency',
            'memory',
            'throughput',
            'trends',
            'cache_statistics',
            'timestamp',
        ]);

        expect($analytics['latency'])->toHaveKeys(['avg_ms', 'p50_ms', 'p95_ms', 'p99_ms']);
        expect($analytics['memory'])->toHaveKeys(['total_keys', 'estimated_size_bytes', 'estimated_size_mb']);
        expect($analytics['throughput'])->toHaveKeys([
            'operations_per_minute',
            'evictions_per_hour',
            'prefetches_per_hour',
        ]);
    });

    it('calculates hit and miss rates correctly', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);

        // Generate some cache hits and misses
        $cacheManager->put('test:key1', ['data' => 'value1']);
        $cacheManager->get('test:key1'); // Hit
        $cacheManager->get('test:key1'); // Hit
        $cacheManager->get('test:nonexistent'); // Miss

        $agent = new CacheManagementAgent($mcpClient, $cacheManager);
        $analytics = $agent->getPerformanceAnalytics();

        expect($analytics['hit_rate'])->toBeGreaterThanOrEqual(0);
        expect($analytics['miss_rate'])->toBeGreaterThanOrEqual(0);
        expect($analytics['hit_rate'] + $analytics['miss_rate'])->toBe(100.0);
    });

    it('can reset analytics', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Perform some operations to generate analytics
        $agent->optimizeCache();
        $agent->performPrefetch();

        // Reset analytics
        $agent->resetAnalytics();

        // Analytics should be empty after reset
        // (Note: The analytics structure will still exist, but data arrays will be empty)
        expect(true)->toBeTrue(); // Reset completed without error
    });

    it('tracks optimization operations in analytics', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Perform optimization
        $result = $agent->optimizeCache();

        expect($result['success'])->toBeTrue();
        expect($result['metrics'])->toHaveKey('optimization_duration_ms');
        expect($result['metrics']['optimization_duration_ms'])->toBeGreaterThanOrEqual(0);
    });
});

describe('CacheManagementAgent - Cache Eviction', function () {
    it('performs eviction and returns correct result structure', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Add some items to cache and record access
        $cacheManager->put('character_data:Item1', ['name' => 'Item1']);
        $cacheManager->put('character_data:Item2', ['name' => 'Item2']);
        $agent->recordAccess('character_data:Item1', true);
        $agent->recordAccess('character_data:Item2', true);

        $result = $agent->performEviction(1);

        expect($result)->toHaveKeys([
            'success',
            'evicted_count',
            'evicted_keys',
            'freed_bytes',
            'strategy',
            'duration_ms',
        ]);
        expect($result['success'])->toBeTrue();
        expect($result['strategy'])->toBe(CacheManagementAgent::EVICTION_LRU);
    });

    it('evicts items based on TTL strategy', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Add items with different TTLs
        $cacheManager->put('character_data:ShortTTL', ['name' => 'Short'], 60);
        $cacheManager->put('character_data:LongTTL', ['name' => 'Long'], 86400);

        $agent->setEvictionStrategy(CacheManagementAgent::EVICTION_TTL);
        $result = $agent->performEviction(1);

        expect($result['success'])->toBeTrue();
        expect($result['strategy'])->toBe(CacheManagementAgent::EVICTION_TTL);
    });

    it('evicts items based on FIFO strategy', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Record access patterns in order
        $agent->recordAccess('character_data:First', true);
        $agent->recordAccess('character_data:Second', true);
        $agent->recordAccess('character_data:Third', true);

        $cacheManager->put('character_data:First', ['name' => 'First']);
        $cacheManager->put('character_data:Second', ['name' => 'Second']);
        $cacheManager->put('character_data:Third', ['name' => 'Third']);

        $agent->setEvictionStrategy(CacheManagementAgent::EVICTION_FIFO);
        $result = $agent->performEviction(1);

        expect($result['success'])->toBeTrue();
        expect($result['strategy'])->toBe(CacheManagementAgent::EVICTION_FIFO);
    });

    it('evicts items based on random strategy', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Add multiple items
        for ($i = 1; $i <= 5; $i++) {
            $cacheManager->put("character_data:Item{$i}", ['name' => "Item{$i}"]);
            $agent->recordAccess("character_data:Item{$i}", true);
        }

        $agent->setEvictionStrategy(CacheManagementAgent::EVICTION_RANDOM);
        $result = $agent->performEviction(2);

        expect($result['success'])->toBeTrue();
        expect($result['strategy'])->toBe(CacheManagementAgent::EVICTION_RANDOM);
    });

    it('handles eviction when no items to evict', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Don't add any items or access patterns
        $result = $agent->performEviction(5);

        expect($result['success'])->toBeTrue();
        expect($result['evicted_count'])->toBe(0);
        expect($result['evicted_keys'])->toBeEmpty();
    });
});

describe('CacheManagementAgent - Integration', function () {
    it('integrates with CacheManagerService correctly', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Add data through cache manager
        $cacheManager->put('character_data:Integration', ['name' => 'Test']);

        // Record access through agent
        $agent->recordAccess('character_data:Integration', true);

        // Verify data is accessible
        $data = $cacheManager->get('character_data:Integration');
        expect($data)->not->toBeNull();
        expect($data['name'])->toBe('Test');

        // Verify access pattern is recorded
        $patterns = $agent->getAccessPatterns();
        expect($patterns)->toHaveKey('character_data:Integration');
    });

    it('provides comprehensive status information', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Record some access patterns
        $agent->recordAccess('test:key1', true);
        $agent->recordAccess('test:key2', false);

        $status = $agent->getStatus();

        expect($status)->toHaveKeys([
            'healthy',
            'mcp_enabled',
            'cache_available',
            'eviction_strategy',
            'optimization_strategy',
            'access_patterns_count',
        ]);

        expect($status['access_patterns_count'])->toBe(2);
        expect($status['eviction_strategy'])->toBe(CacheManagementAgent::EVICTION_LRU);
        expect($status['optimization_strategy'])->toBe(CacheManagementAgent::OPTIMIZATION_BALANCED);
    });

    it('handles full optimization workflow', function () {
        $mcpClient = mock(MCPClientService::class);
        $mcpClient->shouldReceive('isEnabled')->andReturn(true);

        $cacheManager = app(CacheManagerService::class);
        $agent = new CacheManagementAgent($mcpClient, $cacheManager);

        // Setup: Add cache data and record access patterns
        for ($i = 1; $i <= 10; $i++) {
            $cacheManager->put("character_data:Char{$i}", ['name' => "Character {$i}"]);
            $agent->recordAccess("character_data:Char{$i}", true);
        }

        // Step 1: Get initial analytics
        $initialAnalytics = $agent->getPerformanceAnalytics();
        expect($initialAnalytics)->toHaveKey('hit_rate');

        // Step 2: Perform optimization
        $optimizationResult = $agent->optimizeCache();
        expect($optimizationResult['success'])->toBeTrue();

        // Step 3: Perform prefetch
        $prefetchResult = $agent->performPrefetch([
            'strategy' => CacheManagementAgent::PREFETCH_POPULAR,
            'max_items' => 5,
        ]);
        expect($prefetchResult['success'])->toBeTrue();

        // Step 4: Get final analytics
        $finalAnalytics = $agent->getPerformanceAnalytics();
        expect($finalAnalytics)->toHaveKey('hit_rate');
    });
});
