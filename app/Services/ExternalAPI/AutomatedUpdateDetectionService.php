<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * Automated Update Detection Service with MCP Monitoring Agents
 *
 * Monitors external data sources for changes using MCP monitoring agents,
 * detects updates automatically, and triggers synchronization workflows.
 *
 * Requirements: 14.3, 14.4, 56.3, Task 4.4.4
 */
class AutomatedUpdateDetectionService
{
    /**
     * Update detection cache key
     */
    protected const UPDATE_DETECTION_KEY = 'update_detection:';

    /**
     * Change log key
     */
    protected const CHANGE_LOG_KEY = 'change_log:';

    /**
     * Monitoring interval in seconds
     */
    protected const MONITORING_INTERVAL = 300; // 5 minutes

    /**
     * Change detection threshold
     */
    protected const CHANGE_THRESHOLD = 0.05; // 5% change

    /**
     * Maximum change log entries
     */
    protected const MAX_CHANGE_LOG_ENTRIES = 1000;

    public function __construct(
        protected MCPClientService $mcpClient,
        protected UmapyoiApiClient $umapyoiClient,
        protected UmamusumeDBApiClient $umamusumeDBClient,
        protected DataSynchronizationAgentService $syncAgent,
        protected BackgroundSyncService $backgroundSync
    ) {}

    /**
     * Monitor data source for updates
     *
     * @param  array<string, mixed>  $options
     * @return array{updates_detected: bool, changes: array<string, mixed>, last_check: string, next_check: string}
     */
    public function monitorDataSource(string $dataSource, array $options = []): array
    {
        Log::info('[UpdateDetection] Monitoring data source', [
            'data_source' => $dataSource,
            'options' => $options,
        ]);

        $startTime = microtime(true);

        // Get last known state
        $lastState = $this->getLastKnownState($dataSource);

        // Fetch current state
        $currentState = $this->fetchCurrentState($dataSource);

        if ($currentState === null) {
            Log::warning('[UpdateDetection] Failed to fetch current state', [
                'data_source' => $dataSource,
            ]);

            return [
                'updates_detected' => false,
                'changes' => [],
                'last_check' => now()->toIso8601String(),
                'next_check' => now()->addSeconds(self::MONITORING_INTERVAL)->toIso8601String(),
            ];
        }

        // Detect changes
        $changes = $this->detectChanges($lastState, $currentState, $dataSource);

        $updatesDetected = ! empty($changes);

        if ($updatesDetected) {
            // Store current state as new baseline
            $this->storeCurrentState($dataSource, $currentState);

            // Log changes
            $this->logChanges($dataSource, $changes);

            // Trigger synchronization if needed
            if ($options['auto_sync'] ?? true) {
                $this->triggerSynchronization($dataSource, $changes);
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('[UpdateDetection] Monitoring completed', [
            'data_source' => $dataSource,
            'updates_detected' => $updatesDetected,
            'changes_count' => count($changes),
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'updates_detected' => $updatesDetected,
            'changes' => $changes,
            'last_check' => now()->toIso8601String(),
            'next_check' => now()->addSeconds(self::MONITORING_INTERVAL)->toIso8601String(),
        ];
    }

    /**
     * Monitor all configured data sources
     *
     * @return array<string, array{updates_detected: bool, changes: array<string, mixed>, last_check: string, next_check: string}>
     */
    public function monitorAllSources(): array
    {
        $dataSources = [
            'umapyoi_characters',
            'umapyoi_support_cards',
            'umapyoi_news',
            'umamusumedb_meta',
            'umamusumedb_skills',
        ];

        $results = [];

        foreach ($dataSources as $source) {
            try {
                $results[$source] = $this->monitorDataSource($source);
            } catch (\Exception $e) {
                Log::error('[UpdateDetection] Monitoring failed', [
                    'data_source' => $source,
                    'error' => $e->getMessage(),
                ]);

                $results[$source] = [
                    'updates_detected' => false,
                    'changes' => [],
                    'last_check' => now()->toIso8601String(),
                    'next_check' => now()->addSeconds(self::MONITORING_INTERVAL)->toIso8601String(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get last known state for data source
     *
     * @return array<string, mixed>|null
     */
    protected function getLastKnownState(string $dataSource): ?array
    {
        $stateKey = self::UPDATE_DETECTION_KEY.$dataSource.':state';

        return Cache::get($stateKey);
    }

    /**
     * Fetch current state from data source
     *
     * @return array<string, mixed>|null
     */
    protected function fetchCurrentState(string $dataSource): ?array
    {
        try {
            $data = match ($dataSource) {
                'umapyoi_characters' => $this->umapyoiClient->getCharacters(),
                'umapyoi_support_cards' => $this->umapyoiClient->getSupportCards(),
                'umapyoi_news' => $this->umapyoiClient->getNews(),
                'umamusumedb_meta' => $this->umamusumeDBClient->getMetaTierRankings(),
                'umamusumedb_skills' => $this->umamusumeDBClient->getSkillEffectiveness(),
                default => null,
            };

            if ($data === null) {
                return null;
            }

            // Create state snapshot
            return [
                'data' => $data,
                'count' => is_array($data) ? count($data) : 0,
                'checksum' => $this->calculateChecksum($data),
                'timestamp' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('[UpdateDetection] Failed to fetch current state', [
                'data_source' => $dataSource,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Detect changes between states
     *
     * @param  array<string, mixed>|null  $lastState
     * @param  array<string, mixed>  $currentState
     * @return array<string, mixed>
     */
    protected function detectChanges(?array $lastState, array $currentState, string $dataSource): array
    {
        if ($lastState === null) {
            return [
                'type' => 'initial_state',
                'message' => 'First monitoring check - establishing baseline',
            ];
        }

        $changes = [];

        // Check for count changes
        if ($lastState['count'] !== $currentState['count']) {
            $changes['count_change'] = [
                'type' => 'count',
                'previous' => $lastState['count'],
                'current' => $currentState['count'],
                'difference' => $currentState['count'] - $lastState['count'],
            ];
        }

        // Check for checksum changes
        if ($lastState['checksum'] !== $currentState['checksum']) {
            $changes['content_change'] = [
                'type' => 'content',
                'previous_checksum' => $lastState['checksum'],
                'current_checksum' => $currentState['checksum'],
                'message' => 'Data content has changed',
            ];

            // Perform detailed change analysis
            $detailedChanges = $this->analyzeDetailedChanges(
                $lastState['data'],
                $currentState['data']
            );

            if (! empty($detailedChanges)) {
                $changes['detailed_changes'] = $detailedChanges;
            }
        }

        // Check for significant time gap
        $lastTimestamp = \Carbon\Carbon::parse($lastState['timestamp']);
        $currentTimestamp = \Carbon\Carbon::parse($currentState['timestamp']);
        $hoursSinceLastCheck = $lastTimestamp->diffInHours($currentTimestamp);

        if ($hoursSinceLastCheck > 24) {
            $changes['stale_data_warning'] = [
                'type' => 'warning',
                'message' => sprintf('Data has not been checked for %d hours', $hoursSinceLastCheck),
                'hours_since_last_check' => $hoursSinceLastCheck,
            ];
        }

        return $changes;
    }

    /**
     * Analyze detailed changes between data sets
     *
     * @return array<string, mixed>
     */
    protected function analyzeDetailedChanges(mixed $lastData, mixed $currentData): array
    {
        if (! is_array($lastData) || ! is_array($currentData)) {
            return [];
        }

        $changes = [
            'added' => [],
            'removed' => [],
            'modified' => [],
        ];

        // Normalize data to associative arrays by ID
        $lastNormalized = $this->normalizeDataById($lastData);
        $currentNormalized = $this->normalizeDataById($currentData);

        // Find added items
        $addedIds = array_diff(array_keys($currentNormalized), array_keys($lastNormalized));

        foreach ($addedIds as $id) {
            $changes['added'][] = [
                'id' => $id,
                'data' => $currentNormalized[$id],
            ];
        }

        // Find removed items
        $removedIds = array_diff(array_keys($lastNormalized), array_keys($currentNormalized));

        foreach ($removedIds as $id) {
            $changes['removed'][] = [
                'id' => $id,
                'data' => $lastNormalized[$id],
            ];
        }

        // Find modified items
        $commonIds = array_intersect(array_keys($lastNormalized), array_keys($currentNormalized));

        foreach ($commonIds as $id) {
            $lastItem = $lastNormalized[$id];
            $currentItem = $currentNormalized[$id];

            if (json_encode($lastItem) !== json_encode($currentItem)) {
                $changes['modified'][] = [
                    'id' => $id,
                    'previous' => $lastItem,
                    'current' => $currentItem,
                    'fields_changed' => $this->findChangedFields($lastItem, $currentItem),
                ];
            }
        }

        return $changes;
    }

    /**
     * Normalize data by ID
     *
     * @param  array<mixed>  $data
     * @return array<string|int, mixed>
     */
    protected function normalizeDataById(array $data): array
    {
        $normalized = [];

        foreach ($data as $item) {
            if (is_array($item) && isset($item['id'])) {
                $normalized[$item['id']] = $item;
            }
        }

        return $normalized;
    }

    /**
     * Find changed fields between two items
     *
     * @param  array<string, mixed>  $lastItem
     * @param  array<string, mixed>  $currentItem
     * @return array<string>
     */
    protected function findChangedFields(array $lastItem, array $currentItem): array
    {
        $changedFields = [];

        $allFields = array_unique(array_merge(array_keys($lastItem), array_keys($currentItem)));

        foreach ($allFields as $field) {
            $lastValue = $lastItem[$field] ?? null;
            $currentValue = $currentItem[$field] ?? null;

            if ($lastValue !== $currentValue) {
                $changedFields[] = $field;
            }
        }

        return $changedFields;
    }

    /**
     * Calculate checksum for data
     */
    protected function calculateChecksum(mixed $data): string
    {
        return md5(json_encode($data));
    }

    /**
     * Store current state
     *
     * @param  array<string, mixed>  $state
     */
    protected function storeCurrentState(string $dataSource, array $state): void
    {
        $stateKey = self::UPDATE_DETECTION_KEY.$dataSource.':state';
        Cache::put($stateKey, $state, 86400); // 24 hours
    }

    /**
     * Log changes
     *
     * @param  array<string, mixed>  $changes
     */
    protected function logChanges(string $dataSource, array $changes): void
    {
        $changeLogKey = self::CHANGE_LOG_KEY.$dataSource;

        $logEntry = [
            'data_source' => $dataSource,
            'changes' => $changes,
            'timestamp' => now()->toIso8601String(),
        ];

        // Add to Redis list
        Redis::lpush($changeLogKey, json_encode($logEntry));

        // Trim to max entries
        Redis::ltrim($changeLogKey, 0, self::MAX_CHANGE_LOG_ENTRIES - 1);

        // Set expiry to 30 days
        Redis::expire($changeLogKey, 2592000);

        Log::info('[UpdateDetection] Changes logged', [
            'data_source' => $dataSource,
            'changes_count' => count($changes),
        ]);
    }

    /**
     * Trigger synchronization
     *
     * @param  array<string, mixed>  $changes
     */
    protected function triggerSynchronization(string $dataSource, array $changes): void
    {
        Log::info('[UpdateDetection] Triggering synchronization', [
            'data_source' => $dataSource,
            'changes' => $changes,
        ]);

        // Queue background sync
        $this->backgroundSync->queueSync($dataSource, [
            'triggered_by' => 'update_detection',
            'changes' => $changes,
        ]);
    }

    /**
     * Get change log for data source
     *
     * @return array<int, array<string, mixed>>
     */
    public function getChangeLog(string $dataSource, int $limit = 10): array
    {
        $changeLogKey = self::CHANGE_LOG_KEY.$dataSource;

        $entries = Redis::lrange($changeLogKey, 0, $limit - 1);

        return array_map(fn ($entry) => json_decode($entry, true), $entries);
    }

    /**
     * Get monitoring status for all sources
     *
     * @return array<string, array{last_check: string|null, updates_detected: int, last_change: string|null}>
     */
    public function getMonitoringStatus(): array
    {
        $dataSources = [
            'umapyoi_characters',
            'umapyoi_support_cards',
            'umapyoi_news',
            'umamusumedb_meta',
            'umamusumedb_skills',
        ];

        $status = [];

        foreach ($dataSources as $source) {
            $lastState = $this->getLastKnownState($source);
            $changeLog = $this->getChangeLog($source, 1);

            $status[$source] = [
                'last_check' => $lastState['timestamp'] ?? null,
                'updates_detected' => count($changeLog),
                'last_change' => ! empty($changeLog) ? $changeLog[0]['timestamp'] : null,
            ];
        }

        return $status;
    }

    /**
     * Get update detection statistics
     *
     * @return array{total_checks: int, updates_detected: int, avg_changes_per_update: float, most_active_source: string}
     */
    public function getUpdateStatistics(): array
    {
        $dataSources = [
            'umapyoi_characters',
            'umapyoi_support_cards',
            'umapyoi_news',
            'umamusumedb_meta',
            'umamusumedb_skills',
        ];

        $totalChecks = 0;
        $totalUpdates = 0;
        $totalChanges = 0;
        $sourceActivity = [];

        foreach ($dataSources as $source) {
            $changeLog = $this->getChangeLog($source, 100);
            $updateCount = count($changeLog);

            $totalChecks += $updateCount;
            $totalUpdates += $updateCount;

            foreach ($changeLog as $entry) {
                $totalChanges += count($entry['changes']);
            }

            $sourceActivity[$source] = $updateCount;
        }

        arsort($sourceActivity);
        $mostActiveSource = array_key_first($sourceActivity) ?? 'none';

        return [
            'total_checks' => $totalChecks,
            'updates_detected' => $totalUpdates,
            'avg_changes_per_update' => $totalUpdates > 0 ? round($totalChanges / $totalUpdates, 2) : 0.0,
            'most_active_source' => $mostActiveSource,
        ];
    }

    /**
     * Schedule automated monitoring
     *
     * @param  array<string>  $dataSources
     * @param  array<string, mixed>  $options
     */
    public function scheduleAutomatedMonitoring(array $dataSources, array $options = []): void
    {
        $monitoringId = uniqid('monitoring_', true);

        Log::info('[UpdateDetection] Scheduling automated monitoring', [
            'monitoring_id' => $monitoringId,
            'sources' => $dataSources,
            'interval' => $options['interval'] ?? self::MONITORING_INTERVAL,
        ]);

        // In production, this would schedule a recurring job
        // For now, we log the scheduling
    }
}
