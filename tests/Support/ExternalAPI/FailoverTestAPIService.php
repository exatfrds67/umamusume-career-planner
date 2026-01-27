<?php

declare(strict_types=1);

namespace Tests\Support\ExternalAPI;

use App\Services\ExternalAPI\ExternalAPIService;

/**
 * Test implementation for failover performance testing.
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

    /**
     * @return array<string, mixed>
     */
    public function testFetchWithFallback(string $endpoint): array
    {
        return $this->fetchWithFallback('GET', $endpoint);
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
