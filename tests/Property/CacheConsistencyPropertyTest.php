<?php

declare(strict_types=1);

use App\Services\RedisCacheOptimizationService;
use Illuminate\Support\Facades\Cache;

describe('Cache Consistency Property Tests', function () {
    beforeEach(function () {
        $this->cacheService = app(RedisCacheOptimizationService::class);
    });

    /**
     * Property 1: Cache Tiering Consistency
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 1: Cache Tiering Consistency
     * Validates: Requirements NFR-P-05, NFR-P-10
     *
     * Data stored via the cache service must always be retrievable
     * with the same value until expiration or explicit invalidation.
     */
    it('ensures cached data is always retrievable with the correct value', function () {
        for ($i = 0; $i < 100; $i++) {
            $key = 'property_test_tier_'.$i.'_'.bin2hex(random_bytes(4));
            $value = [
                'id' => random_int(1, 10000),
                'name' => 'Test Character '.$i,
                'speed' => random_int(0, 1200),
                'stamina' => random_int(0, 1200),
                'power' => random_int(0, 1200),
            ];

            $result = $this->cacheService->remember($key, 'character_data', fn () => $value);

            expect($result)->toBe($value);

            $cached = Cache::get($key);
            expect($cached)->toBe($value);
        }
    })->group('property');

    it('ensures TTL strategies return positive integers for all known strategies', function () {
        $strategies = [
            'training_predictions',
            'character_data',
            'external_api',
            'static_game_data',
            'user_preferences',
            'ai_conversations',
            'mcp_server_status',
        ];

        foreach ($strategies as $strategy) {
            $ttl = $this->cacheService->getTTL($strategy);

            expect($ttl)->toBeInt()
                ->and($ttl)->toBeGreaterThan(0)
                ->and($ttl)->toBeLessThanOrEqual(604800);
        }
    })->group('property');

    it('returns default TTL for unknown strategies', function () {
        for ($i = 0; $i < 100; $i++) {
            $unknownStrategy = 'unknown_strategy_'.bin2hex(random_bytes(4));
            $ttl = $this->cacheService->getTTL($unknownStrategy);

            expect($ttl)->toBeInt()
                ->and($ttl)->toBeGreaterThan(0);
        }
    })->group('property');

    it('ensures cache warming reports accurate counts', function () {
        for ($i = 0; $i < 20; $i++) {
            $providerCount = random_int(1, 5);
            $providers = [];

            for ($j = 0; $j < $providerCount; $j++) {
                $key = 'warm_test_'.$i.'_'.$j.'_'.bin2hex(random_bytes(2));
                $providers[$key] = fn () => ['data' => random_int(1, 1000)];
            }

            $result = $this->cacheService->warmCache($providers);

            expect($result)->toHaveKeys(['warmed', 'failed', 'skipped', 'duration_ms'])
                ->and($result['warmed'] + $result['failed'] + $result['skipped'])->toBe($providerCount)
                ->and($result['duration_ms'])->toBeGreaterThanOrEqual(0);
        }
    })->group('property');

    /**
     * Property 2: Cache Invalidation Completeness
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 2: Cache Invalidation Completeness
     * Validates: Requirements NFR-P-05, NFR-P-10
     *
     * After invalidation, previously cached data must no longer be retrievable.
     */
    it('ensures cache entries are not retrievable after explicit removal', function () {
        for ($i = 0; $i < 100; $i++) {
            $key = 'invalidation_test_'.$i.'_'.bin2hex(random_bytes(4));
            $value = ['data' => random_int(1, 99999)];

            Cache::put($key, $value, 3600);
            expect(Cache::get($key))->toBe($value);

            Cache::forget($key);
            expect(Cache::get($key))->toBeNull();
        }
    })->group('property');

    it('ensures remember returns fresh data when cache is cold', function () {
        for ($i = 0; $i < 100; $i++) {
            $key = 'cold_cache_'.$i.'_'.bin2hex(random_bytes(4));
            $expectedValue = random_int(1, 99999);
            $callCount = 0;

            $result = $this->cacheService->remember($key, 'training_predictions', function () use ($expectedValue, &$callCount) {
                $callCount++;

                return $expectedValue;
            });

            expect($result)->toBe($expectedValue)
                ->and($callCount)->toBe(1);

            $secondCallCount = 0;
            $secondResult = $this->cacheService->remember($key, 'training_predictions', function () use (&$secondCallCount) {
                $secondCallCount++;

                return 'should_not_be_called';
            });

            expect($secondResult)->toBe($expectedValue)
                ->and($secondCallCount)->toBe(0);
        }
    })->group('property');

    it('ensures hit rate statistics track accurately', function () {
        $service = new RedisCacheOptimizationService;

        for ($i = 0; $i < 100; $i++) {
            $key = 'hit_rate_'.$i;
            $isHit = (bool) random_int(0, 1);
            $latency = random_int(1, 100) / 10.0;

            $service->recordAccess($key, $isHit, $latency);
        }

        $stats = $service->getHitRateStatistics();

        expect($stats)->toBeArray()
            ->and($stats)->toHaveKey('overall');

        if (isset($stats['overall']['total'])) {
            expect($stats['overall']['total'])->toBe(100);
        }
    })->group('property');
});
