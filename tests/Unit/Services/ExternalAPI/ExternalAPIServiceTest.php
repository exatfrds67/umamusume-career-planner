<?php

declare(strict_types=1);

namespace Tests\Unit\Services\ExternalAPI;

use App\Services\ExternalAPI\ExternalAPIService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;

/**
 * Test implementation of ExternalAPIService for testing
 */
class TestExternalAPIService extends ExternalAPIService
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
            'disabled' => [
                'priority' => 4,
                'base_url' => 'https://api.disabled.test',
                'timeout' => 5,
                'rate_limit' => 30,
                'enabled' => false,
            ],
        ];
    }

    // Expose protected methods for testing
    public function testFetchWithFallback(string $endpoint, string $method = 'GET', array $params = [], ?string $preferredSource = null): array
    {
        return $this->fetchWithFallback($endpoint, $method, $params, $preferredSource);
    }

    public function testFetchFromSource(string $sourceName, string $endpoint, string $method = 'GET', array $params = []): array
    {
        return $this->fetchFromSource($sourceName, $endpoint, $method, $params);
    }

    public function testCheckRateLimit(string $sourceName, int $maxAttempts): bool
    {
        return $this->checkRateLimit($sourceName, $maxAttempts);
    }

    public function testIsCircuitBreakerOpen(string $sourceName): bool
    {
        return $this->isCircuitBreakerOpen($sourceName);
    }

    public function testIncrementCircuitBreaker(string $sourceName): void
    {
        $this->incrementCircuitBreaker($sourceName);
    }

    public function testResetCircuitBreaker(string $sourceName): void
    {
        $this->resetCircuitBreaker($sourceName);
    }
}

beforeEach(function () {
    // Clear cache and rate limiters before each test
    Cache::flush();
    RateLimiter::clear('api_rate_limit:primary');
    RateLimiter::clear('api_rate_limit:secondary');
    RateLimiter::clear('api_rate_limit:tertiary');

    // Mock MCP client
    $this->mcpClient = Mockery::mock(MCPClientService::class);

    // Mock API Performance Metrics Service
    $this->metricsService = Mockery::mock(\App\Services\ExternalAPI\APIPerformanceMetricsService::class);
    $this->metricsService->shouldReceive('recordResponseTime')->byDefault();
    $this->metricsService->shouldReceive('recordError')->byDefault();

    $this->service = new TestExternalAPIService($this->mcpClient, $this->metricsService);
});

afterEach(function () {
    Mockery::close();
});

describe('API Source Configuration', function () {
    it('initializes API sources correctly', function () {
        $sources = $this->service->getApiSources();

        expect($sources)->toHaveCount(4)
            ->and($sources['primary'])->toHaveKey('priority', 1)
            ->and($sources['primary'])->toHaveKey('base_url', 'https://api.primary.test')
            ->and($sources['primary'])->toHaveKey('enabled', true);
    });

    it('returns sorted API sources by priority', function () {
        $sources = $this->service->getApiSources();
        $priorities = array_column($sources, 'priority');

        expect($priorities)->toBe([1, 2, 3, 4]);
    });

    it('can enable a disabled source', function () {
        $result = $this->service->enableSource('disabled');

        expect($result)->toBeTrue();

        $sources = $this->service->getApiSources();
        expect($sources['disabled']['enabled'])->toBeTrue();
    });

    it('can disable an enabled source', function () {
        $result = $this->service->disableSource('primary');

        expect($result)->toBeTrue();

        $sources = $this->service->getApiSources();
        expect($sources['primary']['enabled'])->toBeFalse();
    });

    it('returns false when enabling non-existent source', function () {
        $result = $this->service->enableSource('nonexistent');

        expect($result)->toBeFalse();
    });

    it('returns false when disabling non-existent source', function () {
        $result = $this->service->disableSource('nonexistent');

        expect($result)->toBeFalse();
    });
});

describe('Rate Limiting', function () {
    it('allows requests within rate limit', function () {
        $result = $this->service->testCheckRateLimit('primary', 100);

        expect($result)->toBeTrue();
    });

    it('blocks requests exceeding rate limit', function () {
        // Exhaust rate limit
        for ($i = 0; $i < 100; $i++) {
            $this->service->testCheckRateLimit('primary', 100);
        }

        // Next request should be blocked
        $result = $this->service->testCheckRateLimit('primary', 100);

        expect($result)->toBeFalse();
    });

    it('tracks rate limits per source independently', function () {
        // Exhaust primary rate limit
        for ($i = 0; $i < 100; $i++) {
            $this->service->testCheckRateLimit('primary', 100);
        }

        // Secondary should still allow requests
        $result = $this->service->testCheckRateLimit('secondary', 60);

        expect($result)->toBeTrue();
    });
});

describe('Circuit Breaker', function () {
    it('starts with circuit breaker closed', function () {
        $isOpen = $this->service->testIsCircuitBreakerOpen('primary');

        expect($isOpen)->toBeFalse();
    });

    it('opens circuit breaker after threshold failures', function () {
        // Increment failures to threshold
        for ($i = 0; $i < 5; $i++) {
            $this->service->testIncrementCircuitBreaker('primary');
        }

        $isOpen = $this->service->testIsCircuitBreakerOpen('primary');

        expect($isOpen)->toBeTrue();
    });

    it('resets circuit breaker on success', function () {
        // Increment failures
        for ($i = 0; $i < 3; $i++) {
            $this->service->testIncrementCircuitBreaker('primary');
        }

        // Reset circuit breaker
        $this->service->testResetCircuitBreaker('primary');

        $isOpen = $this->service->testIsCircuitBreakerOpen('primary');

        expect($isOpen)->toBeFalse();
    });

    it('tracks circuit breaker status for all sources', function () {
        // Open circuit breaker for primary
        for ($i = 0; $i < 5; $i++) {
            $this->service->testIncrementCircuitBreaker('primary');
        }

        $status = $this->service->getCircuitBreakerStatus();

        expect($status)->toHaveKey('primary')
            ->and($status['primary']['is_open'])->toBeTrue()
            ->and($status['primary']['failures'])->toBe(5)
            ->and($status['secondary']['is_open'])->toBeFalse();
    });
});

describe('Request Logging', function () {
    it('logs successful requests', function () {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'API Request') &&
                    $context['source'] === 'primary' &&
                    $context['status'] === 'started';
            });

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'API Response Success') &&
                    $context['source'] === 'primary' &&
                    $context['status'] === 'success';
            });

        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')->andReturn([
            'success' => true,
            'status' => 200,
            'headers' => [],
            'body' => json_encode(['data' => 'test']),
        ]);

        $this->service->testFetchFromSource('primary', '/test');
    });

    it('logs failed requests', function () {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'API Request');
            });

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'API Response Failed') &&
                    $context['source'] === 'primary' &&
                    $context['status'] === 'failed';
            });

        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')->andThrow(new \RuntimeException('Connection failed'));

        $this->service->testFetchFromSource('primary', '/test');
    });

    it('logs rate limited requests', function () {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'API Request') &&
                    $context['status'] === 'rate_limited';
            });

        // Exhaust rate limit
        for ($i = 0; $i < 100; $i++) {
            $this->service->testCheckRateLimit('primary', 100);
        }

        $this->service->testFetchFromSource('primary', '/test');
    });
});

describe('Timeout Handling', function () {
    it('uses configured timeout for requests', function () {
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')
            ->withArgs(function ($url, $method, $params, $headers, $timeout) {
                return $timeout === 5; // Default timeout from config
            })
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => [],
                'body' => json_encode(['data' => 'test']),
            ]);

        Log::shouldReceive('info')->twice();

        $this->service->testFetchFromSource('primary', '/test');
    });

    it('handles timeout exceptions', function () {
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')->andThrow(new \RuntimeException('Request timeout'));

        Log::shouldReceive('info')->once();
        Log::shouldReceive('warning')->once();

        $result = $this->service->testFetchFromSource('primary', '/test');

        expect($result['success'])->toBeFalse()
            ->and($result['error'])->toContain('timeout');
    });
});

describe('Automatic Failover', function () {
    it('falls back to secondary source when primary fails', function () {
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);

        // Primary fails
        $this->mcpClient->shouldReceive('fetch')
            ->once()
            ->with(Mockery::pattern('/primary/'), Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any())
            ->andThrow(new \RuntimeException('Primary failed'));

        // Secondary succeeds
        $this->mcpClient->shouldReceive('fetch')
            ->once()
            ->with(Mockery::pattern('/secondary/'), Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any())
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => [],
                'body' => json_encode(['data' => 'test']),
            ]);

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('warning')->atLeast()->once();

        $result = $this->service->testFetchWithFallback('/test');

        expect($result['success'])->toBeTrue()
            ->and($result['source'])->toBe('secondary');
    });

    it('tries all sources before failing', function () {
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);

        // All sources fail
        $this->mcpClient->shouldReceive('fetch')
            ->times(3) // primary, secondary, tertiary (disabled is skipped)
            ->andThrow(new \RuntimeException('API failed'));

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('warning')->atLeast()->once();
        Log::shouldReceive('error')->once();

        $result = $this->service->testFetchWithFallback('/test');

        expect($result['success'])->toBeFalse()
            ->and($result['source'])->toBe('none')
            ->and($result['error'])->toContain('All API sources failed');
    });

    it('skips disabled sources during fallback', function () {
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);

        // All enabled sources fail
        $this->mcpClient->shouldReceive('fetch')
            ->times(3) // Only enabled sources
            ->andThrow(new \RuntimeException('API failed'));

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('warning')->atLeast()->once();
        Log::shouldReceive('error')->once();

        $result = $this->service->testFetchWithFallback('/test');

        expect($result['metadata']['attempted_sources'])->not->toContain('disabled');
    });

    it('skips sources with open circuit breaker', function () {
        // Open circuit breaker for primary
        for ($i = 0; $i < 5; $i++) {
            $this->service->testIncrementCircuitBreaker('primary');
        }

        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);

        // Secondary succeeds (primary is skipped)
        $this->mcpClient->shouldReceive('fetch')
            ->once()
            ->with(Mockery::pattern('/secondary/'), Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any())
            ->andReturn([
                'success' => true,
                'status' => 200,
                'headers' => [],
                'body' => json_encode(['data' => 'test']),
            ]);

        Log::shouldReceive('info')->atLeast()->once();
        Log::shouldReceive('debug')->once();

        $result = $this->service->testFetchWithFallback('/test');

        expect($result['success'])->toBeTrue()
            ->and($result['source'])->toBe('secondary');
    });
});

describe('Health Status', function () {
    it('returns health status for all sources', function () {
        $status = $this->service->getHealthStatus();

        expect($status)->toHaveCount(4)
            ->and($status['primary']['enabled'])->toBeTrue()
            ->and($status['primary']['circuit_breaker_open'])->toBeFalse()
            ->and($status['primary']['priority'])->toBe(1)
            ->and($status['disabled']['enabled'])->toBeFalse();
    });

    it('reflects circuit breaker status in health check', function () {
        // Open circuit breaker for primary
        for ($i = 0; $i < 5; $i++) {
            $this->service->testIncrementCircuitBreaker('primary');
        }

        $status = $this->service->getHealthStatus();

        expect($status['primary']['circuit_breaker_open'])->toBeTrue();
    });
});

describe('Response Metadata', function () {
    it('includes response time in metadata', function () {
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')->andReturn([
            'success' => true,
            'status' => 200,
            'headers' => [],
            'body' => json_encode(['data' => 'test']),
        ]);

        Log::shouldReceive('info')->twice();

        $result = $this->service->testFetchFromSource('primary', '/test');

        expect($result['metadata'])->toHaveKey('response_time_ms')
            ->and($result['metadata']['response_time_ms'])->toBeGreaterThanOrEqual(0);
    });

    it('includes status code in metadata', function () {
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')->andReturn([
            'success' => true,
            'status' => 200,
            'headers' => [],
            'body' => json_encode(['data' => 'test']),
        ]);

        Log::shouldReceive('info')->twice();

        $result = $this->service->testFetchFromSource('primary', '/test');

        expect($result['metadata'])->toHaveKey('status_code')
            ->and($result['metadata']['status_code'])->toBe(200);
    });

    it('includes fetched timestamp in metadata', function () {
        $this->mcpClient->shouldReceive('isFetchAvailable')->andReturn(true);
        $this->mcpClient->shouldReceive('fetch')->andReturn([
            'success' => true,
            'status' => 200,
            'headers' => [],
            'body' => json_encode(['data' => 'test']),
        ]);

        Log::shouldReceive('info')->twice();

        $result = $this->service->testFetchFromSource('primary', '/test');

        expect($result['metadata'])->toHaveKey('fetched_at')
            ->and($result['metadata']['fetched_at'])->toBeString();
    });
});
