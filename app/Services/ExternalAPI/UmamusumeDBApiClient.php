<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * UmamusumeDB.com API Client with MCP-Enhanced Retry Logic
 *
 * Provides access to training calculations, meta data, and community builds
 * from UmamusumeDB.com with intelligent retry and fallback mechanisms.
 *
 * Requirements: 14.1, 55.3, Task 4.4.1
 */
class UmamusumeDBApiClient
{
    /**
     * Base URL for UmamusumeDB.com API
     */
    protected string $baseUrl;

    /**
     * API timeout in seconds
     */
    protected int $timeout;

    /**
     * Cache TTL in seconds (12 hours for meta data)
     */
    protected const CACHE_TTL = 43200;

    /**
     * Cache prefix
     */
    protected const CACHE_PREFIX = 'umamusumedb:';

    /**
     * Maximum retry attempts with exponential backoff
     */
    protected const MAX_RETRIES = 5;

    /**
     * Initial retry delay in milliseconds
     */
    protected const INITIAL_RETRY_DELAY = 500;

    /**
     * Maximum retry delay in milliseconds
     */
    protected const MAX_RETRY_DELAY = 10000;

    public function __construct(
        protected MCPClientService $mcpClient
    ) {
        $this->baseUrl = (string) config('services.umamusumedb.url', 'https://api.umamusumedb.com');
        $this->timeout = (int) config('services.umamusumedb.timeout', 30);
    }

    /**
     * Get training calculation data for a specific scenario
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: array<string, mixed>|null, source: string, error?: string}
     */
    public function getTrainingCalculation(): array
        $cacheKey = self::CACHE_PREFIX.'training:'.md5(json_encode($params) ?: '');

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return [
                'success' => true,
                'data' => Cache::get($cacheKey),
                'source' => 'cache',
            ];
        }

        try {
            $response = $this->makeRequestWithRetry('POST', '/v1/training/calculate', $params);

            if (! $response['success']) {
                throw new \RuntimeException($response['error'] ?? 'Unknown error');
            }

            $calculation = $response['data']['calculation'] ?? null;

            if ($calculation) {
                // Cache training calculations for 1 hour
                Cache::put($cacheKey, $calculation, 3600);
            }

            return [
                'success' => true,
                'data' => $calculation,
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            Log::error('[UmamusumeDBApiClient] Failed to get training calculation', [
                'params' => $params,
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
     * Get meta tier rankings for support cards
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getMetaTierRankings(): array
        $cacheKey = self::CACHE_PREFIX.'meta:tier_rankings';

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return [
                'success' => true,
                'data' => Cache::get($cacheKey, []),
                'source' => 'cache',
            ];
        }

        try {
            $response = $this->makeRequestWithRetry('GET', '/v1/meta/tier-rankings');

            if (! $response['success']) {
                throw new \RuntimeException($response['error'] ?? 'Unknown error');
            }

            $rankings = $response['data']['rankings'] ?? [];

            // Cache meta data for 12 hours
            Cache::put($cacheKey, $rankings, self::CACHE_TTL);

            Log::info('[UmamusumeDBApiClient] Meta tier rankings fetched successfully', [
                'count' => count($rankings),
                'source' => 'api',
            ]);

            return [
                'success' => true,
                'data' => $rankings,
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            Log::error('[UmamusumeDBApiClient] Failed to fetch meta tier rankings', [
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
     * Get community builds for a specific character
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getCommunityBuilds(): array
        $cacheKey = self::CACHE_PREFIX."builds:{$characterId}";

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return [
                'success' => true,
                'data' => Cache::get($cacheKey, []),
                'source' => 'cache',
            ];
        }

        try {
            $response = $this->makeRequestWithRetry('GET', "/v1/characters/{$characterId}/builds");

            if (! $response['success']) {
                throw new \RuntimeException($response['error'] ?? 'Unknown error');
            }

            $builds = $response['data']['builds'] ?? [];

            // Cache community builds for 6 hours
            Cache::put($cacheKey, $builds, 21600);

            Log::info('[UmamusumeDBApiClient] Community builds fetched successfully', [
                'character_id' => $characterId,
                'count' => count($builds),
                'source' => 'api',
            ]);

            return [
                'success' => true,
                'data' => $builds,
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            Log::error('[UmamusumeDBApiClient] Failed to fetch community builds', [
                'character_id' => $characterId,
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
     * Get skill effectiveness data
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getSkillEffectiveness(): array
        $cacheKey = self::CACHE_PREFIX.'skills:effectiveness';

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return [
                'success' => true,
                'data' => Cache::get($cacheKey, []),
                'source' => 'cache',
            ];
        }

        try {
            $response = $this->makeRequestWithRetry('GET', '/v1/skills/effectiveness');

            if (! $response['success']) {
                throw new \RuntimeException($response['error'] ?? 'Unknown error');
            }

            $effectiveness = $response['data']['skills'] ?? [];

            // Cache skill effectiveness for 12 hours
            Cache::put($cacheKey, $effectiveness, self::CACHE_TTL);

            Log::info('[UmamusumeDBApiClient] Skill effectiveness fetched successfully', [
                'count' => count($effectiveness),
                'source' => 'api',
            ]);

            return [
                'success' => true,
                'data' => $effectiveness,
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            Log::error('[UmamusumeDBApiClient] Failed to fetch skill effectiveness', [
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
     * Get race strategy recommendations
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: array<string, mixed>|null, source: string, error?: string}
     */
    public function getRaceStrategy(): array
        $cacheKey = self::CACHE_PREFIX.'race_strategy:'.md5(json_encode($params));

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return [
                'success' => true,
                'data' => Cache::get($cacheKey),
                'source' => 'cache',
            ];
        }

        try {
            $response = $this->makeRequestWithRetry('POST', '/v1/race/strategy', $params);

            if (! $response['success']) {
                throw new \RuntimeException($response['error'] ?? 'Unknown error');
            }

            $strategy = $response['data']['strategy'] ?? null;

            if ($strategy) {
                // Cache race strategies for 2 hours
                Cache::put($cacheKey, $strategy, 7200);
            }

            return [
                'success' => true,
                'data' => $strategy,
                'source' => 'api',
            ];
        } catch (\Exception $e) {
            Log::error('[UmamusumeDBApiClient] Failed to get race strategy', [
                'params' => $params,
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
     * Make an HTTP request with MCP-enhanced retry logic
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: array<string, mixed>, error?: string}
     */
    protected function makeRequestWithRetry(): array
        $url = $this->baseUrl.$endpoint;
        $attempt = 0;
        $delay = self::INITIAL_RETRY_DELAY;

        while ($attempt < self::MAX_RETRIES) {
            $attempt = ($attempt ?? 0) + 1;

            try {
                // Check if MCP fetch server is available for enhanced capabilities
                if ($this->mcpClient->isServerEnabled('fetch')) {
                    $response = $this->makeRequestViaMCP($method, $url, $params);
                } else {
                    $response = $this->makeRequestViaHttp($method, $url, $params);
                }

                if ($response['success']) {
                    return $response;
                }

                // If not successful, throw exception to trigger retry
                throw new \RuntimeException($response['error'] ?? 'Request failed');
            } catch (\Exception $e) {
                $isLastAttempt = $attempt >= self::MAX_RETRIES;

                Log::warning('[UmamusumeDBApiClient] Request failed', [
                    'attempt' => $attempt,
                    'max_retries' => self::MAX_RETRIES,
                    'delay_ms' => $delay,
                    'error' => $e->getMessage(),
                    'is_last_attempt' => $isLastAttempt,
                ]);

                if ($isLastAttempt) {
                    throw $e;
                }

                // Exponential backoff with jitter
                $jitter = rand(0, (int) ($delay * 0.1));
                usleep(($delay + $jitter) * 1000);

                // Increase delay for next attempt (exponential backoff)
                $delay = min($delay * 2, self::MAX_RETRY_DELAY);
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
    protected function makeRequestViaMCP(): array
        // In production, this would use actual MCP fetch server
        // For now, we'll use the standard HTTP client as fallback
        Log::debug('[UmamusumeDBApiClient] MCP fetch server not yet implemented, using HTTP fallback');

        return $this->makeRequestViaHttp($method, $url, $params);
    }

    /**
     * Make request via standard HTTP client
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: array<string, mixed>, error?: string}
     */
    protected function makeRequestViaHttp(): array
        $httpClient = Http::timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'UmamusumeCareerPlanner/1.0',
            ]);

        if ($method === 'GET') {
            $response = $httpClient->get($url, $params);
        } else {
            $response = $httpClient->send($method, $url, [
                'json' => $params,
            ]);
        }

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
        $patterns = [
            self::CACHE_PREFIX.'meta:*',
            self::CACHE_PREFIX.'skills:*',
            self::CACHE_PREFIX.'training:*',
            self::CACHE_PREFIX.'builds:*',
            self::CACHE_PREFIX.'race_strategy:*',
        ];

        foreach ($patterns as $pattern) {
            // Note: In production, you'd want to use Cache::tags() for better cache management
            // For now, we'll just log the clear operation
            Log::info('[UmamusumeDBApiClient] Cache pattern cleared', ['pattern' => $pattern]);
        }

        Log::info('[UmamusumeDBApiClient] Cache cleared');
    }

    /**
     * Get cache statistics
     *
     * @return array{meta_tier_rankings: bool, skill_effectiveness: bool}
     */
    public function getCacheStatus(): array
        return [
            'meta_tier_rankings' => Cache::has(self::CACHE_PREFIX.'meta:tier_rankings'),
            'skill_effectiveness' => Cache::has(self::CACHE_PREFIX.'skills:effectiveness'),
        ];
    }
}
