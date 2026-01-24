<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SkillRecommendationRequest;
use App\Services\Neuron\SkillRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Skill Recommendation API Controller
 *
 * Provides RESTful endpoints for AI-powered skill recommendations using Neuron AI agents.
 * Supports both standard JSON responses and streaming responses for real-time feedback.
 *
 * **Validates: Requirements 7.5, 12.1, 12.2, 12.3, 12.4, 12.5**
 */
class SkillRecommendationController extends Controller
{
    /**
     * Create a new Skill Recommendation Controller instance.
     *
     * Injects SkillRecommendationService for agent interactions.
     */
    public function __construct(
        private readonly SkillRecommendationService $skillRecommendationService
    ) {}

    /**
     * Get skill recommendations for a character.
     *
     * Returns structured skill recommendations based on character stats,
     * aptitudes, acquired skills, available hints, and build strategy.
     *
     * @param  SkillRecommendationRequest  $request  Validated request with character_id and skill_context
     * @return JsonResponse Skill recommendations with acquisition strategy
     */
    public function getRecommendations(SkillRecommendationRequest $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            // Get authenticated user
            $user = $request->user();
            if ($user === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }

            // Extract validated data
            $validated = $request->validated();
            /** @var int $characterId */
            $characterId = $validated['character_id'];
            /** @var array<string, mixed> $skillContext */
            $skillContext = $validated['skill_context'] ?? [];

            // Validate skill context structure
            $validationErrors = $this->skillRecommendationService->validateSkillContext($skillContext);
            if (! empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid skill context structure.',
                    'errors' => $validationErrors,
                ], 422);
            }

            // Get skill recommendations from service
            $recommendations = $this->skillRecommendationService->getRecommendations(
                $characterId,
                $skillContext,
                $user->id ?? throw new \Exception('User required')
            );

            // Parse response for API format
            $responseData = $this->skillRecommendationService->parseResponse($recommendations);

            // Add metadata
            $responseData['processing_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            $responseData['timestamp'] = now()->toIso8601String();

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Skill recommendations generated successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Skill recommendation request for non-existent character', [
                'character_id' => $request->integer('character_id'),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Character not found.',
            ], 404);
        } catch (\Exception $e) {
            // Log error with context
            Log::error('Skill recommendation generation failed', [
                'character_id' => $request->integer('character_id'),
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to generate skill recommendations. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 503);
        }
    }

    /**
     * Get streaming skill recommendations for a character.
     *
     * Returns skill recommendations as a Server-Sent Events (SSE) stream for real-time
     * feedback. Useful for displaying progressive responses in the UI.
     *
     * @param  SkillRecommendationRequest  $request  Validated request with character_id and skill_context
     * @return StreamedResponse SSE stream of skill recommendations
     */
    public function getRecommendationsStreaming(SkillRecommendationRequest $request): StreamedResponse
    {
        // Get authenticated user
        $user = $request->user();
        if ($user === null) {
            return response()->stream(function () {
                echo 'data: '.json_encode([
                    'error' => 'Authentication required.',
                ])."\n\n";
                flush();
            }, 401, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        // Extract validated data
        $validated = $request->validated();
        /** @var int $characterId */
        $characterId = $validated['character_id'];
        /** @var array<string, mixed> $skillContext */
        $skillContext = $validated['skill_context'] ?? [];

        // Validate skill context structure
        $validationErrors = $this->skillRecommendationService->validateSkillContext($skillContext);
        if (! empty($validationErrors)) {
            return response()->stream(function () use ($validationErrors) {
                echo 'data: '.json_encode([
                    'error' => 'Invalid skill context structure.',
                    'validation_errors' => $validationErrors,
                ])."\n\n";
                flush();
            }, 422, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        return response()->stream(function () use ($characterId, $skillContext, $user) {
            try {
                // Stream recommendations from service
                foreach ($this->skillRecommendationService->getRecommendationsStreaming($characterId, $skillContext, $user->id ?? throw new \Exception('User required')) as $chunk) {
                    echo 'data: '.json_encode([
                        'chunk' => $chunk,
                    ])."\n\n";
                    flush();
                }

                // Send completion event
                echo 'data: '.json_encode([
                    'done' => true,
                ])."\n\n";
                flush();
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::warning('Streaming skill recommendation request for non-existent character', [
                    'character_id' => $characterId,
                    'user_id' => $user->id ?? throw new \Exception('User required'),
                ]);

                echo 'data: '.json_encode([
                    'error' => 'Character not found.',
                ])."\n\n";
                flush();
            } catch (\Exception $e) {
                // Log error with context
                Log::error('Streaming skill recommendation generation failed', [
                    'character_id' => $characterId,
                    'user_id' => $user->id ?? throw new \Exception('User required'),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                echo 'data: '.json_encode([
                    'error' => 'Unable to generate skill recommendations. Please try again.',
                ])."\n\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get skill acquisition history for a character.
     *
     * Retrieves past skill acquisitions with details for review and analysis.
     *
     * @param  int  $characterId  The character ID
     * @return JsonResponse Array of past skill acquisitions
     */
    public function getHistory(int $characterId): JsonResponse
    {
        try {
            // Get authenticated user
            $user = auth()->user();
            if ($user === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }

            // Get acquisition history from service
            $history = $this->skillRecommendationService->getAcquisitionHistory(
                $characterId,
                limit: 20
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'character_id' => $characterId,
                    'history' => $history,
                    'count' => count($history),
                ],
                'message' => 'Skill acquisition history retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve skill acquisition history', [
                'character_id' => $characterId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve skill acquisition history.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get skill synergies for a character.
     *
     * Analyzes acquired skills and identifies synergies between them.
     *
     * @param  int  $characterId  The character ID
     * @return JsonResponse Array of skill synergies
     */
    public function getSynergies(int $characterId): JsonResponse
    {
        try {
            // Get authenticated user
            $user = auth()->user();
            if ($user === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }

            // Calculate skill synergies from service
            $synergies = $this->skillRecommendationService->calculateSkillSynergies($characterId);

            return response()->json([
                'success' => true,
                'data' => [
                    'character_id' => $characterId,
                    'synergies' => $synergies,
                    'count' => count($synergies),
                ],
                'message' => 'Skill synergies calculated successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to calculate skill synergies', [
                'character_id' => $characterId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate skill synergies.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
