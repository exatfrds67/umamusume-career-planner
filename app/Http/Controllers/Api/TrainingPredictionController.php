<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BatchTrainingPredictionRequest;
use App\Http\Requests\Api\TrainingPredictionRequest;
use App\Http\Requests\Api\TrainingRecommendationRequest;
use App\Http\Resources\Api\TrainingPredictionResource;
use App\Models\Character;
use App\Services\TrainingCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
     * Get single training prediction
     */
    public function predict(TrainingPredictionRequest $request): TrainingPredictionResource
    {
        $startTime = microtime(true);

        // Load character with relationships
        $character = Character::with(['aptitudes', 'supportCards.supportCard', 'factors'])
            ->findOrFail($request->input('character_id'));

        // Generate cache key
        $cacheKey = $this->generateCacheKey(
            $character->id,
            $request->input('training_type'),
            $request->except(['character_id', 'training_type'])
        );

        // Try to get from cache
        $cacheHit = Cache::tags(['training_predictions', "character_{$character->id}"])->has($cacheKey);

        $prediction = Cache::tags(['training_predictions', "character_{$character->id}"])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($character, $request, $startTime) {
                // Calculate prediction
                $prediction = $this->trainingService->calculateTrainingPrediction(
                    $character,
                    $request->input('training_type'),
                    $request->except(['character_id', 'training_type'])
                );

                // Add MCP optimization if requested
                if ($request->input('use_mcp', false)) {
                    $mcpOptimization = $this->trainingService->getMCPOptimization(
                        $character,
                        $request->input('mcp_context', [])
                    );

                    if ($mcpOptimization) {
                        $prediction['mcp_optimization'] = $mcpOptimization;
                    }
                }

                // Add metadata
                $prediction['training_type'] = $request->input('training_type');
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
            'training_type' => $request->input('training_type'),
            'cached' => $prediction['cached'],
            'processing_time_ms' => $prediction['processing_time_ms'],
        ]);

        return new TrainingPredictionResource($prediction);
    }

    /**
     * Get batch training predictions for multiple training types
     */
    public function batchPredict(BatchTrainingPredictionRequest $request): AnonymousResourceCollection
    {
        $startTime = microtime(true);

        // Load character with relationships
        $character = Character::with(['aptitudes', 'supportCards.supportCard', 'factors'])
            ->findOrFail($request->input('character_id'));

        $trainingTypes = $request->input('training_types');
        $trainingData = $request->except(['character_id', 'training_types', 'include_recommendations']);

        // Generate cache key for batch prediction
        $cacheKey = $this->generateCacheKey(
            $character->id,
            'batch_'.implode('_', $trainingTypes),
            $trainingData
        );

        // Try to get from cache
        $cacheHit = Cache::tags(['training_predictions', "character_{$character->id}"])->has($cacheKey);

        $predictions = Cache::tags(['training_predictions', "character_{$character->id}"])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($character, $trainingTypes, $trainingData) {
                // Calculate batch predictions
                $batchPredictions = $this->trainingService->calculateBatchPredictions(
                    $character,
                    $trainingTypes,
                    $trainingData
                );

                // Add metadata to each prediction
                $predictions = [];
                foreach ($batchPredictions as $trainingType => $prediction) {
                    $prediction['training_type'] = $trainingType;
                    $prediction['cached'] = false;
                    $prediction['cache_ttl'] = self::CACHE_TTL;
                    $prediction['timestamp'] = now()->toIso8601String();
                    $predictions[] = $prediction;
                }

                return $predictions;
            });

        // Update cached flag and processing time
        $processingTime = round((microtime(true) - $startTime) * 1000, 2);
        foreach ($predictions as &$prediction) {
            if ($cacheHit) {
                $prediction['cached'] = true;
            }
            $prediction['processing_time_ms'] = $processingTime;
        }

        // Add recommendations if requested
        if ($request->input('include_recommendations', false)) {
            $recommendation = $this->trainingService->getRecommendedTraining(
                $character,
                $trainingData
            );

            // Add recommendation to the response
            foreach ($predictions as &$prediction) {
                if ($prediction['training_type'] === $recommendation['recommended_training']) {
                    $prediction['recommendation'] = [
                        'is_recommended' => true,
                        'reason' => $recommendation['reason'],
                        'rank' => 1,
                    ];
                } else {
                    $prediction['recommendation'] = [
                        'is_recommended' => false,
                        'rank' => null,
                    ];
                }
            }
        }

        // Log batch prediction request
        Log::info('Batch training predictions generated', [
            'character_id' => $character->id,
            'training_types' => $trainingTypes,
            'count' => count($predictions),
            'cached' => $cacheHit,
            'processing_time_ms' => $processingTime,
        ]);

        return TrainingPredictionResource::collection($predictions);
    }

    /**
     * Get recommended training option with reasoning
     */
    public function recommend(TrainingRecommendationRequest $request): JsonResponse
    {
        $startTime = microtime(true);

        // Load character with relationships
        $character = Character::with(['aptitudes', 'supportCards.supportCard', 'factors'])
            ->findOrFail($request->input('character_id'));

        $trainingData = $request->except(['character_id']);

        // Generate cache key
        $cacheKey = $this->generateCacheKey(
            $character->id,
            'recommendation',
            $trainingData
        );

        // Try to get from cache
        $cacheHit = Cache::tags(['training_predictions', "character_{$character->id}"])->has($cacheKey);

        $recommendation = Cache::tags(['training_predictions', "character_{$character->id}"])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($character, $trainingData) {
                return $this->trainingService->getRecommendedTraining(
                    $character,
                    $trainingData
                );
            });

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
    }

    /**
     * Clear prediction cache for a character
     */
    public function clearCache(int $characterId): JsonResponse
    {
        // Verify character exists
        $character = Character::findOrFail($characterId);

        // Clear cache tags
        Cache::tags(['training_predictions', "character_{$character->id}"])->flush();

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
    }

    /**
     * Get cache statistics for a character
     */
    public function cacheStats(int $characterId): JsonResponse
    {
        // Verify character exists
        $character = Character::findOrFail($characterId);

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
