<?php

declare(strict_types=1);

use App\Services\ExternalAPI\APIPerformanceMetricsService;
use App\Services\ExternalAPI\CacheManagerService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Cache::flush();

    // Configure MCP servers
    Config::set('mcp.enabled', true);
    Config::set('mcp.debug', false);
    Config::set('mcp.servers', [
        'fetch' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['mcp-server-fetch'],
            'capabilities' => ['http_client'],
        ],
    ]);

    $this->mcpClient = new MCPClientService;
    $this->mcpClient->healthCheck();

    $this->metricsService = Mockery::mock(APIPerformanceMetricsService::class);
    $this->metricsService->shouldReceive('recordResponseTime')->byDefault();
    $this->metricsService->shouldReceive('recordError')->byDefault();

    $this->cacheManager = new CacheManagerService;
});

afterEach(function () {
    Cache::flush();
    Mockery::close();
});

describe('External API Integration', function () {
    describe('API Response Caching', function () {
        it('caches API response data', function () {
            $key = 'character_data:test_character';
            $data = [
                'name' => 'Test Character',
                'speed' => 100,
                'stamina' => 90,
                'power' => 85,
            ];

            $this->cacheManager->put($key, $data);
            $cached = $this->cacheManager->get($key);

            expect($cached)->toBeArray()
                ->and($cached['name'])->toBe('Test Character')
                ->and($cached['speed'])->toBe(100);
        });

        it('adds cache metadata to retrieved data', function () {
            $key = 'support_cards:123';
            $data = ['name' => 'Test Support Card', 'rarity' => 'SSR'];

            $this->cacheManager->put($key, $data);
            $cached = $this->cacheManager->get($key);

            expect($cached)->toHaveKey('_cache')
                ->and($cached['_cache'])->toHaveKey('cached_at')
                ->and($cached['_cache'])->toHaveKey('age_seconds')
                ->and($cached['_cache'])->toHaveKey('is_stale')
                ->and($cached['_cache'])->toHaveKey('source')
                ->and($cached['_cache']['source'])->toBe('cache');
        });

        it('tracks cache statistics', function () {
            $this->cacheManager->resetStatistics();

            $key = 'character_data:test';
            $this->cacheManager->put($key, ['name' => 'Test']);

            // Generate hits and misses
            $this->cacheManager->get($key); // hit
            $this->cacheManager->get($key); // hit
            $this->cacheManager->get('non_existent'); // miss

            $stats = $this->cacheManager->getStatistics();

            expect($stats['hits'])->toBe(2)
                ->and($stats['misses'])->toBe(1)
                ->and($stats['total_requests'])->toBe(3)
                ->and($stats['hit_rate'])->toBeGreaterThan(60);
        });

        it('uses correct TTL for different data types', function () {
            expect($this->cacheManager->getTTL('character_data:test'))->toBe(86400)
                ->and($this->cacheManager->getTTL('support_cards:123'))->toBe(43200)
                ->and($this->cacheManager->getTTL('meta_rankings:speed'))->toBe(21600)
                ->and($this->cacheManager->getTTL('race_data:tokyo'))->toBe(172800);
        });
    });

    describe('API Fallback Mechanism', function () {
        it('stores fallback data in cache', function () {
            $key = 'character_data:fallback_test';
            $primaryData = ['name' => 'Primary Data', 'source' => 'primary'];

            $this->cacheManager->put($key, $primaryData);

            // Simulate fallback scenario - data should be available from cache
            $cached = $this->cacheManager->get($key);

            expect($cached)->toBeArray()
                ->and($cached['name'])->toBe('Primary Data');
        });

        it('handles cache miss gracefully', function () {
            $result = $this->cacheManager->get('non_existent_key');

            expect($result)->toBeNull();
        });

        it('deletes cache entries', function () {
            $key = 'character_data:delete_test';
            $this->cacheManager->put($key, ['name' => 'Test']);

            expect($this->cacheManager->has($key))->toBeTrue();

            $this->cacheManager->delete($key);

            expect($this->cacheManager->has($key))->toBeFalse();
        });
    });

    describe('Data Validation Integration', function () {
        it('validates cached data structure', function () {
            $key = 'character_data:validation_test';
            $validData = [
                'name' => 'Valid Character',
                'speed' => 100,
                'stamina' => 90,
                'power' => 85,
                'guts' => 80,
                'wit' => 75,
            ];

            $this->cacheManager->put($key, $validData);
            $cached = $this->cacheManager->get($key);

            expect($cached)->toHaveKey('name')
                ->and($cached)->toHaveKey('speed')
                ->and($cached)->toHaveKey('stamina')
                ->and($cached)->toHaveKey('power')
                ->and($cached)->toHaveKey('guts')
                ->and($cached)->toHaveKey('wit');
        });

        it('handles complex nested data structures', function () {
            $key = 'character_data:nested_test';
            $nestedData = [
                'name' => 'Nested Character',
                'stats' => [
                    'speed' => 100,
                    'stamina' => 90,
                ],
                'skills' => [
                    ['name' => 'Skill 1', 'level' => 3],
                    ['name' => 'Skill 2', 'level' => 5],
                ],
                'aptitudes' => [
                    'turf' => 'A',
                    'dirt' => 'B',
                ],
            ];

            $this->cacheManager->put($key, $nestedData);
            $cached = $this->cacheManager->get($key);

            expect($cached['stats'])->toBeArray()
                ->and($cached['stats']['speed'])->toBe(100)
                ->and($cached['skills'])->toHaveCount(2)
                ->and($cached['aptitudes']['turf'])->toBe('A');
        });
    });

    describe('Performance Metrics Integration', function () {
        it('provides cache information for monitoring', function () {
            $info = $this->cacheManager->getCacheInfo();

            expect($info)->toHaveKey('statistics')
                ->and($info)->toHaveKey('ttl_config')
                ->and($info)->toHaveKey('staleness_threshold');
        });

        it('tracks cache size', function () {
            $this->cacheManager->put('test:1', ['data' => 'value1']);
            $this->cacheManager->put('test:2', ['data' => 'value2']);

            $size = $this->cacheManager->getCacheSize();

            expect($size)->toHaveKey('total_keys')
                ->and($size)->toHaveKey('estimated_size_bytes');
        });
    });
});
