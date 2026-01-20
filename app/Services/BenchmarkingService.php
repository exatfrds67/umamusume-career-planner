<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Career;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Benchmarking Service
 *
 * Provides performance benchmarking against community averages including:
 * - Community benchmark data storage and retrieval
 * - User performance comparison against benchmarks
 * - Percentile ranking calculations
 * - Benchmark trend analysis
 *
 * Requirements: 25.4 (Task 5.2.5)
 */
class BenchmarkingService
{
    /**
     * Cache TTL for benchmark data (4 hours)
     */
    protected const CACHE_TTL = 14400;

    /**
     * Stat types for benchmarking
     *
     * @var array<string>
     */
    protected const STAT_TYPES = ['speed', 'stamina', 'power', 'guts', 'wit'];

    /**
     * Minimum sample size for reliable benchmarks
     */
    protected const MIN_BENCHMARK_SAMPLE = 10;

    /**
     * Percentile thresholds for ranking
     *
     * @var array<string, int>
     */
    protected const PERCENTILE_THRESHOLDS = [
        'top_1' => 99,
        'top_5' => 95,
        'top_10' => 90,
        'top_25' => 75,
        'above_average' => 50,
        'below_average' => 25,
        'bottom_10' => 10,
    ];

    // =========================================================================
    // COMMUNITY BENCHMARK DATA
    // =========================================================================

    /**
     * Get or calculate community benchmark data
     *
     * @return array{
     *     benchmarks: array,
     *     sample_size: int,
     *     last_updated: string,
     *     scenario_benchmarks: array<string, array>,
     *     percentile_thresholds: array<string, array>
     * }
     */
    public function getCommunityBenchmarks(): array
    {
        $cacheKey = 'benchmarks:community';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $careers = Career::whereNotNull('completed_at')
                ->with(['trainingSessions', 'races'])
                ->get();

            if ($careers->count() < self::MIN_BENCHMARK_SAMPLE) {
                return $this->getInsufficientBenchmarkData($careers->count());
            }

            return [
                'benchmarks' => $this->calculateOverallBenchmarks($careers),
                'sample_size' => $careers->count(),
                'last_updated' => now()->toIso8601String(),
                'scenario_benchmarks' => $this->calculateScenarioBenchmarks($careers),
                'percentile_thresholds' => $this->calculatePercentileThresholds($careers),
            ];
        });
    }

    /**
     * Calculate overall community benchmarks
     *
     * @return array{
     *     efficiency: array{mean: float, median: float, std_dev: float, min: float, max: float},
     *     win_rate: array{mean: float, median: float, std_dev: float, min: float, max: float},
     *     total_stats: array{mean: float, median: float, std_dev: float, min: int, max: int},
     *     stat_averages: array<string, array>
     * }
     */
    protected function calculateOverallBenchmarks(Collection $careers): array
    {
        $efficiencies = [];
        $winRates = [];
        $totalStats = [];
        $statValues = array_fill_keys(self::STAT_TYPES, []);

        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;
            $races = $career->races;

            // Efficiency
            $efficiency = $this->calculateCareerEfficiency($sessions);
            $efficiencies[] = $efficiency;

            // Win rate
            $winRate = $races->count() > 0
                ? ($races->where('won_race', true)->count() / $races->count()) * 100
                : 0;
            $winRates[] = $winRate;

            // Total stats
            $total = ($career->final_speed ?? 0)
                + ($career->final_stamina ?? 0)
                + ($career->final_power ?? 0)
                + ($career->final_guts ?? 0)
                + ($career->final_wit ?? 0);
            $totalStats[] = $total;

            // Individual stats
            foreach (self::STAT_TYPES as $stat) {
                $statValues[$stat][] = $career->{"final_{$stat}"} ?? 0;
            }
        }

        $statAverages = [];
        foreach ($statValues as $stat => $values) {
            $statAverages[$stat] = $this->calculateStatistics($values);
        }

        return [
            'efficiency' => $this->calculateStatistics($efficiencies),
            'win_rate' => $this->calculateStatistics($winRates),
            'total_stats' => $this->calculateStatistics($totalStats),
            'stat_averages' => $statAverages,
        ];
    }

    /**
     * Calculate benchmarks by scenario type
     *
     * @return array<string, array{
     *     sample_size: int,
     *     efficiency: array,
     *     win_rate: array,
     *     total_stats: array
     * }>
     */
    protected function calculateScenarioBenchmarks(Collection $careers): array
    {
        $scenarios = ['ura_finale', 'unity_cup'];
        $result = [];

        foreach ($scenarios as $scenario) {
            $scenarioCareers = $careers->where('scenario_type', $scenario);

            if ($scenarioCareers->count() < 3) {
                $result[$scenario] = [
                    'sample_size' => $scenarioCareers->count(),
                    'efficiency' => ['mean' => 0.0, 'median' => 0.0, 'std_dev' => 0.0],
                    'win_rate' => ['mean' => 0.0, 'median' => 0.0, 'std_dev' => 0.0],
                    'total_stats' => ['mean' => 0.0, 'median' => 0.0, 'std_dev' => 0.0],
                ];

                continue;
            }

            $efficiencies = [];
            $winRates = [];
            $totalStats = [];

            foreach ($scenarioCareers as $career) {
                $sessions = $career->trainingSessions;
                $races = $career->races;

                $efficiencies[] = $this->calculateCareerEfficiency($sessions);

                $winRates[] = $races->count() > 0
                    ? ($races->where('won_race', true)->count() / $races->count()) * 100
                    : 0;

                $totalStats[] = ($career->final_speed ?? 0)
                    + ($career->final_stamina ?? 0)
                    + ($career->final_power ?? 0)
                    + ($career->final_guts ?? 0)
                    + ($career->final_wit ?? 0);
            }

            $result[$scenario] = [
                'sample_size' => $scenarioCareers->count(),
                'efficiency' => $this->calculateStatistics($efficiencies),
                'win_rate' => $this->calculateStatistics($winRates),
                'total_stats' => $this->calculateStatistics($totalStats),
            ];
        }

        return $result;
    }

    /**
     * Calculate percentile thresholds for ranking
     *
     * @return array{
     *     efficiency: array<int, float>,
     *     win_rate: array<int, float>,
     *     total_stats: array<int, float>
     * }
     */
    protected function calculatePercentileThresholds(Collection $careers): array
    {
        $efficiencies = [];
        $winRates = [];
        $totalStats = [];

        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;
            $races = $career->races;

            $efficiencies[] = $this->calculateCareerEfficiency($sessions);

            $winRates[] = $races->count() > 0
                ? ($races->where('won_race', true)->count() / $races->count()) * 100
                : 0;

            $totalStats[] = ($career->final_speed ?? 0)
                + ($career->final_stamina ?? 0)
                + ($career->final_power ?? 0)
                + ($career->final_guts ?? 0)
                + ($career->final_wit ?? 0);
        }

        $percentiles = [10, 25, 50, 75, 90, 95, 99];

        return [
            'efficiency' => $this->calculatePercentiles($efficiencies, $percentiles),
            'win_rate' => $this->calculatePercentiles($winRates, $percentiles),
            'total_stats' => $this->calculatePercentiles($totalStats, $percentiles),
        ];
    }

    // =========================================================================
    // USER PERFORMANCE COMPARISON
    // =========================================================================

    /**
     * Compare user performance against community benchmarks
     *
     * @return array{
     *     user_metrics: array,
     *     benchmark_comparison: array,
     *     percentile_rankings: array,
     *     performance_summary: array,
     *     improvement_areas: array<string>
     * }
     */
    public function compareUserPerformance(User $user): array
    {
        $cacheKey = "benchmarks:user_comparison:{$user->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL / 2, function () use ($user) {
            $userCareers = Career::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->with(['trainingSessions', 'races'])
                ->get();

            if ($userCareers->isEmpty()) {
                return $this->getNoUserDataResult();
            }

            $benchmarks = $this->getCommunityBenchmarks();

            if (isset($benchmarks['error'])) {
                return [
                    'user_metrics' => $this->calculateUserMetrics($userCareers),
                    'benchmark_comparison' => ['error' => 'Insufficient community data for comparison'],
                    'percentile_rankings' => [],
                    'performance_summary' => [],
                    'improvement_areas' => [],
                ];
            }

            $userMetrics = $this->calculateUserMetrics($userCareers);
            $benchmarkComparison = $this->compareToBenchmarks($userMetrics, $benchmarks['benchmarks']);
            $percentileRankings = $this->calculateUserPercentiles($userMetrics, $benchmarks['percentile_thresholds']);
            $performanceSummary = $this->generatePerformanceSummary($benchmarkComparison, $percentileRankings);
            $improvementAreas = $this->identifyImprovementAreas($benchmarkComparison, $percentileRankings);

            return [
                'user_metrics' => $userMetrics,
                'benchmark_comparison' => $benchmarkComparison,
                'percentile_rankings' => $percentileRankings,
                'performance_summary' => $performanceSummary,
                'improvement_areas' => $improvementAreas,
            ];
        });
    }

    /**
     * Calculate user's performance metrics
     *
     * @return array{
     *     career_count: int,
     *     average_efficiency: float,
     *     average_win_rate: float,
     *     average_total_stats: float,
     *     stat_averages: array<string, float>,
     *     best_career: array|null,
     *     recent_trend: string
     * }
     */
    protected function calculateUserMetrics(Collection $careers): array
    {
        $efficiencies = [];
        $winRates = [];
        $totalStats = [];
        $statTotals = array_fill_keys(self::STAT_TYPES, []);

        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;
            $races = $career->races;

            $efficiency = $this->calculateCareerEfficiency($sessions);
            $efficiencies[] = $efficiency;

            $winRate = $races->count() > 0
                ? ($races->where('won_race', true)->count() / $races->count()) * 100
                : 0;
            $winRates[] = $winRate;

            $total = ($career->final_speed ?? 0)
                + ($career->final_stamina ?? 0)
                + ($career->final_power ?? 0)
                + ($career->final_guts ?? 0)
                + ($career->final_wit ?? 0);
            $totalStats[] = $total;

            foreach (self::STAT_TYPES as $stat) {
                $statTotals[$stat][] = $career->{"final_{$stat}"} ?? 0;
            }
        }

        $statAverages = [];
        foreach ($statTotals as $stat => $values) {
            $statAverages[$stat] = count($values) > 0 ? round(array_sum($values) / count($values), 1) : 0.0;
        }

        // Find best career
        $bestCareer = null;
        $bestScore = -1;
        foreach ($careers as $index => $career) {
            $score = ($efficiencies[$index] ?? 0) * 0.4 + ($winRates[$index] ?? 0) * 0.3 + (($totalStats[$index] ?? 0) / 50) * 0.3;
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestCareer = [
                    'career_id' => $career->id,
                    'efficiency' => $efficiencies[$index] ?? 0,
                    'win_rate' => $winRates[$index] ?? 0,
                    'total_stats' => $totalStats[$index] ?? 0,
                ];
            }
        }

        // Calculate recent trend
        $recentTrend = $this->calculateRecentTrend($efficiencies);

        return [
            'career_count' => $careers->count(),
            'average_efficiency' => count($efficiencies) > 0 ? round(array_sum($efficiencies) / count($efficiencies), 1) : 0.0,
            'average_win_rate' => count($winRates) > 0 ? round(array_sum($winRates) / count($winRates), 1) : 0.0,
            'average_total_stats' => count($totalStats) > 0 ? round(array_sum($totalStats) / count($totalStats), 0) : 0,
            'stat_averages' => $statAverages,
            'best_career' => $bestCareer,
            'recent_trend' => $recentTrend,
        ];
    }

    /**
     * Compare user metrics to community benchmarks
     *
     * @param  array<string, mixed>  $userMetrics
     * @param  array<string, mixed>  $benchmarks
     * @return array{
     *     efficiency: array{user: float, benchmark: float, difference: float, status: string},
     *     win_rate: array{user: float, benchmark: float, difference: float, status: string},
     *     total_stats: array{user: float, benchmark: float, difference: float, status: string},
     *     stat_comparison: array<string, array>
     * }
     */
    protected function compareToBenchmarks(array $userMetrics, array $benchmarks): array
    {
        $efficiencyDiff = $userMetrics['average_efficiency'] - ($benchmarks['efficiency']['mean'] ?? 0);
        $winRateDiff = $userMetrics['average_win_rate'] - ($benchmarks['win_rate']['mean'] ?? 0);
        $totalStatsDiff = $userMetrics['average_total_stats'] - ($benchmarks['total_stats']['mean'] ?? 0);

        $statComparison = [];
        foreach (self::STAT_TYPES as $stat) {
            $userAvg = $userMetrics['stat_averages'][$stat] ?? 0;
            $benchmarkAvg = $benchmarks['stat_averages'][$stat]['mean'] ?? 0;
            $diff = $userAvg - $benchmarkAvg;

            $statComparison[$stat] = [
                'user' => $userAvg,
                'benchmark' => round($benchmarkAvg, 1),
                'difference' => round($diff, 1),
                'status' => $this->getComparisonStatus($diff, 50),
            ];
        }

        return [
            'efficiency' => [
                'user' => $userMetrics['average_efficiency'],
                'benchmark' => round($benchmarks['efficiency']['mean'] ?? 0, 1),
                'difference' => round($efficiencyDiff, 1),
                'status' => $this->getComparisonStatus($efficiencyDiff, 5),
            ],
            'win_rate' => [
                'user' => $userMetrics['average_win_rate'],
                'benchmark' => round($benchmarks['win_rate']['mean'] ?? 0, 1),
                'difference' => round($winRateDiff, 1),
                'status' => $this->getComparisonStatus($winRateDiff, 10),
            ],
            'total_stats' => [
                'user' => $userMetrics['average_total_stats'],
                'benchmark' => round($benchmarks['total_stats']['mean'] ?? 0, 0),
                'difference' => round($totalStatsDiff, 0),
                'status' => $this->getComparisonStatus($totalStatsDiff, 200),
            ],
            'stat_comparison' => $statComparison,
        ];
    }

    // =========================================================================
    // PERCENTILE RANKING CALCULATIONS
    // =========================================================================

    /**
     * Calculate user's percentile rankings
     *
     * @param  array<string, mixed>  $userMetrics
     * @param  array<string, array<int, float>>  $thresholds
     * @return array{
     *     efficiency_percentile: int,
     *     win_rate_percentile: int,
     *     total_stats_percentile: int,
     *     overall_percentile: int,
     *     ranking_tier: string
     * }
     */
    protected function calculateUserPercentiles(array $userMetrics, array $thresholds): array
    {
        $efficiencyPercentile = $this->findPercentile(
            $userMetrics['average_efficiency'],
            $thresholds['efficiency'] ?? []
        );

        $winRatePercentile = $this->findPercentile(
            $userMetrics['average_win_rate'],
            $thresholds['win_rate'] ?? []
        );

        $totalStatsPercentile = $this->findPercentile(
            $userMetrics['average_total_stats'],
            $thresholds['total_stats'] ?? []
        );

        // Calculate overall percentile (weighted average)
        $overallPercentile = (int) round(
            ($efficiencyPercentile * 0.4) + ($winRatePercentile * 0.3) + ($totalStatsPercentile * 0.3)
        );

        $rankingTier = $this->determineRankingTier($overallPercentile);

        return [
            'efficiency_percentile' => $efficiencyPercentile,
            'win_rate_percentile' => $winRatePercentile,
            'total_stats_percentile' => $totalStatsPercentile,
            'overall_percentile' => $overallPercentile,
            'ranking_tier' => $rankingTier,
        ];
    }

    /**
     * Find percentile for a given value
     *
     * @param  array<int, float>  $thresholds
     */
    protected function findPercentile(float $value, array $thresholds): int
    {
        if (empty($thresholds)) {
            return 50; // Default to median if no thresholds
        }

        $percentiles = [10, 25, 50, 75, 90, 95, 99];

        foreach (array_reverse($percentiles) as $percentile) {
            if (isset($thresholds[$percentile]) && $value >= $thresholds[$percentile]) {
                return $percentile;
            }
        }

        return 5; // Below 10th percentile
    }

    /**
     * Determine ranking tier based on percentile
     */
    protected function determineRankingTier(int $percentile): string
    {
        return match (true) {
            $percentile >= 99 => 'legendary',
            $percentile >= 95 => 'elite',
            $percentile >= 90 => 'expert',
            $percentile >= 75 => 'advanced',
            $percentile >= 50 => 'intermediate',
            $percentile >= 25 => 'developing',
            default => 'beginner',
        };
    }

    /**
     * Generate performance summary
     *
     * @param  array<string, mixed>  $comparison
     * @param  array<string, mixed>  $percentiles
     * @return array{
     *     overall_assessment: string,
     *     strengths: array<string>,
     *     weaknesses: array<string>,
     *     notable_achievements: array<string>
     * }
     */
    protected function generatePerformanceSummary(array $comparison, array $percentiles): array
    {
        $strengths = [];
        $weaknesses = [];
        $achievements = [];

        // Analyze efficiency
        if ($comparison['efficiency']['status'] === 'above_average') {
            $strengths[] = 'Training efficiency is above community average';
        } elseif ($comparison['efficiency']['status'] === 'below_average') {
            $weaknesses[] = 'Training efficiency needs improvement';
        }

        // Analyze win rate
        if ($comparison['win_rate']['status'] === 'above_average') {
            $strengths[] = 'Race win rate exceeds community average';
        } elseif ($comparison['win_rate']['status'] === 'below_average') {
            $weaknesses[] = 'Race performance could be improved';
        }

        // Analyze total stats
        if ($comparison['total_stats']['status'] === 'above_average') {
            $strengths[] = 'Final stat totals are above average';
        } elseif ($comparison['total_stats']['status'] === 'below_average') {
            $weaknesses[] = 'Final stats are below community average';
        }

        // Check for notable achievements
        if ($percentiles['overall_percentile'] >= 90) {
            $achievements[] = 'Top 10% performer in the community';
        }
        if ($percentiles['efficiency_percentile'] >= 95) {
            $achievements[] = 'Elite training efficiency (top 5%)';
        }
        if ($percentiles['win_rate_percentile'] >= 95) {
            $achievements[] = 'Elite race performance (top 5%)';
        }

        // Overall assessment
        $overallAssessment = match ($percentiles['ranking_tier']) {
            'legendary' => 'Outstanding performance! You are among the best players.',
            'elite' => 'Excellent performance! You consistently outperform most players.',
            'expert' => 'Great performance! You are well above average.',
            'advanced' => 'Good performance! You are above the community average.',
            'intermediate' => 'Solid performance! You are at the community average.',
            'developing' => 'Room for improvement. Focus on the areas identified below.',
            default => 'Keep practicing! Every career is a learning opportunity.',
        };

        return [
            'overall_assessment' => $overallAssessment,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'notable_achievements' => $achievements,
        ];
    }

    /**
     * Identify areas for improvement
     *
     * @param  array<string, mixed>  $comparison
     * @param  array<string, mixed>  $percentiles
     * @return array<string>
     */
    protected function identifyImprovementAreas(array $comparison, array $percentiles): array
    {
        $improvements = [];

        // Check efficiency
        if ($percentiles['efficiency_percentile'] < 50) {
            $gap = abs($comparison['efficiency']['difference']);
            $improvements[] = "Improve training efficiency by {$gap}% to reach community average.";
        }

        // Check win rate
        if ($percentiles['win_rate_percentile'] < 50) {
            $gap = abs($comparison['win_rate']['difference']);
            $improvements[] = "Improve race win rate by {$gap}% to match community average.";
        }

        // Check individual stats
        foreach ($comparison['stat_comparison'] as $stat => $data) {
            if ($data['status'] === 'below_average' && abs($data['difference']) > 100) {
                $improvements[] = "Focus on {$stat} training - currently ".abs($data['difference']).' points below average.';
            }
        }

        // Add general recommendations based on tier
        if ($percentiles['ranking_tier'] === 'beginner' || $percentiles['ranking_tier'] === 'developing') {
            $improvements[] = 'Consider reviewing training guides and optimal support card combinations.';
        }

        return array_slice($improvements, 0, 5);
    }

    // =========================================================================
    // BENCHMARK TREND ANALYSIS
    // =========================================================================

    /**
     * Analyze benchmark trends over time
     *
     * @return array{
     *     community_trend: string,
     *     efficiency_trend: array,
     *     win_rate_trend: array,
     *     meta_shifts: array<string>,
     *     trend_analysis: string
     * }
     */
    public function analyzeBenchmarkTrends(): array
    {
        $cacheKey = 'benchmarks:trends';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            // Get careers grouped by month
            $careers = Career::whereNotNull('completed_at')
                ->where('completed_at', '>=', now()->subMonths(6))
                ->with(['trainingSessions', 'races'])
                ->orderBy('completed_at', 'asc')
                ->get();

            if ($careers->count() < self::MIN_BENCHMARK_SAMPLE) {
                return [
                    'community_trend' => 'insufficient_data',
                    'efficiency_trend' => [],
                    'win_rate_trend' => [],
                    'meta_shifts' => [],
                    'trend_analysis' => 'Not enough data to analyze trends.',
                ];
            }

            $monthlyData = $this->groupCareersByMonth($careers);
            $efficiencyTrend = $this->calculateMonthlyTrend($monthlyData, 'efficiency');
            $winRateTrend = $this->calculateMonthlyTrend($monthlyData, 'win_rate');

            $communityTrend = $this->determineCommunityTrend($efficiencyTrend, $winRateTrend);
            $metaShifts = $this->identifyMetaShifts($monthlyData);
            $trendAnalysis = $this->generateTrendAnalysis($communityTrend, $metaShifts);

            return [
                'community_trend' => $communityTrend,
                'efficiency_trend' => $efficiencyTrend,
                'win_rate_trend' => $winRateTrend,
                'meta_shifts' => $metaShifts,
                'trend_analysis' => $trendAnalysis,
            ];
        });
    }

    /**
     * Group careers by month
     *
     * @return array<string, Collection>
     */
    protected function groupCareersByMonth(Collection $careers): array
    {
        $grouped = [];

        foreach ($careers as $career) {
            $month = $career->completed_at->format('Y-m');
            if (! isset($grouped[$month])) {
                $grouped[$month] = collect();
            }
            $grouped[$month]->push($career);
        }

        return $grouped;
    }

    /**
     * Calculate monthly trend for a metric
     *
     * @param  array<string, Collection>  $monthlyData
     * @return array<string, float>
     */
    protected function calculateMonthlyTrend(array $monthlyData, string $metric): array
    {
        $trend = [];

        foreach ($monthlyData as $month => $careers) {
            $values = [];

            foreach ($careers as $career) {
                if ($metric === 'efficiency') {
                    $values[] = $this->calculateCareerEfficiency($career->trainingSessions);
                } elseif ($metric === 'win_rate') {
                    $races = $career->races;
                    $values[] = $races->count() > 0
                        ? ($races->where('won_race', true)->count() / $races->count()) * 100
                        : 0;
                }
            }

            $trend[$month] = count($values) > 0 ? round(array_sum($values) / count($values), 1) : 0.0;
        }

        return $trend;
    }

    /**
     * Determine overall community trend
     *
     * @param  array<string, float>  $efficiencyTrend
     * @param  array<string, float>  $winRateTrend
     */
    protected function determineCommunityTrend(array $efficiencyTrend, array $winRateTrend): string
    {
        if (count($efficiencyTrend) < 2) {
            return 'insufficient_data';
        }

        $efficiencyValues = array_values($efficiencyTrend);
        $winRateValues = array_values($winRateTrend);

        $efficiencyChange = end($efficiencyValues) - reset($efficiencyValues);
        $winRateChange = end($winRateValues) - reset($winRateValues);

        $overallChange = ($efficiencyChange + $winRateChange) / 2;

        return match (true) {
            $overallChange > 5 => 'improving',
            $overallChange < -5 => 'declining',
            default => 'stable',
        };
    }

    /**
     * Identify meta shifts in the community
     *
     * @param  array<string, Collection>  $monthlyData
     * @return array<string>
     */
    protected function identifyMetaShifts(array $monthlyData): array
    {
        $shifts = [];

        // Analyze training type preferences over time
        $monthlyTrainingPrefs = [];

        foreach ($monthlyData as $month => $careers) {
            $typeCounts = array_fill_keys(self::STAT_TYPES, 0);

            foreach ($careers as $career) {
                foreach ($career->trainingSessions as $session) {
                    $type = $session->training_type ?? 'other';
                    if (isset($typeCounts[$type])) {
                        $typeCounts[$type]++;
                    }
                }
            }

            $total = array_sum($typeCounts);
            if ($total > 0) {
                $monthlyTrainingPrefs[$month] = array_map(fn ($c) => round(($c / $total) * 100, 1), $typeCounts);
            }
        }

        // Detect significant shifts
        $months = array_keys($monthlyTrainingPrefs);
        if (count($months) >= 2) {
            $firstMonth = $monthlyTrainingPrefs[$months[0]] ?? [];
            $lastMonth = $monthlyTrainingPrefs[end($months)] ?? [];

            foreach (self::STAT_TYPES as $stat) {
                $change = ($lastMonth[$stat] ?? 0) - ($firstMonth[$stat] ?? 0);
                if (abs($change) > 10) {
                    $direction = $change > 0 ? 'increased' : 'decreased';
                    $shifts[] = ucfirst($stat)." training focus has {$direction} by ".abs(round($change)).'%';
                }
            }
        }

        return $shifts;
    }

    /**
     * Generate trend analysis text
     *
     * @param  array<string>  $metaShifts
     */
    protected function generateTrendAnalysis(string $communityTrend, array $metaShifts): string
    {
        $analysis = match ($communityTrend) {
            'improving' => 'The community is showing overall improvement in performance metrics.',
            'declining' => 'Community performance has been declining. This may indicate meta changes or increased difficulty.',
            'stable' => 'Community performance has remained stable over the analyzed period.',
            default => 'Insufficient data to determine community trends.',
        };

        if (! empty($metaShifts)) {
            $analysis .= ' Notable meta shifts: '.implode('; ', array_slice($metaShifts, 0, 3)).'.';
        }

        return $analysis;
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Calculate career efficiency from training sessions
     */
    protected function calculateCareerEfficiency(Collection $sessions): float
    {
        if ($sessions->isEmpty()) {
            return 0.0;
        }

        $totalGains = 0;
        foreach ($sessions as $session) {
            $totalGains += ($session->speed_gain ?? 0)
                + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0)
                + ($session->guts_gain ?? 0)
                + ($session->wit_gain ?? 0);
        }

        $idealTotal = $sessions->count() * 30;

        return $idealTotal > 0 ? min(100, round(($totalGains / $idealTotal) * 100, 1)) : 0.0;
    }

    /**
     * Calculate statistics for an array of values
     *
     * @param  array<float|int>  $values
     * @return array{mean: float, median: float, std_dev: float, min: float|int, max: float|int}
     */
    protected function calculateStatistics(array $values): array
    {
        if (empty($values)) {
            return ['mean' => 0.0, 'median' => 0.0, 'std_dev' => 0.0, 'min' => 0, 'max' => 0];
        }

        sort($values);
        $count = count($values);
        $mean = array_sum($values) / $count;

        // Median
        $middle = (int) floor($count / 2);
        $median = $count % 2 === 0
            ? ($values[$middle - 1] + $values[$middle]) / 2
            : $values[$middle];

        // Standard deviation
        $variance = array_sum(array_map(fn ($v) => pow($v - $mean, 2), $values)) / $count;
        $stdDev = sqrt($variance);

        return [
            'mean' => round($mean, 2),
            'median' => round($median, 2),
            'std_dev' => round($stdDev, 2),
            'min' => min($values),
            'max' => max($values),
        ];
    }

    /**
     * Calculate percentiles for an array of values
     *
     * @param  array<float|int>  $values
     * @param  array<int>  $percentiles
     * @return array<int, float>
     */
    protected function calculatePercentiles(array $values, array $percentiles): array
    {
        if (empty($values)) {
            return array_fill_keys($percentiles, 0.0);
        }

        sort($values);
        $count = count($values);
        $result = [];

        foreach ($percentiles as $p) {
            $index = ($p / 100) * ($count - 1);
            $lower = (int) floor($index);
            $upper = (int) ceil($index);
            $fraction = $index - $lower;

            if ($lower === $upper || $upper >= $count) {
                $result[$p] = round($values[$lower], 2);
            } else {
                $result[$p] = round($values[$lower] + $fraction * ($values[$upper] - $values[$lower]), 2);
            }
        }

        return $result;
    }

    /**
     * Get comparison status based on difference
     */
    protected function getComparisonStatus(float $difference, float $threshold): string
    {
        return match (true) {
            $difference > $threshold => 'above_average',
            $difference < -$threshold => 'below_average',
            default => 'average',
        };
    }

    /**
     * Calculate recent trend from efficiency values
     *
     * @param  array<float>  $efficiencies
     */
    protected function calculateRecentTrend(array $efficiencies): string
    {
        if (count($efficiencies) < 3) {
            return 'insufficient_data';
        }

        $recent = array_slice($efficiencies, -3);
        $older = array_slice($efficiencies, 0, -3);

        if (empty($older)) {
            return 'insufficient_data';
        }

        $recentAvg = array_sum($recent) / count($recent);
        $olderAvg = array_sum($older) / count($older);

        $change = $recentAvg - $olderAvg;

        return match (true) {
            $change > 5 => 'improving',
            $change < -5 => 'declining',
            default => 'stable',
        };
    }

    /**
     * Get result for insufficient benchmark data
     *
     * @return array{error: string, message: string, current_sample_size: int, required_sample_size: int}
     */
    protected function getInsufficientBenchmarkData(int $currentSize): array
    {
        return [
            'error' => 'insufficient_data',
            'message' => 'At least '.self::MIN_BENCHMARK_SAMPLE.' completed careers are required for benchmarking.',
            'current_sample_size' => $currentSize,
            'required_sample_size' => self::MIN_BENCHMARK_SAMPLE,
        ];
    }

    /**
     * Get result when user has no data
     *
     * @return array{error: string, message: string}
     */
    protected function getNoUserDataResult(): array
    {
        return [
            'error' => 'no_user_data',
            'message' => 'No completed careers found for this user.',
            'user_metrics' => [],
            'benchmark_comparison' => [],
            'percentile_rankings' => [],
            'performance_summary' => [],
            'improvement_areas' => [],
        ];
    }

    /**
     * Clear benchmark cache
     */
    public function clearCache(?User $user = null): void
    {
        Cache::forget('benchmarks:community');
        Cache::forget('benchmarks:trends');

        if ($user) {
            Cache::forget("benchmarks:user_comparison:{$user->id}");
        }
    }
}
