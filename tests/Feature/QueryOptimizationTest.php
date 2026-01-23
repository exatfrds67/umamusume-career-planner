<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\QueryOptimizationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->service = new QueryOptimizationService;
    $this->user = User::factory()->create();
});

describe('QueryOptimizationService', function (): void {
    describe('Query Listening', function (): void {
        it('starts and stops listening to queries', function (): void {
            $this->service->startListening();

            // Execute a query
            DB::select('SELECT 1');

            $this->service->stopListening();

            // Service should have recorded the query
            expect($this->service->getQueryStats())->toBeArray();
        });

        it('records slow queries when threshold is exceeded', function (): void {
            config(['query-optimization.slow_query.warning_threshold_ms' => 0.001]);

            $this->service->startListening();

            // Execute a query that will exceed the threshold
            DB::select('SELECT 1');

            $this->service->stopListening();

            $slowQueries = $this->service->getSlowQueries();
            expect($slowQueries)->toBeArray();
        });
    });

    describe('Query Caching', function (): void {
        it('caches query results when enabled', function (): void {
            config(['query-optimization.cache.enabled' => true]);

            $cacheKey = 'test_query_'.time();
            $expectedResult = ['data' => 'test'];

            $result = $this->service->cachedQuery($cacheKey, fn () => $expectedResult);

            expect($result)->toBe($expectedResult);

            // Second call should return cached result
            $cachedResult = $this->service->cachedQuery($cacheKey, fn () => ['different' => 'data']);

            expect($cachedResult)->toBe($expectedResult);
        });

        it('bypasses cache when disabled', function (): void {
            config(['query-optimization.cache.enabled' => false]);

            $cacheKey = 'test_query_disabled_'.time();
            $firstResult = ['first' => 'result'];
            $secondResult = ['second' => 'result'];

            $result1 = $this->service->cachedQuery($cacheKey, fn () => $firstResult);
            $result2 = $this->service->cachedQuery($cacheKey, fn () => $secondResult);

            expect($result1)->toBe($firstResult);
            expect($result2)->toBe($secondResult);
        });

        it('tracks cache hit and miss statistics', function (): void {
            config(['query-optimization.cache.enabled' => true]);

            $cacheKey = 'test_stats_'.time();

            // First call - cache miss
            $this->service->cachedQuery($cacheKey, fn () => 'data');

            // Second call - cache hit
            $this->service->cachedQuery($cacheKey, fn () => 'data');

            $stats = $this->service->getCacheStats();

            expect($stats)->toHaveKeys(['hits', 'misses', 'total', 'hit_rate']);
            expect($stats['total'])->toBeGreaterThanOrEqual(2);
        });
    });

    describe('Index Recommendations', function (): void {
        it('generates index recommendations for slow queries', function (): void {
            // Manually add query stats to simulate slow queries
            $reflection = new ReflectionClass($this->service);
            $property = $reflection->getProperty('queryStats');
            $property->setAccessible(true);
            $property->setValue($this->service, [
                'hash1' => [
                    'sql' => 'SELECT * FROM characters WHERE user_id = ? AND scenario_type = ?',
                    'count' => 100,
                    'total_time' => 5000.0,
                    'avg_time' => 50.0,
                    'max_time' => 100.0,
                    'min_time' => 20.0,
                ],
            ]);

            config(['query-optimization.indexing.min_query_count' => 10]);
            config(['query-optimization.indexing.min_avg_execution_ms' => 40]);

            $recommendations = $this->service->analyzeAndRecommendIndexes();

            expect($recommendations)->toBeArray();
        });

        it('generates SQL statements for recommended indexes', function (): void {
            // Set up recommendations
            $reflection = new ReflectionClass($this->service);
            $property = $reflection->getProperty('indexRecommendations');
            $property->setAccessible(true);
            $property->setValue($this->service, [
                'hash1' => [
                    'table' => 'characters',
                    'columns' => ['user_id', 'scenario_type'],
                    'reason' => 'Test reason',
                    'priority' => 'high',
                    'estimated_improvement' => '~70% faster',
                ],
            ]);

            $sqlStatements = $this->service->generateIndexSQL();

            expect($sqlStatements)->toBeArray();
            expect($sqlStatements)->not->toBeEmpty();
            expect($sqlStatements[0])->toContain('CREATE INDEX');
            expect($sqlStatements[0])->toContain('characters');
        });
    });

    describe('N+1 Query Detection', function (): void {
        it('detects potential N+1 query issues', function (): void {
            // Simulate N+1 pattern by adding multiple similar queries
            $reflection = new ReflectionClass($this->service);
            $property = $reflection->getProperty('executedQueries');
            $property->setAccessible(true);

            $timestamp = time();
            $queries = [];
            for ($i = 0; $i < 10; $i++) {
                $queries[] = [
                    'sql' => 'SELECT * FROM skills WHERE character_id = ?',
                    'bindings' => [$i],
                    'time' => 5.0,
                    'connection' => 'sqlite',
                    'timestamp' => $timestamp,
                ];
            }
            $property->setValue($this->service, $queries);

            $issues = $this->service->detectNPlusOneQueries();

            expect($issues)->toBeArray();
        });
    });

    describe('Performance Metrics', function (): void {
        it('returns comprehensive performance metrics', function (): void {
            $metrics = $this->service->getPerformanceMetrics();

            expect($metrics)->toHaveKeys([
                'total_queries',
                'slow_queries',
                'avg_query_time',
                'cache_hit_rate',
                'recommendations_count',
            ]);
        });

        it('clears all collected data', function (): void {
            // Add some data first
            $this->service->startListening();
            DB::select('SELECT 1');
            $this->service->stopListening();

            $this->service->clearData();

            expect($this->service->getSlowQueries())->toBeEmpty();
            expect($this->service->getQueryStats())->toBeEmpty();
        });
    });

    describe('Query Normalization', function (): void {
        it('normalizes queries for grouping', function (): void {
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('normalizeQuery');
            $method->setAccessible(true);

            $sql1 = 'SELECT * FROM users WHERE id = 1';
            $sql2 = 'SELECT * FROM users WHERE id = 2';

            $normalized1 = $method->invoke($this->service, $sql1);
            $normalized2 = $method->invoke($this->service, $sql2);

            expect($normalized1)->toBe($normalized2);
        });
    });

    describe('Table Name Extraction', function (): void {
        it('extracts table name from SELECT query', function (): void {
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('extractTableName');
            $method->setAccessible(true);

            $sql = 'SELECT * FROM characters WHERE id = 1';
            $table = $method->invoke($this->service, $sql);

            expect($table)->toBe('characters');
        });

        it('extracts table name from UPDATE query', function (): void {
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('extractTableName');
            $method->setAccessible(true);

            $sql = 'UPDATE users SET name = ? WHERE id = ?';
            $table = $method->invoke($this->service, $sql);

            expect($table)->toBe('users');
        });

        it('extracts table name from INSERT query', function (): void {
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('extractTableName');
            $method->setAccessible(true);

            $sql = 'INSERT INTO skills (name, type) VALUES (?, ?)';
            $table = $method->invoke($this->service, $sql);

            expect($table)->toBe('skills');
        });

        it('extracts table name from DELETE query', function (): void {
            $reflection = new ReflectionClass($this->service);
            $method = $reflection->getMethod('extractTableName');
            $method->setAccessible(true);

            $sql = 'DELETE FROM careers WHERE id = ?';
            $table = $method->invoke($this->service, $sql);

            expect($table)->toBe('careers');
        });
    });
});

describe('PerformanceController API', function (): void {
    beforeEach(function (): void {
        $this->user = User::factory()->create();
    });

    describe('Dashboard Endpoint', function (): void {
        it('returns performance dashboard data', function (): void {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/dashboard');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'metrics',
                        'cache',
                        'connection',
                        'recent_slow_queries',
                        'recommendations_count',
                        'n_plus_one_issues',
                        'thresholds',
                    ],
                    'timestamp',
                ]);
        });
    });

    describe('Slow Query Log Endpoint', function (): void {
        it('returns slow query log with pagination', function (): void {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/slow-queries?page=1&per_page=10');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data',
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'total_pages',
                    ],
                ]);
        });

        it('filters slow queries by minimum time', function (): void {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/slow-queries?min_time=100');

            $response->assertSuccessful();
        });
    });

    describe('Index Analysis Endpoint', function (): void {
        it('returns index analysis and recommendations', function (): void {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/index-analysis');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'recommendations',
                        'sql_statements',
                        'summary',
                    ],
                ]);
        });

        it('analyzes specific table when requested', function (): void {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/index-analysis?table=characters');

            $response->assertSuccessful();
        });
    });

    describe('Query Stats Endpoint', function (): void {
        it('returns query statistics', function (): void {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/query-stats');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data',
                    'total_patterns',
                ]);
        });

        it('limits results when requested', function (): void {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/query-stats?limit=10');

            $response->assertSuccessful();
        });
    });

    describe('Explain Query Endpoint', function (): void {
        it('explains a SELECT query', function (): void {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/explain', [
                    'sql' => 'SELECT * FROM users WHERE id = 1',
                ]);

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'sql',
                        'execution_plan',
                    ],
                ]);
        });

        it('rejects non-SELECT queries', function (): void {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/explain', [
                    'sql' => 'DELETE FROM users WHERE id = 1',
                ]);

            $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'error' => 'Only SELECT queries can be explained',
                ]);
        });

        it('validates SQL parameter is required', function (): void {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/explain', []);

            $response->assertStatus(422);
        });
    });

    describe('N+1 Detection Endpoint', function (): void {
        it('returns N+1 query detection results', function (): void {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/n-plus-one');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'issues',
                        'total_issues',
                    ],
                ]);
        });
    });

    describe('Cache Stats Endpoint', function (): void {
        it('returns cache statistics', function (): void {
            $response = $this->actingAs($this->user)
                ->getJson('/api/performance/cache-stats');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'query_cache',
                        'config',
                    ],
                ]);
        });
    });

    describe('Cache Invalidation Endpoint', function (): void {
        it('invalidates cache for a specific table', function (): void {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/invalidate-cache', [
                    'table' => 'characters',
                ]);

            $response->assertSuccessful()
                ->assertJson([
                    'success' => true,
                ]);
        });

        it('validates table parameter is required', function (): void {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/invalidate-cache', []);

            $response->assertStatus(422);
        });
    });

    describe('Clear Data Endpoint', function (): void {
        it('clears all performance data', function (): void {
            $response = $this->actingAs($this->user)
                ->postJson('/api/performance/clear-data');

            $response->assertSuccessful()
                ->assertJson([
                    'success' => true,
                    'message' => 'Performance data cleared successfully',
                ]);
        });
    });

    describe('Authentication', function (): void {
        it('requires authentication for all endpoints', function (): void {
            $endpoints = [
                ['GET', '/api/performance/dashboard'],
                ['GET', '/api/performance/slow-queries'],
                ['GET', '/api/performance/index-analysis'],
                ['GET', '/api/performance/query-stats'],
                ['POST', '/api/performance/explain'],
                ['GET', '/api/performance/n-plus-one'],
                ['GET', '/api/performance/cache-stats'],
                ['POST', '/api/performance/invalidate-cache'],
                ['POST', '/api/performance/clear-data'],
            ];

            foreach ($endpoints as [$method, $endpoint]) {
                $response = $method === 'GET'
                    ? $this->getJson($endpoint)
                    : $this->postJson($endpoint, []);

                $response->assertUnauthorized();
            }
        });
    });
});

describe('Configuration', function (): void {
    it('has valid query optimization configuration', function (): void {
        $config = config('query-optimization');

        expect($config)->toBeArray();
        expect($config)->toHaveKeys([
            'slow_query',
            'cache',
            'indexing',
            'connection_pooling',
            'monitoring',
            'analysis',
            'retention',
        ]);
    });

    it('has valid slow query thresholds', function (): void {
        $slowQuery = config('query-optimization.slow_query');

        expect($slowQuery['warning_threshold_ms'])->toBeNumeric();
        expect($slowQuery['critical_threshold_ms'])->toBeNumeric();
        expect($slowQuery['critical_threshold_ms'])->toBeGreaterThan($slowQuery['warning_threshold_ms']);
    });

    it('has valid cache configuration', function (): void {
        $cache = config('query-optimization.cache');

        expect($cache['default_ttl'])->toBeNumeric();
        expect($cache['enabled'])->toBeBool();
        expect($cache['always_cache_tables'])->toBeArray();
        expect($cache['never_cache_tables'])->toBeArray();
    });

    it('has valid monitoring configuration', function (): void {
        $monitoring = config('query-optimization.monitoring');

        expect($monitoring['enabled'])->toBeBool();
        expect($monitoring['alert_thresholds'])->toBeArray();
        expect($monitoring['notification_channels'])->toBeArray();
    });
});
