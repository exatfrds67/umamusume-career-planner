<?php

namespace App\Services\AI;

use App\Services\MCP\Tools\AWSPricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cost Tracking Service
 *
 * Tracks AI service costs, manages budgets, and provides cost optimization recommendations.
 * Integrates with AWSPricingService for real-time pricing data.
 *
 * Requirements: 56.4, 57.5
 */
class CostTrackingService
{
    protected AWSPricingService $awsPricing;

    protected float $defaultBudgetLimit;

    public function __construct(AWSPricingService $awsPricing)
    {
        $this->awsPricing = $awsPricing;
        $this->defaultBudgetLimit = 100.0; // $100/month default
    }

    /**
     * Track AI request cost
     *
     * @param  array<string, mixed>  $metadata
     */
    public function trackCost(
        string $provider,
        string $model,
        int $inputTokens,
        int $outputTokens,
        float $cost,
        ?int $userId = null,
        ?int $characterId = null,
        ?string $requestType = null,
        array $metadata = []
    ): void {
        try {
            DB::table('ucp_ai_costs')->insert([
                'user_id' => $userId,
                'character_id' => $characterId,
                'provider' => $provider,
                'model' => $model,
                'request_type' => $requestType,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'total_tokens' => $inputTokens + $outputTokens,
                'input_cost' => $this->calculateInputCost($model, $inputTokens),
                'output_cost' => $this->calculateOutputCost($model, $outputTokens),
                'total_cost' => $cost,
                'response_time' => is_numeric((is_array($metadata) && isset($metadata['response_time']) ? $metadata['response_time'] : null)) ? (float) ($metadata['response_time']) : null,
                'cached' => (bool) ($metadata['cached'] ?? false),
                'request_summary' => isset($metadata['summary']) && is_string($metadata['summary']) ? $metadata['summary'] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('[CostTracking] Failed to track cost', [
                'error' => $e->getMessage(),
                'provider' => $provider,
                'model' => $model,
            ]);
        }
    }

    /**
     * Get total cost for a period
     */
    public function getTotalCost(string $period = '30d', ?int $userId = null): float
    {
        $hours = $this->periodToHours($period);
        $since = now()->subHours($hours);

        $query = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', $since);

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        $sum = $query->sum('total_cost');

        return is_numeric($sum) ? (float) $sum : 0.0;
    }

    /**
     * Get cost breakdown by provider
     *
     * @return array<string, float>
     */
    public function getCostByProvider(string $period = '24h', ?int $userId = null): array
    {
        $hours = $this->periodToHours($period);
        $since = now()->subHours($hours);

        $query = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', $since)
            ->select('provider', DB::raw('SUM(total_cost) as total'))
            ->groupBy('provider');

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        return $query->get()
            ->pluck('total', 'provider')
            ->map(fn ($cost) => is_numeric($cost) ? round((float) $cost, 6) : 0.0)
            ->toArray();
    }

    /**
     * Get cost breakdown by model
     *
     * @return array<string, float>
     */
    public function getCostByModel(string $period = '24h', ?int $userId = null): array
    {
        $hours = $this->periodToHours($period);
        $since = now()->subHours($hours);

        $query = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', $since)
            ->select('model', DB::raw('SUM(total_cost) as total'))
            ->groupBy('model');

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        return $query->get()
            ->pluck('total', 'model')
            ->map(fn ($cost) => is_numeric($cost) ? round((float) $cost, 6) : 0.0)
            ->toArray();
    }

    /**
     * Get cost breakdown by request type
     *
     * @return array<string, float>
     */
    public function getCostByRequestType(): array
    {
        $hours = $this->periodToHours($period);
        $since = now()->subHours($hours);

        $query = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', $since)
            ->whereNotNull('request_type')
            ->select('request_type', DB::raw('SUM(total_cost) as total'))
            ->groupBy('request_type');

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        return $query->get()
            ->pluck('total', 'request_type')
            ->map(fn ($cost) => is_numeric($cost) ? round((float) $cost, 6) : 0.0)
            ->toArray();
    }

    /**
     * Get daily cost trend
     *
     * @return array<int, array{date: string, cost: float}>
     */
    public function getDailyCostTrend(int $days = 30, ?int $userId = null): array
    {
        $since = now()->subDays($days);

        $query = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', $since)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_cost) as cost'))
            ->groupBy('date')
            ->orderBy('date');

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        return $query->get()
            ->map(fn ($record) => [
                'date' => $record->date,
                'cost' => is_numeric($record->cost) ? round((float) $record->cost, 6) : 0.0,
            ])
            ->toArray();
    }

    /**
     * Get budget status
     *
     * @return array{
     *     budget_limit: float,
     *     current_spend: float,
     *     projected_monthly: float,
     *     remaining_budget: float,
     *     budget_utilization: float,
     *     days_remaining: int,
     *     daily_budget_remaining: float,
     *     status: string,
     *     alert_level: string
     * }
     */
    public function getBudgetStatus(?int $userId = null, ?float $customBudget = null): array
    {
        $budgetLimit = $customBudget ?? $this->defaultBudgetLimit;

        // Get current month's spend
        $currentSpend = $this->getTotalCost('30d', $userId);

        // Calculate daily average and project monthly
        $dailyAverage = $this->getTotalCost('1d', $userId);
        $projectedMonthly = $dailyAverage * 30;

        $remainingBudget = $budgetLimit - $currentSpend;
        $budgetUtilization = $budgetLimit > 0 ? ($currentSpend / $budgetLimit) * 100 : 0;

        // Calculate days remaining in month
        $daysRemaining = now()->daysInMonth - now()->day;
        $dailyBudgetRemaining = $daysRemaining > 0 ? $remainingBudget / $daysRemaining : 0;

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
            'projected_monthly' => round($projectedMonthly, 4),
            'remaining_budget' => round($remainingBudget, 4),
            'budget_utilization' => round($budgetUtilization, 2),
            'days_remaining' => $daysRemaining,
            'daily_budget_remaining' => round($dailyBudgetRemaining, 4),
            'status' => $status,
            'alert_level' => $alertLevel,
        ];
    }

    /**
     * Get cost optimization recommendations
     *
     * @return array{
     *     current_cost: float,
     *     optimized_cost: float,
     *     potential_savings: float,
     *     recommendations: array<int, array{
     *         type: string,
     *         description: string,
     *         impact: string,
     *         estimated_savings: float
     *     }>
     * }
     */
    public function getCostOptimizationRecommendations(?int $userId = null): array
    {
        $currentCost = $this->getTotalCost('30d', $userId);
        $costByProvider = $this->getCostByProvider('30d', $userId);
        $costByModel = $this->getCostByModel('30d', $userId);

        $recommendations = [];
        $potentialSavings = 0.0;

        // Recommendation 1: Use local Ollama for simple requests
        $bedrockCost = $costByProvider['bedrock'] ?? 0.0;
        if ($bedrockCost > 0) {
            $estimatedSavings = $bedrockCost * 0.4; // 40% of Bedrock costs could be local
            $potentialSavings = ($potentialSavings ?? 0) + $estimatedSavings;

            $recommendations[] = [
                'type' => 'local_processing',
                'description' => 'Use local Ollama models for simple requests instead of Bedrock',
                'impact' => 'high',
                'estimated_savings' => round($estimatedSavings, 4),
            ];
        }

        // Recommendation 2: Use cheaper models for simple tasks
        $sonnetCost = $costByModel['claude-3-5-sonnet'] ?? 0.0;
        if ($sonnetCost > 0) {
            $estimatedSavings = $sonnetCost * 0.3; // 30% could use Haiku instead
            $potentialSavings = ($potentialSavings ?? 0) + $estimatedSavings;

            $recommendations[] = [
                'type' => 'model_selection',
                'description' => 'Use Claude 3.5 Haiku for simple requests instead of Sonnet',
                'impact' => 'medium',
                'estimated_savings' => round($estimatedSavings, 4),
            ];
        }

        // Recommendation 3: Implement response caching
        $cachedPercentage = $this->getCachedRequestPercentage($userId);
        if ($cachedPercentage < 20) {
            $estimatedSavings = $currentCost * 0.2; // 20% savings from caching
            $potentialSavings = ($potentialSavings ?? 0) + $estimatedSavings;

            $recommendations[] = [
                'type' => 'caching',
                'description' => 'Implement response caching for repeated queries',
                'impact' => 'medium',
                'estimated_savings' => round($estimatedSavings, 4),
            ];
        }

        // Recommendation 4: Optimize token usage
        $avgTokensPerRequest = $this->getAverageTokensPerRequest($userId);
        if ($avgTokensPerRequest > 2000) {
            $estimatedSavings = $currentCost * 0.15; // 15% savings from optimization
            $potentialSavings = ($potentialSavings ?? 0) + $estimatedSavings;

            $recommendations[] = [
                'type' => 'token_optimization',
                'description' => 'Optimize prompts to reduce token usage',
                'impact' => 'low',
                'estimated_savings' => round($estimatedSavings, 4),
            ];
        }

        $optimizedCost = $currentCost - $potentialSavings;

        return [
            'current_cost' => round($currentCost, 4),
            'optimized_cost' => round(max(0, $optimizedCost), 4),
            'potential_savings' => round($potentialSavings, 4),
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Calculate input cost for a model
     */
    protected function calculateInputCost(string $model, int $tokens): float
    {
        $pricing = $this->awsPricing->getBedrockPricing([$model]);

        if (! isset($pricing['models'][$model])) {
            return 0.0;
        }

        $modelPricing = $pricing['models'][$model];
        if (! \is_array($modelPricing) || ! isset($modelPricing['input_price'], $modelPricing['unit'])) {
            return 0.0;
        }

        $inputPrice = is_numeric($modelPricing['input_price']) ? (float) $modelPricing['input_price'] : 0.0;

        // Check if pricing is per 1K or 1M tokens
        if ($modelPricing['unit'] === 'per 1K tokens') {
            return ($tokens / 1000) * $inputPrice;
        }

        return ($tokens / 1000000) * $inputPrice;
    }

    /**
     * Calculate output cost for a model
     */
    protected function calculateOutputCost(string $model, int $tokens): float
    {
        $pricing = $this->awsPricing->getBedrockPricing([$model]);

        if (! isset($pricing['models'][$model])) {
            return 0.0;
        }

        $modelPricing = $pricing['models'][$model];
        if (! \is_array($modelPricing) || ! isset($modelPricing['output_price'], $modelPricing['unit'])) {
            return 0.0;
        }

        $outputPrice = is_numeric($modelPricing['output_price']) ? (float) $modelPricing['output_price'] : 0.0;

        // Check if pricing is per 1K or 1M tokens
        if ($modelPricing['unit'] === 'per 1K tokens') {
            return ($tokens / 1000) * $outputPrice;
        }

        return ($tokens / 1000000) * $outputPrice;
    }

    /**
     * Get cached request percentage
     */
    protected function getCachedRequestPercentage(?int $userId = null): float
    {
        $query = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', now()->subDays(30));

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        $total = $query->count();
        $cached = (clone $query)->where('cached', '=', true)->count();

        return $total > 0 ? ($cached / $total) * 100 : 0;
    }

    /**
     * Get average tokens per request
     */
    protected function getAverageTokensPerRequest(?int $userId = null): float
    {
        $query = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', now()->subDays(30));

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        $avg = $query->avg('total_tokens');

        return is_numeric($avg) ? (float) $avg : 0.0;
    }

    /**
     * Convert period string to hours
     */
    protected function periodToHours(string $period): int
    {
        return match ($period) {
            '1h' => 1,
            '1d' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 720,
        };
    }
}
