<?php

declare(strict_types=1);

use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    $this->cacheManager = new CacheManagerService;
});

afterEach(function () {
    Cache::flush();
});

describe('Cache Performance', function () {
    describe('Cache Hit Rate Performance', function () {
        it('achieves high cache hit rate with repeated access', function () {
            $this->cacheManager->resetStatistics();

            // Pre-populate cache
            for ($i = 1; $i <= 20; $i++) {
                $this->cacheManager->put("hitrate:item{$i}", ['id' => $i]);
            }

            // Access items multiple times
            for ($round = 1; $round <= 3; $round++) {
                for ($i = 1; $i <= 20; $i++) {
                    $this->cacheManager->get("hitrate:item{$i}");
                }
            }

            $stats = $this->cacheManager->getStatistics();

            // Should have 60 hits (20 items × 3 rounds)
            expect($stats['hits'])->toBe(60)
                ->and($stats['hit_rate'])->toBe(100.0); // 100% hit rate
        });

        it('maintains acceptable hit rate with mixed access patterns', function () {
            $this->cacheManager->resetStatistics();

            // Pre-populate cache
            for ($i = 1; $i <= 10; $i++) {
                $this->cacheManager->put("mixed:item{$i}", ['id' => $i]);
            }

            // Mixed access: some hits, some misses
            for ($i = 1; $i <= 10; $i++) {
                $this->cacheManager->get("mixed:item{$i}"); // Hit
            }
            for ($i = 11; $i <= 15; $i++) {
                $this->cacheManager->get("mixed:item{$i}"); // Miss
            }

            $stats = $this->cacheManager->getStatistics();

            // 10 hits, 5 misses = 66.67% hit rate
            expect($stats['hits'])->toBe(10)
                ->and($stats['misses'])->toBe(5)
                ->and($stats['hit_rate'])->toBeGreaterThan(60); // Above 60% hit rate
        });

        it('tracks hit rate accurately over time', function () {
            $this->cacheManager->resetStatistics();

            // Phase 1: All hits
            for ($i = 1; $i <= 5; $i++) {
                $this->cacheManager->put("phase:item{$i}", ['id' => $i]);
                $this->cacheManager->get("phase:item{$i}");
            }

            $phase1Stats = $this->cacheManager->getStatistics();
            expect($phase1Stats['hit_rate'])->toBe(100.0);

            // Phase 2: Some misses
            for ($i = 6; $i <= 10; $i++) {
                $this->cacheManager->get("phase:item{$i}"); // Miss
            }

            $phase2Stats = $this->cacheManager->getStatistics();

            // 5 hits, 5 misses = 50% hit rate
            expect($phase2Stats['hit_rate'])->toBe(50.0);
        });
    });

    describe('Cache Size Performance', function () {
        it('handles large cache sizes efficiently', function () {
            // Create 100 cache entries
            for ($i = 1; $i <= 100; $i++) {
                $this->cacheManager->put("large:item{$i}", [
                    'id' => $i,
                    'data' => str_repeat('x', 500), // 500 bytes per entry
                ]);
            }

            $startTime = microtime(true);
            $size = $this->cacheManager->getCacheSize();
            $duration = (microtime(true) - $startTime) * 1000;

            // Note: Array cache driver returns 0 for size metrics
            expect($size)->toHaveKey('total_keys')
                ->and($size)->toHaveKey('estimated_size_bytes')
                ->and($size['total_keys'])->toBeInt()
                ->and($size['estimated_size_bytes'])->toBeInt()
                ->and($duration)->toBeLessThan(500); // Size calculation should be fast
        });

        it('retrieves cached keys list efficiently', function () {
            // Create multiple entries
            for ($i = 1; $i <= 30; $i++) {
                $this->cacheManager->put("keys:item{$i}", ['id' => $i]);
            }

            $startTime = microtime(true);
            $keys = $this->cacheManager->getCachedKeys();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($keys)->toBeArray()
                ->and($duration)->toBeLessThan(500);
        });

        it('handles cache growth without performance degradation', function () {
            $durations = [];

            // Measure performance at different cache sizes
            for ($batch = 1; $batch <= 3; $batch++) {
                $startTime = microtime(true);

                for ($i = 1; $i <= 20; $i++) {
                    $key = "growth:batch{$batch}:item{$i}";
                    $this->cacheManager->put($key, ['batch' => $batch, 'id' => $i]);
                }

                $durations[] = (microtime(true) - $startTime) * 1000;
            }

            // Performance should remain consistent
            $avgDuration = array_sum($durations) / count($durations);
            $maxDuration = max($durations);
            $minDuration = min($durations);

            // Check that performance is reasonably consistent
            // Allow for some variance but ensure no major degradation
            expect($maxDuration)->toBeLessThan($avgDuration * 2) // Max within 2x of average
                ->and($minDuration)->toBeGreaterThan(0)
                ->and($avgDuration)->toBeGreaterThan(0);
        });
    });

    describe('Cache Invalidation Performance', function () {
        it('invalidates cache by pattern efficiently', function () {
            // Create entries to invalidate
            for ($i = 1; $i <= 20; $i++) {
                $this->cacheManager->put("invalidate:item{$i}", ['id' => $i]);
            }

            $startTime = microtime(true);
            $result = $this->cacheManager->invalidateByPattern('invalidate:*');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result)->toHaveKey('success')
                ->and($duration)->toBeLessThan(1000); // Pattern invalidation in under 1 second
        });

        it('invalidates cache by type quickly', function () {
            $this->cacheManager->put('character_data:test1', ['name' => 'Test 1']);
            $this->cacheManager->put('character_data:test2', ['name' => 'Test 2']);

            $startTime = microtime(true);
            $result = $this->cacheManager->invalidateByType('character_data');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result['success'])->toBeTrue()
                ->and($duration)->toBeLessThan(500);
        });

        it('invalidates specific keys efficiently', function () {
            $keys = [];
            for ($i = 1; $i <= 15; $i++) {
                $key = "specific:item{$i}";
                $keys[] = $key;
                $this->cacheManager->put($key, ['id' => $i]);
            }

            $startTime = microtime(true);
            $result = $this->cacheManager->invalidateKeys($keys);
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result['success'])->toBeTrue()
                ->and($result['invalidated_count'])->toBe(15)
                ->and($duration)->toBeLessThan(1000); // 15 invalidations in under 1 second
        });

        it('handles cache flush efficiently', function () {
            // Create multiple entries
            for ($i = 1; $i <= 30; $i++) {
                $this->cacheManager->put("flush:item{$i}", ['id' => $i]);
            }

            $startTime = microtime(true);
            $result = $this->cacheManager->flush();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result)->toBeTrue()
                ->and($duration)->toBeLessThan(2000); // Flush in under 2 seconds
        });
    });

    describe('Cache Metadata Performance', function () {
        it('stores and retrieves metadata efficiently', function () {
            $key = 'metadata:performance_test';
            $data = ['name' => 'Test', 'value' => 123];

            $startTime = microtime(true);
            $this->cacheManager->put($key, $data);
            $metadata = $this->cacheManager->getCacheMetadata($key);
            $duration = (microtime(true) - $startTime) * 1000;

            expect($metadata)->toBeArray()
                ->and($metadata)->toHaveKey('cached_at')
                ->and($duration)->toBeLessThan(100);
        });

        it('calculates TTL efficiently for different data types', function () {
            $dataTypes = [
                'character_data:test',
                'support_cards:123',
                'meta_rankings:speed',
                'race_data:tokyo',
                'skills:skill1',
                'news:latest',
                'game_mechanics:breakpoints',
            ];

            $startTime = microtime(true);

            foreach ($dataTypes as $key) {
                $ttl = $this->cacheManager->getTTL($key);
                expect($ttl)->toBeInt()->and($ttl)->toBeGreaterThan(0);
            }

            $duration = (microtime(true) - $startTime) * 1000;

            expect($duration)->toBeLessThan(50); // All TTL calculations in under 50ms
        });

        it('retrieves TTL configuration quickly', function () {
            $startTime = microtime(true);
            $config = $this->cacheManager->getTTLConfig();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($config)->toBeArray()
                ->and($config)->toHaveKey('character_data')
                ->and($duration)->toBeLessThan(10); // Config retrieval should be instant
        });
    });

    describe('Cache Staleness Performance', function () {
        it('checks cache staleness efficiently', function () {
            // Create some cache entries
            for ($i = 1; $i <= 10; $i++) {
                $this->cacheManager->put("stale:item{$i}", ['id' => $i]);
            }

            $startTime = microtime(true);
            $result = $this->cacheManager->checkCacheStaleness();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result)->toHaveKey('needs_invalidation')
                ->and($result)->toHaveKey('stale_types')
                ->and($duration)->toBeLessThan(2000); // Staleness check in under 2 seconds
        });

        it('invalidates stale cache efficiently', function () {
            // Create entries
            for ($i = 1; $i <= 10; $i++) {
                $this->cacheManager->put("stale_inv:item{$i}", ['id' => $i]);
            }

            $startTime = microtime(true);
            $result = $this->cacheManager->invalidateStaleCache();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result)->toHaveKey('success')
                ->and($result)->toHaveKey('invalidated_types')
                ->and($duration)->toBeLessThan(3000); // Stale invalidation in under 3 seconds
        });
    });

    describe('Game Version Performance', function () {
        it('sets game version efficiently', function () {
            $startTime = microtime(true);
            $result = $this->cacheManager->setGameVersion('1.0.0');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result['success'])->toBeTrue()
                ->and($duration)->toBeLessThan(100);
        });

        it('detects version changes quickly', function () {
            $this->cacheManager->setGameVersion('1.0.0');

            $startTime = microtime(true);
            $result = $this->cacheManager->setGameVersion('1.1.0');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result['version_changed'])->toBeTrue()
                ->and($duration)->toBeLessThan(2000); // Version change with invalidation
        });

        it('retrieves version history efficiently', function () {
            $this->cacheManager->setGameVersion('1.0.0');
            $this->cacheManager->setGameVersion('1.1.0');
            $this->cacheManager->setGameVersion('1.2.0');

            $startTime = microtime(true);
            $history = $this->cacheManager->getVersionHistory();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($history)->toBeArray()
                ->and($duration)->toBeLessThan(50);
        });
    });

    describe('Warming Statistics Performance', function () {
        it('stores warming statistics efficiently', function () {
            $startTime = microtime(true);
            $result = $this->cacheManager->warmCache('high');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result)->toHaveKey('duration_ms')
                ->and($duration)->toBeLessThan(10000); // High priority warming in under 10 seconds
        });

        it('retrieves warming statistics quickly', function () {
            $this->cacheManager->warmCache('high');

            $startTime = microtime(true);
            $stats = $this->cacheManager->getWarmingStatistics();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($stats)->toBeArray()
                ->and($duration)->toBeLessThan(50);
        });
    });

    describe('Overall Cache Performance', function () {
        it('maintains consistent performance across operations', function () {
            $operations = [
                'put' => fn () => $this->cacheManager->put('perf:test', ['data' => 'value']),
                'get' => fn () => $this->cacheManager->get('perf:test'),
                'has' => fn () => $this->cacheManager->has('perf:test'),
                'delete' => fn () => $this->cacheManager->delete('perf:test'),
            ];

            foreach ($operations as $name => $operation) {
                $startTime = microtime(true);
                $operation();
                $duration = (microtime(true) - $startTime) * 1000;

                expect($duration)->toBeLessThan(100); // All basic operations under 100ms
            }
        });

        it('handles complex operations efficiently', function () {
            $complexOperations = [
                'cache_info' => fn () => $this->cacheManager->getCacheInfo(),
                'statistics' => fn () => $this->cacheManager->getStatistics(),
                'cache_size' => fn () => $this->cacheManager->getCacheSize(),
                'cached_keys' => fn () => $this->cacheManager->getCachedKeys(),
            ];

            foreach ($complexOperations as $name => $operation) {
                $startTime = microtime(true);
                $operation();
                $duration = (microtime(true) - $startTime) * 1000;

                expect($duration)->toBeLessThan(500); // Complex operations under 500ms
            }
        });
    });
});
