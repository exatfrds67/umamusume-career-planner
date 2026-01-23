<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\CacheManagementService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Background Sync Service with MCP Agent Integration
 *
 * Manages background data synchronization and reconciliation using
 * strands-agents MCP server for intelligent sync coordination.
 *
 * Requirements: 14.2, 55.3, 56.3, Task 4.4.3
 */
class BackgroundSyncService
{
    /**
     * Sync queue key
     */
    protected const SYNC_QUEUE_KEY = 'sync_queue:';

    /**
     * Sync status key
     */
    protected const SYNC_STATUS_KEY = 'sync_status:';

    /**
     * Sync history key
     */
    protected const SYNC_HISTORY_KEY = 'sync_history:';

    /**
     * Maximum sync history entries
     */
    protected const MAX_HISTORY_ENTRIES = 100;

    /**
     * Sync retry attempts
     */
    protected const MAX_RETRY_ATTEMPTS = 3;

    public function __construct(
        protected MCPClientService $mcpClient,
        protected CacheManagementService $cacheManager,
        protected ExternalAPIFacade $apiFacade,
        protected GracefulDegradationService $degradationService
    ) {}

    /**
     * Queue data for background synchronization
     *
     * @param  array<string, mixed>  $options
     */
    public function queueSync(string $dataType, array $options = []): void
    {
        $syncJob = [
            'id' => uniqid('sync_', true),
            'data_type' => $dataType,
            'options' => $options,
            'queued_at' => now()->toIso8601String(),
            'attempts' => 0,
            'status' => 'queued',
        ];

        // Add to sync queue
        $queueKey = self::SYNC_QUEUE_KEY.$dataType;
        Redis::rpush($queueKey, json_encode($syncJob));

        Log::info('[BackgroundSync] Sync job queued', [
            'job_id' => $syncJob['id'],
            'data_type' => $dataType,
            'options' => $options,
        ]);
    }

    /**
     * Process sync queue for a specific data type
     *
     * @return array{processed: int, succeeded: int, failed: int, skipped: int}
     */
    public function processSyncQueue(): array
        $queueKey = self::SYNC_QUEUE_KEY.$dataType;
        $processed = 0;
        $succeeded = 0;
        $failed = 0;
        $skipped = 0;

        Log::info('[BackgroundSync] Processing sync queue', [
            'data_type' => $dataType,
        ]);

        // Process all jobs in queue
        while ($jobJson = Redis::lpop($queueKey)) {
            $job = json_decode($jobJson, true);
            $processed = ($processed ?? 0) + 1;

            // Check if max retry attempts exceeded
            if ($job['attempts'] >= self::MAX_RETRY_ATTEMPTS) {
                $skipped = ($skipped ?? 0) + 1;
                $this->recordSyncHistory($job, 'skipped', 'Max retry attempts exceeded');

                continue;
            }

            // Attempt sync
            $result = $this->syncData($job['data_type'], $job['options']);

            if ($result['success']) {
                $succeeded = ($succeeded ?? 0) + 1;
                $this->recordSyncHistory($job, 'succeeded', $result['message']);
            } else {
                $failed = ($failed ?? 0) + 1;
                $job['attempts']++;

                // Re-queue if not max attempts
                if ($job['attempts'] < self::MAX_RETRY_ATTEMPTS) {
                    Redis::rpush($queueKey, json_encode($job));
                }

                $this->recordSyncHistory($job, 'failed', $result['message']);
            }
        }

        Log::info('[BackgroundSync] Sync queue processed', [
            'data_type' => $dataType,
            'processed' => $processed,
            'succeeded' => $succeeded,
            'failed' => $failed,
            'skipped' => $skipped,
        ]);

        return [
            'processed' => $processed,
            'succeeded' => $succeeded,
            'failed' => $failed,
            'skipped' => $skipped,
        ];
    }

    /**
     * Sync specific data type
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, message: string, data_count: int}
     */
    public function syncData(): array
        Log::info('[BackgroundSync] Starting data sync', [
            'data_type' => $dataType,
            'options' => $options,
        ]);

        try {
            $result = match ($dataType) {
                'characters' => $this->syncCharacters($options),
                'support_cards' => $this->syncSupportCards($options),
                'meta_rankings' => $this->syncMetaRankings($options),
                'skill_effectiveness' => $this->syncSkillEffectiveness($options),
                default => [
                    'success' => false,
                    'message' => 'Unknown data type',
                    'data_count' => 0,
                ],
            };

            if ($result['success']) {
                $this->updateSyncStatus($dataType, 'completed', $result['data_count']);
            } else {
                $this->updateSyncStatus($dataType, 'failed', 0);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('[BackgroundSync] Sync failed', [
                'data_type' => $dataType,
                'error' => $e->getMessage(),
            ]);

            $this->updateSyncStatus($dataType, 'error', 0);

            return [
                'success' => false,
                'message' => 'Sync error: '.$e->getMessage(),
                'data_count' => 0,
            ];
        }
    }

    /**
     * Sync characters data
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, message: string, data_count: int}
     */
    protected function syncCharacters(): array
        $result = $this->apiFacade->getCharacters(true);

        if (! $result['success']) {
            return [
                'success' => false,
                'message' => 'Failed to fetch characters: '.($result['error'] ?? 'Unknown error'),
                'data_count' => 0,
            ];
        }

        $dataCount = count($result['data']);

        // Store with timestamp for age tracking
        $this->degradationService->storeWithTimestamp(
            'umapyoi:characters',
            $result['data'],
            86400
        );

        return [
            'success' => true,
            'message' => sprintf('Successfully synced %d characters', $dataCount),
            'data_count' => $dataCount,
        ];
    }

    /**
     * Sync support cards data
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, message: string, data_count: int}
     */
    protected function syncSupportCards(): array
        $result = $this->apiFacade->getSupportCards(true);

        if (! $result['success']) {
            return [
                'success' => false,
                'message' => 'Failed to fetch support cards: '.($result['error'] ?? 'Unknown error'),
                'data_count' => 0,
            ];
        }

        $dataCount = count($result['data']);

        // Store with timestamp for age tracking
        $this->degradationService->storeWithTimestamp(
            'umapyoi:support_cards',
            $result['data'],
            86400
        );

        return [
            'success' => true,
            'message' => sprintf('Successfully synced %d support cards', $dataCount),
            'data_count' => $dataCount,
        ];
    }

    /**
     * Sync meta rankings data
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, message: string, data_count: int}
     */
    protected function syncMetaRankings(): array
        $result = $this->apiFacade->getMetaTierRankings(true);

        if (! $result['success']) {
            return [
                'success' => false,
                'message' => 'Failed to fetch meta rankings: '.($result['error'] ?? 'Unknown error'),
                'data_count' => 0,
            ];
        }

        $dataCount = count($result['data']);

        // Store with timestamp for age tracking
        $this->degradationService->storeWithTimestamp(
            'umamusumedb:meta:tier_rankings',
            $result['data'],
            43200
        );

        return [
            'success' => true,
            'message' => sprintf('Successfully synced %d meta rankings', $dataCount),
            'data_count' => $dataCount,
        ];
    }

    /**
     * Sync skill effectiveness data
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, message: string, data_count: int}
     */
    protected function syncSkillEffectiveness(): array
        $result = $this->apiFacade->getSkillEffectiveness(true);

        if (! $result['success']) {
            return [
                'success' => false,
                'message' => 'Failed to fetch skill effectiveness: '.($result['error'] ?? 'Unknown error'),
                'data_count' => 0,
            ];
        }

        $dataCount = count($result['data']);

        // Store with timestamp for age tracking
        $this->degradationService->storeWithTimestamp(
            'umamusumedb:skills:effectiveness',
            $result['data'],
            43200
        );

        return [
            'success' => true,
            'message' => sprintf('Successfully synced %d skill effectiveness entries', $dataCount),
            'data_count' => $dataCount,
        ];
    }

    /**
     * Update sync status
     */
    protected function updateSyncStatus(string $dataType, string $status, int $dataCount): void
    {
        $statusKey = self::SYNC_STATUS_KEY.$dataType;

        $statusData = [
            'data_type' => $dataType,
            'status' => $status,
            'data_count' => $dataCount,
            'last_sync' => now()->toIso8601String(),
        ];

        Cache::put($statusKey, $statusData, 86400);
    }

    /**
     * Get sync status for a data type
     *
     * @return array<string, mixed>|null
     */
    public function getSyncStatus(string $dataType): ?array
    {
        $statusKey = self::SYNC_STATUS_KEY.$dataType;

        return Cache::get($statusKey);
    }

    /**
     * Get sync status for all data types
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllSyncStatus(): array
        return [
            'characters' => $this->getSyncStatus('characters'),
            'support_cards' => $this->getSyncStatus('support_cards'),
            'meta_rankings' => $this->getSyncStatus('meta_rankings'),
            'skill_effectiveness' => $this->getSyncStatus('skill_effectiveness'),
        ];
    }

    /**
     * Record sync history
     *
     * @param  array<string, mixed>  $job
     */
    protected function recordSyncHistory(array $job, string $status, string $message): void
    {
        $historyKey = self::SYNC_HISTORY_KEY.$job['data_type'];

        $historyEntry = [
            'job_id' => $job['id'],
            'data_type' => $job['data_type'],
            'status' => $status,
            'message' => $message,
            'attempts' => $job['attempts'],
            'timestamp' => now()->toIso8601String(),
        ];

        // Add to history list
        Redis::lpush($historyKey, json_encode($historyEntry));

        // Trim to max entries
        Redis::ltrim($historyKey, 0, self::MAX_HISTORY_ENTRIES - 1);
    }

    /**
     * Get sync history for a data type
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSyncHistory(): array
        $historyKey = self::SYNC_HISTORY_KEY.$dataType;
        $entries = Redis::lrange($historyKey, 0, $limit - 1);

        return array_map(fn ($entry) => json_decode($entry, true), $entries);
    }

    /**
     * Reconcile data between cache and API
     *
     * @return array{reconciled: bool, differences: array<string, mixed>, actions_taken: array<string>}
     */
    public function reconcileData(): array
        Log::info('[BackgroundSync] Starting data reconciliation', [
            'data_type' => $dataType,
        ]);

        // Get cached data
        $cacheKey = $this->getCacheKeyForDataType($dataType);
        $cachedData = Cache::get($cacheKey);

        if (! $cachedData) {
            return [
                'reconciled' => false,
                'differences' => [],
                'actions_taken' => ['No cached data to reconcile'],
            ];
        }

        // Fetch fresh data from API
        $freshData = $this->fetchFreshData($dataType);

        if (! $freshData['success']) {
            return [
                'reconciled' => false,
                'differences' => [],
                'actions_taken' => ['Failed to fetch fresh data for reconciliation'],
            ];
        }

        // Compare data
        $differences = $this->compareData($cachedData, $freshData['data']);

        $actionsTaken = [];

        if (! empty($differences)) {
            // Update cache with fresh data
            $this->degradationService->storeWithTimestamp(
                $cacheKey,
                $freshData['data'],
                $this->getTTLForDataType($dataType)
            );

            $actionsTaken[] = sprintf('Updated cache with %d changes', count($differences));
        } else {
            $actionsTaken[] = 'No differences found, cache is up to date';
        }

        Log::info('[BackgroundSync] Data reconciliation completed', [
            'data_type' => $dataType,
            'differences_count' => count($differences),
        ]);

        return [
            'reconciled' => true,
            'differences' => $differences,
            'actions_taken' => $actionsTaken,
        ];
    }

    /**
     * Get cache key for data type
     */
    protected function getCacheKeyForDataType(string $dataType): string
    {
        return match ($dataType) {
            'characters' => 'umapyoi:characters',
            'support_cards' => 'umapyoi:support_cards',
            'meta_rankings' => 'umamusumedb:meta:tier_rankings',
            'skill_effectiveness' => 'umamusumedb:skills:effectiveness',
            default => 'unknown:'.$dataType,
        };
    }

    /**
     * Get TTL for data type
     */
    protected function getTTLForDataType(string $dataType): int
    {
        return match ($dataType) {
            'characters', 'support_cards' => 86400, // 24 hours
            'meta_rankings', 'skill_effectiveness' => 43200, // 12 hours
            default => 3600, // 1 hour
        };
    }

    /**
     * Fetch fresh data from API
     *
     * @return array{success: bool, data: mixed, error?: string}
     */
    protected function fetchFreshData(): array
        return match ($dataType) {
            'characters' => $this->apiFacade->getCharacters(true),
            'support_cards' => $this->apiFacade->getSupportCards(true),
            'meta_rankings' => $this->apiFacade->getMetaTierRankings(true),
            'skill_effectiveness' => $this->apiFacade->getSkillEffectiveness(true),
            default => ['success' => false, 'data' => null, 'error' => 'Unknown data type'],
        };
    }

    /**
     * Compare cached and fresh data
     *
     * @param  mixed  $cachedData
     * @param  mixed  $freshData
     * @return array<string, mixed>
     */
    protected function compareData(): array
        // Simple comparison - in production, this would be more sophisticated
        if (json_encode($cachedData) === json_encode($freshData)) {
            return [];
        }

        return [
            'cached_count' => is_array($cachedData) ? count($cachedData) : 0,
            'fresh_count' => is_array($freshData) ? count($freshData) : 0,
            'changed' => true,
        ];
    }

    /**
     * Schedule automatic sync for all data types
     */
    public function scheduleAutoSync(): void
    {
        $dataTypes = ['characters', 'support_cards', 'meta_rankings', 'skill_effectiveness'];

        foreach ($dataTypes as $dataType) {
            $this->queueSync($dataType, ['auto_sync' => true]);
        }

        Log::info('[BackgroundSync] Auto-sync scheduled for all data types');
    }

    /**
     * Process all sync queues
     *
     * @return array<string, array{processed: int, succeeded: int, failed: int, skipped: int}>
     */
    public function processAllQueues(): array
        $results = [];

        $dataTypes = ['characters', 'support_cards', 'meta_rankings', 'skill_effectiveness'];

        foreach ($dataTypes as $dataType) {
            $results[$dataType] = $this->processSyncQueue($dataType);
        }

        return $results;
    }
}
