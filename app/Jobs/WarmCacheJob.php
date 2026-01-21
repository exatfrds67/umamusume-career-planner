<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Warm Cache Job
 *
 * Background job for warming the cache with frequently accessed data.
 * This job preloads data into the cache to improve performance and
 * reduce API calls during normal operation.
 *
 * Features:
 * - Priority-based warming (high, medium, low, all)
 * - Automatic retry on failure
 * - Performance monitoring
 * - Detailed logging
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.1.2
 */
class WarmCacheJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300; // 5 minutes

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @param  string  $priority  Priority level: 'high', 'medium', 'low', or 'all'
     */
    public function __construct(
        public string $priority = 'all'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(CacheManagerService $cacheManager): void
    {
        Log::info('[WarmCacheJob] Starting cache warming job', [
            'priority' => $this->priority,
            'attempt' => $this->attempts(),
            'job_id' => $this->job?->getJobId(),
        ]);

        try {
            // Perform cache warming
            $result = $cacheManager->warmCache($this->priority);

            if ($result['success']) {
                Log::info('[WarmCacheJob] Cache warming completed successfully', [
                    'priority' => $this->priority,
                    'warmed_items' => $result['warmed_items'],
                    'duration_ms' => $result['duration_ms'],
                    'attempt' => $this->attempts(),
                ]);
            } else {
                Log::warning('[WarmCacheJob] Cache warming completed with failures', [
                    'priority' => $this->priority,
                    'warmed_items' => $result['warmed_items'],
                    'failed_items' => $result['failed_items'],
                    'duration_ms' => $result['duration_ms'],
                    'attempt' => $this->attempts(),
                ]);

                // If there were failures and we have retries left, fail the job
                if ($result['failed_items'] > 0 && $this->attempts() < $this->tries) {
                    throw new \RuntimeException(
                        "Cache warming failed for {$result['failed_items']} items"
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('[WarmCacheJob] Cache warming job failed', [
                'priority' => $this->priority,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[WarmCacheJob] Cache warming job failed permanently', [
            'priority' => $this->priority,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['cache-warming', "priority:{$this->priority}"];
    }
}
