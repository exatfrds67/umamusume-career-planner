<?php

declare(strict_types=1);

use App\Jobs\SyncExternalDataJob;
use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Cache::flush();
    Queue::fake();

    $this->cacheManager = new CacheManagerService;
});

afterEach(function () {
    Cache::flush();
});

describe('Background Sync Integration', function () {
    describe('Sync Job Creation', function () {
        it('creates sync job with valid data type', function () {
            $job = new SyncExternalDataJob('character', ['char1', 'char2']);

            expect($job->dataType)->toBe('character')
                ->and($job->identifiers)->toHaveCount(2)
                ->and($job->syncId)->toBeString();
        });

        it('generates unique sync ID when not provided', function () {
            $job1 = new SyncExternalDataJob('character', ['char1']);
            $job2 = new SyncExternalDataJob('character', ['char1']);

            expect($job1->syncId)->not->toBe($job2->syncId);
        });

        it('uses provided sync ID', function () {
            $customSyncId = 'custom_sync_123';
            $job = new SyncExternalDataJob('character', ['char1'], $customSyncId);

            expect($job->syncId)->toBe($customSyncId);
        });

        it('throws exception for invalid data type', function () {
            expect(fn () => new SyncExternalDataJob('invalid_type', ['test']))
                ->toThrow(\InvalidArgumentException::class);
        });

        it('accepts all valid data types', function () {
            $validTypes = ['character', 'support_card', 'race', 'skill', 'news', 'meta_ranking', 'game_mechanics'];

            foreach ($validTypes as $type) {
                $job = new SyncExternalDataJob($type, ['test']);
                expect($job->dataType)->toBe($type);
            }
        });
    });

    describe('Sync Job Queuing', function () {
        it('dispatches sync job to queue', function () {
            SyncExternalDataJob::dispatch('character', ['char1', 'char2']);

            Queue::assertPushed(SyncExternalDataJob::class, function ($job) {
                return $job->dataType === 'character' &&
                    count($job->identifiers) === 2;
            });
        });

        it('assigns correct queue based on data type priority', function () {
            // High priority
            $highPriorityJob = new SyncExternalDataJob('character', ['char1']);
            expect($highPriorityJob->queue)->toBe('high');

            // Medium priority
            $mediumPriorityJob = new SyncExternalDataJob('race', ['race1']);
            expect($mediumPriorityJob->queue)->toBe('default');

            // Low priority
            $lowPriorityJob = new SyncExternalDataJob('news', ['news1']);
            expect($lowPriorityJob->queue)->toBe('low');
        });

        it('includes job tags for monitoring', function () {
            $job = new SyncExternalDataJob('character', ['char1'], 'test_sync_123');
            $tags = $job->tags();

            expect($tags)->toContain('data-sync')
                ->and($tags)->toContain('type:character')
                ->and($tags)->toContain('sync:test_sync_123');
        });
    });

    describe('Sync Progress Tracking', function () {
        it('retrieves sync progress by ID', function () {
            $syncId = 'progress_test_123';

            // Simulate progress data
            Cache::put("sync_progress:{$syncId}", [
                'sync_id' => $syncId,
                'data_type' => 'character',
                'total_items' => 10,
                'processed_items' => 5,
                'status' => 'in_progress',
            ], 3600);

            $progress = SyncExternalDataJob::getSyncProgress($syncId);

            expect($progress)->toBeArray()
                ->and($progress['sync_id'])->toBe($syncId)
                ->and($progress['total_items'])->toBe(10)
                ->and($progress['processed_items'])->toBe(5)
                ->and($progress['status'])->toBe('in_progress');
        });

        it('returns null for non-existent sync progress', function () {
            $progress = SyncExternalDataJob::getSyncProgress('non_existent_sync');

            expect($progress)->toBeNull();
        });
    });

    describe('Cache Integration with Sync', function () {
        it('syncs data to cache manager', function () {
            $key = 'character:sync_test';
            $data = [
                'name' => 'Synced Character',
                'speed' => 100,
            ];

            $this->cacheManager->put($key, $data);
            $cached = $this->cacheManager->get($key);

            expect($cached['name'])->toBe('Synced Character')
                ->and($cached['speed'])->toBe(100);
        });

        it('handles conflict detection in cached data', function () {
            $key = 'character:conflict_test';

            // Original data
            $originalData = ['name' => 'Original', 'version' => 1];
            $this->cacheManager->put($key, $originalData);

            // Updated data (simulating sync)
            $updatedData = ['name' => 'Updated', 'version' => 2];
            $this->cacheManager->put($key, $updatedData);

            $cached = $this->cacheManager->get($key);

            expect($cached['name'])->toBe('Updated')
                ->and($cached['version'])->toBe(2);
        });

        it('preserves cache metadata after sync', function () {
            $key = 'character:metadata_test';
            $data = ['name' => 'Test Character'];

            $this->cacheManager->put($key, $data);
            $cached = $this->cacheManager->get($key);

            expect($cached)->toHaveKey('_cache')
                ->and($cached['_cache'])->toHaveKey('cached_at')
                ->and($cached['_cache'])->toHaveKey('source');
        });
    });

    describe('Sync Job Configuration', function () {
        it('has correct retry configuration', function () {
            $job = new SyncExternalDataJob('character', ['char1']);

            expect($job->tries)->toBe(3)
                ->and($job->timeout)->toBe(300)
                ->and($job->backoff)->toBe(60);
        });
    });

    describe('Cache Invalidation Integration', function () {
        it('invalidates cache by pattern', function () {
            // Create multiple cache entries
            $this->cacheManager->put('character_data:char1', ['name' => 'Char 1']);
            $this->cacheManager->put('character_data:char2', ['name' => 'Char 2']);
            $this->cacheManager->put('support_cards:card1', ['name' => 'Card 1']);

            // Invalidate character data pattern
            $result = $this->cacheManager->invalidateByPattern('character_data:*');

            expect($result)->toHaveKey('success')
                ->and($result)->toHaveKey('invalidated_count');
        });

        it('invalidates cache by data type', function () {
            $this->cacheManager->put('character_data:test', ['name' => 'Test']);

            $result = $this->cacheManager->invalidateByType('character_data');

            expect($result['success'])->toBeTrue();
        });

        it('invalidates specific cache keys', function () {
            $this->cacheManager->put('character_data:key1', ['name' => 'Key 1']);
            $this->cacheManager->put('character_data:key2', ['name' => 'Key 2']);

            $result = $this->cacheManager->invalidateKeys([
                'character_data:key1',
                'character_data:key2',
            ]);

            expect($result['success'])->toBeTrue()
                ->and($result['invalidated_count'])->toBe(2);
        });
    });

    describe('Game Version Tracking', function () {
        it('sets and retrieves game version', function () {
            $result = $this->cacheManager->setGameVersion('1.0.0');

            expect($result['success'])->toBeTrue()
                ->and($result['new_version'])->toBe('1.0.0');

            $version = $this->cacheManager->getGameVersion();
            expect($version)->toBe('1.0.0');
        });

        it('detects version changes', function () {
            $this->cacheManager->setGameVersion('1.0.0');
            $result = $this->cacheManager->setGameVersion('1.1.0');

            expect($result['version_changed'])->toBeTrue()
                ->and($result['previous_version'])->toBe('1.0.0')
                ->and($result['new_version'])->toBe('1.1.0');
        });

        it('does not flag unchanged version', function () {
            $this->cacheManager->setGameVersion('1.0.0');
            $result = $this->cacheManager->setGameVersion('1.0.0');

            expect($result['version_changed'])->toBeFalse();
        });

        it('tracks version history', function () {
            $this->cacheManager->setGameVersion('1.0.0');
            $this->cacheManager->setGameVersion('1.1.0');
            $this->cacheManager->setGameVersion('1.2.0');

            $history = $this->cacheManager->getVersionHistory();

            expect($history)->toBeArray()
                ->and(count($history))->toBeGreaterThanOrEqual(2);
        });
    });
});
