<?php

namespace App\Services\AI;

use App\Models\AIConversation;
use App\Services\MCP\AgentOrchestrationService;
use App\Services\MCP\MCPClientService;
use App\Services\MCP\Tools\AWSPricingService;
use App\Services\MCP\Tools\Context7Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * AI Dashboard Service
 *
 * Aggregates metrics from all AI services for comprehensive dashboard display.
 * Provides real-time monitoring, performance comparison, cost tracking, and agent management.
 *
 * Requirements: 56.4, 57.5, 13.5
 */
class AIDashboardService
{
    protected MCPClientService $mcpClient;

    protected HybridAIService $hybridAI;

    protected AgentOrchestrationService $agentOrchestration;

    protected AWSPricingService $awsPricing;

    protected Context7Service $context7;

    protected AIPerformanceMonitor $performanceMonitor;

    public function __construct(
        MCPClientService $mcpClient,
        HybridAIService $hybridAI,
        AgentOrchestrationService $agentOrchestration,
        AWSPricingService $awsPricing,
        Context7Service $context7,
        AIPerformanceMonitor $performanceMonitor
    ) {
        $this->mcpClient = $mcpClient;
        $this->hybridAI = $hybridAI;
        $this->agentOrchestration = $agentOrchestration;
        $this->awsPricing = $awsPricing;
        $this->context7 = $context7;
        $this->performanceMonitor = $performanceMonitor;
    }

    /**
     * Get comprehensive dashboard overview
     *
     * @return array{
     *     summary: array<string, mixed>,
     *     servers: array<string, mixed>,
     *     performance: array<string, mixed>,
     *     costs: array<string, mixed>,
     *     agents: array<string, mixed>,
     *     conversations: array<string, mixed>
     * }
     */
    public function getDashboardOverview(): array
    {
        $cacheKey = 'ai_dashboard_overview';

        return Cache::remember($cacheKey, 60, function () {
            return [
                'summary' => $this->getSummaryMetrics(),
                'servers' => $this->getServerStatus(),
                'performance' => $this->getPerformanceComparison(),
                'costs' => $this->getCostSummary(),
                'agents' => $this->getAgentsSummary(),
                'conversations' => $this->getConversationsSummary(),
            ];
        });
    }

    /**
     * Get summary metrics
     *
     * @return array{
     *     total_requests_24h: int,
     *     success_rate: float,
     *     avg_response_time: float,
     *     total_cost_24h: float,
     *     active_agents: int,
     *     healthy_servers: int,
     *     total_servers: int
     * }
     */
    public function getSummaryMetrics(): array
    {
        $metrics24h = $this->getMetricsForPeriod('24h');

        $totalRequests = $metrics24h['total_requests'] ?? 0;
        $successfulRequests = $metrics24h['successful_requests'] ?? 0;
        $successRate = $totalRequests > 0 ? ($successfulRequests / $totalRequests) * 100 : 0;

        $serverHealth = $this->mcpClient->getAllServerHealth();
        $healthyServers = collect($serverHealth)->filter(fn ($h) => $h['status'] === 'healthy')->count();
        $totalServers = count($serverHealth);

        return [
            'total_requests_24h' => $totalRequests,
            'success_rate' => round($successRate, 2),
            'avg_response_time' => $metrics24h['avg_response_time'] ?? 0.0,
            'total_cost_24h' => $metrics24h['total_cost'] ?? 0.0,
            'active_agents' => $this->getActiveAgentCount(),
            'healthy_servers' => $healthyServers,
            'total_servers' => $totalServers,
        ];
    }

    /**
     * Get MCP server status with health checks
     *
     * @return array<string, array{
     *     name: string,
     *     status: string,
     *     is_connected: bool,
     *     response_time: float|null,
     *     consecutive_failures: int,
     *     last_success_at: string|null,
     *     last_failure_at: string|null,
     *     capabilities: array<string, mixed>
     * }>
     */
    public function getServerStatus(): array
    {
        $healthCheck = $this->mcpClient->healthCheck();
        $servers = [];

        foreach ($healthCheck as $name => $result) {
            $health = $this->mcpClient->getServerHealth($name);

            $servers[$name] = [
                'name' => $name,
                'status' => $result['status'],
                'is_connected' => $result['status'] === 'healthy',
                'response_time' => null, // TODO: Implement actual response time measurement
                'consecutive_failures' => $health['consecutive_failures'] ?? 0,
                'last_success_at' => null, // TODO: Track from database
                'last_failure_at' => null, // TODO: Track from database
                'capabilities' => $result['capabilities'] ?? [],
            ];
        }

        return $servers;
    }

    /**
     * Get AI provider performance comparison
     *
     * @return array{
     *     providers: array<string, array{
     *         name: string,
     *         requests_24h: int,
     *         success_rate: float,
     *         avg_response_time: float,
     *         min_response_time: float,
     *         max_response_time: float,
     *         p95_response_time: float,
     *         p99_response_time: float,
     *         total_tokens: int,
     *         total_cost: float,
     *         avg_confidence: float
     *     }>,
     *     comparison: array<string, mixed>
     * }
     */
    public function getPerformanceComparison(): array
    {
        $providers = ['ollama', 'bedrock', 'mcp-strands', 'mcp-agentcore'];
        $providerMetrics = [];

        foreach ($providers as $provider) {
            $metrics = $this->getProviderMetrics($provider, '24h');
            $providerMetrics[$provider] = [
                'name' => $provider,
                'requests_24h' => $metrics['request_count'] ?? 0,
                'success_rate' => $metrics['success_rate'] ?? 0.0,
                'avg_response_time' => $metrics['avg_response_time'] ?? 0.0,
                'min_response_time' => $metrics['min_response_time'] ?? 0.0,
                'max_response_time' => $metrics['max_response_time'] ?? 0.0,
                'p95_response_time' => $metrics['p95_response_time'] ?? 0.0,
                'p99_response_time' => $metrics['p99_response_time'] ?? 0.0,
                'total_tokens' => $metrics['total_tokens'] ?? 0,
                'total_cost' => $metrics['total_cost'] ?? 0.0,
                'avg_confidence' => $metrics['avg_confidence'] ?? 0.0,
            ];
        }

        // Generate comparison insights
        $comparison = $this->generateProviderComparison($providerMetrics);

        return [
            'providers' => $providerMetrics,
            'comparison' => $comparison,
        ];
    }

    /**
     * Get cost summary and budget status
     *
     * @return array{
     *     daily_cost: float,
     *     weekly_cost: float,
     *     monthly_cost: float,
     *     projected_monthly: float,
     *     by_provider: array<string, float>,
     *     by_model: array<string, float>,
     *     budget_status: array<string, mixed>,
     *     optimization_recommendations: array<int, array<string, mixed>>
     * }
     */
    public function getCostSummary(): array
    {
        $dailyCost = $this->getCostForPeriod('1d');
        $weeklyCost = $this->getCostForPeriod('7d');
        $monthlyCost = $this->getCostForPeriod('30d');

        // Project monthly cost based on daily average
        $projectedMonthly = $dailyCost * 30;

        // Get cost breakdown by provider and model
        $byProvider = $this->getCostByProvider('30d');
        $byModel = $this->getCostByModel('30d');

        // Get budget status
        $budgetStatus = $this->getBudgetStatus($monthlyCost, $projectedMonthly);

        // Get optimization recommendations
        $optimizationRecs = $this->awsPricing->getCostOptimizationRecommendations([]);

        return [
            'daily_cost' => round($dailyCost, 4),
            'weekly_cost' => round($weeklyCost, 4),
            'monthly_cost' => round($monthlyCost, 4),
            'projected_monthly' => round($projectedMonthly, 4),
            'by_provider' => $byProvider,
            'by_model' => $byModel,
            'budget_status' => $budgetStatus,
            'optimization_recommendations' => $optimizationRecs['recommendations'] ?? [],
        ];
    }

    /**
     * Get agents summary
     *
     * @return array{
     *     total_agents: int,
     *     active_agents: int,
     *     idle_agents: int,
     *     agents: array<int, array<string, mixed>>
     * }
     */
    public function getAgentsSummary(): array
    {
        // TODO: Implement actual agent tracking from database
        // For now, return static agent information

        $agents = [
            [
                'id' => 1,
                'name' => 'Training Optimization Agent',
                'type' => 'training',
                'status' => 'idle',
                'tasks_completed' => 0,
                'success_rate' => 0.0,
                'avg_duration' => 0.0,
                'last_active_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'Career Strategy Agent',
                'type' => 'career',
                'status' => 'idle',
                'tasks_completed' => 0,
                'success_rate' => 0.0,
                'avg_duration' => 0.0,
                'last_active_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Race Analysis Agent',
                'type' => 'race',
                'status' => 'idle',
                'tasks_completed' => 0,
                'success_rate' => 0.0,
                'avg_duration' => 0.0,
                'last_active_at' => null,
            ],
            [
                'id' => 4,
                'name' => 'Skill Management Agent',
                'type' => 'skill',
                'status' => 'idle',
                'tasks_completed' => 0,
                'success_rate' => 0.0,
                'avg_duration' => 0.0,
                'last_active_at' => null,
            ],
        ];

        $activeAgents = collect($agents)->filter(fn ($a) => $a['status'] === 'processing')->count();
        $idleAgents = collect($agents)->filter(fn ($a) => $a['status'] === 'idle')->count();

        return [
            'total_agents' => count($agents),
            'active_agents' => $activeAgents,
            'idle_agents' => $idleAgents,
            'agents' => $agents,
        ];
    }

    /**
     * Get conversations summary with tool usage
     *
     * @return array{
     *     total_conversations: int,
     *     total_messages: int,
     *     avg_conversation_length: float,
     *     most_used_tools: array<int, array{tool: string, count: int}>,
     *     recent_conversations: array<int, array<string, mixed>>
     * }
     */
    public function getConversationsSummary(): array
    {
        $totalConversations = AIConversation::distinct('conversation_id')->count('conversation_id');
        $totalMessages = AIConversation::count();

        $avgLength = $totalConversations > 0 ? $totalMessages / $totalConversations : 0;

        // Get recent conversations
        $recentConversations = AIConversation::with('character')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($conv) => [
                'id' => $conv->id,
                'conversation_id' => $conv->conversation_id,
                'character_name' => $conv->character?->name ?? 'Unknown',
                'message_type' => $conv->message_type,
                'ai_model_used' => $conv->ai_model_used,
                'processing_time' => $conv->processing_time,
                'cost' => $conv->cost,
                'created_at' => $conv->created_at?->toIso8601String(),
            ])
            ->toArray();

        // TODO: Implement tool usage tracking
        $mostUsedTools = [];

        return [
            'total_conversations' => $totalConversations,
            'total_messages' => $totalMessages,
            'avg_conversation_length' => round($avgLength, 2),
            'most_used_tools' => $mostUsedTools,
            'recent_conversations' => $recentConversations,
        ];
    }

    /**
     * Get metrics for a specific time period
     *
     * @return array<string, mixed>
     */
    protected function getMetricsForPeriod(string $period): array
    {
        $hours = match ($period) {
            '1h' => 1,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };

        $since = now()->subHours($hours);

        $conversations = AIConversation::where('created_at', '>=', $since)
            ->where('message_type', 'assistant')
            ->get();

        $totalRequests = $conversations->count();
        $successfulRequests = $conversations->filter(fn ($c) => $c->ai_model_used !== null)->count();
        $avgResponseTime = $conversations->avg('processing_time') ?? 0.0;
        $totalCost = $conversations->sum('cost') ?? 0.0;

        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'avg_response_time' => round($avgResponseTime, 3),
            'total_cost' => round($totalCost, 6),
        ];
    }

    /**
     * Get provider-specific metrics
     *
     * @return array<string, mixed>
     */
    protected function getProviderMetrics(string $provider, string $period): array
    {
        $hours = match ($period) {
            '1h' => 1,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };

        $since = now()->subHours($hours);

        // Map provider names to model patterns
        $modelPattern = match ($provider) {
            'ollama' => ['llama%', 'mistral%', 'qwen%'],
            'bedrock' => ['claude%', 'nova%'],
            'mcp-strands' => ['strands%'],
            'mcp-agentcore' => ['agentcore%'],
            default => [],
        };

        if (empty($modelPattern)) {
            return [];
        }

        $query = AIConversation::where('created_at', '>=', $since)
            ->where('message_type', 'assistant');

        foreach ($modelPattern as $pattern) {
            $query->orWhere('ai_model_used', 'like', $pattern);
        }

        $conversations = $query->get();

        $requestCount = $conversations->count();
        $successCount = $conversations->filter(fn ($c) => $c->ai_model_used !== null)->count();
        $successRate = $requestCount > 0 ? ($successCount / $requestCount) * 100 : 0;

        $responseTimes = $conversations->pluck('processing_time')->filter()->sort()->values();
        $avgResponseTime = $responseTimes->avg() ?? 0.0;
        $minResponseTime = $responseTimes->min() ?? 0.0;
        $maxResponseTime = $responseTimes->max() ?? 0.0;

        // Calculate percentiles
        $p95Index = (int) ceil($responseTimes->count() * 0.95) - 1;
        $p99Index = (int) ceil($responseTimes->count() * 0.99) - 1;
        $p95ResponseTime = $responseTimes->get($p95Index) ?? 0.0;
        $p99ResponseTime = $responseTimes->get($p99Index) ?? 0.0;

        $totalTokens = $conversations->sum('token_count') ?? 0;
        $totalCost = $conversations->sum('cost') ?? 0.0;

        return [
            'request_count' => $requestCount,
            'success_rate' => round($successRate, 2),
            'avg_response_time' => round($avgResponseTime, 3),
            'min_response_time' => round($minResponseTime, 3),
            'max_response_time' => round($maxResponseTime, 3),
            'p95_response_time' => round($p95ResponseTime, 3),
            'p99_response_time' => round($p99ResponseTime, 3),
            'total_tokens' => $totalTokens,
            'total_cost' => round($totalCost, 6),
            'avg_confidence' => 0.0, // TODO: Track confidence scores
        ];
    }

    /**
     * Generate provider comparison insights
     *
     * @param  array<string, array<string, mixed>>  $providerMetrics
     * @return array<string, mixed>
     */
    protected function generateProviderComparison(array $providerMetrics): array
    {
        $fastest = null;
        $cheapest = null;
        $mostReliable = null;

        $fastestTime = PHP_FLOAT_MAX;
        $cheapestCost = PHP_FLOAT_MAX;
        $highestSuccessRate = 0.0;

        foreach ($providerMetrics as $provider => $metrics) {
            if ($metrics['avg_response_time'] > 0 && $metrics['avg_response_time'] < $fastestTime) {
                $fastestTime = $metrics['avg_response_time'];
                $fastest = $provider;
            }

            if ($metrics['total_cost'] > 0 && $metrics['total_cost'] < $cheapestCost) {
                $cheapestCost = $metrics['total_cost'];
                $cheapest = $provider;
            }

            if ($metrics['success_rate'] > $highestSuccessRate) {
                $highestSuccessRate = $metrics['success_rate'];
                $mostReliable = $provider;
            }
        }

        return [
            'fastest_provider' => $fastest,
            'cheapest_provider' => $cheapest,
            'most_reliable_provider' => $mostReliable,
            'recommendation' => $this->generateProviderRecommendation($fastest, $cheapest, $mostReliable),
        ];
    }

    /**
     * Generate provider recommendation
     */
    protected function generateProviderRecommendation(?string $fastest, ?string $cheapest, ?string $mostReliable): string
    {
        if ($fastest === $cheapest && $cheapest === $mostReliable) {
            return "Use {$fastest} for all requests - it's the best overall choice.";
        }

        if ($cheapest === 'ollama') {
            return 'Use Ollama for simple requests (fastest and free), Bedrock for complex tasks.';
        }

        return "Balance between speed (use {$fastest}), cost (use {$cheapest}), and reliability (use {$mostReliable}).";
    }

    /**
     * Get cost for a specific period
     */
    protected function getCostForPeriod(string $period): float
    {
        $hours = match ($period) {
            '1h' => 1,
            '1d' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };

        $since = now()->subHours($hours);

        return AIConversation::where('created_at', '>=', $since)
            ->where('message_type', 'assistant')
            ->sum('cost') ?? 0.0;
    }

    /**
     * Get cost breakdown by provider
     *
     * @return array<string, float>
     */
    protected function getCostByProvider(string $period): array
    {
        $hours = match ($period) {
            '1h' => 1,
            '1d' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 720,
        };

        $since = now()->subHours($hours);

        $costs = AIConversation::where('created_at', '>=', $since)
            ->where('message_type', 'assistant')
            ->whereNotNull('ai_model_used')
            ->get()
            ->groupBy(function ($conv) {
                $model = $conv->ai_model_used ?? '';
                if (str_contains($model, 'llama') || str_contains($model, 'mistral') || str_contains($model, 'qwen')) {
                    return 'ollama';
                }
                if (str_contains($model, 'claude') || str_contains($model, 'nova')) {
                    return 'bedrock';
                }
                if (str_contains($model, 'strands')) {
                    return 'mcp-strands';
                }
                if (str_contains($model, 'agentcore')) {
                    return 'mcp-agentcore';
                }

                return 'unknown';
            })
            ->map(fn ($group) => round($group->sum('cost'), 6))
            ->toArray();

        return $costs;
    }

    /**
     * Get cost breakdown by model
     *
     * @return array<string, float>
     */
    protected function getCostByModel(string $period): array
    {
        $hours = match ($period) {
            '1h' => 1,
            '1d' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 720,
        };

        $since = now()->subHours($hours);

        $costs = AIConversation::where('created_at', '>=', $since)
            ->where('message_type', 'assistant')
            ->whereNotNull('ai_model_used')
            ->select('ai_model_used', DB::raw('SUM(cost) as total_cost'))
            ->groupBy('ai_model_used')
            ->get()
            ->pluck('total_cost', 'ai_model_used')
            ->map(fn ($cost) => round($cost, 6))
            ->toArray();

        return $costs;
    }

    /**
     * Get budget status
     *
     * @return array{
     *     budget_limit: float,
     *     current_spend: float,
     *     projected_spend: float,
     *     remaining_budget: float,
     *     budget_utilization: float,
     *     status: string,
     *     alert_level: string
     * }
     */
    protected function getBudgetStatus(float $currentSpend, float $projectedSpend): array
    {
        // TODO: Make budget limit configurable
        $budgetLimit = 100.0; // $100/month default

        $remainingBudget = $budgetLimit - $currentSpend;
        $budgetUtilization = $budgetLimit > 0 ? ($currentSpend / $budgetLimit) * 100 : 0;

        $status = match (true) {
            $budgetUtilization >= 100 => 'exceeded',
            $budgetUtilization >= 90 => 'critical',
            $budgetUtilization >= 75 => 'warning',
            default => 'healthy',
        };

        $alertLevel = match ($status) {
            'exceeded' => 'danger',
            'critical' => 'danger',
            'warning' => 'warning',
            default => 'success',
        };

        return [
            'budget_limit' => $budgetLimit,
            'current_spend' => round($currentSpend, 4),
            'projected_spend' => round($projectedSpend, 4),
            'remaining_budget' => round($remainingBudget, 4),
            'budget_utilization' => round($budgetUtilization, 2),
            'status' => $status,
            'alert_level' => $alertLevel,
        ];
    }

    /**
     * Get active agent count
     */
    protected function getActiveAgentCount(): int
    {
        // TODO: Implement actual agent tracking
        return 0;
    }
}
