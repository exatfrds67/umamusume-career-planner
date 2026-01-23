<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Performance Regression Detection Service
 *
 * Provides automated performance regression detection including:
 * - Baseline calculation from historical data
 * - Statistical deviation analysis
 * - Regression reporting and tracking
 * - Trend analysis for early warning
 *
 * @see Requirements: 54.2, 59.1
 * @see Task: 6.1.5 Comprehensive performance monitoring setup
 */
class PerformanceRegressionService
{
    /**
     * Regression data prefix
     */
    protected const REGRESSION_PREFIX = 'apm:regression:';

    /**
     * Baseline data prefix
     */
    protected const BASELINE_PREFIX = 'apm:baseline:';

    /**
     * Regression status constants
     */
    public const STATUS_DETECTED = 'detected';

    public const STATUS_INVESTIGATING = 'investigating';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_FALSE_POSITIVE = 'false_positive';

    /**
     * Create a new PerformanceRegressionService instance.
     */
    public function __construct(
        protected readonly ApmService $apmService
    ) {}

    /**
     * Check for performance regressions.
     *
     * @return array{checked: int, detected: int, regressions: array<int, array<string, mixed>>}
     */
    public function checkRegressions(): array
        if (! config('apm.regression.enabled', true)) {
            return ['checked' => 0, 'detected' => 0, 'regressions' => []];
        }

        $checked = 0;
        $detected = 0;
        $regressions = [];

        // Check response time regression
        $checked = ($checked ?? 0) + 1;
        $responseTimeRegression = $this->checkMetricRegression('response_time');
        if ($responseTimeRegression !== null) {
            $detected = ($detected ?? 0) + 1;
            $regressions[] = $responseTimeRegression;
        }

        // Check error rate regression
        $checked = ($checked ?? 0) + 1;
        $errorRateRegression = $this->checkMetricRegression('error_rate');
        if ($errorRateRegression !== null) {
            $detected = ($detected ?? 0) + 1;
            $regressions[] = $errorRateRegression;
        }

        // Check throughput regression
        $checked = ($checked ?? 0) + 1;
        $throughputRegression = $this->checkMetricRegression('throughput');
        if ($throughputRegression !== null) {
            $detected = ($detected ?? 0) + 1;
            $regressions[] = $throughputRegression;
        }

        // Check memory usage regression
        $checked = ($checked ?? 0) + 1;
        $memoryRegression = $this->checkMetricRegression('memory_usage');
        if ($memoryRegression !== null) {
            $detected = ($detected ?? 0) + 1;
            $regressions[] = $memoryRegression;
        }

        return [
            'checked' => $checked,
            'detected' => $detected,
            'regressions' => $regressions,
        ];
    }

    /**
     * Calculate and store baseline for a metric.
     *
     * @return array{metric: string, baseline: float, std_dev: float, data_points: int, calculated_at: string}|null
     */
    public function calculateBaseline(string $metric): ?array
    {
        $baselinePeriod = (int) config('apm.regression.baseline_period_hours', 24);
        $minDataPoints = (int) config('apm.regression.min_data_points', 100);

        // Get historical data
        $data = $this->getHistoricalData($metric, $baselinePeriod);

        if (\count($data) < $minDataPoints) {
            Log::info('[RegressionDetection] Insufficient data points for baseline', [
                'metric' => $metric,
                'data_points' => \count($data),
                'required' => $minDataPoints,
            ]);

            return null;
        }

        // Calculate statistics
        $values = array_column($data, 'value');
        $mean = array_sum($values) / \count($values);
        $stdDev = $this->calculateStandardDeviation($values, $mean);

        $baseline = [
            'metric' => $metric,
            'baseline' => round($mean, 4),
            'std_dev' => round($stdDev, 4),
            'data_points' => \count($data),
            'calculated_at' => now()->toIso8601String(),
        ];

        // Store baseline
        Cache::put(self::BASELINE_PREFIX.$metric, $baseline, 86400); // 24 hours

        Log::info('[RegressionDetection] Baseline calculated', $baseline);

        return $baseline;
    }

    /**
     * Get baseline for a metric.
     *
     * @return array{metric: string, baseline: float, std_dev: float, data_points: int, calculated_at: string}|null
     */
    public function getBaseline(string $metric): ?array
    {
        $baseline = Cache::get(self::BASELINE_PREFIX.$metric);

        if ($baseline === null) {
            // Try to calculate baseline
            return $this->calculateBaseline($metric);
        }

        return $baseline;
    }

    /**
     * Get all regressions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRegressions(): array
        $regressions = Cache::get(self::REGRESSION_PREFIX.'list', []);

        // Sort by detected_at descending
        usort($regressions, fn ($a, $b) => strtotime($b['detected_at']) <=> strtotime($a['detected_at']));

        return \array_slice($regressions, 0, $limit);
    }

    /**
     * Get active (unresolved) regressions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActiveRegressions(): array
        $regressions = $this->getRegressions();

        return array_values(array_filter(
            $regressions,
            fn ($r) => $r['status'] === self::STATUS_DETECTED || $r['status'] === self::STATUS_INVESTIGATING
        ));
    }

    /**
     * Update regression status.
     */
    public function updateRegressionStatus(string $regressionId, string $status, ?string $notes = null): bool
    {
        $regressions = Cache::get(self::REGRESSION_PREFIX.'list', []);

        foreach ($regressions as &$regression) {
            if ($regression['id'] === $regressionId) {
                $regression['status'] = $status;
                $regression['updated_at'] = now()->toIso8601String();

                if ($notes !== null) {
                    $regression['notes'] = $notes;
                }

                if ($status === self::STATUS_RESOLVED) {
                    $regression['resolved_at'] = now()->toIso8601String();
                }

                $this->saveRegressions($regressions);

                return true;
            }
        }

        return false;
    }

    /**
     * Get regression statistics.
     *
     * @return array{total: int, active: int, resolved: int, by_metric: array<string, int>, avg_deviation: float}
     */
    public function getRegressionStatistics(): array
        $regressions = $this->getRegressions();

        $active = 0;
        $resolved = 0;
        $byMetric = [];
        $totalDeviation = 0.0;

        foreach ($regressions as $regression) {
            if ($regression['status'] === self::STATUS_DETECTED || $regression['status'] === self::STATUS_INVESTIGATING) {
                $active = ($active ?? 0) + 1;
            } elseif ($regression['status'] === self::STATUS_RESOLVED) {
                $resolved = ($resolved ?? 0) + 1;
            }

            $byMetric[$regression['metric']] = ($byMetric[$regression['metric']] ?? 0) + 1;
            $totalDeviation = ($totalDeviation ?? 0) + abs($regression['deviation_percent']);
        }

        return [
            'total' => \count($regressions),
            'active' => $active,
            'resolved' => $resolved,
            'by_metric' => $byMetric,
            'avg_deviation' => \count($regressions) > 0 ? round($totalDeviation / \count($regressions), 2) : 0,
        ];
    }

    /**
     * Generate regression report.
     *
     * @return array{
     *     generated_at: string,
     *     period: string,
     *     summary: array<string, mixed>,
     *     active_regressions: array<int, array<string, mixed>>,
     *     baselines: array<string, array<string, mixed>>,
     *     recommendations: array<int, string>
     * }
     */
    public function generateReport(): array
        $activeRegressions = $this->getActiveRegressions();
        $statistics = $this->getRegressionStatistics();

        $baselines = [];
        $metrics = ['response_time', 'error_rate', 'throughput', 'memory_usage'];

        foreach ($metrics as $metric) {
            $baseline = $this->getBaseline($metric);
            if ($baseline !== null) {
                $baselines[$metric] = $baseline;
            }
        }

        $recommendations = $this->generateRecommendations($activeRegressions);

        return [
            'generated_at' => now()->toIso8601String(),
            'period' => config('apm.regression.baseline_period_hours', 24).' hours',
            'summary' => $statistics,
            'active_regressions' => $activeRegressions,
            'baselines' => $baselines,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Clear all regression data.
     */
    public function clearData(): void
    {
        Cache::forget(self::REGRESSION_PREFIX.'list');

        $metrics = ['response_time', 'error_rate', 'throughput', 'memory_usage'];
        foreach ($metrics as $metric) {
            Cache::forget(self::BASELINE_PREFIX.$metric);
        }

        Log::info('[RegressionDetection] All regression data cleared');
    }

    /**
     * Check for regression in a specific metric.
     *
     * @return array<string, mixed>|null
     */
    protected function checkMetricRegression(string $metric): ?array
    {
        $baseline = $this->getBaseline($metric);

        if ($baseline === null) {
            return null;
        }

        $currentValue = $this->getCurrentMetricValue($metric);

        if ($currentValue === null) {
            return null;
        }

        $threshold = $this->getRegressionThreshold($metric);
        $deviation = $this->calculateDeviation($currentValue, $baseline['baseline']);

        // For throughput, we check for decrease (negative deviation)
        // For other metrics, we check for increase (positive deviation)
        $isRegression = $metric === 'throughput'
            ? $deviation <= -$threshold
            : $deviation >= $threshold;

        if ($isRegression) {
            return $this->createRegression($metric, $baseline['baseline'], $currentValue, $deviation);
        }

        return null;
    }

    /**
     * Create a regression record.
     *
     * @return array<string, mixed>
     */
    protected function createRegression(): array
        $regression = [
            'id' => uniqid('reg_', true),
            'metric' => $metric,
            'baseline' => round($baseline, 4),
            'current' => round($current, 4),
            'deviation_percent' => round($deviation, 2),
            'detected_at' => now()->toIso8601String(),
            'status' => self::STATUS_DETECTED,
            'notes' => null,
        ];

        // Store regression
        $this->storeRegression($regression);

        // Also update APM regressions cache for dashboard
        $regressions = Cache::get(self::REGRESSION_PREFIX.'list', []);
        Cache::put('apm:regressions', $regressions, 604800);

        Log::warning('[RegressionDetection] Performance regression detected', $regression);

        return $regression;
    }

    /**
     * Store a regression.
     *
     * @param  array<string, mixed>  $regression
     */
    protected function storeRegression(array $regression): void
    {
        $regressions = Cache::get(self::REGRESSION_PREFIX.'list', []);
        $regressions[] = $regression;

        // Keep last 500 regressions
        if (\count($regressions) > 500) {
            $regressions = \array_slice($regressions, -500);
        }

        Cache::put(self::REGRESSION_PREFIX.'list', $regressions, 2592000); // 30 days
    }

    /**
     * Save regressions to cache.
     *
     * @param  array<int, array<string, mixed>>  $regressions
     */
    protected function saveRegressions(array $regressions): void
    {
        Cache::put(self::REGRESSION_PREFIX.'list', $regressions, 2592000);
        Cache::put('apm:regressions', $regressions, 2592000);
    }

    /**
     * Get current value for a metric.
     */
    protected function getCurrentMetricValue(string $metric): ?float
    {
        $healthScore = $this->apmService->calculateHealthScore();
        $components = $healthScore['components'];

        return match ($metric) {
            'response_time' => $components['response_time']['value'] ?? null,
            'error_rate' => $components['error_rate']['value'] ?? null,
            'throughput' => $components['throughput']['value'] ?? null,
            'memory_usage' => $components['memory_usage']['value'] ?? null,
            default => null,
        };
    }

    /**
     * Get regression threshold for a metric.
     */
    protected function getRegressionThreshold(string $metric): float
    {
        $thresholds = config('apm.regression.thresholds', []);

        return (float) ($thresholds[$metric] ?? 25);
    }

    /**
     * Calculate percentage deviation from baseline.
     */
    protected function calculateDeviation(float $current, float $baseline): float
    {
        if ($baseline == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $baseline) / $baseline) * 100;
    }

    /**
     * Calculate standard deviation.
     *
     * @param  array<float>  $values
     */
    protected function calculateStandardDeviation(array $values, float $mean): float
    {
        $count = \count($values);

        if ($count < 2) {
            return 0.0;
        }

        $sumSquaredDiff = 0.0;
        foreach ($values as $value) {
            $sumSquaredDiff = ($sumSquaredDiff ?? 0) + ($value - $mean) ** 2;
        }

        return sqrt($sumSquaredDiff / ($count - 1));
    }

    /**
     * Get historical data for a metric.
     *
     * @return array<int, array{value: float, recorded_at: string}>
     */
    protected function getHistoricalData(): array
        $data = [];

        for ($i = $hours - 1; $i >= 0; $i--) {
            $hourKey = "apm:metrics:{$metric}:".date('Y-m-d-H', strtotime("-{$i} hours"));
            $hourData = Cache::get($hourKey, []);
            $data = array_merge($data, $hourData);
        }

        return $data;
    }

    /**
     * Generate recommendations based on active regressions.
     *
     * @param  array<int, array<string, mixed>>  $regressions
     * @return array<int, string>
     */
    protected function generateRecommendations(): array
        $recommendations = [];

        foreach ($regressions as $regression) {
            $metric = $regression['metric'];
            $deviation = $regression['deviation_percent'];

            $recommendations[] = match ($metric) {
                'response_time' => \sprintf(
                    'Response time increased by %.1f%%. Consider: optimizing database queries, adding caching, or scaling resources.',
                    $deviation
                ),
                'error_rate' => \sprintf(
                    'Error rate increased by %.1f%%. Review recent deployments, check error logs, and verify external service health.',
                    $deviation
                ),
                'throughput' => \sprintf(
                    'Throughput decreased by %.1f%%. Check for bottlenecks, verify load balancer health, and review resource utilization.',
                    abs($deviation)
                ),
                'memory_usage' => \sprintf(
                    'Memory usage increased by %.1f%%. Check for memory leaks, optimize data structures, and consider increasing memory limits.',
                    $deviation
                ),
                default => \sprintf('Performance regression detected in %s (%.1f%% deviation).', $metric, $deviation),
            };
        }

        if (empty($recommendations)) {
            $recommendations[] = 'No active performance regressions detected. System is performing within baseline parameters.';
        }

        return $recommendations;
    }
}
