<?php

declare(strict_types=1);

namespace Tests\Integration\ExternalAPI;

use App\Services\ExternalAPI\APIPerformanceMetricsService;
use App\Services\ExternalAPI\CacheManagerService;
use App\Services\ExternalAPI\ExternalAPIService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Mockery;

/**
 * Test implementation for failover performance testing
 */
class FailoverTestAPIService extends ExternalAPIService
{
    protected function initializeApiSources(): void
    {
        $this->apiSources = [
            'primary' => [
                'priority' => 1,
                'base_url' => 'https://api.primary.test',
                'timeout' => 5,
                'rate_limit' => 100,
                'enabled' => true,
            ],
            'secondary' => [
                'priority' => 2,
                'base_url' => 'https://api.secondary.test',
                'timeout' => 5,
                'rate_limit' => 60,
                'enabled' => true,
            ],
            'tertiary' => [
                'priority' => 3,
                'base_url' => 'https://api.tertiary.test',
                'timeout' => 5,
                'rate_limit' => 30,
                'enabled' => true,
            ],
        ];
    }

    public function testFetchWithFallback(string $endpoint): array
    {
        return $this->fetchWithFallback($endpoint);
    }

    public function testIncrementCircuitBreaker(string $sourceName): void
    {
        $this->incrementCircuitBreaker($sourceName);
    }

    public function testIsCircuitBreakerOpen(string $sourceName): bool
    {
        return $this->isCircuitBreakerOpen($sourceName);
    }
}

beforeEach(function () {
    Cache::flush();

    Config::set('mcp.enabled', true);
    Config::set('mcp.servers', [
        'fetch' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['mcp-server-fetch'],
            'capabilities' => ['http_client'],
        ],
    ]);

    $this->mcpClient = new MCPClientService;
    $this->mcpClient->healthCheck();

    $this->metricsService = Mockery::mock(APIPerformanceMetricsService::class);
    $this->metricsService->shouldReceive('recordResponseTime')->byDefault();
    $this->metricsService->shouldReceive('recordError')->byDefault();

    $this->apiService = new FailoverTestAPIService($this->mcpClient, $this->metricsService);
    $this->cacheManager = new CacheManagerService;
});

afterEach(function () {
    Cache::flush();
    Mockery::close();
});

describe('Failover Performance', function () {
    describe('Circuit Breaker Performance', function () {
        it('detects circuit breaker status quickly', function () {
            $startTime = microtime(true);
            $isOpen = $this->apiService->testIsCircuitBreakerOpen('primary');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($isOpen)->toBeBool()
                ->and($duration)->toBeLessThan(50); // Should be very fast
        });

        it('increments circuit breaker efficiently', function () {
            $startTime = microtime(true);

            for ($i = 0; $i < 5; $i++) {
                $this->apiService->testIncrementCircuitBreaker('primary');
            }

            $duration = (microtime(true) - $startTime) * 1000;

            expect($duration)->toBeLessThan(200); // 5 increments in under 200ms
        });

        it('retrieves circuit breaker status for all sources quickly', function () {
            // Open circuit breaker for one source
            for ($i = 0; $i < 5; $i++) {
                $this->apiService->testIncrementCircuitBreaker('primary');
            }

            $startTime = microtime(true);
            $status = $this->apiService->getCircuitBreakerStatus();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($status)->toBeArray()
                ->and($status)->toHaveKey('primary')
                ->and($status)->toHaveKey('secondary')
                ->and($duration)->toBeLessThan(100);
        });
    });

    describe('Health Check Performance', function () {
        it('performs health check within acceptable time', function () {
            $startTime = microtime(true);
            $health = $this->apiService->getHealthStatus();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($health)->toBeArray()
                ->and($duration)->toBeLessThan(200); // Health check should be fast
        });

        it('handles multiple health checks efficiently', function () {
            $startTime = microtime(true);

            for ($i = 0; $i < 5; $i++) {
                $this->apiService->getHealthStatus();
            }

            $duration = (microtime(true) - $startTime) * 1000;

            expect($duration)->toBeLessThan(500); // 5 health checks in under 500ms
        });
    });

    describe('Source Management Performance', function () {
        it('enables and disables sources quickly', function () {
            $startTime = microtime(true);

            $this->apiService->disableSource('primary');
            $this->apiService->enableSource('primary');

            $duration = (microtime(true) - $startTime) * 1000;

            expect($duration)->toBeLessThan(50); // Should be very fast
        });

        it('retrieves API sources configuration efficiently', function () {
            $startTime = microtime(true);
            $sources = $this->apiService->getApiSources();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($sources)->toBeArray()
                ->and($duration)->toBeLessThan(10); // Config retrieval should be instant
        });
    });

    describe('MCP Server Failover', function () {
        it('detects server health status quickly', function () {
            $startTime = microtime(true);
            $isHealthy = $this->mcpClient->isServerHealthy('fetch');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($isHealthy)->toBeBool()
                ->and($duration)->toBeLessThan(50);
        });

        it('performs health check on all servers efficiently', function () {
            $startTime = microtime(true);
            $results = $this->mcpClient->healthCheck();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($results)->toBeArray()
                ->and($duration)->toBeLessThan(1000); // All servers checked in under 1 second
        });

        it('resets server health quickly', function () {
            $this->mcpClient->healthCheck();

            $startTime = microtime(true);
            $this->mcpClient->resetServerHealth('fetch');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($duration)->toBeLessThan(50);
        });

        it('retrieves all server health status efficiently', function () {
            $this->mcpClient->healthCheck();

            $startTime = microtime(true);
            $health = $this->mcpClient->getAllServerHealth();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($health)->toBeArray()
                ->and($duration)->toBeLessThan(100);
        });
    });

    describe('Cache Failover Performance', function () {
        it('falls back to cache quickly when API unavailable', function () {
            $key = 'character_data:failover_test';
            $cachedData = ['name' => 'Cached Character', 'speed' => 100];

            // Pre-populate cache
            $this->cacheManager->put($key, $cachedData);

            // Measure cache retrieval as fallback
            $startTime = microtime(true);
            $result = $this->cacheManager->get($key);
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result)->toBeArray()
                ->and($result['name'])->toBe('Cached Character')
                ->and($duration)->toBeLessThan(100); // Cache fallback should be very fast
        });

        it('handles cache miss detection quickly', function () {
            $startTime = microtime(true);
            $result = $this->cacheManager->get('non_existent_key');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result)->toBeNull()
                ->and($duration)->toBeLessThan(50); // Miss detection should be instant
        });

        it('checks cache existence efficiently', function () {
            $this->cacheManager->put('test:exists', ['data' => 'value']);

            $startTime = microtime(true);
            $exists = $this->cacheManager->has('test:exists');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($exists)->toBeTrue()
                ->and($duration)->toBeLessThan(50);
        });
    });

    describe('Recovery Performance', function () {
        it('recovers from circuit breaker state efficiently', function () {
            // Open circuit breaker
            for ($i = 0; $i < 5; $i++) {
                $this->apiService->testIncrementCircuitBreaker('primary');
            }

            expect($this->apiService->testIsCircuitBreakerOpen('primary'))->toBeTrue();

            // Measure recovery time (cache clear simulates timeout)
            $startTime = microtime(true);
            Cache::forget('circuit_breaker:primary');
            $isOpen = $this->apiService->testIsCircuitBreakerOpen('primary');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($isOpen)->toBeFalse()
                ->and($duration)->toBeLessThan(100); // Recovery should be fast
        });

        it('handles rapid state transitions efficiently', function () {
            $startTime = microtime(true);

            // Rapid enable/disable cycles
            for ($i = 0; $i < 10; $i++) {
                $this->apiService->disableSource('secondary');
                $this->apiService->enableSource('secondary');
            }

            $duration = (microtime(true) - $startTime) * 1000;

            expect($duration)->toBeLessThan(500); // 20 state changes in under 500ms
        });
    });

    describe('Monitoring Performance', function () {
        it('retrieves comprehensive cache info efficiently', function () {
            // Generate some cache activity
            for ($i = 1; $i <= 10; $i++) {
                $this->cacheManager->put("monitor:item{$i}", ['id' => $i]);
            }

            $startTime = microtime(true);
            $info = $this->cacheManager->getCacheInfo();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($info)->toBeArray()
                ->and($info)->toHaveKey('statistics')
                ->and($info)->toHaveKey('ttl_config')
                ->and($duration)->toBeLessThan(200);
        });

        it('calculates cache statistics quickly', function () {
            $this->cacheManager->resetStatistics();

            // Generate activity
            for ($i = 1; $i <= 10; $i++) {
                $this->cacheManager->put("stats:item{$i}", ['id' => $i]);
                $this->cacheManager->get("stats:item{$i}");
            }

            $startTime = microtime(true);
            $stats = $this->cacheManager->getStatistics();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($stats)->toBeArray()
                ->and($stats)->toHaveKey('hit_rate')
                ->and($duration)->toBeLessThan(100);
        });
    });
});
