<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ApiPerformanceMonitoringService;
use App\Services\ApiResponseCachingService;
use App\Services\ApmService;
use App\Services\PerformanceAlertingService;
use App\Services\PerformanceRegressionService;
use App\Services\QueryOptimizationService;
use App\Services\RedisCacheOptimizationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Performance Controller
 *
 * Provides API endpoints for monitoring and managing database query performance,
 * including metrics dashboard, slow query logs, index analysis, Redis cache optimization,
 * API performance monitoring with bottleneck identification, and comprehensive APM features.
 *
 * @see Requirements: 17.3, 17.4, 50.2, 50.3, 52.3, 52.4, 54.2, 59.1, 59.2
 */
class PerformanceController extends Controller
{
    /**
     * Create a new PerformanceController instance.
     */
    public function __construct(
        private readonly QueryOptimizationService $queryOptimizationService,
        private readonly RedisCacheOptimizationService $redisCacheService,
        private readonly ApiPerformanceMonitoringService $apiPerformanceService,
        private readonly ApiResponseCachingService $apiCachingService,
        private readonly ApmService $apmService,
        private readonly PerformanceAlertingService $alertingService,
        private readonly PerformanceRegressionService $regressionService
    ) {}

    /**
     * Get query performance metrics dashboard.
     *
     * Returns comprehensive performance metrics including:
     * - Total queries executed
     * - Slow query count
     * - Average query time
     * - Cache hit rate
     * - Index recommendations count
     */
    public function dashboard(): JsonResponse
    {
        $metrics = $this->queryOptimizationService->getPerformanceMetrics();
        $cacheStats = $this->queryOptimizationService->getCacheStats();

        // Get database connection info
        $connectionInfo = $this->getDatabaseConnectionInfo();

        // Get recent slow queries summary
        $slowQueries = $this->queryOptimizationService->getSlowQueries();
        $recentSlowQueries = array_slice($slowQueries, -10);

        // Get index recommendations
        $recommendations = $this->queryOptimizationService->analyzeAndRecommendIndexes();

        // Check for N+1 issues
        $nPlusOneIssues = $this->queryOptimizationService->detectNPlusOneQueries();

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => $metrics,
                'cache' => $cacheStats,
                'connection' => $connectionInfo,
                'recent_slow_queries' => array_map(fn ($q) => [
                    'sql' => $this->truncateSql($q['sql']),
                    'time_ms' => round($q['time'], 2),
                    'timestamp' => date('Y-m-d H:i:s', $q['timestamp']),
                ], $recentSlowQueries),
                'recommendations_count' => count($recommendations),
                'n_plus_one_issues' => count($nPlusOneIssues),
                'thresholds' => [
                    'slow_query_warning_ms' => config('query-optimization.slow_query.warning_threshold_ms'),
                    'slow_query_critical_ms' => config('query-optimization.slow_query.critical_threshold_ms'),
                ],
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get slow query log.
     *
     * Returns detailed information about slow queries detected,
     * with optional filtering and pagination.
     */
    public function slowQueryLog(Request $request): JsonResponse
    {
        $slowQueries = $this->queryOptimizationService->getSlowQueries();

        // Apply filters
        $minTime = $request->float('min_time', 0);
        $maxTime = $request->float('max_time', PHP_FLOAT_MAX);
        $search = $request->string('search', '');

        $filtered = array_filter($slowQueries, function ($query) use ($minTime, $maxTime, $search) {
            if ($query['time'] < $minTime || $query['time'] > $maxTime) {
                return false;
            }
            if ($search !== '' && stripos($query['sql'], (string) $search) === false) {
                return false;
            }

            return true;
        });

        // Sort by time descending
        usort($filtered, fn ($a, $b) => $b['time'] <=> $a['time']);

        // Paginate
        $page = max(1, $request->integer('page', 1));
        $perPage = min(100, max(1, $request->integer('per_page', 20)));
        $total = count($filtered);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($filtered, $offset, $perPage);

        return response()->json([
            'success' => true,
            'data' => array_map(fn ($q) => [
                'sql' => $q['sql'],
                'bindings' => $q['bindings'],
                'time_ms' => round($q['time'], 2),
                'connection' => $q['connection'],
                'timestamp' => date('Y-m-d H:i:s', $q['timestamp']),
                'severity' => $q['time'] >= config('query-optimization.slow_query.critical_threshold_ms')
                    ? 'critical'
                    : 'warning',
            ], $items),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Get index analysis and recommendations.
     *
     * Returns analysis of current indexes and recommendations
     * for new indexes based on query patterns.
     */
    public function indexAnalysis(Request $request): JsonResponse
    {
        // Get index recommendations
        $recommendations = $this->queryOptimizationService->analyzeAndRecommendIndexes();

        // Generate SQL statements for recommendations
        $sqlStatements = $this->queryOptimizationService->generateIndexSQL();

        // Analyze specific table if requested
        $tableAnalysis = null;
        $table = $request->string('table', '');
        if ($table !== '') {
            $tableAnalysis = $this->queryOptimizationService->analyzeTableIndexes((string) $table);
        }

        // Group recommendations by priority
        $byPriority = [
            'critical' => [],
            'high' => [],
            'medium' => [],
            'low' => [],
        ];

        foreach ($recommendations as $hash => $rec) {
            $byPriority[$rec['priority']][] = [
                'id' => $hash,
                ...$rec,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'recommendations' => $byPriority,
                'sql_statements' => $sqlStatements,
                'table_analysis' => $tableAnalysis,
                'summary' => [
                    'total_recommendations' => count($recommendations),
                    'critical_count' => count($byPriority['critical']),
                    'high_count' => count($byPriority['high']),
                    'medium_count' => count($byPriority['medium']),
                    'low_count' => count($byPriority['low']),
                ],
            ],
        ]);
    }

    /**
     * Get query statistics.
     *
     * Returns aggregated statistics about query patterns,
     * execution times, and frequency.
     */
    public function queryStats(Request $request): JsonResponse
    {
        $stats = $this->queryOptimizationService->getQueryStats();

        // Sort by total time descending
        uasort($stats, fn ($a, $b) => $b['total_time'] <=> $a['total_time']);

        // Apply limit
        $limit = min(100, max(1, $request->integer('limit', 50)));
        $stats = array_slice($stats, 0, $limit, true);

        return response()->json([
            'success' => true,
            'data' => array_map(fn ($s) => [
                'sql' => $this->truncateSql($s['sql']),
                'count' => $s['count'],
                'total_time_ms' => round($s['total_time'], 2),
                'avg_time_ms' => round($s['avg_time'], 2),
                'max_time_ms' => round($s['max_time'], 2),
                'min_time_ms' => round($s['min_time'] === PHP_FLOAT_MAX ? 0 : $s['min_time'], 2),
            ], $stats),
            'total_patterns' => count($this->queryOptimizationService->getQueryStats()),
        ]);
    }

    /**
     * Explain a specific query.
     *
     * Returns the execution plan for a given SQL query.
     */
    public function explainQuery(Request $request): JsonResponse
    {
        $request->validate([
            'sql' => 'required|string|max:10000',
        ]);

        $sql = $request->string('sql');

        // Security: Only allow SELECT queries
        if (! preg_match('/^\s*SELECT\s/i', (string) $sql)) {
            return response()->json([
                'success' => false,
                'error' => 'Only SELECT queries can be explained',
            ], 400);
        }

        $plan = $this->queryOptimizationService->explainQuery((string) $sql);

        return response()->json([
            'success' => true,
            'data' => [
                'sql' => (string) $sql,
                'execution_plan' => $plan,
            ],
        ]);
    }

    /**
     * Detect N+1 query issues.
     *
     * Returns potential N+1 query problems detected
     * in recent query patterns.
     */
    public function detectNPlusOne(): JsonResponse
    {
        $issues = $this->queryOptimizationService->detectNPlusOneQueries();

        return response()->json([
            'success' => true,
            'data' => [
                'issues' => array_map(fn ($issue) => [
                    'query' => $this->truncateSql($issue['query']),
                    'count' => $issue['count'],
                    'potential_n_plus_1' => $issue['potential_n_plus_1'],
                    'recommendation' => 'Consider using eager loading with with() or load()',
                ], $issues),
                'total_issues' => count($issues),
            ],
        ]);
    }

    /**
     * Clear performance data.
     *
     * Clears all collected query statistics and recommendations.
     */
    public function clearData(): JsonResponse
    {
        $this->queryOptimizationService->clearData();

        return response()->json([
            'success' => true,
            'message' => 'Performance data cleared successfully',
        ]);
    }

    /**
     * Invalidate cache for a specific table.
     */
    public function invalidateCache(Request $request): JsonResponse
    {
        $request->validate([
            'table' => 'required|string|max:255',
        ]);

        $table = $request->string('table');
        $this->queryOptimizationService->invalidateTableCache((string) $table);

        return response()->json([
            'success' => true,
            'message' => "Cache invalidated for table: {$table}",
        ]);
    }

    /**
     * Get cache statistics.
     */
    public function cacheStats(): JsonResponse
    {
        $stats = $this->queryOptimizationService->getCacheStats();

        // Get Redis info if available
        $redisInfo = null;
        try {
            if (config('cache.default') === 'redis') {
                $redis = Cache::store('redis')->getRedis();
                $info = $redis->info();
                $redisInfo = [
                    'used_memory' => $info['used_memory_human'] ?? 'N/A',
                    'connected_clients' => $info['connected_clients'] ?? 'N/A',
                    'total_commands_processed' => $info['total_commands_processed'] ?? 'N/A',
                    'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                    'keyspace_misses' => $info['keyspace_misses'] ?? 0,
                ];
            }
        } catch (\Exception) {
            // Redis not available
        }

        return response()->json([
            'success' => true,
            'data' => [
                'query_cache' => $stats,
                'redis' => $redisInfo,
                'config' => [
                    'enabled' => config('query-optimization.cache.enabled'),
                    'default_ttl' => config('query-optimization.cache.default_ttl'),
                    'driver' => config('query-optimization.cache.driver'),
                ],
            ],
        ]);
    }

    /**
     * Get Redis health status.
     *
     * GET /api/performance/redis/health
     *
     * @see Requirements: 17.4, 59.2
     */
    public function redisHealth(): JsonResponse
    {
        $health = $this->redisCacheService->checkHealth();

        return response()->json([
            'success' => true,
            'data' => $health,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get Redis memory usage statistics.
     *
     * GET /api/performance/redis/memory
     *
     * @see Requirements: 17.4, 59.2
     */
    public function redisMemory(): JsonResponse
    {
        $memory = $this->redisCacheService->getMemoryUsage();

        return response()->json([
            'success' => true,
            'data' => $memory,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Trigger cache warming.
     *
     * POST /api/performance/redis/warm
     *
     * @see Requirements: 17.4, 59.2
     */
    public function warmRedisCache(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'types' => 'array',
            'types.*' => 'string|in:skills,support_cards,meta_rankings,characters,training_calculations',
        ]);

        $types = $validated['types'] ?? ['skills', 'support_cards', 'meta_rankings'];

        // Build data providers based on requested types
        $providers = $this->buildCacheWarmingProviders($types);

        $result = $this->redisCacheService->warmCache($providers);

        return response()->json([
            'success' => true,
            'data' => $result,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Invalidate cache by tags.
     *
     * POST /api/performance/redis/invalidate
     *
     * @see Requirements: 17.4, 59.2
     */
    public function invalidateRedisCache(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tags' => 'array',
            'tags.*' => 'string',
            'data_type' => 'string',
            'cascade' => 'boolean',
        ]);

        if (isset($validated['data_type'])) {
            $cascade = $validated['cascade'] ?? false;

            if ($cascade) {
                $result = $this->redisCacheService->invalidateWithCascade($validated['data_type']);
            } else {
                $result = $this->redisCacheService->invalidateByTags(
                    config("cache-management.invalidation.default_tags.{$validated['data_type']}", [])
                );
            }
        } elseif (isset($validated['tags'])) {
            $result = $this->redisCacheService->invalidateByTags($validated['tags']);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Either tags or data_type must be provided',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get cache hit rate statistics.
     *
     * GET /api/performance/redis/hit-rate
     *
     * @see Requirements: 17.4, 59.2
     */
    public function redisHitRate(): JsonResponse
    {
        $stats = $this->redisCacheService->getHitRateStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get cache optimization recommendations.
     *
     * GET /api/performance/redis/recommendations
     *
     * @see Requirements: 17.4, 59.2
     */
    public function redisRecommendations(): JsonResponse
    {
        $recommendations = $this->redisCacheService->getOptimizationRecommendations();

        return response()->json([
            'success' => true,
            'data' => [
                'recommendations' => $recommendations,
                'count' => \count($recommendations),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Cleanup stale cache entries.
     *
     * POST /api/performance/redis/cleanup
     *
     * @see Requirements: 17.4, 59.2
     */
    public function cleanupRedisCache(): JsonResponse
    {
        $result = $this->redisCacheService->cleanupStaleEntries();

        return response()->json([
            'success' => true,
            'data' => $result,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get comprehensive Redis statistics.
     *
     * GET /api/performance/redis/stats
     *
     * @see Requirements: 17.4, 59.2
     */
    public function redisComprehensiveStats(): JsonResponse
    {
        $stats = $this->redisCacheService->getComprehensiveStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Build cache warming providers based on requested types.
     *
     * @param  array<string>  $types
     * @return array<string, callable>
     */
    private function buildCacheWarmingProviders(array $types): array
    {
        $providers = [];

        foreach ($types as $type) {
            $providers[$type] = match ($type) {
                'skills' => fn () => DB::table('ucp_skills')->get()->toArray(),
                'support_cards' => fn () => DB::table('ucp_support_cards')->get()->toArray(),
                'meta_rankings' => fn () => $this->getMetaRankingsData(),
                'characters' => fn () => DB::table('ucp_characters')->get()->toArray(),
                'training_calculations' => fn () => $this->getTrainingCalculationsData(),
                default => fn () => [],
            };
        }

        return $providers;
    }

    /**
     * Get meta rankings data for cache warming.
     *
     * @return array<mixed>
     */
    private function getMetaRankingsData(): array
    {
        return DB::table('ucp_support_cards')
            ->whereNotNull('meta_tier')
            ->orderBy('meta_tier')
            ->get()
            ->toArray();
    }

    /**
     * Get training calculations data for cache warming.
     *
     * @return array<mixed>
     */
    private function getTrainingCalculationsData(): array
    {
        // Return base training calculation data
        return [
            'stat_multipliers' => [
                'speed' => 1.0,
                'stamina' => 1.0,
                'power' => 1.0,
                'guts' => 1.0,
                'wit' => 1.0,
            ],
            'facility_bonuses' => [
                1 => 1.0,
                2 => 1.2,
                3 => 1.4,
                4 => 1.6,
                5 => 2.0,
            ],
            'friendship_thresholds' => [
                'rainbow' => 80,
                'bonus_2' => 2,
                'bonus_3' => 3,
            ],
        ];
    }

    /**
     * Get database connection information.
     *
     * @return array{driver: string, database: string, host: string|null, connection_count: int|null}
     */
    private function getDatabaseConnectionInfo(): array
    {
        $connection = DB::connection();
        $config = $connection->getConfig();

        return [
            'driver' => (string) ($config['driver'] ?? 'unknown'),
            'database' => (string) ($config['database'] ?? 'unknown'),
            'host' => isset($config['host']) ? (string) $config['host'] : null,
            'connection_count' => null, // Would need specific driver support
        ];
    }

    /**
     * Truncate SQL for display.
     */
    private function truncateSql(string $sql, int $maxLength = 200): string
    {
        if (strlen($sql) <= $maxLength) {
            return $sql;
        }

        return substr($sql, 0, $maxLength).'...';
    }

    /*
    |--------------------------------------------------------------------------
    | API Performance Monitoring Endpoints
    |--------------------------------------------------------------------------
    |
    | These endpoints provide comprehensive API performance monitoring including
    | response time tracking, bottleneck identification, and optimization recommendations.
    |
    | @see Requirements: 52.3, 52.4
    | @see Task: 6.1.4 API performance optimization and monitoring
    |
    */

    /**
     * Get API performance dashboard.
     *
     * GET /api/performance/api/dashboard
     *
     * Returns comprehensive API performance metrics including:
     * - Overview metrics (total requests, avg response time, error rate)
     * - Endpoint-specific metrics
     * - Identified bottlenecks
     * - Performance trends
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiDashboard(): JsonResponse
    {
        $dashboard = $this->apiPerformanceService->getDashboard();

        return response()->json([
            'success' => true,
            'data' => $dashboard,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get API performance overview metrics.
     *
     * GET /api/performance/api/overview
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiOverview(): JsonResponse
    {
        $overview = $this->apiPerformanceService->getOverviewMetrics();

        return response()->json([
            'success' => true,
            'data' => $overview,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get endpoint-specific performance metrics.
     *
     * GET /api/performance/api/endpoints
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiEndpoints(): JsonResponse
    {
        $endpoints = $this->apiPerformanceService->getEndpointMetrics();

        return response()->json([
            'success' => true,
            'data' => [
                'endpoints' => $endpoints,
                'count' => count($endpoints),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Identify API performance bottlenecks.
     *
     * GET /api/performance/api/bottlenecks
     *
     * Returns identified performance issues with severity levels
     * and recommendations for improvement.
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiBottlenecks(): JsonResponse
    {
        $bottlenecks = $this->apiPerformanceService->identifyBottlenecks();

        return response()->json([
            'success' => true,
            'data' => [
                'bottlenecks' => $bottlenecks,
                'count' => count($bottlenecks),
                'by_severity' => [
                    'critical' => count(array_filter($bottlenecks, fn ($b) => $b['severity'] === 'critical')),
                    'warning' => count(array_filter($bottlenecks, fn ($b) => $b['severity'] === 'warning')),
                    'info' => count(array_filter($bottlenecks, fn ($b) => $b['severity'] === 'info')),
                ],
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get slow API requests.
     *
     * GET /api/performance/api/slow-requests
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiSlowRequests(Request $request): JsonResponse
    {
        $limit = min(100, max(1, $request->integer('limit', 50)));
        $slowRequests = $this->apiPerformanceService->getSlowRequests($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'slow_requests' => $slowRequests,
                'count' => count($slowRequests),
                'threshold_ms' => config('api-performance.monitoring.slow_request_threshold_ms', 1000),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get API performance trends.
     *
     * GET /api/performance/api/trends
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiTrends(): JsonResponse
    {
        $trends = $this->apiPerformanceService->getPerformanceTrends();

        return response()->json([
            'success' => true,
            'data' => $trends,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get request batching recommendations.
     *
     * GET /api/performance/api/batching-recommendations
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiBatchingRecommendations(): JsonResponse
    {
        $recommendations = $this->apiPerformanceService->getBatchingRecommendations();

        return response()->json([
            'success' => true,
            'data' => [
                'recommendations' => $recommendations,
                'count' => count($recommendations),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get resource utilization metrics.
     *
     * GET /api/performance/api/resources
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiResources(): JsonResponse
    {
        $resources = $this->apiPerformanceService->getResourceUtilization();

        return response()->json([
            'success' => true,
            'data' => $resources,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Clear API performance metrics.
     *
     * POST /api/performance/api/clear
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiClearMetrics(): JsonResponse
    {
        $this->apiPerformanceService->clearMetrics();

        return response()->json([
            'success' => true,
            'message' => 'API performance metrics cleared successfully',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | API Response Caching Endpoints
    |--------------------------------------------------------------------------
    |
    | These endpoints manage API response caching with intelligent invalidation.
    |
    | @see Requirements: 52.3, 52.4
    | @see Task: 6.1.4 API performance optimization and monitoring
    |
    */

    /**
     * Get API cache statistics.
     *
     * GET /api/performance/api-cache/stats
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiCacheStats(): JsonResponse
    {
        $stats = $this->apiCachingService->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Invalidate API cache by tags.
     *
     * POST /api/performance/api-cache/invalidate
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiCacheInvalidate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tags' => 'array',
            'tags.*' => 'string',
            'data_type' => 'string',
            'endpoint_pattern' => 'string',
            'cascade' => 'boolean',
        ]);

        $result = [];

        if (isset($validated['endpoint_pattern'])) {
            $result = $this->apiCachingService->invalidateByEndpoint($validated['endpoint_pattern']);
        } elseif (isset($validated['data_type'])) {
            $cascade = $validated['cascade'] ?? false;
            if ($cascade) {
                $result = $this->apiCachingService->invalidateWithCascade($validated['data_type']);
            } else {
                $result = $this->apiCachingService->invalidateByTags(
                    config("api-performance.cache.data_type_tags.{$validated['data_type']}", ['api_response'])
                );
            }
        } elseif (isset($validated['tags'])) {
            $result = $this->apiCachingService->invalidateByTags($validated['tags']);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Either tags, data_type, or endpoint_pattern must be provided',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Warm API cache for frequently accessed endpoints.
     *
     * POST /api/performance/api-cache/warm
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiCacheWarm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoints' => 'array',
            'endpoints.*.method' => 'required|string|in:GET,POST',
            'endpoints.*.path' => 'required|string',
            'endpoints.*.params' => 'array',
        ]);

        $endpoints = $validated['endpoints'] ?? config('api-performance.cache.warming.endpoints', []);

        $result = $this->apiCachingService->warmCache($endpoints);

        return response()->json([
            'success' => true,
            'data' => $result,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get rate limiting configuration and status.
     *
     * GET /api/performance/rate-limiting/status
     *
     * @see Requirements: 52.4
     */
    public function rateLimitingStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $tier = 'public';

        if ($user !== null) {
            $tier = 'authenticated';
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                $tier = 'admin';
            } elseif (method_exists($user, 'isPremium') && $user->isPremium()) {
                $tier = 'premium';
            }
        }

        $tierConfig = config("api-performance.rate_limiting.tiers.{$tier}", []);

        return response()->json([
            'success' => true,
            'data' => [
                'current_tier' => $tier,
                'limits' => $tierConfig,
                'enabled' => config('api-performance.rate_limiting.enabled', true),
                'endpoint_specific_limits' => config('api-performance.rate_limiting.endpoint_limits', []),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get comprehensive API performance configuration.
     *
     * GET /api/performance/api/config
     *
     * @see Requirements: 52.3, 52.4
     */
    public function apiConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'compression' => [
                    'enabled' => config('api-performance.compression.enabled', true),
                    'level' => config('api-performance.compression.level', 6),
                    'min_size' => config('api-performance.compression.min_size', 1024),
                ],
                'rate_limiting' => [
                    'enabled' => config('api-performance.rate_limiting.enabled', true),
                    'tiers' => array_keys(config('api-performance.rate_limiting.tiers', [])),
                ],
                'caching' => [
                    'enabled' => config('api-performance.cache.enabled', true),
                    'default_ttl' => config('api-performance.cache.default_ttl', 300),
                ],
                'monitoring' => [
                    'enabled' => config('api-performance.monitoring.enabled', true),
                    'slow_request_threshold_ms' => config('api-performance.monitoring.slow_request_threshold_ms', 1000),
                ],
                'batching' => [
                    'enabled' => config('api-performance.batching.enabled', true),
                    'max_batch_size' => config('api-performance.batching.max_batch_size', 10),
                ],
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | APM (Application Performance Monitoring) Endpoints
    |--------------------------------------------------------------------------
    |
    | These endpoints provide comprehensive APM features including unified
    | dashboard, health scoring, alerting, and regression detection.
    |
    | @see Requirements: 54.2, 59.1
    | @see Task: 6.1.5 Comprehensive performance monitoring setup
    |
    */

    /**
     * Display the APM dashboard view.
     *
     * GET /performance/apm/dashboard
     *
     * @see Requirements: 54.2, 59.1
     */
    public function apmDashboardView(): View
    {
        $data = $this->apmService->getDashboardData();

        return view('performance.dashboard', compact('data'));
    }

    /**
     * Get comprehensive APM dashboard data.
     *
     * GET /api/performance/apm/dashboard
     *
     * Returns aggregated metrics from all monitoring sources including:
     * - Health score with component breakdown
     * - Overview metrics
     * - Database, cache, API, and system metrics
     * - Performance trends
     * - Recent alerts and regressions
     *
     * @see Requirements: 54.2, 59.1
     */
    public function apmDashboard(): JsonResponse
    {
        $dashboard = $this->apmService->getDashboardData();

        return response()->json([
            'success' => true,
            'data' => $dashboard,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get application health score.
     *
     * GET /api/performance/apm/health-score
     *
     * @see Requirements: 54.2, 59.1
     */
    public function apmHealthScore(): JsonResponse
    {
        $healthScore = $this->apmService->calculateHealthScore();

        return response()->json([
            'success' => true,
            'data' => $healthScore,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get APM overview metrics.
     *
     * GET /api/performance/apm/overview
     *
     * @see Requirements: 54.2, 59.1
     */
    public function apmOverview(): JsonResponse
    {
        $overview = $this->apmService->getOverviewMetrics();

        return response()->json([
            'success' => true,
            'data' => $overview,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get performance trends.
     *
     * GET /api/performance/apm/trends
     *
     * @see Requirements: 54.2, 59.1
     */
    public function apmTrends(): JsonResponse
    {
        $trends = $this->apmService->getPerformanceTrends();

        return response()->json([
            'success' => true,
            'data' => $trends,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get aggregated metrics for a specific metric type.
     *
     * GET /api/performance/apm/metrics/{metric}
     *
     * @see Requirements: 54.2, 59.1
     */
    public function apmMetrics(Request $request, string $metric): JsonResponse
    {
        $hours = min(168, max(1, $request->integer('hours', 24)));
        $metrics = $this->apmService->getAggregatedMetrics($metric, $hours);

        return response()->json([
            'success' => true,
            'data' => [
                'metric' => $metric,
                'period_hours' => $hours,
                'statistics' => $metrics,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Clear all APM data.
     *
     * POST /api/performance/apm/clear
     *
     * @see Requirements: 54.2, 59.1
     */
    public function apmClear(): JsonResponse
    {
        $this->apmService->clearData();

        return response()->json([
            'success' => true,
            'message' => 'APM data cleared successfully',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Performance Alerting Endpoints
    |--------------------------------------------------------------------------
    |
    | These endpoints manage performance alerts and notifications.
    |
    | @see Requirements: 54.2, 59.1
    | @see Task: 6.1.5 Comprehensive performance monitoring setup
    |
    */

    /**
     * Check all alert conditions.
     *
     * POST /api/performance/alerts/check
     *
     * @see Requirements: 54.2, 59.1
     */
    public function alertsCheck(): JsonResponse
    {
        $result = $this->alertingService->checkAlerts();

        return response()->json([
            'success' => true,
            'data' => $result,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get all alerts.
     *
     * GET /api/performance/alerts
     *
     * @see Requirements: 54.2, 59.1
     */
    public function alertsList(Request $request): JsonResponse
    {
        $limit = min(100, max(1, $request->integer('limit', 50)));
        $alerts = $this->alertingService->getAlerts($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'alerts' => $alerts,
                'count' => \count($alerts),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get unacknowledged alerts.
     *
     * GET /api/performance/alerts/unacknowledged
     *
     * @see Requirements: 54.2, 59.1
     */
    public function alertsUnacknowledged(): JsonResponse
    {
        $alerts = $this->alertingService->getUnacknowledgedAlerts();

        return response()->json([
            'success' => true,
            'data' => [
                'alerts' => $alerts,
                'count' => \count($alerts),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get alert statistics.
     *
     * GET /api/performance/alerts/statistics
     *
     * @see Requirements: 54.2, 59.1
     */
    public function alertsStatistics(): JsonResponse
    {
        $statistics = $this->alertingService->getAlertStatistics();

        return response()->json([
            'success' => true,
            'data' => $statistics,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Acknowledge an alert.
     *
     * POST /api/performance/alerts/{alertId}/acknowledge
     *
     * @see Requirements: 54.2, 59.1
     */
    public function alertsAcknowledge(string $alertId): JsonResponse
    {
        $acknowledged = $this->alertingService->acknowledgeAlert($alertId);

        return response()->json([
            'success' => $acknowledged,
            'message' => $acknowledged ? 'Alert acknowledged successfully' : 'Alert not found',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Clear all alerts.
     *
     * POST /api/performance/alerts/clear
     *
     * @see Requirements: 54.2, 59.1
     */
    public function alertsClear(): JsonResponse
    {
        $this->alertingService->clearAlerts();

        return response()->json([
            'success' => true,
            'message' => 'All alerts cleared successfully',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Performance Regression Detection Endpoints
    |--------------------------------------------------------------------------
    |
    | These endpoints manage performance regression detection and reporting.
    |
    | @see Requirements: 54.2, 59.1
    | @see Task: 6.1.5 Comprehensive performance monitoring setup
    |
    */

    /**
     * Check for performance regressions.
     *
     * POST /api/performance/regressions/check
     *
     * @see Requirements: 54.2, 59.1
     */
    public function regressionsCheck(): JsonResponse
    {
        $result = $this->regressionService->checkRegressions();

        return response()->json([
            'success' => true,
            'data' => $result,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get all regressions.
     *
     * GET /api/performance/regressions
     *
     * @see Requirements: 54.2, 59.1
     */
    public function regressionsList(Request $request): JsonResponse
    {
        $limit = min(100, max(1, $request->integer('limit', 50)));
        $regressions = $this->regressionService->getRegressions($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'regressions' => $regressions,
                'count' => \count($regressions),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get active regressions.
     *
     * GET /api/performance/regressions/active
     *
     * @see Requirements: 54.2, 59.1
     */
    public function regressionsActive(): JsonResponse
    {
        $regressions = $this->regressionService->getActiveRegressions();

        return response()->json([
            'success' => true,
            'data' => [
                'regressions' => $regressions,
                'count' => \count($regressions),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get regression statistics.
     *
     * GET /api/performance/regressions/statistics
     *
     * @see Requirements: 54.2, 59.1
     */
    public function regressionsStatistics(): JsonResponse
    {
        $statistics = $this->regressionService->getRegressionStatistics();

        return response()->json([
            'success' => true,
            'data' => $statistics,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get baseline for a metric.
     *
     * GET /api/performance/regressions/baseline/{metric}
     *
     * @see Requirements: 54.2, 59.1
     */
    public function regressionsBaseline(string $metric): JsonResponse
    {
        $baseline = $this->regressionService->getBaseline($metric);

        return response()->json([
            'success' => true,
            'data' => $baseline,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Calculate baseline for a metric.
     *
     * POST /api/performance/regressions/baseline/{metric}
     *
     * @see Requirements: 54.2, 59.1
     */
    public function regressionsCalculateBaseline(string $metric): JsonResponse
    {
        $baseline = $this->regressionService->calculateBaseline($metric);

        return response()->json([
            'success' => $baseline !== null,
            'data' => $baseline,
            'message' => $baseline !== null ? 'Baseline calculated successfully' : 'Insufficient data for baseline calculation',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Update regression status.
     *
     * PATCH /api/performance/regressions/{regressionId}
     *
     * @see Requirements: 54.2, 59.1
     */
    public function regressionsUpdate(Request $request, string $regressionId): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:detected,investigating,resolved,false_positive',
            'notes' => 'nullable|string|max:1000',
        ]);

        $updated = $this->regressionService->updateRegressionStatus(
            $regressionId,
            $validated['status'],
            $validated['notes'] ?? null
        );

        return response()->json([
            'success' => $updated,
            'message' => $updated ? 'Regression status updated successfully' : 'Regression not found',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Generate regression report.
     *
     * GET /api/performance/regressions/report
     *
     * @see Requirements: 54.2, 59.1
     */
    public function regressionsReport(): JsonResponse
    {
        $report = $this->regressionService->generateReport();

        return response()->json([
            'success' => true,
            'data' => $report,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Clear all regression data.
     *
     * POST /api/performance/regressions/clear
     *
     * @see Requirements: 54.2, 59.1
     */
    public function regressionsClear(): JsonResponse
    {
        $this->regressionService->clearData();

        return response()->json([
            'success' => true,
            'message' => 'All regression data cleared successfully',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
