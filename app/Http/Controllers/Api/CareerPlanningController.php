<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CareerPlanningRequest;
use App\Services\Neuron\CareerPlanningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Career Planning API Controller
 *
 * Provides RESTful endpoints for AI-powered long-term career planning.
 * Supports both standard JSON responses and streaming responses.
 *
 * **Validates: Requirements 7.5, 12.1, 12.2, 12.3, 12.4, 12.5**
 */
class CareerPlanningController extends Controller
{
    /**
     * Create a new Career Planning Controller instance.
     */
    public function __construct(
        private readonly CareerPlanningService $careerPlanningService
    ) {}

    /**
     * Get career planning guidance for a character.
     */
    public function getPlan(CareerPlanningRequest $request): JsonResponse
    {
        $startTime = microtime(true);

        try {
            $user = $request->user();
            if ($user === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }

            $validated = $request->validated();
            /** @var int $characterId */
            $characterId = $validated['character_id'];
            /** @var array<string, mixed> $planningContext */
            $planningContext = $validated['planning_context'] ?? [];

            $validationErrors = $this->careerPlanningService->validatePlanningContext($planningContext);
            if (! empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid planning context structure.',
                    'errors' => $validationErrors,
                ], 422);
            }

            $plan = $this->careerPlanningService->getPlan(
                $characterId,
                $planningContext,
                $user->id ?? throw new \Exception('User required')
            );

            $responseData = $this->careerPlanningService->parseResponse($plan);
            $responseData['processing_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            $responseData['timestamp'] = now()->toIso8601String();

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Career plan generated successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Career planning request for non-existent character', [
                'character_id' => $request->integer('character_id'),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Character not found.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Career planning generation failed', [
                'character_id' => $request->integer('character_id'),
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to generate career plan. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 503);
        }
    }

    /**
     * Get streaming career planning guidance for a character.
     */
    public function getPlanStreaming(CareerPlanningRequest $request): StreamedResponse
    {
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

        $validated = $request->validated();
        /** @var int $characterId */
        $characterId = $validated['character_id'];
        /** @var array<string, mixed> $planningContext */
        $planningContext = $validated['planning_context'] ?? [];

        $validationErrors = $this->careerPlanningService->validatePlanningContext($planningContext);
        if (! empty($validationErrors)) {
            return response()->stream(function () use ($validationErrors) {
                echo 'data: '.json_encode([
                    'error' => 'Invalid planning context structure.',
                    'validation_errors' => $validationErrors,
                ])."\n\n";
                flush();
            }, 422, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        return response()->stream(function () use ($characterId, $planningContext, $user) {
            try {
                foreach ($this->careerPlanningService->getPlanStreaming($characterId, $planningContext, $user->id ?? throw new \Exception('User required')) as $chunk) {
                    echo 'data: '.json_encode([
                        'chunk' => $chunk,
                    ])."\n\n";
                    flush();
                }

                echo 'data: '.json_encode([
                    'done' => true,
                ])."\n\n";
                flush();
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::warning('Streaming career planning request for non-existent character', [
                    'character_id' => $characterId,
                    'user_id' => $user->id ?? throw new \Exception('User required'),
                ]);

                echo 'data: '.json_encode([
                    'error' => 'Character not found.',
                ])."\n\n";
                flush();
            } catch (\Exception $e) {
                Log::error('Streaming career planning generation failed', [
                    'character_id' => $characterId,
                    'user_id' => $user->id ?? throw new \Exception('User required'),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                echo 'data: '.json_encode([
                    'error' => 'Unable to generate career plan. Please try again.',
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
     * Get career planning history for a character.
     */
    public function getHistory(int $characterId): JsonResponse
    {
        try {
            $user = auth()->user();
            if ($user === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                ], 401);
            }

            $history = $this->careerPlanningService->getPlanHistory(
                $characterId,
                $user->id ?? throw new \Exception('User required'),
                limit: 20
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'character_id' => $characterId,
                    'history' => $history,
                    'count' => count($history),
                ],
                'message' => 'Career planning history retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve career planning history', [
                'character_id' => $characterId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve career planning history.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
