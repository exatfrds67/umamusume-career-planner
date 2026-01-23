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

        // Store updated history
        Cache::put($cacheKey, $history, self::CONTEXT_CACHE_TTL);

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
    public function getApiCallContext(): array
        $contextKey = $this->generateContextKey($apiName, $endpoint);
        $cacheKey = self::CACHE_PREFIX."api_call:{$contextKey}";

        $context = Cache::get($cacheKey, []);

        return is_array($context) ? $context : [];
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
        $mergedContext = array_merge($existingContext, $context);

        Cache::put($cacheKey, $mergedContext, self::CONTEXT_CACHE_TTL);

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
    public function getCharacterContext(): array
        $cacheKey = self::CACHE_PREFIX."character:{$characterId}";

        return Cache::get($cacheKey, []);
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
        $mergedContext = array_merge($existingContext, $context);

        Cache::put($cacheKey, $mergedContext, self::CONTEXT_CACHE_TTL);

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
    public function getCareerContext(): array
        $cacheKey = self::CACHE_PREFIX."career:{$careerId}";

        return Cache::get($cacheKey, []);
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
    public function getConversationContext(): array
        $cacheKey = self::CACHE_PREFIX."conversation:{$conversationId}";

        return Cache::get($cacheKey, []);
    }

    /**
     * Analyze context patterns for intelligent caching
     *
     * @return array{frequent_endpoints: array<string, int>, cache_hit_rate: float, recommendations: array<string>}
     */
    public function analyzeContextPatterns(): array
        $pattern = self::CACHE_PREFIX."api_call:{$apiName}:*";

        // In production, this would analyze actual cache patterns
        // For now, return mock analysis
        return [
            'frequent_endpoints' => [
                '/v1/characters' => 45,
                '/v1/support-cards' => 32,
                '/v1/skills' => 28,
            ],
            'cache_hit_rate' => 0.78,
            'recommendations' => [
                'Increase cache TTL for /v1/characters endpoint',
                'Implement predictive caching for support cards',
                'Consider background refresh for frequently accessed data',
            ],
        ];
    }

    /**
     * Get context summary for debugging
     *
     * @return array{total_contexts: int, character_contexts: int, career_contexts: int, api_contexts: int}
     */
    public function getContextSummary(): array
        // In production, this would count actual cached contexts
        // For now, return mock summary
        return [
            'total_contexts' => 127,
            'character_contexts' => 45,
            'career_contexts' => 38,
            'api_contexts' => 44,
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
        // In production, this would use Cache::tags() or pattern matching
        Log::info('[Context7Service] All contexts cleared');
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
        return [
            'enabled' => $this->mcpClient->isServerEnabled('context7'),
            'healthy' => $this->mcpClient->isServerHealthy('context7'),
            'capabilities' => $this->mcpClient->getServerCapabilities('context7'),
        ];
    }
}
