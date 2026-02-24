<?php

declare(strict_types=1);

use App\Services\ApiResponseCachingService;
use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

describe('API Caching Property Tests', function () {
    beforeEach(function () {
        $this->apiCachingService = app(ApiResponseCachingService::class);
        $this->cacheManager = new CacheManagerService;
    });

    /**
     * Property 5: API Response Caching Round-Trip
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 5: API Response Caching Round-Trip
     * Validates: Requirements FR-08.6, INT-EXT-04
     *
     * Data cached via the API caching service must be retrievable
     * with the same structure and values.
     */
    it('ensures API response data is retrievable after caching', function () {
        for ($i = 0; $i < 100; $i++) {
            $key = 'api_roundtrip_'.$i.'_'.bin2hex(random_bytes(4));
            $data = [
                'id' => random_int(1, 10000),
                'name' => 'Character_'.$i,
                'stats' => [
                    'speed' => random_int(0, 1200),
                    'stamina' => random_int(0, 1200),
                    'power' => random_int(0, 1200),
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            $putResult = $this->cacheManager->put($key, $data);
            expect($putResult)->toBeTrue();

            $retrieved = $this->cacheManager->get($key);
            expect($retrieved)->not->toBeNull()
                ->and($retrieved['id'])->toBe($data['id'])
                ->and($retrieved['name'])->toBe($data['name'])
                ->and($retrieved['stats'])->toBe($data['stats'])
                ->and($retrieved)->toHaveKey('_cache');

            $this->cacheManager->delete($key);
        }
    })->group('property');

    it('ensures CacheManager reports existence correctly', function () {
        for ($i = 0; $i < 100; $i++) {
            $key = 'existence_test_'.$i.'_'.bin2hex(random_bytes(4));

            expect($this->cacheManager->has($key))->toBeFalse();

            $this->cacheManager->put($key, ['value' => $i]);
            expect($this->cacheManager->has($key))->toBeTrue();

            $this->cacheManager->delete($key);
            expect($this->cacheManager->has($key))->toBeFalse();
        }
    })->group('property');

    it('ensures cache metadata contains required staleness info', function () {
        for ($i = 0; $i < 50; $i++) {
            $key = 'metadata_test_'.$i.'_'.bin2hex(random_bytes(4));
            $data = ['value' => random_int(1, 1000)];

            $this->cacheManager->put($key, $data);
            $retrieved = $this->cacheManager->get($key);

            expect($retrieved)->not->toBeNull()
                ->and($retrieved)->toHaveKey('_cache')
                ->and($retrieved['_cache'])->toHaveKey('is_stale')
                ->and($retrieved['_cache']['is_stale'])->toBeBool();

            $this->cacheManager->delete($key);
        }
    })->group('property');

    it('ensures TTL config returns positive values for all data types', function () {
        $config = $this->cacheManager->getTTLConfig();

        expect($config)->toBeArray()
            ->and(count($config))->toBeGreaterThan(0);

        foreach ($config as $type => $ttl) {
            expect($ttl)->toBeInt()
                ->and($ttl)->toBeGreaterThan(0)
                ->and($type)->toBeString();
        }
    })->group('property');

    /**
     * Property 6: Request Coalescing Deduplication
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 6: Request Coalescing Deduplication
     * Validates: Requirements FR-08.6, INT-EXT-04
     *
     * The API caching service must generate deterministic cache keys
     * for identical requests.
     */
    it('ensures identical requests produce identical cache keys', function () {
        for ($i = 0; $i < 100; $i++) {
            $path = '/api/v1/characters/'.random_int(1, 1000);
            $queryParam = 'include='.['stats', 'skills', 'races'][random_int(0, 2)];

            $request1 = Request::create($path.'?'.$queryParam, 'GET');
            $request2 = Request::create($path.'?'.$queryParam, 'GET');

            $key1 = $this->apiCachingService->generateCacheKey($request1);
            $key2 = $this->apiCachingService->generateCacheKey($request2);

            expect($key1)->toBe($key2);
        }
    })->group('property');

    it('ensures different requests produce different cache keys', function () {
        $keys = [];

        for ($i = 0; $i < 100; $i++) {
            $path = '/api/v1/characters/'.$i;
            $request = Request::create($path, 'GET');
            $key = $this->apiCachingService->generateCacheKey($request);

            expect($key)->toBeString()
                ->and($key)->not->toBeEmpty();

            $keys[] = $key;
        }

        $uniqueKeys = array_unique($keys);
        expect(count($uniqueKeys))->toBe(count($keys));
    })->group('property');

    it('ensures statistics structure is consistent', function () {
        for ($i = 0; $i < 20; $i++) {
            $stats = $this->apiCachingService->getStatistics();

            expect($stats)->toBeArray()
                ->and($stats)->toHaveKey('hits')
                ->and($stats)->toHaveKey('misses')
                ->and($stats['hits'])->toBeInt()
                ->and($stats['misses'])->toBeInt()
                ->and($stats['hits'])->toBeGreaterThanOrEqual(0)
                ->and($stats['misses'])->toBeGreaterThanOrEqual(0);
        }
    })->group('property');
});
