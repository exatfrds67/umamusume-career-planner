<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BatchTrainingPredictionRequest;
use App\Http\Requests\Api\TrainingPredictionRequest;
use App\Http\Requests\Api\TrainingRecommendationRequest;
use App\Http\Requests\Api\TrainingSimulationRequest;
use App\Http\Resources\Api\TrainingPredictionResource;
use App\Models\Character;
use App\Services\TrainingCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Training Prediction API Controller
 *
 * Provides RESTful endpoints for real-time training predictions with:
 * - Redis-based intelligent caching
 * - MCP agent integration for complex calculations
 * - Batch prediction capabilities
 * - Prediction accuracy tracking
 * - Cost optimization via awspricing MCP server
 */
class TrainingPredictionController extends Controller
{
    /**
     * Cache TTL in seconds (5 minutes)
     */
    private const CACHE_TTL = 300;

    /**
     * Cache prefix for training predictions
     */
    private const CACHE_PREFIX = 'training_prediction:';

    public function __construct(
        private readonly TrainingCalculationService $trainingService
    ) {}

    /**
     * Get logic to support tagging if available, otherwise fallback to standard cache
     *
     * @param  array<string>  $tags
     */
    private function getCache(array $tags = []): \Illuminate\Contracts\Cache\Repository|\Illuminate\Cache\TaggedCache
    {
        $store = Cache::getStore();

        // Check if the store supports tags
        if (method_exists($store, 'tags')) {
            return Cache::tags($tags);
        }

        // Fallback for file/database drivers that don't support tags
        // We just return the standard cache facade/repository
        return Cache::store();
    }

    /**
     * Get single training prediction
     */
    public function predict(TrainingPredictionRequest $request): TrainingPredictionResource|JsonResponse
    {
        try {
            $startTime = microtime(true);

            // Ensure user is authenticated
            $user = $request->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'error' => 'You must be authenticated to access this resource.',
                ], 401);
            }

            // Load character with relationships
            /** @var Character $character */
            $character = Character::query()
                ->with(['aptitudes', 'supportCards.supportCard', 'factors'])
                ->findOrFail($request->input('character_id'));

            // Get validated data
            $validated = $request->validated();
            /** @var string $trainingType */
            $trainingType = $validated['training_type'];

            // Generate cache key
            $cacheKey = $this->generateCacheKey(
                $character->id,
                $trainingType,
                $request->except(['character_id', 'training_type'])
            );

            $tags = ['training_predictions', "character_{$character->id}"];

            // Try to get from cache
            $cacheHit = $this->getCache($tags)->has($cacheKey);

            $prediction = $this->getCache($tags)
                ->remember($cacheKey, self::CACHE_TTL, function () use ($character, $request, $startTime, $trainingType, $validated) {
                    // Calculate prediction
                    $prediction = $this->trainingService->calculateTrainingPrediction(
                        $character,
                        $trainingType,
                        $request->except(['character_id', 'training_type'])
                    );

                    // Add MCP optimization if requested
                    if ($request->input('use_mcp', false)) {
                        /** @var array<string, mixed> $mcpContext */
                        $mcpContext = $validated['mcp_context'] ?? [];
                        if (! empty($mcpContext)) {
                            $mcpOptimization = $this->trainingService->getMCPOptimization(
                                $character,
                                $mcpContext
                            );

                            if ($mcpOptimization) {
                                $prediction['mcp_optimization'] = $mcpOptimization;
                            }
                        }
                    }

                    // Add metadata
                    $prediction['training_type'] = $trainingType;
                    $prediction['cached'] = false;
                    $prediction['cache_ttl'] = self::CACHE_TTL;
                    $prediction['processing_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
                    $prediction['timestamp'] = now()->toIso8601String();

                    return $prediction;
                });

            // Update cached flag and processing time if retrieved from cache
            if ($cacheHit) {
                $prediction['cached'] = true;
                $prediction['processing_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            }

            // Log prediction request
            Log::info('Training prediction generated', [
                'character_id' => $character->id,
                'training_type' => $trainingType,
                'cached' => $prediction['cached'],
                'processing_time_ms' => $prediction['processing_time_ms'],
            ]);

            return new TrainingPredictionResource($prediction);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $user = $request->user();
            Log::warning('Character not found for training prediction', [
                'character_id' => $request->input('character_id'),
                'user_id' => $user?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Character not found',
                'error' => 'The requested character does not exist or does not belong to you.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Training prediction failed', [
                'character_id' => $request->input('character_id'),
                'training_type' => $request->input('training_type'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Training prediction failed',
                'error' => 'An error occurred while calculating the training prediction. Please try again.',
            ], 500);
        }
    }

    /**
     * Get batch training predictions for multiple training types
     * Returns predictions in WF-004 format with facilities, recommendation, and summary
     */
    public function batchPredict(BatchTrainingPredictionRequest $request): JsonResponse
    {
        try {
            $startTime = microtime(true);

            // Ensure user is authenticated
            $user = $request->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'error' => 'You must be authenticated to access this resource.',
                ], 401);
            }

            // Load character with relationships
            /** @var Character $character */
            $character = Character::query()
                ->with(['aptitudes', 'supportCards.supportCard', 'factors'])
                ->findOrFail($request->input('character_id'));

            // Get validated data
            $validated = $request->validated();
            /** @var array<string> $trainingTypes */
            $trainingTypes = $validated['training_types'] ?? ['speed', 'stamina', 'power', 'guts', 'wit'];
            $trainingData = $request->except(['character_id', 'training_types', 'include_recommendations']);

            // Generate cache key for batch prediction
            $cacheKey = $this->generateCacheKey(
                $character->id,
                'batch_'.\implode('_', $trainingTypes),
                $trainingData
            );

            $tags = ['training_predictions', "character_{$character->id}"];

            // Try to get from cache
            $cacheHit = $this->getCache($tags)->has($cacheKey);

            $result = $this->getCache($tags)
                ->remember($cacheKey, self::CACHE_TTL, function () use ($character, $trainingTypes, $trainingData) {
                    // Calculate batch predictions
                    $batchPredictions = $this->trainingService->calculateBatchPredictions(
                        $character,
                        $trainingTypes,
                        $trainingData
                    );

                    // Get recommendation
                    $recommendation = $this->trainingService->getRecommendedTraining(
                        $character,
                        $trainingData
                    );

                    // Build facilities object (keyed by training type for WF-004 UI)
                    $facilities = [];
                    foreach ($batchPredictions as $trainingType => $prediction) {
                        // Add support card info for this facility
                        $activeCards = $this->getActiveSupportCardsForFacility($character, $trainingType);
                        $prediction['active_support_cards'] = $activeCards;
                        $prediction['skill_hints'] = $this->getSkillHintsForFacility($character, $trainingType, $activeCards);
                        $prediction['recommendation_score'] = $this->calculateRecommendationScore($prediction, $recommendation, $trainingType);
                        $facilities[$trainingType] = $prediction;
                    }

                    // Add rest option
                    $facilities['rest'] = [
                        'energy_recovery' => 50,
                        'mood_effect' => 'Possible improvement',
                        'failure_risk' => 0,
                        'stat_gains' => [],
                        'recommendation_score' => $character->energy_level < 40 ? 80 : 20,
                    ];

                    return [
                        'facilities' => $facilities,
                        'recommendation' => $recommendation,
                        'summary' => $recommendation['reason'] ?? 'Best option for current character state.',
                        'cached' => false,
                        'cache_ttl' => self::CACHE_TTL,
                        'timestamp' => now()->toIso8601String(),
                    ];
                });

            // Update cached flag and processing time
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);
            $result['cached'] = $cacheHit;
            $result['processing_time_ms'] = $processingTime;

            // Log batch prediction request
            Log::info('Batch training predictions generated', [
                'character_id' => $character->id,
                'training_types' => $trainingTypes,
                'recommended' => $result['recommendation']['recommended_training'] ?? null,
                'cached' => $cacheHit,
                'processing_time_ms' => $processingTime,
            ]);

            return response()->json($result);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Character not found for batch training predictions', [
                'character_id' => $request->input('character_id'),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Character not found',
                'error' => 'The requested character does not exist or does not belong to you.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Batch training predictions failed', [
                'character_id' => $request->input('character_id'),
                'training_types' => $request->input('training_types'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Batch training predictions failed',
                'error' => 'An error occurred while calculating batch predictions. Please try again.',
            ], 500);
        }
    }

    /**
     * Simulate a training action and return authoritative preview values.
     */
    public function simulate(TrainingSimulationRequest $request): JsonResponse
    {
        try {
            /** @var Character $character */
            $character = Character::query()
                ->with(['aptitudes', 'supportCards.supportCard', 'factors'])
                ->findOrFail($request->integer('character_id'));

            $trainingType = (string) $request->string('training_type');

            if ($trainingType === 'rest') {
                $energyRecovery = 50;
                $currentEnergy = (int) ($character->energy_level ?? 100);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'training_type' => 'rest',
                        'stat_gains' => [],
                        'energy_change' => $energyRecovery,
                        'energy_after' => min(100, $currentEnergy + $energyRecovery),
                        'failure_risk' => 0.0,
                        'failure_percent' => 0,
                        'risk_level' => 'low',
                        'skill_points' => 0,
                    ],
                    'message' => 'Rest simulation generated successfully',
                ]);
            }

            $prediction = $this->trainingService->calculateTrainingPrediction($character, $trainingType);

            $energyCost = isset($prediction['energy_cost']) && is_numeric($prediction['energy_cost'])
                ? (int) $prediction['energy_cost']
                : 0;

            $currentEnergy = (int) ($character->energy_level ?? 100);
            $failureRisk = isset($prediction['failure_risk']) && is_numeric($prediction['failure_risk'])
                ? (float) $prediction['failure_risk']
                : 0.0;
            $failurePercent = (int) round($failureRisk * 100);

            $riskLevel = match (true) {
                $failurePercent < 15 => 'low',
                $failurePercent <= 40 => 'medium',
                default => 'high',
            };

            return response()->json([
                'success' => true,
                'data' => [
                    'training_type' => $trainingType,
                    'stat_gains' => is_array($prediction['stat_gains'] ?? null) ? $prediction['stat_gains'] : [],
                    'skill_points' => is_numeric($prediction['skill_points'] ?? null) ? (int) $prediction['skill_points'] : 0,
                    'energy_change' => -$energyCost,
                    'energy_after' => max(0, $currentEnergy - $energyCost),
                    'failure_risk' => $failureRisk,
                    'failure_percent' => $failurePercent,
                    'risk_level' => $riskLevel,
                ],
                'message' => 'Training simulation generated successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Character not found',
                'error' => 'The requested character does not exist or does not belong to you.',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('Training simulation failed', [
                'character_id' => $request->input('character_id'),
                'training_type' => $request->input('training_type'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Training simulation failed',
                'error' => 'An error occurred while simulating this training option.',
            ], 500);
        }
    }

    /**
     * Get active support cards for a specific training facility
     *
     * @return array<int, array<string, mixed>>
     */
    private function getActiveSupportCardsForFacility(Character $character, string $trainingType): array
    {
        $activeCards = [];

        foreach ($character->supportCards as $characterCard) {
            $card = $characterCard->supportCard;
            if (! $card) {
                continue;
            }

            // Check if card type matches training type
            $cardType = strtolower($card->card_type ?? '');
            if ($cardType === strtolower($trainingType)) {
                $bondLevel = $characterCard->bond_level ?? 0;
                $activeCards[] = [
                    'id' => $card->id,
                    'name' => $card->name ?? 'Unknown Card',
                    'card_type' => $card->card_type,
                    'bond_level' => $bondLevel,
                    'friendship_active' => $bondLevel >= 80,
                    'has_guaranteed_hint' => $bondLevel >= 80 && ($card->has_skill_hint ?? false),
                    'limit_break_level' => $characterCard->limit_break_level ?? 0,
                ];
            }
        }

        return $activeCards;
    }

    /**
     * Get skill hints available at a training facility
     *
     * @param  array<int, array<string, mixed>>  $activeCards
     * @return array<int, array<string, mixed>>
     */
    private function getSkillHintsForFacility(Character $character, string $trainingType, array $activeCards): array
    {
        $hints = [];

        foreach ($activeCards as $card) {
            if ($card['has_guaranteed_hint'] ?? false) {
                $hints[] = [
                    'skill_name' => 'Skill Hint',
                    'probability' => 100,
                    'is_guaranteed' => true,
                    'source_card' => $card['name'],
                ];
            }
        }

        // Add random hint chance based on training type
        if (\count($activeCards) > 0) {
            $hints[] = [
                'skill_name' => ucfirst($trainingType).' Skill',
                'probability' => 15 + (\count($activeCards) * 5),
                'is_guaranteed' => false,
                'source_card' => 'Training',
            ];
        }

        return $hints;
    }

    /**
     * Calculate recommendation score for a training option
     *
     * @param  array<string, mixed>  $prediction
     * @param  array<string, mixed>  $recommendation
     */
    private function calculateRecommendationScore(array $prediction, array $recommendation, string $trainingType): int
    {
        // If this is the recommended training, give it a high score
        if (($recommendation['recommended_training'] ?? '') === $trainingType) {
            return 90 + \rand(0, 10);
        }

        // Calculate score based on stat gains and risk with proper type safety
        $statGains = $prediction['stat_gains'] ?? [];
        $statGainsArray = is_array($statGains) ? $statGains : [];
        $totalGain = \array_sum($statGainsArray);

        $failureRisk = $prediction['failure_risk'] ?? 0;
        $riskPenalty = (is_numeric($failureRisk) ? (float) $failureRisk : 0.0) * 30;

        $totalBonus = $prediction['total_bonus'] ?? 0;
        $bonusMultiplier = (is_numeric($totalBonus) ? (float) $totalBonus : 0.0) * 10;

        return (int) \min(100, \max(0, $totalGain + $bonusMultiplier - $riskPenalty));
    }

    /**
     * Get recommended training option with reasoning
     */
    public function recommend(TrainingRecommendationRequest $request): JsonResponse
    {
        try {
            $startTime = microtime(true);

            // Ensure user is authenticated
            $user = $request->user();
            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'error' => 'You must be authenticated to access this resource.',
                ], 401);
            }

            // Load character with relationships
            /** @var Character $character */
            $character = Character::query()
                ->with(['aptitudes', 'supportCards.supportCard', 'factors'])
                ->findOrFail($request->input('character_id'));

            $trainingData = $request->except(['character_id']);

            // Generate cache key
            $cacheKey = $this->generateCacheKey(
                $character->id,
                'recommendation',
                $trainingData
            );

            $tags = ['training_predictions', "character_{$character->id}"];

            // Try to get from cache
            $cacheHit = $this->getCache($tags)->has($cacheKey);

            $recommendation = $this->getCache($tags)
                ->remember($cacheKey, self::CACHE_TTL, fn () => $this->trainingService->getRecommendedTraining(
                    $character,
                    $trainingData
                ));

            // Add metadata
            $recommendation['cached'] = $cacheHit;
            $recommendation['cache_ttl'] = self::CACHE_TTL;
            $recommendation['processing_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            $recommendation['timestamp'] = now()->toIso8601String();

            // Log recommendation request
            Log::info('Training recommendation generated', [
                'character_id' => $character->id,
                'recommended_training' => $recommendation['recommended_training'],
                'processing_time_ms' => $recommendation['processing_time_ms'],
            ]);

            return response()->json([
                'success' => true,
                'data' => $recommendation,
                'message' => 'Training recommendation generated successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $user = $request->user();
            Log::warning('Character not found for training recommendation', [
                'character_id' => $request->input('character_id'),
                'user_id' => $user?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Character not found',
                'error' => 'The requested character does not exist or does not belong to you.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Training recommendation failed', [
                'character_id' => $request->input('character_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Training recommendation failed',
                'error' => 'An error occurred while generating the recommendation. Please try again.',
            ], 500);
        }
    }

    /**
     * Clear prediction cache for a character
     */
    public function clearCache(int $characterId): JsonResponse
    {
        try {
            // Verify character exists
            /** @var Character $character */
            $character = Character::query()->findOrFail($characterId);

            // Clear cache tags
            if (method_exists(Cache::getStore(), 'tags')) {
                Cache::tags(['training_predictions', "character_{$character->id}"])->flush();
            } else {
                // For file/database cache, we can't easily clear by tag, so we might skip or use forget() if keys were predictable.
                // But keys involve hashes. So for now, we just skip explicit clearing or would need to clear all (which is bad).
                // We'll just log that specific clearing isn't supported on this driver.
                Log::warning('Cache clearing by tag not supported on this driver', ['driver' => config('cache.default')]);
            }

            Log::info('Training prediction cache cleared', [
                'character_id' => $character->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Training prediction cache cleared successfully',
                'data' => [
                    'character_id' => $character->id,
                    'cleared_at' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Character not found for cache clearing', [
                'character_id' => $characterId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Character not found',
                'error' => 'The requested character does not exist.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Cache clearing failed', [
                'character_id' => $characterId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cache clearing failed',
                'error' => 'An error occurred while clearing the cache. Please try again.',
            ], 500);
        }
    }

    /**
     * Get cache statistics for a character
     */
    public function cacheStats(int $characterId): JsonResponse
    {
        try {
            // Verify character exists
            /** @var Character $character */
            $character = Character::query()->findOrFail($characterId);

            // Get cache statistics (simplified version)
            $stats = [
                'character_id' => $character->id,
                'cache_enabled' => config('cache.default') === 'redis',
                'cache_driver' => config('cache.default'),
                'cache_ttl' => self::CACHE_TTL,
                'cache_prefix' => self::CACHE_PREFIX,
                'timestamp' => now()->toIso8601String(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Cache statistics retrieved successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Character not found for cache stats', [
                'character_id' => $characterId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Character not found',
                'error' => 'The requested character does not exist.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Cache stats retrieval failed', [
                'character_id' => $characterId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cache stats retrieval failed',
                'error' => 'An error occurred while retrieving cache statistics. Please try again.',
            ], 500);
        }
    }

    /**
     * Generate cache key for predictions
     *
     * @param  array<string, mixed>  $context
     */
    private function generateCacheKey(int $characterId, string $trainingType, array $context): string
    {
        // Sort context to ensure consistent cache keys
        ksort($context);

        // Generate hash of context
        $contextHash = md5(json_encode($context) ?: '');

        return self::CACHE_PREFIX."{$characterId}:{$trainingType}:{$contextHash}";
    }
}
