<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\StorageMode;
use App\Models\Character;
use App\Models\ConversationMessage;
use App\Services\AI\ConversationHistoryService;
use App\Services\MCP\AgentRoutingService;
use App\Services\MCP\RealTimeMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AIChatController extends Controller
{
    public function __construct(
        private readonly AgentRoutingService $routingService,
        private readonly RealTimeMonitoringService $realTimeMonitoringService,
        private readonly ConversationHistoryService $conversationHistoryService,
    ) {}

    /**
     * Display the AI chat interface
     */
    public function index(Request $request): View
    {
        $character = null;
        $userId = Auth::id();

        // Load character context if provided
        if ($request->has('character_id')) {
            $character = Character::with(['currentCareer', 'aptitudes', 'skills'])
                ->where('user_id', $userId)
                ->findOrFail($request->integer('character_id'));
        } elseif ($userId) {
            // Auto-load the most recently active character
            $character = Character::with(['currentCareer', 'aptitudes'])
                ->where('user_id', $userId)
                ->orderByDesc('updated_at')
                ->first();
        }

        // Determine storage mode
        $storageMode = StorageMode::fromRequest($request);

        // Load recent conversation history (last 10 unique conversations)
        $recentConversations = [];
        if ($userId) {
            try {
                $historyData = $this->conversationHistoryService->getConversations([
                    'limit' => 10,
                    'offset' => 0,
                ]);
                // Group by conversation_id and get the latest message per conversation
                $grouped = collect($historyData['conversations'] ?? [])
                    ->groupBy('conversation_id')
                    ->map(fn ($msgs) => $msgs->first())
                    ->values()
                    ->take(10)
                    ->toArray();
                $recentConversations = $grouped;
            } catch (\Exception $e) {
                Log::warning('Failed to load conversation history for AI chat', ['error' => $e->getMessage()]);
            }
        }

        // Get AI provider status
        $aiStatus = Cache::remember('ai:provider:status', 30, function () {
            $ollamaEnabled = (bool) config('ai.providers.ollama.enabled', true);
            $bedrockEnabled = (bool) config('ai.providers.bedrock.enabled', false);
            $defaultProvider = (string) config('ai.default_provider', 'ollama');

            return [
                'primary' => $defaultProvider,
                'available' => true,
                'ollama_available' => $ollamaEnabled,
                'bedrock_available' => $bedrockEnabled,
                'ollama_model' => (string) config('ai.providers.ollama.model', 'llama3.2'),
                'bedrock_model' => (string) config('ai.providers.bedrock.model', 'claude-3-5-sonnet'),
            ];
        });

        return view('ai.chat', [
            'character' => $character,
            'storageMode' => $storageMode,
            'recentConversations' => $recentConversations,
            'aiStatus' => $aiStatus,
        ]);
    }

    /**
     * Send a message to the AI and get a response
     */
    public function sendMessage(Request $request): JsonResponse
    {
        if (! app()->runningUnitTests()) {
            set_time_limit(120);
        }

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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('AI chat message failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'message' => $validated['message'],
            ]);

            return response()->json([
                'success' => false,
                'error' => $this->normalizeErrorMessage($e),
            ], 500);
        }
    }

    /**
     * Stream a message response using Server-Sent Events (SSE).
     *
     * NOTE: Current implementation is "fake streaming" - it processes the full
     * AI response first, then chunks it for display. True incremental streaming
     * would require streaming support from the underlying AI provider (Ollama
     * supports this, Bedrock's InvokeModel does not by default).
     *
     * Future enhancement: Use Bedrock's InvokeModelWithResponseStream API or
     * Ollama's streaming endpoint for true token-by-token streaming.
     */
    public function sendMessageStreaming(Request $request): StreamedResponse
    {
        if (! app()->runningUnitTests()) {
            set_time_limit(120);
        }

        $validated = $this->validateChatRequest($request);

        $userId = Auth::id();
        if (! is_int($userId)) {
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
                    echo 'data: '.json_encode([
                        'chunk' => $chunk,
                    ])."\n\n";
                    flush();
                }

                echo 'data: '.json_encode([
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
                        'rag_enhanced' => $response['rag_enhanced'] ?? false,
                        'knowledge_sources' => $response['knowledge_sources'] ?? [],
                    ],
                    'conversation_id' => $conversationId,
                ])."\n\n";
                flush();
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                echo 'data: '.json_encode([
                    'error' => 'Character not found.',
                ])."\n\n";
                flush();
            } catch (\Exception $e) {
                Log::error('AI chat streaming failed', [
                    'error' => $e->getMessage(),
                    'user_id' => $userId,
                    'message' => $validated['message'] ?? null,
                ]);

                echo 'data: '.json_encode([
                    'error' => $this->normalizeErrorMessage($e),
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
            $status['servers'] = array_merge(
                $status['servers'] ?? [],
                $this->buildProviderStatusEntries()
            );

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
                    'servers' => $this->buildProviderStatusEntries(),
                    'alerts' => [],
                ],
            ]);
        }
    }

    /**
     * Build provider health entries for ollama and bedrock.
     *
     * @return array<string, array{name: string, status: string, is_connected: bool}>
     */
    private function buildProviderStatusEntries(): array
    {
        $cacheKey = 'ollama_availability';
        if (Cache::has($cacheKey)) {
            $ollamaOk = (bool) Cache::get($cacheKey);
        } else {
            try {
                $host = config('ai.ollama.host', 'http://localhost:11434');
                $host = is_string($host) ? $host : 'http://localhost:11434';
                $ollamaOk = Http::timeout(2)->get("{$host}/api/tags")->successful();
            } catch (\Exception) {
                $ollamaOk = false;
            }
            Cache::put($cacheKey, $ollamaOk, 60);
        }

        $bedrockEnabled = (bool) config('ai.bedrock.enabled', false);

        return [
            'ollama' => [
                'name' => 'ollama',
                'status' => $ollamaOk ? 'healthy' : 'offline',
                'is_connected' => $ollamaOk,
            ],
            'bedrock' => [
                'name' => 'bedrock',
                'status' => $bedrockEnabled ? 'healthy' : 'disabled',
                'is_connected' => $bedrockEnabled,
            ],
        ];
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
            'model' => config('ai.ollama.default_model', 'llama3'),
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
                ->where('user_id', Auth::id())
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

        if (is_array($responseContent) && isset($responseContent['error']) && is_string($responseContent['error'])) {
            throw new \Exception($responseContent['error']);
        }

        if (is_string($responseContent)) {
            $decodedResponse = json_decode($responseContent, true);
            if (is_array($decodedResponse) && isset($decodedResponse['error']) && is_string($decodedResponse['error'])) {
                throw new \Exception($decodedResponse['error']);
            }
        }

        $knowledgeBaseText = null;
        if (isset($context['knowledge_base']) && is_string($context['knowledge_base'])) {
            $knowledgeBaseText = $context['knowledge_base'];
        } elseif (is_array($responseContent) && isset($responseContent['knowledge_base']) && is_string($responseContent['knowledge_base'])) {
            $knowledgeBaseText = $responseContent['knowledge_base'];
        } elseif (isset($execution['knowledge_base']) && is_string($execution['knowledge_base'])) {
            $knowledgeBaseText = $execution['knowledge_base'];
        }

        $knowledgeSources = [];
        if (is_array($responseContent) && isset($responseContent['knowledge_sources']) && is_array($responseContent['knowledge_sources'])) {
            $knowledgeSources = array_values(array_filter($responseContent['knowledge_sources'], fn ($source) => is_string($source) && $source !== ''));
        } elseif (isset($execution['knowledge_sources']) && is_array($execution['knowledge_sources'])) {
            $knowledgeSources = array_values(array_filter($execution['knowledge_sources'], fn ($source) => is_string($source) && $source !== ''));
        } elseif (is_string($knowledgeBaseText)) {
            $knowledgeSources = $this->extractKnowledgeSources($knowledgeBaseText);
        }

        $ragEnhanced = (bool) (
            (isset($context['rag_enhanced']) ? $context['rag_enhanced'] : null)
            ?? (is_array($responseContent) && isset($responseContent['rag_enhanced']) ? $responseContent['rag_enhanced'] : null)
            ?? (isset($execution['rag_enhanced']) ? $execution['rag_enhanced'] : null)
            ?? (! empty($knowledgeSources))
        );

        $content = match (true) {
            is_string($responseContent) => $responseContent,
            is_array($responseContent) => is_string($responseContent['content'] ?? null)
                ? $responseContent['content']
                : throw new \Exception('The AI agent could not generate a text response for this request. Please try rephrasing your message.'),
            is_scalar($responseContent) => (string) $responseContent,
            default => throw new \Exception('The AI agent returned an invalid data type.'),
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
            'rag_enhanced' => $ragEnhanced,
            'knowledge_sources' => $knowledgeSources,
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
    /**
     * Rate an AI message (thumbs up/down feedback)
     */
    public function rateMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message_id' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'in:up,down,1,-1,positive,negative'],
        ]);

        $userId = Auth::id();
        if (! is_int($userId)) {
            return response()->json(['success' => false, 'error' => 'Authentication required.'], 401);
        }

        $isPositive = in_array($validated['rating'], ['up', '1', 'positive']);

        $message = ConversationMessage::find((int) $validated['message_id'], ['*']);
        if ($message instanceof ConversationMessage) {
            $message->quality_rating = $isPositive ? 1 : -1;
            $message->is_helpful = $isPositive;
            $message->save();
        }

        Log::info('[AI] Message rated', [
            'user_id' => $userId,
            'message_id' => $validated['message_id'],
            'rating' => $validated['rating'],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Map backend or provider exceptions to safe, actionable user messages
     */
    private function normalizeErrorMessage(\Exception $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'rate limit') || str_contains($message, 'throttl')) {
            return 'The AI provider is currently receiving too many requests. Please wait a moment and try again.';
        }
        if (str_contains($message, 'connection refused') || str_contains($message, 'timeout') || str_contains($message, 'failed to connect')) {
            return "We're having trouble connecting to the AI service. Please check your connection or try switching to a different provider in settings.";
        }
        if (
            str_contains($message, 'memory')
            || str_contains($message, 'context length')
            || str_contains($message, 'token limit')
            || str_contains($message, 'requires more system memory')
            || str_contains($message, 'out of memory')
        ) {
            return 'The selected model needs more memory than is currently available. Try a smaller model or switch provider, then retry.';
        }
        if (str_contains($message, 'unauthorized') || str_contains($message, 'credentials') || str_contains($message, 'signature')) {
            return 'There is an authentication issue with the AI provider. Let the system administrator know or verify your API keys if explicitly configured.';
        }
        if (str_contains($message, 'structured data without a text response') || str_contains($message, 'invalid data type')) {
            return 'The AI agent returned structured data without a text response. Please refine your query.';
        }

        // Fallback for general errors to hide raw backend exception
        return 'The AI encountered an unexpected issue while processing your message. Trying a different model or provider might help.';
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function logConversation(
        int $userId,
        ?int $characterId,
        ?string $conversationId,
        string $message,
        array $response = []
    ): void {
        try {
            $convId = $conversationId ?? Str::uuid()->toString();

            $conversation = \App\Models\AIConversation::firstOrCreate(
                ['conversation_id' => $convId],
                [
                    'user_id' => $userId,
                    'conversation_type' => 'general_help',
                    'status' => 'active',
                    'message_count' => 0,
                    'started_at' => now(),
                    'last_activity_at' => now(),
                    'ai_model' => $response['model'] ?? 'unknown',
                    'ai_version' => '1.0',
                ]
            );

            ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'message_type' => 'user',
                'message_content' => $message,
                'status' => 'completed',
                'sent_at' => now(),
            ]);

            ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'message_type' => 'ai',
                'message_content' => $response['content'] ?? '',
                'ai_model_used' => $response['model'] ?? null,
                'processing_time' => $response['processing_time'] ?? null,
                'tokens_used' => $response['tokens'] ?? null,
                'cost_estimate' => $response['cost'] ?? null,
                'message_metadata' => [
                    'provider' => $response['provider'] ?? null,
                    'agent' => $response['agent'] ?? null,
                    'confidence' => $response['confidence'] ?? null,
                    'tools_used' => $response['tools_used'] ?? [],
                    'rag_enhanced' => $response['rag_enhanced'] ?? false,
                    'knowledge_sources' => $response['knowledge_sources'] ?? [],
                ],
                'status' => 'completed',
                'sent_at' => now(),
            ]);

            $conversation->increment('message_count', 2);
            $conversation->update(['last_activity_at' => now()]);
        } catch (\Exception $e) {
            Log::error('Failed to log conversation', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
        }
    }
}
