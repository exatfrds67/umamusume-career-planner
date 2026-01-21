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
        $priority = $this->option('priority');
        $async = $this->option('async');
        $showStats = $this->option('stats');

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
        if ($result['success']) {
            $this->info('✓ Cache warming completed successfully');
        } else {
            $this->warn('⚠ Cache warming completed with some failures');
        }

        $this->newLine();

        // Display summary
        $this->info('Summary:');
        $this->line("  Warmed Items: {$result['warmed_items']}");
        $this->line("  Failed Items: {$result['failed_items']}");
        $this->line('  Duration: '.round($result['duration_ms'], 2).'ms');
        $this->newLine();

        // Display detailed results
        if (! empty($result['items'])) {
            $this->info('Detailed Results:');

            $tableData = [];
            foreach ($result['items'] as $task => $status) {
                $statusIcon = match ($status) {
                    'success' => '✓',
                    'failed' => '✗',
                    'error' => '⚠',
                    default => '?',
                };

                $tableData[] = [
                    $task,
                    "{$statusIcon} {$status}",
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
        $this->line("  Hit Rate: {$stats['hit_rate']}%");
        $this->line("  Total Hits: {$stats['hits']}");
        $this->line("  Total Misses: {$stats['misses']}");
        $this->line("  Total Requests: {$stats['total_requests']}");
        $this->newLine();

        // Get warming statistics
        $warmingStats = $cacheManager->getWarmingStatistics();
        if ($warmingStats) {
            $this->info('Last Warming Run:');
            $this->line("  Time: {$warmingStats['last_run']}");
            $this->line("  Warmed Items: {$warmingStats['warmed_items']}");
            $this->line("  Failed Items: {$warmingStats['failed_items']}");
            $this->line('  Duration: '.round($warmingStats['duration_ms'], 2).'ms');
            $this->newLine();
        }

        // Get cache size
        $sizeInfo = $cacheManager->getCacheSize();
        $this->info('Cache Size:');
        $this->line("  Total Keys: {$sizeInfo['total_keys']}");
        $this->line('  Estimated Size: '.number_format($sizeInfo['estimated_size_bytes']).' bytes');
    }
}
