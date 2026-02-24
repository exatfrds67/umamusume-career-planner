<?php

declare(strict_types=1);

use App\Services\QueryOptimizationService;
use Illuminate\Support\Facades\Cache;

describe('Query Optimization Property Tests', function () {
    beforeEach(function () {
        $this->queryService = new QueryOptimizationService;
        $configPrefix = config('query-optimization.cache.prefix', 'query_cache:');
        $this->cachePrefix = is_string($configPrefix) ? $configPrefix : 'query_cache:';
    });

    /**
     * Property 3: Query Count Reduction
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 3: Query Count Reduction
     * Validates: Requirements NFR-P-09, NFR-P-10
     *
     * Cached queries must execute the callback only once for repeated calls
     * with the same cache key, reducing database query count.
     */
    it('ensures cached queries execute callback only once for repeated calls', function () {
        for ($i = 0; $i < 100; $i++) {
            $cacheKey = 'query_prop_test_'.$i.'_'.bin2hex(random_bytes(4));
            $callCount = 0;
            $expectedResult = ['id' => random_int(1, 10000), 'name' => 'test_'.$i];

            $firstResult = $this->queryService->cachedQuery($cacheKey, function () use ($expectedResult, &$callCount) {
                $callCount++;

                return $expectedResult;
            });

            expect($firstResult)->toBe($expectedResult)
                ->and($callCount)->toBe(1);

            $secondResult = $this->queryService->cachedQuery($cacheKey, function () use (&$callCount) {
                $callCount++;

                return ['should_not_execute' => true];
            });

            expect($secondResult)->toBe($expectedResult)
                ->and($callCount)->toBe(1);

            Cache::forget($this->cachePrefix.$cacheKey);
        }
    })->group('property');

    it('ensures invalidation causes next query to execute callback again', function () {

        for ($i = 0; $i < 100; $i++) {
            $cacheKey = 'query_invalidation_'.$i.'_'.bin2hex(random_bytes(4));
            $callCount = 0;

            $this->queryService->cachedQuery($cacheKey, function () use (&$callCount) {
                $callCount++;

                return ['first_call' => true];
            });

            expect($callCount)->toBe(1);

            Cache::forget($this->cachePrefix.$cacheKey);

            $this->queryService->cachedQuery($cacheKey, function () use (&$callCount) {
                $callCount++;

                return ['second_call' => true];
            });

            expect($callCount)->toBe(2);

            Cache::forget($this->cachePrefix.$cacheKey);
        }
    })->group('property');

    it('ensures query stats structure is always consistent', function () {
        for ($i = 0; $i < 20; $i++) {
            $service = new QueryOptimizationService;

            $cacheKey = 'stats_test_'.$i.'_'.bin2hex(random_bytes(4));
            $service->cachedQuery($cacheKey, fn () => ['data' => $i]);

            $stats = $service->getQueryStats();

            expect($stats)->toBeArray();

            $cacheStats = $service->getCacheStats();
            expect($cacheStats)->toBeArray();

            Cache::forget($this->cachePrefix.$cacheKey);
        }
    })->group('property');

    it('ensures performance metrics return valid structure', function () {
        for ($i = 0; $i < 20; $i++) {
            $service = new QueryOptimizationService;

            $metrics = $service->getPerformanceMetrics();

            expect($metrics)->toBeArray();
        }
    })->group('property');
});
