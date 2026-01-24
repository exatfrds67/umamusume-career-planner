<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Conflict Detection Service for External API Integration
 *
 * Detects field-level conflicts between data from multiple API sources,
 * calculates confidence scores, provides multiple resolution strategies,
 * and logs conflicts for auditing and debugging.
 *
 * Features:
 * - Field-level conflict detection between data sources
 * - Confidence scoring based on source priority and data freshness
 * - Multiple resolution strategies (priority, newest, manual, merge)
 * - Comprehensive conflict logging for debugging and monitoring
 *
 * Requirements: 14.3, 14.4 (Data Reconciliation and Conflict Detection)
 * Task: 3.2.2
 */
class ConflictDetectionService
{
    /**
     * Source confidence scores based on priority
     * Higher values indicate more reliable sources
     */
    public const SOURCE_CONFIDENCE = [
        'umapyoi' => 0.95,
        'umamusumedb' => 0.85,
        'umalator' => 0.80,
        'cache' => 0.70,
        'manual' => 1.00,
        'unknown' => 0.50,
    ];

    /**
     * Resolution strategies
     */
    public const STRATEGY_HIGHEST_CONFIDENCE = 'highest_confidence';

    public const STRATEGY_NEWEST = 'newest';

    public const STRATEGY_MANUAL = 'manual';

    public const STRATEGY_MERGE = 'merge';

    public const STRATEGY_PRIORITY = 'priority';

    /**
     * Conflict severity levels
     */
    public const SEVERITY_CRITICAL = 'critical';

    public const SEVERITY_HIGH = 'high';

    public const SEVERITY_MEDIUM = 'medium';

    public const SEVERITY_LOW = 'low';

    /**
     * Cache key prefix for conflict logs
     */
    private const CONFLICT_LOG_KEY = 'conflict_log:';

    /**
     * Maximum conflict log entries to retain
     */
    private const MAX_LOG_ENTRIES = 500;

    /**
     * Conflict history for current session
     *
     * @var array<int, array<string, mixed>>
     */
    private array $conflictHistory = [];

    /**
     * Detect conflicts between multiple data sources
     *
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     * @return array{has_conflicts: bool, conflicts: array<string, array<string, mixed>>, summary: array<string, mixed>}
     */
    public function detectConflicts(array $sources): array
    {
        $startTime = microtime(true);

        Log::info('[ConflictDetection] Starting conflict detection', [
            'sources_count' => \count($sources),
            'source_names' => array_keys($sources),
        ]);

        if (\count($sources) < 2) {
            return [
                'has_conflicts' => false,
                'conflicts' => [],
                'summary' => [
                    'total_conflicts' => 0,
                    'sources_compared' => \count($sources),
                    'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
                ],
            ];
        }

        $conflicts = [];
        $sourceNames = array_keys($sources);

        // Compare each pair of sources
        for ($i = 0; $i < \count($sourceNames); $i++) {
            for ($j = $i + 1; $j < \count($sourceNames); $j++) {
                $source1Name = $sourceNames[$i];
                $source2Name = $sourceNames[$j];

                $pairConflicts = $this->compareSourcePair(
                    $source1Name,
                    $sources[$source1Name],
                    $source2Name,
                    $sources[$source2Name]
                );

                if (! empty($pairConflicts)) {
                    $conflicts["{$source1Name}_vs_{$source2Name}"] = $pairConflicts;
                }
            }
        }

        $duration = (microtime(true) - $startTime) * 1000;
        $hasConflicts = ! empty($conflicts);

        // Log conflicts if found
        if ($hasConflicts) {
            $this->logConflicts($conflicts, $sources);
        }

        $summary = $this->generateConflictSummary($conflicts, $sources, $duration);

        Log::info('[ConflictDetection] Conflict detection completed', [
            'has_conflicts' => $hasConflicts,
            'total_conflicts' => $summary['total_conflicts'],
            'duration_ms' => round($duration, 2),
        ]);

        return [
            'has_conflicts' => $hasConflicts,
            'conflicts' => $conflicts,
            'summary' => $summary,
        ];
    }

    /**
     * Compare two data sources and detect field-level conflicts
     *
     * @param  array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}  $source1Data
     * @param  array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}  $source2Data
     * @return array<string, array<string, mixed>>
     */
    protected function compareSourcePair(string $source1Name, array $source1Data, string $source2Name, array $source2Data): array
    {
        $conflicts = [];

        $data1 = $this->normalizeData($source1Data['data'] ?? []);
        $data2 = $this->normalizeData($source2Data['data'] ?? []);

        // Handle list data (arrays of items with IDs)
        if ($this->isListData($data1) && $this->isListData($data2)) {
            $conflicts = $this->compareListData($source1Name, $data1, $source2Name, $data2);
        } else {
            // Handle single record data
            $conflicts = $this->compareSingleRecord($source1Name, $data1, $source2Name, $data2);
        }

        // Add confidence scores to each conflict
        foreach ($conflicts as $key => &$conflict) {
            $conflict['source1_confidence'] = $this->calculateConfidence($source1Name, $source1Data);
            $conflict['source2_confidence'] = $this->calculateConfidence($source2Name, $source2Data);
            $conflict['severity'] = $this->determineSeverity($conflict);
            $conflict['recommended_resolution'] = $this->recommendResolution($conflict);
        }

        return $conflicts;
    }

    /**
     * Compare list data (arrays of items)
     *
     * @param  array<int|string, mixed>  $data1
     * @param  array<int|string, mixed>  $data2
     * @return array<string, array<string, mixed>>
     */
    protected function compareListData(string $source1Name, array $data1, string $source2Name, array $data2): array
    {
        $conflicts = [];

        // Index data by ID for comparison
        $indexed1 = $this->indexById($data1);
        $indexed2 = $this->indexById($data2);

        // Find common IDs
        $commonIds = array_intersect(array_keys($indexed1), array_keys($indexed2));

        foreach ($commonIds as $id) {
            $item1 = $indexed1[$id];
            $item2 = $indexed2[$id];

            $itemConflicts = $this->compareFields($item1, $item2);

            if (! empty($itemConflicts)) {
                $conflicts["item_{$id}"] = [
                    'type' => 'field_mismatch',
                    'item_id' => $id,
                    'source1' => $source1Name,
                    'source2' => $source2Name,
                    'fields' => $itemConflicts,
                    'source1_data' => $item1,
                    'source2_data' => $item2,
                ];
            }
        }

        // Track missing items
        $onlyIn1 = array_diff(array_keys($indexed1), array_keys($indexed2));
        $onlyIn2 = array_diff(array_keys($indexed2), array_keys($indexed1));

        if (! empty($onlyIn1)) {
            $conflicts['missing_in_'.$source2Name] = [
                'type' => 'missing_items',
                'source' => $source2Name,
                'missing_ids' => array_values($onlyIn1),
                'count' => \count($onlyIn1),
            ];
        }

        if (! empty($onlyIn2)) {
            $conflicts['missing_in_'.$source1Name] = [
                'type' => 'missing_items',
                'source' => $source1Name,
                'missing_ids' => array_values($onlyIn2),
                'count' => \count($onlyIn2),
            ];
        }

        return $conflicts;
    }

    /**
     * Compare single record data
     *
     * @param  array<string, mixed>  $data1
     * @param  array<string, mixed>  $data2
     * @return array<string, array<string, mixed>>
     */
    protected function compareSingleRecord(string $source1Name, array $data1, string $source2Name, array $data2): array
    {
        $conflicts = [];
        $fieldConflicts = $this->compareFields($data1, $data2);

        if (! empty($fieldConflicts)) {
            $conflicts['record'] = [
                'type' => 'field_mismatch',
                'source1' => $source1Name,
                'source2' => $source2Name,
                'fields' => $fieldConflicts,
                'source1_data' => $data1,
                'source2_data' => $data2,
            ];
        }

        return $conflicts;
    }

    /**
     * Compare fields between two data items
     *
     * @param  array<string, mixed>  $item1
     * @param  array<string, mixed>  $item2
     * @return array<string, array{value1: mixed, value2: mixed, type: string}>
     */
    protected function compareFields(array $item1, array $item2): array
    {
        $conflicts = [];
        $allFields = array_unique(array_merge(array_keys($item1), array_keys($item2)));

        foreach ($allFields as $field) {
            // Skip metadata fields
            if (! is_string($field) || \str_starts_with($field, '_')) {
                continue;
            }

            $value1 = $item1[$field] ?? null;
            $value2 = $item2[$field] ?? null;

            if (! $this->valuesAreEqual($value1, $value2)) {
                $conflicts[$field] = [
                    'value1' => $value1,
                    'value2' => $value2,
                    'type' => $this->determineConflictType($value1, $value2),
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Check if two values are equal (with type coercion for numeric strings)
     */
    protected function valuesAreEqual(mixed $value1, mixed $value2): bool
    {
        // Handle null comparisons
        if ($value1 === null && $value2 === null) {
            return true;
        }

        if ($value1 === null || $value2 === null) {
            return false;
        }

        // Handle numeric string comparisons
        if (\is_numeric($value1) && \is_numeric($value2)) {
            return (float) $value1 === (float) $value2;
        }

        // Handle array comparisons
        if (\is_array($value1) && \is_array($value2)) {
            return $this->arraysAreEqual($value1, $value2);
        }

        // Strict comparison for other types
        return $value1 === $value2;
    }

    /**
     * Check if two arrays are equal
     *
     * @param  array<mixed>  $arr1
     * @param  array<mixed>  $arr2
     */
    protected function arraysAreEqual(array $arr1, array $arr2): bool
    {
        if (\count($arr1) !== \count($arr2)) {
            return false;
        }

        foreach ($arr1 as $key => $value) {
            if (! \array_key_exists($key, $arr2)) {
                return false;
            }

            if (! $this->valuesAreEqual($value, $arr2[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine the type of conflict
     */
    protected function determineConflictType(mixed $value1, mixed $value2): string
    {
        if ($value1 === null || $value2 === null) {
            return 'missing_value';
        }

        if (\gettype($value1) !== \gettype($value2)) {
            return 'type_mismatch';
        }

        if (\is_numeric($value1) && \is_numeric($value2)) {
            return 'numeric_difference';
        }

        if (\is_string($value1) && \is_string($value2)) {
            return 'string_difference';
        }

        if (\is_array($value1) && \is_array($value2)) {
            return 'array_difference';
        }

        return 'value_mismatch';
    }

    /**
     * Calculate confidence score for a data source
     *
     * @param  array{data?: mixed, source?: string, timestamp?: string, metadata?: array<string, mixed>}  $sourceData
     */
    public function calculateConfidence(string $sourceName, array $sourceData): float
    {
        // Base confidence from source priority
        $baseConfidence = self::SOURCE_CONFIDENCE[$sourceName] ?? self::SOURCE_CONFIDENCE['unknown'];

        // Adjust for data freshness if timestamp available
        $freshnessModifier = 1.0;
        if (isset($sourceData['timestamp'])) {
            $freshnessModifier = $this->calculateFreshnessModifier($sourceData['timestamp']);
        }

        // Adjust for data completeness
        $completenessModifier = 1.0;
        $metadata = $sourceData['metadata'] ?? [];
        if (is_array($metadata) && isset($metadata['completeness']) && is_numeric($metadata['completeness'])) {
            $completenessModifier = (float) $metadata['completeness'] / 100;
        }

        // Calculate final confidence (capped at 1.0)
        $confidence = $baseConfidence * $freshnessModifier * $completenessModifier;

        return \min(1.0, \max(0.0, round($confidence, 4)));
    }

    /**
     * Calculate freshness modifier based on data age
     */
    protected function calculateFreshnessModifier(string $timestamp): float
    {
        try {
            $dataTime = \Carbon\Carbon::parse($timestamp);
            $ageHours = $dataTime->diffInHours(now());

            // Data older than 48 hours starts losing confidence
            if ($ageHours <= 24) {
                return 1.0;
            }

            if ($ageHours <= 48) {
                return 0.95;
            }

            if ($ageHours <= 168) { // 1 week
                return 0.85;
            }

            if ($ageHours <= 720) { // 30 days
                return 0.70;
            }

            return 0.50;
        } catch (\Exception $e) {
            return 1.0; // Default to full freshness if timestamp parsing fails
        }
    }

    /**
     * Determine conflict severity based on field importance and difference magnitude
     *
     * @param  array<string, mixed>  $conflict
     */
    protected function determineSeverity(array $conflict): string
    {
        $type = $conflict['type'] ?? 'unknown';

        // Missing items are high severity
        if ($type === 'missing_items') {
            $count = $conflict['count'] ?? 0;

            return $count > 10 ? self::SEVERITY_CRITICAL : self::SEVERITY_HIGH;
        }

        // Field mismatches depend on the fields involved
        $fields = $conflict['fields'] ?? null;
        if ($type === 'field_mismatch' && is_array($fields)) {
            $criticalFields = ['id', 'name', 'rarity', 'stats'];
            $highFields = ['speed', 'stamina', 'power', 'guts', 'wit', 'sp_cost'];

            foreach ($fields as $field => $details) {
                if (is_string($field) && in_array($field, $criticalFields, true)) {
                    return self::SEVERITY_CRITICAL;
                }

                if (is_string($field) && in_array($field, $highFields, true)) {
                    return self::SEVERITY_HIGH;
                }
            }

            return count($fields) > 5 ? self::SEVERITY_HIGH : self::SEVERITY_MEDIUM;
        }

        return self::SEVERITY_LOW;
    }

    /**
     * Recommend a resolution strategy based on conflict characteristics
     *
     * @param  array<string, mixed>  $conflict
     */
    protected function recommendResolution(array $conflict): string
    {
        $source1Confidence = is_numeric($conflict['source1_confidence'] ?? null) ? (float) $conflict['source1_confidence'] : 0.5;
        $source2Confidence = is_numeric($conflict['source2_confidence'] ?? null) ? (float) $conflict['source2_confidence'] : 0.5;
        $severity = $conflict['severity'] ?? self::SEVERITY_LOW;

        // Critical conflicts should be manually reviewed
        if ($severity === self::SEVERITY_CRITICAL) {
            return self::STRATEGY_MANUAL;
        }

        // Large confidence difference - use highest confidence
        $confidenceDiff = \abs($source1Confidence - $source2Confidence);
        if ($confidenceDiff > 0.15) {
            return self::STRATEGY_HIGHEST_CONFIDENCE;
        }

        // Similar confidence - use priority-based
        if ($confidenceDiff <= 0.05) {
            return self::STRATEGY_PRIORITY;
        }

        // Default to highest confidence
        return self::STRATEGY_HIGHEST_CONFIDENCE;
    }

    /**
     * Resolve conflicts using the specified strategy
     *
     * @param  array<string, array<string, mixed>>  $conflicts
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     * @return array{resolved_data: mixed, resolution_log: array<string, mixed>}
     */
    public function resolveConflicts(array $conflicts, array $sources, string $strategy = self::STRATEGY_HIGHEST_CONFIDENCE): array
    {
        Log::info('[ConflictDetection] Resolving conflicts', [
            'conflict_count' => \count($conflicts),
            'strategy' => $strategy,
        ]);

        $resolutionLog = [];
        /** @var array<string, mixed> $resolvedData */
        $resolvedData = [];

        // Get source with highest confidence as base
        $primarySource = $this->selectPrimarySource($sources, $strategy);
        $primaryData = $sources[$primarySource]['data'] ?? [];
        $resolvedData = is_array($primaryData) ? $primaryData : [];

        // Conflicts are structured as: pair_key => array of conflicts
        foreach ($conflicts as $pairKey => $pairConflicts) {
            if (! is_array($pairConflicts)) {
                continue;
            }
            // Handle both nested and flat conflict structures
            if (isset($pairConflicts['type'])) {
                // Flat structure - single conflict
                /** @var array<string, mixed> $flatConflict */
                $flatConflict = $pairConflicts;
                $resolution = $this->resolveConflict($flatConflict, $sources, $strategy);
                $resolutionLog[$pairKey] = $resolution;
            } else {
                // Nested structure - multiple conflicts per pair
                foreach ($pairConflicts as $conflictKey => $conflict) {
                    if (! is_array($conflict)) {
                        continue;
                    }
                    /** @var array<string, mixed> $typedConflict */
                    $typedConflict = $conflict;
                    $resolution = $this->resolveConflict($typedConflict, $sources, $strategy);
                    $resolutionLog["{$pairKey}:{$conflictKey}"] = $resolution;

                    // Apply resolution to data
                    if (isset($resolution['resolved_value']) && isset($resolution['field'])) {
                        $this->applyResolution($resolvedData, $resolution);
                    }
                }
            }
        }

        Log::info('[ConflictDetection] Conflicts resolved', [
            'resolutions_applied' => \count($resolutionLog),
            'primary_source' => $primarySource,
        ]);

        return [
            'resolved_data' => $resolvedData,
            'resolution_log' => $resolutionLog,
            'primary_source' => $primarySource,
            'strategy_used' => $strategy,
        ];
    }

    /**
     * Select primary source based on strategy
     *
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     */
    protected function selectPrimarySource(array $sources, string $strategy): string
    {
        $sourceConfidences = [];

        foreach ($sources as $name => $data) {
            $sourceConfidences[$name] = $this->calculateConfidence($name, $data);
        }

        // Handle empty sources - return first key or empty string
        if ($sourceConfidences === []) {
            $keys = array_keys($sources);

            return $keys[0] ?? '';
        }

        $maxConfidence = max($sourceConfidences);
        $defaultSource = array_keys($sourceConfidences, $maxConfidence)[0];

        return match ($strategy) {
            self::STRATEGY_HIGHEST_CONFIDENCE => $defaultSource,
            self::STRATEGY_NEWEST => $this->selectNewestSource($sources),
            self::STRATEGY_PRIORITY => $this->selectPrioritySource($sources),
            default => $defaultSource,
        };
    }

    /**
     * Select source with newest timestamp
     *
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     */
    protected function selectNewestSource(array $sources): string
    {
        $newest = null;
        $newestTime = null;

        foreach ($sources as $name => $data) {
            $timestampValue = is_array($data) && isset($data['timestamp']) ? $data['timestamp'] : null;
            if ($timestampValue !== null) {
                $time = \Carbon\Carbon::parse($timestampValue);
                if ($newestTime === null || $time->gt($newestTime)) {
                    $newestTime = $time;
                    $newest = $name;
                }
            }
        }

        if ($newest !== null) {
            return $newest;
        }

        $firstKey = array_key_first($sources);

        return is_string($firstKey) ? $firstKey : '';
    }

    /**
     * Select source based on priority ordering
     *
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     */
    protected function selectPrioritySource(array $sources): string
    {
        $priorityOrder = ['umapyoi', 'umamusumedb', 'umalator', 'cache'];

        foreach ($priorityOrder as $source) {
            if (isset($sources[$source])) {
                return $source;
            }
        }

        $firstKey = array_key_first($sources);

        return is_string($firstKey) ? $firstKey : '';
    }

    /**
     * Resolve a single conflict
     *
     * @param  array<string, mixed>  $conflict
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     * @return array<string, mixed>
     */
    protected function resolveConflict(array $conflict, array $sources, string $strategy): array
    {
        $conflictType = $conflict['type'] ?? 'unknown';
        $conflictType = is_string($conflictType) ? $conflictType : 'unknown';

        $resolution = [
            'conflict_type' => $conflictType,
            'strategy_used' => $strategy,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($conflictType === 'missing_items') {
            $resolution['action'] = 'include_from_available_source';
            $resolution['note'] = 'Missing items will be included from the source that has them';

            return $resolution;
        }

        $conflictFields = $conflict['fields'] ?? null;
        if ($conflictType === 'field_mismatch' && is_array($conflictFields)) {
            $source1 = isset($conflict['source1']) && is_string($conflict['source1']) ? $conflict['source1'] : '';
            $source2 = isset($conflict['source2']) && is_string($conflict['source2']) ? $conflict['source2'] : '';
            $source1Confidence = is_numeric($conflict['source1_confidence'] ?? null) ? (float) $conflict['source1_confidence'] : 0.5;
            $source2Confidence = is_numeric($conflict['source2_confidence'] ?? null) ? (float) $conflict['source2_confidence'] : 0.5;

            // Build typed source arrays for method calls
            /** @var array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}> $sourcePair */
            $sourcePair = [];
            if ($source1 !== '' && isset($sources[$source1])) {
                $sourcePair[$source1] = $sources[$source1];
            }
            if ($source2 !== '' && isset($sources[$source2])) {
                $sourcePair[$source2] = $sources[$source2];
            }

            $selectedSource = match ($strategy) {
                self::STRATEGY_HIGHEST_CONFIDENCE => $source1Confidence >= $source2Confidence ? $source1 : $source2,
                self::STRATEGY_NEWEST => $sourcePair !== [] ? $this->selectNewestSource($sourcePair) : $source1,
                self::STRATEGY_PRIORITY => $sourcePair !== [] ? $this->selectPrioritySource($sourcePair) : $source1,
                default => $source1Confidence >= $source2Confidence ? $source1 : $source2,
            };

            $resolution['selected_source'] = $selectedSource;
            $resolution['source1_confidence'] = $source1Confidence;
            $resolution['source2_confidence'] = $source2Confidence;
            $resolution['fields_resolved'] = array_keys($conflictFields);
        }

        return $resolution;
    }

    /**
     * Apply resolution to data
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $resolution
     */
    protected function applyResolution(array &$data, array $resolution): void
    {
        $field = $resolution['field'] ?? null;
        if (is_string($field) && isset($resolution['resolved_value'])) {
            $data[$field] = $resolution['resolved_value'];
        }
    }

    /**
     * Log conflicts for auditing and debugging
     *
     * @param  array<string, array<string, mixed>>  $conflicts
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     */
    protected function logConflicts(array $conflicts, array $sources): void
    {
        $logEntry = [
            'timestamp' => now()->toIso8601String(),
            'sources' => array_keys($sources),
            'conflict_count' => \count($conflicts),
            'conflicts' => [],
        ];

        foreach ($conflicts as $key => $conflict) {
            $logEntry['conflicts'][$key] = [
                'type' => $conflict['type'] ?? 'unknown',
                'severity' => $conflict['severity'] ?? self::SEVERITY_LOW,
                'source1_confidence' => $conflict['source1_confidence'] ?? null,
                'source2_confidence' => $conflict['source2_confidence'] ?? null,
                'recommended_resolution' => $conflict['recommended_resolution'] ?? null,
            ];

            // Add field details for field mismatches
            $conflictFields = $conflict['fields'] ?? null;
            if (is_array($conflictFields)) {
                $logEntry['conflicts'][$key]['affected_fields'] = array_keys($conflictFields);
            }
        }

        // Store in memory history
        $this->conflictHistory[] = $logEntry;
        if (\count($this->conflictHistory) > self::MAX_LOG_ENTRIES) {
            \array_shift($this->conflictHistory);
        }

        // Store in cache for persistence
        $cacheKey = self::CONFLICT_LOG_KEY.now()->format('Y-m-d');
        /** @var array<int, array<string, mixed>> $existingLogs */
        $existingLogs = Cache::get($cacheKey, []);
        if (! is_array($existingLogs)) {
            $existingLogs = [];
        }
        $existingLogs[] = $logEntry;

        // Keep only last MAX_LOG_ENTRIES per day
        if (\count($existingLogs) > self::MAX_LOG_ENTRIES) {
            $existingLogs = \array_slice($existingLogs, -self::MAX_LOG_ENTRIES);
        }

        Cache::put($cacheKey, $existingLogs, 86400 * 7); // Keep for 7 days

        Log::warning('[ConflictDetection] Conflicts detected and logged', [
            'conflict_count' => \count($conflicts),
            'sources' => array_keys($sources),
            'severities' => array_column($logEntry['conflicts'], 'severity'),
        ]);
    }

    /**
     * Generate conflict summary
     *
     * @param  array<string, array<string, mixed>>  $conflicts
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     * @return array<string, mixed>
     */
    protected function generateConflictSummary(array $conflicts, array $sources, float $durationMs): array
    {
        $severityCounts = [
            self::SEVERITY_CRITICAL => 0,
            self::SEVERITY_HIGH => 0,
            self::SEVERITY_MEDIUM => 0,
            self::SEVERITY_LOW => 0,
        ];

        /** @var array<string, int> $typeCounts */
        $typeCounts = [];
        /** @var array<int, string> $affectedFields */
        $affectedFields = [];

        foreach ($conflicts as $conflict) {
            $severity = $conflict['severity'] ?? self::SEVERITY_LOW;
            $severity = is_string($severity) ? $severity : self::SEVERITY_LOW;
            if (isset($severityCounts[$severity])) {
                $severityCounts[$severity]++;
            }

            $type = $conflict['type'] ?? 'unknown';
            $type = is_string($type) ? $type : 'unknown';
            $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;

            $conflictFields = $conflict['fields'] ?? null;
            if (is_array($conflictFields)) {
                foreach (array_keys($conflictFields) as $fieldKey) {
                    if (is_string($fieldKey)) {
                        $affectedFields[] = $fieldKey;
                    }
                }
            }
        }

        return [
            'total_conflicts' => \count($conflicts),
            'sources_compared' => \count($sources),
            'severity_breakdown' => $severityCounts,
            'type_breakdown' => $typeCounts,
            'affected_fields' => array_unique($affectedFields),
            'duration_ms' => round($durationMs, 2),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get conflict history
     *
     * @return array<int, array<string, mixed>>
     */
    public function getConflictHistory(int $limit = 100): array
    {
        return \array_slice($this->conflictHistory, -$limit);
    }

    /**
     * Get conflict logs from cache for a specific date
     *
     * @return array<int, array<string, mixed>>
     */
    public function getConflictLogs(?string $date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');
        $cacheKey = self::CONFLICT_LOG_KEY.$date;
        /** @var array<int, array<string, mixed>> $logs */
        $logs = Cache::get($cacheKey, []);

        return is_array($logs) ? $logs : [];
    }

    /**
     * Get conflict statistics
     *
     * @return array{total: int, by_severity: array<string, int>, by_type: array<string, int>, avg_per_detection: float}
     */
    public function getConflictStatistics(): array
    {
        $total = 0;
        $bySeverity = [
            self::SEVERITY_CRITICAL => 0,
            self::SEVERITY_HIGH => 0,
            self::SEVERITY_MEDIUM => 0,
            self::SEVERITY_LOW => 0,
        ];
        /** @var array<string, int> $byType */
        $byType = [];

        foreach ($this->conflictHistory as $entry) {
            $conflictCount = $entry['conflict_count'] ?? 0;
            $total += is_int($conflictCount) ? $conflictCount : 0;

            $entryConflicts = $entry['conflicts'] ?? null;
            if (is_array($entryConflicts)) {
                foreach ($entryConflicts as $conflict) {
                    if (! is_array($conflict)) {
                        continue;
                    }
                    $severity = $conflict['severity'] ?? self::SEVERITY_LOW;
                    $severity = is_string($severity) ? $severity : self::SEVERITY_LOW;
                    if (isset($bySeverity[$severity])) {
                        $bySeverity[$severity]++;
                    }

                    $type = $conflict['type'] ?? 'unknown';
                    $type = is_string($type) ? $type : 'unknown';
                    $byType[$type] = ($byType[$type] ?? 0) + 1;
                }
            }
        }

        $detectionCount = \count($this->conflictHistory);

        return [
            'total' => $total,
            'by_severity' => $bySeverity,
            'by_type' => $byType,
            'avg_per_detection' => $detectionCount > 0 ? round($total / $detectionCount, 2) : 0.0,
            'detection_count' => $detectionCount,
        ];
    }

    /**
     * Clear conflict history
     */
    public function clearHistory(): void
    {
        $this->conflictHistory = [];
        Log::info('[ConflictDetection] Conflict history cleared');
    }

    /**
     * Normalize data for comparison
     *
     * @return array<string, mixed>
     */
    protected function normalizeData(mixed $data): array
    {
        if (! \is_array($data)) {
            return [];
        }

        /** @var array<string, mixed> $normalized */
        $normalized = [];
        foreach ($data as $key => $value) {
            $normalized[(string) $key] = $value;
        }

        return $normalized;
    }

    /**
     * Check if data is a list (sequential array of items)
     *
     * @param  array<mixed>  $data
     */
    protected function isListData(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        // Check if it's a sequential array
        if (\array_keys($data) !== \range(0, \count($data) - 1)) {
            return false;
        }

        // Check if first item is an array (list of records)
        return \is_array($data[0] ?? null);
    }

    /**
     * Index array data by ID field
     *
     * @param  array<int|string, mixed>  $data
     * @return array<string|int, array<string, mixed>>
     */
    protected function indexById(array $data): array
    {
        $indexed = [];

        foreach ($data as $item) {
            if (\is_array($item) && isset($item['id'])) {
                $idValue = $item['id'];
                if (is_string($idValue) || is_int($idValue)) {
                    $indexed[$idValue] = $item;
                }
            }
        }

        return $indexed;
    }

    /**
     * Get source confidence score
     */
    public function getSourceConfidence(string $sourceName): float
    {
        return self::SOURCE_CONFIDENCE[$sourceName] ?? self::SOURCE_CONFIDENCE['unknown'];
    }

    /**
     * Get all source confidence scores
     *
     * @return array<string, float>
     */
    public function getAllSourceConfidences(): array
    {
        return self::SOURCE_CONFIDENCE;
    }

    /**
     * Validate and detect conflicts for multi-source data fetch
     *
     * This is a convenience method that combines detection and resolution
     *
     * @param  array<string, array{data: mixed, source: string, timestamp?: string, metadata?: array<string, mixed>}>  $sources
     * @return array{data: mixed, has_conflicts: bool, conflicts: array<string, mixed>, resolution: array<string, mixed>}
     */
    public function validateAndResolve(array $sources, string $strategy = self::STRATEGY_HIGHEST_CONFIDENCE): array
    {
        $detection = $this->detectConflicts($sources);

        if (! $detection['has_conflicts']) {
            // No conflicts - return data from highest confidence source
            $primarySource = $this->selectPrimarySource($sources, $strategy);

            return [
                'data' => $sources[$primarySource]['data'] ?? [],
                'has_conflicts' => false,
                'conflicts' => [],
                'resolution' => [
                    'strategy_used' => $strategy,
                    'primary_source' => $primarySource,
                ],
            ];
        }

        $resolution = $this->resolveConflicts($detection['conflicts'], $sources, $strategy);

        return [
            'data' => $resolution['resolved_data'],
            'has_conflicts' => true,
            'conflicts' => $detection['conflicts'],
            'resolution' => $resolution,
        ];
    }
}
