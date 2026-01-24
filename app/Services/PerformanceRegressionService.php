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
    {
        if (! config('apm.regression.enabled', true)) {
            return ['checked' => 0, 'detected' => 0, 'regressions' => []];
        }

        $checked = 0;
        $detected = 0;
        $regressions = [];

        // Check response time regression
        $checked++;
        $responseTimeRegression = $this->checkMetricRegression('response_time');
        if ($responseTimeRegression !== null) {
            $detected++;
            $regressions[] = $responseTimeRegression;
        }

        // Check error rate regression
        $checked++;
        $errorRateRegression = $this->checkMetricRegression('error_rate');
        if ($errorRateRegression !== null) {
            $detected++;
            $regressions[] = $errorRateRegression;
        }

        // Check throughput regression
        $checked++;
        $throughputRegression = $this->checkMetricRegression('throughput');
        if ($throughputRegression !== null) {
            $detected++;
            $regressions[] = $throughputRegression;
        }

        // Check memory usage regression
        $checked++;
        $memoryRegression = $this->checkMetricRegression('memory_usage');
        if ($memoryRegression !== null) {
            $detected++;
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
        $baselinePeriodConfig = config('apm.regression.baseline_period_hours', 24);
        $minDataPointsConfig = config('apm.regression.min_data_points', 100);
        $baselinePeriod = is_numeric($baselinePeriodConfig) ? (int) $baselinePeriodConfig : 24;
        $minDataPoints = is_numeric($minDataPointsConfig) ? (int) $minDataPointsConfig : 100;

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

        if (! is_array($baseline)) {
            return null;
        }

        /** @var array{metric: string, baseline: float, std_dev: float, data_points: int, calculated_at: string} $baseline */
        return $baseline;
    }

    /**
     * Get all regressions.
     *
     * @param  int  $limit  Maximum number of regressions to return
     * @return array<int, array<string, mixed>>
     */
    public function getRegressions(int $limit = 100): array
    {
        $regressions = Cache::get(self::REGRESSION_PREFIX.'list', []);

        if (! is_array($regressions)) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $regressions */

        // Sort by detected_at descending
        usort($regressions, function (array $a, array $b): int {
            $aTime = is_string($a['detected_at'] ?? null) ? strtotime($a['detected_at']) : 0;
            $bTime = is_string($b['detected_at'] ?? null) ? strtotime($b['detected_at']) : 0;

            return ($bTime ?: 0) <=> ($aTime ?: 0);
        });

        return \array_slice($regressions, 0, $limit);
    }

    /**
     * Get active (unresolved) regressions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActiveRegressions(): array
    {
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
        $regressionsCache = Cache::get(self::REGRESSION_PREFIX.'list', []);

        if (! is_array($regressionsCache)) {
            return false;
        }

        /** @var array<int, array<string, mixed>> $regressions */
        $regressions = $regressionsCache;

        foreach ($regressions as &$regression) {
            if (! is_array($regression)) {
                continue;
            }

            if (($regression['id'] ?? '') === $regressionId) {
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
    {
        $regressions = $this->getRegressions();

        $active = 0;
        $resolved = 0;
        $byMetric = [];
        $totalDeviation = 0.0;

        foreach ($regressions as $regression) {
            /** @var array{status: string, metric: string, deviation_percent: float} $regression */
            $status = is_string($regression['status'] ?? null) ? $regression['status'] : '';
            $metric = is_string($regression['metric'] ?? null) ? $regression['metric'] : '';
            $deviation = is_numeric($regression['deviation_percent'] ?? null) ? (float) $regression['deviation_percent'] : 0.0;

            if ($status === self::STATUS_DETECTED || $status === self::STATUS_INVESTIGATING) {
                $active++;
            } elseif ($status === self::STATUS_RESOLVED) {
                $resolved++;
            }

            if ($metric !== '') {
                $byMetric[$metric] = ($byMetric[$metric] ?? 0) + 1;
            }
            $totalDeviation += abs($deviation);
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
    {
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

        $periodConfig = config('apm.regression.baseline_period_hours', 24);
        $period = (is_numeric($periodConfig) ? (string) $periodConfig : '24').' hours';

        return [
            'generated_at' => now()->toIso8601String(),
            'period' => $period,
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
     * @param  string  $metric  The metric name
     * @param  float  $baseline  The baseline value
     * @param  float  $current  The current value
     * @param  float  $deviation  The deviation percentage
     * @return array<string, mixed>
     */
    protected function createRegression(string $metric, float $baseline, float $current, float $deviation): array
    {
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
        $regressionsCache = Cache::get(self::REGRESSION_PREFIX.'list', []);
        if (is_array($regressionsCache)) {
            Cache::put('apm:regressions', $regressionsCache, 604800);
        }

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
        $regressionsCache = Cache::get(self::REGRESSION_PREFIX.'list', []);
        $regressions = is_array($regressionsCache) ? $regressionsCache : [];
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
        $components = is_array($healthScore['components'] ?? null) ? $healthScore['components'] : [];

        $component = is_array($components[$metric] ?? null) ? $components[$metric] : [];
        $value = $component['value'] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Get regression threshold for a metric.
     */
    protected function getRegressionThreshold(string $metric): float
    {
        $thresholds = config('apm.regression.thresholds', []);

        if (! is_array($thresholds)) {
            return 25.0;
        }

        $threshold = $thresholds[$metric] ?? 25;

        return is_numeric($threshold) ? (float) $threshold : 25.0;
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
            $sumSquaredDiff += ($value - $mean) ** 2;
        }

        return sqrt($sumSquaredDiff / ($count - 1));
    }

    /**
     * Get historical data for a metric.
     *
     * @param  string  $metric  The metric name
     * @param  int  $hours  Number of hours of historical data to retrieve
     * @return array<int, array{value: float, recorded_at: string}>
     */
    protected function getHistoricalData(string $metric, int $hours): array
    {
        /** @var array<int, array{value: float, recorded_at: string}> $data */
        $data = [];

        for ($i = $hours - 1; $i >= 0; $i--) {
            $timestamp = strtotime("-{$i} hours");
            if ($timestamp === false) {
                continue;
            }
            $hourKey = "apm:metrics:{$metric}:".date('Y-m-d-H', $timestamp);
            $hourData = Cache::get($hourKey, []);
            if (is_array($hourData)) {
                /** @var array<int, array{value: float, recorded_at: string}> $hourData */
                $data = array_merge($data, $hourData);
            }
        }

        return $data;
    }

    /**
     * Generate recommendations based on active regressions.
     *
     * @param  array<int, array<string, mixed>>  $regressions
     * @return array<int, string>
     */
    protected function generateRecommendations(array $regressions): array
    {
        $recommendations = [];

        foreach ($regressions as $regression) {
            $metric = is_string($regression['metric'] ?? null) ? $regression['metric'] : 'unknown';
            $deviation = is_numeric($regression['deviation_percent'] ?? null) ? (float) $regression['deviation_percent'] : 0.0;

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
