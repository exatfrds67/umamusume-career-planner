<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Character;
use App\Services\MCP\AgentRoutingService;
use App\Services\MCP\RealTimeMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AIChatController extends Controller
{
    public function __construct(
        private readonly AgentRoutingService $routingService,
        private readonly RealTimeMonitoringService $realTimeMonitoringService
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
        $validated = $this->validateChatRequest($request);

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

            $response = $this->executeChatRequest($validated, $context);

            $this->logConversation(
                userId: $userId,
                characterId: isset($validated['character_id']) && is_int($validated['character_id']) ? $validated['character_id'] : null,
                conversationId: is_string($conversationId) ? $conversationId : null,
                message: is_string($validated['message']) ? $validated['message'] : '',
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
                    'cost' => $response['cost'],
                    'tools_used' => $response['tools_used'],
                    'rag_enhanced' => $response['rag_enhanced'] ?? false,
                    'knowledge_sources' => $response['knowledge_sources'] ?? [],
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
     * Stream a message response using Server-Sent Events (SSE).
     */
    public function sendMessageStreaming(Request $request): StreamedResponse
    {
        $validated = $this->validateChatRequest($request);

        $userId = Auth::id();
        if (! is_int($userId)) {
            return response()->stream(function () {
                echo 'data: ' . json_encode([
                    'error' => 'Authentication required.',
                ]) . "\n\n";
                flush();
            }, 401, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        return response()->stream(function () use ($validated, $userId) {
            try {
                $context = $this->buildContext($validated);
                $conversationId = $validated['conversation_id'] ?? Str::uuid()->toString();

                $response = $this->executeChatRequest($validated, $context);

                $this->logConversation(
                    userId: $userId,
                    characterId: isset($validated['character_id']) && is_int($validated['character_id']) ? $validated['character_id'] : null,
                    conversationId: is_string($conversationId) ? $conversationId : null,
                    message: is_string($validated['message']) ? $validated['message'] : '',
                    response: $response
                );

                $chunks = $this->chunkResponse($response['content']);
                foreach ($chunks as $chunk) {
                    echo 'data: ' . json_encode([
                        'chunk' => $chunk,
                    ]) . "\n\n";
                    flush();
                }

                echo 'data: ' . json_encode([
                    'done' => true,
                    'metadata' => [
                        'model' => $response['model'],
                        'provider' => $response['provider'],
                        'agent' => $response['agent'] ?? null,
                        'confidence' => $response['confidence'] ?? null,
                        'processing_time' => $response['processing_time'],
                        'tokens' => $response['tokens'] ?? null,
                        'cost' => $response['cost'],
                        'tools_used' => $response['tools_used'],
                    ],
                    'conversation_id' => $conversationId,
                ]) . "\n\n";
                flush();
            } catch (\Exception $e) {
                Log::error('AI chat streaming failed', [
                    'error' => $e->getMessage(),
                    'user_id' => $userId,
                    'message' => $validated['message'] ?? null,
                ]);

                echo 'data: ' . json_encode([
                    'error' => 'Failed to process your message. Please try again.',
                ]) . "\n\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get available models configuration
     */
    public function getModels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'models' => [
                'ollama' => config('ai.ollama.available_models', []),
                'bedrock' => config('ai.bedrock.pricing', []),
            ],
            'defaults' => [
                'ollama' => config('ai.ollama.default_model'),
                'bedrock' => config('ai.bedrock.default_model'),
            ],
        ]);
    }

    /**
     * Get MCP server status with real-time monitoring
     */
    public function getServerStatus(): JsonResponse
    {
        try {
            $status = $this->realTimeMonitoringService->getRealTimeServerStatus();

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get server status', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'timestamp' => now()->toIso8601String(),
                    'overall_status' => 'unknown',
                    'servers' => [],
                    'alerts' => [],
                ],
            ]);
        }
    }

    /**
     * Get current agent workflow status with progress tracking
     */
    public function getWorkflowStatus(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $workflow = $this->realTimeMonitoringService->getAgentProgressTracking(
                is_int($userId) ? $userId : null
            );

            return response()->json([
                'success' => true,
                'data' => $workflow,
            ], 200, [], JSON_PRESERVE_ZERO_FRACTION);
        } catch (\Exception $e) {
            Log::error('Failed to get workflow status', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'workflow_id' => null,
                    'workflow_name' => null,
                    'status' => 'idle',
                    'progress_percentage' => 0.0,
                    'current_step' => null,
                    'total_steps' => 0,
                    'completed_steps' => 0,
                    'agents' => [],
                    'estimated_completion' => null,
                    'started_at' => null,
                ],
            ], 200, [], JSON_PRESERVE_ZERO_FRACTION);
        }
    }

    /**
     * Get active MCP tool usage and execution monitoring
     */
    public function getToolUsage(): JsonResponse
    {
        try {
            $toolData = $this->realTimeMonitoringService->getToolExecutionMonitoring();

            return response()->json([
                'success' => true,
                'data' => $toolData,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get tool usage', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'timestamp' => now()->toIso8601String(),
                    'active_tools' => [],
                    'recent_executions' => [],
                    'tool_statistics' => [],
                ],
            ]);
        }
    }

    /**
     * Get performance metrics comparing providers and agents
     */
    public function getPerformanceMetrics(): JsonResponse
    {
        try {
            $metrics = $this->realTimeMonitoringService->getPerformanceMetrics();

            return response()->json([
                'success' => true,
                'data' => $metrics,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get performance metrics', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'timestamp' => now()->toIso8601String(),
                    'providers' => [],
                    'agents' => [],
                    'comparison' => [
                        'fastest_provider' => 'N/A',
                        'most_reliable_provider' => 'N/A',
                        'most_cost_effective' => 'N/A',
                        'best_performing_agent' => 'N/A',
                    ],
                ],
            ]);
        }
    }

    /**
     * Handle server disconnection and recovery
     */
    public function handleServerDisconnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_name' => 'required|string|max:255',
            'error' => 'required|string|max:1000',
        ]);

        try {
            $result = $this->realTimeMonitoringService->handleServerDisconnection(
                $validated['server_name'],
                $validated['error']
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to handle server disconnection', [
                'error' => $e->getMessage(),
                'server_name' => $validated['server_name'],
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to handle server disconnection',
                'data' => [
                    'server' => $validated['server_name'],
                    'event' => 'disconnection',
                    'error' => $validated['error'],
                    'reconnection_attempted' => false,
                    'reconnection_successful' => false,
                    'reconnection_message' => 'Failed to process disconnection',
                    'timestamp' => now()->toIso8601String(),
                ],
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
            $cacheKey = 'ai_chat_preferences_' . Auth::id();
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
        $cacheKey = 'ai_chat_preferences_' . Auth::id();
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
     * Validate AI chat request payload.
     *
     * @return array<string, mixed>
     */
    private function validateChatRequest(Request $request): array
    {
        if ($request->isJson()) {
            $request->merge($request->json()->all());
        }

        return $request->validate([
            'message' => 'required|string|max:2000',
            'character_id' => 'nullable|integer|exists:ucp_characters,id',
            'career_id' => 'nullable|integer|exists:ucp_careers,id',
            'provider' => 'nullable|string|in:ollama,bedrock,agent',
            'model' => 'nullable|string|max:255',
            'agent_type' => 'nullable|string|in:training,career,race,skill',
            'conversation_id' => 'nullable|string|max:255',
        ]);
    }

    /**
     * Execute AI chat request and normalize response.
     *
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>  $context
     * @return array{content: string, model: string, provider: string, processing_time: float, tokens: int|null, cost: float, tools_used: array<int, string>, agent: string|null, confidence: float|null}
     */
    private function executeChatRequest(array $validated, array $context): array
    {
        $requestPayload = [
            'prompt' => $validated['message'],
            'context' => $context,
            'agent_type' => $validated['agent_type'] ?? null,
            'preferred_provider' => $validated['provider'] ?? null,
            'preferred_model' => $validated['model'] ?? null,
        ];

        /** @var array{response: mixed, model: string, provider: string, execution_time: float, cost: float} $execution */
        $execution = $this->routingService->executeWithFallback($requestPayload);
        $responseContent = $execution['response'];
        $encodedResponse = json_encode($responseContent);
        $encodedResponse = $encodedResponse === false ? '' : $encodedResponse;

        $content = match (true) {
            is_string($responseContent) => $responseContent,
            is_array($responseContent) => is_string($responseContent['content'] ?? null)
                ? $responseContent['content']
                : $encodedResponse,
            is_scalar($responseContent) => (string) $responseContent,
            default => $encodedResponse,
        };

        return [
            'content' => $content,
            'model' => $execution['model'],
            'provider' => $execution['provider'],
            'processing_time' => $execution['execution_time'],
            'tokens' => null,
            'cost' => $execution['cost'],
            'tools_used' => [],
            'agent' => is_array($responseContent) && isset($responseContent['agent']) && is_string($responseContent['agent']) ? $responseContent['agent'] : null,
            'confidence' => is_array($responseContent) && isset($responseContent['confidence']) && is_float($responseContent['confidence']) ? $responseContent['confidence'] : null,
            'rag_enhanced' => isset($context['rag_enhanced']) ? $context['rag_enhanced'] : false,
            'knowledge_sources' => isset($context['knowledge_base']) && is_string($context['knowledge_base']) ? $this->extractKnowledgeSources($context['knowledge_base']) : [],
        ];
    }

    /**
     * Extract knowledge sources from RAG context for attribution
     *
     * @return array<int, string>
     */
    private function extractKnowledgeSources(string $knowledgeBase): array
    {
        $sources = [];
        // Extract source filenames from knowledge base context
        if (preg_match_all('/\(([^)]+\.md)\)/', $knowledgeBase, $matches)) {
            $sources = array_unique($matches[1]);
        }

        return array_values($sources);
    }

    /**
     * Split response text into SSE-friendly chunks.
     *
     * @return array<int, string>
     */
    private function chunkResponse(string $content, int $chunkSize = 160): array
    {
        if ($content === '') {
            return [''];
        }

        $chunks = [];
        $length = mb_strlen($content);
        for ($offset = 0; $offset < $length; $offset += $chunkSize) {
            $chunks[] = mb_substr($content, $offset, $chunkSize);
        }

        return $chunks;
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
                'cost_estimate' => $response['cost'],
                'metadata' => json_encode([
                    'provider' => $response['provider'],
                    'agent' => $response['agent'] ?? null,
                    'confidence' => $response['confidence'] ?? null,
                    'tools_used' => $response['tools_used'],
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
