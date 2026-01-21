<?php

namespace App\Console\Commands;

use App\Services\RedisCacheOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RedisHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:health
                            {--detailed : Show detailed Redis information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Redis server health and display statistics';

    /**
     * Execute the console command.
     */
    public function handle(RedisCacheOptimizationService $cacheService): int
    {
        $this->info('Checking Redis health...');
        $this->newLine();

        try {
            // Test connection
            if (! $cacheService->testConnection()) {
                $this->error('❌ Redis connection failed!');
                $this->error('Please check:');
                $this->error('  1. Redis server is running in WSL');
                $this->error('  2. Redis is listening on 127.0.0.1:6379');
                $this->error('  3. phpredis extension is installed');

                return self::FAILURE;
            }

            $this->info('✅ Redis connection successful!');
            $this->newLine();

            // Get statistics
            $stats = $cacheService->getStatistics();

            // Display basic statistics
            $this->info('📊 Cache Statistics:');
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

            // Check for warnings
            $this->checkWarnings($stats);

            // Show detailed information if requested
            if ($this->option('detailed')) {
                $this->showDetailedInfo();
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Health check failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Check for warnings based on statistics
     */
    protected function checkWarnings(array $stats): void
    {
        $this->newLine();
        $this->info('⚠️  Health Warnings:');

        $warnings = [];

        // Check hit rate
        if (isset($stats['hit_rate']) && $stats['hit_rate'] !== 'N/A') {
            $hitRate = (float) str_replace('%', '', $stats['hit_rate']);
            if ($hitRate < 80) {
                $warnings[] = "Low cache hit rate ({$stats['hit_rate']}). Consider cache warming.";
            }
        }

        // Check fragmentation ratio
        if (isset($stats['fragmentation_ratio']) && $stats['fragmentation_ratio'] !== 'N/A') {
            $fragmentation = (float) $stats['fragmentation_ratio'];
            if ($fragmentation > 1.5) {
                $warnings[] = "High memory fragmentation ({$stats['fragmentation_ratio']}). Consider Redis restart.";
            }
        }

        if (empty($warnings)) {
            $this->info('  No warnings detected. Redis is healthy! ✅');
        } else {
            foreach ($warnings as $warning) {
                $this->warn('  • '.$warning);
            }
        }
    }

    /**
     * Show detailed Redis information
     */
    protected function showDetailedInfo(): void
    {
        $this->newLine();
        $this->info('📋 Detailed Information:');

        try {
            // Get info from all Redis connections
            $connections = ['default', 'cache', 'session'];

            foreach ($connections as $connection) {
                $this->newLine();
                $this->info("Connection: {$connection}");

                try {
                    $info = Redis::connection($connection)->info();

                    $this->table(
                        ['Key', 'Value'],
                        [
                            ['Redis Version', $info['redis_version'] ?? 'N/A'],
                            ['OS', $info['os'] ?? 'N/A'],
                            ['Process ID', $info['process_id'] ?? 'N/A'],
                            ['TCP Port', $info['tcp_port'] ?? 'N/A'],
                            ['Database', config("database.redis.{$connection}.database")],
                            ['Total Commands', $info['total_commands_processed'] ?? 'N/A'],
                            ['Keyspace Hits', $info['keyspace_hits'] ?? 'N/A'],
                            ['Keyspace Misses', $info['keyspace_misses'] ?? 'N/A'],
                        ]
                    );
                } catch (\Exception $e) {
                    $this->error("  Failed to get info for {$connection}: ".$e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->error('Failed to get detailed information: '.$e->getMessage());
        }
    }
}
