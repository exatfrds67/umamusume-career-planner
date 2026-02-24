<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Race;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();

    $this->cacheManager = app(CacheManagerService::class);

    // Seed test data for warming methods
    Character::factory()->count(3)->create();
    SupportCard::factory()->count(5)->create();
    Skill::factory()->count(5)->create();
    Race::factory()->count(3)->create();
});

afterEach(function () {
    Cache::flush();
});

describe('Cache Warming', function () {
    it('can warm cache with all priorities', function () {
        $result = $this->cacheManager->warmCache('all');

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['success', 'warmed_items', 'failed_items', 'duration_ms', 'items'])
            ->and($result['success'])->toBeTrue()
            ->and($result['warmed_items'])->toBeGreaterThan(0)
            ->and($result['failed_items'])->toBe(0)
            ->and($result['duration_ms'])->toBeGreaterThan(0);
    });

    it('can warm cache with high priority only', function () {
        $result = $this->cacheManager->warmCache('high');

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['warmed_items'])->toBeGreaterThan(0)
            ->and($result['items'])->toHaveKeys(['top_characters', 'top_support_cards']);
    });

    it('can warm cache with medium priority', function () {
        $result = $this->cacheManager->warmCache('medium');

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['items'])->toHaveKeys([
                'top_characters',
                'top_support_cards',
                'race_definitions',
                'popular_skills',
            ]);
    });

    it('can warm cache with low priority', function () {
        $result = $this->cacheManager->warmCache('low');

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['items'])->toHaveKeys([
                'top_characters',
                'top_support_cards',
                'race_definitions',
                'popular_skills',
                'meta_rankings',
                'game_mechanics',
            ]);
    });

    it('stores warming statistics after warming', function () {
        $this->cacheManager->warmCache('high');

        $stats = $this->cacheManager->getWarmingStatistics();

        expect($stats)->toBeArray()
            ->and($stats)->toHaveKeys([
                'last_run',
                'warmed_items',
                'failed_items',
                'duration_ms',
                'success',
                'items',
            ])
            ->and($stats['success'])->toBeTrue();
    });

    it('creates real data for cache keys', function () {
        $this->cacheManager->warmCache('high');

        // Check that character data was cached using a real character from the DB
        $character = Character::first();
        $characterData = $this->cacheManager->get("character_data:{$character->name}");

        expect($characterData)->toBeArray()
            ->and($characterData)->toHaveKey('name')
            ->and($characterData['name'])->toBe($character->name);
    });

    it('skips already cached items', function () {
        // Pre-cache some data using a real character name
        $character = Character::first();
        $this->cacheManager->put("character_data:{$character->name}", [
            'name' => $character->name,
            'status' => 'cached',
        ]);

        $result = $this->cacheManager->warmCache('high');

        expect($result['success'])->toBeTrue()
            ->and($result['warmed_items'])->toBeGreaterThan(0);

        // Verify the pre-cached data wasn't overwritten
        $data = $this->cacheManager->get("character_data:{$character->name}");
        expect($data['status'])->toBe('cached');
    });

    it('tracks warming duration', function () {
        $result = $this->cacheManager->warmCache('high');

        expect($result['duration_ms'])->toBeGreaterThan(0)
            ->and($result['duration_ms'])->toBeLessThan(10000); // Should complete in under 10 seconds
    });

    it('returns detailed item status', function () {
        $result = $this->cacheManager->warmCache('all');

        expect($result['items'])->toBeArray()
            ->and($result['items'])->not->toBeEmpty();

        foreach ($result['items'] as $task => $status) {
            expect($status)->toBeIn(['success', 'failed', 'error']);
        }
    });
});

describe('Warming Statistics', function () {
    it('returns null when no warming has been performed', function () {
        $stats = $this->cacheManager->getWarmingStatistics();

        expect($stats)->toBeNull();
    });

    it('stores and retrieves warming statistics', function () {
        $this->cacheManager->warmCache('high');

        $stats = $this->cacheManager->getWarmingStatistics();

        expect($stats)->toBeArray()
            ->and($stats['last_run'])->toBeString()
            ->and($stats['warmed_items'])->toBeInt()
            ->and($stats['failed_items'])->toBeInt()
            ->and($stats['duration_ms'])->toBeFloat()
            ->and($stats['success'])->toBeBool();
    });

    it('updates statistics on subsequent warming runs', function () {
        $this->cacheManager->warmCache('high');
        $firstStats = $this->cacheManager->getWarmingStatistics();

        sleep(1); // Ensure different timestamp

        $this->cacheManager->warmCache('medium');
        $secondStats = $this->cacheManager->getWarmingStatistics();

        expect($secondStats['last_run'])->not->toBe($firstStats['last_run'])
            ->and($secondStats['warmed_items'])->toBeGreaterThanOrEqual($firstStats['warmed_items']);
    });
});

describe('Priority-Based Warming', function () {
    it('warms fewer items with high priority than all', function () {
        $highResult = $this->cacheManager->warmCache('high');
        Cache::flush();

        $allResult = $this->cacheManager->warmCache('all');

        expect($allResult['warmed_items'])->toBeGreaterThan($highResult['warmed_items']);
    });

    it('includes high priority items in medium priority warming', function () {
        $result = $this->cacheManager->warmCache('medium');

        expect($result['items'])->toHaveKeys(['top_characters', 'top_support_cards']);
    });

    it('includes all items in low priority warming', function () {
        $result = $this->cacheManager->warmCache('low');

        expect($result['items'])->toHaveKeys([
            'top_characters',
            'top_support_cards',
            'race_definitions',
            'popular_skills',
            'meta_rankings',
            'game_mechanics',
        ]);
    });
});

describe('Cache Integration', function () {
    it('integrates with existing cache operations', function () {
        // Warm cache
        $this->cacheManager->warmCache('high');

        // Verify we can retrieve cached data
        $character = Character::first();
        $data = $this->cacheManager->get("character_data:{$character->name}");

        expect($data)->toBeArray()
            ->and($data)->toHaveKey('_cache')
            ->and($data['_cache'])->toHaveKeys([
                'cached_at',
                'age_seconds',
                'ttl_seconds',
                'is_stale',
                'staleness_percentage',
                'source',
            ]);
    });

    it('respects TTL configuration for warmed data', function () {
        $this->cacheManager->warmCache('high');

        $character = Character::first();
        $metadata = $this->cacheManager->getCacheMetadata("character_data:{$character->name}");

        expect($metadata)->toBeArray()
            ->and($metadata['ttl'])->toBe(86400); // 24 hours for character_data
    });
});
