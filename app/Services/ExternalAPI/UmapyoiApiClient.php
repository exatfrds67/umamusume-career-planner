<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\CacheManagementService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Umapyoi.net API Client with MCP Fetch Integration
 *
 * Provides access to Uma Musume character, support card, and news data
 * from umapyoi.net API with MCP-enhanced HTTP client capabilities and
 * intelligent caching.
 *
 * Requirements: 14.1, 14.5, 55.3, 56.4, Task 4.4.1, Task 4.4.2
 */
class UmapyoiApiClient
{
    /**
     * Base URL for umapyoi.net API
     */
    protected string $baseUrl;

    /**
     * API timeout in seconds
     */
    protected int $timeout;

    /**
     * Cache TTL in seconds (24 hours)
     */
    protected const CACHE_TTL = 86400;

    /**
     * Cache prefix
     */
    protected const CACHE_PREFIX = 'umapyoi:';

    /**
     * Maximum retry attempts
     */
    protected const MAX_RETRIES = 3;

    /**
     * Retry delay in milliseconds
     */
    protected const RETRY_DELAY = 1000;

    public function __construct(
        protected MCPClientService $mcpClient,
        protected CacheManagementService $cacheManager
    ) {
        $this->baseUrl = (string) config('services.umapyoi.url', 'https://api.umapyoi.net');
        $this->timeout = (int) config('services.umapyoi.timeout', 30);
    }

    /**
     * Fetch all characters from umapyoi.net
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getCharacters(bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX.'characters';

        // Use cache manager for intelligent caching
        return $this->cacheManager->remember(
            $cacheKey,
            function () {
                $startTime = microtime(true);

                try {
                    $response = $this->makeRequest('GET', '/v1/characters');

                    // Record API response time
                    $responseTime = (microtime(true) - $startTime) * 1000;
                    $this->cacheManager->recordApiResponseTime('umapyoi_characters', $responseTime);

                    if (! $response['success']) {
                        throw new \RuntimeException($response['error'] ?? 'Unknown error');
                    }

                    $characters = $response['data']['characters'] ?? [];

                    Log::info('[UmapyoiApiClient] Characters fetched successfully', [
                        'count' => count($characters),
                        'source' => 'api',
                        'response_time_ms' => round($responseTime, 2),
                    ]);

                    return [
                        'success' => true,
                        'data' => $characters,
                        'source' => 'api',
                    ];
                } catch (\Exception $e) {
                    Log::error('[UmapyoiApiClient] Failed to fetch characters', [
                        'error' => $e->getMessage(),
                    ]);

                    return [
                        'success' => false,
                        'data' => [],
                        'source' => 'error',
                        'error' => $e->getMessage(),
                    ];
                }
            },
            self::CACHE_TTL
        );
    }

    /**
     * Fetch a specific character by ID
     *
     * @return array{success: bool, data: array<string, mixed>|null, source: string, error?: string}
     */
    public function getCharacter(string $characterId, bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX."character:{$characterId}";

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return [
                'success' => true,
                'data' => Cache::get($cacheKey),
                'source' => 'cache',
            ];
        }

        try {
            $response = $this->makeRequest('GET', "/v1/characters/{$characterId}");

            if (! $response['success']) {
                throw new \RuntimeException($response['error'] ?? 'Unknown error');
            }

            $character = $response['data']['character'] ?? null;

            if ($character) {
                Cache::put($cacheKey, $character, self::CACHE_TTL);
            }

            return [
                'success' => true,
                'data' => $character,
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            Log::error('[UmapyoiApiClient] Failed to fetch character', [
                'character_id' => $characterId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'source' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch all support cards from umapyoi.net
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getSupportCards(bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX.'support_cards';

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return [
                'success' => true,
                'data' => Cache::get($cacheKey, []),
                'source' => 'cache',
            ];
        }

        try {
            $response = $this->makeRequest('GET', '/v1/support-cards');

            if (! $response['success']) {
                throw new \RuntimeException($response['error'] ?? 'Unknown error');
            }

            $cards = $response['data']['support_cards'] ?? [];

            // Cache the result
            Cache::put($cacheKey, $cards, self::CACHE_TTL);

            Log::info('[UmapyoiApiClient] Support cards fetched successfully', [
                'count' => count($cards),
                'source' => 'api',
            ]);

            return [
                'success' => true,
                'data' => $cards,
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            Log::error('[UmapyoiApiClient] Failed to fetch support cards', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => [],
                'source' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch a specific support card by ID
     *
     * @return array{success: bool, data: array<string, mixed>|null, source: string, error?: string}
     */
    public function getSupportCard(string $cardId, bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX."support_card:{$cardId}";

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return [
                'success' => true,
                'data' => Cache::get($cacheKey),
                'source' => 'cache',
            ];
        }

        try {
            $response = $this->makeRequest('GET', "/v1/support-cards/{$cardId}");

            if (! $response['success']) {
                throw new \RuntimeException($response['error'] ?? 'Unknown error');
            }

            $card = $response['data']['support_card'] ?? null;

            if ($card) {
                Cache::put($cacheKey, $card, self::CACHE_TTL);
            }

            return [
                'success' => true,
                'data' => $card,
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            Log::error('[UmapyoiApiClient] Failed to fetch support card', [
                'card_id' => $cardId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'source' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch latest news from umapyoi.net
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getNews(int $limit = 10, bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX."news:limit:{$limit}";

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return [
                'success' => true,
                'data' => Cache::get($cacheKey, []),
                'source' => 'cache',
            ];
        }

        try {
            $response = $this->makeRequest('GET', '/v1/news', [
                'limit' => $limit,
            ]);

            if (! $response['success']) {
                throw new \RuntimeException($response['error'] ?? 'Unknown error');
            }

            $news = $response['data']['news'] ?? [];

            // Cache the result for 1 hour (news updates more frequently)
            Cache::put($cacheKey, $news, 3600);

            Log::info('[UmapyoiApiClient] News fetched successfully', [
                'count' => count($news),
                'source' => 'api',
            ]);

            return [
                'success' => true,
                'data' => $news,
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            Log::error('[UmapyoiApiClient] Failed to fetch news', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => [],
                'source' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Make an HTTP request with MCP fetch integration and retry logic
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: array<string, mixed>, error?: string}
     */
    protected function makeRequest(string $method, string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl.$endpoint;
        $attempt = 0;

        while ($attempt < self::MAX_RETRIES) {
            $attempt++;

            try {
                // Check if MCP fetch server is available for enhanced capabilities
                if ($this->mcpClient->isServerEnabled('fetch')) {
                    return $this->makeRequestViaMCP($method, $url, $params);
                }

                // Fallback to standard HTTP client
                return $this->makeRequestViaHttp($method, $url, $params);
            } catch (\Exception $e) {
                Log::warning('[UmapyoiApiClient] Request failed', [
                    'attempt' => $attempt,
                    'max_retries' => self::MAX_RETRIES,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt >= self::MAX_RETRIES) {
                    throw $e;
                }

                // Exponential backoff
                usleep(self::RETRY_DELAY * $attempt * 1000);
            }
        }

        return [
            'success' => false,
            'data' => [],
            'error' => 'Max retries exceeded',
        ];
    }

    /**
     * Make request via MCP fetch server
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: array<string, mixed>, error?: string}
     */
    protected function makeRequestViaMCP(string $method, string $url, array $params): array
    {
        // In production, this would use actual MCP fetch server
        // For now, we'll use the standard HTTP client as fallback
        Log::debug('[UmapyoiApiClient] MCP fetch server not yet implemented, using HTTP fallback');

        return $this->makeRequestViaHttp($method, $url, $params);
    }

    /**
     * Make request via standard HTTP client
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: array<string, mixed>, error?: string}
     */
    protected function makeRequestViaHttp(string $method, string $url, array $params): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'UmamusumeCareerPlanner/1.0',
            ])
            ->send($method, $url, [
                'query' => $params,
            ]);

        if (! $response->successful()) {
            return [
                'success' => false,
                'data' => [],
                'error' => "HTTP {$response->status()}: {$response->body()}",
            ];
        }

        return [
            'success' => true,
            'data' => $response->json() ?? [],
        ];
    }

    /**
     * Check if the API is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear all cached data
     */
    public function clearCache(): void
    {
        $keys = [
            self::CACHE_PREFIX.'characters',
            self::CACHE_PREFIX.'support_cards',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Log::info('[UmapyoiApiClient] Cache cleared');
    }

    /**
     * Get cache statistics
     *
     * @return array{characters: bool, support_cards: bool, news: bool}
     */
    public function getCacheStatus(): array
    {
        return [
            'characters' => Cache::has(self::CACHE_PREFIX.'characters'),
            'support_cards' => Cache::has(self::CACHE_PREFIX.'support_cards'),
            'news' => Cache::has(self::CACHE_PREFIX.'news:limit:10'),
        ];
    }
}
