<?php

declare(strict_types=1);

use App\Jobs\SyncExternalDataJob;
use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    // Clear cache before each test
    Cache::flush();

    // Fake queue for testing
    Queue::fake();
});

describe('SyncExternalDataJob', function () {
    it('can be instantiated with valid data type', function () {
        $job = new SyncExternalDataJob('character', ['Silence Suzuka']);

        expect($job->dataType)->toBe('character')
            ->and($job->identifiers)->toBe(['Silence Suzuka'])
            ->and($job->syncId)->not->toBeNull();
    });

    it('throws exception for invalid data type', function () {
        new SyncExternalDataJob('invalid_type', ['test']);
    })->throws(\InvalidArgumentException::class, 'Invalid data type: invalid_type');

    it('generates unique sync ID when not provided', function () {
        $job1 = new SyncExternalDataJob('character', ['Test 1']);
        $job2 = new SyncExternalDataJob('character', ['Test 2']);

        expect($job1->syncId)->not->toBe($job2->syncId);
    });

    it('uses provided sync ID when given', function () {
        $syncId = 'custom_sync_id_123';
        $job = new SyncExternalDataJob('character', ['Test'], $syncId);

        expect($job->syncId)->toBe($syncId);
    });

    it('assigns correct queue based on data type priority', function () {
        // High priority
        $characterJob = new SyncExternalDataJob('character', ['Test']);
        expect($characterJob->queue)->toBe('high');

        $supportCardJob = new SyncExternalDataJob('support_card', ['Test']);
        expect($supportCardJob->queue)->toBe('high');

        // Medium priority (default queue)
        $raceJob = new SyncExternalDataJob('race', ['Test']);
        expect($raceJob->queue)->toBe('default');

        $skillJob = new SyncExternalDataJob('skill', ['Test']);
        expect($skillJob->queue)->toBe('default');

        // Low priority
        $newsJob = new SyncExternalDataJob('news', ['Test']);
        expect($newsJob->queue)->toBe('low');

        $metaJob = new SyncExternalDataJob('meta_ranking', ['Test']);
        expect($metaJob->queue)->toBe('low');
    });

    it('initializes progress tracking when job starts', function () {
        $syncId = 'test_sync_123';
        $identifiers = ['Item 1', 'Item 2', 'Item 3'];

        $job = new SyncExternalDataJob('character', $identifiers, $syncId);

        // Mock the handle method to just initialize progress
        $cacheManager = Mockery::mock(CacheManagerService::class);

        // Manually call initializeProgress through reflection
        $reflection = new ReflectionClass($job);
        $method = $reflection->getMethod('initializeProgress');
        $method->setAccessible(true);
        $method->invoke($job);

        // Check progress was initialized
        $progress = Cache::get("sync_progress:{$syncId}");

        expect($progress)->not->toBeNull()
            ->and($progress['sync_id'])->toBe($syncId)
            ->and($progress['data_type'])->toBe('character')
            ->and($progress['total_items'])->toBe(3)
            ->and($progress['processed_items'])->toBe(0)
            ->and($progress['status'])->toBe('in_progress');
    });

    it('updates progress during synchronization', function () {
        $syncId = 'test_sync_456';
        $job = new SyncExternalDataJob('character', ['Item 1', 'Item 2'], $syncId);

        // Initialize progress
        $reflection = new ReflectionClass($job);
        $initMethod = $reflection->getMethod('initializeProgress');
        $initMethod->setAccessible(true);
        $initMethod->invoke($job);

        // Update progress
        $updateMethod = $reflection->getMethod('updateProgress');
        $updateMethod->setAccessible(true);
        $updateMethod->invoke($job, 1, 2);

        // Check progress was updated
        $progress = Cache::get("sync_progress:{$syncId}");

        expect($progress['processed_items'])->toBe(1)
            ->and($progress['progress_percentage'])->toBe(50.0);
    });

    it('completes progress tracking with results', function () {
        $syncId = 'test_sync_789';
        $job = new SyncExternalDataJob('character', ['Item 1'], $syncId);

        // Initialize progress
        $reflection = new ReflectionClass($job);
        $initMethod = $reflection->getMethod('initializeProgress');
        $initMethod->setAccessible(true);
        $initMethod->invoke($job);

        // Complete progress
        $completeMethod = $reflection->getMethod('completeProgress');
        $completeMethod->setAccessible(true);
        $completeMethod->invoke($job, 5, 2, 1);

        // Check progress was completed
        $progress = Cache::get("sync_progress:{$syncId}");

        expect($progress['success_count'])->toBe(5)
            ->and($progress['failure_count'])->toBe(2)
            ->and($progress['conflict_count'])->toBe(1)
            ->and($progress['status'])->toBe('completed_with_errors');
    });

    it('marks progress as completed when no failures', function () {
        $syncId = 'test_sync_success';
        $job = new SyncExternalDataJob('character', ['Item 1'], $syncId);

        // Initialize and complete progress
        $reflection = new ReflectionClass($job);
        $initMethod = $reflection->getMethod('initializeProgress');
        $initMethod->setAccessible(true);
        $initMethod->invoke($job);

        $completeMethod = $reflection->getMethod('completeProgress');
        $completeMethod->setAccessible(true);
        $completeMethod->invoke($job, 5, 0, 0);

        // Check status
        $progress = Cache::get("sync_progress:{$syncId}");

        expect($progress['status'])->toBe('completed');
    });

    it('can retrieve sync progress by sync ID', function () {
        $syncId = 'test_sync_retrieve';
        $job = new SyncExternalDataJob('character', ['Item 1'], $syncId);

        // Initialize progress
        $reflection = new ReflectionClass($job);
        $method = $reflection->getMethod('initializeProgress');
        $method->setAccessible(true);
        $method->invoke($job);

        // Retrieve progress using static method
        $progress = SyncExternalDataJob::getSyncProgress($syncId);

        expect($progress)->not->toBeNull()
            ->and($progress['sync_id'])->toBe($syncId);
    });

    it('returns null for non-existent sync ID', function () {
        $progress = SyncExternalDataJob::getSyncProgress('non_existent_id');

        expect($progress)->toBeNull();
    });

    it('builds correct cache key for identifier', function () {
        $job = new SyncExternalDataJob('character', ['Silence Suzuka']);

        $reflection = new ReflectionClass($job);
        $method = $reflection->getMethod('buildCacheKey');
        $method->setAccessible(true);

        $cacheKey = $method->invoke($job, 'Silence Suzuka');

        expect($cacheKey)->toBe('character:Silence Suzuka');
    });

    it('detects conflicts between cached and fresh data', function () {
        $job = new SyncExternalDataJob('character', ['Test']);

        $cachedData = ['name' => 'Test', 'speed' => 100];
        $freshData = ['name' => 'Test', 'speed' => 150];

        $reflection = new ReflectionClass($job);
        $method = $reflection->getMethod('hasConflict');
        $method->setAccessible(true);

        $hasConflict = $method->invoke($job, $cachedData, $freshData);

        expect($hasConflict)->toBeTrue();
    });

    it('does not detect conflict when data is identical', function () {
        $job = new SyncExternalDataJob('character', ['Test']);

        $cachedData = ['name' => 'Test', 'speed' => 100];
        $freshData = ['name' => 'Test', 'speed' => 100];

        $reflection = new ReflectionClass($job);
        $method = $reflection->getMethod('hasConflict');
        $method->setAccessible(true);

        $hasConflict = $method->invoke($job, $cachedData, $freshData);

        expect($hasConflict)->toBeFalse();
    });

    it('resolves conflict by preferring fresh data by default', function () {
        $job = new SyncExternalDataJob('character', ['Test']);

        $cachedData = ['name' => 'Test', 'speed' => 100];
        $freshData = ['name' => 'Test', 'speed' => 150];

        $reflection = new ReflectionClass($job);
        $method = $reflection->getMethod('resolveConflict');
        $method->setAccessible(true);

        $result = $method->invoke($job, $cachedData, $freshData, 'Test');

        expect($result['strategy'])->toBe('prefer_fresh')
            ->and($result['data']['speed'])->toBe(150);
    });

    it('preserves user modifications during conflict resolution', function () {
        $job = new SyncExternalDataJob('character', ['Test']);

        $cachedData = [
            'name' => 'Test',
            'speed' => 100,
            '_user_modified' => true,
            '_user_notes' => 'Custom notes',
        ];
        $freshData = ['name' => 'Test', 'speed' => 150];

        $reflection = new ReflectionClass($job);
        $method = $reflection->getMethod('resolveConflict');
        $method->setAccessible(true);

        $result = $method->invoke($job, $cachedData, $freshData, 'Test');

        expect($result['strategy'])->toBe('preserve_user_modifications')
            ->and($result['data']['_user_modified'])->toBeTrue()
            ->and($result['data']['_user_notes'])->toBe('Custom notes')
            ->and($result['data']['_original_data'])->toBe($freshData);
    });

    it('removes cache metadata for comparison', function () {
        $job = new SyncExternalDataJob('character', ['Test']);

        $data = [
            'name' => 'Test',
            'speed' => 100,
            '_cache' => ['cached_at' => '2024-01-01'],
            '_metadata' => ['source' => 'api'],
            '_source' => 'umapyoi',
        ];

        $reflection = new ReflectionClass($job);
        $method = $reflection->getMethod('removeCacheMetadata');
        $method->setAccessible(true);

        $cleanData = $method->invoke($job, $data);

        expect($cleanData)->toBe(['name' => 'Test', 'speed' => 100])
            ->and($cleanData)->not->toHaveKey('_cache')
            ->and($cleanData)->not->toHaveKey('_metadata')
            ->and($cleanData)->not->toHaveKey('_source');
    });

    it('has correct job tags', function () {
        $syncId = 'test_sync_tags';
        $job = new SyncExternalDataJob('character', ['Test'], $syncId);

        $tags = $job->tags();

        expect($tags)->toContain('data-sync')
            ->and($tags)->toContain('type:character')
            ->and($tags)->toContain("sync:{$syncId}");
    });

    it('has correct retry configuration', function () {
        $job = new SyncExternalDataJob('character', ['Test']);

        expect($job->tries)->toBe(3)
            ->and($job->timeout)->toBe(300)
            ->and($job->backoff)->toBe(60);
    });

    it('updates progress to failed status on job failure', function () {
        $syncId = 'test_sync_failed';
        $job = new SyncExternalDataJob('character', ['Test'], $syncId);

        // Initialize progress
        $reflection = new ReflectionClass($job);
        $method = $reflection->getMethod('initializeProgress');
        $method->setAccessible(true);
        $method->invoke($job);

        // Simulate job failure
        $exception = new \RuntimeException('Test failure');
        $job->failed($exception);

        // Check progress was updated to failed
        $progress = Cache::get("sync_progress:{$syncId}");

        expect($progress['status'])->toBe('failed')
            ->and($progress['error'])->toBe('Test failure');
    });

    it('supports all defined data types', function () {
        $dataTypes = [
            'character',
            'support_card',
            'race',
            'skill',
            'news',
            'meta_ranking',
            'game_mechanics',
        ];

        foreach ($dataTypes as $dataType) {
            $job = new SyncExternalDataJob($dataType, ['Test']);
            expect($job->dataType)->toBe($dataType);
        }
    });
});

describe('SyncExternalDataJob Integration', function () {
    it('can be dispatched to queue', function () {
        Queue::fake();

        SyncExternalDataJob::dispatch('character', ['Silence Suzuka']);

        Queue::assertPushed(SyncExternalDataJob::class, function ($job) {
            return $job->dataType === 'character'
                && $job->identifiers === ['Silence Suzuka'];
        });
    });

    it('can be dispatched with custom sync ID', function () {
        Queue::fake();

        $syncId = 'custom_sync_123';
        SyncExternalDataJob::dispatch('character', ['Test'], $syncId);

        Queue::assertPushed(SyncExternalDataJob::class, function ($job) use ($syncId) {
            return $job->syncId === $syncId;
        });
    });

    it('can be dispatched to specific queue', function () {
        Queue::fake();

        SyncExternalDataJob::dispatch('character', ['Test'])->onQueue('high');

        Queue::assertPushedOn('high', SyncExternalDataJob::class);
    });
});
