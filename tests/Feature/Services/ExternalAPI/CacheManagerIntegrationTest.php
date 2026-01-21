<?php

declare(strict_types=1);

use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Clear cache before each test
    Cache::flush();

    $this->cacheManager = app(CacheManagerService::class);
});

afterEach(function () {
    // Clean up after each test
    Cache::flush();
});

describe('CacheManagerService Integration', function () {
    it('integrates with external API service for caching', function () {
        $key = 'character_data:test_character';
        $apiData = [
            'id' => 1,
            'name' => 'Silence Suzuka',
            'speed' => 100,
            'stamina' => 90,
            'power' => 85,
        ];

        // Simulate API response being cached
        $this->cacheManager->put($key, $apiData);

        // Retrieve from cache
        $cached = $this->cacheManager->get($key);

        expect($cached)->toBeArray()
            ->and($cached['name'])->toBe('Silence Suzuka')
            ->and($cached['_cache'])->toBeArray()
            ->and($cached['_cache']['source'])->toBe('cache')
            ->and($cached['_cache']['is_stale'])->toBeFalse();
    });

    it('provides staleness information for offline mode', function () {
        $key = 'character_data:offline_test';
        $data = [
            'name' => 'Test Character',
            'speed' => 100,
        ];

        // Cache the data
        $this->cacheManager->put($key, $data);

        // Retrieve and check staleness
        $cached = $this->cacheManager->get($key);

        expect($cached['_cache'])->toHaveKey('cached_at')
            ->and($cached['_cache'])->toHaveKey('age_seconds')
            ->and($cached['_cache'])->toHaveKey('ttl_seconds')
            ->and($cached['_cache'])->toHaveKey('is_stale')
            ->and($cached['_cache'])->toHaveKey('staleness_percentage');
    });

    it('tracks cache performance metrics', function () {
        $this->cacheManager->resetStatistics();

        // Simulate API calls with cache hits and misses
        $this->cacheManager->put('character_data:char1', ['name' => 'Character 1']);
        $this->cacheManager->put('character_data:char2', ['name' => 'Character 2']);

        // Cache hits
        $this->cacheManager->get('character_data:char1');
        $this->cacheManager->get('character_data:char2');
        $this->cacheManager->get('character_data:char1');

        // Cache misses
        $this->cacheManager->get('character_data:non_existent');

        $stats = $this->cacheManager->getStatistics();

        expect($stats['hits'])->toBe(3)
            ->and($stats['misses'])->toBe(1)
            ->and($stats['total_requests'])->toBe(4)
            ->and($stats['hit_rate'])->toBe(75.0);
    });

    it('supports different TTL for different data types', function () {
        // Character data - 24 hours
        $this->cacheManager->put('character_data:test', ['name' => 'Test']);
        $charMetadata = $this->cacheManager->getCacheMetadata('character_data:test');
        expect($charMetadata['ttl'])->toBe(86400);

        // Support cards - 12 hours
        $this->cacheManager->put('support_cards:123', ['name' => 'Card']);
        $cardMetadata = $this->cacheManager->getCacheMetadata('support_cards:123');
        expect($cardMetadata['ttl'])->toBe(43200);

        // Meta rankings - 6 hours
        $this->cacheManager->put('meta_rankings:speed', ['rank' => 1]);
        $metaMetadata = $this->cacheManager->getCacheMetadata('meta_rankings:speed');
        expect($metaMetadata['ttl'])->toBe(21600);

        // Race data - 48 hours
        $this->cacheManager->put('race_data:tokyo_2400', ['distance' => 2400]);
        $raceMetadata = $this->cacheManager->getCacheMetadata('race_data:tokyo_2400');
        expect($raceMetadata['ttl'])->toBe(172800);
    });

    it('provides comprehensive cache information for monitoring', function () {
        // Add some cached data
        $this->cacheManager->put('character_data:test1', ['name' => 'Test 1']);
        $this->cacheManager->put('support_cards:test2', ['name' => 'Test 2']);

        $info = $this->cacheManager->getCacheInfo();

        expect($info)->toBeArray()
            ->and($info)->toHaveKey('statistics')
            ->and($info)->toHaveKey('redis')
            ->and($info)->toHaveKey('ttl_config')
            ->and($info)->toHaveKey('staleness_threshold')
            ->and($info['statistics'])->toBeArray()
            ->and($info['ttl_config'])->toBeArray();
    });

    it('handles cache invalidation correctly', function () {
        $key = 'character_data:test';
        $data = ['name' => 'Test Character'];

        // Cache the data
        $this->cacheManager->put($key, $data);
        expect($this->cacheManager->has($key))->toBeTrue();

        // Invalidate (delete) the cache
        $this->cacheManager->delete($key);
        expect($this->cacheManager->has($key))->toBeFalse();

        // Should return null after deletion
        $result = $this->cacheManager->get($key);
        expect($result)->toBeNull();
    });

    it('supports cache warming scenario', function () {
        // Simulate cache warming with multiple popular characters
        $popularCharacters = [
            'Silence Suzuka',
            'Tokai Teio',
            'Gold Ship',
            'Special Week',
            'Vodka',
        ];

        foreach ($popularCharacters as $character) {
            $key = "character_data:{$character}";
            $data = [
                'name' => $character,
                'speed' => rand(80, 100),
                'stamina' => rand(80, 100),
            ];

            $this->cacheManager->put($key, $data);
        }

        // Verify all characters are cached
        foreach ($popularCharacters as $character) {
            $key = "character_data:{$character}";
            expect($this->cacheManager->has($key))->toBeTrue();

            $cached = $this->cacheManager->get($key);
            expect($cached)->toBeArray()
                ->and($cached['name'])->toBe($character)
                ->and($cached['_cache']['source'])->toBe('cache');
        }
    });

    it('provides cache size information', function () {
        // Add some data
        $this->cacheManager->put('character_data:test1', ['name' => 'Test 1', 'data' => str_repeat('x', 1000)]);
        $this->cacheManager->put('character_data:test2', ['name' => 'Test 2', 'data' => str_repeat('y', 2000)]);

        $size = $this->cacheManager->getCacheSize();

        expect($size)->toBeArray()
            ->and($size)->toHaveKey('total_keys')
            ->and($size)->toHaveKey('estimated_size_bytes')
            ->and($size['total_keys'])->toBeInt()
            ->and($size['estimated_size_bytes'])->toBeInt();
    });

    it('handles concurrent cache operations', function () {
        $keys = [];

        // Simulate concurrent writes
        for ($i = 1; $i <= 10; $i++) {
            $key = "character_data:concurrent_test_{$i}";
            $keys[] = $key;
            $this->cacheManager->put($key, ['id' => $i, 'name' => "Character {$i}"]);
        }

        // Verify all writes succeeded
        foreach ($keys as $index => $key) {
            $cached = $this->cacheManager->get($key);
            expect($cached)->toBeArray()
                ->and($cached['id'])->toBe($index + 1);
        }
    });

    it('maintains data integrity with complex structures', function () {
        $key = 'character_data:complex_structure';
        $complexData = [
            'id' => 1,
            'name' => 'Silence Suzuka',
            'base_stats' => [
                'speed' => 100,
                'stamina' => 90,
                'power' => 85,
                'guts' => 80,
                'wit' => 95,
            ],
            'aptitudes' => [
                'turf' => 'A',
                'dirt' => 'G',
                'short' => 'G',
                'mile' => 'A',
                'medium' => 'A',
                'long' => 'S',
            ],
            'skills' => [
                ['id' => 1, 'name' => 'Speed Star', 'level' => 3],
                ['id' => 2, 'name' => 'Acceleration', 'level' => 5],
            ],
            'metadata' => [
                'rarity' => 3,
                'release_date' => '2021-02-24',
                'voice_actor' => 'Takahashi Minami',
            ],
        ];

        $this->cacheManager->put($key, $complexData);
        $cached = $this->cacheManager->get($key);

        // Verify structure integrity
        expect($cached['name'])->toBe('Silence Suzuka')
            ->and($cached['base_stats'])->toBeArray()
            ->and($cached['base_stats']['speed'])->toBe(100)
            ->and($cached['aptitudes'])->toBeArray()
            ->and($cached['aptitudes']['long'])->toBe('S')
            ->and($cached['skills'])->toBeArray()
            ->and($cached['skills'])->toHaveCount(2)
            ->and($cached['metadata'])->toBeArray()
            ->and($cached['metadata']['rarity'])->toBe(3);
    });
});

describe('CacheManagerService - Offline Functionality', function () {
    it('provides data age warnings for offline mode', function () {
        $key = 'character_data:offline_character';
        $data = ['name' => 'Offline Character'];

        $this->cacheManager->put($key, $data);
        $cached = $this->cacheManager->get($key);

        // Check that age information is available
        expect($cached['_cache']['age_seconds'])->toBeInt()
            ->and($cached['_cache']['age_seconds'])->toBeGreaterThanOrEqual(0);
    });

    it('calculates refresh recommendations based on staleness', function () {
        $key = 'character_data:stale_check';
        $data = ['name' => 'Test Character'];

        $this->cacheManager->put($key, $data);
        $cached = $this->cacheManager->get($key);

        // Fresh data should not be stale
        expect($cached['_cache']['is_stale'])->toBeFalse()
            ->and($cached['_cache']['staleness_percentage'])->toBeLessThan(80);
    });

    it('maintains cache during simulated API outage', function () {
        // Pre-populate cache
        $characters = ['Char1', 'Char2', 'Char3'];

        foreach ($characters as $char) {
            $key = "character_data:{$char}";
            $this->cacheManager->put($key, ['name' => $char]);
        }

        // Simulate API outage - cache should still serve data
        foreach ($characters as $char) {
            $key = "character_data:{$char}";
            $cached = $this->cacheManager->get($key);

            expect($cached)->toBeArray()
                ->and($cached['name'])->toBe($char)
                ->and($cached['_cache']['source'])->toBe('cache');
        }
    });
});
