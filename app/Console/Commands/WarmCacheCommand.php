<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\WarmCacheJob;
use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Warm Cache Command
 *
 * Intelligently warms application caches with frequently accessed data.
 * Supports priority-based warming and can run synchronously or dispatch
 * background jobs for asynchronous warming.
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.1.2
 */
class WarmCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm
                            {--priority=all : Priority level: high, medium, low, or all}
                            {--async : Run cache warming in background job}
                            {--stats : Show warming statistics after completion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm application caches with frequently accessed external API data';

    /**
     * Execute the console command.
     */
    public function handle(CacheManagerService $cacheManager): int
    {
        /** @var string $priority */
        $priority = $this->option('priority') ?? 'all';
        $async = (bool) $this->option('async');
        $showStats = (bool) $this->option('stats');

        // Validate priority option
        if (! in_array($priority, ['high', 'medium', 'low', 'all'])) {
            $this->error("Invalid priority level: {$priority}");
            $this->info('Valid options: high, medium, low, all');

            return Command::FAILURE;
        }

        $this->info('Starting cache warming...');
        $this->info("Priority: {$priority}");
        $this->info('Mode: '.($async ? 'Asynchronous (background job)' : 'Synchronous'));
        $this->newLine();

        if ($async) {
            // Dispatch background job
            WarmCacheJob::dispatch($priority);

            $this->info('✓ Cache warming job dispatched to queue');
            $this->info('The cache will be warmed in the background.');
            $this->newLine();

            Log::info('[WarmCacheCommand] Cache warming job dispatched', [
                'priority' => $priority,
                'mode' => 'async',
            ]);

            return Command::SUCCESS;
        }

        // Run synchronously
        $startTime = microtime(true);

        try {
            $result = $cacheManager->warmCache($priority);

            $duration = (microtime(true) - $startTime) * 1000;

            // Display results
            $this->displayResults($result);

            // Show statistics if requested
            if ($showStats) {
                $this->newLine();
                $this->displayStatistics($cacheManager);
            }

            Log::info('[WarmCacheCommand] Cache warming completed', [
                'priority' => $priority,
                'mode' => 'sync',
                'result' => $result,
            ]);

            return $result['success'] ? Command::SUCCESS : Command::FAILURE;
        } catch (\Exception $e) {
            $this->error('Cache warming failed: '.$e->getMessage());
            $this->newLine();

            Log::error('[WarmCacheCommand] Cache warming failed', [
                'priority' => $priority,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Display warming results
     *
     * @param  array<string, mixed>  $result
     */
    protected function displayResults(array $result): void
    {
        $success = isset($result['success']) && is_bool($result['success']) ? $result['success'] : false;
        $warmedItems = isset($result['warmed_items']) && is_int($result['warmed_items']) ? $result['warmed_items'] : 0;
        $failedItems = isset($result['failed_items']) && is_int($result['failed_items']) ? $result['failed_items'] : 0;
        $durationMs = isset($result['duration_ms']) && is_numeric($result['duration_ms']) ? (float) $result['duration_ms'] : 0.0;

        if ($success) {
            $this->info('✓ Cache warming completed successfully');
        } else {
            $this->warn('⚠ Cache warming completed with some failures');
        }

        $this->newLine();

        // Display summary
        $this->info('Summary:');
        $this->line("  Warmed Items: {$warmedItems}");
        $this->line("  Failed Items: {$failedItems}");
        $this->line('  Duration: '.round($durationMs, 2).'ms');
        $this->newLine();

        // Display detailed results
        $items = $result['items'] ?? [];
        if (! empty($items) && is_array($items)) {
            $this->info('Detailed Results:');

            $tableData = [];
            foreach ($items as $task => $status) {
                $statusStr = is_string($status) ? $status : 'unknown';
                $statusIcon = match ($statusStr) {
                    'success' => '✓',
                    'failed' => '✗',
                    'error' => '⚠',
                    default => '?',
                };

                $tableData[] = [
                    (string) $task,
                    "{$statusIcon} {$statusStr}",
                ];
            }

            $this->table(['Task', 'Status'], $tableData);
        }
    }

    /**
     * Display cache statistics
     */
    protected function displayStatistics(CacheManagerService $cacheManager): void
    {
        $this->info('Cache Statistics:');

        // Get general statistics
        $stats = $cacheManager->getStatistics();
        $hitRate = $stats['hit_rate'] ?? 0;
        $hits = $stats['hits'] ?? 0;
        $misses = $stats['misses'] ?? 0;
        $totalRequests = $stats['total_requests'] ?? 0;

        $this->line("  Hit Rate: {$hitRate}%");
        $this->line("  Total Hits: {$hits}");
        $this->line("  Total Misses: {$misses}");
        $this->line("  Total Requests: {$totalRequests}");
        $this->newLine();

        // Get warming statistics
        $warmingStats = $cacheManager->getWarmingStatistics();
        if (! empty($warmingStats)) {
            $lastRun = isset($warmingStats['last_run']) && is_string($warmingStats['last_run'])
                ? $warmingStats['last_run']
                : 'N/A';
            $warmedItems = isset($warmingStats['warmed_items']) && is_int($warmingStats['warmed_items'])
                ? $warmingStats['warmed_items']
                : 0;
            $failedItems = isset($warmingStats['failed_items']) && is_int($warmingStats['failed_items'])
                ? $warmingStats['failed_items']
                : 0;
            $durationMs = isset($warmingStats['duration_ms']) && is_numeric($warmingStats['duration_ms'])
                ? (float) $warmingStats['duration_ms']
                : 0.0;

            $this->info('Last Warming Run:');
            $this->line("  Time: {$lastRun}");
            $this->line("  Warmed Items: {$warmedItems}");
            $this->line("  Failed Items: {$failedItems}");
            $this->line('  Duration: '.round($durationMs, 2).'ms');
            $this->newLine();
        }

        // Get cache size
        $sizeInfo = $cacheManager->getCacheSize();
        $totalKeys = (int) ($sizeInfo['total_keys'] ?? 0);
        $estimatedSize = (int) ($sizeInfo['estimated_size_bytes'] ?? 0);

        $this->info('Cache Size:');
        $this->line("  Total Keys: {$totalKeys}");
        $this->line('  Estimated Size: '.number_format($estimatedSize).' bytes');
    }
}
