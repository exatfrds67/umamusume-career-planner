<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Historical Tracking Service
 *
 * Provides long-term trend analysis and historical tracking including:
 * - Long-term trend analysis across multiple careers
 * - Success rate tracking with confidence intervals
 * - Machine learning model update recommendations based on performance data
 * - Historical performance benchmarking
 *
 * Requirements: 25.4 (Task 5.2.5)
 */
class HistoricalTrackingService
{
    /**
     * Cache TTL for historical data (2 hours)
     */
    protected const CACHE_TTL = 7200;

    /**
     * Stat types for analysis
     *
     * @var array<string>
     */
    protected const STAT_TYPES = ['speed', 'stamina', 'power', 'guts', 'wit'];

    /**
     * Career phases for analysis
     *
     * @var array<string, array{min: int, max: int}>
     */
    protected const CAREER_PHASES = [
        'junior' => ['min' => 1, 'max' => 24],
        'classic' => ['min' => 25, 'max' => 48],
        'senior' => ['min' => 49, 'max' => 72],
    ];

    /**
     * Minimum sample size for statistical significance
     */
    protected const MIN_SAMPLE_SIZE = 5;

    /**
     * Z-score for 95% confidence interval
     */
    protected const Z_SCORE_95 = 1.96;

    /**
     * Z-score for 99% confidence interval
     */
    protected const Z_SCORE_99 = 2.576;

    // =========================================================================
    // LONG-TERM TREND ANALYSIS
    // =========================================================================

    /**
     * Analyze long-term trends across multiple careers for a user
     *
     * @return array<string, mixed>
     */
    public function analyzeLongTermTrends(User $user): array
    {
        $cacheKey = "historical:long_term_trends:{$user->id}";

        /** @var array<string, mixed> */
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $careers = Career::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->with(['trainingSessions', 'races'])
                ->orderBy('completed_at', 'asc')
                ->get();

            if ($careers->count() < self::MIN_SAMPLE_SIZE) {
                return $this->getInsufficientDataResult('long_term_trends', $careers->count());
            }

            return [
                'trend_summary' => $this->calculateTrendSummary($careers),
                'performance_evolution' => $this->calculatePerformanceEvolution($careers),
                'stat_trends' => $this->calculateStatTrends($careers),
                'efficiency_trends' => $this->calculateEfficiencyTrends($careers),
                'race_performance_trends' => $this->calculateRacePerformanceTrends($careers),
                'seasonal_patterns' => $this->identifySeasonalPatterns($careers),
                'improvement_velocity' => $this->calculateImprovementVelocity($careers),
            ];
        });
    }

    /**
     * Calculate overall trend summary
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     total_careers: int,
     *     date_range: array{start: string, end: string},
     *     overall_trend: string,
     *     improvement_rate: float,
     *     consistency_score: float,
     *     peak_performance_career: int|null
     * }
     */
    protected function calculateTrendSummary(Collection $careers): array
    {
        $totalCareers = $careers->count();
        /** @var Career|null $firstCareer */
        $firstCareer = $careers->first();
        /** @var Career|null $lastCareer */
        $lastCareer = $careers->last();

        // Calculate overall efficiency for first and last halves
        $midpoint = (int) floor($totalCareers / 2);
        $firstHalf = $careers->take($midpoint);
        $secondHalf = $careers->skip($midpoint);

        $firstHalfEfficiency = $this->calculateAverageEfficiency($firstHalf);
        $secondHalfEfficiency = $this->calculateAverageEfficiency($secondHalf);

        $improvementRate = $firstHalfEfficiency > 0
            ? round((($secondHalfEfficiency - $firstHalfEfficiency) / $firstHalfEfficiency) * 100, 2)
            : 0.0;

        $overallTrend = match (true) {
            $improvementRate > 10 => 'improving',
            $improvementRate < -10 => 'declining',
            default => 'stable',
        };

        // Find peak performance career
        /** @var Career|null $peakCareer */
        $peakCareer = $careers->sortByDesc(function (Career $career): float {
            return $this->calculateCareerScore($career);
        })->first();

        // Calculate consistency score
        $consistencyScore = $this->calculateConsistencyScore($careers);

        $startDate = $firstCareer?->completed_at instanceof \Carbon\Carbon
            ? $firstCareer->completed_at->format('Y-m-d')
            : 'N/A';
        $endDate = $lastCareer?->completed_at instanceof \Carbon\Carbon
            ? $lastCareer->completed_at->format('Y-m-d')
            : 'N/A';

        return [
            'total_careers' => $totalCareers,
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'overall_trend' => $overallTrend,
            'improvement_rate' => $improvementRate,
            'consistency_score' => $consistencyScore,
            'peak_performance_career' => $peakCareer?->id,
        ];
    }

    /**
     * Calculate performance evolution over time
     *
     * @param  Collection<int, Career>  $careers
     * @return array<int, array{
     *     career_id: int,
     *     completed_at: string,
     *     efficiency: float,
     *     total_stats: int,
     *     win_rate: float,
     *     score: float
     * }>
     */
    protected function calculatePerformanceEvolution(Collection $careers): array
    {
        $evolution = [];

        /** @var Career $career */
        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;
            $races = $career->races;

            $efficiency = $this->calculateCareerEfficiencyFromSessions($sessions);
            $totalStats = $this->calculateTotalStats($career);
            $winRate = $this->calculateWinRate($races);
            $score = $this->calculateCareerScore($career);

            $completedAt = $career->completed_at instanceof \Carbon\Carbon
                ? $career->completed_at->format('Y-m-d')
                : 'N/A';

            $evolution[] = [
                'career_id' => $career->id,
                'completed_at' => $completedAt,
                'efficiency' => $efficiency,
                'total_stats' => $totalStats,
                'win_rate' => $winRate,
                'score' => $score,
            ];
        }

        return $evolution;
    }

    /**
     * Calculate stat trends over time
     *
     * @param  Collection<int, Career>  $careers
     * @return array<string, array{
     *     trend: string,
     *     average: float,
     *     growth_rate: float,
     *     best_career: int|null,
     *     values: array<int, int>
     * }>
     */
    protected function calculateStatTrends(Collection $careers): array
    {
        $statTrends = [];

        foreach (self::STAT_TYPES as $stat) {
            /** @var array<int, int> $values */
            $values = [];

            /** @var Career $career */
            foreach ($careers as $career) {
                $finalStat = $career->{"final_{$stat}"} ?? 0;
                $values[$career->id] = (int) $finalStat;
            }

            $numericValues = array_values($values);
            $average = count($numericValues) > 0 ? array_sum($numericValues) / count($numericValues) : 0;

            // Calculate growth rate (first half vs second half)
            $midpoint = (int) floor(count($numericValues) / 2);
            $firstHalf = array_slice($numericValues, 0, $midpoint);
            $secondHalf = array_slice($numericValues, $midpoint);

            $firstAvg = count($firstHalf) > 0 ? array_sum($firstHalf) / count($firstHalf) : 0;
            $secondAvg = count($secondHalf) > 0 ? array_sum($secondHalf) / count($secondHalf) : 0;

            $growthRate = $firstAvg > 0 ? round((($secondAvg - $firstAvg) / $firstAvg) * 100, 2) : 0.0;

            $trend = match (true) {
                $growthRate > 5 => 'improving',
                $growthRate < -5 => 'declining',
                default => 'stable',
            };

            // Find best career for this stat
            $bestCareerId = null;
            if (! empty($numericValues)) {
                $searchResult = array_search(max($numericValues), $values, true);
                $bestCareerId = is_int($searchResult) ? $searchResult : null;
            }

            $statTrends[$stat] = [
                'trend' => $trend,
                'average' => round($average, 1),
                'growth_rate' => $growthRate,
                'best_career' => $bestCareerId,
                'values' => $values,
            ];
        }

        return $statTrends;
    }

    /**
     * Calculate efficiency trends over time
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     trend: string,
     *     average_efficiency: float,
     *     improvement_rate: float,
     *     efficiency_by_career: array<int, float>,
     *     moving_average: array<int, float>
     * }
     */
    protected function calculateEfficiencyTrends(Collection $careers): array
    {
        $efficiencies = [];

        /** @var Career $career */
        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;
            $efficiencies[$career->id] = $this->calculateCareerEfficiencyFromSessions($sessions);
        }

        $numericValues = array_values($efficiencies);
        $average = count($numericValues) > 0 ? array_sum($numericValues) / count($numericValues) : 0;

        // Calculate improvement rate
        $midpoint = (int) floor(count($numericValues) / 2);
        $firstHalf = array_slice($numericValues, 0, $midpoint);
        $secondHalf = array_slice($numericValues, $midpoint);

        $firstAvg = count($firstHalf) > 0 ? array_sum($firstHalf) / count($firstHalf) : 0;
        $secondAvg = count($secondHalf) > 0 ? array_sum($secondHalf) / count($secondHalf) : 0;

        $improvementRate = $firstAvg > 0 ? round((($secondAvg - $firstAvg) / $firstAvg) * 100, 2) : 0.0;

        $trend = match (true) {
            $improvementRate > 5 => 'improving',
            $improvementRate < -5 => 'declining',
            default => 'stable',
        };

        // Calculate 3-career moving average
        $movingAverage = $this->calculateMovingAverage($efficiencies, 3);

        return [
            'trend' => $trend,
            'average_efficiency' => round($average, 1),
            'improvement_rate' => $improvementRate,
            'efficiency_by_career' => $efficiencies,
            'moving_average' => $movingAverage,
        ];
    }

    /**
     * Calculate race performance trends
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     win_rate_trend: string,
     *     average_win_rate: float,
     *     win_rate_by_career: array<int, float>,
     *     g1_performance_trend: array{performances: array<int, float>, average: float},
     *     position_improvement: float
     * }
     */
    protected function calculateRacePerformanceTrends(Collection $careers): array
    {
        $winRates = [];
        $g1Performances = [];
        $avgPositions = [];

        /** @var Career $career */
        foreach ($careers as $career) {
            $races = $career->races;
            $winRates[$career->id] = $this->calculateWinRate($races);

            // G1 performance
            $g1Races = $races->where('race_grade', '=', 'G1');
            $g1Performances[$career->id] = $g1Races->count() > 0
                ? round(($g1Races->where('won_race', true)->count() / $g1Races->count()) * 100, 1)
                : 0.0;

            // Average position
            $avgPositions[$career->id] = $races->count() > 0
                ? round((float) $races->avg('finish_position'), 2)
                : 0.0;
        }

        $numericWinRates = array_values($winRates);
        $averageWinRate = count($numericWinRates) > 0 ? array_sum($numericWinRates) / count($numericWinRates) : 0;

        // Calculate trend
        $midpoint = (int) floor(count($numericWinRates) / 2);
        $firstHalf = array_slice($numericWinRates, 0, $midpoint);
        $secondHalf = array_slice($numericWinRates, $midpoint);

        $firstAvg = count($firstHalf) > 0 ? array_sum($firstHalf) / count($firstHalf) : 0;
        $secondAvg = count($secondHalf) > 0 ? array_sum($secondHalf) / count($secondHalf) : 0;

        $winRateTrend = match (true) {
            $secondAvg > $firstAvg + 5 => 'improving',
            $secondAvg < $firstAvg - 5 => 'declining',
            default => 'stable',
        };

        // Position improvement (lower is better)
        $firstPositions = array_slice(array_values($avgPositions), 0, $midpoint);
        $secondPositions = array_slice(array_values($avgPositions), $midpoint);
        $firstPosAvg = count($firstPositions) > 0 ? array_sum($firstPositions) / count($firstPositions) : 0;
        $secondPosAvg = count($secondPositions) > 0 ? array_sum($secondPositions) / count($secondPositions) : 0;
        $positionImprovement = round($firstPosAvg - $secondPosAvg, 2);

        return [
            'win_rate_trend' => $winRateTrend,
            'average_win_rate' => round($averageWinRate, 1),
            'win_rate_by_career' => $winRates,
            'g1_performance_trend' => [
                'performances' => $g1Performances,
                'average' => count($g1Performances) > 0 ? round(array_sum($g1Performances) / count($g1Performances), 1) : 0.0,
            ],
            'position_improvement' => $positionImprovement,
        ];
    }

    /**
     * Identify seasonal patterns in performance
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     monthly_performance: array<string, array{average_score: float, career_count: int}>,
     *     best_month: string|null,
     *     worst_month: string|null,
     *     pattern_detected: bool,
     *     pattern_description: string
     * }
     */
    protected function identifySeasonalPatterns(Collection $careers): array
    {
        /** @var array<string, array{scores: array<float>, count: int}> $monthlyData */
        $monthlyData = [];

        /** @var Career $career */
        foreach ($careers as $career) {
            $completedAt = $career->completed_at;
            if ($completedAt instanceof \Carbon\Carbon) {
                $month = $completedAt->format('F');
                if (! isset($monthlyData[$month])) {
                    $monthlyData[$month] = ['scores' => [], 'count' => 0];
                }
                $monthlyData[$month]['scores'][] = $this->calculateCareerScore($career);
                $monthlyData[$month]['count']++;
            }
        }

        /** @var array<string, array{average_score: float, career_count: int}> $monthlyPerformance */
        $monthlyPerformance = [];
        foreach ($monthlyData as $month => $data) {
            $scores = $data['scores'];
            $monthlyPerformance[$month] = [
                'average_score' => count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0.0,
                'career_count' => $data['count'],
            ];
        }

        // Find best and worst months
        $bestMonth = null;
        $worstMonth = null;
        $bestScore = -1.0;
        $worstScore = (float) PHP_INT_MAX;

        foreach ($monthlyPerformance as $month => $data) {
            $careerCount = $data['career_count'];
            $avgScore = $data['average_score'];
            if ($careerCount >= 2) { // Only consider months with enough data
                if ($avgScore > $bestScore) {
                    $bestScore = $avgScore;
                    $bestMonth = (string) $month;
                }
                if ($avgScore < $worstScore) {
                    $worstScore = $avgScore;
                    $worstMonth = (string) $month;
                }
            }
        }

        // Detect pattern
        $patternDetected = $bestMonth !== null && $worstMonth !== null && ($bestScore - $worstScore) > 10;
        $patternDescription = $patternDetected
            ? "Performance tends to be higher in {$bestMonth} and lower in {$worstMonth}."
            : 'No significant seasonal pattern detected.';

        return [
            'monthly_performance' => $monthlyPerformance,
            'best_month' => $bestMonth,
            'worst_month' => $worstMonth,
            'pattern_detected' => $patternDetected,
            'pattern_description' => $patternDescription,
        ];
    }

    /**
     * Calculate improvement velocity (rate of improvement over time)
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     velocity: float,
     *     acceleration: float,
     *     projected_next_score: float,
     *     time_to_mastery: int|null,
     *     velocity_trend: string
     * }
     */
    protected function calculateImprovementVelocity(Collection $careers): array
    {
        $scores = [];
        /** @var Career $career */
        foreach ($careers as $index => $career) {
            $scores[$index] = $this->calculateCareerScore($career);
        }

        if (count($scores) < 3) {
            return [
                'velocity' => 0.0,
                'acceleration' => 0.0,
                'projected_next_score' => 0.0,
                'time_to_mastery' => null,
                'velocity_trend' => 'insufficient_data',
            ];
        }

        // Calculate velocity (change per career)
        $velocities = [];
        $scoreValues = array_values($scores);
        for ($i = 1; $i < count($scoreValues); $i++) {
            $velocities[] = $scoreValues[$i] - $scoreValues[$i - 1];
        }

        $avgVelocity = array_sum($velocities) / count($velocities);

        // Calculate acceleration (change in velocity)
        $accelerations = [];
        for ($i = 1; $i < count($velocities); $i++) {
            $accelerations[] = $velocities[$i] - $velocities[$i - 1];
        }

        $avgAcceleration = count($accelerations) > 0 ? array_sum($accelerations) / count($accelerations) : 0;

        // Project next score
        $lastScore = end($scoreValues);
        $projectedNextScore = $lastScore + $avgVelocity + ($avgAcceleration / 2);

        // Estimate time to mastery (score of 90)
        $masteryScore = 90;
        $timeToMastery = null;
        if ($avgVelocity > 0 && $lastScore < $masteryScore) {
            $timeToMastery = (int) ceil(($masteryScore - $lastScore) / $avgVelocity);
        }

        $velocityTrend = match (true) {
            $avgAcceleration > 1 => 'accelerating',
            $avgAcceleration < -1 => 'decelerating',
            $avgVelocity > 0 => 'steady_improvement',
            $avgVelocity < 0 => 'declining',
            default => 'stable',
        };

        return [
            'velocity' => round($avgVelocity, 2),
            'acceleration' => round($avgAcceleration, 2),
            'projected_next_score' => round(max(0, min(100, $projectedNextScore)), 1),
            'time_to_mastery' => $timeToMastery,
            'velocity_trend' => $velocityTrend,
        ];
    }

    // =========================================================================
    // SUCCESS RATE TRACKING WITH CONFIDENCE INTERVALS
    // =========================================================================

    /**
     * Calculate success rates with confidence intervals
     *
     * @return array<string, mixed>
     */
    public function calculateSuccessRatesWithConfidence(User $user): array
    {
        $cacheKey = "historical:success_rates:{$user->id}";

        /** @var array<string, mixed> */
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $careers = Career::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->with(['character', 'trainingSessions', 'races'])
                ->get();

            if ($careers->count() < self::MIN_SAMPLE_SIZE) {
                return $this->getInsufficientDataResult('success_rates', $careers->count());
            }

            return [
                'overall_success_rate' => $this->calculateOverallSuccessRate($careers),
                'success_by_scenario' => $this->calculateSuccessByScenario($careers),
                'success_by_character_type' => $this->calculateSuccessByCharacterType($careers),
                'success_factors' => $this->identifySuccessFactors($careers),
                'confidence_analysis' => $this->performConfidenceAnalysis($careers),
            ];
        });
    }

    /**
     * Calculate overall success rate with confidence interval
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     success_rate: float,
     *     sample_size: int,
     *     confidence_interval_95: array{lower: float, upper: float},
     *     confidence_interval_99: array{lower: float, upper: float},
     *     margin_of_error: float
     * }
     */
    protected function calculateOverallSuccessRate(Collection $careers): array
    {
        $totalCareers = $careers->count();
        $successfulCareers = $careers->filter(fn (Career $c): bool => $this->isCareerSuccessful($c))->count();

        $successRate = $totalCareers > 0 ? ($successfulCareers / $totalCareers) * 100 : 0;

        // Calculate confidence intervals using Wilson score interval
        $ci95 = $this->calculateWilsonConfidenceInterval($successfulCareers, $totalCareers, self::Z_SCORE_95);
        $ci99 = $this->calculateWilsonConfidenceInterval($successfulCareers, $totalCareers, self::Z_SCORE_99);

        // Margin of error (half-width of 95% CI)
        $marginOfError = ($ci95['upper'] - $ci95['lower']) / 2;

        return [
            'success_rate' => round($successRate, 1),
            'sample_size' => $totalCareers,
            'confidence_interval_95' => $ci95,
            'confidence_interval_99' => $ci99,
            'margin_of_error' => round($marginOfError, 2),
        ];
    }

    /**
     * Calculate Wilson score confidence interval
     *
     * @return array{lower: float, upper: float}
     */
    protected function calculateWilsonConfidenceInterval(int $successes, int $total, float $zScore): array
    {
        if ($total === 0) {
            return ['lower' => 0.0, 'upper' => 0.0];
        }

        $p = $successes / $total;
        $n = $total;
        $z2 = $zScore * $zScore;

        $denominator = 1 + ($z2 / $n);
        $center = $p + ($z2 / (2 * $n));
        $spread = $zScore * sqrt(($p * (1 - $p) / $n) + ($z2 / (4 * $n * $n)));

        $lower = ($center - $spread) / $denominator;
        $upper = ($center + $spread) / $denominator;

        return [
            'lower' => round(max(0, $lower) * 100, 1),
            'upper' => round(min(1, $upper) * 100, 1),
        ];
    }

    /**
     * Calculate success rates by scenario type
     *
     * @param  Collection<int, Career>  $careers
     * @return array<string, array{
     *     success_rate: float,
     *     sample_size: int,
     *     confidence_interval_95: array{lower: float, upper: float}
     * }>
     */
    protected function calculateSuccessByScenario(Collection $careers): array
    {
        $scenarios = ['ura_finale', 'unity_cup'];
        $result = [];

        foreach ($scenarios as $scenario) {
            $scenarioCareers = $careers->where('scenario_type', $scenario);
            $total = $scenarioCareers->count();
            $successes = $scenarioCareers->filter(fn (Career $c): bool => $this->isCareerSuccessful($c))->count();

            $successRate = $total > 0 ? ($successes / $total) * 100 : 0;
            $ci95 = $this->calculateWilsonConfidenceInterval($successes, $total, self::Z_SCORE_95);

            $result[$scenario] = [
                'success_rate' => round($successRate, 1),
                'sample_size' => $total,
                'confidence_interval_95' => $ci95,
            ];
        }

        return $result;
    }

    /**
     * Calculate success rates by character type/specialization
     *
     * @param  Collection<int, Career>  $careers
     * @return array<string, array{
     *     success_rate: float,
     *     sample_size: int,
     *     confidence_interval_95: array{lower: float, upper: float}
     * }>
     */
    protected function calculateSuccessByCharacterType(Collection $careers): array
    {
        /** @var array<string, array{total: int, successes: int}> $characterTypes */
        $characterTypes = [];

        /** @var Career $career */
        foreach ($careers as $career) {
            $character = $career->character;
            if (! $character) {
                continue;
            }

            // Determine character type based on highest stat focus
            $type = $this->determineCharacterType($career);

            if (! isset($characterTypes[$type])) {
                $characterTypes[$type] = ['total' => 0, 'successes' => 0];
            }

            $characterTypes[$type]['total']++;
            if ($this->isCareerSuccessful($career)) {
                $characterTypes[$type]['successes']++;
            }
        }

        $result = [];
        foreach ($characterTypes as $type => $data) {
            $total = $data['total'];
            $successes = $data['successes'];
            $successRate = $total > 0 ? ($successes / $total) * 100 : 0;
            $ci95 = $this->calculateWilsonConfidenceInterval($successes, $total, self::Z_SCORE_95);

            $result[$type] = [
                'success_rate' => round($successRate, 1),
                'sample_size' => $total,
                'confidence_interval_95' => $ci95,
            ];
        }

        return $result;
    }

    /**
     * Identify factors that correlate with success
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     high_impact_factors: array<string, float>,
     *     training_correlations: array<string, float>,
     *     race_correlations: array<string, float>,
     *     recommendations: array<string>
     * }
     */
    protected function identifySuccessFactors(Collection $careers): array
    {
        $successfulCareers = $careers->filter(fn (Career $c): bool => $this->isCareerSuccessful($c));
        $unsuccessfulCareers = $careers->filter(fn (Career $c): bool => ! $this->isCareerSuccessful($c));

        // Calculate average metrics for successful vs unsuccessful careers
        $successfulMetrics = $this->calculateAverageMetrics($successfulCareers);
        $unsuccessfulMetrics = $this->calculateAverageMetrics($unsuccessfulCareers);

        // Identify high impact factors
        $highImpactFactors = [];
        $trainingCorrelations = [];
        $raceCorrelations = [];

        // Compare efficiency
        $efficiencyDiff = $successfulMetrics['efficiency'] - $unsuccessfulMetrics['efficiency'];
        if (abs($efficiencyDiff) > 5) {
            $highImpactFactors['training_efficiency'] = round($efficiencyDiff, 1);
        }

        // Compare training type distributions
        foreach (self::STAT_TYPES as $stat) {
            $diff = ($successfulMetrics['training_distribution'][$stat] ?? 0) -
                ($unsuccessfulMetrics['training_distribution'][$stat] ?? 0);
            if (abs($diff) > 3) {
                $trainingCorrelations["{$stat}_training"] = round($diff, 1);
            }
        }

        // Compare race metrics
        $winRateDiff = $successfulMetrics['win_rate'] - $unsuccessfulMetrics['win_rate'];
        if (abs($winRateDiff) > 10) {
            $raceCorrelations['win_rate'] = round($winRateDiff, 1);
        }

        // Generate recommendations
        $recommendations = $this->generateSuccessRecommendations($highImpactFactors, $trainingCorrelations);

        return [
            'high_impact_factors' => $highImpactFactors,
            'training_correlations' => $trainingCorrelations,
            'race_correlations' => $raceCorrelations,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Perform confidence analysis on success metrics
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     statistical_significance: bool,
     *     sample_adequacy: string,
     *     reliability_score: float,
     *     recommendations_for_improvement: array<string>
     * }
     */
    protected function performConfidenceAnalysis(Collection $careers): array
    {
        $sampleSize = $careers->count();

        // Determine sample adequacy
        $sampleAdequacy = match (true) {
            $sampleSize >= 30 => 'excellent',
            $sampleSize >= 20 => 'good',
            $sampleSize >= 10 => 'moderate',
            $sampleSize >= 5 => 'minimal',
            default => 'insufficient',
        };

        // Calculate reliability score based on sample size and variance
        $successRates = [];
        $chunkSize = max(1, (int) floor($sampleSize / 3));

        foreach ($careers->chunk($chunkSize) as $chunk) {
            $successes = $chunk->filter(fn (Career $c): bool => $this->isCareerSuccessful($c))->count();
            $successRates[] = $chunk->count() > 0 ? ($successes / $chunk->count()) * 100 : 0;
        }

        $variance = $this->calculateVariance($successRates);
        $reliabilityScore = max(0, min(100, 100 - ($variance / 2)));

        // Statistical significance (simplified check)
        $statisticalSignificance = $sampleSize >= 10 && $variance < 400;

        // Recommendations
        $recommendations = [];
        if ($sampleSize < 20) {
            $recommendations[] = 'Complete more careers to improve statistical confidence.';
        }
        if ($variance > 200) {
            $recommendations[] = 'Performance is inconsistent. Focus on developing consistent strategies.';
        }
        if (! $statisticalSignificance) {
            $recommendations[] = 'Current data may not be statistically significant. Continue tracking.';
        }

        return [
            'statistical_significance' => $statisticalSignificance,
            'sample_adequacy' => $sampleAdequacy,
            'reliability_score' => round($reliabilityScore, 1),
            'recommendations_for_improvement' => $recommendations,
        ];
    }

    // =========================================================================
    // ML MODEL UPDATE RECOMMENDATIONS
    // =========================================================================

    /**
     * Generate ML model update recommendations based on performance data
     *
     * @return array<string, mixed>
     */
    public function generateMLModelUpdateRecommendations(User $user): array
    {
        $cacheKey = "historical:ml_recommendations:{$user->id}";

        /** @var array<string, mixed> */
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $careers = Career::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->with(['trainingSessions', 'races'])
                ->orderBy('completed_at', 'desc')
                ->get();

            if ($careers->count() < self::MIN_SAMPLE_SIZE) {
                return $this->getInsufficientDataResult('ml_recommendations', $careers->count());
            }

            return [
                'model_performance' => $this->assessModelPerformance($careers),
                'update_recommendations' => $this->generateUpdateRecommendations($careers),
                'feature_importance' => $this->calculateFeatureImportance($careers),
                'data_quality_assessment' => $this->assessDataQuality($careers),
                'retraining_priority' => $this->determineRetrainingPriority($careers),
            ];
        });
    }

    /**
     * Assess current model performance based on prediction accuracy
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     overall_accuracy: float,
     *     stat_prediction_accuracy: array<string, float>,
     *     race_prediction_accuracy: float,
     *     accuracy_trend: string,
     *     degradation_detected: bool
     * }
     */
    protected function assessModelPerformance(Collection $careers): array
    {
        /** @var array<string, array<float>> $statAccuracies */
        $statAccuracies = array_fill_keys(self::STAT_TYPES, []);
        $raceAccuracies = [];

        /** @var Career $career */
        foreach ($careers as $career) {
            // Analyze training session predictions
            foreach ($career->trainingSessions as $session) {
                $predictions = $session->prediction_metadata ?? [];
                if (! empty($predictions)) {
                    foreach (self::STAT_TYPES as $stat) {
                        $predicted = $predictions["predicted_{$stat}"] ?? null;
                        $actual = $session->{"{$stat}_gain"} ?? 0;

                        if ($predicted !== null) {
                            $accuracy = 100 - min(100, abs($predicted - $actual) * 5);
                            $statAccuracies[$stat][] = max(0, $accuracy);
                        }
                    }
                }
            }

            // Analyze race predictions
            foreach ($career->races as $race) {
                $predictions = $race->prediction_metadata ?? [];
                if (isset($predictions['predicted_position']) && isset($race->finish_position)) {
                    $positionDiff = abs($predictions['predicted_position'] - $race->finish_position);
                    $accuracy = 100 - min(100, $positionDiff * 20);
                    $raceAccuracies[] = max(0, $accuracy);
                }
            }
        }

        // Calculate averages
        $statPredictionAccuracy = [];
        foreach ($statAccuracies as $stat => $accuracies) {
            $statPredictionAccuracy[$stat] = count($accuracies) > 0
                ? round(array_sum($accuracies) / count($accuracies), 1)
                : 0.0;
        }

        $overallStatAccuracy = count($statPredictionAccuracy) > 0
            ? array_sum($statPredictionAccuracy) / count($statPredictionAccuracy)
            : 0.0;

        $racePredictionAccuracy = count($raceAccuracies) > 0
            ? round(array_sum($raceAccuracies) / count($raceAccuracies), 1)
            : 0.0;

        $overallAccuracy = ($overallStatAccuracy + $racePredictionAccuracy) / 2;

        // Check for degradation (compare recent vs older predictions)
        $recentCareers = $careers->take(5);
        $olderCareers = $careers->skip(5)->take(5);

        $recentAccuracy = $this->calculateAveragePredictionAccuracy($recentCareers);
        $olderAccuracy = $this->calculateAveragePredictionAccuracy($olderCareers);

        $degradationDetected = $olderAccuracy > 0 && ($recentAccuracy < $olderAccuracy - 5);

        $accuracyTrend = match (true) {
            $recentAccuracy > $olderAccuracy + 5 => 'improving',
            $degradationDetected => 'degrading',
            default => 'stable',
        };

        return [
            'overall_accuracy' => round($overallAccuracy, 1),
            'stat_prediction_accuracy' => $statPredictionAccuracy,
            'race_prediction_accuracy' => $racePredictionAccuracy,
            'accuracy_trend' => $accuracyTrend,
            'degradation_detected' => $degradationDetected,
        ];
    }

    /**
     * Generate specific update recommendations for ML models
     *
     * @param  Collection<int, Career>  $careers
     * @return array<string>
     */
    protected function generateUpdateRecommendations(Collection $careers): array
    {
        $recommendations = [];

        $modelPerformance = $this->assessModelPerformance($careers);

        // Check overall accuracy
        if ($modelPerformance['overall_accuracy'] < 70) {
            $recommendations[] = 'Model accuracy is below 70%. Consider retraining with recent data.';
        }

        // Check for degradation
        if ($modelPerformance['degradation_detected']) {
            $recommendations[] = 'Model performance degradation detected. Immediate retraining recommended.';
        }

        // Check individual stat accuracies
        foreach ($modelPerformance['stat_prediction_accuracy'] as $stat => $accuracy) {
            if ($accuracy < 60) {
                $recommendations[] = "Low accuracy for {$stat} predictions ({$accuracy}%). Review {$stat} training features.";
            }
        }

        // Check race prediction accuracy
        if ($modelPerformance['race_prediction_accuracy'] < 60) {
            $recommendations[] = 'Race prediction accuracy is low. Consider adding more race-specific features.';
        }

        // Check data volume
        if ($careers->count() < 20) {
            $recommendations[] = 'Limited training data available. Continue collecting career data before major model updates.';
        }

        // Check data recency
        /** @var Career|null $mostRecent */
        $mostRecent = $careers->first();
        $mostRecentCompletedAt = $mostRecent?->completed_at;
        if ($mostRecent && $mostRecentCompletedAt instanceof \Carbon\Carbon && $mostRecentCompletedAt->diffInDays(now()) > 30) {
            $recommendations[] = 'No recent career data. Model may not reflect current game meta.';
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Model performance is satisfactory. Continue monitoring.';
        }

        return $recommendations;
    }

    /**
     * Calculate feature importance based on correlation with success
     *
     * @param  Collection<int, Career>  $careers
     * @return array<string, float>
     */
    protected function calculateFeatureImportance(Collection $careers): array
    {
        /** @var array<string, array<float>> $features */
        $features = [
            'training_efficiency' => [],
            'friendship_training_rate' => [],
            'race_win_rate' => [],
            'total_stats' => [],
            'sp_efficiency' => [],
        ];

        /** @var array<int> $successLabels */
        $successLabels = [];

        /** @var Career $career */
        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;
            $races = $career->races;

            $isSuccessful = $this->isCareerSuccessful($career) ? 1 : 0;
            $successLabels[] = $isSuccessful;

            // Training efficiency
            $features['training_efficiency'][] = $this->calculateCareerEfficiencyFromSessions($sessions);

            // Friendship training rate
            $friendshipCount = $sessions->where('friendship_training', true)->count();
            $features['friendship_training_rate'][] = $sessions->count() > 0
                ? ($friendshipCount / $sessions->count()) * 100
                : 0;

            // Race win rate
            $features['race_win_rate'][] = $this->calculateWinRate($races);

            // Total stats
            $features['total_stats'][] = $this->calculateTotalStats($career);

            // SP efficiency
            /** @var int|float $sessionsSpSum */
            $sessionsSpSum = $sessions->sum('sp_gain');
            /** @var int|float $racesSpSum */
            $racesSpSum = $races->sum('sp_reward');
            $totalSp = (int) $sessionsSpSum + (int) $racesSpSum;
            $features['sp_efficiency'][] = $sessions->count() > 0 ? $totalSp / $sessions->count() : 0;
        }

        // Calculate correlation with success for each feature
        $importance = [];
        foreach ($features as $featureName => $values) {
            $correlation = $this->calculateCorrelation($values, $successLabels);
            $importance[$featureName] = round(abs($correlation) * 100, 1);
        }

        // Sort by importance
        arsort($importance);

        return $importance;
    }

    /**
     * Assess data quality for ML training
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     completeness: float,
     *     consistency: float,
     *     recency: float,
     *     volume_adequacy: string,
     *     issues: array<string>
     * }
     */
    protected function assessDataQuality(Collection $careers): array
    {
        $issues = [];

        // Completeness: Check for missing data
        $totalFields = 0;
        $missingFields = 0;

        /** @var Career $career */
        foreach ($careers as $career) {
            $totalFields += 10; // Expected fields per career

            if (! $career->final_speed) {
                $missingFields++;
            }
            if (! $career->final_stamina) {
                $missingFields++;
            }
            if (! $career->final_power) {
                $missingFields++;
            }
            if (! $career->final_guts) {
                $missingFields++;
            }
            if (! $career->final_wit) {
                $missingFields++;
            }
            if ($career->trainingSessions->isEmpty()) {
                $missingFields += 3;
            }
            if ($career->races->isEmpty()) {
                $missingFields += 2;
            }
        }

        $completeness = $totalFields > 0 ? ((($totalFields - $missingFields) / $totalFields) * 100) : 0;

        if ($completeness < 80) {
            $issues[] = 'Data completeness is below 80%. Some careers have missing information.';
        }

        // Consistency: Check for outliers
        $scores = [];
        /** @var Career $careerForScore */
        foreach ($careers as $careerForScore) {
            $scores[] = $this->calculateCareerScore($careerForScore);
        }

        $mean = count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
        $stdDev = sqrt($this->calculateVariance($scores));
        $outliers = 0;

        foreach ($scores as $score) {
            if (abs($score - $mean) > 2 * $stdDev) {
                $outliers++;
            }
        }

        $consistency = count($scores) > 0 ? ((count($scores) - $outliers) / count($scores)) * 100 : 100;

        if ($outliers > 0) {
            $issues[] = "{$outliers} career(s) detected as outliers. Review for data entry errors.";
        }

        // Recency: Check how recent the data is
        $recentCareers = $careers->filter(function (Career $career): bool {
            $completedAt = $career->completed_at;

            return $completedAt instanceof \Carbon\Carbon && $completedAt->diffInDays(now()) <= 30;
        })->count();

        $recency = $careers->count() > 0 ? ($recentCareers / $careers->count()) * 100 : 0;

        if ($recency < 20) {
            $issues[] = 'Less than 20% of data is from the last 30 days. Consider collecting more recent data.';
        }

        // Volume adequacy
        $volumeAdequacy = match (true) {
            $careers->count() >= 50 => 'excellent',
            $careers->count() >= 30 => 'good',
            $careers->count() >= 15 => 'moderate',
            $careers->count() >= 5 => 'minimal',
            default => 'insufficient',
        };

        return [
            'completeness' => round($completeness, 1),
            'consistency' => round($consistency, 1),
            'recency' => round($recency, 1),
            'volume_adequacy' => $volumeAdequacy,
            'issues' => $issues,
        ];
    }

    /**
     * Determine retraining priority based on all factors
     *
     * @param  Collection<int, Career>  $careers
     */
    protected function determineRetrainingPriority(Collection $careers): string
    {
        $modelPerformance = $this->assessModelPerformance($careers);
        $dataQuality = $this->assessDataQuality($careers);

        $urgencyScore = 0;

        // Model performance factors
        if ($modelPerformance['degradation_detected']) {
            $urgencyScore += 30;
        }
        if ($modelPerformance['overall_accuracy'] < 60) {
            $urgencyScore += 25;
        } elseif ($modelPerformance['overall_accuracy'] < 70) {
            $urgencyScore += 15;
        }

        // Data quality factors
        if ($dataQuality['completeness'] < 70) {
            $urgencyScore += 10;
        }
        if ($dataQuality['recency'] < 30) {
            $urgencyScore += 15;
        }

        // Volume factor
        if ($careers->count() >= 30 && $dataQuality['recency'] > 50) {
            $urgencyScore += 10; // Good opportunity to retrain
        }

        return match (true) {
            $urgencyScore >= 50 => 'critical',
            $urgencyScore >= 30 => 'high',
            $urgencyScore >= 15 => 'medium',
            default => 'low',
        };
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Calculate average efficiency for a collection of careers
     *
     * @param  Collection<int, Career>  $careers
     */
    protected function calculateAverageEfficiency(Collection $careers): float
    {
        if ($careers->isEmpty()) {
            return 0.0;
        }

        $totalEfficiency = 0.0;
        $count = 0;

        /** @var Career $career */
        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;
            if ($sessions->isNotEmpty()) {
                $totalEfficiency += $this->calculateCareerEfficiencyFromSessions($sessions);
                $count++;
            }
        }

        return $count > 0 ? round($totalEfficiency / $count, 1) : 0.0;
    }

    /**
     * Calculate career efficiency from training sessions
     *
     * @param  Collection<int, \App\Models\TrainingSession>|iterable<\App\Models\TrainingSession>  $sessions
     */
    protected function calculateCareerEfficiencyFromSessions(mixed $sessions): float
    {
        if (! ($sessions instanceof Collection)) {
            /** @var array<int, \App\Models\TrainingSession> $sessionsArray */
            $sessionsArray = is_iterable($sessions) ? iterator_to_array($sessions) : [];
            $sessions = collect($sessionsArray);
        }

        if ($sessions->isEmpty()) {
            return 0.0;
        }

        $totalGains = 0;
        /** @var \App\Models\TrainingSession $session */
        foreach ($sessions as $session) {
            $totalGains += (int) ($session->speed_gain ?? 0)
                + (int) ($session->stamina_gain ?? 0)
                + (int) ($session->power_gain ?? 0)
                + (int) ($session->guts_gain ?? 0)
                + (int) ($session->wit_gain ?? 0);
        }

        $idealTotal = $sessions->count() * 30;

        return $idealTotal > 0 ? min(100, round(($totalGains / $idealTotal) * 100, 1)) : 0.0;
    }

    /**
     * Calculate career score (composite metric)
     */
    protected function calculateCareerScore(Career $career): float
    {
        $sessions = $career->trainingSessions;
        $races = $career->races;

        $efficiency = $this->calculateCareerEfficiencyFromSessions($sessions);
        $winRate = $this->calculateWinRate($races);
        $totalStats = $this->calculateTotalStats($career);

        // Normalize total stats (max ~6000 for all stats at 1200)
        $normalizedStats = min(100, ($totalStats / 5000) * 100);

        // Weighted composite score
        return round(($efficiency * 0.4) + ($winRate * 0.3) + ($normalizedStats * 0.3), 1);
    }

    /**
     * Calculate total stats from career
     */
    protected function calculateTotalStats(Career $career): int
    {
        return ($career->final_speed ?? 0)
            + ($career->final_stamina ?? 0)
            + ($career->final_power ?? 0)
            + ($career->final_guts ?? 0)
            + ($career->final_wit ?? 0);
    }

    /**
     * Calculate win rate from races
     *
     * @param  Collection<int, Race>|iterable<Race>  $races
     */
    protected function calculateWinRate(mixed $races): float
    {
        if (! ($races instanceof Collection)) {
            /** @var array<int, Race> $racesArray */
            $racesArray = is_iterable($races) ? iterator_to_array($races) : [];
            $races = collect($racesArray);
        }

        if ($races->isEmpty()) {
            return 0.0;
        }

        $wins = $races->where('won_race', true)->count();

        return round(($wins / $races->count()) * 100, 1);
    }

    /**
     * Calculate consistency score based on variance
     *
     * @param  Collection<int, Career>  $careers
     */
    protected function calculateConsistencyScore(Collection $careers): float
    {
        $scores = [];
        /** @var Career $career */
        foreach ($careers as $career) {
            $scores[] = $this->calculateCareerScore($career);
        }

        if (count($scores) < 2) {
            return 100.0;
        }

        $variance = $this->calculateVariance($scores);

        // Convert variance to consistency score (lower variance = higher consistency)
        return max(0, min(100, 100 - sqrt($variance)));
    }

    /**
     * Calculate variance of an array
     *
     * @param  array<float|int>  $values
     */
    protected function calculateVariance(array $values): float
    {
        if (count($values) < 2) {
            return 0.0;
        }

        $mean = array_sum($values) / count($values);

        return array_sum(array_map(fn ($v) => pow($v - $mean, 2), $values)) / count($values);
    }

    /**
     * Calculate moving average
     *
     * @param  array<int, float>  $values
     * @return array<int, float>
     */
    protected function calculateMovingAverage(array $values, int $window): array
    {
        $result = [];
        $keys = array_keys($values);
        $numericValues = array_values($values);

        for ($i = 0; $i < count($numericValues); $i++) {
            $start = max(0, $i - $window + 1);
            $windowValues = array_slice($numericValues, $start, $i - $start + 1);
            $result[$keys[$i]] = round(array_sum($windowValues) / count($windowValues), 1);
        }

        return $result;
    }

    /**
     * Calculate Pearson correlation coefficient
     *
     * @param  array<float|int>  $x
     * @param  array<float|int>  $y
     */
    protected function calculateCorrelation(array $x, array $y): float
    {
        $n = min(count($x), count($y));
        if ($n < 2) {
            return 0.0;
        }

        $x = array_slice($x, 0, $n);
        $y = array_slice($y, 0, $n);

        $meanX = array_sum($x) / $n;
        $meanY = array_sum($y) / $n;

        $numerator = 0;
        $denomX = 0;
        $denomY = 0;

        for ($i = 0; $i < $n; $i++) {
            $diffX = $x[$i] - $meanX;
            $diffY = $y[$i] - $meanY;
            $numerator += $diffX * $diffY;
            $denomX += $diffX * $diffX;
            $denomY += $diffY * $diffY;
        }

        $denominator = sqrt($denomX * $denomY);

        return $denominator > 0 ? $numerator / $denominator : 0.0;
    }

    /**
     * Determine if a career was successful
     */
    protected function isCareerSuccessful(Career $career): bool
    {
        $analysis = $career->performance_analysis ?? [];

        if (isset($analysis['goals_achieved'])) {
            return $analysis['goals_achieved'] === true;
        }

        // Check efficiency as fallback
        $sessions = $career->trainingSessions;
        $efficiency = $this->calculateCareerEfficiencyFromSessions($sessions);

        return $efficiency >= 70;
    }

    /**
     * Determine character type based on final stats
     */
    protected function determineCharacterType(Career $career): string
    {
        $stats = [
            'speed' => $career->final_speed ?? 0,
            'stamina' => $career->final_stamina ?? 0,
            'power' => $career->final_power ?? 0,
            'guts' => $career->final_guts ?? 0,
            'wit' => $career->final_wit ?? 0,
        ];

        arsort($stats);
        $topStat = array_key_first($stats);

        return $topStat.'_focused';
    }

    /**
     * Calculate average metrics for a collection of careers
     *
     * @param  Collection<int, Career>  $careers
     * @return array{efficiency: float, training_distribution: array<string, float>, win_rate: float}
     */
    protected function calculateAverageMetrics(Collection $careers): array
    {
        if ($careers->isEmpty()) {
            return [
                'efficiency' => 0.0,
                'training_distribution' => array_fill_keys(self::STAT_TYPES, 0.0),
                'win_rate' => 0.0,
            ];
        }

        $efficiencies = [];
        /** @var array<string, array<float>> $distributions */
        $distributions = array_fill_keys(self::STAT_TYPES, []);
        $winRates = [];

        /** @var Career $career */
        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;
            $races = $career->races;

            $efficiencies[] = $this->calculateCareerEfficiencyFromSessions($sessions);
            $winRates[] = $this->calculateWinRate($races);

            // Training distribution
            $total = $sessions->count();
            if ($total > 0) {
                foreach (self::STAT_TYPES as $stat) {
                    $count = $sessions->where('training_type', $stat)->count();
                    $distributions[$stat][] = ($count / $total) * 100;
                }
            }
        }

        $avgDistribution = [];
        foreach ($distributions as $stat => $values) {
            $avgDistribution[$stat] = count($values) > 0 ? round(array_sum($values) / count($values), 1) : 0.0;
        }

        return [
            'efficiency' => count($efficiencies) > 0 ? round(array_sum($efficiencies) / count($efficiencies), 1) : 0.0,
            'training_distribution' => $avgDistribution,
            'win_rate' => count($winRates) > 0 ? round(array_sum($winRates) / count($winRates), 1) : 0.0,
        ];
    }

    /**
     * Generate success recommendations based on identified factors
     *
     * @param  array<string, float>  $highImpactFactors
     * @param  array<string, float>  $trainingCorrelations
     * @return array<string>
     */
    protected function generateSuccessRecommendations(array $highImpactFactors, array $trainingCorrelations): array
    {
        $recommendations = [];

        if (isset($highImpactFactors['training_efficiency']) && $highImpactFactors['training_efficiency'] > 0) {
            $recommendations[] = 'Focus on improving training efficiency - it strongly correlates with success.';
        }

        foreach ($trainingCorrelations as $training => $correlation) {
            if ($correlation > 5) {
                $stat = str_replace('_training', '', $training);
                $recommendations[] = "Successful careers tend to have more {$stat} training sessions.";
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Continue current strategies while monitoring performance trends.';
        }

        return array_slice($recommendations, 0, 5);
    }

    /**
     * Calculate average prediction accuracy for a collection of careers
     *
     * @param  Collection<int, Career>  $careers
     */
    protected function calculateAveragePredictionAccuracy(Collection $careers): float
    {
        $accuracies = [];

        /** @var Career $career */
        foreach ($careers as $career) {
            foreach ($career->trainingSessions as $session) {
                $predictions = $session->prediction_metadata ?? [];
                if (! empty($predictions)) {
                    foreach (self::STAT_TYPES as $stat) {
                        $predicted = $predictions["predicted_{$stat}"] ?? null;
                        $actual = $session->{"{$stat}_gain"} ?? 0;

                        if ($predicted !== null) {
                            $accuracy = 100 - min(100, abs($predicted - $actual) * 5);
                            $accuracies[] = max(0, $accuracy);
                        }
                    }
                }
            }
        }

        return count($accuracies) > 0 ? round(array_sum($accuracies) / count($accuracies), 1) : 0.0;
    }

    /**
     * Get result for insufficient data
     *
     * @return array{error: string, message: string, current_sample_size: int, required_sample_size: int}
     */
    protected function getInsufficientDataResult(string $analysisType, int $currentSize): array
    {
        return [
            'error' => 'insufficient_data',
            'message' => 'At least '.self::MIN_SAMPLE_SIZE." completed careers are required for {$analysisType} analysis.",
            'current_sample_size' => $currentSize,
            'required_sample_size' => self::MIN_SAMPLE_SIZE,
        ];
    }

    /**
     * Clear all historical tracking cache for a user
     */
    public function clearCache(User $user): void
    {
        Cache::forget("historical:long_term_trends:{$user->id}");
        Cache::forget("historical:success_rates:{$user->id}");
        Cache::forget("historical:ml_recommendations:{$user->id}");
    }
}
