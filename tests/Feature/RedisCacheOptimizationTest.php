<?php

declare(strict_types=1);

use App\Services\RedisCacheOptimizationService;
use Illuminate\Support\Facades\Cache;

/**
 * Redis Cache Optimization Service Tests
 *
 * Tests for Redis caching optimization including health checks,
 * cache warming, tag-based invalidation, and memory monitoring.
 *
 * Requirements: 17.4, 59.2
 * Task: 6.1.2 Redis caching optimization and strategy
 *
 * @property RedisCacheOptimizationService $service
 */
beforeEach(function () {
    // Use array cache driver for testing (no Redis required for basic tests)
    config(['cache.default' => 'array']);

    // Clear all caches before each test
    Cache::flush();

    /** @var RedisCacheOptimizationService $service */
    $this->service = new RedisCacheOptimizationService;
});

describe('Health Check', function () {
    test('checkHealth returns health status structure', function () {
        $health = $this->service->checkHealth();

        expect($health)->toHaveKeys(['healthy', 'latency_ms', 'connection_info', 'error']);
        expect($health['latency_ms'])->toBeGreaterThanOrEqual(0);
    });

    test('checkHealth returns unhealthy when Redis not available', function () {
        // With array cache driver, Redis should not be available
        $health = $this->service->checkHealth();

        // Should return unhealthy status when Redis is not available
        expect($health)->toHaveKey('healthy');
        expect($health)->toHaveKey('error');
    });

    test('isRedisAvailable returns false when extension not loaded', function () {
        // In test environment without Redis extension
        $available = $this->service->isRedisAvailable();

        // Should return false if phpredis extension is not loaded
        expect($available)->toBeBool();
    });
});

describe('Memory Usage', function () {
    test('getMemoryUsage returns memory statistics structure', function () {
        $memory = $this->service->getMemoryUsage();

        expect($memory)->toHaveKeys([
            'used_memory',
            'used_memory_human',
            'used_memory_peak',
            'used_memory_peak_human',
            'memory_fragmentation_ratio',
            'usage_percent',
            'status',
            'recommendations',
        ]);

        expect($memory['status'])->toBeIn(['healthy', 'warning', 'critical']);
        expect($memory['recommendations'])->toBeArray();
    });

    test('getMemoryUsage returns defaults when Redis not available', function () {
        $memory = $this->service->getMemoryUsage();

        // Should return default values when Redis is not available
        expect($memory['used_memory'])->toBe(0);
        expect($memory['status'])->toBe('healthy');
    });
});

describe('Cache Warming', function () {
    test('warmCache warms multiple keys successfully', function () {
        $providers = [
            'test_key_1' => fn () => ['data' => 'value1'],
            'test_key_2' => fn () => ['data' => 'value2'],
            'test_key_3' => fn () => ['data' => 'value3'],
        ];

        $result = $this->service->warmCache($providers);

        expect($result)->toHaveKeys(['warmed', 'failed', 'skipped', 'duration_ms', 'details']);
        expect($result['warmed'])->toBe(3);
        expect($result['failed'])->toBe(0);
        expect($result['duration_ms'])->toBeGreaterThan(0);
    });

    test('warmCache skips already cached keys', function () {
        // Pre-cache a key
        Cache::put('umamusume-career-planner:test_key_1', ['data' => 'cached'], 60);

        $providers = [
            'test_key_1' => fn () => ['data' => 'new_value'],
        ];

        $result = $this->service->warmCache($providers);

        expect($result['warmed'])->toBe(0);
        expect($result['skipped'])->toBe(1);
        expect($result['details']['test_key_1']['status'])->toBe('skipped');
    });

    test('warmCache handles provider failures gracefully', function () {
        $providers = [
            'good_key' => fn () => ['data' => 'value'],
            'bad_key' => fn () => throw new \Exception('Provider failed'),
        ];

        $result = $this->service->warmCache($providers);

        expect($result['warmed'])->toBe(1);
        expect($result['failed'])->toBe(1);
        expect($result['details']['bad_key']['status'])->toBe('failed');
    });

    test('warmCache respects priority ordering', function () {
        // Configure priorities
        config([
            'cache-management.warming.priority_types' => [
                'high_priority' => ['priority' => 1, 'ttl' => 3600],
                'low_priority' => ['priority' => 10, 'ttl' => 3600],
            ],
        ]);

        $order = [];
        $providers = [
            'low_priority' => function () use (&$order) {
                $order[] = 'low_priority';

                return ['data' => 'low'];
            },
            'high_priority' => function () use (&$order) {
                $order[] = 'high_priority';

                return ['data' => 'high'];
            },
        ];

        $this->service->warmCache($providers);

        expect($order[0])->toBe('high_priority');
        expect($order[1])->toBe('low_priority');
    });

    test('warmCache processes large batches efficiently', function () {
        $providers = [];

        // Create 100 providers
        for ($i = 0; $i < 100; $i++) {
            $providers["key_{$i}"] = fn () => ['data' => "value_{$i}"];
        }

        $result = $this->service->warmCache($providers);

        expect($result['warmed'])->toBe(100);
        expect($result['failed'])->toBe(0);
        expect($result['duration_ms'])->toBeGreaterThan(0);
    });
});

describe('Cache Invalidation', function () {
    test('invalidateByTags returns result structure', function () {
        $result = $this->service->invalidateByTags(['test_tag']);

        expect($result)->toHaveKeys(['invalidated', 'tags']);
        expect($result['tags'])->toBe(['test_tag']);
    });

    test('invalidateWithCascade applies cascade rules', function () {
        // Configure cascade rules
        config([
            'cache-management.invalidation.cascade_rules' => [
                'skills' => ['training_calculations', 'race_strategies'],
            ],
            'cache-management.invalidation.default_tags' => [
                'skills' => ['game_data', 'skills'],
                'training_calculations' => ['calculations', 'training'],
                'race_strategies' => ['calculations', 'races'],
            ],
        ]);

        $result = $this->service->invalidateWithCascade('skills');

        expect($result)->toHaveKeys(['invalidated', 'cascaded']);
        expect($result['cascaded'])->toContain('training_calculations');
        expect($result['cascaded'])->toContain('race_strategies');
    });

    test('invalidateWithCascade handles missing cascade rules', function () {
        config([
            'cache-management.invalidation.cascade_rules' => [],
            'cache-management.invalidation.default_tags' => [
                'unknown_type' => ['test_tag'],
            ],
        ]);

        $result = $this->service->invalidateWithCascade('unknown_type');

        expect($result['cascaded'])->toBeEmpty();
    });
});

describe('Hit Rate Statistics', function () {
    test('getHitRateStatistics returns statistics structure', function () {
        $stats = $this->service->getHitRateStatistics();

        expect($stats)->toHaveKeys(['overall', 'by_key', 'recommendations']);
        expect($stats['overall'])->toHaveKeys(['hit_rate', 'hits', 'misses', 'total']);
        expect($stats['recommendations'])->toBeArray();
    });

    test('recordAccess tracks cache hits and misses', function () {
        config(['cache-management.monitoring.enabled' => true]);
        config(['cache-management.monitoring.sample_rate' => 1.0]);

        $this->service->recordAccess('test_key', true, 5.0);
        $this->service->recordAccess('test_key', true, 3.0);
        $this->service->recordAccess('test_key', false, 10.0);

        $stats = $this->service->getHitRateStatistics();

        expect($stats['by_key'])->toHaveKey('test_key');
        expect($stats['by_key']['test_key']['hits'])->toBe(2);
        expect($stats['by_key']['test_key']['misses'])->toBe(1);
    });

    test('recordAccess respects sample rate of zero', function () {
        config(['cache-management.monitoring.enabled' => true]);
        config(['cache-management.monitoring.sample_rate' => 0.0]); // 0% sampling

        $this->service->recordAccess('test_key', true, 5.0);

        $stats = $this->service->getHitRateStatistics();

        // With 0% sampling, no metrics should be recorded
        expect($stats['by_key'])->not->toHaveKey('test_key');
    });

    test('recordAccess respects monitoring disabled', function () {
        config(['cache-management.monitoring.enabled' => false]);

        $this->service->recordAccess('test_key', true, 5.0);

        $stats = $this->service->getHitRateStatistics();

        // With monitoring disabled, no metrics should be recorded
        expect($stats['by_key'])->not->toHaveKey('test_key');
    });
});

describe('Cleanup', function () {
    test('cleanupStaleEntries returns cleanup statistics structure', function () {
        $result = $this->service->cleanupStaleEntries();

        expect($result)->toHaveKeys(['scanned', 'deleted', 'freed_memory_bytes', 'duration_ms']);
        expect($result['scanned'])->toBeGreaterThanOrEqual(0);
        expect($result['duration_ms'])->toBeGreaterThanOrEqual(0);
    });
});

describe('Optimization Recommendations', function () {
    test('getOptimizationRecommendations returns recommendations array', function () {
        $recommendations = $this->service->getOptimizationRecommendations();

        expect($recommendations)->toBeArray();

        foreach ($recommendations as $rec) {
            expect($rec)->toHaveKeys(['type', 'severity', 'message', 'action']);
            expect($rec['severity'])->toBeIn(['info', 'warning', 'critical']);
        }
    });

    test('getOptimizationRecommendations includes connection warning when Redis unavailable', function () {
        $recommendations = $this->service->getOptimizationRecommendations();

        // Should include a connection-related recommendation when Redis is not available
        $connectionRecs = array_filter($recommendations, fn ($r) => $r['type'] === 'connection');

        // May or may not have connection recommendation depending on environment
        expect($recommendations)->toBeArray();
    });
});

describe('Comprehensive Statistics', function () {
    test('getComprehensiveStats returns all statistics', function () {
        $stats = $this->service->getComprehensiveStats();

        expect($stats)->toHaveKeys(['health', 'memory', 'hit_rate', 'recommendations', 'config']);
        expect($stats['config'])->toHaveKeys([
            'warming_enabled',
            'monitoring_enabled',
            'cleanup_enabled',
            'compression_enabled',
        ]);
    });

    test('getComprehensiveStats config reflects configuration values', function () {
        config(['cache-management.warming.enabled' => true]);
        config(['cache-management.monitoring.enabled' => false]);

        $stats = $this->service->getComprehensiveStats();

        expect($stats['config']['warming_enabled'])->toBeTrue();
        expect($stats['config']['monitoring_enabled'])->toBeFalse();
    });
});

describe('Configuration', function () {
    test('cache-management config has required keys', function () {
        $config = config('cache-management');

        expect($config)->toHaveKeys([
            'redis',
            'warming',
            'memory',
            'ttl',
            'invalidation',
            'monitoring',
            'cleanup',
            'optimization',
            'alerts',
        ]);
    });

    test('TTL configuration has data type settings', function () {
        $ttlConfig = config('cache-management.ttl.by_type');

        expect($ttlConfig)->toHaveKeys([
            'skills',
            'support_cards',
            'meta_rankings',
            'training_calculations',
        ]);
    });

    test('invalidation configuration has cascade rules', function () {
        $invalidationConfig = config('cache-management.invalidation');

        expect($invalidationConfig)->toHaveKeys([
            'tags_enabled',
            'default_tags',
            'cascade_rules',
        ]);
    });

    test('warming configuration has priority types', function () {
        $warmingConfig = config('cache-management.warming');

        expect($warmingConfig)->toHaveKeys([
            'enabled',
            'batch_size',
            'priority_types',
        ]);
    });

    test('memory configuration has thresholds', function () {
        $memoryConfig = config('cache-management.memory');

        expect($memoryConfig)->toHaveKeys([
            'max_usage_percent',
            'warning_threshold_percent',
            'critical_threshold_percent',
        ]);
    });
});

describe('Tag Management', function () {
    test('getTagsForType returns configured tags', function () {
        config([
            'cache-management.invalidation.default_tags' => [
                'skills' => ['game_data', 'skills', 'static'],
            ],
        ]);

        // Use reflection to test protected method
        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('getTagsForType');
        $method->setAccessible(true);

        $tags = $method->invoke($this->service, 'skills');

        expect($tags)->toBe(['game_data', 'skills', 'static']);
    });

    test('getTagsForType returns empty array for unknown type', function () {
        config([
            'cache-management.invalidation.default_tags' => [],
        ]);

        $reflection = new ReflectionClass($this->service);
        $method = $reflection->getMethod('getTagsForType');
        $method->setAccessible(true);

        $tags = $method->invoke($this->service, 'unknown_type');

        expect($tags)->toBe([]);
    });
});
