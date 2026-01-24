<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Career;
use App\Models\Race;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Career Comparison Service
 *
 * Provides multi-career comparison functionality including:
 * - Side-by-side career comparison with key metrics
 * - Pattern identification for successful decision sequences
 * - Success factor analysis across multiple career runs
 * - Statistical significance testing for pattern validation
 *
 * Requirements: 15.1, 15.2 (Task 5.2.2)
 */
class CareerComparisonService
{
    /**
     * Cache TTL for comparison results (30 minutes)
     */
    protected const CACHE_TTL = 1800;

    /**
     * Stat types for analysis
     *
     * @var array<string>
     */
    protected const STAT_TYPES = ['speed', 'stamina', 'power', 'guts', 'wit'];

    /**
     * Career phases for pattern analysis
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
     * Significance level for statistical tests (alpha = 0.05)
     */
    protected const SIGNIFICANCE_LEVEL = 0.05;

    // =========================================================================
    // SIDE-BY-SIDE CAREER COMPARISON
    // =========================================================================

    /**
     * Compare multiple careers side-by-side with comprehensive metrics
     *
     * @param  array<int>  $careerIds  Array of career IDs to compare
     * @return array{
     *     careers: array<array<string, mixed>>,
     *     comparison_summary: array<string, mixed>,
     *     stat_comparison: array<string, array<string, mixed>>,
     *     training_comparison: array<string, mixed>,
     *     race_comparison: array<string, mixed>,
     *     key_differences: array<string>,
     *     best_performer: array<string, mixed>
     * }
     */
    public function compareCareers(array $careerIds): array
    {
        if (count($careerIds) < 2) {
            return $this->getEmptyComparisonResult('At least 2 careers required for comparison');
        }

        $cacheKey = 'career_comparison:'.md5(implode(',', $careerIds));

        /** @var array{careers: array<array<string, mixed>>, comparison_summary: array<string, mixed>, stat_comparison: array<string, array<string, mixed>>, training_comparison: array<string, mixed>, race_comparison: array<string, mixed>, key_differences: array<string>, best_performer: array<string, mixed>} $cached */
        $cached = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($careerIds): array {
            $careers = Career::with(['character', 'trainingSessions', 'races'])
                ->whereIn('id', $careerIds)
                ->get();

            if ($careers->count() < 2) {
                return $this->getEmptyComparisonResult('Could not find enough careers to compare');
            }

            // Build career data for each career
            $careerData = $this->buildCareerComparisonData($careers);

            // Calculate stat comparison
            $statComparison = $this->calculateStatComparison($careers);

            // Calculate training comparison
            $trainingComparison = $this->calculateTrainingComparison($careers);

            // Calculate race comparison
            $raceComparison = $this->calculateRaceComparison($careers);

            // Generate comparison summary
            $comparisonSummary = $this->generateComparisonSummary($careerData);

            // Identify key differences
            $keyDifferences = $this->identifyKeyDifferences($careerData, $statComparison, $trainingComparison);

            // Determine best performer
            $bestPerformer = $this->determineBestPerformer($careerData);

            return [
                'careers' => $careerData,
                'comparison_summary' => $comparisonSummary,
                'stat_comparison' => $statComparison,
                'training_comparison' => $trainingComparison,
                'race_comparison' => $raceComparison,
                'key_differences' => $keyDifferences,
                'best_performer' => $bestPerformer,
            ];
        });

        /** @var array{careers: array<array<string, mixed>>, comparison_summary: array<string, mixed>, stat_comparison: array<string, array<string, mixed>>, training_comparison: array<string, mixed>, race_comparison: array<string, mixed>, key_differences: array<string>, best_performer: array<string, mixed>} $result */
        $result = $cached;

        return $result;
    }

    /**
     * Build comprehensive comparison data for each career
     *
     * @param  Collection<int, Career>  $careers
     * @return array<int, array<string, mixed>>
     */
    protected function buildCareerComparisonData(Collection $careers): array
    {
        $careerData = [];

        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;
            $races = $career->races;

            // Calculate final stats from performance analysis or last session
            $finalStats = $this->extractFinalStats($career);

            // Calculate efficiency metrics
            $efficiency = $this->calculateCareerEfficiency($sessions);

            // Calculate race performance
            $racePerformance = $this->calculateRacePerformance($races);

            $careerData[] = [
                'career_id' => $career->id,
                'career_name' => $career->career_name ?? "Career #{$career->id}",
                'character_name' => $career->character->name ?? 'Unknown',
                'scenario_type' => $career->scenario_type,
                'status' => $career->status,
                'started_at' => $career->started_at instanceof \DateTimeInterface ? $career->started_at->format('Y-m-d') : null,
                'completed_at' => $career->completed_at instanceof \DateTimeInterface ? $career->completed_at->format('Y-m-d') : null,
                'total_turns' => $career->current_turn ?? $sessions->max('turn_number') ?? 0,
                'final_stats' => $finalStats,
                'total_stat_points' => (int) array_sum($finalStats),
                'efficiency_rating' => $efficiency['rating'],
                'training_sessions_count' => $sessions->count(),
                'races_count' => $races->count(),
                'race_win_rate' => $racePerformance['win_rate'],
                'average_finish_position' => $racePerformance['avg_position'],
                'total_sp_earned' => (is_numeric($sessions->sum('sp_gain')) ? (int) $sessions->sum('sp_gain') : 0) + (is_numeric($races->sum('sp_reward')) ? (int) $races->sum('sp_reward') : 0),
                'skills_acquired' => $this->countSkillsAcquired($sessions),
                'training_failures' => $sessions->where('training_failed', true)->count(),
                'injuries' => $sessions->where('injury_occurred', true)->count(),
            ];
        }

        return $careerData;
    }

    /**
     * Extract final stats from career
     *
     * @return array<string, int>
     */
    protected function extractFinalStats(Career $career): array
    {
        // Try to get from performance analysis first
        /** @var array<string, mixed> $analysis */
        $analysis = $career->performance_analysis ?? [];
        if (isset($analysis['final_stats']) && is_array($analysis['final_stats'])) {
            /** @var array<string, int> $finalStats */
            $finalStats = $analysis['final_stats'];

            return $finalStats;
        }

        // Fall back to last training session stats
        $lastSession = $career->trainingSessions()
            ->orderBy('turn_number', 'desc')
            ->first();

        if ($lastSession) {
            return [
                'speed' => $lastSession->speed_gain ?? 0,
                'stamina' => $lastSession->stamina_gain ?? 0,
                'power' => $lastSession->power_gain ?? 0,
                'guts' => $lastSession->guts_gain ?? 0,
                'wit' => $lastSession->wit_gain ?? 0,
            ];
        }

        return array_fill_keys(self::STAT_TYPES, 0);
    }

    /**
     * Calculate career efficiency metrics
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array{rating: float, avg_gain_per_turn: float, failure_rate: float}
     */
    protected function calculateCareerEfficiency(Collection $sessions): array
    {
        if ($sessions->isEmpty()) {
            return ['rating' => 0.0, 'avg_gain_per_turn' => 0.0, 'failure_rate' => 0.0];
        }

        $totalGains = 0;
        $turnCount = $sessions->count();
        $failures = $sessions->where('training_failed', true)->count();

        foreach ($sessions as $session) {
            $totalGains += ($session->speed_gain ?? 0)
                + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0)
                + ($session->guts_gain ?? 0)
                + ($session->wit_gain ?? 0);
        }

        $avgGainPerTurn = $turnCount > 0 ? round($totalGains / $turnCount, 2) : 0.0;

        // Ideal average is ~30 stat points per turn
        $idealTotal = $turnCount * 30;
        $rating = $idealTotal > 0 ? min(100, round(($totalGains / $idealTotal) * 100, 1)) : 0.0;

        $failureRate = $turnCount > 0 ? round(($failures / $turnCount) * 100, 1) : 0.0;

        return [
            'rating' => $rating,
            'avg_gain_per_turn' => $avgGainPerTurn,
            'failure_rate' => $failureRate,
        ];
    }

    /**
     * Calculate race performance metrics
     *
     * @param  Collection<int, Race>  $races
     * @return array{win_rate: float, avg_position: float, total_races: int}
     */
    protected function calculateRacePerformance(Collection $races): array
    {
        if ($races->isEmpty()) {
            return ['win_rate' => 0.0, 'avg_position' => 0.0, 'total_races' => 0];
        }

        $totalRaces = $races->count();
        $wins = $races->where('won_race', true)->count();
        $sumPosition = $races->sum('finish_position');
        $totalPosition = is_numeric($sumPosition) ? (float) $sumPosition : 0.0;

        return [
            'win_rate' => round(($wins / $totalRaces) * 100, 1),
            'avg_position' => $totalRaces > 0 ? round($totalPosition / $totalRaces, 2) : 0.0,
            'total_races' => $totalRaces,
        ];
    }

    /**
     * Count skills acquired during career
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     */
    protected function countSkillsAcquired(Collection $sessions): int
    {
        $totalSkills = 0;

        foreach ($sessions as $session) {
            /** @var array<mixed> $hints */
            $hints = $session->skill_hints_obtained ?? [];
            $totalSkills += is_array($hints) ? count($hints) : 0;
        }

        return $totalSkills;
    }

    /**
     * Calculate stat comparison across careers
     *
     * @param  Collection<int, Career>  $careers
     * @return array<string, array{min: int, max: int, avg: float, std_dev: float, values: array<int, int>}>
     */
    protected function calculateStatComparison(Collection $careers): array
    {
        $comparison = [];

        foreach (self::STAT_TYPES as $stat) {
            $values = [];

            foreach ($careers as $career) {
                $finalStats = $this->extractFinalStats($career);
                $values[$career->id] = $finalStats[$stat] ?? 0;
            }

            $numericValues = array_values($values);

            if (count($numericValues) > 0) {
                $avg = array_sum($numericValues) / count($numericValues);
                $variance = count($numericValues) > 1
                    ? array_sum(array_map(fn ($v) => pow($v - $avg, 2), $numericValues)) / count($numericValues)
                    : 0;

                $comparison[$stat] = [
                    'min' => min($numericValues),
                    'max' => max($numericValues),
                    'avg' => round($avg, 1),
                    'std_dev' => round(sqrt($variance), 2),
                    'range' => max($numericValues) - min($numericValues),
                    'values' => $values,
                ];
            }
        }

        return $comparison;
    }

    /**
     * Calculate training comparison across careers
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     training_type_distribution: array<int, array<string, int>>,
     *     efficiency_comparison: array<int, float>,
     *     phase_comparison: array<string, array<int, array<string, mixed>>>
     * }
     */
    protected function calculateTrainingComparison(Collection $careers): array
    {
        $typeDistribution = [];
        $efficiencyComparison = [];
        $phaseComparison = [];

        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;

            // Training type distribution
            $typeDistribution[$career->id] = $this->calculateTrainingTypeDistribution($sessions);

            // Efficiency by career
            $efficiency = $this->calculateCareerEfficiency($sessions);
            $efficiencyComparison[$career->id] = $efficiency['rating'];

            // Phase-by-phase comparison
            foreach (self::CAREER_PHASES as $phase => $range) {
                $phaseSessions = $sessions->filter(function ($s) use ($range) {
                    $turn = $s->turn_number ?? 0;

                    return $turn >= $range['min'] && $turn <= $range['max'];
                });

                if (! isset($phaseComparison[$phase])) {
                    $phaseComparison[$phase] = [];
                }

                $phaseComparison[$phase][$career->id] = [
                    'sessions' => $phaseSessions->count(),
                    'efficiency' => $this->calculateCareerEfficiency($phaseSessions)['rating'],
                    'avg_gains' => $this->calculateAverageGains($phaseSessions),
                ];
            }
        }

        return [
            'training_type_distribution' => $typeDistribution,
            'efficiency_comparison' => $efficiencyComparison,
            'phase_comparison' => $phaseComparison,
        ];
    }

    /**
     * Calculate training type distribution
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array<string, int>
     */
    protected function calculateTrainingTypeDistribution(Collection $sessions): array
    {
        $distribution = array_fill_keys(['speed', 'stamina', 'power', 'guts', 'wit', 'rest', 'other'], 0);

        foreach ($sessions as $session) {
            $type = $session->training_type ?? 'other';
            if (isset($distribution[$type])) {
                $distribution[$type]++;
            } else {
                $distribution['other']++;
            }
        }

        return $distribution;
    }

    /**
     * Calculate average stat gains from sessions
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array<string, float>
     */
    protected function calculateAverageGains(Collection $sessions): array
    {
        if ($sessions->isEmpty()) {
            return array_fill_keys(self::STAT_TYPES, 0.0);
        }

        $totals = array_fill_keys(self::STAT_TYPES, 0);
        $count = $sessions->count();

        foreach ($sessions as $session) {
            foreach (self::STAT_TYPES as $stat) {
                $totals[$stat] += $session->{"{$stat}_gain"} ?? 0;
            }
        }

        return array_map(fn ($total) => round($total / $count, 2), $totals);
    }

    /**
     * Calculate race comparison across careers
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     win_rates: array<int, float>,
     *     avg_positions: array<int, float>,
     *     race_grade_performance: array<int, array<string, array{count: int, wins: int, win_rate: float}>>
     * }
     */
    protected function calculateRaceComparison(Collection $careers): array
    {
        $winRates = [];
        $avgPositions = [];
        $gradePerformance = [];

        foreach ($careers as $career) {
            $races = $career->races;
            $performance = $this->calculateRacePerformance($races);

            $winRates[$career->id] = $performance['win_rate'];
            $avgPositions[$career->id] = $performance['avg_position'];

            // Performance by race grade
            $gradePerformance[$career->id] = $this->calculateRaceGradePerformance($races);
        }

        return [
            'win_rates' => $winRates,
            'avg_positions' => $avgPositions,
            'race_grade_performance' => $gradePerformance,
        ];
    }

    /**
     * Calculate performance by race grade
     *
     * @param  Collection<int, Race>  $races
     * @return array<string, array{count: int, wins: int, win_rate: float}>
     */
    protected function calculateRaceGradePerformance(Collection $races): array
    {
        $grades = ['G1', 'G2', 'G3', 'OP', 'Pre-OP'];
        $performance = [];

        foreach ($grades as $grade) {
            $gradeRaces = $races->where('race_grade', $grade);
            $count = $gradeRaces->count();
            $wins = $gradeRaces->where('won_race', true)->count();

            $performance[$grade] = [
                'count' => $count,
                'wins' => $wins,
                'win_rate' => $count > 0 ? round(($wins / $count) * 100, 1) : 0.0,
            ];
        }

        return $performance;
    }

    /**
     * Generate comparison summary
     *
     * @param  array<int, array<string, mixed>>  $careerData
     * @return array{
     *     total_careers: int,
     *     avg_efficiency: float,
     *     avg_total_stats: float,
     *     avg_win_rate: float,
     *     efficiency_variance: float,
     *     stat_variance: float
     * }
     */
    protected function generateComparisonSummary(array $careerData): array
    {
        $count = count($careerData);

        if ($count === 0) {
            return [
                'total_careers' => 0,
                'avg_efficiency' => 0.0,
                'avg_total_stats' => 0.0,
                'avg_win_rate' => 0.0,
                'efficiency_variance' => 0.0,
                'stat_variance' => 0.0,
            ];
        }

        /** @var array<float|int> $efficiencies */
        $efficiencies = array_column($careerData, 'efficiency_rating');
        /** @var array<float|int> $totalStats */
        $totalStats = array_column($careerData, 'total_stat_points');
        /** @var array<float|int> $winRates */
        $winRates = array_column($careerData, 'race_win_rate');

        $avgEfficiency = array_sum($efficiencies) / $count;
        $avgTotalStats = array_sum($totalStats) / $count;
        $avgWinRate = array_sum($winRates) / $count;

        // Calculate variances
        $efficiencyVariance = $this->calculateVariance($efficiencies);
        $statVariance = $this->calculateVariance($totalStats);

        return [
            'total_careers' => $count,
            'avg_efficiency' => round($avgEfficiency, 1),
            'avg_total_stats' => round($avgTotalStats, 1),
            'avg_win_rate' => round($avgWinRate, 1),
            'efficiency_variance' => round($efficiencyVariance, 2),
            'stat_variance' => round($statVariance, 2),
        ];
    }

    /**
     * Calculate variance of an array of values
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
     * Identify key differences between careers
     *
     * @param  array<int, array<string, mixed>>  $careerData
     * @param  array<string, array<string, mixed>>  $statComparison
     * @param  array<string, mixed>  $trainingComparison
     * @return array<string>
     */
    protected function identifyKeyDifferences(array $careerData, array $statComparison, array $trainingComparison = []): array
    {
        $differences = [];

        // Check stat differences
        foreach ($statComparison as $stat => $data) {
            $range = is_numeric($data['range'] ?? null) ? (int) $data['range'] : 0;
            if ($range > 100) {
                $differences[] = ucfirst($stat)." varies significantly ({$range} point range)";
            }
        }

        // Check efficiency differences
        /** @var array<float|int> $efficiencies */
        $efficiencies = array_column($careerData, 'efficiency_rating');
        if (count($efficiencies) > 0) {
            $efficiencyRange = (float) max($efficiencies) - (float) min($efficiencies);
            if ($efficiencyRange > 15) {
                $differences[] = "Training efficiency varies by {$efficiencyRange}%";
            }
        }

        // Check win rate differences
        /** @var array<float|int> $winRates */
        $winRates = array_column($careerData, 'race_win_rate');
        if (count($winRates) > 0) {
            $winRateRange = (float) max($winRates) - (float) min($winRates);
            if ($winRateRange > 20) {
                $differences[] = "Race win rate varies by {$winRateRange}%";
            }
        }

        // Check training type distribution differences
        if (isset($trainingComparison['training_type_distribution']) && is_array($trainingComparison['training_type_distribution'])) {
            /** @var array<int, array<string, int>> $distributions */
            $distributions = $trainingComparison['training_type_distribution'];
            foreach (['speed', 'stamina', 'power'] as $type) {
                $typeCounts = array_map(fn ($d) => $d[$type] ?? 0, $distributions);
                if (count($typeCounts) > 0) {
                    $typeRange = max($typeCounts) - min($typeCounts);
                    if ($typeRange > 10) {
                        $differences[] = ucfirst($type).' training frequency varies significantly';
                    }
                }
            }
        }

        return array_slice($differences, 0, 5);
    }

    /**
     * Determine the best performing career
     *
     * @param  array<int, array<string, mixed>>  $careerData
     * @return array{career_id: int, career_name: string, score: float, strengths: array<string>}
     */
    protected function determineBestPerformer(array $careerData): array
    {
        if (empty($careerData)) {
            return ['career_id' => 0, 'career_name' => 'N/A', 'score' => 0.0, 'strengths' => []];
        }

        $scores = [];

        foreach ($careerData as $career) {
            // Calculate composite score (weighted)
            $effRating = is_numeric($career['efficiency_rating'] ?? null) ? (float) $career['efficiency_rating'] : 0.0;
            $winRate = is_numeric($career['race_win_rate'] ?? null) ? (float) $career['race_win_rate'] : 0.0;
            $totalStats = is_numeric($career['total_stat_points'] ?? null) ? (float) $career['total_stat_points'] : 0.0;
            $failures = is_int($career['training_failures'] ?? null) ? $career['training_failures'] : 0;

            $score = ($effRating * 0.3)
                + ($winRate * 0.3)
                + (min(100.0, $totalStats / 50) * 0.25)
                + ((100 - ($failures * 5)) * 0.15);

            $careerId = is_int($career['career_id'] ?? null) ? $career['career_id'] : 0;
            $scores[$careerId] = [
                'career' => $career,
                'score' => $score,
            ];
        }

        // Find best performer
        $best = null;
        $bestScore = -1;

        foreach ($scores as $careerId => $data) {
            if ($data['score'] > $bestScore) {
                $bestScore = $data['score'];
                $best = $data['career'];
            }
        }

        // Identify strengths
        $strengths = [];
        if ($best) {
            $avgEfficiency = array_sum(array_column($careerData, 'efficiency_rating')) / count($careerData);
            $avgWinRate = array_sum(array_column($careerData, 'race_win_rate')) / count($careerData);

            if ($best['efficiency_rating'] > $avgEfficiency + 5) {
                $strengths[] = 'High training efficiency';
            }
            if ($best['race_win_rate'] > $avgWinRate + 10) {
                $strengths[] = 'Strong race performance';
            }
            if ($best['training_failures'] === 0) {
                $strengths[] = 'No training failures';
            }
            if ($best['skills_acquired'] > 10) {
                $strengths[] = 'Good skill acquisition';
            }
        }

        return [
            'career_id' => is_int($best['career_id'] ?? null) ? $best['career_id'] : 0,
            'career_name' => is_string($best['career_name'] ?? null) ? $best['career_name'] : 'N/A',
            'score' => round($bestScore, 1),
            'strengths' => $strengths,
        ];
    }

    // =========================================================================
    // PATTERN IDENTIFICATION FOR SUCCESSFUL DECISION SEQUENCES
    // =========================================================================

    /**
     * Identify successful decision patterns across multiple careers
     *
     * @param  array<int>  $careerIds
     * @return array{
     *     training_patterns: array<string, mixed>,
     *     race_strategy_patterns: array<string, mixed>,
     *     decision_sequences: array<string, mixed>,
     *     success_correlations: array<string, array{correlation: float, strength: string}>,
     *     recommended_patterns: array<string>
     * }
     */
    public function identifySuccessPatterns(array $careerIds): array
    {
        $cacheKey = 'success_patterns:'.md5(implode(',', $careerIds));

        /** @var array{training_patterns: array<string, mixed>, race_strategy_patterns: array<string, mixed>, decision_sequences: array<string, mixed>, success_correlations: array<string, array{correlation: float, strength: string}>, recommended_patterns: array<string>} $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($careerIds) {
            $careers = Career::with(['trainingSessions', 'races'])
                ->whereIn('id', $careerIds)
                ->get();

            if ($careers->isEmpty()) {
                return $this->getEmptyPatternResult();
            }

            // Separate successful and unsuccessful careers
            $successfulCareers = $careers->filter(fn ($c) => $this->isCareerSuccessful($c));
            $unsuccessfulCareers = $careers->filter(fn ($c) => ! $this->isCareerSuccessful($c));

            // Identify training patterns
            $trainingPatterns = $this->identifyTrainingPatterns($successfulCareers, $unsuccessfulCareers);

            // Identify race strategy patterns
            $raceStrategyPatterns = $this->identifyRaceStrategyPatterns($successfulCareers, $unsuccessfulCareers);

            // Identify decision sequences
            $decisionSequences = $this->identifyDecisionSequences($successfulCareers);

            // Calculate success correlations
            $successCorrelations = $this->calculateSuccessCorrelations($careers);

            // Generate recommended patterns
            $recommendedPatterns = $this->generateRecommendedPatterns(
                $trainingPatterns,
                $raceStrategyPatterns,
                $successCorrelations
            );

            return [
                'training_patterns' => $trainingPatterns,
                'race_strategy_patterns' => $raceStrategyPatterns,
                'decision_sequences' => $decisionSequences,
                'success_correlations' => $successCorrelations,
                'recommended_patterns' => $recommendedPatterns,
            ];
        });

        return $result;
    }

    /**
     * Determine if a career was successful
     */
    protected function isCareerSuccessful(Career $career): bool
    {
        /** @var array<string, mixed> $analysis */
        $analysis = $career->performance_analysis ?? [];

        if (isset($analysis['goals_achieved'])) {
            return $analysis['goals_achieved'] === true;
        }

        // Check rating from performance analysis if available
        if (isset($analysis['rating']) && is_numeric($analysis['rating'])) {
            return (float) $analysis['rating'] >= 4;
        }

        // Default: check if completed with good efficiency
        if ($career->completed_at !== null) {
            $sessions = $career->trainingSessions;
            $efficiency = $this->calculateCareerEfficiency($sessions);

            return $efficiency['rating'] >= 70;
        }

        return false;
    }

    /**
     * Identify training patterns that correlate with success
     *
     * @param  Collection<int, Career>  $successfulCareers
     * @param  Collection<int, Career>  $unsuccessfulCareers
     * @return array{
     *     successful_patterns: array<string, mixed>,
     *     unsuccessful_patterns: array<string, mixed>,
     *     key_differences: array<int, array<string, mixed>>,
     *     phase_patterns: array<string, array<string, mixed>>
     * }
     */
    protected function identifyTrainingPatterns(Collection $successfulCareers, Collection $unsuccessfulCareers): array
    {
        $successfulPatterns = $this->extractTrainingPatterns($successfulCareers);
        $unsuccessfulPatterns = $this->extractTrainingPatterns($unsuccessfulCareers);

        // Identify key differences
        $keyDifferences = $this->comparePatterns($successfulPatterns, $unsuccessfulPatterns);

        // Analyze phase-specific patterns
        $phasePatterns = $this->analyzePhasePatterns($successfulCareers, $unsuccessfulCareers);

        return [
            'successful_patterns' => $successfulPatterns,
            'unsuccessful_patterns' => $unsuccessfulPatterns,
            'key_differences' => $keyDifferences,
            'phase_patterns' => $phasePatterns,
        ];
    }

    /**
     * Extract training patterns from a collection of careers
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     avg_training_distribution: array<string, float>,
     *     avg_efficiency: float,
     *     common_sequences: array<int, array{sequence: array<string>, frequency: int}>,
     *     friendship_training_rate: float
     * }
     */
    protected function extractTrainingPatterns(Collection $careers): array
    {
        if ($careers->isEmpty()) {
            return [
                'avg_training_distribution' => [],
                'avg_efficiency' => 0.0,
                'common_sequences' => [],
                'friendship_training_rate' => 0.0,
            ];
        }

        $distributions = [];
        $efficiencies = [];
        $friendshipRates = [];
        $sequences = [];

        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;

            // Training distribution
            $dist = $this->calculateTrainingTypeDistribution($sessions);
            $total = array_sum($dist);
            if ($total > 0) {
                foreach ($dist as $type => $count) {
                    $distributions[$type][] = ($count / $total) * 100;
                }
            }

            // Efficiency
            $efficiency = $this->calculateCareerEfficiency($sessions);
            $efficiencies[] = $efficiency['rating'];

            // Friendship training rate
            $friendshipCount = $sessions->where('friendship_training', true)->count();
            $friendshipRates[] = $sessions->count() > 0 ? ($friendshipCount / $sessions->count()) * 100 : 0;

            // Extract training sequences (first 10 turns)
            $earlySequence = $sessions->sortBy('turn_number')
                ->take(10)
                ->pluck('training_type')
                ->filter()
                ->values()
                ->toArray();
            if (! empty($earlySequence)) {
                /** @var array<int, string> $stringSequence */
                $stringSequence = array_map(fn ($v) => is_string($v) ? $v : '', $earlySequence);
                $sequences[] = $stringSequence;
            }
        }

        // Calculate averages
        $avgDistribution = [];
        foreach ($distributions as $type => $values) {
            $avgDistribution[$type] = round(array_sum($values) / count($values), 1);
        }

        return [
            'avg_training_distribution' => $avgDistribution,
            'avg_efficiency' => count($efficiencies) > 0 ? round(array_sum($efficiencies) / count($efficiencies), 1) : 0.0,
            'common_sequences' => $this->findCommonSequences($sequences),
            'friendship_training_rate' => count($friendshipRates) > 0 ? round(array_sum($friendshipRates) / count($friendshipRates), 1) : 0.0,
        ];
    }

    /**
     * Find common training sequences
     *
     * @param  array<int, array<int, string>>  $sequences
     * @return array<int, array{sequence: array<string>, frequency: int}>
     */
    protected function findCommonSequences(array $sequences): array
    {
        if (empty($sequences)) {
            return [];
        }

        // Find common 3-turn subsequences
        $subsequenceCounts = [];

        foreach ($sequences as $sequence) {
            for ($i = 0; $i <= count($sequence) - 3; $i++) {
                $subseq = array_slice($sequence, $i, 3);
                $key = implode('->', $subseq);
                $subsequenceCounts[$key] = ($subsequenceCounts[$key] ?? 0) + 1;
            }
        }

        // Sort by frequency and return top patterns
        arsort($subsequenceCounts);
        $topPatterns = array_slice($subsequenceCounts, 0, 5, true);

        $result = [];
        foreach ($topPatterns as $key => $count) {
            $result[] = [
                'sequence' => explode('->', $key),
                'frequency' => $count,
            ];
        }

        return $result;
    }

    /**
     * Compare patterns between successful and unsuccessful careers
     *
     * @param  array<string, mixed>  $successfulPatterns
     * @param  array<string, mixed>  $unsuccessfulPatterns
     * @return array<int, array{factor: string, successful_value: float, unsuccessful_value: float, difference: float}>
     */
    protected function comparePatterns(array $successfulPatterns, array $unsuccessfulPatterns): array
    {
        $differences = [];

        // Compare training distributions
        /** @var array<string, float> $successDist */
        $successDist = $successfulPatterns['avg_training_distribution'] ?? [];
        /** @var array<string, float> $unsuccessDist */
        $unsuccessDist = $unsuccessfulPatterns['avg_training_distribution'] ?? [];

        foreach (self::STAT_TYPES as $type) {
            $successVal = is_numeric($successDist[$type] ?? null) ? (float) $successDist[$type] : 0.0;
            $unsuccessVal = is_numeric($unsuccessDist[$type] ?? null) ? (float) $unsuccessDist[$type] : 0.0;
            $diff = $successVal - $unsuccessVal;

            if (abs($diff) > 5) {
                $differences[] = [
                    'factor' => ucfirst($type).' training %',
                    'successful_value' => $successVal,
                    'unsuccessful_value' => $unsuccessVal,
                    'difference' => round($diff, 1),
                ];
            }
        }

        // Compare efficiency
        $successEfficiency = is_numeric($successfulPatterns['avg_efficiency'] ?? null) ? (float) $successfulPatterns['avg_efficiency'] : 0.0;
        $unsuccessEfficiency = is_numeric($unsuccessfulPatterns['avg_efficiency'] ?? null) ? (float) $unsuccessfulPatterns['avg_efficiency'] : 0.0;
        $effDiff = $successEfficiency - $unsuccessEfficiency;
        if (abs($effDiff) > 5) {
            $differences[] = [
                'factor' => 'Training efficiency',
                'successful_value' => $successEfficiency,
                'unsuccessful_value' => $unsuccessEfficiency,
                'difference' => round($effDiff, 1),
            ];
        }

        // Compare friendship training rate
        $successFriendship = is_numeric($successfulPatterns['friendship_training_rate'] ?? null) ? (float) $successfulPatterns['friendship_training_rate'] : 0.0;
        $unsuccessFriendship = is_numeric($unsuccessfulPatterns['friendship_training_rate'] ?? null) ? (float) $unsuccessfulPatterns['friendship_training_rate'] : 0.0;
        $friendDiff = $successFriendship - $unsuccessFriendship;
        if (abs($friendDiff) > 5) {
            $differences[] = [
                'factor' => 'Friendship training rate',
                'successful_value' => $successFriendship,
                'unsuccessful_value' => $unsuccessFriendship,
                'difference' => round($friendDiff, 1),
            ];
        }

        return $differences;
    }

    /**
     * Analyze phase-specific patterns
     *
     * @param  Collection<int, Career>  $successfulCareers
     * @param  Collection<int, Career>  $unsuccessfulCareers
     * @return array<string, array{successful: array<string, mixed>, unsuccessful: array<string, mixed>, recommendation: string}>
     */
    protected function analyzePhasePatterns(Collection $successfulCareers, Collection $unsuccessfulCareers): array
    {
        $phasePatterns = [];

        foreach (self::CAREER_PHASES as $phase => $range) {
            $successfulPhaseData = $this->extractPhaseData($successfulCareers, $range);
            $unsuccessfulPhaseData = $this->extractPhaseData($unsuccessfulCareers, $range);

            $recommendation = $this->generatePhaseRecommendation($phase, $successfulPhaseData, $unsuccessfulPhaseData);

            $phasePatterns[$phase] = [
                'successful' => $successfulPhaseData,
                'unsuccessful' => $unsuccessfulPhaseData,
                'recommendation' => $recommendation,
            ];
        }

        return $phasePatterns;
    }

    /**
     * Extract phase-specific data from careers
     *
     * @param  Collection<int, Career>  $careers
     * @param  array{min: int, max: int}  $range
     * @return array{avg_efficiency: float, dominant_training: string, avg_gains: array<string, float>}
     */
    protected function extractPhaseData(Collection $careers, array $range): array
    {
        if ($careers->isEmpty()) {
            return [
                'avg_efficiency' => 0.0,
                'dominant_training' => 'N/A',
                'avg_gains' => array_fill_keys(self::STAT_TYPES, 0.0),
            ];
        }

        $allPhaseSessions = collect();

        foreach ($careers as $career) {
            $phaseSessions = $career->trainingSessions->filter(function ($s) use ($range) {
                $turn = $s->turn_number ?? 0;

                return $turn >= $range['min'] && $turn <= $range['max'];
            });
            $allPhaseSessions = $allPhaseSessions->merge($phaseSessions);
        }

        if ($allPhaseSessions->isEmpty()) {
            return [
                'avg_efficiency' => 0.0,
                'dominant_training' => 'N/A',
                'avg_gains' => array_fill_keys(self::STAT_TYPES, 0.0),
            ];
        }

        $efficiency = $this->calculateCareerEfficiency($allPhaseSessions);
        $distribution = $this->calculateTrainingTypeDistribution($allPhaseSessions);
        $avgGains = $this->calculateAverageGains($allPhaseSessions);

        // Find dominant training type
        arsort($distribution);
        $dominantTraining = array_key_first($distribution) ?? 'N/A';

        return [
            'avg_efficiency' => $efficiency['rating'],
            'dominant_training' => $dominantTraining,
            'avg_gains' => $avgGains,
        ];
    }

    /**
     * Generate phase-specific recommendation
     *
     * @param  array<string, mixed>  $successfulData
     * @param  array<string, mixed>  $unsuccessfulData
     */
    protected function generatePhaseRecommendation(string $phase, array $successfulData, array $unsuccessfulData): string
    {
        $successEfficiency = is_numeric($successfulData['avg_efficiency'] ?? null) ? (float) $successfulData['avg_efficiency'] : 0.0;
        $unsuccessEfficiency = is_numeric($unsuccessfulData['avg_efficiency'] ?? null) ? (float) $unsuccessfulData['avg_efficiency'] : 0.0;
        $effDiff = $successEfficiency - $unsuccessEfficiency;

        $successDominant = is_string($successfulData['dominant_training'] ?? null) ? $successfulData['dominant_training'] : 'mixed';
        $unsuccessDominant = is_string($unsuccessfulData['dominant_training'] ?? null) ? $unsuccessfulData['dominant_training'] : 'mixed';

        if ($effDiff > 10) {
            return "In {$phase} phase, successful careers focus on {$successDominant} training with {$successEfficiency}% efficiency.";
        }

        if ($successDominant !== $unsuccessDominant) {
            return "Successful careers prioritize {$successDominant} training during {$phase} phase.";
        }

        return "Maintain consistent training during {$phase} phase.";
    }

    /**
     * Identify race strategy patterns
     *
     * @param  Collection<int, Career>  $successfulCareers
     * @param  Collection<int, Career>  $unsuccessfulCareers
     * @return array{
     *     successful_strategies: array<string, mixed>,
     *     unsuccessful_strategies: array<string, mixed>,
     *     strategy_effectiveness: array<string, array{races: int, wins: int, win_rate: float, avg_position: float}>,
     *     distance_patterns: array<string, array{successful_win_rate: float, unsuccessful_win_rate: float, recommended_style: string}>
     * }
     */
    protected function identifyRaceStrategyPatterns(Collection $successfulCareers, Collection $unsuccessfulCareers): array
    {
        $successfulStrategies = $this->extractRaceStrategies($successfulCareers);
        $unsuccessfulStrategies = $this->extractRaceStrategies($unsuccessfulCareers);

        // Calculate strategy effectiveness
        $strategyEffectiveness = $this->calculateStrategyEffectiveness(
            $successfulCareers->merge($unsuccessfulCareers)
        );

        // Analyze distance-specific patterns
        $distancePatterns = $this->analyzeDistancePatterns($successfulCareers, $unsuccessfulCareers);

        return [
            'successful_strategies' => $successfulStrategies,
            'unsuccessful_strategies' => $unsuccessfulStrategies,
            'strategy_effectiveness' => $strategyEffectiveness,
            'distance_patterns' => $distancePatterns,
        ];
    }

    /**
     * Extract race strategies from careers
     *
     * @param  Collection<int, Career>  $careers
     * @return array{running_style_distribution: array<string, float>, avg_win_rate: float, avg_position: float}
     */
    protected function extractRaceStrategies(Collection $careers): array
    {
        if ($careers->isEmpty()) {
            return [
                'running_style_distribution' => [],
                'avg_win_rate' => 0.0,
                'avg_position' => 0.0,
            ];
        }

        $allRaces = collect();
        foreach ($careers as $career) {
            $allRaces = $allRaces->merge($career->races);
        }

        if ($allRaces->isEmpty()) {
            return [
                'running_style_distribution' => [],
                'avg_win_rate' => 0.0,
                'avg_position' => 0.0,
            ];
        }

        // Running style distribution
        $styleDistribution = [];
        foreach ($allRaces as $race) {
            $style = $race->running_style ?? 'unknown';
            $styleDistribution[$style] = ($styleDistribution[$style] ?? 0) + 1;
        }

        // Calculate percentages
        $total = array_sum($styleDistribution);
        foreach ($styleDistribution as $style => $count) {
            $styleDistribution[$style] = round(($count / $total) * 100, 1);
        }

        // Win rate and average position
        $wins = $allRaces->where('won_race', true)->count();
        $totalRaces = $allRaces->count();
        $avgPositionValue = $allRaces->avg('finish_position');
        $avgPosition = is_numeric($avgPositionValue) ? (float) $avgPositionValue : 0.0;

        return [
            'running_style_distribution' => $styleDistribution,
            'avg_win_rate' => $totalRaces > 0 ? round(($wins / $totalRaces) * 100, 1) : 0.0,
            'avg_position' => round($avgPosition, 2),
        ];
    }

    /**
     * Calculate effectiveness of each running style
     *
     * @param  Collection<int, Career>  $careers
     * @return array<string, array{races: int, wins: int, win_rate: float, avg_position: float}>
     */
    protected function calculateStrategyEffectiveness(Collection $careers): array
    {
        $allRaces = collect();
        foreach ($careers as $career) {
            $allRaces = $allRaces->merge($career->races);
        }

        $styles = ['front_runner', 'pace_chaser', 'late_surger', 'end_closer'];
        $effectiveness = [];

        foreach ($styles as $style) {
            $styleRaces = $allRaces->where('running_style', $style);
            $count = $styleRaces->count();
            $wins = $styleRaces->where('won_race', true)->count();
            $avgPos = $styleRaces->avg('finish_position');

            $effectiveness[$style] = [
                'races' => $count,
                'wins' => $wins,
                'win_rate' => $count > 0 ? round(($wins / $count) * 100, 1) : 0.0,
                'avg_position' => $count > 0 && is_numeric($avgPos) ? round((float) $avgPos, 2) : 0.0,
            ];
        }

        return $effectiveness;
    }

    /**
     * Analyze distance-specific patterns
     *
     * @param  Collection<int, Career>  $successfulCareers
     * @param  Collection<int, Career>  $unsuccessfulCareers
     * @return array<string, array{successful_win_rate: float, unsuccessful_win_rate: float, recommended_style: string}>
     */
    protected function analyzeDistancePatterns(Collection $successfulCareers, Collection $unsuccessfulCareers): array
    {
        $distances = ['short', 'mile', 'intermediate', 'long'];
        $patterns = [];

        foreach ($distances as $distance) {
            $successfulRaces = $this->getRacesByDistance($successfulCareers, $distance);
            $unsuccessfulRaces = $this->getRacesByDistance($unsuccessfulCareers, $distance);

            $successWinRate = $successfulRaces->count() > 0
                ? round(($successfulRaces->where('won_race', true)->count() / $successfulRaces->count()) * 100, 1)
                : 0.0;

            $unsuccessWinRate = $unsuccessfulRaces->count() > 0
                ? round(($unsuccessfulRaces->where('won_race', true)->count() / $unsuccessfulRaces->count()) * 100, 1)
                : 0.0;

            // Find most successful running style for this distance
            $recommendedStyle = $this->findBestStyleForDistance($successfulRaces);

            $patterns[$distance] = [
                'successful_win_rate' => $successWinRate,
                'unsuccessful_win_rate' => $unsuccessWinRate,
                'recommended_style' => $recommendedStyle,
            ];
        }

        return $patterns;
    }

    /**
     * Get races by distance category
     *
     * @param  Collection<int, Career>  $careers
     * @return Collection<int, Race>
     */
    protected function getRacesByDistance(Collection $careers, string $distance): Collection
    {
        /** @var Collection<int, Race> $allRaces */
        $allRaces = collect();
        foreach ($careers as $career) {
            $allRaces = $allRaces->merge($career->races);
        }

        return $allRaces->filter(function ($race) use ($distance) {
            return ($race->distance_category ?? '') === $distance;
        });
    }

    /**
     * Find the best running style for a distance
     *
     * @param  Collection<int, Race>  $races
     */
    protected function findBestStyleForDistance(Collection $races): string
    {
        if ($races->isEmpty()) {
            return 'N/A';
        }

        $styleWinRates = [];
        $styles = ['front_runner', 'pace_chaser', 'late_surger', 'end_closer'];

        foreach ($styles as $style) {
            $styleRaces = $races->where('running_style', $style);
            $count = $styleRaces->count();

            if ($count >= 2) {
                $wins = $styleRaces->where('won_race', true)->count();
                $styleWinRates[$style] = $wins / $count;
            }
        }

        if (empty($styleWinRates)) {
            return 'N/A';
        }

        arsort($styleWinRates);

        return array_key_first($styleWinRates);
    }

    /**
     * Identify successful decision sequences
     *
     * @param  Collection<int, Career>  $successfulCareers
     * @return array{
     *     early_game_sequences: array<string, mixed>,
     *     mid_game_sequences: array<string, mixed>,
     *     late_game_sequences: array<string, mixed>,
     *     critical_decision_points: array<string, mixed>
     * }
     */
    protected function identifyDecisionSequences(Collection $successfulCareers): array
    {
        $earlySequences = [];
        $midSequences = [];
        $lateSequences = [];
        $criticalPoints = [];

        foreach ($successfulCareers as $career) {
            $sessions = $career->trainingSessions->sortBy('turn_number');

            // Early game (turns 1-24)
            $earlySessions = $sessions->filter(fn ($s) => ($s->turn_number ?? 0) <= 24);
            if ($earlySessions->isNotEmpty()) {
                $earlySequences[] = $this->extractDecisionSequence($earlySessions);
            }

            // Mid game (turns 25-48)
            $midSessions = $sessions->filter(fn ($s) => ($s->turn_number ?? 0) > 24 && ($s->turn_number ?? 0) <= 48);
            if ($midSessions->isNotEmpty()) {
                $midSequences[] = $this->extractDecisionSequence($midSessions);
            }

            // Late game (turns 49-72)
            $lateSessions = $sessions->filter(fn ($s) => ($s->turn_number ?? 0) > 48);
            if ($lateSessions->isNotEmpty()) {
                $lateSequences[] = $this->extractDecisionSequence($lateSessions);
            }

            // Identify critical decision points
            $criticalPoints = array_merge($criticalPoints, $this->identifyCriticalDecisions($career));
        }

        return [
            'early_game_sequences' => $this->summarizeSequences($earlySequences),
            'mid_game_sequences' => $this->summarizeSequences($midSequences),
            'late_game_sequences' => $this->summarizeSequences($lateSequences),
            'critical_decision_points' => $this->summarizeCriticalPoints($criticalPoints),
        ];
    }

    /**
     * Extract decision sequence from sessions
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array{training_types: array<int, string>, efficiency: float, key_decisions: array<int, array<string, mixed>>}
     */
    protected function extractDecisionSequence(Collection $sessions): array
    {
        /** @var array<int, string> $trainingTypes */
        $trainingTypes = $sessions->pluck('training_type')->filter()->values()->toArray();
        $efficiency = $this->calculateCareerEfficiency($sessions)['rating'];

        // Identify key decisions (high-gain sessions, friendship training, etc.)
        $keyDecisions = [];
        foreach ($sessions as $session) {
            $totalGain = ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);

            if ($totalGain > 40 || $session->friendship_training) {
                $keyDecisions[] = [
                    'turn' => $session->turn_number,
                    'type' => $session->training_type,
                    'gain' => $totalGain,
                    'friendship' => $session->friendship_training ?? false,
                ];
            }
        }

        return [
            'training_types' => $trainingTypes,
            'efficiency' => $efficiency,
            'key_decisions' => array_slice($keyDecisions, 0, 5),
        ];
    }

    /**
     * Identify critical decision points in a career
     *
     * @return array<int, array{turn: int, type: string, impact: string}>
     */
    protected function identifyCriticalDecisions(Career $career): array
    {
        $criticalPoints = [];
        $sessions = $career->trainingSessions->sortBy('turn_number');

        // Pre-race decisions
        $races = $career->races;
        foreach ($races as $race) {
            $raceTurn = $race->turn_number ?? 0;
            $preRaceSessions = $sessions->filter(fn ($s) => ($s->turn_number ?? 0) >= $raceTurn - 3 && ($s->turn_number ?? 0) < $raceTurn);

            if ($preRaceSessions->isNotEmpty() && $race->won_race) {
                $criticalPoints[] = [
                    'turn' => $raceTurn - 1,
                    'type' => 'pre_race_preparation',
                    'impact' => 'Led to race victory',
                ];
            }
        }

        // High-efficiency training streaks
        $streak = 0;
        $streakStart = 0;
        foreach ($sessions as $session) {
            $totalGain = ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);

            if ($totalGain > 35) {
                if ($streak === 0) {
                    $streakStart = $session->turn_number ?? 0;
                }
                $streak++;
            } else {
                if ($streak >= 3) {
                    $criticalPoints[] = [
                        'turn' => $streakStart,
                        'type' => 'high_efficiency_streak',
                        'impact' => "Maintained high efficiency for {$streak} turns",
                    ];
                }
                $streak = 0;
            }
        }

        return $criticalPoints;
    }

    /**
     * Summarize decision sequences
     *
     * @param  array<int, array<string, mixed>>  $sequences
     * @return array{common_patterns: array<string, int>, avg_efficiency: float, key_insights: array<int, string>}
     */
    protected function summarizeSequences(array $sequences): array
    {
        if (empty($sequences)) {
            return [
                'common_patterns' => [],
                'avg_efficiency' => 0.0,
                'key_insights' => [],
            ];
        }

        // Find common training type patterns
        $allTypes = [];
        $efficiencies = [];

        foreach ($sequences as $seq) {
            $seqTypes = is_array($seq['training_types'] ?? null) ? $seq['training_types'] : [];
            $allTypes = array_merge($allTypes, $seqTypes);
            $efficiencies[] = is_numeric($seq['efficiency'] ?? null) ? (float) $seq['efficiency'] : 0.0;
        }

        // Count type frequencies - ensure all values are strings
        /** @var array<string> $stringTypes */
        $stringTypes = array_filter($allTypes, fn ($v) => is_string($v));
        $typeCounts = array_count_values($stringTypes);
        arsort($typeCounts);

        $avgEfficiency = count($efficiencies) > 0 ? array_sum($efficiencies) / count($efficiencies) : 0;

        // Generate insights
        $insights = [];
        $topType = array_key_first($typeCounts);
        if (is_string($topType)) {
            $insights[] = 'Most common training: '.ucfirst($topType);
        }
        if ($avgEfficiency > 70) {
            $insights[] = "High average efficiency ({$avgEfficiency}%)";
        }

        return [
            'common_patterns' => array_slice($typeCounts, 0, 3, true),
            'avg_efficiency' => round($avgEfficiency, 1),
            'key_insights' => $insights,
        ];
    }

    /**
     * Summarize critical decision points
     *
     * @param  array<int, array<string, mixed>>  $criticalPoints
     * @return array{most_common_types: array<string, int>, total_critical_points: int, recommendations: array<int, string>}
     */
    protected function summarizeCriticalPoints(array $criticalPoints): array
    {
        if (empty($criticalPoints)) {
            return [
                'most_common_types' => [],
                'total_critical_points' => 0,
                'recommendations' => [],
            ];
        }

        $typeCounts = [];
        foreach ($criticalPoints as $point) {
            $type = is_string($point['type'] ?? null) ? $point['type'] : 'unknown';
            $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
        }
        arsort($typeCounts);

        $recommendations = [];
        if (isset($typeCounts['pre_race_preparation']) && $typeCounts['pre_race_preparation'] > 2) {
            $recommendations[] = 'Focus on preparation in the 3 turns before important races';
        }
        if (isset($typeCounts['high_efficiency_streak']) && $typeCounts['high_efficiency_streak'] > 1) {
            $recommendations[] = 'Maintain training momentum for consecutive high-efficiency turns';
        }

        return [
            'most_common_types' => $typeCounts,
            'total_critical_points' => count($criticalPoints),
            'recommendations' => $recommendations,
        ];
    }

    // =========================================================================
    // SUCCESS FACTOR ANALYSIS
    // =========================================================================

    /**
     * Analyze success factors across multiple career runs
     *
     * @param  array<int>  $careerIds
     * @return array{
     *     success_factors: array<int, array<string, mixed>>,
     *     factor_importance: array<string, array<string, mixed>>,
     *     correlation_matrix: array<string, array<string, float>>,
     *     actionable_insights: array<string>
     * }
     */
    public function analyzeSuccessFactors(array $careerIds): array
    {
        $cacheKey = 'success_factors:'.md5(implode(',', $careerIds));

        /** @var array{success_factors: array<int, array<string, mixed>>, factor_importance: array<string, array<string, mixed>>, correlation_matrix: array<string, array<string, float>>, actionable_insights: array<string>} $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($careerIds) {
            $careers = Career::with(['trainingSessions', 'races'])
                ->whereIn('id', $careerIds)
                ->get();

            if ($careers->count() < self::MIN_SAMPLE_SIZE) {
                return [
                    'success_factors' => [],
                    'factor_importance' => [],
                    'correlation_matrix' => [],
                    'actionable_insights' => ['Insufficient data for success factor analysis (minimum 5 careers required)'],
                ];
            }

            // Extract factors for each career
            $careerFactors = $this->extractCareerFactors($careers);

            // Calculate factor importance
            $factorImportance = $this->calculateFactorImportance($careerFactors);

            // Build correlation matrix
            $correlationMatrix = $this->buildCorrelationMatrix($careerFactors);

            // Generate actionable insights
            $actionableInsights = $this->generateActionableInsights($factorImportance, $correlationMatrix);

            return [
                'success_factors' => $careerFactors,
                'factor_importance' => $factorImportance,
                'correlation_matrix' => $correlationMatrix,
                'actionable_insights' => $actionableInsights,
            ];
        });

        return $result;
    }

    /**
     * Extract measurable factors from each career
     *
     * @param  Collection<int, Career>  $careers
     * @return array<int, array{career_id: int, is_successful: bool, factors: array<string, float|int>}>
     */
    protected function extractCareerFactors(Collection $careers): array
    {
        $careerFactors = [];

        foreach ($careers as $career) {
            $sessions = $career->trainingSessions;
            $races = $career->races;
            $efficiency = $this->calculateCareerEfficiency($sessions);
            $racePerformance = $this->calculateRacePerformance($races);

            $factors = [
                'training_efficiency' => $efficiency['rating'],
                'failure_rate' => $efficiency['failure_rate'],
                'friendship_training_rate' => $this->calculateFriendshipRate($sessions),
                'race_win_rate' => $racePerformance['win_rate'],
                'avg_race_position' => $racePerformance['avg_position'],
                'total_stat_points' => $this->calculateTotalStatPoints($sessions),
                'skill_acquisition_rate' => $this->calculateSkillAcquisitionRate($sessions),
                'training_consistency' => $this->calculateTrainingConsistency($sessions),
                'early_game_efficiency' => $this->calculatePhaseEfficiency($sessions, 'junior'),
                'mid_game_efficiency' => $this->calculatePhaseEfficiency($sessions, 'classic'),
                'late_game_efficiency' => $this->calculatePhaseEfficiency($sessions, 'senior'),
            ];

            $careerFactors[] = [
                'career_id' => $career->id,
                'is_successful' => $this->isCareerSuccessful($career),
                'factors' => $factors,
            ];
        }

        return $careerFactors;
    }

    /**
     * Calculate friendship training rate
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     */
    protected function calculateFriendshipRate(Collection $sessions): float
    {
        if ($sessions->isEmpty()) {
            return 0.0;
        }

        $friendshipCount = $sessions->where('friendship_training', true)->count();

        return round(($friendshipCount / $sessions->count()) * 100, 1);
    }

    /**
     * Calculate total stat points gained
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     */
    protected function calculateTotalStatPoints(Collection $sessions): int
    {
        $total = 0;

        foreach ($sessions as $session) {
            if (! is_object($session)) {
                continue;
            }
            $total += ($session->speed_gain ?? 0)
                + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0)
                + ($session->guts_gain ?? 0)
                + ($session->wit_gain ?? 0);
        }

        return $total;
    }

    /**
     * Calculate skill acquisition rate
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     */
    protected function calculateSkillAcquisitionRate(Collection $sessions): float
    {
        if ($sessions->isEmpty()) {
            return 0.0;
        }

        $skillsAcquired = 0;
        foreach ($sessions as $session) {
            if (! is_object($session)) {
                continue;
            }
            /** @var array<mixed> $hints */
            $hints = $session->skill_hints_obtained ?? [];
            $skillsAcquired += is_array($hints) ? count($hints) : 0;
        }

        return round(($skillsAcquired / $sessions->count()) * 100, 1);
    }

    /**
     * Calculate training consistency (inverse of variance)
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     */
    protected function calculateTrainingConsistency(Collection $sessions): float
    {
        if ($sessions->count() < 2) {
            return 0.0;
        }

        $gains = [];
        foreach ($sessions as $session) {
            $gains[] = ($session->speed_gain ?? 0)
                + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0)
                + ($session->guts_gain ?? 0)
                + ($session->wit_gain ?? 0);
        }

        $variance = $this->calculateVariance($gains);

        // Convert to consistency score (0-100, where 100 = perfectly consistent)
        return max(0, min(100, round(100 - sqrt($variance), 1)));
    }

    /**
     * Calculate efficiency for a specific phase
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     */
    protected function calculatePhaseEfficiency(Collection $sessions, string $phase): float
    {
        $range = self::CAREER_PHASES[$phase] ?? ['min' => 1, 'max' => 24];

        /** @var Collection<int, \App\Models\TrainingSession> $phaseSessions */
        $phaseSessions = $sessions->filter(function ($s) use ($range) {
            $turn = $s->turn_number ?? 0;

            return $turn >= $range['min'] && $turn <= $range['max'];
        });

        return $this->calculateCareerEfficiency($phaseSessions)['rating'];
    }

    /**
     * Calculate factor importance using correlation with success
     *
     * @param  array<int, array{career_id: int, is_successful: bool, factors: array<string, float|int>}>  $careerFactors
     * @return array<string, array{correlation: float, importance: string, direction: string}>
     */
    protected function calculateFactorImportance(array $careerFactors): array
    {
        $factorNames = [
            'training_efficiency',
            'failure_rate',
            'friendship_training_rate',
            'race_win_rate',
            'total_stat_points',
            'skill_acquisition_rate',
            'training_consistency',
            'early_game_efficiency',
            'mid_game_efficiency',
            'late_game_efficiency',
        ];

        $importance = [];

        foreach ($factorNames as $factorName) {
            $factorValues = [];
            $successValues = [];

            foreach ($careerFactors as $career) {
                $factorValues[] = $career['factors'][$factorName] ?? 0;
                $successValues[] = $career['is_successful'] ? 1 : 0;
            }

            $correlation = $this->calculatePearsonCorrelation($factorValues, $successValues);

            $importance[$factorName] = [
                'correlation' => round($correlation, 3),
                'importance' => $this->categorizeImportance($correlation),
                'direction' => $correlation >= 0 ? 'positive' : 'negative',
            ];
        }

        // Sort by absolute correlation
        uasort($importance, fn ($a, $b) => abs($b['correlation']) <=> abs($a['correlation']));

        return $importance;
    }

    /**
     * Calculate Pearson correlation coefficient
     *
     * @param  array<float|int>  $x
     * @param  array<float|int>  $y
     */
    protected function calculatePearsonCorrelation(array $x, array $y): float
    {
        $n = count($x);

        if ($n !== count($y) || $n < 2) {
            return 0.0;
        }

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
            $sumY2 += $y[$i] * $y[$i];
        }

        $numerator = ($n * $sumXY) - ($sumX * $sumY);
        $denominator = sqrt((($n * $sumX2) - ($sumX * $sumX)) * (($n * $sumY2) - ($sumY * $sumY)));

        if ($denominator == 0) {
            return 0.0;
        }

        return $numerator / $denominator;
    }

    /**
     * Categorize importance based on correlation strength
     */
    protected function categorizeImportance(float $correlation): string
    {
        $absCorr = abs($correlation);

        return match (true) {
            $absCorr >= 0.7 => 'very_high',
            $absCorr >= 0.5 => 'high',
            $absCorr >= 0.3 => 'moderate',
            $absCorr >= 0.1 => 'low',
            default => 'negligible',
        };
    }

    /**
     * Build correlation matrix between factors
     *
     * @param  array<int, array{career_id: int, is_successful: bool, factors: array<string, float|int>}>  $careerFactors
     * @return array<string, array<string, float>>
     */
    protected function buildCorrelationMatrix(array $careerFactors): array
    {
        $factorNames = [
            'training_efficiency',
            'race_win_rate',
            'total_stat_points',
            'training_consistency',
        ];

        $matrix = [];

        foreach ($factorNames as $factor1) {
            $matrix[$factor1] = [];

            foreach ($factorNames as $factor2) {
                $values1 = array_map(fn ($c) => $c['factors'][$factor1] ?? 0, $careerFactors);
                $values2 = array_map(fn ($c) => $c['factors'][$factor2] ?? 0, $careerFactors);

                $matrix[$factor1][$factor2] = round($this->calculatePearsonCorrelation($values1, $values2), 3);
            }
        }

        return $matrix;
    }

    /**
     * Generate actionable insights from factor analysis
     *
     * @param  array<string, array{correlation: float, importance: string, direction: string}>  $factorImportance
     * @param  array<string, array<string, float>>  $correlationMatrix
     * @return array<string>
     */
    protected function generateActionableInsights(array $factorImportance, array $correlationMatrix): array
    {
        $insights = [];

        // Top factors
        $topFactors = array_slice($factorImportance, 0, 3, true);

        foreach ($topFactors as $factor => $data) {
            $importance = $data['importance'] ?? '';
            $direction = $data['direction'] ?? '';
            if ($importance === 'very_high' || $importance === 'high') {
                $directionLabel = $direction === 'positive' ? 'increases' : 'decreases';
                $factorLabel = str_replace('_', ' ', $factor);
                $insights[] = "Higher {$factorLabel} strongly {$directionLabel} success probability";
            }
        }

        // Check for interesting correlations
        if (isset($correlationMatrix['training_efficiency']['race_win_rate'])) {
            $corr = $correlationMatrix['training_efficiency']['race_win_rate'];
            if ($corr > 0.5) {
                $insights[] = 'Training efficiency is strongly correlated with race performance';
            }
        }

        // Failure rate insight
        if (isset($factorImportance['failure_rate']) && $factorImportance['failure_rate']['correlation'] < -0.3) {
            $insights[] = 'Minimizing training failures significantly improves career outcomes';
        }

        // Consistency insight
        if (isset($factorImportance['training_consistency']) && $factorImportance['training_consistency']['correlation'] > 0.3) {
            $insights[] = 'Consistent training performance leads to better overall results';
        }

        return array_slice($insights, 0, 5);
    }

    // =========================================================================
    // STATISTICAL SIGNIFICANCE TESTING
    // =========================================================================

    /**
     * Perform statistical significance testing for pattern validation
     *
     * @param  array<int>  $careerIds
     * @return array{
     *     t_tests: array<string, array<string, mixed>>,
     *     chi_square_tests: array<string, array<string, mixed>>,
     *     effect_sizes: array<string, array<string, mixed>>,
     *     confidence_intervals: array<string, array<string, mixed>>,
     *     overall_significance: array<string, mixed>
     * }
     */
    public function performStatisticalTests(array $careerIds): array
    {
        $cacheKey = 'statistical_tests:'.md5(implode(',', $careerIds));

        /** @var array{t_tests: array<string, array<string, mixed>>, chi_square_tests: array<string, array<string, mixed>>, effect_sizes: array<string, array<string, mixed>>, confidence_intervals: array<string, array<string, mixed>>, overall_significance: array<string, mixed>} $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($careerIds) {
            $careers = Career::with(['trainingSessions', 'races'])
                ->whereIn('id', $careerIds)
                ->get();

            if ($careers->count() < self::MIN_SAMPLE_SIZE) {
                return [
                    't_tests' => [],
                    'chi_square_tests' => [],
                    'effect_sizes' => [],
                    'confidence_intervals' => [],
                    'overall_significance' => [
                        'sufficient_data' => false,
                        'message' => 'Minimum 5 careers required for statistical testing',
                    ],
                ];
            }

            // Separate successful and unsuccessful careers
            $successfulCareers = $careers->filter(fn ($c) => $this->isCareerSuccessful($c));
            $unsuccessfulCareers = $careers->filter(fn ($c) => ! $this->isCareerSuccessful($c));

            // Perform t-tests for continuous variables
            $tTests = $this->performTTests($successfulCareers, $unsuccessfulCareers);

            // Perform chi-square tests for categorical variables
            $chiSquareTests = $this->performChiSquareTests($successfulCareers, $unsuccessfulCareers);

            // Calculate effect sizes
            $effectSizes = $this->calculateEffectSizes($successfulCareers, $unsuccessfulCareers);

            // Calculate confidence intervals
            $confidenceIntervals = $this->calculateConfidenceIntervals($successfulCareers, $unsuccessfulCareers);

            // Overall significance summary
            $overallSignificance = $this->summarizeSignificance($tTests, $chiSquareTests, $effectSizes);

            return [
                't_tests' => $tTests,
                'chi_square_tests' => $chiSquareTests,
                'effect_sizes' => $effectSizes,
                'confidence_intervals' => $confidenceIntervals,
                'overall_significance' => $overallSignificance,
            ];
        });

        return $result;
    }

    /**
     * Perform independent samples t-tests
     *
     * @param  Collection<int, Career>  $successfulCareers
     * @param  Collection<int, Career>  $unsuccessfulCareers
     * @return array<string, array{t_statistic: float, p_value: float, significant: bool, interpretation: string}>
     */
    protected function performTTests(Collection $successfulCareers, Collection $unsuccessfulCareers): array
    {
        $variables = [
            'training_efficiency' => fn ($c) => $this->calculateCareerEfficiency($c->trainingSessions)['rating'],
            'total_stat_points' => fn ($c) => $this->calculateTotalStatPoints($c->trainingSessions),
            'race_win_rate' => fn ($c) => $this->calculateRacePerformance($c->races)['win_rate'],
            'avg_race_position' => fn ($c) => $this->calculateRacePerformance($c->races)['avg_position'],
        ];

        $results = [];

        foreach ($variables as $name => $extractor) {
            /** @var array<int, float|int> $successValues */
            $successValues = $successfulCareers->map($extractor)->values()->toArray();
            /** @var array<int, float|int> $unsuccessValues */
            $unsuccessValues = $unsuccessfulCareers->map($extractor)->values()->toArray();

            if (count($successValues) >= 2 && count($unsuccessValues) >= 2) {
                $tTest = $this->calculateTTest($successValues, $unsuccessValues);
                $results[$name] = $tTest;
            }
        }

        return $results;
    }

    /**
     * Calculate independent samples t-test
     *
     * @param  array<int, float|int>  $group1
     * @param  array<int, float|int>  $group2
     * @return array{t_statistic: float, p_value: float, significant: bool, interpretation: string}
     */
    protected function calculateTTest(array $group1, array $group2): array
    {
        $n1 = count($group1);
        $n2 = count($group2);

        if ($n1 < 2 || $n2 < 2) {
            return [
                't_statistic' => 0.0,
                'p_value' => 1.0,
                'significant' => false,
                'interpretation' => 'Insufficient sample size',
            ];
        }

        $mean1 = array_sum($group1) / $n1;
        $mean2 = array_sum($group2) / $n2;

        $var1 = $this->calculateVariance($group1);
        $var2 = $this->calculateVariance($group2);

        // Pooled standard error
        $se = sqrt(($var1 / $n1) + ($var2 / $n2));

        if ($se == 0) {
            return [
                't_statistic' => 0.0,
                'p_value' => 1.0,
                'significant' => false,
                'interpretation' => 'No variance in data',
            ];
        }

        $tStatistic = ($mean1 - $mean2) / $se;

        // Degrees of freedom (Welch's approximation)
        $df = $this->calculateWelchDF($var1, $n1, $var2, $n2);

        // Approximate p-value using t-distribution
        $pValue = $this->approximateTDistributionPValue($tStatistic, $df);

        $significant = $pValue < self::SIGNIFICANCE_LEVEL;

        $interpretation = match (true) {
            $pValue < 0.001 => 'Highly significant difference (p < 0.001)',
            $pValue < 0.01 => 'Very significant difference (p < 0.01)',
            $pValue < 0.05 => 'Significant difference (p < 0.05)',
            $pValue < 0.1 => 'Marginally significant (p < 0.1)',
            default => 'No significant difference',
        };

        return [
            't_statistic' => round($tStatistic, 3),
            'p_value' => round($pValue, 4),
            'significant' => $significant,
            'interpretation' => $interpretation,
        ];
    }

    /**
     * Calculate Welch's degrees of freedom
     */
    protected function calculateWelchDF(float $var1, int $n1, float $var2, int $n2): float
    {
        $s1 = $var1 / $n1;
        $s2 = $var2 / $n2;

        $numerator = pow($s1 + $s2, 2);
        $denominator = (pow($s1, 2) / ($n1 - 1)) + (pow($s2, 2) / ($n2 - 1));

        if ($denominator == 0) {
            return 1.0;
        }

        return $numerator / $denominator;
    }

    /**
     * Approximate p-value from t-distribution
     * Uses a simplified approximation for two-tailed test
     */
    protected function approximateTDistributionPValue(float $t, float $df): float
    {
        // Use approximation based on normal distribution for large df
        if ($df > 30) {
            // Standard normal approximation
            $z = abs($t);

            return 2 * (1 - $this->normalCDF($z));
        }

        // For smaller df, use a rough approximation
        $absT = abs($t);

        // Critical values approximation
        if ($absT > 3.5) {
            return 0.001;
        }
        if ($absT > 2.5) {
            return 0.02;
        }
        if ($absT > 2.0) {
            return 0.05;
        }
        if ($absT > 1.7) {
            return 0.1;
        }
        if ($absT > 1.3) {
            return 0.2;
        }

        return 0.5;
    }

    /**
     * Standard normal CDF approximation
     */
    protected function normalCDF(float $z): float
    {
        // Approximation using error function
        $a1 = 0.254829592;
        $a2 = -0.284496736;
        $a3 = 1.421413741;
        $a4 = -1.453152027;
        $a5 = 1.061405429;
        $p = 0.3275911;

        $sign = $z < 0 ? -1 : 1;
        $z = abs($z) / sqrt(2);

        $t = 1.0 / (1.0 + $p * $z);
        $y = 1.0 - ((((($a5 * $t + $a4) * $t) + $a3) * $t + $a2) * $t + $a1) * $t * exp(-$z * $z);

        return 0.5 * (1.0 + $sign * $y);
    }

    /**
     * Perform chi-square tests for categorical variables
     *
     * @param  Collection<int, Career>  $successfulCareers
     * @param  Collection<int, Career>  $unsuccessfulCareers
     * @return array<string, array{chi_square: float, p_value: float, significant: bool, interpretation: string}>
     */
    protected function performChiSquareTests(Collection $successfulCareers, Collection $unsuccessfulCareers): array
    {
        $results = [];

        // Test training type preference
        $results['training_type_preference'] = $this->chiSquareTrainingType($successfulCareers, $unsuccessfulCareers);

        // Test running style preference
        $results['running_style_preference'] = $this->chiSquareRunningStyle($successfulCareers, $unsuccessfulCareers);

        return $results;
    }

    /**
     * Chi-square test for training type preference
     *
     * @param  Collection<int, Career>  $successfulCareers
     * @param  Collection<int, Career>  $unsuccessfulCareers
     * @return array{chi_square: float, p_value: float, significant: bool, interpretation: string}
     */
    protected function chiSquareTrainingType(Collection $successfulCareers, Collection $unsuccessfulCareers): array
    {
        $trainingTypes = ['speed', 'stamina', 'power', 'guts', 'wit'];

        // Build contingency table
        $observed = [];
        foreach ($trainingTypes as $type) {
            $observed['successful'][$type] = 0;
            $observed['unsuccessful'][$type] = 0;
        }

        foreach ($successfulCareers as $career) {
            $dist = $this->calculateTrainingTypeDistribution($career->trainingSessions);
            foreach ($trainingTypes as $type) {
                $observed['successful'][$type] += $dist[$type] ?? 0;
            }
        }

        foreach ($unsuccessfulCareers as $career) {
            $dist = $this->calculateTrainingTypeDistribution($career->trainingSessions);
            foreach ($trainingTypes as $type) {
                $observed['unsuccessful'][$type] += $dist[$type] ?? 0;
            }
        }

        return $this->calculateChiSquare($observed, $trainingTypes);
    }

    /**
     * Chi-square test for running style preference
     *
     * @param  Collection<int, Career>  $successfulCareers
     * @param  Collection<int, Career>  $unsuccessfulCareers
     * @return array{chi_square: float, p_value: float, significant: bool, interpretation: string}
     */
    protected function chiSquareRunningStyle(Collection $successfulCareers, Collection $unsuccessfulCareers): array
    {
        $styles = ['front_runner', 'pace_chaser', 'late_surger', 'end_closer'];

        // Build contingency table
        $observed = [];
        foreach ($styles as $style) {
            $observed['successful'][$style] = 0;
            $observed['unsuccessful'][$style] = 0;
        }

        foreach ($successfulCareers as $career) {
            foreach ($career->races as $race) {
                $style = $race->running_style ?? 'unknown';
                if (isset($observed['successful'][$style])) {
                    $observed['successful'][$style]++;
                }
            }
        }

        foreach ($unsuccessfulCareers as $career) {
            foreach ($career->races as $race) {
                $style = $race->running_style ?? 'unknown';
                if (isset($observed['unsuccessful'][$style])) {
                    $observed['unsuccessful'][$style]++;
                }
            }
        }

        return $this->calculateChiSquare($observed, $styles);
    }

    /**
     * Calculate chi-square statistic
     *
     * @param  array<string, array<string, int>>  $observed
     * @param  array<string>  $categories
     * @return array{chi_square: float, p_value: float, significant: bool, interpretation: string}
     */
    protected function calculateChiSquare(array $observed, array $categories): array
    {
        // Calculate row and column totals
        $rowTotals = [];
        $colTotals = array_fill_keys($categories, 0);
        $grandTotal = 0;

        foreach ($observed as $row => $cols) {
            $rowTotals[$row] = array_sum($cols);
            $grandTotal += $rowTotals[$row];

            foreach ($cols as $col => $count) {
                $colTotals[$col] += $count;
            }
        }

        if ($grandTotal == 0) {
            return [
                'chi_square' => 0.0,
                'p_value' => 1.0,
                'significant' => false,
                'interpretation' => 'No data available',
            ];
        }

        // Calculate chi-square statistic
        $chiSquare = 0.0;

        foreach ($observed as $row => $cols) {
            foreach ($cols as $col => $observedCount) {
                $expected = ($rowTotals[$row] * $colTotals[$col]) / $grandTotal;

                if ($expected > 0) {
                    $chiSquare += pow($observedCount - $expected, 2) / $expected;
                }
            }
        }

        // Degrees of freedom
        $df = (count($observed) - 1) * (count($categories) - 1);

        // Approximate p-value
        $pValue = $this->approximateChiSquarePValue($chiSquare, $df);

        $significant = $pValue < self::SIGNIFICANCE_LEVEL;

        $interpretation = match (true) {
            $pValue < 0.001 => 'Highly significant association (p < 0.001)',
            $pValue < 0.01 => 'Very significant association (p < 0.01)',
            $pValue < 0.05 => 'Significant association (p < 0.05)',
            $pValue < 0.1 => 'Marginally significant (p < 0.1)',
            default => 'No significant association',
        };

        return [
            'chi_square' => round($chiSquare, 3),
            'p_value' => round($pValue, 4),
            'significant' => $significant,
            'interpretation' => $interpretation,
        ];
    }

    /**
     * Approximate chi-square p-value
     */
    protected function approximateChiSquarePValue(float $chiSquare, int $df): float
    {
        if ($df <= 0) {
            return 1.0;
        }

        // Use approximation based on critical values
        /** @var array<int, array<int, float>> $criticalValues */
        $criticalValues = [
            1 => [4 => 0.05, 7 => 0.01, 11 => 0.001],
            2 => [6 => 0.05, 9 => 0.01, 14 => 0.001],
            3 => [8 => 0.05, 11 => 0.01, 16 => 0.001],
            4 => [9 => 0.05, 13 => 0.01, 18 => 0.001],
            5 => [11 => 0.05, 15 => 0.01, 21 => 0.001],
        ];

        $dfKey = min($df, 5);

        if (isset($criticalValues[$dfKey])) {
            foreach ($criticalValues[$dfKey] as $critical => $pValue) {
                if ($chiSquare >= (float) $critical) {
                    return $pValue;
                }
            }
        }

        return 0.5;
    }

    /**
     * Calculate effect sizes (Cohen's d)
     *
     * @param  Collection<int, Career>  $successfulCareers
     * @param  Collection<int, Career>  $unsuccessfulCareers
     * @return array<string, array{cohens_d: float, effect_size: string, interpretation: string}>
     */
    protected function calculateEffectSizes(Collection $successfulCareers, Collection $unsuccessfulCareers): array
    {
        $variables = [
            'training_efficiency' => fn ($c) => $this->calculateCareerEfficiency($c->trainingSessions)['rating'],
            'total_stat_points' => fn ($c) => $this->calculateTotalStatPoints($c->trainingSessions),
            'race_win_rate' => fn ($c) => $this->calculateRacePerformance($c->races)['win_rate'],
        ];

        $results = [];

        foreach ($variables as $name => $extractor) {
            /** @var array<float> $successValues */
            $successValues = $successfulCareers->map($extractor)->values()->toArray();
            /** @var array<float> $unsuccessValues */
            $unsuccessValues = $unsuccessfulCareers->map($extractor)->values()->toArray();

            if (count($successValues) >= 2 && count($unsuccessValues) >= 2) {
                $cohensD = $this->calculateCohensD($successValues, $unsuccessValues);

                $effectSize = match (true) {
                    abs($cohensD) >= 0.8 => 'large',
                    abs($cohensD) >= 0.5 => 'medium',
                    abs($cohensD) >= 0.2 => 'small',
                    default => 'negligible',
                };

                $direction = $cohensD > 0 ? 'higher in successful' : 'higher in unsuccessful';

                $results[$name] = [
                    'cohens_d' => round($cohensD, 3),
                    'effect_size' => $effectSize,
                    'interpretation' => ucfirst($effectSize)." effect, {$direction} careers",
                ];
            }
        }

        return $results;
    }

    /**
     * Calculate Cohen's d effect size
     *
     * @param  array<float>  $group1
     * @param  array<float>  $group2
     */
    protected function calculateCohensD(array $group1, array $group2): float
    {
        $n1 = count($group1);
        $n2 = count($group2);

        if ($n1 < 2 || $n2 < 2) {
            return 0.0;
        }

        $mean1 = array_sum($group1) / $n1;
        $mean2 = array_sum($group2) / $n2;

        $var1 = $this->calculateVariance($group1);
        $var2 = $this->calculateVariance($group2);

        // Pooled standard deviation
        $pooledVar = (($n1 - 1) * $var1 + ($n2 - 1) * $var2) / ($n1 + $n2 - 2);
        $pooledSD = sqrt($pooledVar);

        if ($pooledSD == 0) {
            return 0.0;
        }

        return ($mean1 - $mean2) / $pooledSD;
    }

    /**
     * Calculate confidence intervals for key metrics
     *
     * @param  Collection<int, Career>  $successfulCareers
     * @param  Collection<int, Career>  $unsuccessfulCareers
     * @return array<string, array{mean_diff: float, ci_lower: float, ci_upper: float, interpretation: string}>
     */
    protected function calculateConfidenceIntervals(Collection $successfulCareers, Collection $unsuccessfulCareers): array
    {
        $variables = [
            'training_efficiency' => fn ($c) => $this->calculateCareerEfficiency($c->trainingSessions)['rating'],
            'race_win_rate' => fn ($c) => $this->calculateRacePerformance($c->races)['win_rate'],
        ];

        $results = [];

        foreach ($variables as $name => $extractor) {
            /** @var array<float> $successValues */
            $successValues = $successfulCareers->map($extractor)->values()->toArray();
            /** @var array<float> $unsuccessValues */
            $unsuccessValues = $unsuccessfulCareers->map($extractor)->values()->toArray();

            if (count($successValues) >= 2 && count($unsuccessValues) >= 2) {
                $ci = $this->calculateMeanDifferenceCI($successValues, $unsuccessValues);
                $results[$name] = $ci;
            }
        }

        return $results;
    }

    /**
     * Calculate 95% confidence interval for mean difference
     *
     * @param  array<float>  $group1
     * @param  array<float>  $group2
     * @return array{mean_diff: float, ci_lower: float, ci_upper: float, interpretation: string}
     */
    protected function calculateMeanDifferenceCI(array $group1, array $group2): array
    {
        $n1 = count($group1);
        $n2 = count($group2);

        $mean1 = array_sum($group1) / $n1;
        $mean2 = array_sum($group2) / $n2;
        $meanDiff = $mean1 - $mean2;

        $var1 = $this->calculateVariance($group1);
        $var2 = $this->calculateVariance($group2);

        $se = sqrt(($var1 / $n1) + ($var2 / $n2));

        // 95% CI uses z = 1.96 for large samples
        $zValue = 1.96;
        $margin = $zValue * $se;

        $ciLower = $meanDiff - $margin;
        $ciUpper = $meanDiff + $margin;

        // Interpretation
        $interpretation = match (true) {
            $ciLower > 0 => 'Successful careers significantly higher',
            $ciUpper < 0 => 'Unsuccessful careers significantly higher',
            default => 'No significant difference (CI includes zero)',
        };

        return [
            'mean_diff' => round($meanDiff, 2),
            'ci_lower' => round($ciLower, 2),
            'ci_upper' => round($ciUpper, 2),
            'interpretation' => $interpretation,
        ];
    }

    /**
     * Summarize overall statistical significance
     *
     * @param  array<string, array<string, mixed>>  $tTests
     * @param  array<string, array<string, mixed>>  $chiSquareTests
     * @param  array<string, array<string, mixed>>  $effectSizes
     * @return array{sufficient_data: bool, significant_findings: int, key_findings: array<string>, overall_conclusion: string}
     */
    protected function summarizeSignificance(array $tTests, array $chiSquareTests, array $effectSizes): array
    {
        $significantFindings = 0;
        $keyFindings = [];

        // Count significant t-tests
        foreach ($tTests as $name => $result) {
            if ($result['significant'] ?? false) {
                $significantFindings++;
                $interpretation = is_string($result['interpretation'] ?? null) ? $result['interpretation'] : '';
                $keyFindings[] = ucfirst(str_replace('_', ' ', $name)).': '.$interpretation;
            }
        }

        // Count significant chi-square tests
        foreach ($chiSquareTests as $name => $result) {
            if ($result['significant'] ?? false) {
                $significantFindings++;
                $interpretation = is_string($result['interpretation'] ?? null) ? $result['interpretation'] : '';
                $keyFindings[] = ucfirst(str_replace('_', ' ', $name)).': '.$interpretation;
            }
        }

        // Add large effect sizes
        foreach ($effectSizes as $name => $result) {
            if (($result['effect_size'] ?? '') === 'large') {
                $keyFindings[] = ucfirst(str_replace('_', ' ', $name)).' shows large effect size';
            }
        }

        $totalTests = count($tTests) + count($chiSquareTests);
        $overallConclusion = match (true) {
            $significantFindings >= 3 => 'Strong statistical evidence for pattern differences between successful and unsuccessful careers',
            $significantFindings >= 1 => 'Some statistical evidence for pattern differences',
            default => 'No statistically significant differences found',
        };

        return [
            'sufficient_data' => true,
            'significant_findings' => $significantFindings,
            'total_tests' => $totalTests,
            'key_findings' => array_slice($keyFindings, 0, 5),
            'overall_conclusion' => $overallConclusion,
        ];
    }

    // =========================================================================
    // SUCCESS CORRELATIONS AND RECOMMENDATIONS
    // =========================================================================

    /**
     * Calculate success correlations across all factors
     *
     * @param  Collection<int, Career>  $careers
     * @return array<string, array{correlation: float, strength: string}>
     */
    protected function calculateSuccessCorrelations(Collection $careers): array
    {
        $factors = [
            'training_efficiency' => fn ($c) => $this->calculateCareerEfficiency($c->trainingSessions)['rating'],
            'friendship_rate' => fn ($c) => $this->calculateFriendshipRate($c->trainingSessions),
            'race_win_rate' => fn ($c) => $this->calculateRacePerformance($c->races)['win_rate'],
            'total_stats' => fn ($c) => $this->calculateTotalStatPoints($c->trainingSessions),
        ];

        $correlations = [];
        /** @var array<float|int> $successValues */
        $successValues = $careers->map(fn ($c) => $this->isCareerSuccessful($c) ? 1 : 0)->values()->toArray();

        foreach ($factors as $name => $extractor) {
            /** @var array<float|int> $factorValues */
            $factorValues = $careers->map($extractor)->values()->toArray();
            $correlation = $this->calculatePearsonCorrelation($factorValues, $successValues);

            $strength = match (true) {
                abs($correlation) >= 0.7 => 'strong',
                abs($correlation) >= 0.4 => 'moderate',
                abs($correlation) >= 0.2 => 'weak',
                default => 'negligible',
            };

            $correlations[$name] = [
                'correlation' => round($correlation, 3),
                'strength' => $strength,
            ];
        }

        return $correlations;
    }

    /**
     * Generate recommended patterns based on analysis
     *
     * @param  array<string, mixed>  $trainingPatterns
     * @param  array<string, mixed>  $raceStrategyPatterns
     * @param  array<string, array{correlation: float, strength: string}>  $successCorrelations
     * @return array<string>
     */
    protected function generateRecommendedPatterns(array $trainingPatterns, array $raceStrategyPatterns, array $successCorrelations): array
    {
        $recommendations = [];

        // Training recommendations
        /** @var array<string, mixed> $successfulPatterns */
        $successfulPatterns = is_array($trainingPatterns['successful_patterns'] ?? null) ? $trainingPatterns['successful_patterns'] : [];
        $avgDistribution = is_array($successfulPatterns['avg_training_distribution'] ?? null) ? $successfulPatterns['avg_training_distribution'] : [];
        if (! empty($avgDistribution)) {
            $topTraining = array_keys($avgDistribution);
            if (! empty($topTraining)) {
                arsort($avgDistribution);
                $topType = array_key_first($avgDistribution);
                $recommendations[] = "Focus on {$topType} training (most common in successful careers)";
            }
        }

        // Friendship training recommendation
        $friendshipRate = is_numeric($successfulPatterns['friendship_training_rate'] ?? null) ? (float) $successfulPatterns['friendship_training_rate'] : 0.0;
        if ($friendshipRate > 20) {
            $recommendations[] = "Prioritize friendship training when available (successful careers average {$friendshipRate}% friendship training)";
        }

        // Race strategy recommendations
        /** @var array<string, array<string, mixed>> $strategyEffectiveness */
        $strategyEffectiveness = is_array($raceStrategyPatterns['strategy_effectiveness'] ?? null) ? $raceStrategyPatterns['strategy_effectiveness'] : [];
        $bestStrategy = null;
        $bestWinRate = 0.0;

        foreach ($strategyEffectiveness as $style => $data) {
            $winRate = is_numeric($data['win_rate'] ?? null) ? (float) $data['win_rate'] : 0.0;
            $races = is_int($data['races'] ?? null) ? $data['races'] : 0;
            if ($winRate > $bestWinRate && $races >= 3) {
                $bestWinRate = $winRate;
                $bestStrategy = is_string($style) ? $style : null;
            }
        }

        if ($bestStrategy !== null) {
            $recommendations[] = 'Consider '.str_replace('_', ' ', $bestStrategy)." running style ({$bestWinRate}% win rate)";
        }

        // Correlation-based recommendations
        foreach ($successCorrelations as $factor => $data) {
            $strength = $data['strength'] ?? 'negligible';
            $correlation = $data['correlation'] ?? 0.0;
            if ($strength === 'strong' && $correlation > 0) {
                $recommendations[] = 'Higher '.str_replace('_', ' ', $factor).' strongly correlates with success';
            }
        }

        return array_slice(array_unique($recommendations), 0, 5);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Get empty comparison result
     *
     * @param  string  $message  Error/status message
     * @return array{
     *     careers: array<array<string, mixed>>,
     *     comparison_summary: array<string, mixed>,
     *     stat_comparison: array<string, array<string, mixed>>,
     *     training_comparison: array<string, mixed>,
     *     race_comparison: array<string, mixed>,
     *     key_differences: array<string>,
     *     best_performer: array<string, mixed>,
     *     message?: string
     * }
     */
    protected function getEmptyComparisonResult(string $message = ''): array
    {
        return [
            'careers' => [],
            'comparison_summary' => [
                'total_careers' => 0,
                'avg_efficiency' => 0.0,
                'avg_total_stats' => 0.0,
                'avg_win_rate' => 0.0,
                'efficiency_variance' => 0.0,
                'stat_variance' => 0.0,
            ],
            'stat_comparison' => [],
            'training_comparison' => [],
            'race_comparison' => [],
            'key_differences' => [],
            'best_performer' => ['career_id' => 0, 'career_name' => 'N/A', 'score' => 0.0, 'strengths' => []],
            'message' => $message,
        ];
    }

    /**
     * Get empty pattern result
     *
     * @return array<string, mixed>
     */
    protected function getEmptyPatternResult(): array
    {
        return [
            'training_patterns' => [],
            'race_strategy_patterns' => [],
            'decision_sequences' => [],
            'success_correlations' => [],
            'recommended_patterns' => ['Insufficient data for pattern analysis'],
        ];
    }

    /**
     * Clear comparison cache for specific careers
     *
     * @param  array<int>  $careerIds
     */
    public function clearCache(array $careerIds): void
    {
        $cacheKeys = [
            'career_comparison:'.md5(implode(',', $careerIds)),
            'success_patterns:'.md5(implode(',', $careerIds)),
            'success_factors:'.md5(implode(',', $careerIds)),
            'statistical_tests:'.md5(implode(',', $careerIds)),
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get comprehensive comparison analysis
     *
     * @param  array<int>  $careerIds
     * @return array{
     *     comparison: array<string, mixed>,
     *     patterns: array<string, mixed>,
     *     success_factors: array<string, mixed>,
     *     statistical_tests: array<string, mixed>
     * }
     */
    public function getComprehensiveAnalysis(array $careerIds): array
    {
        return [
            'comparison' => $this->compareCareers($careerIds),
            'patterns' => $this->identifySuccessPatterns($careerIds),
            'success_factors' => $this->analyzeSuccessFactors($careerIds),
            'statistical_tests' => $this->performStatisticalTests($careerIds),
        ];
    }
}
