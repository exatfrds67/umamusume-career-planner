<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RaceStrategyRequest;
use App\Services\Neuron\RaceStrategyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Race Strategy API Controller
 *
 * Provides RESTful endpoints for AI-powered race strategy recommendations using Neuron AI agents.
 * Supports both standard JSON responses and streaming responses for real-time feedback.
 *
 * **Validates: Requirements 7.5, 12.1, 12.2, 12.3, 12.4, 12.5**
 */
class RaceStrategyController extends Controller
{
    /**
     * Create a new Race Strategy Controller instance.
     *
     * Injects RaceStrategyService for agent interactions.
     */
    public function __construct(
        private readonly RaceStrategyService $raceStrategyService
    ) {}

    /**
     * Get race strategy for a character and race.
     *
     * Returns structured race strategy recommendations based on character stats,
     * aptitudes, skills, race conditions, and track characteristics.
     *
     * @param  RaceStrategyRequest  $request  Validated request with character_id and race_data
     * @return JsonResponse Race strategy with recommendations and analysis
     */
    public function getStrategy(RaceStrategyRequest $request): JsonResponse
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
            $characterId = $request->integer('character_id');
            $raceData = $request->input('race_data', []);

            // Ensure race_data is an array with string keys
            if (! is_array($raceData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid race data format.',
                ], 422);
            }

            /** @var array<string, mixed> $raceData */

            // Validate race data structure
            $validationErrors = $this->raceStrategyService->validateRaceData($raceData);
            if (! empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid race data structure.',
                    'errors' => $validationErrors,
                ], 422);
            }

            // Get race strategy from service
            $strategy = $this->raceStrategyService->getStrategy(
                $characterId,
                $raceData,
                $user->id ?? throw new \Exception('User required')
            );

            // Parse response for API format
            $responseData = $this->raceStrategyService->parseResponse($strategy);

            // Add metadata
            $responseData['processing_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            $responseData['timestamp'] = now()->toIso8601String();

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Race strategy generated successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Race strategy request for non-existent character', [
                'character_id' => $request->integer('character_id'),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Character not found.',
            ], 404);
        } catch (\Exception $e) {
            // Log error with context
            Log::error('Race strategy generation failed', [
                'character_id' => $request->integer('character_id'),
                'user_id' => $request->user()?->id,
                'race_name' => $request->input('race_data.race_name'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to generate race strategy. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 503);
        }
    }

    /**
     * Get streaming race strategy for a character and race.
     *
     * Returns race strategy as a Server-Sent Events (SSE) stream for real-time
     * feedback. Useful for displaying progressive responses in the UI.
     *
     * @param  RaceStrategyRequest  $request  Validated request with character_id and race_data
     * @return StreamedResponse SSE stream of race strategy
     */
    public function getStrategyStreaming(RaceStrategyRequest $request): StreamedResponse
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
        $characterId = $request->integer('character_id');
        $raceData = $request->input('race_data', []);

        // Ensure race_data is an array
        if (! is_array($raceData)) {
            return response()->stream(function () {
                echo 'data: '.json_encode([
                    'error' => 'Invalid race data format.',
                ])."\n\n";
                flush();
            }, 422, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        /** @var array<string, mixed> $raceData */

        // Validate race data structure
        $validationErrors = $this->raceStrategyService->validateRaceData($raceData);
        if (! empty($validationErrors)) {
            return response()->stream(function () use ($validationErrors) {
                echo 'data: '.json_encode([
                    'error' => 'Invalid race data structure.',
                    'validation_errors' => $validationErrors,
                ])."\n\n";
                flush();
            }, 422, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        return response()->stream(function () use ($characterId, $raceData, $user) {
            try {
                // Stream strategy from service
                foreach ($this->raceStrategyService->getStrategyStreaming($characterId, $raceData, $user->id ?? throw new \Exception('User required')) as $chunk) {
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
                Log::warning('Streaming race strategy request for non-existent character', [
                    'character_id' => $characterId,
                    'user_id' => $user->id ?? throw new \Exception('User required'),
                ]);

                echo 'data: '.json_encode([
                    'error' => 'Character not found.',
                ])."\n\n";
                flush();
            } catch (\Exception $e) {
                // Log error with context
                Log::error('Streaming race strategy generation failed', [
                    'character_id' => $characterId,
                    'user_id' => $user->id ?? throw new \Exception('User required'),
                    'race_name' => isset($raceData['race_name']) ? $raceData['race_name'] : 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                echo 'data: '.json_encode([
                    'error' => 'Unable to generate race strategy. Please try again.',
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
     * Get race strategy history for a character.
     *
     * Retrieves past race strategy advice from chat history for review and analysis.
     *
     * @param  int  $characterId  The character ID
     * @param  int|null  $raceId  Optional race ID for specific race history
     * @return JsonResponse Array of past strategy messages
     */
    public function getHistory(int $characterId, ?int $raceId = null): JsonResponse
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

            // Get strategy history from service
            $history = $this->raceStrategyService->getStrategyHistory(
                $characterId,
                $user->id ?? throw new \Exception('User required'),
                $raceId,
                limit: 20
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'character_id' => $characterId,
                    'race_id' => $raceId,
                    'history' => $history,
                    'count' => count($history),
                ],
                'message' => 'Race strategy history retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve race strategy history', [
                'character_id' => $characterId,
                'race_id' => $raceId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve race strategy history.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get recommended skills for a race.
     *
     * Returns a list of skills that would be beneficial for the specified race
     * based on character capabilities and race conditions.
     *
     * @param  RaceStrategyRequest  $request  Validated request with character_id and race_data
     * @return JsonResponse Array of recommended skills
     */
    public function getRecommendedSkills(RaceStrategyRequest $request): JsonResponse
    {
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
            $characterId = $request->integer('character_id');
            $raceData = $request->input('race_data', []);

            // Ensure race_data is an array with string keys
            if (! is_array($raceData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid race data format.',
                ], 422);
            }

            /** @var array<string, mixed> $raceData */

            // Get recommended skills from service
            $skills = $this->raceStrategyService->getRecommendedSkills(
                $characterId,
                $raceData
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'character_id' => $characterId,
                    'race_name' => isset($raceData['race_name']) && is_string($raceData['race_name']) ? $raceData['race_name'] : 'Unknown Race',
                    'recommended_skills' => $skills,
                    'count' => count($skills),
                ],
                'message' => 'Recommended skills retrieved successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Recommended skills request for non-existent character', [
                'character_id' => $request->integer('character_id'),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Character not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve recommended skills', [
                'character_id' => $request->integer('character_id'),
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve recommended skills.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
