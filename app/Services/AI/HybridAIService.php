<?php

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Models\ConversationMessage;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Hybrid AI Service with MCP Integration
 *
 * Integrates local Ollama models with MCP Bedrock services for intelligent AI processing.
 * Implements intelligent routing between local and cloud AI based on complexity, cost, and availability.
 *
 * Requirements: 13.1, 56.2, 57.2
 */
class HybridAIService
{
    protected MCPClientService $mcpClient;

    protected OllamaService $ollamaService;

    protected BedrockService $bedrockService;

    protected AIPerformanceMonitor $performanceMonitor;

    protected VectorStoreService $vectorStore;

    protected bool $enabled;

    protected string $defaultModel;

    protected int $ollamaTimeout;

    protected int $bedrockTimeout;

    protected float $costThreshold;

    /** @var array<string, mixed> */
    protected array $modelPricing;

    public function __construct(
        MCPClientService $mcpClient,
        OllamaService $ollamaService,
        BedrockService $bedrockService,
        AIPerformanceMonitor $performanceMonitor,
        VectorStoreService $vectorStore
    ) {
        $this->mcpClient = $mcpClient;
        $this->ollamaService = $ollamaService;
        $this->bedrockService = $bedrockService;
        $this->performanceMonitor = $performanceMonitor;
        $this->vectorStore = $vectorStore;

        $enabledConfig = Config::get('ai.hybrid.enabled', true);
        $this->enabled = is_bool($enabledConfig) ? $enabledConfig : (bool) $enabledConfig;

        $modelConfig = Config::get('ai.ollama.default_model', 'llama3.3');
        $this->defaultModel = is_string($modelConfig) ? $modelConfig : 'llama3.3';

        $ollamaTimeoutConfig = Config::get('ai.ollama.timeout', 15);
        $this->ollamaTimeout = is_int($ollamaTimeoutConfig) ? $ollamaTimeoutConfig : (is_numeric($ollamaTimeoutConfig) ? (int) $ollamaTimeoutConfig : 15);

        $bedrockTimeoutConfig = Config::get('ai.bedrock.timeout', 30);
        $this->bedrockTimeout = is_int($bedrockTimeoutConfig) ? $bedrockTimeoutConfig : (is_numeric($bedrockTimeoutConfig) ? (int) $bedrockTimeoutConfig : 30);

        $costConfig = Config::get('ai.hybrid.cost_threshold', 0.01);
        $this->costThreshold = is_float($costConfig) || is_int($costConfig) ? (float) $costConfig : 0.01;

        $pricing = Config::get('ai.bedrock.pricing', []);
        $this->modelPricing = is_array($pricing) ? $pricing : [];
    }

    /**
     * Process AI request with intelligent routing
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     content: string,
     *     model: string,
     *     provider: string,
     *     processing_time: float,
     *     token_count: int,
     *     cost: float,
     *     confidence: float,
     *     metadata: array<string, mixed>
     * }
     */
    public function processRequest(string $prompt, array $context = [], ?int $characterId = null, ?string $conversationId = null): array
    {
        $startTime = microtime(true);

        // Enhance context with knowledge base retrieval (RAG)
        $enrichedContext = $this->enrichContextWithKnowledge($prompt, $context);

        // Analyze request complexity
        $complexity = $this->analyzeComplexity($prompt, $enrichedContext);

        // Determine optimal provider
        $provider = $this->selectProvider($complexity, $enrichedContext);

        // Process request with selected provider
        try {
            $response = match ($provider) {
                'ollama' => $this->processWithOllama($prompt, $enrichedContext, $complexity),
                'bedrock' => $this->processWithBedrock($prompt, $enrichedContext, $complexity),
                'mcp-strands' => $this->processWithMCPStrands($prompt, $enrichedContext, $complexity),
                'mcp-agentcore' => $this->processWithMCPAgentCore($prompt, $enrichedContext, $complexity),
                default => throw new \InvalidArgumentException("Unknown provider: {$provider}"),
            };

            // Add processing metadata
            $response['processing_time'] = microtime(true) - $startTime;
            $response['provider'] = $provider;

            // Store conversation if IDs provided
            if ($characterId && $conversationId) {
                $authId = Auth::id();
                $userId = is_int($authId) ? $authId : (is_numeric($authId) ? (int) $authId : null);
                $this->storeConversation($characterId, $conversationId, $prompt, $response, $userId);
            }

            // Track performance metrics
            $this->performanceMonitor->trackRequest($provider, $response);

            /** @var array{content: string, model: string, provider: string, processing_time: float, token_count: int, cost: float, confidence: float, metadata: array<string, mixed>} */
            return $response;
        } catch (\Exception $e) {
            Log::error('[HybridAI] Request processing failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'prompt_length' => \strlen($prompt),
            ]);

            // Attempt fallback
            $fallbackResponse = $this->handleFailureWithFallback($provider, $e, $prompt, $context);

            // Add processing metadata
            $fallbackResponse['processing_time'] = microtime(true) - $startTime;

            // Store conversation if IDs provided
            if ($characterId && $conversationId) {
                $authId = Auth::id();
                $userId = is_int($authId) ? $authId : (is_numeric($authId) ? (int) $authId : null);
                $this->storeConversation($characterId, $conversationId, $prompt, $fallbackResponse, $userId);
            }

            // Track performance metrics for fallback
            $fallbackProvider = is_string($fallbackResponse['provider']) ? $fallbackResponse['provider'] : 'unknown';
            $this->performanceMonitor->trackRequest($fallbackProvider, $fallbackResponse);

            /** @var array{content: string, model: string, provider: string, processing_time: float, token_count: int, cost: float, confidence: float, metadata: array<string, mixed>} */
            return $fallbackResponse;
        }
    }

    /**
     * Enrich context with relevant knowledge base information using RAG
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function enrichContextWithKnowledge(string $prompt, array $context = []): array
    {
        try {
            // Determine if RAG enhancement is needed
            if (! $this->shouldUseRAG($prompt)) {
                return $context;
            }

            // Retrieve relevant knowledge
            $knowledgeContext = $this->vectorStore->getRelevantContext($prompt, [
                'top_k' => 3,
                'category' => 'game-mechanics',
                'include_metadata' => true,
            ]);

            if (empty($knowledgeContext)) {
                return $context;
            }

            // Add knowledge to context
            $enrichedContext = $context;
            $enrichedContext['knowledge_base'] = $knowledgeContext;
            $enrichedContext['rag_enhanced'] = true;

            Log::info('[HybridAI] Context enriched with RAG knowledge', [
                'prompt_length' => strlen($prompt),
                'knowledge_length' => strlen($knowledgeContext),
            ]);

            return $enrichedContext;
        } catch (\Exception $e) {
            Log::warning('[HybridAI] Failed to enrich context with knowledge base', [
                'error' => $e->getMessage(),
            ]);

            return $context;
        }
    }

    /**
     * Determine if RAG should be used for this prompt
     */
    protected function shouldUseRAG(string $prompt): bool
    {
        $prompt = strtolower($prompt);

        // RAG keywords indicating game mechanics questions
        $ragKeywords = [
            'stat', 'speed', 'stamina', 'power', 'guts', 'wit',
            'training', 'skill', 'race', 'aptitude', 'support card',
            'how', 'why', 'what', 'when', 'which', 'should',
            'strategy', 'build', 'optimal', 'best', 'breakpoint',
            'sp', 'energy', 'mood', 'bond', 'hint',
        ];

        foreach ($ragKeywords as $keyword) {
            if (str_contains($prompt, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Analyze request complexity
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     level: string,
     *     token_estimate: int,
     *     requires_rag: bool,
     *     requires_multi_step: bool,
     *     estimated_cost: float
     * }
     */
    protected function analyzeComplexity(string $prompt, array $context = []): array
    {
        $tokenEstimate = $this->estimateTokenCount($prompt, $context);
        $requiresRAG = $this->requiresRAG($prompt, $context);
        $requiresMultiStep = $this->requiresMultiStepReasoning($prompt, $context);

        // Determine complexity level
        $level = 'simple';
        if ($requiresMultiStep) {
            $level = 'complex';
        } elseif ($tokenEstimate > 4000) {
            $level = 'complex';
        } elseif ($tokenEstimate > 1000 || $requiresRAG) {
            $level = 'medium';
        }

        // Estimate cost for Bedrock
        $estimatedCost = $this->estimateCost($tokenEstimate, 'claude-3-5-sonnet');

        return [
            'level' => $level,
            'token_estimate' => $tokenEstimate,
            'requires_rag' => $requiresRAG,
            'requires_multi_step' => $requiresMultiStep,
            'estimated_cost' => $estimatedCost,
        ];
    }

    /**
     * Select optimal AI provider based on complexity and availability
     *
     * @param  array<string, mixed>  $complexity
     * @param  array<string, mixed>  $context
     */
    protected function selectProvider(array $complexity, array $context = []): string
    {
        // Check if user has provider preference
        if (isset($context['preferred_provider']) && is_string($context['preferred_provider'])) {
            $preferred = $context['preferred_provider'];
            if ($this->isProviderAvailable($preferred)) {
                return $preferred;
            }
        }

        // For simple requests, prefer local Ollama
        if ($complexity['level'] === 'simple' && $this->ollamaService->isAvailable()) {
            return 'ollama';
        }

        // For complex requests requiring advanced reasoning
        if ($complexity['requires_multi_step'] || $complexity['requires_rag']) {
            // Check if MCP servers are available
            if ($this->mcpClient->isStrandsAgentsAvailable()) {
                return 'mcp-strands';
            }

            if ($this->mcpClient->isAgentCoreAvailable()) {
                return 'mcp-agentcore';
            }

            // Fallback to direct Bedrock
            if ($this->bedrockService->isAvailable()) {
                return 'bedrock';
            }
        }

        // For medium complexity, try Ollama first with fallback
        if ($complexity['level'] === 'medium' && $this->ollamaService->isAvailable()) {
            return 'ollama';
        }

        // Default to Bedrock if available
        if ($this->bedrockService->isAvailable()) {
            return 'bedrock';
        }

        // Last resort: try Ollama even if it might be slow
        if ($this->ollamaService->isAvailable()) {
            return 'ollama';
        }

        throw new \RuntimeException('No AI providers available');
    }

    /**
     * Process request with local Ollama
     *
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $complexity
     * @return array<string, mixed>
     */
    protected function processWithOllama(
        string $prompt,
        array $context,
        array $complexity
    ): array {
        $startTime = microtime(true);

        try {
            $response = $this->ollamaService->generate(
                $prompt,
                $context,
                $this->defaultModel,
                $this->ollamaTimeout
            );

            $processingTime = microtime(true) - $startTime;

            // Check if processing was too slow
            if ($processingTime > $this->ollamaTimeout) {
                Log::warning('[HybridAI] Ollama processing exceeded timeout', [
                    'processing_time' => $processingTime,
                    'timeout' => $this->ollamaTimeout,
                ]);

                // Trigger fallback to Bedrock
                throw new \RuntimeException('Ollama processing timeout');
            }

            $tokenCount = isset($response['token_count']) && is_int($response['token_count'])
                ? $response['token_count']
                : (isset($complexity['token_estimate']) && is_int($complexity['token_estimate']) ? $complexity['token_estimate'] : 0);

            return [
                'content' => $response['content'],
                'model' => $response['model'],
                'provider' => 'ollama',
                'token_count' => $tokenCount,
                'cost' => 0.0, // Local processing is free
                'confidence' => isset($response['confidence']) && (is_float($response['confidence']) || is_int($response['confidence'])) ? (float) $response['confidence'] : 0.8,
                'metadata' => [
                    'provider' => 'ollama',
                    'processing_time' => $processingTime,
                    'model_version' => isset($response['model_version']) && is_string($response['model_version']) ? $response['model_version'] : 'unknown',
                ],
            ];
        } catch (\Exception $e) {
            Log::error('[HybridAI] Ollama processing failed', [
                'error' => $e->getMessage(),
                'model' => $this->defaultModel,
            ]);

            throw $e;
        }
    }

    /**
     * Process request with AWS Bedrock
     *
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $complexity
     * @return array<string, mixed>
     */
    protected function processWithBedrock(
        string $prompt,
        array $context,
        array $complexity
    ): array {
        $startTime = microtime(true);

        // Select appropriate Bedrock model based on complexity
        $model = $this->selectBedrockModel($complexity);

        try {
            $response = $this->bedrockService->generate(
                $prompt,
                $context,
                $model
            );

            $processingTime = microtime(true) - $startTime;
            $responseTokenCount = isset($response['token_count']) && is_int($response['token_count']) ? $response['token_count'] : 0;
            $cost = $this->calculateCost($responseTokenCount, $model);

            return [
                'content' => $response['content'],
                'model' => $model,
                'provider' => 'bedrock',
                'token_count' => $responseTokenCount,
                'cost' => $cost,
                'confidence' => isset($response['confidence']) && (is_float($response['confidence']) || is_int($response['confidence'])) ? (float) $response['confidence'] : 0.9,
                'metadata' => [
                    'provider' => 'bedrock',
                    'processing_time' => $processingTime,
                    'model_version' => isset($response['model_version']) && is_string($response['model_version']) ? $response['model_version'] : 'unknown',
                    'request_id' => isset($response['request_id']) ? $response['request_id'] : null,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('[HybridAI] Bedrock processing failed', [
                'error' => $e->getMessage(),
                'model' => $model,
            ]);

            throw $e;
        }
    }

    /**
     * Process request with MCP Strands Agents
     *
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $complexity
     * @return array<string, mixed>
     */
    protected function processWithMCPStrands(
        string $prompt,
        array $context,
        array $complexity
    ): array {
        $startTime = microtime(true);

        try {
            // Create agent via MCP strands-agents server
            $agent = $this->createStrandsAgent($context);

            // Process request through agent
            $response = $agent->process($prompt, $context);

            $processingTime = microtime(true) - $startTime;

            $tokenCount = isset($response['token_count']) && is_int($response['token_count'])
                ? $response['token_count']
                : (isset($complexity['token_estimate']) && is_int($complexity['token_estimate']) ? $complexity['token_estimate'] : 0);

            $model = isset($response['model']) && is_string($response['model']) ? $response['model'] : 'claude-3-5-sonnet';
            $cost = $this->calculateCost($tokenCount, $model);

            return [
                'content' => isset($response['content']) && is_string($response['content']) ? $response['content'] : '',
                'model' => $model,
                'provider' => 'mcp-strands',
                'token_count' => $tokenCount,
                'cost' => $cost,
                'confidence' => isset($response['confidence']) && (is_float($response['confidence']) || is_int($response['confidence'])) ? (float) $response['confidence'] : 0.95,
                'metadata' => [
                    'provider' => 'mcp-strands',
                    'processing_time' => $processingTime,
                    'agent_id' => isset($response['agent_id']) ? $response['agent_id'] : null,
                    'workflow_steps' => isset($response['workflow_steps']) && is_array($response['workflow_steps']) ? $response['workflow_steps'] : [],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('[HybridAI] MCP Strands processing failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Process request with MCP AgentCore
     *
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $complexity
     * @return array<string, mixed>
     */
    protected function processWithMCPAgentCore(
        string $prompt,
        array $context,
        array $complexity
    ): array {
        $startTime = microtime(true);

        try {
            // Create agent via MCP agentcore-mcp-server
            $agent = $this->createAgentCoreAgent($context);

            // Process request through agent
            $response = $agent->process($prompt, $context);

            $processingTime = microtime(true) - $startTime;

            $tokenCount = isset($response['token_count']) && is_int($response['token_count'])
                ? $response['token_count']
                : (isset($complexity['token_estimate']) && is_int($complexity['token_estimate']) ? $complexity['token_estimate'] : 0);

            $model = isset($response['model']) && is_string($response['model']) ? $response['model'] : 'claude-3-5-sonnet';
            $cost = $this->calculateCost($tokenCount, $model);

            return [
                'content' => isset($response['content']) && is_string($response['content']) ? $response['content'] : '',
                'model' => $model,
                'provider' => 'mcp-agentcore',
                'token_count' => $tokenCount,
                'cost' => $cost,
                'confidence' => isset($response['confidence']) && (is_float($response['confidence']) || is_int($response['confidence'])) ? (float) $response['confidence'] : 0.95,
                'metadata' => [
                    'provider' => 'mcp-agentcore',
                    'processing_time' => $processingTime,
                    'agent_id' => isset($response['agent_id']) ? $response['agent_id'] : null,
                    'workflow_steps' => isset($response['workflow_steps']) && is_array($response['workflow_steps']) ? $response['workflow_steps'] : [],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('[HybridAI] MCP AgentCore processing failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle failure with intelligent fallback
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function handleFailureWithFallback(
        string $failedProvider,
        \Exception $exception,
        string $prompt,
        array $context
    ): array {
        Log::warning('[HybridAI] Attempting fallback after failure', [
            'failed_provider' => $failedProvider,
            'error' => $exception->getMessage(),
        ]);

        // Determine fallback provider
        $fallbackProvider = match ($failedProvider) {
            'ollama' => $this->bedrockService->isAvailable() ? 'bedrock' : null,
            'mcp-strands' => $this->bedrockService->isAvailable() ? 'bedrock' : 'ollama',
            'mcp-agentcore' => $this->bedrockService->isAvailable() ? 'bedrock' : 'ollama',
            default => $this->ollamaService->isAvailable() ? 'ollama' : null,
        };

        if (! $fallbackProvider) {
            throw new \RuntimeException('No fallback provider available', 0, $exception);
        }

        // Retry with fallback provider
        $complexity = $this->analyzeComplexity($prompt, $context);

        $response = match ($fallbackProvider) {
            'ollama' => $this->processWithOllama($prompt, $context, $complexity),
            default => $this->processWithBedrock($prompt, $context, $complexity),
        };

        // Add provider to response
        $response['provider'] = $fallbackProvider;

        return $response;
    }

    /**
     * Create Strands agent via MCP
     *
     * Creates an agent using the MCP strands-agents server for advanced
     * multi-step reasoning and tool orchestration.
     *
     * @param  array<string, mixed>  $context
     */
    protected function createStrandsAgent(array $context): StrandsAgentWrapper
    {
        // Check if MCP Strands server is available
        if (! $this->mcpClient->isStrandsAgentsAvailable()) {
            throw new \RuntimeException('MCP Strands agents server is not available');
        }

        return new StrandsAgentWrapper($this->mcpClient, $context);
    }

    /**
     * Create AgentCore agent via MCP
     *
     * Creates an agent using the MCP agentcore-mcp-server for AWS Bedrock
     * AgentCore integration with advanced orchestration capabilities.
     *
     * @param  array<string, mixed>  $context
     */
    protected function createAgentCoreAgent(array $context): AgentCoreWrapper
    {
        // Check if MCP AgentCore server is available
        if (! $this->mcpClient->isAgentCoreAvailable()) {
            throw new \RuntimeException('MCP AgentCore server is not available');
        }

        return new AgentCoreWrapper($this->mcpClient, $context);
    }

    /**
     * Select appropriate Bedrock model based on complexity
     *
     * @param  array<string, mixed>  $complexity
     */
    protected function selectBedrockModel(array $complexity): string
    {
        if ($complexity['requires_multi_step'] || $complexity['level'] === 'complex') {
            return 'claude-3-5-sonnet'; // Best balance of intelligence and cost
        }

        if ($complexity['level'] === 'medium') {
            return 'claude-3-5-haiku'; // Fast and affordable
        }

        return 'nova-2-lite'; // Ultra-budget for simple requests
    }

    /**
     * Estimate token count for prompt and context
     *
     * @param  array<string, mixed>  $context
     */
    protected function estimateTokenCount(string $prompt, array $context = []): int
    {
        // Rough estimation: 1 token ≈ 4 characters
        $promptTokens = (int) (\strlen($prompt) / 4);
        $contextTokens = (int) (\strlen(json_encode($context) ?: '{}') / 4);

        return $promptTokens + $contextTokens;
    }

    /**
     * Check if request requires RAG
     *
     * @param  array<string, mixed>  $context
     */
    protected function requiresRAG(string $prompt, array $context = []): bool
    {
        // Check for keywords indicating need for external knowledge
        $ragKeywords = ['search', 'find', 'lookup', 'database', 'external', 'api'];

        $promptLower = strtolower($prompt);
        foreach ($ragKeywords as $keyword) {
            if (str_contains($promptLower, $keyword)) {
                return true;
            }
        }

        return isset($context['requires_rag']) && $context['requires_rag'] === true;
    }

    /**
     * Check if request requires multi-step reasoning
     *
     * @param  array<string, mixed>  $context
     */
    protected function requiresMultiStepReasoning(string $prompt, array $context = []): bool
    {
        // Check for keywords indicating complex reasoning
        $reasoningKeywords = ['analyze', 'compare', 'evaluate', 'optimize', 'strategy', 'plan'];

        $promptLower = strtolower($prompt);
        foreach ($reasoningKeywords as $keyword) {
            if (str_contains($promptLower, $keyword)) {
                return true;
            }
        }

        return isset($context['requires_multi_step']) && $context['requires_multi_step'] === true;
    }

    /**
     * Estimate cost for Bedrock request
     */
    protected function estimateCost(int $tokenCount, string $model): float
    {
        if (! isset($this->modelPricing[$model])) {
            return 0.0;
        }

        $pricing = $this->modelPricing[$model];
        if (! is_array($pricing)) {
            return 0.0;
        }

        $inputCost = isset($pricing['input']) && (is_float($pricing['input']) || is_int($pricing['input']))
            ? (float) $pricing['input'] * ($tokenCount / 1000000)
            : 0.0;
        $outputCost = isset($pricing['output']) && (is_float($pricing['output']) || is_int($pricing['output']))
            ? (float) $pricing['output'] * ($tokenCount / 1000000)
            : 0.0;

        return $inputCost + $outputCost;
    }

    /**
     * Calculate actual cost for Bedrock request
     */
    protected function calculateCost(int $tokenCount, string $model): float
    {
        return $this->estimateCost($tokenCount, $model);
    }

    /**
     * Check if provider is available
     */
    protected function isProviderAvailable(string $provider): bool
    {
        return match ($provider) {
            'ollama' => $this->ollamaService->isAvailable(),
            'bedrock' => $this->bedrockService->isAvailable(),
            'mcp-strands' => $this->mcpClient->isStrandsAgentsAvailable(),
            'mcp-agentcore' => $this->mcpClient->isAgentCoreAvailable(),
            default => false,
        };
    }

    /**
     * Store conversation in database
     *
     * @param  array<string, mixed>  $response
     */
    protected function storeConversation(
        int $characterId,
        string $conversationId,
        string $prompt,
        array $response,
        ?int $userId = null
    ): void {
        try {
            // First, ensure the conversation exists
            $conversation = AIConversation::firstOrCreate(
                ['conversation_id' => $conversationId],
                [
                    'user_id' => $userId ?? Auth::id(),
                    'character_id' => $characterId,
                    'conversation_type' => 'ai_advisory',
                    'status' => 'active',
                    'message_count' => 0,
                ]
            );

            // Store user message
            ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'message_type' => 'user',
                'message_content' => $prompt,
                'status' => 'sent',
                'is_visible' => true,
            ]);

            // Store AI response
            ConversationMessage::create([
                'conversation_id' => $conversation->id,
                'message_type' => 'assistant',
                'message_content' => is_string($response['content']) ? $response['content'] : '',
                'ai_model_used' => is_string($response['model']) ? $response['model'] : null,
                'processing_time' => isset($response['processing_time']) && (is_float($response['processing_time']) || is_int($response['processing_time'])) ? (float) $response['processing_time'] : null,
                'tokens_used' => isset($response['token_count']) && is_int($response['token_count']) ? $response['token_count'] : null,
                'cost_estimate' => isset($response['cost']) && (is_float($response['cost']) || is_int($response['cost'])) ? (float) $response['cost'] : null,
                'status' => 'sent',
                'is_visible' => true,
            ]);

            // Update conversation message count
            $conversation->increment('message_count', 2);
            $conversation->last_activity_at = now();
            $conversation->save();
        } catch (\Exception $e) {
            Log::error('[HybridAI] Failed to store conversation', [
                'error' => $e->getMessage(),
                'character_id' => $characterId,
                'conversation_id' => $conversationId,
            ]);
        }
    }

    /**
     * Get conversation history
     *
     * @return array<int, array{id: int|null, conversation_id: int|null, message_type: string|null, content: string|null, ai_model_used: string|null, processing_time: float|null, token_count: int|null, cost: float|null, created_at: string|null}>
     */
    public function getConversationHistory(
        int $characterId,
        ?string $conversationId = null,
        int $limit = 50
    ): array {
        // First find the conversation(s) for this character
        $conversationQuery = AIConversation::query()->where('character_id', '=', $characterId, 'and');

        if ($conversationId) {
            $conversationQuery = $conversationQuery->where('conversation_id', '=', $conversationId, 'and');
        }

        $conversationIds = $conversationQuery->pluck('id', null)->toArray();

        if (empty($conversationIds)) {
            return [];
        }

        // Get messages from those conversations
        $messages = ConversationMessage::query()
            ->whereIn('conversation_id', $conversationIds, 'and', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['*']);

        $result = [];
        foreach ($messages as $msg) {
            $result[] = [
                'id' => $msg->id,
                'conversation_id' => $msg->conversation_id,
                'message_type' => $msg->message_type,
                'content' => $msg->message_content,
                'ai_model_used' => $msg->ai_model_used,
                'processing_time' => $msg->processing_time,
                'token_count' => $msg->tokens_used,
                'cost' => $msg->cost_estimate !== null ? (float) $msg->cost_estimate : null,
                'created_at' => $msg->created_at?->toIso8601String(),
            ];
        }

        return $result;
    }

    /**
     * Get AI service status
     *
     * @return array{
     *     enabled: bool,
     *     providers: array<string, array{available: bool, healthy: bool}>,
     *     default_model: string,
     *     performance: array<string, mixed>
     * }
     */
    public function getStatus(): array
    {
        return [
            'enabled' => $this->enabled,
            'providers' => [
                'ollama' => [
                    'available' => $this->ollamaService->isAvailable(),
                    'healthy' => $this->ollamaService->isHealthy(),
                ],
                'bedrock' => [
                    'available' => $this->bedrockService->isAvailable(),
                    'healthy' => $this->bedrockService->isHealthy(),
                ],
                'mcp-strands' => [
                    'available' => $this->mcpClient->isStrandsAgentsAvailable(),
                    'healthy' => $this->mcpClient->isServerHealthy('strands-agents'),
                ],
                'mcp-agentcore' => [
                    'available' => $this->mcpClient->isAgentCoreAvailable(),
                    'healthy' => $this->mcpClient->isServerHealthy('agentcore-mcp-server'),
                ],
            ],
            'default_model' => $this->defaultModel,
            'performance' => $this->performanceMonitor->getMetrics(),
        ];
    }
}

/**
 * Wrapper class for Strands Agent MCP integration
 */
class StrandsAgentWrapper
{
    private MCPClientService $mcpClient;

    /** @var array<string, mixed> */
    private array $context;

    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(MCPClientService $mcpClient, array $context)
    {
        $this->mcpClient = $mcpClient;
        $this->context = $context;
    }

    /**
     * Process request through Strands agent
     *
     * @param  array<string, mixed>  $requestContext
     * @return array<string, mixed>
     */
    public function process(string $prompt, array $requestContext = []): array
    {
        // Use MCP client to invoke strands-agents tools
        $result = $this->mcpClient->callTool('strands-agents', 'create_agent', [
            'prompt' => $prompt,
            'context' => [...$this->context, ...$requestContext],
            'model' => 'claude-3-5-sonnet',
        ]);

        $workflowStepsRaw = $result['workflow_steps'] ?? null;
        /** @var array<mixed> $workflowSteps */
        $workflowSteps = is_array($workflowStepsRaw) ? $workflowStepsRaw : [];

        return [
            'content' => isset($result['response']) && is_string($result['response']) ? $result['response'] : '',
            'model' => isset($result['model']) && is_string($result['model']) ? $result['model'] : 'claude-3-5-sonnet',
            'token_count' => isset($result['token_count']) && is_int($result['token_count']) ? $result['token_count'] : 0,
            'confidence' => isset($result['confidence']) && (is_float($result['confidence']) || is_int($result['confidence'])) ? (float) $result['confidence'] : 0.95,
            'agent_id' => $result['agent_id'] ?? null,
            'workflow_steps' => $workflowSteps,
        ];
    }
}

/**
 * Wrapper class for AgentCore MCP integration
 */
class AgentCoreWrapper
{
    private MCPClientService $mcpClient;

    /** @var array<string, mixed> */
    private array $context;

    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(MCPClientService $mcpClient, array $context)
    {
        $this->mcpClient = $mcpClient;
        $this->context = $context;
    }

    /**
     * Process request through AgentCore agent
     *
     * @param  array<string, mixed>  $requestContext
     * @return array<string, mixed>
     */
    public function process(string $prompt, array $requestContext = []): array
    {
        // Use MCP client to invoke agentcore-mcp-server tools
        $result = $this->mcpClient->callTool('agentcore-mcp-server', 'invoke_agent', [
            'prompt' => $prompt,
            'context' => [...$this->context, ...$requestContext],
            'model' => 'claude-3-5-sonnet',
        ]);

        $workflowStepsRaw = $result['workflow_steps'] ?? null;
        /** @var array<mixed> $workflowSteps */
        $workflowSteps = is_array($workflowStepsRaw) ? $workflowStepsRaw : [];

        return [
            'content' => isset($result['response']) && is_string($result['response']) ? $result['response'] : '',
            'model' => isset($result['model']) && is_string($result['model']) ? $result['model'] : 'claude-3-5-sonnet',
            'token_count' => isset($result['token_count']) && is_int($result['token_count']) ? $result['token_count'] : 0,
            'confidence' => isset($result['confidence']) && (is_float($result['confidence']) || is_int($result['confidence'])) ? (float) $result['confidence'] : 0.95,
            'agent_id' => $result['agent_id'] ?? null,
            'workflow_steps' => $workflowSteps,
        ];
    }
}
