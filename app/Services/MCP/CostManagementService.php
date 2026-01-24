<?php

declare(strict_types=1);

namespace App\Services\MCP;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cost Management Service
 *
 * Tracks and manages AI operation costs across all providers:
 * - Real-time cost tracking per provider
 * - Budget management and alerts
 * - Cost optimization recommendations
 * - Usage analytics and reporting
 *
 * Requirements: 56.1, 56.3, 56.4
 */
class CostManagementService
{
    /**
     * Provider cost rates (per 1K tokens)
     */
    protected const COSTS = [
        'ollama' => [
            'input' => 0.0,
            'output' => 0.0,
        ],
        'bedrock_nova_lite' => [
            'input' => 0.00125,
            'output' => 0.00125,
        ],
        'bedrock_sonnet' => [
            'input' => 0.003,
            'output' => 0.015,
        ],
        'bedrock_opus' => [
            'input' => 0.005,
            'output' => 0.025,
        ],
        'bedrock_haiku' => [
            'input' => 0.001,
            'output' => 0.005,
        ],
    ];

    /**
     * Budget alert thresholds
     */
    protected const ALERT_THRESHOLD_WARNING = 0.75; // 75%

    protected const ALERT_THRESHOLD_CRITICAL = 0.90; // 90%

    protected const ALERT_THRESHOLD_EXCEEDED = 1.0; // 100%

    public function __construct(
        /** @phpstan-ignore-next-line property.onlyWritten - service available for future use */
        private readonly MCPClientService $mcpClient
    ) {}

    /**
     * Track AI operation cost
     *
     * @param  array<string, mixed>  $operation
     */
    public function trackCost(array $operation): void
    {
        $provider = is_string($operation['provider'] ?? null) ? $operation['provider'] : 'unknown';
        $model = is_string($operation['model'] ?? null) ? $operation['model'] : 'unknown';
        $inputTokens = is_numeric($operation['input_tokens'] ?? null) ? (int) $operation['input_tokens'] : 0;
        $outputTokens = is_numeric($operation['output_tokens'] ?? null) ? (int) $operation['output_tokens'] : 0;
        $executionTime = is_numeric($operation['execution_time'] ?? null) ? (float) $operation['execution_time'] : 0.0;

        // Calculate cost
        $cost = $this->calculateCost($provider, $model, $inputTokens, $outputTokens);

        // Calculate input and output costs separately
        $costKey = $this->mapModelToCostKey($provider, $model);
        $costs = self::COSTS[$costKey] ?? self::COSTS['bedrock_sonnet'];
        $inputCostRate = (float) $costs['input'];
        $outputCostRate = (float) $costs['output'];
        $inputCost = ($inputTokens / 1000) * $inputCostRate;
        $outputCost = ($outputTokens / 1000) * $outputCostRate;

        // Store cost record
        try {
            DB::table('ucp_ai_costs')->insert([
                'provider' => $provider,
                'model' => $model,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'total_tokens' => $inputTokens + $outputTokens,
                'input_cost' => round($inputCost, 6),
                'output_cost' => round($outputCost, 6),
                'total_cost' => $cost,
                'response_time' => $executionTime,
                'request_type' => is_string($operation['type'] ?? null) ? $operation['type'] : 'unknown',
                'user_id' => isset($operation['user_id']) ? $operation['user_id'] : null,
                'character_id' => isset($operation['character_id']) ? $operation['character_id'] : null,
                'request_summary' => isset($operation['summary']) && is_string($operation['summary']) ? $operation['summary'] : null,
                'cached' => (bool) ($operation['cached'] ?? false),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update cached totals
            $this->updateCachedTotals($provider, $cost);

            Log::debug('[CostManagement] Cost tracked', [
                'provider' => $provider,
                'model' => $model,
                'cost' => $cost,
                'tokens' => $inputTokens + $outputTokens,
            ]);
        } catch (\Exception $e) {
            Log::error('[CostManagement] Failed to track cost', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate cost for an operation
     */
    public function calculateCost(string $provider, string $model, int $inputTokens, int $outputTokens): float
    {
        // Ollama is always free
        if ($provider === 'ollama') {
            return 0.0;
        }

        // Map model to cost structure
        $costKey = $this->mapModelToCostKey($provider, $model);
        $costs = self::COSTS[$costKey] ?? self::COSTS['bedrock_sonnet'];

        $inputCost = ($inputTokens / 1000) * $costs['input'];
        $outputCost = ($outputTokens / 1000) * $costs['output'];

        return round($inputCost + $outputCost, 6);
    }

    /**
     * Map model name to cost key
     */
    protected function mapModelToCostKey(string $provider, string $model): string
    {
        if ($provider === 'ollama') {
            return 'ollama';
        }

        // Bedrock model mapping
        if (str_contains($model, 'nova-lite')) {
            return 'bedrock_nova_lite';
        }

        if (str_contains($model, 'sonnet')) {
            return 'bedrock_sonnet';
        }

        if (str_contains($model, 'opus')) {
            return 'bedrock_opus';
        }

        if (str_contains($model, 'haiku')) {
            return 'bedrock_haiku';
        }

        // Default to Sonnet pricing
        return 'bedrock_sonnet';
    }

    /**
     * Update cached cost totals
     */
    protected function updateCachedTotals(string $provider, float $cost): void
    {
        $cacheKey = "cost_total_{$provider}";
        $currentTotalRaw = Cache::get($cacheKey, 0.0);
        $currentTotal = is_numeric($currentTotalRaw) ? (float) $currentTotalRaw : 0.0;
        $newTotal = $currentTotal + $cost;

        Cache::put($cacheKey, $newTotal, 3600); // 1 hour

        // Update overall total
        $overallKey = 'cost_total_all';
        $overallTotalRaw = Cache::get($overallKey, 0.0);
        $overallTotal = is_numeric($overallTotalRaw) ? (float) $overallTotalRaw : 0.0;
        Cache::put($overallKey, $overallTotal + $cost, 3600);
    }

    /**
     * Get current budget status
     *
     * @return array{
     *     budget_limit: float,
     *     current_spending: float,
     *     remaining_budget: float,
     *     usage_percentage: float,
     *     is_exceeded: bool,
     *     alert_level: string,
     *     period: string
     * }
     */
    public function checkBudgetStatus(): array
    {
        $budgetLimit = $this->getBudgetLimit();
        $currentSpending = $this->getCurrentSpending();
        $remainingBudget = max(0, $budgetLimit - $currentSpending);
        $usagePercentage = $budgetLimit > 0 ? ($currentSpending / $budgetLimit) * 100 : 0;

        $alertLevel = $this->determineAlertLevel($usagePercentage);
        $isExceeded = $currentSpending >= $budgetLimit;

        return [
            'budget_limit' => $budgetLimit,
            'current_spending' => $currentSpending,
            'remaining_budget' => $remainingBudget,
            'usage_percentage' => round($usagePercentage, 2),
            'is_exceeded' => $isExceeded,
            'alert_level' => $alertLevel,
            'period' => 'monthly',
        ];
    }

    /**
     * Get budget limit from configuration
     */
    protected function getBudgetLimit(): float
    {
        $limit = config('ai.budget.monthly_limit', 100.0);

        return is_numeric($limit) ? (float) $limit : 100.0;
    }

    /**
     * Get current spending for the period
     */
    protected function getCurrentSpending(): float
    {
        // Check cache first
        $cacheKey = 'cost_total_all';
        $cached = Cache::get($cacheKey);

        if ($cached !== null && is_numeric($cached)) {
            return (float) $cached;
        }

        // Calculate from database
        $startOfMonth = now()->startOfMonth();

        $total = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_cost');

        $total = (float) $total;

        // Cache the result
        Cache::put($cacheKey, $total, 3600);

        return $total;
    }

    /**
     * Determine alert level based on usage percentage
     */
    protected function determineAlertLevel(float $usagePercentage): string
    {
        if ($usagePercentage >= self::ALERT_THRESHOLD_EXCEEDED * 100) {
            return 'exceeded';
        }

        if ($usagePercentage >= self::ALERT_THRESHOLD_CRITICAL * 100) {
            return 'critical';
        }

        if ($usagePercentage >= self::ALERT_THRESHOLD_WARNING * 100) {
            return 'warning';
        }

        return 'normal';
    }

    /**
     * Get cost breakdown by provider
     *
     * @return array<string, array{
     *     total_cost: float,
     *     total_tokens: int,
     *     request_count: int,
     *     avg_cost_per_request: float
     * }>
     */
    public function getCostBreakdownByProvider(int $days = 30): array
    {
        $since = now()->subDays($days);

        $breakdown = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', $since)
            ->select('provider')
            ->selectRaw('SUM(total_cost) as total_cost')
            ->selectRaw('SUM(total_tokens) as total_tokens')
            ->selectRaw('COUNT(*) as request_count')
            ->selectRaw('AVG(total_cost) as avg_cost_per_request')
            ->groupBy('provider')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->provider => [
                    'total_cost' => round((is_numeric($row->total_cost) ? (float) $row->total_cost : 0.0), 4),
                    'total_tokens' => (is_numeric($row->total_tokens) ? (int) $row->total_tokens : 0),
                    'request_count' => (is_numeric($row->request_count) ? (int) $row->request_count : 0),
                    'avg_cost_per_request' => round((is_numeric($row->avg_cost_per_request) ? (float) $row->avg_cost_per_request : 0.0), 6),
                ],
            ])
            ->toArray();

        /** @var array<string, array{total_cost: float, total_tokens: int, request_count: int, avg_cost_per_request: float}> $breakdown */
        return $breakdown;
    }

    /**
     * Get cost breakdown by model
     *
     * @return array<string, array{
     *     total_cost: float,
     *     total_tokens: int,
     *     request_count: int,
     *     avg_cost_per_request: float
     * }>
     */
    public function getCostBreakdownByModel(int $days = 30): array
    {
        $since = now()->subDays($days);

        $breakdown = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', $since)
            ->select('model')
            ->selectRaw('SUM(total_cost) as total_cost')
            ->selectRaw('SUM(total_tokens) as total_tokens')
            ->selectRaw('COUNT(*) as request_count')
            ->selectRaw('AVG(total_cost) as avg_cost_per_request')
            ->groupBy('model')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->model => [
                    'total_cost' => round((is_numeric($row->total_cost) ? (float) $row->total_cost : 0.0), 4),
                    'total_tokens' => (is_numeric($row->total_tokens) ? (int) $row->total_tokens : 0),
                    'request_count' => (is_numeric($row->request_count) ? (int) $row->request_count : 0),
                    'avg_cost_per_request' => round((is_numeric($row->avg_cost_per_request) ? (float) $row->avg_cost_per_request : 0.0), 6),
                ],
            ])
            ->toArray();

        /** @var array<string, array{total_cost: float, total_tokens: int, request_count: int, avg_cost_per_request: float}> $breakdown */
        return $breakdown;
    }

    /**
     * Get daily cost trend
     *
     * @return array<string, float>
     */
    public function getDailyCostTrend(int $days = 30): array
    {
        $since = now()->subDays($days);

        $trend = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(total_cost) as daily_cost')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->date => round((is_numeric($row->daily_cost) ? (float) $row->daily_cost : 0.0), 4),
            ])
            ->toArray();

        /** @var array<string, float> $trend */
        return $trend;
    }

    /**
     * Get cost optimization recommendations
     *
     * @return array<int, array{
     *     type: string,
     *     severity: string,
     *     message: string,
     *     potential_savings: float,
     *     action: string
     * }>
     */
    public function getOptimizationRecommendations(): array
    {
        $recommendations = [];
        $breakdown = $this->getCostBreakdownByProvider(30);

        // Check if Ollama could be used more
        $bedrockCost = ($breakdown['bedrock']['total_cost'] ?? 0);
        $ollamaCost = ($breakdown['ollama']['total_cost'] ?? 0);

        if ($bedrockCost > 0 && $ollamaCost == 0) {
            $recommendations[] = [
                'type' => 'provider_optimization',
                'severity' => 'high',
                'message' => 'Consider using local Ollama for simple queries to reduce costs',
                'potential_savings' => $bedrockCost * 0.3, // Estimate 30% could be handled by Ollama
                'action' => 'Enable Ollama for simple requests',
            ];
        }

        // Check for expensive model usage
        $modelBreakdown = $this->getCostBreakdownByModel(30);
        $opusCost = 0.0;

        foreach ($modelBreakdown as $model => $data) {
            if (str_contains($model, 'opus')) {
                $opusCost += (is_array($data) && isset($data['total_cost']) && is_numeric($data['total_cost']) ? (float) $data['total_cost'] : 0.0);
            }
        }

        if ($opusCost > 10.0) {
            $recommendations[] = [
                'type' => 'model_optimization',
                'severity' => 'medium',
                'message' => 'High usage of expensive Opus model detected',
                'potential_savings' => $opusCost * 0.5, // Estimate 50% could use Sonnet
                'action' => 'Review if Sonnet model could handle some Opus requests',
            ];
        }

        // Check budget status
        $budgetStatus = $this->checkBudgetStatus();

        if ($budgetStatus['alert_level'] === 'critical' || $budgetStatus['alert_level'] === 'exceeded') {
            $recommendations[] = [
                'type' => 'budget_alert',
                'severity' => 'critical',
                'message' => 'Budget limit approaching or exceeded',
                'potential_savings' => 0.0,
                'action' => 'Increase budget limit or reduce AI usage',
            ];
        }

        return $recommendations;
    }

    /**
     * Get usage analytics
     *
     * @return array{
     *     total_requests: int,
     *     total_cost: float,
     *     total_tokens: int,
     *     avg_cost_per_request: float,
     *     avg_tokens_per_request: int,
     *     by_provider: array<string, mixed>,
     *     by_model: array<string, mixed>,
     *     daily_trend: array<string, float>,
     *     budget_status: array<string, mixed>,
     *     recommendations: array<int, mixed>
     * }
     */
    public function getUsageAnalytics(int $days = 30): array
    {
        $since = now()->subDays($days);

        $totals = DB::table('ucp_ai_costs')
            ->where('created_at', '>=', $since)
            ->selectRaw('COUNT(*) as total_requests')
            ->selectRaw('SUM(total_cost) as total_cost')
            ->selectRaw('SUM(total_tokens) as total_tokens')
            ->selectRaw('AVG(total_cost) as avg_cost_per_request')
            ->selectRaw('AVG(total_tokens) as avg_tokens_per_request')
            ->first();

        return [
            'total_requests' => (int) ($totals->total_requests ?? 0),
            'total_cost' => round((float) ($totals->total_cost ?? 0), 4),
            'total_tokens' => (int) ($totals->total_tokens ?? 0),
            'avg_cost_per_request' => round((float) ($totals->avg_cost_per_request ?? 0), 6),
            'avg_tokens_per_request' => (int) ($totals->avg_tokens_per_request ?? 0),
            'by_provider' => $this->getCostBreakdownByProvider($days),
            'by_model' => $this->getCostBreakdownByModel($days),
            'daily_trend' => $this->getDailyCostTrend($days),
            'budget_status' => $this->checkBudgetStatus(),
            'recommendations' => $this->getOptimizationRecommendations(),
        ];
    }

    /**
     * Set budget limit
     */
    public function setBudgetLimit(float $limit): void
    {
        // Store in configuration or database
        config(['ai.budget.monthly_limit' => $limit], '');

        Log::info('[CostManagement] Budget limit updated', [
            'new_limit' => $limit,
        ]);
    }

    /**
     * Check if budget alert should be sent
     */
    public function shouldSendBudgetAlert(): bool
    {
        $budgetStatus = $this->checkBudgetStatus();
        $alertLevel = $budgetStatus['alert_level'];

        // Check if alert was already sent for this level
        $cacheKey = "budget_alert_sent_{$alertLevel}";

        if (Cache::has($cacheKey)) {
            return false;
        }

        // Send alert for warning, critical, or exceeded levels
        if (in_array($alertLevel, ['warning', 'critical', 'exceeded'])) {
            // Mark alert as sent (cache for 24 hours)
            Cache::put($cacheKey, true, 86400);

            return true;
        }

        return false;
    }

    /**
     * Get budget alert message
     */
    public function getBudgetAlertMessage(): string
    {
        $budgetStatus = $this->checkBudgetStatus();

        return match ($budgetStatus['alert_level']) {
            'warning' => sprintf(
                'Budget warning: %.1f%% of monthly budget used ($%.2f / $%.2f)',
                $budgetStatus['usage_percentage'],
                $budgetStatus['current_spending'],
                $budgetStatus['budget_limit']
            ),
            'critical' => sprintf(
                'Budget critical: %.1f%% of monthly budget used ($%.2f / $%.2f)',
                $budgetStatus['usage_percentage'],
                $budgetStatus['current_spending'],
                $budgetStatus['budget_limit']
            ),
            'exceeded' => sprintf(
                'Budget exceeded: $%.2f spent (limit: $%.2f)',
                $budgetStatus['current_spending'],
                $budgetStatus['budget_limit']
            ),
            default => 'Budget status normal',
        };
    }

    /**
     * Reset monthly costs (should be run at start of each month)
     */
    public function resetMonthlyCosts(): void
    {
        // Clear cached totals
        Cache::forget('cost_total_all');

        foreach (['ollama', 'bedrock', 'agent'] as $provider) {
            Cache::forget("cost_total_{$provider}");
        }

        // Clear alert flags
        foreach (['warning', 'critical', 'exceeded'] as $level) {
            Cache::forget("budget_alert_sent_{$level}");
        }

        Log::info('[CostManagement] Monthly costs reset');
    }
}
