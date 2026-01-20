<?php

declare(strict_types=1);

namespace App\Services\MCP;

use CloudStudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Agent Routing Service
 *
 * Intelligent routing for AI operations across multiple providers:
 * - Local Ollama models (primary for simple tasks)
 * - AWS Bedrock via MCP (fallback for complex tasks)
 * - MCP Agents (specialized workflows)
 *
 * Features:
 * - Complexity-based routing
 * - Load balancing
 * - Automatic fallback
 * - Performance optimization
 * - Route caching
 *
 * Requirements: 56.1, 56.3, 56.4
 */
class AgentRoutingService
{
    /**
     * Provider types
     */
    public const PROVIDER_OLLAMA = 'ollama';

    public const PROVIDER_BEDROCK = 'bedrock';

    public const PROVIDER_AGENT = 'agent';

    /**
     * Complexity levels
     */
    public const COMPLEXITY_SIMPLE = 'simple';

    public const COMPLEXITY_MODERATE = 'moderate';

    public const COMPLEXITY_COMPLEX = 'complex';

    public const COMPLEXITY_SPECIALIZED = 'specialized';

    /**
     * Performance thresholds (seconds)
     */
    protected const OLLAMA_TIMEOUT = 15.0;

    protected const BEDROCK_TIMEOUT = 30.0;

    protected const AGENT_TIMEOUT = 60.0;

    /**
     * Cost thresholds (per 1K tokens)
     */
    protected const OLLAMA_COST = 0.0; // Free local

    protected const BEDROCK_NOVA_COST = 0.00125;

    protected const BEDROCK_SONNET_COST = 0.003;

    protected const BEDROCK_OPUS_COST = 0.005;

    public function __construct(
        private readonly MCPClientService $mcpClient,
        private readonly CostManagementService $costManager
    ) {}

    /**
     * Route a request to the optimal provider
     *
     * @param  array<string, mixed>  $request
     * @return array{provider: string, model: string, reason: string, estimated_cost: float}
     */
    public function routeRequest(array $request): array
    {
        // Check cache for similar requests
        $cacheKey = $this->generateRouteCacheKey($request);
        $cachedRoute = Cache::get($cacheKey);

        if ($cachedRoute && $this->isRouteValid($cachedRoute)) {
            Log::debug('[AgentRouting] Using cached route', [
                'provider' => $cachedRoute['provider'],
                'cache_key' => $cacheKey,
            ]);

            return $cachedRoute;
        }

        // Detect request complexity
        $complexity = $this->detectComplexity($request);

        // Check budget constraints
        $budgetStatus = $this->costManager->checkBudgetStatus();

        // Select optimal provider
        $route = $this->selectProvider($complexity, $budgetStatus, $request);

        // Cache the route
        Cache::put($cacheKey, $route, 300); // 5 minutes

        Log::info('[AgentRouting] Route selected', [
            'provider' => $route['provider'],
            'model' => $route['model'],
            'complexity' => $complexity,
            'reason' => $route['reason'],
        ]);

        return $route;
    }

    /**
     * Detect request complexity
     *
     * @param  array<string, mixed>  $request
     */
    protected function detectComplexity(array $request): string
    {
        $prompt = $request['prompt'] ?? '';
        $context = $request['context'] ?? [];
        $requiresRAG = $request['requires_rag'] ?? false;
        $multiStep = $request['multi_step'] ?? false;

        // Token count estimation
        $tokenCount = $this->estimateTokenCount($prompt, $context);

        // Specialized agent requirements
        if (isset($request['agent_type']) || $multiStep) {
            return self::COMPLEXITY_SPECIALIZED;
        }

        // Complex reasoning requirements
        if ($requiresRAG || $tokenCount > 4000 || $this->requiresComplexReasoning($prompt)) {
            return self::COMPLEXITY_COMPLEX;
        }

        // Moderate complexity
        if ($tokenCount > 1000 || $this->requiresModerateReasoning($prompt)) {
            return self::COMPLEXITY_MODERATE;
        }

        // Simple queries
        return self::COMPLEXITY_SIMPLE;
    }

    /**
     * Select optimal provider based on complexity and constraints
     *
     * @param  array<string, mixed>  $budgetStatus
     * @param  array<string, mixed>  $request
     * @return array{provider: string, model: string, reason: string, estimated_cost: float}
     */
    protected function selectProvider(string $complexity, array $budgetStatus, array $request): array
    {
        // Check if budget is exceeded
        if ($budgetStatus['is_exceeded']) {
            return $this->selectFallbackProvider($complexity, 'budget_exceeded');
        }

        // Route based on complexity
        return match ($complexity) {
            self::COMPLEXITY_SIMPLE => $this->selectSimpleProvider($request),
            self::COMPLEXITY_MODERATE => $this->selectModerateProvider($request),
            self::COMPLEXITY_COMPLEX => $this->selectComplexProvider($request),
            self::COMPLEXITY_SPECIALIZED => $this->selectSpecializedProvider($request),
            default => $this->selectDefaultProvider(),
        };
    }

    /**
     * Select provider for simple requests
     *
     * @param  array<string, mixed>  $request
     * @return array{provider: string, model: string, reason: string, estimated_cost: float}
     */
    protected function selectSimpleProvider(array $request): array
    {
        // Try Ollama first (free, fast)
        if ($this->isOllamaAvailable()) {
            return [
                'provider' => self::PROVIDER_OLLAMA,
                'model' => (string) config('ai.ollama.model', 'llama3.3'),
                'reason' => 'Simple request - using local Ollama for speed and cost efficiency',
                'estimated_cost' => self::OLLAMA_COST,
            ];
        }

        // Fallback to Bedrock Nova (cheapest cloud option)
        return [
            'provider' => self::PROVIDER_BEDROCK,
            'model' => 'amazon.nova-lite-v1:0',
            'reason' => 'Ollama unavailable - using Bedrock Nova Lite for cost efficiency',
            'estimated_cost' => self::BEDROCK_NOVA_COST,
        ];
    }

    /**
     * Select provider for moderate requests
     *
     * @param  array<string, mixed>  $request
     * @return array{provider: string, model: string, reason: string, estimated_cost: float}
     */
    protected function selectModerateProvider(array $request): array
    {
        // Try Ollama first
        if ($this->isOllamaAvailable()) {
            return [
                'provider' => self::PROVIDER_OLLAMA,
                'model' => (string) config('ai.ollama.model', 'llama3.3'),
                'reason' => 'Moderate request - attempting local Ollama first',
                'estimated_cost' => self::OLLAMA_COST,
            ];
        }

        // Use Bedrock Sonnet for better quality
        return [
            'provider' => self::PROVIDER_BEDROCK,
            'model' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
            'reason' => 'Moderate complexity - using Bedrock Sonnet for balanced performance',
            'estimated_cost' => self::BEDROCK_SONNET_COST,
        ];
    }

    /**
     * Select provider for complex requests
     *
     * @param  array<string, mixed>  $request
     * @return array{provider: string, model: string, reason: string, estimated_cost: float}
     */
    protected function selectComplexProvider(array $request): array
    {
        // Complex requests go directly to Bedrock
        return [
            'provider' => self::PROVIDER_BEDROCK,
            'model' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
            'reason' => 'Complex request requiring advanced reasoning - using Bedrock Sonnet',
            'estimated_cost' => self::BEDROCK_SONNET_COST,
        ];
    }

    /**
     * Select provider for specialized requests
     *
     * @param  array<string, mixed>  $request
     * @return array{provider: string, model: string, reason: string, estimated_cost: float}
     */
    protected function selectSpecializedProvider(array $request): array
    {
        $agentType = $request['agent_type'] ?? 'generic';

        return [
            'provider' => self::PROVIDER_AGENT,
            'model' => $agentType,
            'reason' => "Specialized workflow requiring {$agentType} agent",
            'estimated_cost' => self::BEDROCK_SONNET_COST, // Agents use Bedrock
        ];
    }

    /**
     * Select default provider
     *
     * @return array{provider: string, model: string, reason: string, estimated_cost: float}
     */
    protected function selectDefaultProvider(): array
    {
        return [
            'provider' => self::PROVIDER_OLLAMA,
            'model' => config('ai.ollama.model', 'llama3.3'),
            'reason' => 'Default routing to local Ollama',
            'estimated_cost' => self::OLLAMA_COST,
        ];
    }

    /**
     * Select fallback provider when primary fails
     *
     * @return array{provider: string, model: string, reason: string, estimated_cost: float}
     */
    protected function selectFallbackProvider(string $complexity, string $reason): array
    {
        // If budget exceeded, use Ollama if available
        if ($reason === 'budget_exceeded' && $this->isOllamaAvailable()) {
            return [
                'provider' => self::PROVIDER_OLLAMA,
                'model' => config('ai.ollama.model', 'llama3.3'),
                'reason' => 'Budget exceeded - falling back to free local Ollama',
                'estimated_cost' => self::OLLAMA_COST,
            ];
        }

        // Otherwise use cheapest Bedrock option
        return [
            'provider' => self::PROVIDER_BEDROCK,
            'model' => 'amazon.nova-lite-v1:0',
            'reason' => "Fallback due to: {$reason}",
            'estimated_cost' => self::BEDROCK_NOVA_COST,
        ];
    }

    /**
     * Execute request with automatic fallback
     *
     * @param  array<string, mixed>  $request
     * @return array{success: bool, response: mixed, provider: string, model: string, execution_time: float, cost: float, fallback_used: bool}
     */
    public function executeWithFallback(array $request): array
    {
        $route = $this->routeRequest($request);
        $startTime = microtime(true);

        try {
            // Execute with primary provider
            $response = $this->executeRequest($route, $request);
            $executionTime = microtime(true) - $startTime;

            // Check if performance is acceptable
            if ($this->isPerformanceAcceptable($route['provider'], $executionTime)) {
                // Record successful execution
                $this->recordExecution($route, $executionTime, true);

                return [
                    'success' => true,
                    'response' => $response,
                    'provider' => $route['provider'],
                    'model' => $route['model'],
                    'execution_time' => $executionTime,
                    'cost' => $this->calculateActualCost($route, $request, $response),
                    'fallback_used' => false,
                ];
            }

            // Performance unacceptable, try fallback
            Log::warning('[AgentRouting] Performance threshold exceeded, attempting fallback', [
                'provider' => $route['provider'],
                'execution_time' => $executionTime,
            ]);

            return $this->executeFallback($request, $route, 'performance_threshold');
        } catch (\Exception $e) {
            Log::error('[AgentRouting] Primary provider failed, attempting fallback', [
                'provider' => $route['provider'],
                'error' => $e->getMessage(),
            ]);

            // Record failed execution
            $this->recordExecution($route, microtime(true) - $startTime, false);

            return $this->executeFallback($request, $route, 'provider_failure');
        }
    }

    /**
     * Execute fallback request
     *
     * @param  array<string, mixed>  $request
     * @param  array<string, mixed>  $primaryRoute
     * @return array{success: bool, response: mixed, provider: string, model: string, execution_time: float, cost: float, fallback_used: bool}
     */
    protected function executeFallback(array $request, array $primaryRoute, string $reason): array
    {
        $complexity = $this->detectComplexity($request);
        $fallbackRoute = $this->selectFallbackProvider($complexity, $reason);

        $startTime = microtime(true);

        try {
            $response = $this->executeRequest($fallbackRoute, $request);
            $executionTime = microtime(true) - $startTime;

            // Record successful fallback
            $this->recordExecution($fallbackRoute, $executionTime, true);

            return [
                'success' => true,
                'response' => $response,
                'provider' => $fallbackRoute['provider'],
                'model' => $fallbackRoute['model'],
                'execution_time' => $executionTime,
                'cost' => $this->calculateActualCost($fallbackRoute, $request, $response),
                'fallback_used' => true,
            ];
        } catch (\Exception $e) {
            Log::error('[AgentRouting] Fallback provider also failed', [
                'fallback_provider' => $fallbackRoute['provider'],
                'error' => $e->getMessage(),
            ]);

            // Record failed fallback
            $this->recordExecution($fallbackRoute, microtime(true) - $startTime, false);

            throw new \RuntimeException('All providers failed: '.$e->getMessage());
        }
    }

    /**
     * Execute request with specific provider
     *
     * @param  array<string, mixed>  $route
     * @param  array<string, mixed>  $request
     */
    protected function executeRequest(array $route, array $request): mixed
    {
        return match ($route['provider']) {
            self::PROVIDER_OLLAMA => $this->executeOllama($request),
            self::PROVIDER_BEDROCK => $this->executeBedrock($route['model'], $request),
            self::PROVIDER_AGENT => $this->executeAgent($route['model'], $request),
            default => throw new \InvalidArgumentException("Unknown provider: {$route['provider']}"),
        };
    }

    /**
     * Execute request with Ollama
     *
     * @param  array<string, mixed>  $request
     */
    protected function executeOllama(array $request): string
    {
        $prompt = $request['prompt'] ?? '';
        $context = $request['context'] ?? [];

        $response = Ollama::agent('Umamusume Career Advisor')
            ->model(config('ai.ollama.model', 'llama3.3'))
            ->prompt($prompt)
            ->options([
                'temperature' => $request['temperature'] ?? 0.3,
                'top_p' => $request['top_p'] ?? 0.9,
                'max_tokens' => $request['max_tokens'] ?? 2048,
            ])
            ->ask();

        return (string) $response;
    }

    /**
     * Execute request with Bedrock
     *
     * @param  array<string, mixed>  $request
     */
    protected function executeBedrock(string $model, array $request): string
    {
        // TODO: Implement actual Bedrock API call via MCP
        // For now, return a placeholder
        return "Bedrock response from {$model}";
    }

    /**
     * Execute request with MCP Agent
     *
     * @param  array<string, mixed>  $request
     * @return array<string, mixed>
     */
    protected function executeAgent(string $agentType, array $request): array
    {
        return $this->mcpClient->executeAgent($agentType, $request);
    }

    /**
     * Check if Ollama is available
     */
    protected function isOllamaAvailable(): bool
    {
        try {
            // Check if Ollama service is running
            $cacheKey = 'ollama_availability';
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return (bool) $cached;
            }

            // TODO: Implement actual Ollama health check
            $available = true;

            Cache::put($cacheKey, $available, 60); // Cache for 1 minute

            return $available;
        } catch (\Exception $e) {
            Log::warning('[AgentRouting] Ollama availability check failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if performance is acceptable
     */
    protected function isPerformanceAcceptable(string $provider, float $executionTime): bool
    {
        $threshold = match ($provider) {
            self::PROVIDER_OLLAMA => self::OLLAMA_TIMEOUT,
            self::PROVIDER_BEDROCK => self::BEDROCK_TIMEOUT,
            self::PROVIDER_AGENT => self::AGENT_TIMEOUT,
            default => 30.0,
        };

        return $executionTime <= $threshold;
    }

    /**
     * Calculate actual cost of request
     *
     * @param  array<string, mixed>  $route
     * @param  array<string, mixed>  $request
     */
    protected function calculateActualCost(array $route, array $request, mixed $response): float
    {
        if ($route['provider'] === self::PROVIDER_OLLAMA) {
            return 0.0; // Free
        }

        // Estimate token usage
        $inputTokens = $this->estimateTokenCount($request['prompt'] ?? '', $request['context'] ?? []);
        $outputTokens = $this->estimateTokenCount(
            is_string($response) ? $response : (json_encode($response) ?: '')
        );

        // Calculate cost based on model
        $costPer1K = $route['estimated_cost'];
        $totalTokens = $inputTokens + $outputTokens;

        return ($totalTokens / 1000) * $costPer1K;
    }

    /**
     * Estimate token count
     *
     * @param  array<string, mixed>  $context
     */
    protected function estimateTokenCount(string $text, array $context = []): int
    {
        // Simple estimation: ~4 characters per token
        $textLength = strlen($text);
        $contextLength = strlen(json_encode($context));

        return (int) (($textLength + $contextLength) / 4);
    }

    /**
     * Check if prompt requires complex reasoning
     */
    protected function requiresComplexReasoning(string $prompt): bool
    {
        $complexKeywords = [
            'analyze',
            'compare',
            'evaluate',
            'optimize',
            'strategy',
            'recommend',
            'calculate',
            'predict',
        ];

        $lowerPrompt = strtolower($prompt);

        foreach ($complexKeywords as $keyword) {
            if (str_contains($lowerPrompt, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if prompt requires moderate reasoning
     */
    protected function requiresModerateReasoning(string $prompt): bool
    {
        $moderateKeywords = [
            'explain',
            'describe',
            'summarize',
            'list',
            'show',
        ];

        $lowerPrompt = strtolower($prompt);

        foreach ($moderateKeywords as $keyword) {
            if (str_contains($lowerPrompt, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate cache key for route
     *
     * @param  array<string, mixed>  $request
     */
    protected function generateRouteCacheKey(array $request): string
    {
        $prompt = $request['prompt'] ?? '';
        $agentType = $request['agent_type'] ?? '';
        $complexity = $this->detectComplexity($request);

        return 'route_'.md5($prompt.$agentType.$complexity);
    }

    /**
     * Check if cached route is still valid
     *
     * @param  array<string, mixed>  $route
     */
    protected function isRouteValid(array $route): bool
    {
        $provider = $route['provider'] ?? '';

        // Check if provider is still available
        return match ($provider) {
            self::PROVIDER_OLLAMA => $this->isOllamaAvailable(),
            self::PROVIDER_BEDROCK => $this->mcpClient->isServerHealthy('agentcore-mcp-server'),
            self::PROVIDER_AGENT => $this->mcpClient->isStrandsAgentsAvailable(),
            default => false,
        };
    }

    /**
     * Record execution metrics
     *
     * @param  array{provider?: string, model?: string}  $route
     */
    protected function recordExecution(array $route, float $executionTime, bool $success): void
    {
        $metrics = [
            'provider' => $route['provider'] ?? 'unknown',
            'model' => $route['model'] ?? 'unknown',
            'execution_time' => $executionTime,
            'success' => $success,
            'timestamp' => now()->toIso8601String(),
        ];

        // Store in cache for analytics
        $providerKey = $route['provider'] ?? 'unknown';
        $cacheKey = "routing_metrics_{$providerKey}";
        $allMetrics = Cache::get($cacheKey, []);
        if (! is_array($allMetrics)) {
            $allMetrics = [];
        }
        $allMetrics[] = $metrics;

        // Keep only last 100 metrics
        if (count($allMetrics) > 100) {
            $allMetrics = array_slice($allMetrics, -100);
        }

        Cache::put($cacheKey, $allMetrics, 3600);
    }

    /**
     * Get routing analytics
     *
     * @return array{
     *     total_requests: int,
     *     by_provider: array<string, int>,
     *     avg_execution_time: array<string, float>,
     *     success_rate: array<string, float>,
     *     fallback_rate: float
     * }
     */
    public function getRoutingAnalytics(): array
    {
        $providers = [self::PROVIDER_OLLAMA, self::PROVIDER_BEDROCK, self::PROVIDER_AGENT];
        /** @var array<int, array{provider: string, model: string, execution_time: float, success: bool, timestamp: string}> $allMetrics */
        $allMetrics = [];

        foreach ($providers as $provider) {
            $metrics = Cache::get("routing_metrics_{$provider}", []);
            if (! is_array($metrics)) {
                $metrics = [];
            }
            $allMetrics = array_merge($allMetrics, $metrics);
        }

        if (empty($allMetrics)) {
            return [
                'total_requests' => 0,
                'by_provider' => [],
                'avg_execution_time' => [],
                'success_rate' => [],
                'fallback_rate' => 0.0,
            ];
        }

        $totalRequests = count($allMetrics);
        $byProvider = [];
        $avgExecutionTime = [];
        $successRate = [];

        foreach ($providers as $provider) {
            $providerMetrics = array_filter(
                $allMetrics,
                fn ($m) => is_array($m) && ($m['provider'] ?? null) === $provider
            );
            $count = count($providerMetrics);

            if ($count > 0) {
                $byProvider[$provider] = $count;

                $executionTimes = array_column($providerMetrics, 'execution_time');
                $avgExecutionTime[$provider] = array_sum($executionTimes) / $count;

                $successCount = count(array_filter(
                    $providerMetrics,
                    fn ($m) => is_array($m) && ($m['success'] ?? false)
                ));
                $successRate[$provider] = ($successCount / $count) * 100;
            }
        }

        return [
            'total_requests' => $totalRequests,
            'by_provider' => $byProvider,
            'avg_execution_time' => $avgExecutionTime,
            'success_rate' => $successRate,
            'fallback_rate' => 0.0, // TODO: Calculate from actual fallback tracking
        ];
    }
}
