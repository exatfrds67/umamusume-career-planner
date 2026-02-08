<?php

declare(strict_types=1);

namespace App\Services;

use App\Collections\RecommendationCollection;
use App\ValueObjects\TrainingContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Recommendation Cache Service
 *
 * Provides intelligent caching for AI-generated recommendations to improve response times.
 * Implements context-aware caching with automatic invalidation on state changes.
 *
 * Cache strategy:
 * - Cache key based on context hash (turn, stats, energy, mood, bonds, facilities)
 * - TTL: 5 minutes (recommendations are turn-specific and context-sensitive)
 * - Automatic invalidation when character state changes significantly
 *
 * Performance targets:
 * - Cache hit: <50ms response time
 * - Cache miss: Falls back to AI generation (<2s local, <5s cloud)
 *
 * @see \App\Services\TrainingAdvisoryService For usage in recommendation generation
 */
class RecommendationCacheService
{
    /**
     * Cache key prefix for recommendations
     */
    protected const CACHE_KEY_PREFIX = 'advisory:recommendations';

    /**
     * Cache TTL in seconds (5 minutes)
     * Short TTL because recommendations are highly context-sensitive
     */
    protected const CACHE_TTL = 300;

    /**
     * Get cached recommendations for a training context.
     *
     * Returns cached recommendations if available and still valid for the context.
     * Returns null if no cache exists or cache is invalid.
     *
     * @param  TrainingContext  $context  Training context to get recommendations for
     * @return RecommendationCollection|null Cached recommendations or null
     */
    public function getCachedRecommendations(TrainingContext $context): ?RecommendationCollection
    {
        $cacheKey = $this->getCacheKey($context);

        if (! Cache::has($cacheKey)) {
            return null;
        }

        /** @var array<int, array<string, mixed>>|null $cachedData */
        $cachedData = Cache::get($cacheKey);

        if ($cachedData === null || ! is_array($cachedData)) {
            return null;
        }

        Log::debug('[RecommendationCache] Cache hit', [
            'cache_key' => $cacheKey,
            'turn' => $context->turnNumber,
            'recommendations_count' => count($cachedData),
        ]);

        // Reconstruct RecommendationCollection from cached data
        return $this->reconstructRecommendations($cachedData);
    }

    /**
     * Store recommendations in cache for a training context.
     *
     * @param  TrainingContext  $context  Training context
     * @param  RecommendationCollection  $recommendations  Recommendations to cache
     * @return bool True if successfully cached
     */
    public function cacheRecommendations(
        TrainingContext $context,
        RecommendationCollection $recommendations
    ): bool {
        $cacheKey = $this->getCacheKey($context);

        // Serialize recommendations to array for caching
        $serialized = $recommendations->map(function ($rec) {
            return [
                'type' => $rec->type->value,
                'priority' => $rec->priority->value,
                'action' => $rec->action,
                'reasoning' => $rec->reasoning,
                'expected_outcomes' => $rec->expectedOutcomes,
                'risks' => $rec->risks,
                'confidence_score' => $rec->confidenceScore,
                'source' => $rec->source ?? 'ai',
            ];
        })->toArray();

        $result = Cache::put($cacheKey, $serialized, self::CACHE_TTL);

        Log::debug('[RecommendationCache] Cached recommendations', [
            'cache_key' => $cacheKey,
            'turn' => $context->turnNumber,
            'recommendations_count' => count($serialized),
            'ttl_seconds' => self::CACHE_TTL,
        ]);

        return $result;
    }

    /**
     * Invalidate cached recommendations for a specific career run.
     *
     * Should be called when character state changes significantly:
     * - Training completed (stats changed)
     * - Skill purchased (SP changed)
     * - Rest taken (energy changed)
     * - Event occurred (mood/conditions changed)
     *
     * @param  string  $careerRunId  Career run ID (UUID or numeric)
     * @return int Number of cache entries invalidated
     */
    public function invalidateCareerCache(string $careerRunId): int
    {
        // Get all cache keys for this career
        // Note: This is a simplified implementation. In production, you might want to
        // maintain a separate index of cache keys per career for efficient invalidation.
        $pattern = self::CACHE_KEY_PREFIX.':'.$careerRunId.':*';

        // Laravel's cache doesn't support pattern-based deletion natively
        // For Redis, we can use the Redis facade directly
        if (config('cache.default') === 'redis') {
            $keys = \Illuminate\Support\Facades\Redis::keys($pattern);
            if (! empty($keys)) {
                \Illuminate\Support\Facades\Redis::del($keys);
                Log::info('[RecommendationCache] Invalidated career cache', [
                    'career_id' => $careerRunId,
                    'keys_deleted' => count($keys),
                ]);

                return count($keys);
            }
        }

        // For other cache drivers, we'll just log that invalidation was requested
        // Individual cache entries will expire naturally based on TTL
        Log::info('[RecommendationCache] Cache invalidation requested (will expire naturally)', [
            'career_id' => $careerRunId,
            'cache_driver' => config('cache.default'),
        ]);

        return 0;
    }

    /**
     * Invalidate cached recommendations for a specific turn.
     *
     * @param  string  $careerRunId  Career run ID
     * @param  int  $turnNumber  Turn number to invalidate
     * @return bool True if cache was invalidated
     */
    public function invalidateTurnCache(string $careerRunId, int $turnNumber): bool
    {
        // We can't easily invalidate by turn without the full context
        // So we'll invalidate the entire career cache
        $this->invalidateCareerCache($careerRunId);

        return true;
    }

    /**
     * Check if recommendations are cached for a context.
     *
     * @param  TrainingContext  $context  Training context to check
     * @return bool True if cached
     */
    public function isCached(TrainingContext $context): bool
    {
        return Cache::has($this->getCacheKey($context));
    }

    /**
     * Get cache statistics for monitoring.
     *
     * @param  TrainingContext  $context  Training context
     * @return array{is_cached: bool, cache_key: string, ttl_seconds: int}
     */
    public function getCacheStats(TrainingContext $context): array
    {
        return [
            'is_cached' => $this->isCached($context),
            'cache_key' => $this->getCacheKey($context),
            'ttl_seconds' => self::CACHE_TTL,
        ];
    }

    /**
     * Get the cache TTL in seconds.
     *
     * @return int TTL in seconds
     */
    public function getCacheTtl(): int
    {
        return self::CACHE_TTL;
    }

    /**
     * Build cache key from training context.
     *
     * The cache key is based on a hash of the context state that affects recommendations:
     * - Career run ID
     * - Turn number
     * - Stats (rounded to nearest 10 for cache efficiency)
     * - Energy (rounded to nearest 5)
     * - Mood
     * - Support card bonds (rounded to nearest 5)
     * - Facility levels
     *
     * This allows cache hits for similar contexts while ensuring recommendations
     * remain accurate for the character's state.
     *
     * @param  TrainingContext  $context  Training context
     * @return string Cache key
     */
    protected function getCacheKey(TrainingContext $context): string
    {
        // Build a context signature that captures the essential state
        // We round values to allow cache hits for similar states
        $signature = [
            'career' => $context->careerRunId,
            'turn' => $context->turnNumber,
            'phase' => $context->phase->value,
            // Round stats to nearest 10 for cache efficiency
            'stats' => [
                'speed' => (int) (round($context->stats->speed / 10) * 10),
                'stamina' => (int) (round($context->stats->stamina / 10) * 10),
                'power' => (int) (round($context->stats->power / 10) * 10),
                'guts' => (int) (round($context->stats->guts / 10) * 10),
                'wisdom' => (int) (round($context->stats->wisdom / 10) * 10),
            ],
            // Round energy to nearest 5
            'energy' => (int) (round($context->energy / 5) * 5),
            'mood' => $context->mood->value,
            // Include facility levels (these significantly affect recommendations)
            'facilities' => $context->facilityLevels,
            // Include bond status (rounded to nearest 5)
            // Extract bond levels from SupportCard objects
            'bonds' => array_map(
                fn ($card) => (int) (round($card->bond / 5) * 5),
                $context->deck->cards
            ),
        ];

        // Create a hash of the signature
        $jsonEncoded = json_encode($signature);
        if ($jsonEncoded === false) {
            throw new \RuntimeException('Failed to encode cache signature to JSON');
        }
        $hash = md5($jsonEncoded);

        return self::CACHE_KEY_PREFIX.':'.$context->careerRunId.':'.$hash;
    }

    /**
     * Reconstruct RecommendationCollection from cached data.
     *
     * @param  array<int, array<string, mixed>>  $cachedData  Cached recommendation data
     * @return RecommendationCollection Reconstructed collection
     */
    protected function reconstructRecommendations(array $cachedData): RecommendationCollection
    {
        $recommendations = array_map(function (array $data) {
            // Ensure risks is an array of strings
            $risks = [];
            if (isset($data['risks']) && is_array($data['risks'])) {
                foreach ($data['risks'] as $risk) {
                    if (is_string($risk) || is_numeric($risk)) {
                        $risks[] = (string) $risk;
                    }
                }
            }

            // Ensure expected_outcomes is an array
            $expectedOutcomes = [];
            if (isset($data['expected_outcomes']) && is_array($data['expected_outcomes'])) {
                $expectedOutcomes = $data['expected_outcomes'];
            }

            // Ensure confidence_score is float or null
            $confidenceScore = null;
            if (isset($data['confidence_score']) && (is_float($data['confidence_score']) || is_int($data['confidence_score']))) {
                $confidenceScore = (float) $data['confidence_score'];
            }

            // Ensure type and priority are strings for enum conversion
            $type = isset($data['type']) && (is_string($data['type']) || is_int($data['type']))
                ? $data['type']
                : 'training_facility';
            $priority = isset($data['priority']) && (is_string($data['priority']) || is_int($data['priority']))
                ? $data['priority']
                : 'medium';

            return new \App\ValueObjects\Recommendation(
                type: \App\Enums\RecommendationType::from($type),
                priority: \App\Enums\Priority::from($priority),
                action: is_string($data['action'] ?? null) ? $data['action'] : '',
                reasoning: is_string($data['reasoning'] ?? null) ? $data['reasoning'] : '',
                expectedOutcomes: $expectedOutcomes,
                risks: $risks,
                confidenceScore: $confidenceScore
            );
        }, $cachedData);

        return new RecommendationCollection($recommendations);
    }
}
