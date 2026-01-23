<?php

declare(strict_types=1);

namespace App\Services\MCP;

use App\Models\MCPToolUsage;
use App\Models\UserPreference;
use App\Services\MCPMonitoringService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Cost Optimization Service with AWS Pricing Integration
 *
 * Provides cost optimization recommendations using awspricing MCP server,
 * budget management, and cost-aware routing strategies.
 *
 * Requirements: 14.5, 55.4, 56.4, Task 4.4.5
 */
class CostOptimizationService
{
    /**
     * Cost optimization cache prefix
     */
    protected const COST_CACHE_PREFIX = 'cost_optimization:';

    /**
     * Cost optimization cache TTL in seconds (1 hour)
     */
    protected const COST_CACHE_TTL = 3600;

    /**
     * Budget warning threshold (%)
     */
    protected const BUDGET_WARNING_THRESHOLD = 75.0;

    /**
     * Budget critical threshold (%)
     */
    protected const BUDGET_CRITICAL_THRESHOLD = 90.0;

    /**
     * Default monthly budget (USD)
     */
    protected const DEFAULT_MONTHLY_BUDGET = 10.0;

    public function __construct(
        protected MCPClientService $mcpClient,
        protected MCPMonitoringService $mcpMonitoring
    ) {}

    /**
     * Get comprehensive cost optimization recommendations
     *
     * @return array{
     *     summary: array<string, mixed>,
     *     budget_status: array<string, mixed>,
     *     cost_breakdown: array<string, mixed>,
     *     optimization_opportunities: array<array<string, mixed>>,
     *     recommendations: array<string>,
     *     projected_costs: array<string, mixed>
     * }
     */
    public function getCostOptimizationRecommendations(): array
        $cacheKey = self::COST_CACHE_PREFIX."recommendations:{$userId}";

        if (Cache::has($cacheKey)) {
            Log::debug('[CostOptimization] Returning cached recommendations', [
                'user_id' => $userId,
            ]);

            return Cache::get($cacheKey);
        }

        Log::info('[CostOptimization] Generating cost optimization recommendations', [
            'user_id' => $userId,
        ]);

        $startTime = microtime(true);

        // Get current cost data
        $costAnalytics = $this->mcpMonitoring->getCostAnalytics($userId, 'month');

        $recommendations = [
            'summary' => $this->getCostSummary($userId, $costAnalytics),
            'budget_status' => $this->getBudgetStatus($userId, $costAnalytics['total_cost']),
            'cost_breakdown' => $this->getCostBreakdown($costAnalytics),
            'optimization_opportunities' => $this->identifyOptimizationOpportunities($userId, $costAnalytics),
            'recommendations' => $this->generateCostRecommendations($userId, $costAnalytics),
            'projected_costs' => $this->projectFutureCosts($userId, $costAnalytics),
        ];

        $duration = (microtime(true) - $startTime) * 1000;

        Log::info('[CostOptimization] Recommendations generated', [
            'user_id' => $userId,
            'duration_ms' => round($duration, 2),
            'total_opportunities' => count($recommendations['optimization_opportunities']),
        ]);

        // Cache the recommendations
        Cache::put($cacheKey, $recommendations, self::COST_CACHE_TTL);

        return $recommendations;
    }

    /**
     * Get cost summary
     *
     * @param  array<string, mixed>  $costAnalytics
     * @return array{
     *     current_month_cost: float,
     *     daily_average: float,
     *     projected_month_end: float,
     *     cost_trend: string,
     *     highest_cost_day: array<string, mixed>,
     *     cost_efficiency_score: float
     * }
     */
    protected function getCostSummary(): array
        $currentMonthCost = $costAnalytics['total_cost'];
        $daysInMonth = now()->daysInMonth;
        $currentDay = now()->day;

        $dailyAverage = $currentDay > 0 ? $currentMonthCost / $currentDay : 0;
        $projectedMonthEnd = $dailyAverage * $daysInMonth;

        // Determine cost trend
        $costTrend = $this->determineCostTrend($userId);

        // Find highest cost day
        $highestCostDay = collect($costAnalytics['daily_costs'])
            ->sortByDesc('cost')
            ->first() ?? ['date' => 'N/A', 'cost' => 0];

        // Calculate cost efficiency score (0-100)
        $costEfficiencyScore = $this->calculateCostEfficiencyScore($userId, $costAnalytics);

        return [
            'current_month_cost' => round($currentMonthCost, 2),
            'daily_average' => round($dailyAverage, 4),
            'projected_month_end' => round($projectedMonthEnd, 2),
            'cost_trend' => $costTrend,
            'highest_cost_day' => $highestCostDay,
            'cost_efficiency_score' => round($costEfficiencyScore, 2),
        ];
    }

    /**
     * Get budget status
     *
     * @return array{
     *     budget_limit: float,
     *     current_spend: float,
     *     remaining_budget: float,
     *     percentage_used: float,
     *     status: string,
     *     days_remaining: int,
     *     projected_overage: float,
     *     alert_level: string
     * }
     */
    protected function getBudgetStatus(): array
        // Get user's budget limit from preferences
        $budgetLimit = $this->getUserBudgetLimit($userId);

        $remainingBudget = max(0, $budgetLimit - $currentSpend);
        $percentageUsed = $budgetLimit > 0 ? ($currentSpend / $budgetLimit) * 100 : 0;

        $status = match (true) {
            $percentageUsed >= 100 => 'exceeded',
            $percentageUsed >= self::BUDGET_CRITICAL_THRESHOLD => 'critical',
            $percentageUsed >= self::BUDGET_WARNING_THRESHOLD => 'warning',
            default => 'normal',
        };

        $daysRemaining = now()->daysInMonth - now()->day;

        // Calculate projected overage
        $dailyAverage = now()->day > 0 ? $currentSpend / now()->day : 0;
        $projectedTotal = $dailyAverage * now()->daysInMonth;
        $projectedOverage = max(0, $projectedTotal - $budgetLimit);

        $alertLevel = match (true) {
            $percentageUsed >= 100 || $projectedOverage > 0 => 'critical',
            $percentageUsed >= self::BUDGET_CRITICAL_THRESHOLD => 'high',
            $percentageUsed >= self::BUDGET_WARNING_THRESHOLD => 'medium',
            default => 'low',
        };

        return [
            'budget_limit' => $budgetLimit,
            'current_spend' => round($currentSpend, 2),
            'remaining_budget' => round($remainingBudget, 2),
            'percentage_used' => round($percentageUsed, 2),
            'status' => $status,
            'days_remaining' => $daysRemaining,
            'projected_overage' => round($projectedOverage, 2),
            'alert_level' => $alertLevel,
        ];
    }

    /**
     * Get cost breakdown
     *
     * @param  array<string, mixed>  $costAnalytics
     * @return array{
     *     by_server: array<array<string, mixed>>,
     *     by_tool: array<array<string, mixed>>,
     *     by_category: array<array<string, mixed>>
     * }
     */
    protected function getCostBreakdown(): array
        // Cost by server with percentages
        $totalCost = $costAnalytics['total_cost'];
        $byServer = collect($costAnalytics['cost_by_server'])->map(function ($item) use ($totalCost) {
            $percentage = $totalCost > 0 ? ($item['cost'] / $totalCost) * 100 : 0;

            return array_merge($item, [
                'percentage' => round($percentage, 2),
                'cost_per_request' => $item['requests'] > 0 ? round($item['cost'] / $item['requests'], 6) : 0,
            ]);
        })->all();

        // Cost by tool with percentages
        $byTool = collect($costAnalytics['cost_by_tool'])->map(function ($item) use ($totalCost) {
            $percentage = $totalCost > 0 ? ($item['cost'] / $totalCost) * 100 : 0;

            return array_merge($item, [
                'percentage' => round($percentage, 2),
            ]);
        })->all();

        // Cost by category (AI, Infrastructure, Data)
        $byCategory = $this->categorizeCosts($costAnalytics);

        return [
            'by_server' => $byServer,
            'by_tool' => $byTool,
            'by_category' => $byCategory,
        ];
    }

    /**
     * Identify optimization opportunities
     *
     * @param  array<string, mixed>  $costAnalytics
     * @return array<array{
     *     type: string,
     *     priority: string,
     *     title: string,
     *     description: string,
     *     potential_savings: float,
     *     implementation_effort: string,
     *     action: string
     * }>
     */
    protected function identifyOptimizationOpportunities(): array
        $opportunities = [];

        // Check for expensive tools
        foreach ($costAnalytics['cost_by_tool'] as $tool) {
            if ($tool['cost'] > 1.0 && $tool['requests'] > 10) {
                $avgCost = $tool['average_cost'];
                $potentialSavings = $tool['cost'] * 0.3; // Assume 30% savings with optimization

                $opportunities[] = [
                    'type' => 'expensive_tool',
                    'priority' => 'high',
                    'title' => "Optimize {$tool['tool']} usage",
                    'description' => sprintf(
                        'Tool "%s" costs $%.4f per request. Consider caching results or reducing call frequency.',
                        $tool['tool'],
                        $avgCost
                    ),
                    'potential_savings' => round($potentialSavings, 2),
                    'implementation_effort' => 'medium',
                    'action' => 'implement_caching',
                ];
            }
        }

        // Check for high-frequency low-value calls
        $toolUsage = MCPToolUsage::forUser($userId)
            ->betweenDates(now()->startOfMonth(), now())
            ->get();

        $highFrequencyTools = $toolUsage->groupBy('tool_name')
            ->filter(fn ($items) => $items->count() > 100)
            ->map(function ($items, $toolName) {
                return [
                    'tool' => $toolName,
                    'count' => $items->count(),
                    'total_cost' => $items->sum('cost_estimate'),
                    'avg_cost' => $items->avg('cost_estimate'),
                ];
            });

        foreach ($highFrequencyTools as $tool) {
            if ($tool['avg_cost'] < 0.001) {
                $potentialSavings = $tool['total_cost'] * 0.5; // 50% savings with batching

                $opportunities[] = [
                    'type' => 'high_frequency_calls',
                    'priority' => 'medium',
                    'title' => "Batch {$tool['tool']} requests",
                    'description' => sprintf(
                        'Tool "%s" called %d times. Consider batching requests to reduce overhead.',
                        $tool['tool'],
                        $tool['count']
                    ),
                    'potential_savings' => round($potentialSavings, 4),
                    'implementation_effort' => 'low',
                    'action' => 'implement_batching',
                ];
            }
        }

        // Check for model selection optimization
        if ($this->mcpClient->isServerEnabled('agentcore-mcp-server')) {
            $opportunities[] = [
                'type' => 'model_selection',
                'priority' => 'medium',
                'title' => 'Optimize AI model selection',
                'description' => 'Use cost-effective models (Claude Haiku, Nova Lite) for simple tasks instead of premium models.',
                'potential_savings' => round($costAnalytics['total_cost'] * 0.2, 2),
                'implementation_effort' => 'medium',
                'action' => 'implement_smart_routing',
            ];
        }

        // Sort by potential savings
        usort($opportunities, fn ($a, $b) => $b['potential_savings'] <=> $a['potential_savings']);

        return $opportunities;
    }

    /**
     * Generate cost recommendations
     *
     * @param  array<string, mixed>  $costAnalytics
     * @return array<string>
     */
    protected function generateCostRecommendations(): array
        $recommendations = [];

        $budgetStatus = $this->getBudgetStatus($userId, $costAnalytics['total_cost']);

        // Budget-based recommendations
        if ($budgetStatus['status'] === 'exceeded') {
            $recommendations[] = '⚠️ Budget exceeded by $'.abs($budgetStatus['remaining_budget']).'. Consider reducing API usage or increasing budget limit.';
        } elseif ($budgetStatus['status'] === 'critical') {
            $recommendations[] = "⚠️ Budget usage at {$budgetStatus['percentage_used']}%. Implement cost controls to avoid overage.";
        } elseif ($budgetStatus['projected_overage'] > 0) {
            $recommendations[] = '📊 Projected to exceed budget by $'.$budgetStatus['projected_overage'].' at current usage rate.';
        }

        // Tool-specific recommendations
        $topCostTools = collect($costAnalytics['cost_by_tool'])->take(3);

        foreach ($topCostTools as $tool) {
            if ($tool['cost'] > 0.5) {
                $recommendations[] = "💡 Tool '{$tool['tool']}' accounts for $".round($tool['cost'], 2).' of costs. Consider optimization.';
            }
        }

        // Server-specific recommendations
        foreach ($costAnalytics['cost_by_server'] as $server) {
            if ($server['cost'] > 2.0) {
                $recommendations[] = "🔧 Server '{$server['server']}' costs $".round($server['cost'], 2).'. Review usage patterns.';
            }
        }

        // General optimization recommendations
        if ($costAnalytics['total_cost'] > 5.0) {
            $recommendations[] = '✨ Implement response caching to reduce redundant API calls and lower costs.';
            $recommendations[] = '🎯 Use cost-aware routing to automatically select cheaper models for simple tasks.';
        }

        // AWS pricing recommendations (if awspricing MCP available)
        if ($this->mcpClient->isServerEnabled('awspricing')) {
            $recommendations[] = '💰 Use awspricing MCP server to get real-time pricing data for cost-optimized decisions.';
        }

        if (empty($recommendations)) {
            $recommendations[] = '✅ Cost usage is optimal. Continue monitoring for efficiency.';
        }

        return $recommendations;
    }

    /**
     * Project future costs
     *
     * @param  array<string, mixed>  $costAnalytics
     * @return array{
     *     next_7_days: float,
     *     next_30_days: float,
     *     month_end: float,
     *     confidence: string,
     *     factors: array<string>
     * }
     */
    protected function projectFutureCosts(): array
        $currentDay = now()->day;
        $daysInMonth = now()->daysInMonth;
        $currentCost = $costAnalytics['total_cost'];

        $dailyAverage = $currentDay > 0 ? $currentCost / $currentDay : 0;

        // Project costs
        $next7Days = $dailyAverage * 7;
        $next30Days = $dailyAverage * 30;
        $monthEnd = $dailyAverage * $daysInMonth;

        // Determine confidence based on data points
        $confidence = match (true) {
            $currentDay >= 20 => 'high',
            $currentDay >= 10 => 'medium',
            default => 'low',
        };

        // Factors affecting projection
        $factors = [
            'Based on '.$currentDay.' days of data',
            'Daily average: $'.round($dailyAverage, 4),
        ];

        if ($costAnalytics['total_requests'] < 100) {
            $factors[] = 'Low request volume may affect accuracy';
        }

        return [
            'next_7_days' => round($next7Days, 2),
            'next_30_days' => round($next30Days, 2),
            'month_end' => round($monthEnd, 2),
            'confidence' => $confidence,
            'factors' => $factors,
        ];
    }

    /**
     * Get user budget limit
     */
    protected function getUserBudgetLimit(int $userId): float
    {
        $preference = UserPreference::forUser($userId)
            ->category('budget')
            ->byKey('monthly_limit')
            ->first();

        return $preference ? (float) $preference->preference_value : self::DEFAULT_MONTHLY_BUDGET;
    }

    /**
     * Update user budget limit
     */
    public function updateBudgetLimit(int $userId, float $limit): void
    {
        $preference = UserPreference::forUser($userId)
            ->category('budget')
            ->byKey('monthly_limit')
            ->first();

        if ($preference) {
            $preference->setValue($limit);
        } else {
            UserPreference::create([
                'user_id' => $userId,
                'preference_category' => 'budget',
                'preference_key' => 'monthly_limit',
                'preference_value' => $limit,
                'value_type' => 'float',
                'last_modified_at' => now(),
            ]);
        }

        Log::info('[CostOptimization] Budget limit updated', [
            'user_id' => $userId,
            'new_limit' => $limit,
        ]);

        // Clear cached recommendations
        $cacheKey = self::COST_CACHE_PREFIX."recommendations:{$userId}";
        Cache::forget($cacheKey);
    }

    /**
     * Determine cost trend
     */
    protected function determineCostTrend(int $userId): string
    {
        // Compare current week to previous week
        $currentWeekCost = MCPToolUsage::forUser($userId)
            ->betweenDates(now()->startOfWeek(), now())
            ->sum('cost_estimate');

        $previousWeekCost = MCPToolUsage::forUser($userId)
            ->betweenDates(now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek())
            ->sum('cost_estimate');

        if ($previousWeekCost == 0) {
            return 'stable';
        }

        $change = (($currentWeekCost - $previousWeekCost) / $previousWeekCost) * 100;

        return match (true) {
            $change > 20 => 'increasing_rapidly',
            $change > 5 => 'increasing',
            $change < -20 => 'decreasing_rapidly',
            $change < -5 => 'decreasing',
            default => 'stable',
        };
    }

    /**
     * Calculate cost efficiency score
     *
     * @param  array<string, mixed>  $costAnalytics
     */
    protected function calculateCostEfficiencyScore(int $userId, array $costAnalytics): float
    {
        $score = 100.0;

        // Deduct points for high costs
        if ($costAnalytics['total_cost'] > 10.0) {
            $score -= 20;
        } elseif ($costAnalytics['total_cost'] > 5.0) {
            $score -= 10;
        }

        // Deduct points for expensive tools
        $expensiveTools = collect($costAnalytics['cost_by_tool'])
            ->filter(fn ($tool) => $tool['average_cost'] > 0.01)
            ->count();

        $score -= min($expensiveTools * 5, 20);

        // Deduct points for budget overage
        $budgetStatus = $this->getBudgetStatus($userId, $costAnalytics['total_cost']);

        if ($budgetStatus['status'] === 'exceeded') {
            $score -= 30;
        } elseif ($budgetStatus['status'] === 'critical') {
            $score -= 15;
        }

        return max(0, $score);
    }

    /**
     * Categorize costs
     *
     * @param  array<string, mixed>  $costAnalytics
     * @return array<array{category: string, cost: float, percentage: float}>
     */
    protected function categorizeCosts(): array
        $categories = [
            'ai' => 0.0,
            'infrastructure' => 0.0,
            'data' => 0.0,
            'other' => 0.0,
        ];

        foreach ($costAnalytics['cost_by_server'] as $server) {
            $category = match (true) {
                str_contains($server['server'], 'agentcore') || str_contains($server['server'], 'strands') => 'ai',
                str_contains($server['server'], 'aws') => 'infrastructure',
                str_contains($server['server'], 'fetch') || str_contains($server['server'], 'context') => 'data',
                default => 'other',
            };

            $categories[$category] += $server['cost'];
        }

        $totalCost = array_sum($categories);

        return collect($categories)->map(function ($cost, $category) use ($totalCost) {
            $percentage = $totalCost > 0 ? ($cost / $totalCost) * 100 : 0;

            return [
                'category' => $category,
                'cost' => round($cost, 4),
                'percentage' => round($percentage, 2),
            ];
        })->values()->all();
    }
}
