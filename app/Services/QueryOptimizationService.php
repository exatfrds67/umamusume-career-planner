<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Cache\RedisStore;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Query Optimization Service
 *
 * Provides comprehensive database query optimization capabilities including:
 * - Query analysis and profiling
 * - Index recommendation engine
 * - Query caching strategies
 * - Slow query detection and logging
 * - Performance monitoring and alerts
 *
 * @see Requirements: 17.3, 50.2, 50.3
 */
class QueryOptimizationService
{
    /**
     * Collection of executed queries for analysis.
     *
     * @var array<int, array{sql: string, bindings: array<mixed>, time: float, connection: string, timestamp: int}>
     */
    private array $executedQueries = [];

    /**
     * Collection of slow queries detected.
     *
     * @var array<int, array{sql: string, bindings: array<mixed>, time: float, connection: string, timestamp: int}>
     */
    private array $slowQueries = [];

    /**
     * Index recommendations generated.
     *
     * @var array<string, array{table: string, columns: array<string>, reason: string, priority: string, estimated_improvement: string}>
     */
    private array $indexRecommendations = [];

    /**
     * Query statistics for analysis.
     *
     * @var array<string, array{sql: string, count: int, total_time: float, avg_time: float, max_time: float, min_time: float}>
     */
    private array $queryStats = [];

    /**
     * Cache hit/miss statistics.
     *
     * @var array{hits: int, misses: int, total: int}
     */
    private array $cacheStats = [
        'hits' => 0,
        'misses' => 0,
        'total' => 0,
    ];

    /**
     * Whether query listening is enabled.
     */
    private bool $isListening = false;

    /**
     * Create a new QueryOptimizationService instance.
     */
    public function __construct()
    {
        $this->loadCachedStats();
    }

    /**
     * Start listening to database queries for analysis.
     */
    public function startListening(): void
    {
        if ($this->isListening) {
            return;
        }

        Event::listen(QueryExecuted::class, function (QueryExecuted $query): void {
            $this->recordQuery($query);
        });

        $this->isListening = true;
    }

    /**
     * Stop listening to database queries.
     */
    public function stopListening(): void
    {
        $this->isListening = false;
    }

    /**
     * Record an executed query for analysis.
     */
    private function recordQuery(QueryExecuted $query): void
    {
        $queryData = [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
            'connection' => $query->connectionName,
            'timestamp' => time(),
        ];

        // Store in executed queries (limited to max stored)
        $configMaxStored = config('query-optimization.slow_query.max_stored_queries', 100);
        $maxStored = is_numeric($configMaxStored) ? (int) $configMaxStored : 100;
        if (count($this->executedQueries) >= $maxStored) {
            array_shift($this->executedQueries);
        }
        $this->executedQueries[] = $queryData;

        // Check for slow query
        $configWarningThreshold = config('query-optimization.slow_query.warning_threshold_ms', 100);
        $warningThreshold = is_numeric($configWarningThreshold) ? (float) $configWarningThreshold : 100.0;
        if ($query->time >= $warningThreshold) {
            $this->recordSlowQuery($queryData);
        }

        // Update query statistics
        $this->updateQueryStats($query->sql, $query->time);
    }

    /**
     * Record a slow query.
     *
     * @param  array{sql: string, bindings: array<mixed>, time: float, connection: string, timestamp: int}  $queryData
     */
    private function recordSlowQuery(array $queryData): void
    {
        $configMaxStored = config('query-optimization.slow_query.max_stored_queries', 100);
        $maxStored = is_numeric($configMaxStored) ? (int) $configMaxStored : 100;
        if (count($this->slowQueries) >= $maxStored) {
            array_shift($this->slowQueries);
        }
        $this->slowQueries[] = $queryData;

        // Log slow query if enabled
        if (config('query-optimization.slow_query.logging_enabled', true)) {
            $configCriticalThreshold = config('query-optimization.slow_query.critical_threshold_ms', 500);
            $criticalThreshold = is_numeric($configCriticalThreshold) ? (float) $configCriticalThreshold : 500.0;
            $level = $queryData['time'] >= $criticalThreshold ? 'error' : 'warning';

            $configLogChannel = config('query-optimization.slow_query.log_channel', 'daily');
            $logChannel = is_string($configLogChannel) ? $configLogChannel : 'daily';
            Log::channel($logChannel)
                ->$level('Slow query detected', [
                    'sql' => $queryData['sql'],
                    'time_ms' => $queryData['time'],
                    'connection' => $queryData['connection'],
                ]);
        }
    }

    /**
     * Update query statistics.
     */
    private function updateQueryStats(string $sql, float $time): void
    {
        // Normalize SQL for grouping (remove specific values)
        $normalizedSql = $this->normalizeQuery($sql);
        $hash = md5($normalizedSql);

        if (! isset($this->queryStats[$hash])) {
            $this->queryStats[$hash] = [
                'sql' => $normalizedSql,
                'count' => 0,
                'total_time' => 0.0,
                'avg_time' => 0.0,
                'max_time' => 0.0,
                'min_time' => PHP_FLOAT_MAX,
            ];
        }

        $stats = &$this->queryStats[$hash];
        $stats['count']++;
        $stats['total_time'] += $time;
        $stats['avg_time'] = $stats['total_time'] / $stats['count'];
        $stats['max_time'] = max($stats['max_time'], $time);
        $stats['min_time'] = min($stats['min_time'], $time);
    }

    /**
     * Normalize a SQL query for grouping similar queries.
     */
    private function normalizeQuery(string $sql): string
    {
        // Replace numeric values with placeholders
        $normalized = preg_replace('/\b\d+\b/', '?', $sql) ?? $sql;

        // Replace string values with placeholders
        $normalized = preg_replace('/\'[^\']*\'/', '?', $normalized) ?? $normalized;

        // Replace multiple spaces with single space
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }

    /**
     * Analyze queries and generate index recommendations.
     *
     * @return array<string, array{table: string, columns: array<string>, reason: string, priority: string, estimated_improvement: string}>
     */
    public function analyzeAndRecommendIndexes(): array
    {
        $this->indexRecommendations = [];

        foreach ($this->queryStats as $hash => $stats) {
            $configMinQueryCount = config('query-optimization.indexing.min_query_count', 10);
            $minQueryCount = is_numeric($configMinQueryCount) ? (int) $configMinQueryCount : 10;
            $configMinAvgExecution = config('query-optimization.indexing.min_avg_execution_ms', 50);
            $minAvgExecution = is_numeric($configMinAvgExecution) ? (float) $configMinAvgExecution : 50.0;

            if ($stats['count'] >= $minQueryCount && $stats['avg_time'] >= $minAvgExecution) {
                $recommendation = $this->generateIndexRecommendation($stats['sql'], $stats);
                if ($recommendation !== null) {
                    $this->indexRecommendations[$hash] = $recommendation;
                }
            }
        }

        return $this->indexRecommendations;
    }

    /**
     * Generate an index recommendation for a query.
     *
     * @param  array{sql: string, count: int, total_time: float, avg_time: float, max_time: float, min_time: float}  $stats
     * @return array{table: string, columns: array<string>, reason: string, priority: string, estimated_improvement: string}|null
     */
    private function generateIndexRecommendation(string $sql, array $stats): ?array
    {
        // Extract table name from query
        $table = $this->extractTableName($sql);
        if ($table === null) {
            return null;
        }

        // Check if table is excluded
        $configExcludedTables = config('query-optimization.indexing.excluded_tables', []);
        $excludedTables = is_array($configExcludedTables) ? $configExcludedTables : [];
        if (in_array($table, $excludedTables, true)) {
            return null;
        }

        // Extract columns used in WHERE, ORDER BY, JOIN clauses
        $columns = $this->extractIndexableColumns($sql);
        if (empty($columns)) {
            return null;
        }

        // Determine priority based on query frequency and execution time
        $priority = $this->calculateIndexPriority($stats);

        // Estimate improvement
        $estimatedImprovement = $this->estimateIndexImprovement($stats);

        return [
            'table' => $table,
            'columns' => $columns,
            'reason' => sprintf(
                'Query executed %d times with avg time %.2fms',
                $stats['count'],
                $stats['avg_time']
            ),
            'priority' => $priority,
            'estimated_improvement' => $estimatedImprovement,
        ];
    }

    /**
     * Extract table name from SQL query.
     */
    private function extractTableName(string $sql): ?string
    {
        // Match FROM clause
        if (preg_match('/\bFROM\s+[`"]?(\w+)[`"]?/i', $sql, $matches)) {
            return $matches[1];
        }

        // Match UPDATE clause
        if (preg_match('/\bUPDATE\s+[`"]?(\w+)[`"]?/i', $sql, $matches)) {
            return $matches[1];
        }

        // Match INSERT INTO clause
        if (preg_match('/\bINSERT\s+INTO\s+[`"]?(\w+)[`"]?/i', $sql, $matches)) {
            return $matches[1];
        }

        // Match DELETE FROM clause
        if (preg_match('/\bDELETE\s+FROM\s+[`"]?(\w+)[`"]?/i', $sql, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract columns that could benefit from indexing.
     *
     * @param  string  $sql  The SQL query to analyze
     * @return array<string>
     */
    private function extractIndexableColumns(string $sql): array
    {
        $columns = [];

        // Extract columns from WHERE clause
        if (preg_match_all('/\bWHERE\b.*?(?:\bAND\b|\bOR\b|$)/is', $sql, $whereMatches)) {
            foreach ($whereMatches[0] as $whereClause) {
                if (preg_match_all('/[`"]?(\w+)[`"]?\s*(?:=|<|>|<=|>=|<>|!=|LIKE|IN|BETWEEN)/i', $whereClause, $colMatches)) {
                    $columns = array_merge($columns, $colMatches[1]);
                }
            }
        }

        // Extract columns from ORDER BY clause
        if (preg_match('/\bORDER\s+BY\s+([^;]+)/i', $sql, $orderMatch)) {
            if (preg_match_all('/[`"]?(\w+)[`"]?(?:\s+(?:ASC|DESC))?/i', $orderMatch[1], $colMatches)) {
                $columns = array_merge($columns, $colMatches[1]);
            }
        }

        // Extract columns from JOIN conditions
        if (preg_match_all('/\bON\s+[`"]?(\w+)[`"]?\.[`"]?(\w+)[`"]?\s*=\s*[`"]?(\w+)[`"]?\.[`"]?(\w+)[`"]?/i', $sql, $joinMatches)) {
            $columns = array_merge($columns, $joinMatches[2], $joinMatches[4]);
        }

        // Remove duplicates and common non-indexable columns
        $columns = array_unique($columns);
        $excludeColumns = ['id', 'created_at', 'updated_at', 'deleted_at'];

        return array_values(array_filter($columns, fn ($col) => ! in_array(strtolower($col), $excludeColumns, true)));
    }

    /**
     * Calculate index priority based on query statistics.
     *
     * @param  array{count: int, total_time: float, avg_time: float, max_time: float, min_time: float}  $stats
     */
    private function calculateIndexPriority(array $stats): string
    {
        $score = ($stats['count'] * $stats['avg_time']) / 1000;

        if ($score >= 100) {
            return 'critical';
        }
        if ($score >= 50) {
            return 'high';
        }
        if ($score >= 20) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Estimate improvement from adding an index.
     *
     * @param  array{count: int, total_time: float, avg_time: float, max_time: float, min_time: float}  $stats
     */
    private function estimateIndexImprovement(array $stats): string
    {
        // Rough estimation: indexes typically improve query time by 50-90%
        $estimatedNewTime = $stats['avg_time'] * 0.3;
        $improvement = (($stats['avg_time'] - $estimatedNewTime) / $stats['avg_time']) * 100;

        return sprintf('~%.0f%% faster (%.2fms → %.2fms)', $improvement, $stats['avg_time'], $estimatedNewTime);
    }

    /**
     * Get cached query result or execute and cache.
     *
     * @param  callable(): mixed  $queryCallback
     */
    public function cachedQuery(string $cacheKey, callable $queryCallback, ?int $ttl = null): mixed
    {
        if (! config('query-optimization.cache.enabled', true)) {
            return $queryCallback();
        }

        $configPrefix = config('query-optimization.cache.prefix', 'query_cache:');
        $prefix = is_string($configPrefix) ? $configPrefix : 'query_cache:';
        $fullKey = $prefix.$cacheKey;
        $configDefaultTtl = config('query-optimization.cache.default_ttl', 300);
        $ttl = $ttl ?? (is_numeric($configDefaultTtl) ? (int) $configDefaultTtl : 300);

        $this->cacheStats['total']++;

        if (Cache::has($fullKey)) {
            $this->cacheStats['hits']++;

            return Cache::get($fullKey);
        }

        $this->cacheStats['misses']++;
        $result = $queryCallback();

        Cache::put($fullKey, $result, $ttl);

        return $result;
    }

    /**
     * Invalidate cached query results for a table.
     */
    public function invalidateTableCache(string $table): void
    {
        $configPrefix = config('query-optimization.cache.prefix', 'query_cache:');
        $prefix = is_string($configPrefix) ? $configPrefix : 'query_cache:';

        // For Redis, use pattern-based deletion
        $configCacheDefault = config('cache.default', '');
        $configCacheDriver = config('query-optimization.cache.driver', '');
        $cacheDefault = is_string($configCacheDefault) ? $configCacheDefault : '';
        $cacheDriver = is_string($configCacheDriver) ? $configCacheDriver : '';
        if ($cacheDefault === 'redis' && $cacheDriver === 'redis') {
            try {
                $pattern = $prefix.$table.':*';
                $store = Cache::store('redis')->getStore();
                if ($store instanceof RedisStore) {
                    $redis = $store->connection();
                    $keys = $redis->keys($pattern);
                    if (! empty($keys)) {
                        $redis->del($keys);
                    }
                }
            } catch (\Exception) {
                // Redis not available, fall through to tag-based clearing
            }
        }

        // Also clear using tags if available
        try {
            Cache::tags([$table])->flush();
        } catch (\Exception) {
            // Tags not supported by current cache driver
        }

        // For non-Redis drivers, clear specific known cache keys
        $commonKeys = [
            $prefix.$table.':all',
            $prefix.$table.':count',
            $prefix.$table.':recent',
        ];

        foreach ($commonKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get slow queries detected.
     *
     * @return array<int, array{sql: string, bindings: array<mixed>, time: float, connection: string, timestamp: int}>
     */
    public function getSlowQueries(): array
    {
        return $this->slowQueries;
    }

    /**
     * Get query statistics.
     *
     * @return array<string, array{sql: string, count: int, total_time: float, avg_time: float, max_time: float, min_time: float}>
     */
    public function getQueryStats(): array
    {
        return $this->queryStats;
    }

    /**
     * Get cache statistics.
     *
     * @return array{hits: int, misses: int, total: int, hit_rate: float}
     */
    public function getCacheStats(): array
    {
        $hitRate = $this->cacheStats['total'] > 0
        ? ($this->cacheStats['hits'] / $this->cacheStats['total']) * 100
        : 0.0;

        return [
            ...$this->cacheStats,
            'hit_rate' => round($hitRate, 2),
        ];
    }

    /**
     * Get performance metrics summary.
     *
     * @return array{total_queries: int, slow_queries: int, avg_query_time: float, cache_hit_rate: float, recommendations_count: int}
     */
    public function getPerformanceMetrics(): array
    {
        $totalQueries = count($this->executedQueries);
        $slowQueries = count($this->slowQueries);

        $totalTime = array_sum(array_column($this->executedQueries, 'time'));
        $avgTime = $totalQueries > 0 ? $totalTime / $totalQueries : 0.0;

        $cacheStats = $this->getCacheStats();

        return [
            'total_queries' => $totalQueries,
            'slow_queries' => $slowQueries,
            'avg_query_time' => round($avgTime, 2),
            'cache_hit_rate' => $cacheStats['hit_rate'],
            'recommendations_count' => count($this->indexRecommendations),
        ];
    }

    /**
     * Run EXPLAIN on a query and return the execution plan.
     *
     * @param  string  $sql  The SQL query to explain
     * @return array<int, array<string, mixed>>
     */
    public function explainQuery(string $sql): array
    {
        try {
            $results = DB::select('EXPLAIN '.$sql);

            return array_map(fn ($row) => (array) $row, $results);
        } catch (\Exception $e) {
            Log::warning('Failed to explain query', [
                'sql' => $sql,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Check if a table has proper indexes for common query patterns.
     *
     * @param  string  $table  The table name to analyze
     * @return array{table: string, existing_indexes: array<string>, missing_indexes: array<string>, recommendations: array<string>}
     */
    public function analyzeTableIndexes(string $table): array
    {
        $existingIndexes = [];
        $missingIndexes = [];
        $recommendations = [];

        try {
            // Get existing indexes
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            foreach ($indexes as $index) {
                $indexData = (array) $index;
                $keyName = $indexData['Key_name'] ?? '';
                $columnName = $indexData['Column_name'] ?? '';
                if (! isset($existingIndexes[$keyName])) {
                    $existingIndexes[$keyName] = [];
                }
                $existingIndexes[$keyName][] = $columnName;
            }

            // Analyze query patterns for this table
            foreach ($this->queryStats as $stats) {
                if (stripos($stats['sql'], $table) !== false) {
                    $columns = $this->extractIndexableColumns($stats['sql']);
                    foreach ($columns as $column) {
                        $hasIndex = false;
                        foreach ($existingIndexes as $indexColumns) {
                            if (in_array($column, $indexColumns, true)) {
                                $hasIndex = true;
                                break;
                            }
                        }
                        if (! $hasIndex && ! in_array($column, $missingIndexes, true)) {
                            $missingIndexes[] = $column;
                            $recommendations[] = "Consider adding index on column '{$column}'";
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to analyze table indexes', [
                'table' => $table,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'table' => $table,
            'existing_indexes' => array_keys($existingIndexes),
            'missing_indexes' => $missingIndexes,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Generate SQL for recommended indexes.
     *
     * @return array<string>
     */
    public function generateIndexSQL(): array
    {
        $sqlStatements = [];

        foreach ($this->indexRecommendations as $recommendation) {
            $table = $recommendation['table'];
            $columns = $recommendation['columns'];

            if (empty($columns)) {
                continue;
            }

            $indexName = 'idx_'.$table.'_'.implode('_', array_slice($columns, 0, 3));
            $columnList = implode(', ', array_map(fn ($col) => "`{$col}`", $columns));

            $sqlStatements[] = "CREATE INDEX `{$indexName}` ON `{$table}` ({$columnList});";
        }

        return $sqlStatements;
    }

    /**
     * Check for potential N+1 query issues.
     *
     * @return array<string, array{query: string, count: int, potential_n_plus_1: bool}>
     */
    public function detectNPlusOneQueries(): array
    {
        $potentialIssues = [];

        // Group queries by normalized pattern
        $queryGroups = [];
        foreach ($this->executedQueries as $query) {
            $normalized = $this->normalizeQuery($query['sql']);
            $hash = md5($normalized);

            if (! isset($queryGroups[$hash])) {
                $queryGroups[$hash] = [
                    'query' => $normalized,
                    'count' => 0,
                    'timestamps' => [],
                ];
            }

            $queryGroups[$hash]['count']++;
            $queryGroups[$hash]['timestamps'][] = $query['timestamp'];
        }

        // Identify potential N+1 issues (same query executed many times in short period)
        foreach ($queryGroups as $hash => $group) {
            if ($group['count'] >= 5) {
                // Check if queries were executed within a short time window
                $timestamps = $group['timestamps'];
                sort($timestamps);
                $timeSpan = end($timestamps) - reset($timestamps);

                if ($timeSpan <= 1) { // Within 1 second
                    $potentialIssues[$hash] = [
                        'query' => $group['query'],
                        'count' => $group['count'],
                        'potential_n_plus_1' => true,
                    ];
                }
            }
        }

        return $potentialIssues;
    }

    /**
     * Clear all collected data.
     */
    public function clearData(): void
    {
        $this->executedQueries = [];
        $this->slowQueries = [];
        $this->indexRecommendations = [];
        $this->queryStats = [];
        $this->cacheStats = [
            'hits' => 0,
            'misses' => 0,
            'total' => 0,
        ];
    }

    /**
     * Persist current statistics to cache.
     */
    public function persistStats(): void
    {
        Cache::put('query_optimization:stats', [
            'query_stats' => $this->queryStats,
            'cache_stats' => $this->cacheStats,
            'slow_queries' => array_slice($this->slowQueries, -50),
            'recommendations' => $this->indexRecommendations,
        ], 3600);
    }

    /**
     * Load cached statistics.
     */
    private function loadCachedStats(): void
    {
        $cached = Cache::get('query_optimization:stats');

        if ($cached !== null && is_array($cached)) {
            $queryStats = $cached['query_stats'] ?? [];
            $cacheStats = $cached['cache_stats'] ?? $this->cacheStats;
            $slowQueries = $cached['slow_queries'] ?? [];
            $recommendations = $cached['recommendations'] ?? [];

            /** @var array<string, array{sql: string, count: int, total_time: float, avg_time: float, max_time: float, min_time: float}> $validatedQueryStats */
            $validatedQueryStats = is_array($queryStats) ? $queryStats : [];
            $this->queryStats = $validatedQueryStats;

            /** @var array{hits: int, misses: int, total: int} $validatedCacheStats */
            $validatedCacheStats = is_array($cacheStats) && isset($cacheStats['hits'], $cacheStats['misses'], $cacheStats['total'])
                ? $cacheStats
                : $this->cacheStats;
            $this->cacheStats = $validatedCacheStats;

            /** @var array<int, array{sql: string, bindings: array<mixed>, time: float, connection: string, timestamp: int}> $validatedSlowQueries */
            $validatedSlowQueries = is_array($slowQueries) ? $slowQueries : [];
            $this->slowQueries = $validatedSlowQueries;

            /** @var array<string, array{table: string, columns: array<string>, reason: string, priority: string, estimated_improvement: string}> $validatedRecommendations */
            $validatedRecommendations = is_array($recommendations) ? $recommendations : [];
            $this->indexRecommendations = $validatedRecommendations;
        }
    }
}
