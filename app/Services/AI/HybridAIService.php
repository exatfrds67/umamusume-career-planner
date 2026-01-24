<?php

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Services\MCP\MCPClientService;
use Cloudstudio\Ollama\Facades\Ollama;
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
        AIPerformanceMonitor $performanceMonitor
    ) {
        $this->mcpClient = $mcpClient;
        $this->ollamaService = $ollamaService;
        $this->bedrockService = $bedrockService;
        $this->performanceMonitor = $performanceMonitor;

        $enabledValue = Config::get('ai.hybrid.enabled', true);
        $this->enabled = is_bool($enabledValue) ? $enabledValue : (bool) $enabledValue;
        $defaultModelValue = Config::get('ai.ollama.default_model', 'llama3.3');
        $this->defaultModel = is_string($defaultModelValue) ? $defaultModelValue : 'llama3.3';
        $ollamaTimeoutValue = Config::get('ai.ollama.timeout', 15);
        $this->ollamaTimeout = is_int($ollamaTimeoutValue) ? $ollamaTimeoutValue : 15;
        $bedrockTimeoutValue = Config::get('ai.bedrock.timeout', 30);
        $this->bedrockTimeout = is_int($bedrockTimeoutValue) ? $bedrockTimeoutValue : 30;
        $costThresholdValue = Config::get('ai.hybrid.cost_threshold', 0.01);
        $this->costThreshold = is_float($costThresholdValue) || is_int($costThresholdValue) ? (float) $costThresholdValue : 0.01;

        $pricing = Config::get('ai.bedrock.pricing', []);
        $this->modelPricing = \is_array($pricing) ? $pricing : [];
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

        // Analyze request complexity
        $complexity = $this->analyzeComplexity($prompt, $context);

        // Determine optimal provider
        $provider = $this->selectProvider($complexity, $context);

        // Process request with selected provider
        try {
            $response = match ($provider) {
                'ollama' => $this->processWithOllama($prompt, $context, $complexity),
                'bedrock' => $this->processWithBedrock($prompt, $context, $complexity),
                'mcp-strands' => $this->processWithMCPStrands($prompt, $context, $complexity),
                'mcp-agentcore' => $this->processWithMCPAgentCore($prompt, $context, $complexity),
                default => throw new \InvalidArgumentException("Unknown provider: {$provider}"),
            };

            // Add processing metadata
            $processingTime = microtime(true) - $startTime;

            // Store conversation if IDs provided
            if ($characterId && $conversationId) {
                $responseWithMeta = $response;
                $responseWithMeta['processing_time'] = $processingTime;
                $responseWithMeta['provider'] = $provider;
                $this->storeConversation($characterId, $conversationId, $prompt, $responseWithMeta);
            }

            // Track performance metrics
            $responseForTracking = $response;
            $responseForTracking['processing_time'] = $processingTime;
            $responseForTracking['provider'] = $provider;
            $this->performanceMonitor->trackRequest($provider, $responseForTracking);

            /** @var array{content: string, model: string, provider: string, processing_time: float, token_count: int, cost: float, confidence: float, metadata: array<string, mixed>} */
            return [
                'content' => isset($response['content']) && is_string($response['content']) ? $response['content'] : '',
                'model' => isset($response['model']) && is_string($response['model']) ? $response['model'] : '',
                'provider' => $provider,
                'processing_time' => $processingTime,
                'token_count' => isset($response['token_count']) && is_int($response['token_count']) ? $response['token_count'] : 0,
                'cost' => isset($response['cost']) && (is_float($response['cost']) || is_int($response['cost'])) ? (float) $response['cost'] : 0.0,
                'confidence' => isset($response['confidence']) && (is_float($response['confidence']) || is_int($response['confidence'])) ? (float) $response['confidence'] : 0.0,
                'metadata' => isset($response['metadata']) && is_array($response['metadata']) ? $response['metadata'] : [],
            ];
        } catch (\Exception $e) {
            Log::error('[HybridAI] Request processing failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'prompt_length' => \strlen($prompt),
            ]);

            // Attempt fallback
            $fallbackResponse = $this->handleFailureWithFallback($provider, $e, $prompt, $context);

            // Add processing metadata
            $fallbackProcessingTime = microtime(true) - $startTime;
            $fallbackProvider = isset($fallbackResponse['provider']) && is_string($fallbackResponse['provider']) ? $fallbackResponse['provider'] : $provider;

            // Store conversation if IDs provided
            if ($characterId && $conversationId) {
                $fallbackWithMeta = $fallbackResponse;
                $fallbackWithMeta['processing_time'] = $fallbackProcessingTime;
                $this->storeConversation($characterId, $conversationId, $prompt, $fallbackWithMeta);
            }

            // Track performance metrics for fallback
            $fallbackForTracking = $fallbackResponse;
            $fallbackForTracking['processing_time'] = $fallbackProcessingTime;
            $this->performanceMonitor->trackRequest($fallbackProvider, $fallbackForTracking);

            /** @var array{content: string, model: string, provider: string, processing_time: float, token_count: int, cost: float, confidence: float, metadata: array<string, mixed>} */
            return [
                'content' => isset($fallbackResponse['content']) && is_string($fallbackResponse['content']) ? $fallbackResponse['content'] : '',
                'model' => isset($fallbackResponse['model']) && is_string($fallbackResponse['model']) ? $fallbackResponse['model'] : '',
                'provider' => $fallbackProvider,
                'processing_time' => $fallbackProcessingTime,
                'token_count' => isset($fallbackResponse['token_count']) && is_int($fallbackResponse['token_count']) ? $fallbackResponse['token_count'] : 0,
                'cost' => isset($fallbackResponse['cost']) && (is_float($fallbackResponse['cost']) || is_int($fallbackResponse['cost'])) ? (float) $fallbackResponse['cost'] : 0.0,
                'confidence' => isset($fallbackResponse['confidence']) && (is_float($fallbackResponse['confidence']) || is_int($fallbackResponse['confidence'])) ? (float) $fallbackResponse['confidence'] : 0.0,
                'metadata' => isset($fallbackResponse['metadata']) && is_array($fallbackResponse['metadata']) ? $fallbackResponse['metadata'] : [],
            ];
        }
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
            $cost = $this->calculateCost($response['token_count'], $model);

            return [
                'content' => $response['content'],
                'model' => $model,
                'token_count' => $response['token_count'],
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
            $agent = $this->createStrandsAgent($prompt, $context);

            // Process request through agent (process is a closure property)
            $processCallback = $agent->process;
            $response = $processCallback();

            $processingTime = microtime(true) - $startTime;

            $tokenCount = isset($response['token_count']) && is_int($response['token_count'])
                ? $response['token_count']
                : (isset($complexity['token_estimate']) && is_int($complexity['token_estimate']) ? $complexity['token_estimate'] : 0);

            $model = isset($response['model']) && is_string($response['model']) ? $response['model'] : 'claude-3-5-sonnet';
            $cost = $this->calculateCost($tokenCount, $model);

            return [
                'content' => $response['content'],
                'model' => $model,
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
            $agent = $this->createAgentCoreAgent($prompt, $context);

            // Process request through agent (process is a closure property)
            $processCallback = $agent->process;
            $response = $processCallback();

            $processingTime = microtime(true) - $startTime;

            $tokenCount = isset($response['token_count']) && is_int($response['token_count'])
                ? $response['token_count']
                : (isset($complexity['token_estimate']) && is_int($complexity['token_estimate']) ? $complexity['token_estimate'] : 0);

            $model = isset($response['model']) && is_string($response['model']) ? $response['model'] : 'claude-3-5-sonnet';
            $cost = $this->calculateCost($tokenCount, $model);

            return [
                'content' => $response['content'],
                'model' => $model,
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
            'bedrock' => $this->ollamaService->isAvailable() ? 'ollama' : null,
            'mcp-strands' => $this->bedrockService->isAvailable() ? 'bedrock' : 'ollama',
            'mcp-agentcore' => $this->bedrockService->isAvailable() ? 'bedrock' : 'ollama',
            default => null,
        };

        if (! $fallbackProvider) {
            throw new \RuntimeException('No fallback provider available', 0, $exception);
        }

        // Retry with fallback provider
        $complexity = $this->analyzeComplexity($prompt, $context);

        // Only ollama or bedrock can be fallback providers
        if ($fallbackProvider === 'ollama') {
            $response = $this->processWithOllama($prompt, $context, $complexity);
        } elseif ($fallbackProvider === 'bedrock') {
            $response = $this->processWithBedrock($prompt, $context, $complexity);
        } else {
            throw new \RuntimeException('Invalid fallback provider');
        }

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
     * @return object&\stdClass
     */
    protected function createStrandsAgent(string $prompt, array $context): object
    {
        // Check if MCP Strands server is available
        if (! $this->mcpClient->isStrandsAgentsAvailable()) {
            throw new \RuntimeException('MCP Strands agents server is not available');
        }

        $mcpClient = $this->mcpClient;

        // Create a simple agent wrapper that delegates to MCP
        $agent = new \stdClass;
        $agent->process = function () use ($mcpClient, $prompt, $context): array {
            // Use MCP client to invoke strands-agents tools
            $result = $mcpClient->callTool('strands-agents', 'create_agent', [
                'prompt' => $prompt,
                'context' => $context,
                'model' => 'claude-3-5-sonnet',
            ]);

            return [
                'content' => isset($result['response']) && is_string($result['response']) ? $result['response'] : '',
                'model' => isset($result['model']) && is_string($result['model']) ? $result['model'] : 'claude-3-5-sonnet',
                'token_count' => isset($result['token_count']) && is_int($result['token_count']) ? $result['token_count'] : 0,
                'confidence' => isset($result['confidence']) && (is_float($result['confidence']) || is_int($result['confidence'])) ? (float) $result['confidence'] : 0.95,
                'agent_id' => isset($result['agent_id']) ? $result['agent_id'] : null,
                'workflow_steps' => isset($result['workflow_steps']) && is_array($result['workflow_steps']) ? $result['workflow_steps'] : [],
            ];
        };

        return $agent;
    }

    /**
     * Create AgentCore agent via MCP
     *
     * Creates an agent using the MCP agentcore-mcp-server for AWS Bedrock
     * AgentCore integration with advanced orchestration capabilities.
     *
     * @param  array<string, mixed>  $context
     * @return object&\stdClass
     */
    protected function createAgentCoreAgent(string $prompt, array $context): object
    {
        // Check if MCP AgentCore server is available
        if (! $this->mcpClient->isAgentCoreAvailable()) {
            throw new \RuntimeException('MCP AgentCore server is not available');
        }

        $mcpClient = $this->mcpClient;

        // Create a simple agent wrapper that delegates to MCP
        $agent = new \stdClass;
        $agent->process = function () use ($mcpClient, $prompt, $context): array {
            // Use MCP client to invoke agentcore-mcp-server tools
            $result = $mcpClient->callTool('agentcore-mcp-server', 'invoke_agent', [
                'prompt' => $prompt,
                'context' => $context,
                'model' => 'claude-3-5-sonnet',
            ]);

            return [
                'content' => isset($result['response']) && is_string($result['response']) ? $result['response'] : '',
                'model' => isset($result['model']) && is_string($result['model']) ? $result['model'] : 'claude-3-5-sonnet',
                'token_count' => isset($result['token_count']) && is_int($result['token_count']) ? $result['token_count'] : 0,
                'confidence' => isset($result['confidence']) && (is_float($result['confidence']) || is_int($result['confidence'])) ? (float) $result['confidence'] : 0.95,
                'agent_id' => isset($result['agent_id']) ? $result['agent_id'] : null,
                'workflow_steps' => isset($result['workflow_steps']) && is_array($result['workflow_steps']) ? $result['workflow_steps'] : [],
            ];
        };

        return $agent;
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

        return isset($context['requires_rag']) && $context['requires_rag'];
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

        return isset($context['requires_multi_step']) && $context['requires_multi_step'];
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
        array $response
    ): void {
        try {
            AIConversation::create([
                'character_id' => $characterId,
                'conversation_id' => $conversationId,
                'message_type' => 'user',
                'message_content' => $prompt,
                'ai_model_used' => null,
                'processing_time' => null,
                'token_count' => null,
                'cost' => null,
            ]);

            AIConversation::create([
                'character_id' => $characterId,
                'conversation_id' => $conversationId,
                'message_type' => 'assistant',
                'message_content' => $response['content'],
                'ai_model_used' => $response['model'],
                'processing_time' => $response['processing_time'],
                'token_count' => $response['token_count'],
                'cost' => $response['cost'],
            ]);
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
     * @return array<int, array<string, mixed>>
     */
    public function getConversationHistory(
        int $characterId,
        ?string $conversationId = null,
        int $limit = 50
    ): array {
        $query = AIConversation::where('character_id', $characterId);

        if ($conversationId) {
            $query->where('conversation_id', $conversationId);
        }

        $conversations = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        /** @var array<int, array<string, mixed>> */
        $result = [];
        foreach ($conversations as $conv) {
            $result[] = [
                'id' => $conv->id,
                'conversation_id' => $conv->conversation_id,
                'message_type' => $conv->getAttribute('conversation_type'),
                'message_content' => $conv->getAttribute('conversation_summary'),
                'ai_model_used' => $conv->getAttribute('ai_model'),
                'processing_time' => $conv->getAttribute('processing_time'),
                'token_count' => $conv->getAttribute('token_count'),
                'cost' => $conv->getAttribute('cost'),
                'created_at' => $conv->created_at?->toIso8601String(),
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
