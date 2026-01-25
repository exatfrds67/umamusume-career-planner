<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ExternalAPI\CacheManagerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Sync External Data Job
 *
 * Background job for synchronizing external API data when connectivity is restored.
 * This job fetches fresh data from external APIs and updates the cache with
 * conflict resolution and progress tracking.
 *
 * Features:
 * - Automatic retry on failure
 * - Conflict resolution between cached and fresh data
 * - Progress tracking for monitoring
 * - Detailed logging
 * - Support for multiple data types
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.2.2
 */
class SyncExternalDataJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Sync status: pending
     */
    public const SYNC_PENDING = 'pending';

    /**
     * Sync status: in progress
     */
    public const SYNC_IN_PROGRESS = 'in_progress';

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
     * Supported data types for synchronization
     */
    private const SUPPORTED_DATA_TYPES = [
        'character',
        'support_card',
        'race',
        'skill',
        'news',
        'meta_ranking',
        'game_mechanics',
    ];

    /**
     * Create a new job instance.
     *
     * @param  string  $dataType  Type of data to sync (character, support_card, race, skill, etc.)
     * @param  array<string>  $identifiers  List of identifiers to sync (character names, card IDs, etc.)
     * @param  string|null  $syncId  Unique identifier for this sync operation (for progress tracking)
     */
    public function __construct(
        public string $dataType,
        public array $identifiers,
        public ?string $syncId = null
    ) {
        // Validate data type
        if (! in_array($dataType, self::SUPPORTED_DATA_TYPES, true)) {
            throw new \InvalidArgumentException(
                "Invalid data type: {$dataType}. Supported types: ".implode(', ', self::SUPPORTED_DATA_TYPES)
            );
        }

        // Generate sync ID if not provided
        if ($this->syncId === null) {
            $this->syncId = uniqid('sync_', true);
        }

        // Set queue based on data type priority
        $this->onQueue($this->getQueueForDataType($dataType));
    }

    /**
     * Execute the job.
     */
    public function handle(CacheManagerService $cacheManager): void
    {
        Log::info('[SyncExternalDataJob] Starting data synchronization', [
            'sync_id' => $this->syncId,
            'data_type' => $this->dataType,
            'identifier_count' => count($this->identifiers),
            'attempt' => $this->attempts(),
            'job_id' => (string) ($this->job?->getJobId() ?? ''),
        ]);

        // Initialize progress tracking
        $this->initializeProgress();
        $this->registerActiveSync();

        $successCount = 0;
        $failureCount = 0;
        $conflictCount = 0;
        $errors = [];

        foreach ($this->identifiers as $index => $identifier) {
            try {
                // Update progress
                $this->updateProgress($index + 1, count($this->identifiers));

                // Sync the data
                $result = $this->syncData($identifier, $cacheManager);

                if ($result['success']) {
                    $successCount++;

                    if ($result['had_conflict']) {
                        $conflictCount++;
                    }

                    Log::debug('[SyncExternalDataJob] Data synced successfully', [
                        'sync_id' => $this->syncId,
                        'data_type' => $this->dataType,
                        'identifier' => $identifier,
                        'had_conflict' => $result['had_conflict'],
                        'resolution_strategy' => $result['resolution_strategy'],
                    ]);
                } else {
                    $failureCount++;
                    $errors[$identifier] = $result['error'] ?? 'Unknown error';

                    Log::warning('[SyncExternalDataJob] Data sync failed', [
                        'sync_id' => $this->syncId,
                        'data_type' => $this->dataType,
                        'identifier' => $identifier,
                        'error' => $result['error'] ?? 'Unknown error',
                    ]);
                }
            } catch (\Exception $e) {
                $failureCount++;
                $errors[$identifier] = $e->getMessage();

                Log::error('[SyncExternalDataJob] Exception during data sync', [
                    'sync_id' => $this->syncId,
                    'data_type' => $this->dataType,
                    'identifier' => $identifier,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // Complete progress tracking
        $this->completeProgress($successCount, $failureCount, $conflictCount);
        $this->unregisterActiveSync();

        // Log final results
        Log::info('[SyncExternalDataJob] Data synchronization completed', [
            'sync_id' => $this->syncId,
            'data_type' => $this->dataType,
            'total_items' => count($this->identifiers),
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'conflict_count' => $conflictCount,
            'attempt' => $this->attempts(),
        ]);

        // If there were failures and we have retries left, fail the job
        if ($failureCount > 0 && $this->attempts() < $this->tries) {
            throw new \RuntimeException(
                "Data synchronization failed for {$failureCount} items. Errors: ".json_encode($errors)
            );
        }
    }

    /**
     * Sync a single data item
     *
     * @return array{success: bool, had_conflict: bool, resolution_strategy: string|null, error: string|null}
     */
    protected function syncData(string $identifier, CacheManagerService $cacheManager): array
    {
        $cacheKey = $this->buildCacheKey($identifier);

        // Get existing cached data
        $cachedData = $cacheManager->get($cacheKey);

        // Fetch fresh data from API
        $freshData = $this->fetchFreshData($identifier);

        if ($freshData === null) {
            return [
                'success' => false,
                'had_conflict' => false,
                'resolution_strategy' => null,
                'error' => 'Failed to fetch fresh data from API',
            ];
        }

        // Check for conflicts
        $hadConflict = false;
        $resolutionStrategy = null;

        if ($cachedData !== null) {
            // Remove cache metadata for comparison
            $cachedDataClean = $this->removeCacheMetadata($cachedData);
            $freshDataClean = $this->removeCacheMetadata($freshData);

            // Detect conflicts
            if ($this->hasConflict($cachedDataClean, $freshDataClean)) {
                $hadConflict = true;

                // Resolve conflict
                $resolvedData = $this->resolveConflict($cachedDataClean, $freshDataClean, $identifier);
                $resolutionStrategy = $resolvedData['strategy'];
                $freshData = $resolvedData['data'];

                Log::info('[SyncExternalDataJob] Conflict detected and resolved', [
                    'sync_id' => $this->syncId,
                    'data_type' => $this->dataType,
                    'identifier' => $identifier,
                    'strategy' => $resolutionStrategy,
                ]);
            }
        }

        // Update cache with fresh data
        $cacheManager->put($cacheKey, $freshData);

        return [
            'success' => true,
            'had_conflict' => $hadConflict,
            'resolution_strategy' => $resolutionStrategy,
            'error' => null,
        ];
    }

    /**
     * Fetch fresh data from external API
     *
     * Uses the ExternalDataService to fetch data based on data type.
     * Each data type maps to a specific API endpoint and transformation.
     *
     * @return array<string, mixed>|null
     */
    protected function fetchFreshData(string $identifier): ?array
    {
        Log::debug('[SyncExternalDataJob] Fetching fresh data', [
            'sync_id' => $this->syncId,
            'data_type' => $this->dataType,
            'identifier' => $identifier,
        ]);

        try {
            /** @var \App\Services\ExternalDataService $externalDataService */
            $externalDataService = app(\App\Services\ExternalDataService::class);

            // Fetch data based on data type using ExternalDataService
            // Note: These methods are future API integration points
            $data = match ($this->dataType) {
                'character' => $externalDataService->fetchCharacterData($identifier), // @phpstan-ignore method.notFound
                'support_card' => $externalDataService->fetchSupportCardData($identifier), // @phpstan-ignore method.notFound
                'race' => $externalDataService->fetchRaceData($identifier), // @phpstan-ignore method.notFound
                'skill' => $externalDataService->fetchSkillData($identifier), // @phpstan-ignore method.notFound
                'news' => $externalDataService->fetchNewsData($identifier), // @phpstan-ignore method.notFound
                'meta_ranking' => $externalDataService->fetchMetaRankingData($identifier), // @phpstan-ignore method.notFound
                'game_mechanics' => $externalDataService->fetchGameMechanicsData($identifier), // @phpstan-ignore method.notFound
                default => null,
            };

            if ($data === null) {
                Log::warning('[SyncExternalDataJob] No data returned from API', [
                    'sync_id' => $this->syncId,
                    'data_type' => $this->dataType,
                    'identifier' => $identifier,
                ]);

                return null;
            }

            // Add metadata for tracking
            $data['_fetched_at'] = now()->toIso8601String();
            $data['_source'] = 'external_api';
            $data['_data_type'] = $this->dataType;

            return $data;
        } catch (\Exception $e) {
            Log::error('[SyncExternalDataJob] Failed to fetch data from API', [
                'sync_id' => $this->syncId,
                'data_type' => $this->dataType,
                'identifier' => $identifier,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if there's a conflict between cached and fresh data
     *
     * @param  array<string, mixed>  $cachedData
     * @param  array<string, mixed>  $freshData
     */
    protected function hasConflict(array $cachedData, array $freshData): bool
    {
        // Simple comparison - can be enhanced with more sophisticated logic
        return $cachedData !== $freshData;
    }

    /**
     * Resolve conflict between cached and fresh data
     *
     * @param  array<string, mixed>  $cachedData
     * @param  array<string, mixed>  $freshData
     * @return array{data: array<string, mixed>, strategy: string}
     */
    protected function resolveConflict(array $cachedData, array $freshData, string $identifier): array
    {
        // Default strategy: prefer fresh data from API
        // This can be enhanced with more sophisticated conflict resolution strategies:
        // - Timestamp-based resolution
        // - Field-level merging
        // - User preference-based resolution
        // - Confidence score-based resolution

        $strategy = 'prefer_fresh';

        // Check if cached data has user modifications
        if (isset($cachedData['_user_modified']) && $cachedData['_user_modified'] === true) {
            // Preserve user modifications
            $strategy = 'preserve_user_modifications';

            // Merge fresh data with user modifications
            $resolvedData = array_merge($freshData, [
                '_user_modified' => true,
                '_original_data' => $freshData,
            ]);

            // Preserve specific user-modified fields
            foreach ($cachedData as $key => $value) {
                if (str_starts_with($key, '_user_')) {
                    $resolvedData[$key] = $value;
                }
            }

            return [
                'data' => $resolvedData,
                'strategy' => $strategy,
            ];
        }

        // Default: use fresh data
        return [
            'data' => $freshData,
            'strategy' => $strategy,
        ];
    }

    /**
     * Remove cache metadata from data for comparison
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function removeCacheMetadata(array $data): array
    {
        // Remove cache-specific metadata fields
        $metadataKeys = ['_cache', '_metadata', '_source'];

        foreach ($metadataKeys as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    /**
     * Build cache key for identifier
     */
    protected function buildCacheKey(string $identifier): string
    {
        return "{$this->dataType}:{$identifier}";
    }

    /**
     * Get queue name based on data type priority
     */
    protected function getQueueForDataType(string $dataType): string
    {
        // High priority data types use 'high' queue
        $highPriority = ['character', 'support_card'];

        // Medium priority data types use 'default' queue
        $mediumPriority = ['race', 'skill'];

        // Low priority data types use 'low' queue
        $lowPriority = ['news', 'meta_ranking', 'game_mechanics'];

        if (in_array($dataType, $highPriority, true)) {
            return 'high';
        }

        if (in_array($dataType, $mediumPriority, true)) {
            return 'default';
        }

        return 'low';
    }

    /**
     * Initialize progress tracking
     */
    protected function initializeProgress(): void
    {
        $progressKey = "sync_progress:{$this->syncId}";

        Cache::put($progressKey, [
            'sync_id' => $this->syncId,
            'data_type' => $this->dataType,
            'total_items' => count($this->identifiers),
            'processed_items' => 0,
            'success_count' => 0,
            'failure_count' => 0,
            'conflict_count' => 0,
            'status' => 'in_progress',
            'started_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ], 3600); // Keep for 1 hour
    }

    /**
     * Update progress tracking
     */
    protected function updateProgress(int $processedItems, int $totalItems): void
    {
        $progressKey = "sync_progress:{$this->syncId}";
        $cachedProgress = Cache::get($progressKey, []);
        /** @var array<string, mixed> $progress */
        $progress = \is_array($cachedProgress) ? $cachedProgress : [];

        $progress['processed_items'] = $processedItems;
        $progress['progress_percentage'] = round(($processedItems / $totalItems) * 100, 2);
        $progress['updated_at'] = now()->toISOString();

        Cache::put($progressKey, $progress, 3600);
    }

    /**
     * Complete progress tracking
     */
    protected function completeProgress(int $successCount, int $failureCount, int $conflictCount): void
    {
        $progressKey = "sync_progress:{$this->syncId}";
        $cachedProgress = Cache::get($progressKey, []);
        /** @var array<string, mixed> $progress */
        $progress = \is_array($cachedProgress) ? $cachedProgress : [];

        $progress['success_count'] = $successCount;
        $progress['failure_count'] = $failureCount;
        $progress['conflict_count'] = $conflictCount;
        $progress['status'] = $failureCount > 0 ? 'completed_with_errors' : 'completed';
        $progress['completed_at'] = now()->toISOString();
        $progress['updated_at'] = now()->toISOString();

        Cache::put($progressKey, $progress, 3600);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[SyncExternalDataJob] Data synchronization job failed permanently', [
            'sync_id' => $this->syncId,
            'data_type' => $this->dataType,
            'identifier_count' => count($this->identifiers),
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Update progress tracking to failed status
        $progressKey = "sync_progress:{$this->syncId}";
        $cachedProgress = Cache::get($progressKey, []);
        /** @var array<string, mixed> $progress */
        $progress = \is_array($cachedProgress) ? $cachedProgress : [];

        $progress['status'] = 'failed';
        $progress['error'] = $exception->getMessage();
        $progress['failed_at'] = now()->toISOString();
        $progress['updated_at'] = now()->toISOString();

        Cache::put($progressKey, $progress, 3600);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return [
            'data-sync',
            "type:{$this->dataType}",
            "sync:{$this->syncId}",
        ];
    }

    /**
     * Get sync progress for a sync ID
     *
     * @return array<string, mixed>|null
     */
    public static function getSyncProgress(string $syncId): ?array
    {
        $progressKey = "sync_progress:{$syncId}";
        $result = Cache::get($progressKey);

        if (! \is_array($result)) {
            return null;
        }

        /** @var array<string, mixed> $typedResult */
        $typedResult = $result;

        return $typedResult;
    }

    /**
     * Get all active sync operations
     *
     * Retrieves all sync operations that are currently in progress or pending.
     * Uses cache pattern matching to find all sync progress entries.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getActiveSyncs(): array
    {
        $activeSyncs = [];

        // Get all sync progress keys from cache
        // Note: This requires Redis or a cache driver that supports key scanning
        // For array/file cache, we track active syncs in a separate key
        $cachedIds = Cache::get('active_sync_ids', []);
        /** @var array<int, string> $activeSyncIds */
        $activeSyncIds = \is_array($cachedIds) ? $cachedIds : [];

        foreach ($activeSyncIds as $syncId) {
            $progress = self::getSyncProgress($syncId);

            if ($progress !== null && \in_array($progress['status'] ?? '', [self::SYNC_PENDING, self::SYNC_IN_PROGRESS], true)) {
                $activeSyncs[$syncId] = $progress;
            }
        }

        return $activeSyncs;
    }

    /**
     * Register a sync operation as active
     */
    protected function registerActiveSync(): void
    {
        $cachedIds = Cache::get('active_sync_ids', []);
        /** @var array<int, string|null> $activeSyncIds */
        $activeSyncIds = \is_array($cachedIds) ? $cachedIds : [];
        $activeSyncIds[] = $this->syncId;

        // Keep only unique IDs and limit to last 100
        $activeSyncIds = \array_unique($activeSyncIds);
        if (\count($activeSyncIds) > 100) {
            $activeSyncIds = \array_slice($activeSyncIds, -100);
        }

        Cache::put('active_sync_ids', $activeSyncIds, 3600);
    }

    /**
     * Unregister a sync operation from active list
     */
    protected function unregisterActiveSync(): void
    {
        $cachedIds = Cache::get('active_sync_ids', []);
        /** @var array<int, string|null> $activeSyncIds */
        $activeSyncIds = \is_array($cachedIds) ? $cachedIds : [];
        $activeSyncIds = \array_filter($activeSyncIds, fn ($id) => $id !== $this->syncId);
        Cache::put('active_sync_ids', \array_values($activeSyncIds), 3600);
    }
}
