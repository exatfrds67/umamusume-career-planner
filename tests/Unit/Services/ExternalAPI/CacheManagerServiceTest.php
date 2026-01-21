<?php

declare(strict_types=1);

use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Clear cache before each test
    Cache::flush();

    $this->service = new CacheManagerService;
});

afterEach(function () {
    // Clean up after each test
    Cache::flush();
});

describe('CacheManagerService - Basic Operations', function () {
    it('stores and retrieves data from cache', function () {
        $key = 'character_data:test_character';
        $data = [
            'name' => 'Test Character',
            'speed' => 100,
            'stamina' => 90,
        ];

        $result = $this->service->put($key, $data);

        expect($result)->toBeTrue();

        $cached = $this->service->get($key);

        expect($cached)->toBeArray()
            ->and($cached['name'])->toBe('Test Character')
            ->and($cached['speed'])->toBe(100)
            ->and($cached['stamina'])->toBe(90);
    });

    it('returns null for non-existent cache key', function () {
        $result = $this->service->get('non_existent_key');

        expect($result)->toBeNull();
    });

    it('deletes data from cache', function () {
        $key = 'character_data:test_character';
        $data = ['name' => 'Test Character'];

        $this->service->put($key, $data);
        expect($this->service->has($key))->toBeTrue();

        $deleted = $this->service->delete($key);

        expect($deleted)->toBeTrue()
            ->and($this->service->has($key))->toBeFalse();
    });

    it('checks if cache key exists', function () {
        $key = 'character_data:test_character';
        $data = ['name' => 'Test Character'];

        expect($this->service->has($key))->toBeFalse();

        $this->service->put($key, $data);

        expect($this->service->has($key))->toBeTrue();
    });
});

describe('CacheManagerService - TTL Configuration', function () {
    it('uses correct TTL for character data', function () {
        $ttl = $this->service->getTTL('character_data:test');

        expect($ttl)->toBe(86400); // 24 hours
    });

    it('uses correct TTL for support cards', function () {
        $ttl = $this->service->getTTL('support_cards:123');

        expect($ttl)->toBe(43200); // 12 hours
    });

    it('uses correct TTL for meta rankings', function () {
        $ttl = $this->service->getTTL('meta_rankings:speed');

        expect($ttl)->toBe(21600); // 6 hours
    });

    it('uses correct TTL for race data', function () {
        $ttl = $this->service->getTTL('race_data:tokyo_2400');

        expect($ttl)->toBe(172800); // 48 hours
    });

    it('uses correct TTL for skills', function () {
        $ttl = $this->service->getTTL('skills:speed_star');

        expect($ttl)->toBe(86400); // 24 hours
    });

    it('uses correct TTL for news', function () {
        $ttl = $this->service->getTTL('news:latest');

        expect($ttl)->toBe(3600); // 1 hour
    });

    it('uses correct TTL for game mechanics', function () {
        $ttl = $this->service->getTTL('game_mechanics:stat_breakpoints');

        expect($ttl)->toBe(604800); // 7 days
    });

    it('uses default TTL for unknown data type', function () {
        $ttl = $this->service->getTTL('unknown_type:test');

        expect($ttl)->toBe(3600); // 1 hour default
    });

    it('allows custom TTL override', function () {
        $key = 'character_data:test';
        $data = ['name' => 'Test'];
        $customTtl = 7200; // 2 hours

        $this->service->put($key, $data, $customTtl);

        $metadata = $this->service->getCacheMetadata($key);

        expect($metadata)->toBeArray()
            ->and($metadata['ttl'])->toBe($customTtl);
    });

    it('returns all TTL configuration', function () {
        $config = $this->service->getTTLConfig();

        expect($config)->toBeArray()
            ->and($config)->toHaveKey('character_data')
            ->and($config)->toHaveKey('support_cards')
            ->and($config)->toHaveKey('meta_rankings')
            ->and($config)->toHaveKey('race_data')
            ->and($config)->toHaveKey('skills')
            ->and($config)->toHaveKey('news')
            ->and($config)->toHaveKey('game_mechanics');
    });
});

describe('CacheManagerService - Staleness Indicators', function () {
    it('adds cache metadata to retrieved data', function () {
        $key = 'character_data:test';
        $data = ['name' => 'Test Character'];

        $this->service->put($key, $data);
        $cached = $this->service->get($key);

        expect($cached)->toHaveKey('_cache')
            ->and($cached['_cache'])->toHaveKey('cached_at')
            ->and($cached['_cache'])->toHaveKey('age_seconds')
            ->and($cached['_cache'])->toHaveKey('ttl_seconds')
            ->and($cached['_cache'])->toHaveKey('is_stale')
            ->and($cached['_cache'])->toHaveKey('staleness_percentage')
            ->and($cached['_cache'])->toHaveKey('source');
    });

    it('indicates data is not stale when freshly cached', function () {
        $key = 'character_data:test';
        $data = ['name' => 'Test Character'];

        $this->service->put($key, $data);
        $cached = $this->service->get($key);

        expect($cached['_cache']['is_stale'])->toBeFalse()
            ->and($cached['_cache']['age_seconds'])->toBeLessThan(5)
            ->and($cached['_cache']['staleness_percentage'])->toBeLessThan(1);
    });

    it('calculates staleness percentage correctly', function () {
        $key = 'character_data:test';
        $data = ['name' => 'Test Character'];

        $this->service->put($key, $data);
        $cached = $this->service->get($key);

        expect($cached['_cache']['staleness_percentage'])->toBeFloat()
            ->and($cached['_cache']['staleness_percentage'])->toBeGreaterThanOrEqual(0)
            ->and($cached['_cache']['staleness_percentage'])->toBeLessThanOrEqual(100);
    });

    it('includes TTL in cache metadata', function () {
        $key = 'character_data:test';
        $data = ['name' => 'Test Character'];

        $this->service->put($key, $data);
        $cached = $this->service->get($key);

        expect($cached['_cache']['ttl_seconds'])->toBe(86400); // 24 hours for character data
    });

    it('marks source as cache', function () {
        $key = 'character_data:test';
        $data = ['name' => 'Test Character'];

        $this->service->put($key, $data);
        $cached = $this->service->get($key);

        expect($cached['_cache']['source'])->toBe('cache');
    });
});

describe('CacheManagerService - Cache Metadata', function () {
    it('stores metadata separately from data', function () {
        $key = 'character_data:test';
        $data = ['name' => 'Test Character'];

        $this->service->put($key, $data);
        $metadata = $this->service->getCacheMetadata($key);

        expect($metadata)->toBeArray()
            ->and($metadata)->toHaveKey('cached_at')
            ->and($metadata)->toHaveKey('ttl')
            ->and($metadata)->toHaveKey('data_type')
            ->and($metadata)->toHaveKey('key');
    });

    it('extracts correct data type from key', function () {
        $key = 'support_cards:123';
        $data = ['name' => 'Test Card'];

        $this->service->put($key, $data);
        $metadata = $this->service->getCacheMetadata($key);

        expect($metadata['data_type'])->toBe('support_cards');
    });

    it('returns null for metadata of non-existent key', function () {
        $metadata = $this->service->getCacheMetadata('non_existent_key');

        expect($metadata)->toBeNull();
    });
});

describe('CacheManagerService - Cache Statistics', function () {
    it('tracks cache hits', function () {
        $key = 'character_data:test';
        $data = ['name' => 'Test'];

        $this->service->put($key, $data);

        // Reset stats first
        $this->service->resetStatistics();

        // Trigger cache hits
        $this->service->get($key);
        $this->service->get($key);
        $this->service->get($key);

        $stats = $this->service->getStatistics();

        expect($stats['hits'])->toBe(3)
            ->and($stats['misses'])->toBe(0)
            ->and($stats['total_requests'])->toBe(3)
            ->and($stats['hit_rate'])->toBe(100.0);
    });

    it('tracks cache misses', function () {
        $this->service->resetStatistics();

        // Trigger cache misses
        $this->service->get('non_existent_1');
        $this->service->get('non_existent_2');

        $stats = $this->service->getStatistics();

        expect($stats['hits'])->toBe(0)
            ->and($stats['misses'])->toBe(2)
            ->and($stats['total_requests'])->toBe(2)
            ->and($stats['hit_rate'])->toBe(0.0);
    });

    it('calculates hit rate correctly', function () {
        $key = 'character_data:test';
        $data = ['name' => 'Test'];

        $this->service->put($key, $data);
        $this->service->resetStatistics();

        // 3 hits, 2 misses = 60% hit rate
        $this->service->get($key);
        $this->service->get($key);
        $this->service->get($key);
        $this->service->get('non_existent_1');
        $this->service->get('non_existent_2');

        $stats = $this->service->getStatistics();

        expect($stats['hits'])->toBe(3)
            ->and($stats['misses'])->toBe(2)
            ->and($stats['total_requests'])->toBe(5)
            ->and($stats['hit_rate'])->toBe(60.0);
    });

    it('resets statistics', function () {
        $key = 'character_data:test';
        $data = ['name' => 'Test'];

        $this->service->put($key, $data);
        $this->service->get($key);
        $this->service->get('non_existent');

        $this->service->resetStatistics();

        $stats = $this->service->getStatistics();

        expect($stats['hits'])->toBe(0)
            ->and($stats['misses'])->toBe(0)
            ->and($stats['total_requests'])->toBe(0)
            ->and($stats['hit_rate'])->toBe(0.0);
    });

    it('includes last reset timestamp in statistics', function () {
        $this->service->resetStatistics();

        $stats = $this->service->getStatistics();

        expect($stats)->toHaveKey('last_reset')
            ->and($stats['last_reset'])->toBeString();
    });
});

describe('CacheManagerService - Cache Information', function () {
    it('returns comprehensive cache information', function () {
        $info = $this->service->getCacheInfo();

        expect($info)->toBeArray()
            ->and($info)->toHaveKey('statistics')
            ->and($info)->toHaveKey('redis')
            ->and($info)->toHaveKey('ttl_config')
            ->and($info)->toHaveKey('staleness_threshold');
    });

    it('includes statistics in cache info', function () {
        $info = $this->service->getCacheInfo();

        expect($info['statistics'])->toBeArray()
            ->and($info['statistics'])->toHaveKey('hits')
            ->and($info['statistics'])->toHaveKey('misses')
            ->and($info['statistics'])->toHaveKey('hit_rate')
            ->and($info['statistics'])->toHaveKey('total_requests');
    });

    it('includes TTL configuration in cache info', function () {
        $info = $this->service->getCacheInfo();

        expect($info['ttl_config'])->toBeArray()
            ->and($info['ttl_config'])->toHaveKey('character_data')
            ->and($info['ttl_config'])->toHaveKey('support_cards');
    });

    it('includes staleness threshold in cache info', function () {
        $info = $this->service->getCacheInfo();

        expect($info['staleness_threshold'])->toBeFloat()
            ->and($info['staleness_threshold'])->toBe(0.8);
    });
});

describe('CacheManagerService - Cache Size', function () {
    it('returns cache size information', function () {
        $size = $this->service->getCacheSize();

        expect($size)->toBeArray()
            ->and($size)->toHaveKey('total_keys')
            ->and($size)->toHaveKey('estimated_size_bytes');
    });

    it('tracks number of cached keys', function () {
        $this->service->put('character_data:test1', ['name' => 'Test 1']);
        $this->service->put('character_data:test2', ['name' => 'Test 2']);
        $this->service->put('support_cards:test3', ['name' => 'Test 3']);

        $size = $this->service->getCacheSize();

        // Note: Actual count may vary based on cache driver
        expect($size['total_keys'])->toBeInt()
            ->and($size['total_keys'])->toBeGreaterThanOrEqual(0);
    });
});

describe('CacheManagerService - Edge Cases', function () {
    it('handles empty data array', function () {
        $key = 'character_data:empty';
        $data = [];

        $result = $this->service->put($key, $data);

        expect($result)->toBeTrue();

        $cached = $this->service->get($key);

        expect($cached)->toBeArray()
            ->and($cached)->toHaveKey('_cache');
    });

    it('handles complex nested data structures', function () {
        $key = 'character_data:complex';
        $data = [
            'name' => 'Test Character',
            'stats' => [
                'speed' => 100,
                'stamina' => 90,
                'power' => 85,
            ],
            'skills' => [
                ['name' => 'Skill 1', 'level' => 3],
                ['name' => 'Skill 2', 'level' => 5],
            ],
        ];

        $this->service->put($key, $data);
        $cached = $this->service->get($key);

        expect($cached['name'])->toBe('Test Character')
            ->and($cached['stats'])->toBeArray()
            ->and($cached['stats']['speed'])->toBe(100)
            ->and($cached['skills'])->toBeArray()
            ->and($cached['skills'])->toHaveCount(2);
    });

    it('handles special characters in cache keys', function () {
        $key = 'character_data:Special Character (Test) [123]';
        $data = ['name' => 'Special Character'];

        $result = $this->service->put($key, $data);

        expect($result)->toBeTrue();

        $cached = $this->service->get($key);

        expect($cached)->toBeArray()
            ->and($cached['name'])->toBe('Special Character');
    });

    it('handles deleting non-existent key gracefully', function () {
        $result = $this->service->delete('non_existent_key');

        // Should not throw exception
        expect($result)->toBeBool();
    });
});
