<?php

declare(strict_types=1);

namespace Tests\Support\ExternalAPI;

use App\Services\ExternalAPI\ExternalAPIService;

/**
 * Test implementation of ExternalAPIService for testing.
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

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function testFetchWithFallback(string $endpoint, string $method = 'GET', array $params = [], ?string $preferredSource = null): array
    {
        return $this->fetchWithFallback($endpoint, $method, $params, $preferredSource);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
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
