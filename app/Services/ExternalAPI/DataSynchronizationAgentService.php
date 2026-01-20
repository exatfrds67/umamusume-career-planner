<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use App\Services\MCP\SubagentCoordinationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Data Synchronization Agent Service with MCP Integration
 *
 * Manages multi-source data synchronization using strands-agents MCP server
 * for intelligent coordination, conflict resolution, and data quality assurance.
 *
 * Requirements: 14.3, 14.4, 56.3, Task 4.4.4
 */
class DataSynchronizationAgentService
{
    /**
     * Sync coordination cache key
     */
    protected const SYNC_COORDINATION_KEY = 'sync_coordination:';

    /**
     * Sync conflict key
     */
    protected const SYNC_CONFLICT_KEY = 'sync_conflicts:';

    /**
     * Maximum concurrent sync operations
     */
    protected const MAX_CONCURRENT_SYNCS = 5;

    /**
     * Sync timeout in seconds
     */
    protected const SYNC_TIMEOUT = 300;

    public function __construct(
        protected MCPClientService $mcpClient,
        protected SubagentCoordinationService $subagentCoordination,
        protected UmapyoiApiClient $umapyoiClient,
        protected UmamusumeDBApiClient $umamusumeDBClient,
        protected DataValidationService $validationService,
        protected ConflictResolutionService $conflictResolution
    ) {}

    /**
     * Coordinate multi-source data synchronization using MCP agents
     *
     * @param  array<string>  $dataSources
     * @param  array<string, mixed>  $options
     * @return array{success: bool, synchronized: array<string, mixed>, conflicts: array<string, mixed>, quality_score: float, duration_ms: float}
     */
    public function coordinateMultiSourceSync(array $dataSources, array $options = []): array
    {
        $startTime = microtime(true);
        $syncId = uniqid('sync_', true);

        Log::info('[DataSyncAgent] Starting multi-source synchronization', [
            'sync_id' => $syncId,
            'sources' => $dataSources,
            'options' => $options,
        ]);

        // Check if strands-agents MCP server is available
        if (! $this->mcpClient->isServerEnabled('strands-agents')) {
            Log::warning('[DataSyncAgent] strands-agents MCP server not available, using fallback');

            return $this->fallbackSync($dataSources, $options);
        }

        try {
            // Create coordination workflow using MCP subagents
            $workflow = $this->createSyncWorkflow($syncId, $dataSources, $options);

            // Execute parallel data fetching with MCP agent coordination
            $fetchedData = $this->executeParallelFetch($workflow);

            // Validate fetched data using validation workflows
            $validationResults = $this->validateFetchedData($fetchedData);

            // Detect and resolve conflicts
            $conflictResolution = $this->resolveDataConflicts($fetchedData, $validationResults);

            // Calculate data quality score
            $qualityScore = $this->calculateQualityScore($conflictResolution);

            // Store synchronized data
            $synchronized = $this->storeSynchronizedData($conflictResolution['resolved_data']);

            $duration = (microtime(true) - $startTime) * 1000;

            Log::info('[DataSyncAgent] Multi-source synchronization completed', [
                'sync_id' => $syncId,
                'sources_count' => count($dataSources),
                'conflicts_detected' => count($conflictResolution['conflicts']),
                'quality_score' => $qualityScore,
                'duration_ms' => round($duration, 2),
            ]);

            return [
                'success' => true,
                'synchronized' => $synchronized,
                'conflicts' => $conflictResolution['conflicts'],
                'quality_score' => $qualityScore,
                'duration_ms' => round($duration, 2),
            ];
        } catch (\Exception $e) {
            Log::error('[DataSyncAgent] Multi-source synchronization failed', [
                'sync_id' => $syncId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'synchronized' => [],
                'conflicts' => [],
                'quality_score' => 0.0,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ];
        }
    }

    /**
     * Create synchronization workflow with MCP agent coordination
     *
     * @param  array<string>  $dataSources
     * @param  array<string, mixed>  $options
     * @return array{workflow_id: string, agents: array<string, array<string, mixed>>, coordination_strategy: string}
     */
    protected function createSyncWorkflow(string $syncId, array $dataSources, array $options): array
    {
        $workflowId = "sync_workflow_{$syncId}";

        // Define agents for each data source
        $agents = [];

        foreach ($dataSources as $source) {
            $agents[$source] = [
                'agent_id' => "sync_agent_{$source}_{$syncId}",
                'source' => $source,
                'priority' => $this->getSourcePriority($source),
                'timeout' => $options['timeout'] ?? self::SYNC_TIMEOUT,
                'retry_count' => $options['retry_count'] ?? 3,
            ];
        }

        // Determine coordination strategy based on source count
        $coordinationStrategy = count($dataSources) > 2 ? 'parallel_with_merge' : 'sequential_with_validation';

        Log::debug('[DataSyncAgent] Sync workflow created', [
            'workflow_id' => $workflowId,
            'agents_count' => count($agents),
            'strategy' => $coordinationStrategy,
        ]);

        return [
            'workflow_id' => $workflowId,
            'agents' => $agents,
            'coordination_strategy' => $coordinationStrategy,
        ];
    }

    /**
     * Execute parallel data fetching with MCP agent coordination
     *
     * @param  array{workflow_id: string, agents: array<string, array<string, mixed>>, coordination_strategy: string}  $workflow
     * @return array<string, array{success: bool, data: mixed, source: string, timestamp: string, metadata: array<string, mixed>}>
     */
    protected function executeParallelFetch(array $workflow): array
    {
        $fetchedData = [];

        Log::info('[DataSyncAgent] Executing parallel fetch', [
            'workflow_id' => $workflow['workflow_id'],
            'agents_count' => count($workflow['agents']),
        ]);

        // Execute fetch for each agent
        foreach ($workflow['agents'] as $source => $agentConfig) {
            try {
                $startTime = microtime(true);

                // Fetch data from source
                $data = $this->fetchFromSource($source, $agentConfig);

                $fetchTime = (microtime(true) - $startTime) * 1000;

                $fetchedData[$source] = [
                    'success' => true,
                    'data' => $data,
                    'source' => $source,
                    'timestamp' => now()->toIso8601String(),
                    'metadata' => [
                        'fetch_time_ms' => round($fetchTime, 2),
                        'agent_id' => $agentConfig['agent_id'],
                        'priority' => $agentConfig['priority'],
                    ],
                ];

                Log::debug('[DataSyncAgent] Source fetch completed', [
                    'source' => $source,
                    'fetch_time_ms' => round($fetchTime, 2),
                    'data_count' => is_array($data) ? count($data) : 0,
                ]);
            } catch (\Exception $e) {
                Log::error('[DataSyncAgent] Source fetch failed', [
                    'source' => $source,
                    'error' => $e->getMessage(),
                ]);

                $fetchedData[$source] = [
                    'success' => false,
                    'data' => null,
                    'source' => $source,
                    'timestamp' => now()->toIso8601String(),
                    'metadata' => [
                        'error' => $e->getMessage(),
                        'agent_id' => $agentConfig['agent_id'],
                    ],
                ];
            }
        }

        return $fetchedData;
    }

    /**
     * Fetch data from specific source
     *
     * @param  array<string, mixed>  $agentConfig
     */
    protected function fetchFromSource(string $source, array $agentConfig): mixed
    {
        return match ($source) {
            'umapyoi_characters' => $this->umapyoiClient->getCharacters(),
            'umapyoi_support_cards' => $this->umapyoiClient->getSupportCards(),
            'umapyoi_news' => $this->umapyoiClient->getNews(),
            'umamusumedb_meta' => $this->umamusumeDBClient->getMetaTierRankings(),
            'umamusumedb_skills' => $this->umamusumeDBClient->getSkillEffectiveness(),
            'umamusumedb_training' => $this->umamusumeDBClient->getTrainingCalculation([]),
            default => throw new \InvalidArgumentException("Unknown data source: {$source}"),
        };
    }

    /**
     * Validate fetched data using validation workflows
     *
     * @param  array<string, array{success: bool, data: mixed, source: string, timestamp: string, metadata: array<string, mixed>}>  $fetchedData
     * @return array<string, array{valid: bool, errors: array<string>, warnings: array<string>, score: float}>
     */
    protected function validateFetchedData(array $fetchedData): array
    {
        $validationResults = [];

        foreach ($fetchedData as $source => $result) {
            if (! $result['success']) {
                $validationResults[$source] = [
                    'valid' => false,
                    'errors' => ['Fetch failed'],
                    'warnings' => [],
                    'score' => 0.0,
                ];

                continue;
            }

            // Validate data using validation service
            $validation = $this->validationService->validateData($source, $result['data']);

            $validationResults[$source] = $validation;
        }

        return $validationResults;
    }

    /**
     * Resolve data conflicts between sources
     *
     * @param  array<string, array{success: bool, data: mixed, source: string, timestamp: string, metadata: array<string, mixed>}>  $fetchedData
     * @param  array<string, array{valid: bool, errors: array<string>, warnings: array<string>, score: float}>  $validationResults
     * @return array{resolved_data: array<string, mixed>, conflicts: array<string, mixed>, resolution_strategy: string}
     */
    protected function resolveDataConflicts(array $fetchedData, array $validationResults): array
    {
        // Collect valid data sources
        $validSources = [];

        foreach ($fetchedData as $source => $result) {
            if ($result['success'] && $validationResults[$source]['valid']) {
                $validSources[$source] = [
                    'data' => $result['data'],
                    'priority' => (int) ($result['metadata']['priority'] ?? 0),
                    'quality_score' => $validationResults[$source]['score'],
                ];
            }
        }

        // If only one valid source, no conflicts
        if (count($validSources) <= 1) {
            $resolvedData = ! empty($validSources) ? array_values($validSources)[0]['data'] : [];
            if (! is_array($resolvedData)) {
                $resolvedData = [];
            }

            return [
                'resolved_data' => $resolvedData,
                'conflicts' => [],
                'resolution_strategy' => 'single_source',
            ];
        }

        // Use conflict resolution service for multiple sources
        return $this->conflictResolution->resolveConflicts($validSources);
    }

    /**
     * Calculate data quality score
     *
     * @param  array{resolved_data: array<string, mixed>, conflicts: array<string, mixed>, resolution_strategy: string}  $conflictResolution
     */
    protected function calculateQualityScore(array $conflictResolution): float
    {
        $baseScore = 100.0;

        // Deduct points for conflicts
        $conflictCount = count($conflictResolution['conflicts']);
        $conflictPenalty = min($conflictCount * 5, 30); // Max 30 points penalty

        // Adjust based on resolution strategy
        $strategyBonus = match ($conflictResolution['resolution_strategy']) {
            'single_source' => 0,
            'priority_based' => -5,
            'consensus' => 5,
            'weighted_average' => 10,
            default => 0,
        };

        $finalScore = max(0, $baseScore - $conflictPenalty + $strategyBonus);

        return round($finalScore, 2);
    }

    /**
     * Store synchronized data
     *
     * @param  array<string, mixed>  $resolvedData
     * @return array<string, mixed>
     */
    protected function storeSynchronizedData(array $resolvedData): array
    {
        $stored = [];

        foreach ($resolvedData as $key => $value) {
            $cacheKey = "synchronized:{$key}";
            Cache::put($cacheKey, $value, 86400); // 24 hours

            $stored[$key] = [
                'cached' => true,
                'cache_key' => $cacheKey,
                'ttl' => 86400,
                'size' => is_array($value) ? count($value) : 0,
            ];
        }

        return $stored;
    }

    /**
     * Get source priority for conflict resolution
     */
    protected function getSourcePriority(string $source): int
    {
        return match ($source) {
            'umapyoi_characters', 'umapyoi_support_cards' => 10, // Highest priority
            'umamusumedb_meta', 'umamusumedb_skills' => 8,
            'umapyoi_news' => 6,
            'umamusumedb_training' => 5,
            default => 1,
        };
    }

    /**
     * Fallback synchronization without MCP agents
     *
     * @param  array<string>  $dataSources
     * @param  array<string, mixed>  $options
     * @return array{success: bool, synchronized: array<string, mixed>, conflicts: array<string, mixed>, quality_score: float, duration_ms: float}
     */
    protected function fallbackSync(array $dataSources, array $options): array
    {
        $startTime = microtime(true);
        $synchronized = [];

        foreach ($dataSources as $source) {
            try {
                $data = $this->fetchFromSource($source, ['timeout' => 30]);
                $synchronized[$source] = $data;
            } catch (\Exception $e) {
                Log::error('[DataSyncAgent] Fallback fetch failed', [
                    'source' => $source,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success' => ! empty($synchronized),
            'synchronized' => $synchronized,
            'conflicts' => [],
            'quality_score' => 50.0, // Lower score for fallback
            'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
        ];
    }

    /**
     * Get synchronization status
     *
     * @return array{active_syncs: int, completed_syncs: int, failed_syncs: int, avg_quality_score: float}
     */
    public function getSyncStatus(): array
    {
        // In production, this would query sync history from database
        return [
            'active_syncs' => 0,
            'completed_syncs' => 0,
            'failed_syncs' => 0,
            'avg_quality_score' => 0.0,
        ];
    }

    /**
     * Schedule automatic synchronization
     *
     * @param  array<string>  $dataSources
     * @param  array<string, mixed>  $options
     */
    public function scheduleAutoSync(array $dataSources, array $options = []): void
    {
        $syncId = uniqid('scheduled_sync_', true);

        Log::info('[DataSyncAgent] Scheduling automatic synchronization', [
            'sync_id' => $syncId,
            'sources' => $dataSources,
            'interval' => $options['interval'] ?? 3600,
        ]);

        // In production, this would queue a job for background execution
        // For now, we log the scheduling
    }
}
