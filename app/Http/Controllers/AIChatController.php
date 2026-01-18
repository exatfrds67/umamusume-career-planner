<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\MCP\AgentOrchestrationService;
use App\Services\MCP\AgentRoutingService;
use App\Services\MCP\MCPMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AIChatController extends Controller
{
    public function __construct(
        private readonly AgentOrchestrationService $orchestrationService,
        private readonly AgentRoutingService $routingService,
        private readonly MCPMonitoringService $monitoringService
    ) {}

    /**
     * Display the AI chat interface
     */
    public function index(Request $request): View
    {
        $character = null;

        // Load character context if provided
        if ($request->has('character_id')) {
            $character = Character::with(['currentCareer', 'aptitudes', 'skills'])
                ->where('user_id', Auth::id())
                ->findOrFail($request->integer('character_id'));
        }

        return view('ai.chat', [
            'character' => $character,
        ]);
    }

    /**
     * Send a message to the AI and get a response
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'character_id' => 'nullable|integer|exists:ucp_characters,id',
            'career_id' => 'nullable|integer|exists:ucp_careers,id',
            'provider' => 'nullable|string|in:ollama,bedrock,agent',
            'agent_type' => 'nullable|string|in:training,career,race,skill',
            'conversation_id' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        if (! is_int($userId)) {
            return response()->json([
                'success' => false,
                'error' => 'Authentication required.',
            ], 401);
        }

        try {
            // Build context from character and career data
            $context = $this->buildContext($validated);

            $conversationId = $validated['conversation_id'] ?? Str::uuid()->toString();

            $requestPayload = [
                'prompt' => $validated['message'],
                'context' => $context,
                'agent_type' => $validated['agent_type'] ?? null,
                'preferred_provider' => $validated['provider'] ?? null,
            ];

            /** @var array{response: mixed, model: string, provider: string, execution_time: float, cost: float} $execution */
            $execution = $this->routingService->executeWithFallback($requestPayload);
            $responseContent = $execution['response'];
            $encodedResponse = json_encode($responseContent);
            $encodedResponse = $encodedResponse === false ? '' : $encodedResponse;

            $content = match (true) {
                is_string($responseContent) => $responseContent,
                is_array($responseContent) => (string) ($responseContent['content'] ?? $encodedResponse),
                default => $encodedResponse,
            };

            /** @var array{content: string, model: string, provider: string, processing_time: float, tokens: int|null, cost: float, tools_used: array<int, string>, agent: string|null, confidence: float|null} $response */
            $response = [
                'content' => $content,
                'model' => $execution['model'],
                'provider' => $execution['provider'],
                'processing_time' => $execution['execution_time'],
                'tokens' => null,
                'cost' => $execution['cost'],
                'tools_used' => [],
                'agent' => is_array($responseContent) ? ($responseContent['agent'] ?? null) : null,
                'confidence' => is_array($responseContent) ? ($responseContent['confidence'] ?? null) : null,
            ];

            // Log the conversation
            $this->logConversation(
                userId: $userId,
                characterId: $validated['character_id'] ?? null,
                conversationId: $conversationId,
                message: $validated['message'],
                response: $response
            );

            return response()->json([
                'success' => true,
                'message' => $response['content'],
                'metadata' => [
                    'model' => $response['model'],
                    'provider' => $response['provider'],
                    'agent' => $response['agent'] ?? null,
                    'confidence' => $response['confidence'] ?? null,
                    'processing_time' => $response['processing_time'],
                    'tokens' => $response['tokens'] ?? null,
                    'cost' => $response['cost'] ?? null,
                    'tools_used' => $response['tools_used'] ?? [],
                ],
                'conversation_id' => $conversationId,
            ]);
        } catch (\Exception $e) {
            Log::error('AI chat message failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'message' => $validated['message'],
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to process your message. Please try again.',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get MCP server status
     */
    public function getServerStatus(): JsonResponse
    {
        try {
            $status = $this->monitoringService->performHealthCheck();

            return response()->json([
                'success' => true,
                'servers' => $status,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get server status', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve server status',
                'servers' => [],
            ], 500);
        }
    }

    /**
     * Get current agent workflow status
     */
    public function getWorkflowStatus(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $workflow = $this->orchestrationService->getCurrentWorkflow(
                is_int($userId) ? $userId : null
            );

            return response()->json([
                'success' => true,
                'workflow' => $workflow,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get workflow status', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve workflow status',
                'workflow' => null,
            ], 500);
        }
    }

    /**
     * Get active MCP tool usage
     */
    public function getToolUsage(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $tools = $this->monitoringService->getActiveTools(
                is_int($userId) ? $userId : null
            );

            return response()->json([
                'success' => true,
                'tools' => $tools,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get tool usage', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve tool usage',
                'tools' => [],
            ], 500);
        }
    }

    /**
     * Get or update user preferences
     */
    public function preferences(Request $request): JsonResponse
    {
        if ($request->isMethod('POST')) {
            $validated = $request->validate([
                'provider' => 'nullable|string|in:ollama,bedrock,agent',
                'model' => 'nullable|string|max:255',
                'selected_agent' => 'nullable|string|in:training,career,race,skill',
                'auto_fallback' => 'nullable|boolean',
            ]);

            // Store preferences in cache (or database if needed)
            $cacheKey = 'ai_chat_preferences_'.Auth::id();
            $preferences = Cache::get($cacheKey, []);
            $preferences = is_array($preferences) ? $preferences : [];
            $preferences = array_merge($preferences, $validated);
            Cache::put($cacheKey, $preferences, now()->addDays(30));

            return response()->json([
                'success' => true,
                'preferences' => $preferences,
            ]);
        }

        // GET request - return current preferences
        $cacheKey = 'ai_chat_preferences_'.Auth::id();
        $preferences = Cache::get($cacheKey, [
            'provider' => 'ollama',
            'model' => 'llama3.3',
            'selected_agent' => null,
            'auto_fallback' => true,
        ]);

        return response()->json([
            'success' => true,
            'preferences' => $preferences,
        ]);
    }

    /**
     * Build context from character and career data
     */
    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function buildContext(array $validated): array
    {
        $context = [
            'user_id' => Auth::id(),
        ];

        if (isset($validated['character_id'])) {
            $characterId = $validated['character_id'];
            if (! is_int($characterId)) {
                return $context;
            }

            $character = Character::with(['currentCareer', 'aptitudes', 'skills', 'factors'])
                ->findOrFail($characterId);

            $context['character'] = [
                'id' => $character->id,
                'name' => $character->name,
                'scenario_type' => $character->scenario_type,
                'career_stage' => $character->career_stage,
                'stats' => $character->current_stats,
                'energy_level' => $character->energy_level,
                'mood_status' => $character->mood_status,
                'aptitudes' => $character->aptitudes->toArray(),
                'skills' => $character->skills->toArray(),
                'factors' => $character->factors->toArray(),
            ];

            if ($character->currentCareer) {
                $context['career'] = [
                    'id' => $character->currentCareer->id,
                    'current_turn' => $character->currentCareer->current_turn,
                    'scenario_type' => $character->currentCareer->scenario_type,
                    'training_history' => $character->currentCareer->trainingSessions()
                        ->latest()
                        ->limit(5)
                        ->get()
                        ->toArray(),
                ];
            }
        }

        return $context;
    }

    /**
     * Log conversation to database
     *
     * @param array{
     *   content: string,
     *   model: string,
     *   provider: string,
     *   processing_time: float,
     *   tokens: int|null,
     *   cost: float,
     *   tools_used: array<int, string>,
     *   agent: string|null,
     *   confidence: float|null
     * } $response
     */
    private function logConversation(
        int $userId,
        ?int $characterId,
        ?string $conversationId,
        string $message,
        array $response
    ): void {
        try {
            \App\Models\AIConversation::create([
                'user_id' => $userId,
                'character_id' => $characterId,
                'conversation_id' => $conversationId ?? \Illuminate\Support\Str::uuid()->toString(),
                'message_type' => 'user',
                'message_content' => $message,
                'ai_model_used' => null,
                'processing_time' => null,
                'tokens_used' => null,
                'cost_estimate' => null,
            ]);

            \App\Models\AIConversation::create([
                'user_id' => $userId,
                'character_id' => $characterId,
                'conversation_id' => $conversationId ?? \Illuminate\Support\Str::uuid()->toString(),
                'message_type' => 'ai',
                'message_content' => $response['content'],
                'ai_model_used' => $response['model'],
                'processing_time' => $response['processing_time'],
                'tokens_used' => $response['tokens'] ?? null,
                'cost_estimate' => $response['cost'] ?? null,
                'metadata' => json_encode([
                    'provider' => $response['provider'],
                    'agent' => $response['agent'] ?? null,
                    'confidence' => $response['confidence'] ?? null,
                    'tools_used' => $response['tools_used'] ?? [],
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log conversation', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
        }
    }
}
