<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Conflict Resolution Service with MCP Agent Integration
 *
 * Handles data discrepancies between multiple sources using intelligent
 * resolution strategies, priority-based merging, and consensus algorithms.
 *
 * Requirements: 14.3, 14.4, 56.3, Task 4.4.4
 */
class ConflictResolutionService
{
    /**
     * Conflict history cache key
     */
    protected const CONFLICT_HISTORY_KEY = 'conflict_history:';

    /**
     * Maximum conflict history entries
     */
    protected const MAX_HISTORY_ENTRIES = 100;

    /**
     * Conflict resolution strategies
     */
    protected const STRATEGY_PRIORITY = 'priority_based';

    protected const STRATEGY_CONSENSUS = 'consensus';

    protected const STRATEGY_WEIGHTED = 'weighted_average';

    protected const STRATEGY_LATEST = 'latest_timestamp';

    public function __construct(
        protected MCPClientService $mcpClient
    ) {}

    /**
     * Resolve conflicts between multiple data sources
     *
     * @param  array<string, array{data: mixed, priority: int, quality_score: float}>  $validSources
     * @return array{resolved_data: array<string, mixed>, conflicts: array<string, mixed>, resolution_strategy: string}
     */
    public function resolveConflicts(array $validSources): array
    {
        Log::info('[ConflictResolution] Starting conflict resolution', [
            'sources_count' => count($validSources),
        ]);

        if (count($validSources) === 0) {
            return [
                'resolved_data' => [],
                'conflicts' => [],
                'resolution_strategy' => 'no_data',
            ];
        }

        if (count($validSources) === 1) {
            $source = array_values($validSources)[0];

            return [
                'resolved_data' => $source['data'],
                'conflicts' => [],
                'resolution_strategy' => 'single_source',
            ];
        }

        // Detect conflicts
        $conflicts = $this->detectConflicts($validSources);

        // Choose resolution strategy
        $strategy = $this->chooseResolutionStrategy($validSources, $conflicts);

        // Resolve conflicts using chosen strategy
        $resolvedData = $this->applyResolutionStrategy($validSources, $conflicts, $strategy);

        // Record conflict resolution
        $this->recordConflictResolution($conflicts, $strategy);

        Log::info('[ConflictResolution] Conflict resolution completed', [
            'conflicts_detected' => count($conflicts),
            'strategy' => $strategy,
            'resolved_items' => is_array($resolvedData) ? count($resolvedData) : 0,
        ]);

        return [
            'resolved_data' => $resolvedData,
            'conflicts' => $conflicts,
            'resolution_strategy' => $strategy,
        ];
    }

    /**
     * Detect conflicts between data sources
     *
     * @param  array<string, array{data: mixed, priority: int, quality_score: float}>  $validSources
     * @return array<string, array<string, mixed>>
     */
    protected function detectConflicts(array $validSources): array
    {
        $conflicts = [];

        // Convert all sources to comparable format
        $normalizedSources = [];

        foreach ($validSources as $sourceName => $sourceData) {
            $normalizedSources[$sourceName] = $this->normalizeData($sourceData['data']);
        }

        // Compare each pair of sources
        $sourceNames = array_keys($normalizedSources);

        for ($i = 0; $i < count($sourceNames); $i++) {
            for ($j = $i + 1; $j < count($sourceNames); $j++) {
                $source1 = $sourceNames[$i];
                $source2 = $sourceNames[$j];

                $sourceConflicts = $this->compareDataSources(
                    $normalizedSources[$source1],
                    $normalizedSources[$source2],
                    $source1,
                    $source2
                );

                if (! empty($sourceConflicts)) {
                    $conflicts["{$source1}_vs_{$source2}"] = $sourceConflicts;
                }
            }
        }

        return $conflicts;
    }

    /**
     * Normalize data for comparison
     *
     * @return array<string, mixed>
     */
    protected function normalizeData(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        // If data is a list, convert to associative array by ID
        if ($this->isSequentialArray($data)) {
            $normalized = [];

            foreach ($data as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $normalized[$item['id']] = $item;
                }
            }

            return $normalized;
        }

        return $data;
    }

    /**
     * Compare two data sources
     *
     * @param  array<string, mixed>  $data1
     * @param  array<string, mixed>  $data2
     * @return array<string, array<string, mixed>>
     */
    protected function compareDataSources(array $data1, array $data2, string $source1, string $source2): array
    {
        $conflicts = [];

        // Find items present in both sources
        $commonKeys = array_intersect(array_keys($data1), array_keys($data2));

        foreach ($commonKeys as $key) {
            $item1 = $data1[$key];
            $item2 = $data2[$key];

            // Compare items
            $itemConflicts = $this->compareItems($item1, $item2, $key);

            if (! empty($itemConflicts)) {
                $conflicts[$key] = [
                    'key' => $key,
                    'source1' => $source1,
                    'source2' => $source2,
                    'differences' => $itemConflicts,
                ];
            }
        }

        // Find items only in source1
        $onlyInSource1 = array_diff(array_keys($data1), array_keys($data2));

        if (! empty($onlyInSource1)) {
            $conflicts['missing_in_'.$source2] = [
                'type' => 'missing_items',
                'source' => $source2,
                'missing_keys' => $onlyInSource1,
            ];
        }

        // Find items only in source2
        $onlyInSource2 = array_diff(array_keys($data2), array_keys($data1));

        if (! empty($onlyInSource2)) {
            $conflicts['missing_in_'.$source1] = [
                'type' => 'missing_items',
                'source' => $source1,
                'missing_keys' => $onlyInSource2,
            ];
        }

        return $conflicts;
    }

    /**
     * Compare two items
     *
     * @return array<string, array<string, mixed>>
     */
    protected function compareItems(mixed $item1, mixed $item2, string $key): array
    {
        $differences = [];

        // If both are arrays, compare fields
        if (is_array($item1) && is_array($item2)) {
            $allFields = array_unique(array_merge(array_keys($item1), array_keys($item2)));

            foreach ($allFields as $field) {
                $value1 = $item1[$field] ?? null;
                $value2 = $item2[$field] ?? null;

                if ($value1 !== $value2) {
                    $differences[$field] = [
                        'field' => $field,
                        'value1' => $value1,
                        'value2' => $value2,
                        'type' => 'value_mismatch',
                    ];
                }
            }
        } elseif ($item1 !== $item2) {
            // Simple value comparison
            $differences['value'] = [
                'field' => 'value',
                'value1' => $item1,
                'value2' => $item2,
                'type' => 'value_mismatch',
            ];
        }

        return $differences;
    }

    /**
     * Choose resolution strategy based on sources and conflicts
     *
     * @param  array<string, array{data: mixed, priority: int, quality_score: float}>  $validSources
     * @param  array<string, array<string, mixed>>  $conflicts
     */
    protected function chooseResolutionStrategy(array $validSources, array $conflicts): string
    {
        // If no conflicts, use priority-based
        if (empty($conflicts)) {
            return self::STRATEGY_PRIORITY;
        }

        // Check if MCP agent is available for intelligent strategy selection
        if ($this->mcpClient->isServerEnabled('strands-agents')) {
            // In production, this would use MCP agent to analyze conflicts
            // and recommend optimal strategy
            return $this->mcpRecommendStrategy($validSources, $conflicts);
        }

        // Fallback strategy selection
        $conflictCount = count($conflicts);
        $sourceCount = count($validSources);

        // If many conflicts, use consensus
        if ($conflictCount > 10 && $sourceCount >= 3) {
            return self::STRATEGY_CONSENSUS;
        }

        // If quality scores vary significantly, use weighted average
        $qualityScores = array_column($validSources, 'quality_score');
        $qualityVariance = $this->calculateVariance($qualityScores);

        if ($qualityVariance > 100) {
            return self::STRATEGY_WEIGHTED;
        }

        // Default to priority-based
        return self::STRATEGY_PRIORITY;
    }

    /**
     * Apply resolution strategy
     *
     * @param  array<string, array{data: mixed, priority: int, quality_score: float}>  $validSources
     * @param  array<string, array<string, mixed>>  $conflicts
     * @return array<string, mixed>
     */
    protected function applyResolutionStrategy(array $validSources, array $conflicts, string $strategy): array
    {
        return match ($strategy) {
            self::STRATEGY_PRIORITY => $this->resolvePriorityBased($validSources),
            self::STRATEGY_CONSENSUS => $this->resolveConsensus($validSources),
            self::STRATEGY_WEIGHTED => $this->resolveWeightedAverage($validSources),
            self::STRATEGY_LATEST => $this->resolveLatestTimestamp($validSources),
            default => $this->resolvePriorityBased($validSources),
        };
    }

    /**
     * Resolve using priority-based strategy
     *
     * @param  array<string, array{data: mixed, priority: int, quality_score: float}>  $validSources
     * @return array<string, mixed>
     */
    protected function resolvePriorityBased(array $validSources): array
    {
        // Sort sources by priority (highest first)
        uasort($validSources, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        // Return data from highest priority source
        $highestPriority = array_values($validSources)[0];

        return $highestPriority['data'];
    }

    /**
     * Resolve using consensus strategy
     *
     * @param  array<string, array{data: mixed, priority: int, quality_score: float}>  $validSources
     * @return array<string, mixed>
     */
    protected function resolveConsensus(array $validSources): array
    {
        // Normalize all sources
        $normalizedSources = [];

        foreach ($validSources as $sourceName => $sourceData) {
            $normalizedSources[$sourceName] = $this->normalizeData($sourceData['data']);
        }

        // Find consensus values
        $consensusData = [];
        $allKeys = [];

        foreach ($normalizedSources as $source) {
            $allKeys = array_merge($allKeys, array_keys($source));
        }

        $allKeys = array_unique($allKeys);

        foreach ($allKeys as $key) {
            $values = [];

            foreach ($normalizedSources as $source) {
                if (isset($source[$key])) {
                    $values[] = $source[$key];
                }
            }

            // Use majority value or first value if no consensus
            $consensusData[$key] = $this->findConsensusValue($values);
        }

        return $consensusData;
    }

    /**
     * Resolve using weighted average strategy
     *
     * @param  array<string, array{data: mixed, priority: int, quality_score: float}>  $validSources
     * @return array<string, mixed>
     */
    protected function resolveWeightedAverage(array $validSources): array
    {
        // Calculate weights based on quality scores
        $totalQuality = array_sum(array_column($validSources, 'quality_score'));

        $weights = [];

        foreach ($validSources as $sourceName => $sourceData) {
            $weights[$sourceName] = $totalQuality > 0 ? $sourceData['quality_score'] / $totalQuality : 1 / count($validSources);
        }

        // Normalize all sources
        $normalizedSources = [];

        foreach ($validSources as $sourceName => $sourceData) {
            $normalizedSources[$sourceName] = $this->normalizeData($sourceData['data']);
        }

        // Apply weighted merge
        $mergedData = [];
        $allKeys = [];

        foreach ($normalizedSources as $source) {
            $allKeys = array_merge($allKeys, array_keys($source));
        }

        $allKeys = array_unique($allKeys);

        foreach ($allKeys as $key) {
            $weightedValues = [];

            foreach ($normalizedSources as $sourceName => $source) {
                if (isset($source[$key])) {
                    $weightedValues[] = [
                        'value' => $source[$key],
                        'weight' => $weights[$sourceName],
                    ];
                }
            }

            // Use highest weighted value
            usort($weightedValues, fn ($a, $b) => $b['weight'] <=> $a['weight']);
            $mergedData[$key] = $weightedValues[0]['value'];
        }

        return $mergedData;
    }

    /**
     * Resolve using latest timestamp strategy
     *
     * @param  array<string, array{data: mixed, priority: int, quality_score: float}>  $validSources
     * @return array<string, mixed>
     */
    protected function resolveLatestTimestamp(array $validSources): array
    {
        // Find source with latest timestamp
        $latestSource = null;
        $latestTimestamp = null;

        foreach ($validSources as $sourceName => $sourceData) {
            $data = $sourceData['data'];

            if (is_array($data) && isset($data['timestamp'])) {
                $timestamp = \Carbon\Carbon::parse($data['timestamp']);

                if ($latestTimestamp === null || $timestamp->gt($latestTimestamp)) {
                    $latestTimestamp = $timestamp;
                    $latestSource = $sourceData;
                }
            }
        }

        // If no timestamp found, use priority-based
        if ($latestSource === null) {
            return $this->resolvePriorityBased($validSources);
        }

        return $latestSource['data'];
    }

    /**
     * Find consensus value from multiple values
     *
     * @param  array<mixed>  $values
     */
    protected function findConsensusValue(array $values): mixed
    {
        if (empty($values)) {
            return null;
        }

        // Count occurrences of each value
        $valueCounts = [];

        foreach ($values as $value) {
            $key = json_encode($value);

            if (! isset($valueCounts[$key])) {
                $valueCounts[$key] = ['value' => $value, 'count' => 0];
            }

            $valueCounts[$key]['count']++;
        }

        // Find most common value
        uasort($valueCounts, fn ($a, $b) => $b['count'] <=> $a['count']);

        return array_values($valueCounts)[0]['value'];
    }

    /**
     * MCP-based strategy recommendation
     *
     * @param  array<string, array{data: mixed, priority: int, quality_score: float}>  $validSources
     * @param  array<string, array<string, mixed>>  $conflicts
     */
    protected function mcpRecommendStrategy(array $validSources, array $conflicts): string
    {
        // In production, this would use MCP agent to analyze and recommend
        // For now, use heuristics

        $conflictCount = count($conflicts);
        $sourceCount = count($validSources);
        $avgQuality = array_sum(array_column($validSources, 'quality_score')) / $sourceCount;

        if ($avgQuality > 90 && $conflictCount < 5) {
            return self::STRATEGY_CONSENSUS;
        }

        if ($conflictCount > 20) {
            return self::STRATEGY_WEIGHTED;
        }

        return self::STRATEGY_PRIORITY;
    }

    /**
     * Record conflict resolution
     *
     * @param  array<string, array<string, mixed>>  $conflicts
     */
    protected function recordConflictResolution(array $conflicts, string $strategy): void
    {
        $record = [
            'conflicts_count' => count($conflicts),
            'strategy' => $strategy,
            'timestamp' => now()->toIso8601String(),
        ];

        // Store in cache
        $historyKey = self::CONFLICT_HISTORY_KEY.'latest';
        Cache::put($historyKey, $record, 86400);

        Log::debug('[ConflictResolution] Conflict resolution recorded', $record);
    }

    /**
     * Calculate variance of values
     *
     * @param  array<float>  $values
     */
    protected function calculateVariance(array $values): float
    {
        if (empty($values)) {
            return 0.0;
        }

        $mean = array_sum($values) / count($values);
        $squaredDiffs = array_map(fn ($value) => ($value - $mean) ** 2, $values);

        return array_sum($squaredDiffs) / count($values);
    }

    /**
     * Check if array is sequential
     *
     * @param  array<mixed>  $array
     */
    protected function isSequentialArray(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Get conflict resolution statistics
     *
     * @return array{total_resolutions: int, strategies_used: array<string, int>, avg_conflicts_per_resolution: float}
     */
    public function getResolutionStatistics(): array
    {
        // In production, this would query resolution history from database
        return [
            'total_resolutions' => 0,
            'strategies_used' => [
                self::STRATEGY_PRIORITY => 0,
                self::STRATEGY_CONSENSUS => 0,
                self::STRATEGY_WEIGHTED => 0,
                self::STRATEGY_LATEST => 0,
            ],
            'avg_conflicts_per_resolution' => 0.0,
        ];
    }
}
