<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CacheManagementService;
use App\Services\ExternalAPI\ExternalAPIFacade;
use Illuminate\Console\Command;

/**
 * Warm Cache Command
 *
 * Intelligently warms application caches with frequently accessed data
 * using MCP agents for predictive data fetching.
 *
 * Requirements: 14.5, 55.3, 56.4, Task 4.4.2
 */
class WarmCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm
                            {--type=all : Type of cache to warm (all, characters, support-cards, meta)}
                            {--force : Force refresh even if cached}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm application caches with frequently accessed data using MCP-enhanced predictive fetching';

    /**
     * Execute the console command.
     */
    public function handle(
        CacheManagementService $cacheManager,
        ExternalAPIFacade $apiFacade
    ): int {
        $type = $this->option('type');
        $force = $this->option('force');

        $this->info('Starting cache warming...');
        $this->newLine();

        $startTime = microtime(true);
        $results = [];

        // Determine which caches to warm
        $cacheTypes = $type === 'all'
            ? ['characters', 'support-cards', 'meta']
            : [$type];

        foreach ($cacheTypes as $cacheType) {
            $this->info("Warming {$cacheType} cache...");

            $result = match ($cacheType) {
                'characters' => $this->warmCharactersCache($apiFacade, $force),
                'support-cards' => $this->warmSupportCardsCache($apiFacade, $force),
                'meta' => $this->warmMetaCache($apiFacade, $force),
                default => ['success' => false, 'message' => 'Unknown cache type'],
            };

            $results[$cacheType] = $result;

            if ($result['success']) {
                $this->info("✓ {$cacheType} cache warmed successfully");
            } else {
                $message = $result['message'] ?? 'Unknown error';
                $this->error("✗ Failed to warm {$cacheType} cache: {$message}");
            }

            $this->newLine();
        }

        $duration = (microtime(true) - $startTime) * 1000;

        // Display summary
        $this->info('Cache Warming Summary:');
        $this->table(
            ['Cache Type', 'Status', 'Items'],
            collect($results)->map(function ($result, $type) {
                return [
                    $type,
                    $result['success'] ? '✓ Success' : '✗ Failed',
                    $result['count'] ?? 0,
                ];
            })->toArray()
        );

        $this->info('Total duration: '.round($duration, 2).'ms');

        // Display cache statistics
        $stats = $cacheManager->getHitRateStatistics();
        $this->newLine();
        $this->info('Cache Statistics:');
        $this->line("Hit Rate: {$stats['hit_rate']}%");
        $this->line("Total Hits: {$stats['total_hits']}");
        $this->line("Total Misses: {$stats['total_misses']}");
        $this->line("Avg Response Time: {$stats['avg_response_time_ms']}ms");

        return Command::SUCCESS;
    }

    /**
     * Warm characters cache
     *
     * @return array{success: bool, count: int, message?: string}
     */
    protected function warmCharactersCache(ExternalAPIFacade $apiFacade, bool $force): array
    {
        try {
            $result = $apiFacade->getCharacters($force);

            return [
                'success' => $result['success'],
                'count' => count($result['data'] ?? []),
                'message' => $result['error'] ?? '',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'count' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Warm support cards cache
     *
     * @return array{success: bool, count: int, message?: string}
     */
    protected function warmSupportCardsCache(ExternalAPIFacade $apiFacade, bool $force): array
    {
        try {
            $result = $apiFacade->getSupportCards($force);

            return [
                'success' => $result['success'],
                'count' => count($result['data'] ?? []),
                'message' => $result['error'] ?? '',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'count' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Warm meta data cache
     *
     * @return array{success: bool, count: int, message?: string}
     */
    protected function warmMetaCache(ExternalAPIFacade $apiFacade, bool $force): array
    {
        try {
            $result = $apiFacade->getMetaTierRankings($force);

            return [
                'success' => $result['success'],
                'count' => count($result['data'] ?? []),
                'message' => $result['error'] ?? '',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'count' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }
}
