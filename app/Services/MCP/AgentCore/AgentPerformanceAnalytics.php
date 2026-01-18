<?php

namespace App\Services\MCP\AgentCore;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Agent Performance Analytics
 *
 * Tracks and analyzes agent performance metrics including success rates,
 * response times, cost efficiency, and provides optimization recommendations.
 *
 * Requirements: 56.4, 59.3
 */
class AgentPerformanceAnalytics
{
    protected int $metricsRetentionDays = 30;

    /**
     * Initialize performance tracking for an agent
     */
    public function initializeAgent(string $agentId): void
    {
        $metrics = [
            'agent_id' => $agentId,
            'total_invocations' => 0,
            'successful_invocations' => 0,
            'failed_invocations' => 0,
            'total_execution_time' => 0.0,
            'total_tokens' => 0,
            'total_cost' => 0.0,
            'average_response_time' => 0.0,
            'success_rate' => 1.0,
            'cost_per_invocation' => 0.0,
            'initialized_at' => now()->toIso8601String(),
            'last_updated' => now()->toIso8601String(),
        ];

        Cache::put("agent_metrics_{$agentId}", $metrics, 86400 * $this->metricsRetentionDays);

        Log::info('[AgentAnalytics] Agent metrics initialized', ['agent_id' => $agentId]);
    }

    /**
     * Record an agent invocation
     *
     * @param  array{
     *     execution_time: float,
     *     tokens_used?: int,
     *     cost?: float,
     *     success: bool,
     *     error?: string
     * }  $invocationData
     */
    public function recordInvocation(string $agentId, array $invocationData): void
    {
        $metrics = $this->getAgentMetrics($agentId);

        // Update counters
        $metrics['total_invocations']++;
        if ($invocationData['success']) {
            $metrics['successful_invocations']++;
        } else {
            $metrics['failed_invocations']++;
        }

        // Update execution time
        $metrics['total_execution_time'] += $invocationData['execution_time'];
        $metrics['average_response_time'] = $metrics['total_execution_time'] / $metrics['total_invocations'];

        // Update tokens and cost
        if (isset($invocationData['tokens_used'])) {
            $metrics['total_tokens'] += $invocationData['tokens_used'];
        }
        if (isset($invocationData['cost'])) {
            $metrics['total_cost'] += $invocationData['cost'];
            $metrics['cost_per_invocation'] = $metrics['total_cost'] / $metrics['total_invocations'];
        }

        // Update success rate
        $metrics['success_rate'] = $metrics['successful_invocations'] / $metrics['total_invocations'];

        // Update timestamp
        $metrics['last_updated'] = now()->toIso8601String();

        // Save metrics
        Cache::put("agent_metrics_{$agentId}", $metrics, 86400 * $this->metricsRetentionDays);

        // Store invocation record
        $this->storeInvocationRecord($agentId, $invocationData);

        Log::info('[AgentAnalytics] Invocation recorded', [
            'agent_id' => $agentId,
            'success' => $invocationData['success'],
            'execution_time' => $invocationData['execution_time'],
        ]);
    }

    /**
     * Get agent metrics
     *
     * @return array{
     *     agent_id: string,
     *     total_invocations: int,
     *     successful_invocations: int,
     *     failed_invocations: int,
     *     total_execution_time: float,
     *     total_tokens: int,
     *     total_cost: float,
     *     average_response_time: float,
     *     success_rate: float,
     *     cost_per_invocation: float,
     *     initialized_at: string,
     *     last_updated: string
     * }
     */
    public function getAgentMetrics(string $agentId): array
    {
        /** @var array<string, mixed>|null $metrics */
        $metrics = Cache::get("agent_metrics_{$agentId}");

        if (! $metrics || ! is_array($metrics)) {
            // Initialize if not found
            $this->initializeAgent($agentId);
            $metrics = Cache::get("agent_metrics_{$agentId}");
        }

        return is_array($metrics) ? $metrics : $this->getDefaultMetrics($agentId);
    }

    /**
     * Get performance trends over time
     *
     * @return array{
     *     agent_id: string,
     *     period_days: int,
     *     trends: array{
     *         success_rate: array<string, float>,
     *         response_time: array<string, float>,
     *         cost: array<string, float>,
     *         invocations: array<string, int>
     *     },
     *     analysis: array<string, mixed>
     * }
     */
    public function getPerformanceTrends(string $agentId, int $days = 7): array
    {
        $invocations = $this->getInvocationHistory($agentId, $days);

        $trends = [
            'success_rate' => [],
            'response_time' => [],
            'cost' => [],
            'invocations' => [],
        ];

        // Group by date
        $groupedByDate = [];
        foreach ($invocations as $invocation) {
            $date = substr($invocation['timestamp'], 0, 10); // YYYY-MM-DD
            if (! isset($groupedByDate[$date])) {
                $groupedByDate[$date] = [];
            }
            $groupedByDate[$date][] = $invocation;
        }

        // Calculate daily metrics
        foreach ($groupedByDate as $date => $dayInvocations) {
            $successful = count(array_filter($dayInvocations, fn ($i) => $i['success']));
            $total = count($dayInvocations);

            $trends['success_rate'][$date] = $total > 0 ? $successful / $total : 0.0;
            $trends['invocations'][$date] = $total;

            $avgResponseTime = array_sum(array_column($dayInvocations, 'execution_time')) / $total;
            $trends['response_time'][$date] = $avgResponseTime;

            $totalCost = array_sum(array_column($dayInvocations, 'cost'));
            $trends['cost'][$date] = $totalCost;
        }

        // Analyze trends
        $analysis = $this->analyzeTrends($trends);

        return [
            'agent_id' => $agentId,
            'period_days' => $days,
            'trends' => $trends,
            'analysis' => $analysis,
        ];
    }

    /**
     * Get optimization recommendations
     *
     * @return array{
     *     agent_id: string,
     *     recommendations: array<int, array{
     *         priority: string,
     *         category: string,
     *         recommendation: string,
     *         expected_impact: string
     *     }>,
     *     overall_score: float
     * }
     */
    public function getOptimizationRecommendations(string $agentId): array
    {
        $metrics = $this->getAgentMetrics($agentId);
        $trends = $this->getPerformanceTrends($agentId, 7);

        $recommendations = [];

        // Check success rate
        if ($metrics['success_rate'] < 0.9) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'reliability',
                'recommendation' => 'Success rate is below 90%. Review agent instructions and error handling.',
                'expected_impact' => 'Improve success rate by 10-15%',
            ];
        }

        // Check response time
        if ($metrics['average_response_time'] > 5.0) {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'performance',
                'recommendation' => 'Average response time exceeds 5 seconds. Consider optimizing instructions or using a faster model.',
                'expected_impact' => 'Reduce response time by 30-40%',
            ];
        }

        // Check cost efficiency
        if ($metrics['cost_per_invocation'] > 0.01) {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'cost',
                'recommendation' => 'Cost per invocation is high. Consider using a more cost-effective model or optimizing prompts.',
                'expected_impact' => 'Reduce costs by 20-30%',
            ];
        }

        // Check invocation volume
        if ($metrics['total_invocations'] < 10) {
            $recommendations[] = [
                'priority' => 'low',
                'category' => 'usage',
                'recommendation' => 'Low invocation count. Agent may be underutilized or needs more testing.',
                'expected_impact' => 'Better understanding of agent capabilities',
            ];
        }

        // Check trend analysis
        if (isset($trends['analysis']['declining_success_rate']) && $trends['analysis']['declining_success_rate']) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'reliability',
                'recommendation' => 'Success rate is declining over time. Investigate recent changes or data quality issues.',
                'expected_impact' => 'Stabilize success rate',
            ];
        }

        if (isset($trends['analysis']['increasing_response_time']) && $trends['analysis']['increasing_response_time']) {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'performance',
                'recommendation' => 'Response time is increasing. Check for resource constraints or model performance degradation.',
                'expected_impact' => 'Maintain consistent response times',
            ];
        }

        // Calculate overall performance score
        $overallScore = $this->calculatePerformanceScore($metrics);

        if (empty($recommendations)) {
            $recommendations[] = [
                'priority' => 'low',
                'category' => 'general',
                'recommendation' => 'Agent is performing well. Continue monitoring for any changes.',
                'expected_impact' => 'Maintain current performance levels',
            ];
        }

        return [
            'agent_id' => $agentId,
            'recommendations' => $recommendations,
            'overall_score' => $overallScore,
        ];
    }

    /**
     * Compare multiple agents
     *
     * @param  array<int, string>  $agentIds
     * @return array{
     *     comparison: array<string, array<string, mixed>>,
     *     best_performer: array{
     *         agent_id: string,
     *         category: string,
     *         score: float
     *     },
     *     insights: array<int, string>
     * }
     */
    public function compareAgents(array $agentIds): array
    {
        $comparison = [];
        $scores = [];

        foreach ($agentIds as $agentId) {
            $metrics = $this->getAgentMetrics($agentId);
            $score = $this->calculatePerformanceScore($metrics);

            $comparison[$agentId] = [
                'metrics' => $metrics,
                'score' => $score,
            ];

            $scores[$agentId] = $score;
        }

        // Find best performer
        arsort($scores);
        $bestAgentId = array_key_first($scores);

        $bestPerformer = [
            'agent_id' => $bestAgentId,
            'category' => 'overall',
            'score' => $scores[$bestAgentId],
        ];

        // Generate insights
        $insights = $this->generateComparisonInsights($comparison);

        return [
            'comparison' => $comparison,
            'best_performer' => $bestPerformer,
            'insights' => $insights,
        ];
    }

    /**
     * Archive agent data
     */
    public function archiveAgentData(string $agentId): void
    {
        $metrics = $this->getAgentMetrics($agentId);
        $invocations = $this->getInvocationHistory($agentId, $this->metricsRetentionDays);

        // Store in long-term storage (database or file)
        $archiveData = [
            'agent_id' => $agentId,
            'metrics' => $metrics,
            'invocations' => $invocations,
            'archived_at' => now()->toIso8601String(),
        ];

        Cache::put("agent_archive_{$agentId}", $archiveData, 86400 * 365); // 1 year

        Log::info('[AgentAnalytics] Agent data archived', [
            'agent_id' => $agentId,
            'invocations_count' => count($invocations),
        ]);
    }

    /**
     * Store invocation record
     *
     * @param  array<string, mixed>  $invocationData
     */
    protected function storeInvocationRecord(string $agentId, array $invocationData): void
    {
        $record = [
            'agent_id' => $agentId,
            'execution_time' => $invocationData['execution_time'],
            'tokens_used' => $invocationData['tokens_used'] ?? 0,
            'cost' => $invocationData['cost'] ?? 0.0,
            'success' => $invocationData['success'],
            'error' => $invocationData['error'] ?? null,
            'timestamp' => now()->toIso8601String(),
        ];

        // Get existing records
        /** @var array<int, array<string, mixed>>|null $records */
        $records = Cache::get("agent_invocations_{$agentId}", []);
        if (! is_array($records)) {
            $records = [];
        }

        $records[] = $record;

        // Keep only recent records (last 1000)
        if (count($records) > 1000) {
            $records = array_slice($records, -1000);
        }

        Cache::put("agent_invocations_{$agentId}", $records, 86400 * $this->metricsRetentionDays);
    }

    /**
     * Get invocation history
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getInvocationHistory(string $agentId, int $days): array
    {
        /** @var array<int, array<string, mixed>>|null $records */
        $records = Cache::get("agent_invocations_{$agentId}", []);
        if (! is_array($records)) {
            return [];
        }

        // Filter by date range
        $cutoffDate = now()->subDays($days)->toIso8601String();

        return array_filter($records, fn ($record) => $record['timestamp'] >= $cutoffDate);
    }

    /**
     * Analyze trends
     *
     * @param  array<string, array<string, mixed>>  $trends
     * @return array<string, mixed>
     */
    protected function analyzeTrends(array $trends): array
    {
        $analysis = [];

        // Analyze success rate trend
        $successRates = array_values($trends['success_rate']);
        if (count($successRates) >= 2) {
            $firstHalf = array_slice($successRates, 0, (int) ceil(count($successRates) / 2));
            $secondHalf = array_slice($successRates, (int) floor(count($successRates) / 2));

            $avgFirst = array_sum($firstHalf) / count($firstHalf);
            $avgSecond = array_sum($secondHalf) / count($secondHalf);

            $analysis['declining_success_rate'] = $avgSecond < $avgFirst - 0.05;
            $analysis['improving_success_rate'] = $avgSecond > $avgFirst + 0.05;
        }

        // Analyze response time trend
        $responseTimes = array_values($trends['response_time']);
        if (count($responseTimes) >= 2) {
            $firstHalf = array_slice($responseTimes, 0, (int) ceil(count($responseTimes) / 2));
            $secondHalf = array_slice($responseTimes, (int) floor(count($responseTimes) / 2));

            $avgFirst = array_sum($firstHalf) / count($firstHalf);
            $avgSecond = array_sum($secondHalf) / count($secondHalf);

            $analysis['increasing_response_time'] = $avgSecond > $avgFirst * 1.2;
            $analysis['improving_response_time'] = $avgSecond < $avgFirst * 0.8;
        }

        return $analysis;
    }

    /**
     * Calculate performance score
     *
     * @param  array<string, mixed>  $metrics
     */
    protected function calculatePerformanceScore(array $metrics): float
    {
        // Weighted scoring
        $successRateScore = ($metrics['success_rate'] ?? 0.0) * 40; // 40% weight
        $responseTimeScore = min(1.0, 5.0 / max(0.1, $metrics['average_response_time'] ?? 1.0)) * 30; // 30% weight
        $costEfficiencyScore = min(1.0, 0.01 / max(0.001, $metrics['cost_per_invocation'] ?? 0.01)) * 20; // 20% weight
        $usageScore = min(1.0, ($metrics['total_invocations'] ?? 0) / 100) * 10; // 10% weight

        return round($successRateScore + $responseTimeScore + $costEfficiencyScore + $usageScore, 2);
    }

    /**
     * Generate comparison insights
     *
     * @param  array<string, array<string, mixed>>  $comparison
     * @return array<int, string>
     */
    protected function generateComparisonInsights(array $comparison): array
    {
        $insights = [];

        // Find agent with best success rate
        $bestSuccessRate = 0.0;
        $bestSuccessAgent = '';
        foreach ($comparison as $agentId => $data) {
            $successRate = $data['metrics']['success_rate'] ?? 0.0;
            if ($successRate > $bestSuccessRate) {
                $bestSuccessRate = $successRate;
                $bestSuccessAgent = $agentId;
            }
        }

        if ($bestSuccessAgent) {
            $insights[] = "Agent {$bestSuccessAgent} has the highest success rate at ".round($bestSuccessRate * 100, 1).'%';
        }

        // Find most cost-effective agent
        $lowestCost = PHP_FLOAT_MAX;
        $cheapestAgent = '';
        foreach ($comparison as $agentId => $data) {
            $cost = $data['metrics']['cost_per_invocation'] ?? PHP_FLOAT_MAX;
            if ($cost < $lowestCost) {
                $lowestCost = $cost;
                $cheapestAgent = $agentId;
            }
        }

        if ($cheapestAgent) {
            $insights[] = "Agent {$cheapestAgent} is the most cost-effective at $".round($lowestCost, 4).' per invocation';
        }

        return $insights;
    }

    /**
     * Get default metrics
     *
     * @return array<string, mixed>
     */
    protected function getDefaultMetrics(string $agentId): array
    {
        return [
            'agent_id' => $agentId,
            'total_invocations' => 0,
            'successful_invocations' => 0,
            'failed_invocations' => 0,
            'total_execution_time' => 0.0,
            'total_tokens' => 0,
            'total_cost' => 0.0,
            'average_response_time' => 0.0,
            'success_rate' => 1.0,
            'cost_per_invocation' => 0.0,
            'initialized_at' => now()->toIso8601String(),
            'last_updated' => now()->toIso8601String(),
        ];
    }
}
