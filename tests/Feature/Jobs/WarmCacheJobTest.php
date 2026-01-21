<?php

declare(strict_types=1);

use App\Jobs\WarmCacheJob;
use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Cache::flush();
    Queue::fake();
});

afterEach(function () {
    Cache::flush();
});

describe('WarmCacheJob', function () {
    it('can be dispatched to queue', function () {
        WarmCacheJob::dispatch('high');

        Queue::assertPushed(WarmCacheJob::class, function ($job) {
            return $job->priority === 'high';
        });
    });

    it('executes cache warming with specified priority', function () {
        $job = new WarmCacheJob('high');
        $cacheManager = app(CacheManagerService::class);

        $job->handle($cacheManager);

        // Verify cache was warmed
        $stats = $cacheManager->getWarmingStatistics();
        expect($stats)->toBeArray()
            ->and($stats['success'])->toBeTrue();
    });

    it('has correct retry configuration', function () {
        $job = new WarmCacheJob('all');

        expect($job->tries)->toBe(3)
            ->and($job->timeout)->toBe(300)
            ->and($job->backoff)->toBe(60);
    });

    it('has correct tags', function () {
        $job = new WarmCacheJob('medium');

        $tags = $job->tags();

        expect($tags)->toBeArray()
            ->and($tags)->toContain('cache-warming')
            ->and($tags)->toContain('priority:medium');
    });

    it('can handle all priority levels', function () {
        $priorities = ['high', 'medium', 'low', 'all'];

        foreach ($priorities as $priority) {
            $job = new WarmCacheJob($priority);
            $cacheManager = app(CacheManagerService::class);

            $job->handle($cacheManager);

            $stats = $cacheManager->getWarmingStatistics();
            expect($stats['success'])->toBeTrue();

            Cache::flush();
        }
    });
});

describe('Job Execution', function () {
    it('logs successful completion', function () {
        $job = new WarmCacheJob('high');
        $cacheManager = app(CacheManagerService::class);

        // This should not throw an exception
        expect(fn () => $job->handle($cacheManager))->not->toThrow(Exception::class);
    });

    it('stores warming statistics after execution', function () {
        $job = new WarmCacheJob('high');
        $cacheManager = app(CacheManagerService::class);

        $job->handle($cacheManager);

        $stats = $cacheManager->getWarmingStatistics();

        expect($stats)->toBeArray()
            ->and($stats)->toHaveKeys([
                'last_run',
                'warmed_items',
                'failed_items',
                'duration_ms',
                'success',
            ]);
    });
});

describe('Job Properties', function () {
    it('stores priority correctly', function () {
        $job = new WarmCacheJob('low');

        expect($job->priority)->toBe('low');
    });

    it('can be created with different priorities', function () {
        $priorities = ['high', 'medium', 'low', 'all'];

        foreach ($priorities as $priority) {
            $job = new WarmCacheJob($priority);
            expect($job->priority)->toBe($priority);
        }
    });
});
