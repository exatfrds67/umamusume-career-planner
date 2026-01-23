<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TrainingAdviceRequest;
use App\Services\Neuron\TrainingAdvisorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Training Advisor API Controller
 *
 * Provides RESTful endpoints for AI-powered training advice using Neuron AI agents.
 * Supports both standard JSON responses and streaming responses for real-time feedback.
 *
 * **Validates: Requirements 7.5, 12.1, 12.2, 12.3, 12.4, 12.5**
 */
class TrainingAdvisorController extends Controller
{
    /**
     * Create a new Training Advisor Controller instance.
     *
     * Injects TrainingAdvisorService for agent interactions.
     */
    public function __construct(
        private readonly TrainingAdvisorService $trainingAdvisorService
    ) {}

    /**
     * Get training advice for a character.
     *
     * Returns structured training recommendations based on character stats,
     * aptitudes, support cards, and current training context.
     *
     * @param  TrainingAdviceRequest  $request  Validated request with character_id and training_options
     * @return JsonResponse Training advice with recommendations and reasoning
     */
    public function getAdvice(TrainingAdviceRequest $request): JsonResponse
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
            /** @var array<string, mixed> $trainingOptions */
            $trainingOptions = $validated['training_options'] ?? [];

            // Validate training options structure
            $validationErrors = $this->trainingAdvisorService->validateTrainingOptions($trainingOptions);
            if (! empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid training options structure.',
                    'errors' => $validationErrors,
                ], 422);
            }

            // Get training advice from service
            $advice = $this->trainingAdvisorService->getAdvice(
                $characterId,
                $trainingOptions,
                $user?->id ?? throw new \Exception('User required')
            );

            // Parse response for API format
            $responseData = $this->trainingAdvisorService->parseResponse($advice);

            // Add metadata
            $responseData['processing_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
            $responseData['timestamp'] = now()->toIso8601String();

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Training advice generated successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Training advice request for non-existent character', [
                'character_id' => $request->integer('character_id'),
                'user_id' => $request->user()?->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Character not found.',
            ], 404);
        } catch (\Exception $e) {
            // Log error with context
            Log::error('Training advice generation failed', [
                'character_id' => $request->integer('character_id'),
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to generate training advice. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 503);
        }
    }

    /**
     * Get streaming training advice for a character.
     *
     * Returns training advice as a Server-Sent Events (SSE) stream for real-time
     * feedback. Useful for displaying progressive responses in the UI.
     *
     * @param  TrainingAdviceRequest  $request  Validated request with character_id and training_options
     * @return StreamedResponse SSE stream of training advice
     */
    public function getAdviceStreaming(TrainingAdviceRequest $request): StreamedResponse
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
        /** @var array<string, mixed> $trainingOptions */
        $trainingOptions = $validated['training_options'] ?? [];

        // Validate training options structure
        $validationErrors = $this->trainingAdvisorService->validateTrainingOptions($trainingOptions);
        if (! empty($validationErrors)) {
            return response()->stream(function () use ($validationErrors) {
                echo 'data: '.json_encode([
                    'error' => 'Invalid training options structure.',
                    'validation_errors' => $validationErrors,
                ])."\n\n";
                flush();
            }, 422, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        return response()->stream(function () use ($characterId, $trainingOptions, $user) {
            try {
                // Stream advice from service
                foreach ($this->trainingAdvisorService->getAdviceStreaming($characterId, $trainingOptions, $user?->id ?? throw new \Exception('User required')) as $chunk) {
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
                Log::warning('Streaming training advice request for non-existent character', [
                    'character_id' => $characterId,
                    'user_id' => $user?->id ?? throw new \Exception('User required'),
                ]);

                echo 'data: '.json_encode([
                    'error' => 'Character not found.',
                ])."\n\n";
                flush();
            } catch (\Exception $e) {
                // Log error with context
                Log::error('Streaming training advice generation failed', [
                    'character_id' => $characterId,
                    'user_id' => $user?->id ?? throw new \Exception('User required'),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                echo 'data: '.json_encode([
                    'error' => 'Unable to generate training advice. Please try again.',
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
     * Get training advice history for a character.
     *
     * Retrieves past training advice from chat history for review and analysis.
     *
     * @param  int  $characterId  The character ID
     * @return JsonResponse Array of past advice messages
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

            // Get advice history from service
            $history = $this->trainingAdvisorService->getAdviceHistory(
                $characterId,
                $user?->id ?? throw new \Exception('User required'),
                limit: 20
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'character_id' => $characterId,
                    'history' => $history,
                    'count' => count($history),
                ],
                'message' => 'Training advice history retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve training advice history', [
                'character_id' => $characterId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve training advice history.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
