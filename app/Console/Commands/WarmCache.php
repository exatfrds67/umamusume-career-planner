<?php

namespace App\Console\Commands;

use App\Services\RedisCacheOptimizationService;
use Illuminate\Console\Command;

class WarmCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm
                            {--force : Force cache warming even if cache is already warm}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm Redis cache with frequently accessed data';

    /**
     * Execute the console command.
     */
    public function handle(RedisCacheOptimizationService $cacheService): int
    {
        $this->info('Starting cache warming process...');

        try {
            // Test Redis connection first
            if (! $cacheService->testConnection()) {
                $this->error('Redis connection failed. Please check your Redis configuration.');

                return self::FAILURE;
            }

            $this->info('Redis connection successful.');

            // Warm cache with empty providers array
            // Note: This command uses the old RedisCacheOptimizationService
            // For actual cache warming, use WarmCacheCommand instead
            $result = $cacheService->warmCache([]);

            $warmed = isset($result['warmed']) && is_numeric($result['warmed']) ? (int) $result['warmed'] : 0;
            $failed = isset($result['failed']) && is_numeric($result['failed']) ? (int) $result['failed'] : 0;
            $skipped = isset($result['skipped']) && is_numeric($result['skipped']) ? (int) $result['skipped'] : 0;
            $this->info("Warmed: {$warmed}, Failed: {$failed}, Skipped: {$skipped}");

            // Get cache statistics
            $stats = $cacheService->getStatistics();

            $this->newLine();
            $this->info('Cache warming completed successfully!');
            $this->newLine();

            // Display statistics
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Used Memory', $stats['used_memory']],
                    ['Peak Memory', $stats['used_memory_peak']],
                    ['Total Keys', $stats['total_keys']],
                    ['Hit Rate', $stats['hit_rate']],
                    ['Fragmentation Ratio', $stats['fragmentation_ratio']],
                    ['Connected Clients', $stats['connected_clients']],
                    ['Uptime (days)', $stats['uptime_days']],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Cache warming failed: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }
}
