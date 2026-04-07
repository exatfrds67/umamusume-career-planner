<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AsyncCareerPlanRequest;
use App\Http\Requests\Api\CareerPlanningRequest;
use App\Http\Requests\Api\LockCareerPlanRequest;
use App\Jobs\GenerateCareerPlan;
use App\Jobs\SendTurnNotification;
use App\Models\CareerPlan;
use App\Models\Character;
use App\Services\Neuron\CareerPlanningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

    /**
     * Queue an asynchronous timeline-based career plan.
     */
    public function requestTimelinePlan(AsyncCareerPlanRequest $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $validated = $request->validated();
        $character = Character::query()->findOrFail($request->integer('character_id'));
        $this->authorize('view', $character);

        $jobId = (string) Str::uuid();
        $planId = (string) Str::uuid();

        CareerPlan::query()->create([
            'id' => $planId,
            'user_id' => (int) $user->id,
            'character_id' => $character->id,
            'goal' => isset($validated['goal']) && is_string($validated['goal']) ? $validated['goal'] : null,
            'plan' => [
                'plan_id' => $planId,
                'character_id' => $character->id,
                'created_at' => now()->toIso8601String(),
                'goal' => $validated['goal'] ?? 'Complete the career',
                'status' => 'queued',
                'job_id' => $jobId,
                'total_turns' => 0,
                'timeline' => [],
                'summary' => [],
                'metadata' => [
                    'options' => $validated['options'] ?? [],
                ],
            ],
            'is_locked' => false,
            'current_turn' => 1,
        ]);

        Cache::put("career-plan-job:{$jobId}", [
            'status' => 'queued',
            'plan_id' => $planId,
        ], now()->addDay());

        GenerateCareerPlan::dispatch(
            $jobId,
            $planId,
            $character->id,
            [
                'goal' => $validated['goal'] ?? null,
                'options' => is_array($validated['options'] ?? null) ? $validated['options'] : [],
            ]
        );

        return response()->json([
            'success' => true,
            'job_id' => $jobId,
            'plan_id' => $planId,
            'status_url' => url("/api/career-planning/plan/jobs/{$jobId}"),
        ], 202);
    }

    /**
     * Return the status of an asynchronous plan generation job.
     */
    public function getTimelinePlanStatus(string $jobId): JsonResponse
    {
        $status = Cache::get("career-plan-job:{$jobId}");
        if (! is_array($status)) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'job_id' => $jobId,
                'status' => $status['status'] ?? 'unknown',
                'plan_id' => $status['plan_id'] ?? null,
                'error' => $status['error'] ?? null,
            ],
        ]);
    }

    /**
     * Retrieve a stored timeline-based career plan.
     */
    public function showTimelinePlan(string $planId): JsonResponse
    {
        $user = auth()->user();
        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $plan = CareerPlan::query()
            ->where('id', $planId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (! $plan->isCompleted()) {
            return response()
                ->json([
                    'success' => true,
                    'status' => $plan->status(),
                    'plan_id' => $plan->id,
                ], 202)
                ->header('Retry-After', '10');
        }

        return response()->json([
            'success' => true,
            'plan' => $plan->plan,
        ]);
    }

    /**
     * Lock a generated plan and optionally store notification preferences.
     */
    public function lockTimelinePlan(LockCareerPlanRequest $request, string $planId): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $plan = CareerPlan::query()
            ->where('id', $planId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($plan->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Plan already locked.',
            ], 400);
        }

        if (! $plan->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Plan is not ready to be locked yet.',
            ], 409);
        }

        $preferences = $request->validated('notification_preferences', []);
        $plan->storeNotificationPreferences(is_array($preferences) ? $preferences : []);
        $plan->is_locked = true;
        $plan->locked_at = now();
        $plan->current_turn = $request->integer('start_turn', 1);
        $plan->save();

        SendTurnNotification::dispatch($plan->id);

        return response()->json([
            'success' => true,
            'message' => 'Plan locked.',
        ]);
    }

    /**
     * Get the next planned action from a locked plan.
     */
    public function nextTimelineAction(string $planId): JsonResponse
    {
        $user = auth()->user();
        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $plan = CareerPlan::query()
            ->where('id', $planId)
            ->where('user_id', $user->id)
            ->where('is_locked', true)
            ->firstOrFail();

        $next = $plan->getNextAction();
        if ($next === null) {
            return response()->json([
                'success' => false,
                'message' => 'No further actions. Plan complete?',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'turn' => $plan->current_turn,
            'action' => $next['action'] ?? [],
            'remaining_turns' => max(
                0,
                ((is_numeric($plan->plan['total_turns'] ?? null) ? (int) $plan->plan['total_turns'] : 0) - $plan->current_turn)
            ),
        ]);
    }

    /**
     * Advance the tracked turn for a locked plan.
     */
    public function advanceTimelineTurn(string $planId): JsonResponse
    {
        $user = auth()->user();
        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $plan = CareerPlan::query()
            ->where('id', $planId)
            ->where('user_id', $user->id)
            ->where('is_locked', true)
            ->firstOrFail();

        $plan->advanceTurn();
        SendTurnNotification::dispatch($plan->id);

        return response()->json([
            'success' => true,
            'new_turn' => $plan->current_turn,
        ]);
    }
}
