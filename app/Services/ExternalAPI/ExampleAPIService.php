<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

/**
 * Example API Service
 *
 * Demonstrates how to use the ExternalAPIService base class
 * to integrate with multiple external APIs with automatic failover,
 * rate limiting, timeout handling, and request/response logging.
 *
 * This is a reference implementation showing best practices for
 * extending ExternalAPIService.
 *
 * Requirements: 14.1, 14.2, 14.5
 * Task: 1.2.1
 */
class ExampleAPIService extends ExternalAPIService
{
    /**
     * Initialize API sources with priority ordering
     *
     * Configure multiple API sources with:
     * - priority: Lower numbers = higher priority (1 is highest)
     * - base_url: Base URL for the API
     * - timeout: Request timeout in seconds
     * - rate_limit: Maximum requests per minute
     * - enabled: Whether the source is active
     */
    protected function initializeApiSources(): void
    {
        $this->apiSources = [
            'umapyoi' => [
                'priority' => 1,
                'base_url' => config('services.umapyoi.url', 'https://api.umapyoi.net'),
                'timeout' => 5,
                'rate_limit' => 100,
                'enabled' => true,
            ],
            'umamusumedb' => [
                'priority' => 2,
                'base_url' => config('services.umamusumedb.url', 'https://umamusumedb.com/api'),
                'timeout' => 5,
                'rate_limit' => 60,
                'enabled' => true,
            ],
            'umalator' => [
                'priority' => 3,
                'base_url' => config('services.umalator.url', 'https://umalator.com/api'),
                'timeout' => 5,
                'rate_limit' => 30,
                'enabled' => true,
            ],
        ];
    }

    /**
     * Fetch character data with automatic fallback
     *
     * Example usage:
     * $result = $service->fetchCharacter('silence-suzuka');
     *
     * @return array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}
     */
    public function fetchCharacter(string $characterId): array
    {
        return $this->fetchWithFallback("/characters/{$characterId}");
    }

    /**
     * Fetch support card data with automatic fallback
     *
     * @return array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}
     */
    public function fetchSupportCard(string $cardId): array
    {
        return $this->fetchWithFallback("/support-cards/{$cardId}");
    }

    /**
     * Fetch data from a specific source (bypassing fallback)
     *
     * @return array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}
     */
    public function fetchFromSpecificSource(string $sourceName, string $endpoint): array
    {
        return $this->fetchFromSource($sourceName, $endpoint);
    }

    /**
     * Fetch with preferred source and fallback
     *
     * @return array{success: bool, data: mixed, source: string, error?: string, metadata: array<string, mixed>}
     */
    public function fetchWithPreferredSource(string $endpoint, string $preferredSource): array
    {
        return $this->fetchWithFallback($endpoint, 'GET', [], $preferredSource);
    }
}
