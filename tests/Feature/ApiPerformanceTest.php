<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\ApiPerformanceMonitoringService;
use App\Services\ApiResponseCachingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * API Performance Optimization Tests
 *
 * Tests for API performance optimization and monitoring features including:
 * - Response compression
 * - API response caching with intelligent invalidation
 * - Rate limiting for different user tiers
 * - Performance monitoring with bottleneck identification
 *
 * @see Requirements: 52.3, 52.4
 * @see Task: 6.1.4 API performance optimization and monitoring
 */
beforeEach(function () {
    // Clear cache before each test
    Cache::flush();
});

describe('API Performance Monitoring Service', function () {
    it('records request start and end correctly', function () {
        $service = app(ApiPerformanceMonitoringService::class);

        $requestId = 'test-request-'.uniqid();
        $metadata = [
            'method' => 'GET',
            'path' => 'api/test',
            'user_id' => 1,
            'ip' => '127.0.0.1',
        ];

        // Record request start
        $service->recordRequestStart($requestId, $metadata);

        // Simulate some processing time
        usleep(10000); // 10ms

        // Record request end
        $service->recordRequestEnd($requestId, [
            'status_code' => 200,
            'duration_ms' => 15.5,
            'memory_bytes' => 1024,
            'response_size' => 512,
            'compressed' => false,
        ]);

        // Verify metrics were recorded
        $overview = $service->getOverviewMetrics();
        expect($overview)->toBeArray();
        expect($overview)->toHaveKey('total_requests');
    });

    it('returns overview metrics with correct structure', function () {
        $service = app(ApiPerformanceMonitoringService::class);

        $overview = $service->getOverviewMetrics();

        expect($overview)->toBeArray();
        expect($overview)->toHaveKeys([
            'total_requests',
            'avg_response_time_ms',
            'p95_response_time_ms',
            'p99_response_time_ms',
            'error_rate',
            'slow_request_rate',
            'requests_per_minute',
        ]);
    });

    it('identifies bottlenecks correctly', function () {
        $service = app(ApiPerformanceMonitoringService::class);

        $bottlenecks = $service->identifyBottlenecks();

        expect($bottlenecks)->toBeArray();

        // Each bottleneck should have required fields
        foreach ($bottlenecks as $bottleneck) {
            expect($bottleneck)->toHaveKeys([
                'type',
                'severity',
                'endpoint',
                'description',
                'recommendation',
                'metrics',
            ]);
            expect($bottleneck['severity'])->toBeIn(['critical', 'warning', 'info']);
        }
    });

    it('returns performance trends with hourly and daily data', function () {
        $service = app(ApiPerformanceMonitoringService::class);

        $trends = $service->getPerformanceTrends();

        expect($trends)->toBeArray();
        expect($trends)->toHaveKeys(['hourly', 'daily']);
        expect($trends['hourly'])->toBeArray();
        expect($trends['daily'])->toBeArray();
    });

    it('returns resource utilization metrics', function () {
        $service = app(ApiPerformanceMonitoringService::class);

        $resources = $service->getResourceUtilization();

        expect($resources)->toBeArray();
        expect($resources)->toHaveKeys(['memory', 'cpu', 'database']);
        expect($resources['memory'])->toHaveKeys(['current_mb', 'peak_mb', 'limit_mb']);
    });

    it('clears metrics successfully', function () {
        $service = app(ApiPerformanceMonitoringService::class);

        // This should not throw an exception
        $service->clearMetrics();

        // Verify metrics are cleared
        $overview = $service->getOverviewMetrics();
        expect($overview['total_requests'])->toBe(0);
    });
});

describe('API Response Caching Service', function () {
    it('generates consistent cache keys for same requests', function () {
        $service = app(ApiResponseCachingService::class);

        $request1 = Request::create('/api/test', 'GET', ['param' => 'value']);
        $request2 = Request::create('/api/test', 'GET', ['param' => 'value']);

        $key1 = $service->generateCacheKey($request1);
        $key2 = $service->generateCacheKey($request2);

        expect($key1)->toBe($key2);
    });

    it('generates different cache keys for different requests', function () {
        $service = app(ApiResponseCachingService::class);

        $request1 = Request::create('/api/test', 'GET', ['param' => 'value1']);
        $request2 = Request::create('/api/test', 'GET', ['param' => 'value2']);

        $key1 = $service->generateCacheKey($request1);
        $key2 = $service->generateCacheKey($request2);

        expect($key1)->not->toBe($key2);
    });

    it('stores and retrieves cached responses', function () {
        $service = app(ApiResponseCachingService::class);

        $request = Request::create('/api/test', 'GET');
        $responseData = ['success' => true, 'data' => ['test' => 'value']];

        // Store response
        $stored = $service->put($request, $responseData, 300);
        expect($stored)->toBeTrue();

        // Check if cached
        expect($service->has($request))->toBeTrue();

        // Retrieve cached response
        $cached = $service->get($request);
        expect($cached)->not->toBeNull();
        expect($cached['data'])->toBe($responseData);
        expect($cached['metadata'])->toHaveKeys(['cached_at', 'ttl', 'cache_key']);
    });

    it('invalidates cache by tags', function () {
        $service = app(ApiResponseCachingService::class);

        $request = Request::create('/api/characters/1', 'GET');
        $responseData = ['success' => true, 'data' => ['id' => 1]];

        // Store with tags
        $service->put($request, $responseData, 300, ['characters', 'user_1']);

        // Verify cached
        expect($service->has($request))->toBeTrue();

        // Invalidate by tags
        $result = $service->invalidateByTags(['characters']);
        expect($result['invalidated'])->toBeTrue();
    });

    it('returns cache statistics', function () {
        $service = app(ApiResponseCachingService::class);

        $stats = $service->getStatistics();

        expect($stats)->toBeArray();
        expect($stats)->toHaveKeys([
            'hit_rate',
            'hits',
            'misses',
            'stores',
            'total_size_bytes',
            'by_endpoint',
        ]);
    });

    it('warms cache for specified endpoints', function () {
        $service = app(ApiResponseCachingService::class);

        $endpoints = [
            ['method' => 'GET', 'path' => '/api/skills'],
            ['method' => 'GET', 'path' => '/api/support-cards'],
        ];

        $result = $service->warmCache($endpoints);

        expect($result)->toHaveKeys(['warmed', 'failed', 'details']);
        expect($result['warmed'] + $result['failed'])->toBe(count($endpoints));
    });
});

describe('API Performance Controller Endpoints', function () {
    it('returns API performance dashboard', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/performance/api/dashboard');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'overview',
                'endpoints',
                'bottlenecks',
                'trends',
            ],
            'timestamp',
        ]);
    });

    it('returns API performance overview', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/performance/api/overview');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_requests',
                'avg_response_time_ms',
                'error_rate',
            ],
            'timestamp',
        ]);
    });

    it('returns endpoint metrics', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/performance/api/endpoints');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'endpoints',
                'count',
            ],
            'timestamp',
        ]);
    });

    it('returns bottleneck analysis', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/performance/api/bottlenecks');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'bottlenecks',
                'count',
                'by_severity',
            ],
            'timestamp',
        ]);
    });

    it('returns slow requests', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/performance/api/slow-requests?limit=10');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'slow_requests',
                'count',
                'threshold_ms',
            ],
            'timestamp',
        ]);
    });

    it('returns performance trends', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/performance/api/trends');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'hourly',
                'daily',
            ],
            'timestamp',
        ]);
    });

    it('returns batching recommendations', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/performance/api/batching-recommendations');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'recommendations',
                'count',
            ],
            'timestamp',
        ]);
    });

    it('returns resource utilization', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/performance/api/resources');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'memory',
                'cpu',
                'database',
            ],
            'timestamp',
        ]);
    });

    it('clears API metrics', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/performance/api/clear');

        $response->assertSuccessful();
        $response->assertJson([
            'success' => true,
            'message' => 'API performance metrics cleared successfully',
        ]);
    });

    it('returns API configuration', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/performance/api/config');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'compression',
                'rate_limiting',
                'caching',
                'monitoring',
                'batching',
            ],
            'timestamp',
        ]);
    });
});

describe('API Cache Controller Endpoints', function () {
    it('returns API cache statistics', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/performance/api-cache/stats');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'hit_rate',
                'hits',
                'misses',
            ],
            'timestamp',
        ]);
    });

    it('invalidates API cache by tags', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/performance/api-cache/invalidate', [
                'tags' => ['api_response', 'characters'],
            ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'invalidated',
                'tags',
            ],
            'timestamp',
        ]);
    });

    it('invalidates API cache by data type', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/performance/api-cache/invalidate', [
                'data_type' => 'characters',
                'cascade' => true,
            ]);

        $response->assertSuccessful();
    });

    it('warms API cache', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/performance/api-cache/warm', [
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/skills'],
                ],
            ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'warmed',
                'failed',
                'details',
            ],
            'timestamp',
        ]);
    });

    it('requires tags or data_type for invalidation', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/performance/api-cache/invalidate', []);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    });
});

describe('Rate Limiting Status Endpoint', function () {
    it('returns rate limiting status for authenticated user', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/performance/rate-limiting/status');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'current_tier',
                'limits',
                'enabled',
                'endpoint_specific_limits',
            ],
            'timestamp',
        ]);

        // Authenticated user should have 'authenticated' tier
        $response->assertJsonPath('data.current_tier', 'authenticated');
    });
});

describe('API Performance Configuration', function () {
    it('has correct default compression settings', function () {
        expect(config('api-performance.compression.enabled'))->toBeTrue();
        expect(config('api-performance.compression.level'))->toBe(6);
        expect(config('api-performance.compression.min_size'))->toBe(1024);
    });

    it('has correct default rate limiting tiers', function () {
        $tiers = config('api-performance.rate_limiting.tiers');

        expect($tiers)->toHaveKeys(['public', 'authenticated', 'premium', 'admin']);

        // Public tier should have lowest limits
        expect($tiers['public']['requests_per_minute'])->toBeLessThan($tiers['authenticated']['requests_per_minute']);

        // Admin tier should have highest limits
        expect($tiers['admin']['requests_per_minute'])->toBeGreaterThan($tiers['premium']['requests_per_minute']);
    });

    it('has correct default cache settings', function () {
        expect(config('api-performance.cache.enabled'))->toBeTrue();
        expect(config('api-performance.cache.default_ttl'))->toBe(300);
    });

    it('has correct monitoring thresholds', function () {
        expect(config('api-performance.monitoring.enabled'))->toBeTrue();
        expect(config('api-performance.monitoring.slow_request_threshold_ms'))->toBe(1000);
        expect(config('api-performance.monitoring.very_slow_request_threshold_ms'))->toBe(3000);
    });
});
