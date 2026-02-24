<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Context7 MCP Server Integration Service
 *
 * Provides intelligent context management across API calls using the context7 MCP server.
 * Maintains conversation context, API call history, and intelligent caching strategies.
 *
 * Requirements: 55.3, 56.1, Task 4.4.1
 */
class Context7Service
{
    /**
     * Cache TTL for context data (30 minutes)
     */
    protected const CONTEXT_CACHE_TTL = 1800;

    /**
     * Cache prefix
     */
    protected const CACHE_PREFIX = 'context7:';

    /**
     * Maximum context history size
     */
    protected const MAX_CONTEXT_HISTORY = 50;

    public function __construct(
        protected MCPClientService $mcpClient
    ) {}

    /**
     * Store API call context for future reference
     *
     * @param  array<string, mixed>  $context
     */
    public function storeApiCallContext(string $apiName, string $endpoint, array $context): void
    {
        $contextKey = $this->generateContextKey($apiName, $endpoint);
        $cacheKey = self::CACHE_PREFIX."api_call:{$contextKey}";

        // Get existing context history
        $history = Cache::get($cacheKey, []);
        if (! is_array($history)) {
            $history = [];
        }

        // Add new context entry
        $history[] = [
            'timestamp' => now()->toIso8601String(),
            'api_name' => $apiName,
            'endpoint' => $endpoint,
            'context' => $context,
        ];

        // Limit history size
        if (count($history) > self::MAX_CONTEXT_HISTORY) {
            $history = array_slice($history, -self::MAX_CONTEXT_HISTORY);
        }

        Cache::put($cacheKey, $history, self::CONTEXT_CACHE_TTL);
        $this->trackKey($cacheKey);

        Log::debug('[Context7Service] API call context stored', [
            'api_name' => $apiName,
            'endpoint' => $endpoint,
            'history_size' => count($history),
        ]);
    }

    /**
     * Retrieve API call context history
     *
     * @return array<int, array<string, mixed>>
     */
    public function getApiCallContext(string $apiName, string $endpoint): array
    {
        $contextKey = $this->generateContextKey($apiName, $endpoint);
        $cacheKey = self::CACHE_PREFIX."api_call:{$contextKey}";

        $context = Cache::get($cacheKey, []);

        /** @var array<int, array<string, mixed>> $result */
        $result = is_array($context) ? $context : [];

        return $result;
    }

    /**
     * Store character-specific context
     *
     * @param  array<string, mixed>  $context
     */
    public function storeCharacterContext(int $characterId, array $context): void
    {
        $cacheKey = self::CACHE_PREFIX."character:{$characterId}";

        $existingContext = Cache::get($cacheKey, []);
        /** @var array<string, mixed> $existingArray */
        $existingArray = is_array($existingContext) ? $existingContext : [];
        $mergedContext = array_merge($existingArray, $context);

        Cache::put($cacheKey, $mergedContext, self::CONTEXT_CACHE_TTL);
        $this->trackKey($cacheKey);

        Log::debug('[Context7Service] Character context stored', [
            'character_id' => $characterId,
            'context_keys' => array_keys($context),
        ]);
    }

    /**
     * Retrieve character-specific context
     *
     * @return array<string, mixed>
     */
    public function getCharacterContext(int $characterId): array
    {
        $cacheKey = self::CACHE_PREFIX."character:{$characterId}";

        $context = Cache::get($cacheKey, []);

        /** @var array<string, mixed> $result */
        $result = is_array($context) ? $context : [];

        return $result;
    }

    /**
     * Store career-specific context
     *
     * @param  array<string, mixed>  $context
     */
    public function storeCareerContext(int $careerId, array $context): void
    {
        $cacheKey = self::CACHE_PREFIX."career:{$careerId}";

        $existingContext = Cache::get($cacheKey, []);
        /** @var array<string, mixed> $existingArray */
        $existingArray = is_array($existingContext) ? $existingContext : [];
        $mergedContext = array_merge($existingArray, $context);

        Cache::put($cacheKey, $mergedContext, self::CONTEXT_CACHE_TTL);
        $this->trackKey($cacheKey);

        Log::debug('[Context7Service] Career context stored', [
            'career_id' => $careerId,
            'context_keys' => array_keys($context),
        ]);
    }

    /**
     * Retrieve career-specific context
     *
     * @return array<string, mixed>
     */
    public function getCareerContext(int $careerId): array
    {
        $cacheKey = self::CACHE_PREFIX."career:{$careerId}";

        $context = Cache::get($cacheKey, []);

        /** @var array<string, mixed> $result */
        $result = is_array($context) ? $context : [];

        return $result;
    }

    /**
     * Store AI conversation context
     *
     * @param  array<string, mixed>  $context
     */
    public function storeConversationContext(string $conversationId, array $context): void
    {
        $cacheKey = self::CACHE_PREFIX."conversation:{$conversationId}";

        // Get existing context
        $history = Cache::get($cacheKey, []);
        if (! is_array($history)) {
            $history = [];
        }

        // Add new context entry
        $history[] = [
            'timestamp' => now()->toIso8601String(),
            'context' => $context,
        ];

        // Limit history size
        if (count($history) > self::MAX_CONTEXT_HISTORY) {
            $history = array_slice($history, -self::MAX_CONTEXT_HISTORY);
        }

        Cache::put($cacheKey, $history, self::CONTEXT_CACHE_TTL);
        $this->trackKey($cacheKey);

        Log::debug('[Context7Service] Conversation context stored', [
            'conversation_id' => $conversationId,
            'history_size' => count($history),
        ]);
    }

    /**
     * Retrieve AI conversation context
     *
     * @return array<int, array<string, mixed>>
     */
    public function getConversationContext(string $conversationId): array
    {
        $cacheKey = self::CACHE_PREFIX."conversation:{$conversationId}";

        $context = Cache::get($cacheKey, []);

        /** @var array<int, array<string, mixed>> $result */
        $result = is_array($context) ? $context : [];

        return $result;
    }

    /**
     * Analyze context patterns for intelligent caching
     *
     * @return array{frequent_endpoints: array<string, int>, cache_hit_rate: float, recommendations: array<string>}
     */
    public function analyzeContextPatterns(string $apiName = ''): array
    {
        $frequentEndpoints = [];
        $totalHits = 0;
        $totalMisses = 0;

        $prefix = self::CACHE_PREFIX.'api_call:';
        $allKeys = Cache::get(self::CACHE_PREFIX.'tracked_keys', []);

        if (is_array($allKeys)) {
            foreach ($allKeys as $key) {
                if (! is_string($key)) {
                    continue;
                }

                $history = Cache::get($key, []);
                if (! is_array($history)) {
                    continue;
                }

                foreach ($history as $entry) {
                    if (! is_array($entry)) {
                        continue;
                    }

                    $endpoint = $entry['endpoint'] ?? 'unknown';
                    if (! is_string($endpoint)) {
                        continue;
                    }

                    if ($apiName !== '' && isset($entry['api_name']) && $entry['api_name'] !== $apiName) {
                        continue;
                    }

                    if (! isset($frequentEndpoints[$endpoint])) {
                        $frequentEndpoints[$endpoint] = 0;
                    }
                    $frequentEndpoints[$endpoint]++;
                    $totalHits++;
                }
            }
        }

        arsort($frequentEndpoints);
        $frequentEndpoints = array_slice($frequentEndpoints, 0, 10, true);

        $totalRequests = $totalHits + $totalMisses;
        $cacheHitRate = $totalRequests > 0 ? round($totalHits / $totalRequests, 2) : 0.0;

        $recommendations = [];
        foreach ($frequentEndpoints as $endpoint => $count) {
            if ($count > 20) {
                $recommendations[] = "Increase cache TTL for {$endpoint} endpoint (accessed {$count} times)";
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'No optimization recommendations at this time';
        }

        return [
            'frequent_endpoints' => $frequentEndpoints,
            'cache_hit_rate' => $cacheHitRate,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Get context summary for debugging
     *
     * @return array{total_contexts: int, character_contexts: int, career_contexts: int, api_contexts: int}
     */
    public function getContextSummary(): array
    {
        $characterContexts = 0;
        $careerContexts = 0;
        $apiContexts = 0;

        $trackedKeys = Cache::get(self::CACHE_PREFIX.'tracked_keys', []);
        if (! is_array($trackedKeys)) {
            $trackedKeys = [];
        }

        foreach ($trackedKeys as $key) {
            if (! is_string($key)) {
                continue;
            }

            if (str_contains($key, 'character:')) {
                $characterContexts++;
            } elseif (str_contains($key, 'career:')) {
                $careerContexts++;
            } elseif (str_contains($key, 'api_call:')) {
                $apiContexts++;
            }
        }

        return [
            'total_contexts' => $characterContexts + $careerContexts + $apiContexts,
            'character_contexts' => $characterContexts,
            'career_contexts' => $careerContexts,
            'api_contexts' => $apiContexts,
        ];
    }

    /**
     * Clear context for a specific entity
     */
    public function clearContext(string $type, string $identifier): void
    {
        $cacheKey = self::CACHE_PREFIX."{$type}:{$identifier}";
        Cache::forget($cacheKey);

        Log::info('[Context7Service] Context cleared', [
            'type' => $type,
            'identifier' => $identifier,
        ]);
    }

    /**
     * Clear all contexts
     */
    public function clearAllContexts(): void
    {
        $trackedKeys = Cache::get(self::CACHE_PREFIX.'tracked_keys', []);
        if (is_array($trackedKeys)) {
            foreach ($trackedKeys as $key) {
                if (is_string($key)) {
                    Cache::forget($key);
                }
            }
        }

        Cache::forget(self::CACHE_PREFIX.'tracked_keys');

        Log::info('[Context7Service] All contexts cleared');
    }

    /**
     * Track a cache key for summary and pattern analysis
     */
    protected function trackKey(string $cacheKey): void
    {
        $trackedKeys = Cache::get(self::CACHE_PREFIX.'tracked_keys', []);
        if (! is_array($trackedKeys)) {
            $trackedKeys = [];
        }

        if (! in_array($cacheKey, $trackedKeys, true)) {
            $trackedKeys[] = $cacheKey;
            Cache::put(self::CACHE_PREFIX.'tracked_keys', $trackedKeys, self::CONTEXT_CACHE_TTL);
        }
    }

    /**
     * Generate a unique context key
     */
    protected function generateContextKey(string $apiName, string $endpoint): string
    {
        return md5($apiName.':'.$endpoint);
    }

    /**
     * Check if context7 MCP server is available
     */
    public function isAvailable(): bool
    {
        return $this->mcpClient->isServerEnabled('context7') &&
            $this->mcpClient->isServerHealthy('context7');
    }

    /**
     * Get context7 server status
     *
     * @return array{enabled: bool, healthy: bool, capabilities: array<string, mixed>}
     */
    public function getServerStatus(): array
    {
        return [
            'enabled' => $this->mcpClient->isServerEnabled('context7'),
            'healthy' => $this->mcpClient->isServerHealthy('context7'),
            'capabilities' => $this->mcpClient->getServerCapabilities('context7'),
        ];
    }
}
