<?php

declare(strict_types=1);

use App\Events\GameVersionUpdated;
use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->cacheManager = app(CacheManagerService::class);
    Cache::flush();
});

describe('Cache Invalidation by Pattern', function () {
    it('invalidates cache entries matching pattern with Redis', function () {
        if (config('cache.default') !== 'redis') {
            $this->markTestSkipped('Pattern invalidation requires Redis cache driver');
        }

        // Arrange: Create test cache entries
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);
        $this->cacheManager->put('character_data:Tokai Teio', ['name' => 'Tokai Teio']);
        $this->cacheManager->put('support_cards:1', ['id' => 1]);

        // Act: Invalidate character data
        $result = $this->cacheManager->invalidateByPattern('character_data:*');

        // Assert
        expect($result['success'])->toBeTrue();
        expect($result['invalidated_count'])->toBe(2);
        expect($this->cacheManager->has('character_data:Silence Suzuka'))->toBeFalse();
        expect($this->cacheManager->has('character_data:Tokai Teio'))->toBeFalse();
        expect($this->cacheManager->has('support_cards:1'))->toBeTrue();
    });

    it('returns success with limited support for non-Redis drivers', function () {
        if (config('cache.default') === 'redis') {
            $this->markTestSkipped('This test is for non-Redis cache drivers');
        }

        $result = $this->cacheManager->invalidateByPattern('character_data:*');

        // For non-Redis drivers, the method returns success but with a warning
        expect($result['success'])->toBeTrue();
        expect($result['invalidated_count'])->toBe(0);
    });
});

describe('Cache Invalidation by Type', function () {
    it('invalidates all entries of specific data type with Redis', function () {
        if (config('cache.default') !== 'redis') {
            $this->markTestSkipped('Type invalidation requires Redis cache driver');
        }

        // Arrange
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);
        $this->cacheManager->put('character_data:Tokai Teio', ['name' => 'Tokai Teio']);
        $this->cacheManager->put('support_cards:1', ['id' => 1]);

        // Act
        $result = $this->cacheManager->invalidateByType('character_data');

        // Assert
        expect($result['success'])->toBeTrue();
        expect($result['invalidated_count'])->toBe(2);
        expect($this->cacheManager->has('support_cards:1'))->toBeTrue();
    });

    it('returns error for invalid data type', function () {
        $result = $this->cacheManager->invalidateByType('invalid_type');

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toContain('Invalid data type');
    });
});

describe('Cache Invalidation by Keys', function () {
    it('invalidates specific cache keys', function () {
        // Arrange
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);
        $this->cacheManager->put('character_data:Tokai Teio', ['name' => 'Tokai Teio']);
        $this->cacheManager->put('support_cards:1', ['id' => 1]);

        // Act
        $result = $this->cacheManager->invalidateKeys([
            'character_data:Silence Suzuka',
            'support_cards:1',
        ]);

        // Assert
        expect($result['success'])->toBeTrue();
        expect($result['invalidated_count'])->toBe(2);
        expect($result['failed_keys'])->toBeEmpty();
        expect($this->cacheManager->has('character_data:Silence Suzuka'))->toBeFalse();
        expect($this->cacheManager->has('support_cards:1'))->toBeFalse();
        expect($this->cacheManager->has('character_data:Tokai Teio'))->toBeTrue();
    });

    it('reports failed keys when some invalidations fail', function () {
        $result = $this->cacheManager->invalidateKeys([
            'nonexistent:key1',
            'nonexistent:key2',
        ]);

        expect($result['success'])->toBeFalse();
        expect($result['invalidated_count'])->toBe(0);
        expect($result['failed_keys'])->toHaveCount(2);
    });
});

describe('Game Version Tracking', function () {
    it('sets game version and invalidates cache on version change', function () {
        if (config('cache.default') !== 'redis') {
            $this->markTestSkipped('Version-based invalidation requires Redis cache driver');
        }

        // Arrange: Set initial version and cache data
        $this->cacheManager->setGameVersion('1.0.0');
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);
        $this->cacheManager->put('support_cards:1', ['id' => 1]);

        Event::fake();

        // Act: Update to new version
        $result = $this->cacheManager->setGameVersion('1.1.0');

        // Assert
        expect($result['success'])->toBeTrue();
        expect($result['version_changed'])->toBeTrue();
        expect($result['previous_version'])->toBe('1.0.0');
        expect($result['new_version'])->toBe('1.1.0');
        expect($result['invalidated_types'])->toBeArray();

        // Verify event was dispatched
        Event::assertDispatched(GameVersionUpdated::class, function ($event) {
            return $event->previousVersion === '1.0.0'
                && $event->newVersion === '1.1.0';
        });
    });

    it('does not invalidate cache when version unchanged', function () {
        // Arrange
        $this->cacheManager->setGameVersion('1.0.0');
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);

        Event::fake();

        // Act: Set same version
        $result = $this->cacheManager->setGameVersion('1.0.0');

        // Assert
        expect($result['success'])->toBeTrue();
        expect($result['version_changed'])->toBeFalse();
        expect($result['invalidated_types'])->toBeEmpty();
        expect($this->cacheManager->has('character_data:Silence Suzuka'))->toBeTrue();

        Event::assertNotDispatched(GameVersionUpdated::class);
    });

    it('retrieves current game version', function () {
        $this->cacheManager->setGameVersion('1.2.3');

        $version = $this->cacheManager->getGameVersion();

        expect($version)->toBe('1.2.3');
    });

    it('tracks version change history', function () {
        // Arrange: Make multiple version changes
        $this->cacheManager->setGameVersion('1.0.0');
        $this->cacheManager->setGameVersion('1.1.0');
        $this->cacheManager->setGameVersion('1.2.0');

        // Act
        $history = $this->cacheManager->getVersionHistory();

        // Assert
        expect($history)->toBeArray();
        expect($history)->toHaveCount(3); // All version sets are recorded
        expect($history[0]['new_version'])->toBe('1.0.0');
        expect($history[1]['new_version'])->toBe('1.1.0');
        expect($history[2]['new_version'])->toBe('1.2.0');
    });
});

describe('Cache Staleness Detection', function () {
    it('checks cache staleness', function () {
        // Act
        $result = $this->cacheManager->checkCacheStaleness();

        // Assert
        expect($result)->toHaveKey('needs_invalidation');
        expect($result)->toHaveKey('stale_types');
        expect($result)->toHaveKey('recommendations');
    });

    it('invalidates stale cache entries', function () {
        // Act
        $result = $this->cacheManager->invalidateStaleCache();

        // Assert
        expect($result['success'])->toBeTrue();
        expect($result)->toHaveKey('total_invalidated');
    });
});

describe('Cache Invalidation API Endpoints', function () {
    it('invalidates cache by pattern via API', function () {
        // Arrange
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);

        // Act
        $response = postJson('/api/external-cache/invalidate/pattern', [
            'pattern' => 'character_data:*',
        ]);

        // Assert - API returns success even for non-Redis drivers
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    });

    it('invalidates cache by type via API', function () {
        // Arrange
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);

        // Act
        $response = postJson('/api/external-cache/invalidate/type', [
            'data_type' => 'character_data',
        ]);

        // Assert - API returns success even for non-Redis drivers
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    });

    it('validates data type in API request', function () {
        $response = postJson('/api/external-cache/invalidate/type', [
            'data_type' => 'invalid_type',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['data_type']);
    });

    it('invalidates specific keys via API', function () {
        // Arrange
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);
        $this->cacheManager->put('support_cards:1', ['id' => 1]);

        // Act
        $response = postJson('/api/external-cache/invalidate/keys', [
            'keys' => ['character_data:Silence Suzuka', 'support_cards:1'],
        ]);

        // Assert
        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
        ]);
    });

    it('flushes all cache via API', function () {
        // Arrange
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);
        $this->cacheManager->put('support_cards:1', ['id' => 1]);

        // Act
        $response = postJson('/api/external-cache/invalidate/flush');

        // Assert
        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
        ]);
    });

    it('gets game version via API', function () {
        // Arrange
        $this->cacheManager->setGameVersion('1.2.3');

        // Act
        $response = getJson('/api/external-cache/invalidate/version');

        // Assert
        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
            'data' => [
                'current_version' => '1.2.3',
            ],
        ]);
    });

    it('sets game version via API and triggers invalidation', function () {
        // Arrange
        $this->cacheManager->setGameVersion('1.0.0');
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);

        Event::fake();

        // Act
        $response = postJson('/api/external-cache/invalidate/version', [
            'version' => '1.1.0',
        ]);

        // Assert
        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
            'data' => [
                'version_changed' => true,
                'new_version' => '1.1.0',
            ],
        ]);

        Event::assertDispatched(GameVersionUpdated::class);
    });

    it('checks cache staleness via API', function () {
        $response = getJson('/api/external-cache/invalidate/staleness');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'needs_invalidation',
                'stale_types',
                'recommendations',
            ],
        ]);
    });

    it('invalidates stale cache via API', function () {
        $response = postJson('/api/external-cache/invalidate/stale');

        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
        ]);
    });
});

describe('Cache Flush Capabilities', function () {
    it('flushes all external API cache', function () {
        // Arrange
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);
        $this->cacheManager->put('support_cards:1', ['id' => 1]);
        $this->cacheManager->put('race_data:sprint', ['type' => 'sprint']);

        // Act
        $result = $this->cacheManager->flush();

        // Assert
        expect($result)->toBeTrue();
    });

    it('resets statistics when flushing cache', function () {
        // Arrange: Generate some cache hits
        $this->cacheManager->put('character_data:Silence Suzuka', ['name' => 'Silence Suzuka']);
        $this->cacheManager->get('character_data:Silence Suzuka');

        // Act
        $this->cacheManager->flush();

        // Assert
        $stats = $this->cacheManager->getStatistics();
        expect($stats['hits'])->toBe(0);
        expect($stats['misses'])->toBe(0);
    });
});
