<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Career Analytics Service
 *
 * Provides comprehensive analytics for career performance tracking including:
 * - Career performance metrics calculation (efficiency, success rates)
 * - Training effectiveness analysis with stat gain per turn tracking
 * - Goal completion tracking with timeline analysis
 * - Prediction accuracy measurement and improvement tracking
 *
 * Requirements: 15.3, 25.1 (Task 5.2.1)
 */
class CareerAnalyticsService
{
    /**
     * Cache TTL for analytics (1 hour)
     */
    protected const CACHE_TTL = 3600;

    /**
     * Stat types for analysis
     *
     * @var array<string>
     */
    protected const STAT_TYPES = ['speed', 'stamina', 'power', 'guts', 'wit'];

    /**
     * Distance categories for race analysis
     *
     * @var array<string, array{min: int, max: int}>
     */
    protected const DISTANCE_CATEGORIES = [
        'short' => ['min' => 1000, 'max' => 1400],
        'mile' => ['min' => 1401, 'max' => 1800],
        'intermediate' => ['min' => 1801, 'max' => 2400],
        'long' => ['min' => 2401, 'max' => 4000],
    ];

    // =========================================================================
    // CAREER PERFORMANCE METRICS
    // =========================================================================

    /**
     * Calculate comprehensive career performance metrics
     *
     * @return array{
     *     overall_efficiency: float,
     *     success_rate: float,
     *     completion_rate: float,
     *     average_final_grade: string,
     *     total_careers: int,
     *     completed_careers: int,
     *     metrics_by_scenario: array<string, array<string, mixed>>,
     *     performance_trend: array<string, mixed>
     * }
     */
    public function calculateCareerPerformanceMetrics(Character $character): array
    {
        $cacheKey = "analytics:career_performance:{$character->id}";

        /** @var array{overall_efficiency: float, success_rate: float, completion_rate: float, average_final_grade: string, total_careers: int, completed_careers: int, metrics_by_scenario: array<string, array<string, mixed>>, performance_trend: array<string, mixed>} $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($character) {
            $careers = Career::where('character_id', $character->id)->get();

            if ($careers->isEmpty()) {
                return $this->getEmptyCareerPerformanceResult();
            }

            $completedCareers = $careers->whereNotNull('completed_at');
            $totalCareers = $careers->count();
            $completedCount = $completedCareers->count();

            // Calculate success rate (careers that achieved target goals)
            $successfulCareers = $completedCareers->filter(function ($career) {
                return $this->isCareerSuccessful($career);
            });

            $successRate = $completedCount > 0
                ? round(($successfulCareers->count() / $completedCount) * 100, 1)
                : 0.0;

            // Calculate overall efficiency
            $overallEfficiency = $this->calculateOverallEfficiency($completedCareers);

            // Calculate average final grade
            $averageGrade = $this->calculateAverageFinalGrade($completedCareers);

            // Metrics by scenario type
            $metricsByScenario = $this->calculateMetricsByScenario($careers);

            // Performance trend over time
            $performanceTrend = $this->calculatePerformanceTrend($completedCareers);

            return [
                'overall_efficiency' => $overallEfficiency,
                'success_rate' => $successRate,
                'completion_rate' => $totalCareers > 0 ? round(($completedCount / $totalCareers) * 100, 1) : 0.0,
                'average_final_grade' => $averageGrade,
                'total_careers' => $totalCareers,
                'completed_careers' => $completedCount,
                'metrics_by_scenario' => $metricsByScenario,
                'performance_trend' => $performanceTrend,
            ];
        });

        return $result;
    }

    /**
     * Calculate overall efficiency across completed careers
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Career>  $completedCareers
     */
    protected function calculateOverallEfficiency(Collection $completedCareers): float
    {
        if ($completedCareers->isEmpty()) {
            return 0.0;
        }

        $totalEfficiency = 0.0;
        $careerCount = 0;

        foreach ($completedCareers as $career) {
            $sessions = TrainingSession::where('career_id', $career->id)->get();
            if ($sessions->isNotEmpty()) {
                $careerEfficiency = $this->calculateCareerEfficiency($sessions);
                $totalEfficiency += $careerEfficiency;
                $careerCount++;
            }
        }

        return $careerCount > 0 ? round($totalEfficiency / $careerCount, 1) : 0.0;
    }

    /**
     * Calculate efficiency for a single career's training sessions
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\TrainingSession>  $sessions
     */
    protected function calculateCareerEfficiency(Collection $sessions): float
    {
        $totalStatGains = 0;
        $totalTurns = $sessions->count();

        foreach ($sessions as $session) {
            $totalStatGains += ($session->speed_gain ?? 0)
                + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0)
                + ($session->guts_gain ?? 0)
                + ($session->wit_gain ?? 0);
        }

        // Ideal average is ~30 stat points per turn
        $idealTotal = $totalTurns * 30;

        return $idealTotal > 0 ? min(100, round(($totalStatGains / $idealTotal) * 100, 1)) : 0.0;
    }

    /**
     * Determine if a career was successful based on goals
     */
    protected function isCareerSuccessful(Career $career): bool
    {
        // Check if career has performance analysis with success indicators
        $analysis = $career->performance_analysis ?? [];

        if (is_array($analysis) && isset($analysis['goals_achieved'])) {
            return $analysis['goals_achieved'] === true;
        }

        // Default: check if completed
        return $career->completed_at !== null;
    }

    /**
     * Calculate average final grade from completed careers
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Career>  $completedCareers
     */
    protected function calculateAverageFinalGrade(Collection $completedCareers): string
    {
        if ($completedCareers->isEmpty()) {
            return 'N/A';
        }

        /** @var array<string, int> $gradeValues */
        $gradeValues = [
            'SS' => 12,
            'S' => 11,
            'A+' => 10,
            'A' => 9,
            'B+' => 8,
            'B' => 7,
            'C+' => 6,
            'C' => 5,
            'D+' => 4,
            'D' => 3,
            'E+' => 2,
            'E' => 1,
            'F' => 0,
            'G+' => -1,
        ];

        $totalValue = 0;
        $count = 0;

        foreach ($completedCareers as $career) {
            $analysis = $career->performance_analysis ?? [];
            $grade = (is_array($analysis) && isset($analysis['final_grade']) ? $analysis['final_grade'] : null);

            if ($grade && isset($gradeValues[$grade])) {
                $totalValue += $gradeValues[$grade];
                $count++;
            }
        }

        if ($count === 0) {
            return 'N/A';
        }

        $averageValue = round($totalValue / $count);

        // Convert back to grade
        $grades = array_flip($gradeValues);
        ksort($grades);

        foreach ($grades as $value => $grade) {
            if ($averageValue <= $value) {
                return $grade;
            }
        }

        return 'SS';
    }

    /**
     * Calculate metrics grouped by scenario type
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Career>  $careers
     * @return array<string, array{count: int, completed: int, success_rate: float, avg_efficiency: float}>
     */
    protected function calculateMetricsByScenario(Collection $careers): array
    {
        $scenarios = ['ura_finale', 'unity_cup'];
        $result = [];

        foreach ($scenarios as $scenario) {
            $scenarioCareers = $careers->where('scenario_type', $scenario);
            $completed = $scenarioCareers->whereNotNull('completed_at');

            $successCount = $completed->filter(fn ($c) => $this->isCareerSuccessful($c))->count();

            $result[$scenario] = [
                'count' => $scenarioCareers->count(),
                'completed' => $completed->count(),
                'success_rate' => $completed->count() > 0
                    ? round(($successCount / $completed->count()) * 100, 1)
                    : 0.0,
                'avg_efficiency' => $this->calculateOverallEfficiency($completed),
            ];
        }

        return $result;
    }

    /**
     * Calculate performance trend over completed careers
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Career>  $completedCareers
     * @return array{trend: string, improvement_rate: float, recent_performance: array<int, array<string, mixed>>}
     */
    protected function calculatePerformanceTrend(Collection $completedCareers): array
    {
        if ($completedCareers->count() < 2) {
            return [
                'trend' => 'insufficient_data',
                'improvement_rate' => 0.0,
                'recent_performance' => [],
            ];
        }

        // Sort by completion date
        $sorted = $completedCareers->sortBy('completed_at')->values();

        // Calculate efficiency for each career
        $efficiencies = [];
        foreach ($sorted as $career) {
            $sessions = TrainingSession::where('career_id', $career->id)->get();
            $efficiencies[] = [
                'career_id' => $career->id,
                'completed_at' => $career->completed_at,
                'efficiency' => $this->calculateCareerEfficiency($sessions),
            ];
        }

        // Calculate trend (compare first half to second half)
        $midpoint = (int) floor(count($efficiencies) / 2);
        $firstHalf = array_slice($efficiencies, 0, $midpoint);
        $secondHalf = array_slice($efficiencies, $midpoint);

        $firstAvg = count($firstHalf) > 0
            ? array_sum(array_column($firstHalf, 'efficiency')) / count($firstHalf)
            : 0;
        $secondAvg = count($secondHalf) > 0
            ? array_sum(array_column($secondHalf, 'efficiency')) / count($secondHalf)
            : 0;

        $improvementRate = $firstAvg > 0
            ? round((($secondAvg - $firstAvg) / $firstAvg) * 100, 1)
            : 0.0;

        $trend = match (true) {
            $improvementRate > 10 => 'improving',
            $improvementRate < -10 => 'declining',
            default => 'stable',
        };

        return [
            'trend' => $trend,
            'improvement_rate' => $improvementRate,
            'recent_performance' => array_slice($efficiencies, -5), // Last 5 careers
        ];
    }

    // =========================================================================
    // TRAINING EFFECTIVENESS ANALYSIS
    // =========================================================================

    /**
     * Calculate stat efficiency per turn for a character
     *
     * @return array{
     *     average_gains_per_turn: array<string, float>,
     *     efficiency_rating: float,
     *     best_training_type: string,
     *     improvement_suggestions: array<string>,
     *     stat_gain_distribution: array<string, array<string, mixed>>,
     *     training_type_effectiveness: array<string, array<string, mixed>>
     * }
     */
    public function calculateStatEfficiency(Character $character): array
    {
        $cacheKey = "analytics:stat_efficiency:{$character->id}";

        /** @var array{average_gains_per_turn: array<string, float>, efficiency_rating: float, best_training_type: string, improvement_suggestions: array<string>, stat_gain_distribution: array<string, array<string, mixed>>, training_type_effectiveness: array<string, array<string, mixed>>} $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($character) {
            $sessions = TrainingSession::where('character_id', $character->id)
                ->whereNotNull('training_type')
                ->get();

            if ($sessions->isEmpty()) {
                return $this->getEmptyEfficiencyResult();
            }

            // Calculate average gains per turn
            $averageGains = $this->calculateAverageGainsPerTurn($sessions);

            // Find best training type
            arsort($averageGains);
            $bestType = array_key_first($averageGains) ?? 'N/A';

            // Calculate efficiency rating
            $totalAverage = array_sum($averageGains);
            $efficiencyRating = min(100, round(($totalAverage / 30) * 100, 1));

            // Calculate stat gain distribution
            $statGainDistribution = $this->calculateStatGainDistribution($sessions);

            // Calculate training type effectiveness
            $trainingTypeEffectiveness = $this->calculateTrainingTypeEffectiveness($sessions);

            return [
                'average_gains_per_turn' => $averageGains,
                'efficiency_rating' => $efficiencyRating,
                'best_training_type' => $bestType,
                'improvement_suggestions' => $this->generateEfficiencySuggestions($averageGains, $character),
                'stat_gain_distribution' => $statGainDistribution,
                'training_type_effectiveness' => $trainingTypeEffectiveness,
            ];
        });

        return $result;
    }

    /**
     * Calculate average stat gains per turn
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array<string, float>
     */
    protected function calculateAverageGainsPerTurn(Collection $sessions): array
    {
        /** @var array<string, int> $statTotals */
        $statTotals = array_fill_keys(self::STAT_TYPES, 0);
        $turnCount = $sessions->count();

        foreach ($sessions as $session) {
            $statTotals['speed'] += $session->speed_gain ?? 0;
            $statTotals['stamina'] += $session->stamina_gain ?? 0;
            $statTotals['power'] += $session->power_gain ?? 0;
            $statTotals['guts'] += $session->guts_gain ?? 0;
            $statTotals['wit'] += $session->wit_gain ?? 0;
        }

        return array_map(
            fn ($total) => $turnCount > 0 ? round($total / $turnCount, 2) : 0.0,
            $statTotals
        );
    }

    /**
     * Calculate stat gain distribution (min, max, median, std dev)
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array<string, array{min: int, max: int, median: float, std_dev: float, total: int}>
     */
    protected function calculateStatGainDistribution(Collection $sessions): array
    {
        $distribution = [];

        foreach (self::STAT_TYPES as $stat) {
            /** @var array<int, int|float> $gains */
            $gains = $sessions->pluck("{$stat}_gain")->filter()->values()->toArray();

            if (empty($gains)) {
                $distribution[$stat] = [
                    'min' => 0,
                    'max' => 0,
                    'median' => 0.0,
                    'std_dev' => 0.0,
                    'total' => 0,
                ];

                continue;
            }

            sort($gains);
            $count = count($gains);
            $medianIndex = (int) floor($count / 2);
            $median = $count % 2 === 0
                ? ((float) $gains[$medianIndex - 1] + (float) $gains[$medianIndex]) / 2
                : (float) $gains[$medianIndex];

            $mean = array_sum($gains) / $count;
            $variance = array_sum(array_map(fn ($g) => pow((float) $g - $mean, 2), $gains)) / $count;

            $distribution[$stat] = [
                'min' => (int) min($gains),
                'max' => (int) max($gains),
                'median' => round($median, 1),
                'std_dev' => round(sqrt($variance), 2),
                'total' => (int) array_sum($gains),
            ];
        }

        return $distribution;
    }

    /**
     * Calculate effectiveness of each training type
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array<string, array{count: int, avg_total_gain: float, success_rate: float, avg_sp_gain: float}>
     */
    protected function calculateTrainingTypeEffectiveness(Collection $sessions): array
    {
        $trainingTypes = ['speed', 'stamina', 'power', 'guts', 'wit', 'rest'];
        $effectiveness = [];

        foreach ($trainingTypes as $type) {
            $typeSessions = $sessions->where('training_type', $type);
            $count = $typeSessions->count();

            if ($count === 0) {
                $effectiveness[$type] = [
                    'count' => 0,
                    'avg_total_gain' => 0.0,
                    'success_rate' => 0.0,
                    'avg_sp_gain' => 0.0,
                ];

                continue;
            }

            $totalGains = $typeSessions->sum(function ($s) {
                return ($s->speed_gain ?? 0) + ($s->stamina_gain ?? 0)
                    + ($s->power_gain ?? 0) + ($s->guts_gain ?? 0) + ($s->wit_gain ?? 0);
            });
            $totalGainsFloat = is_numeric($totalGains) ? (float) $totalGains : 0.0;

            $failures = $typeSessions->where('training_failed', true)->count();
            $spGains = $typeSessions->sum('sp_gain');
            $spGainsFloat = is_numeric($spGains) ? (float) $spGains : 0.0;

            $effectiveness[$type] = [
                'count' => $count,
                'avg_total_gain' => round($totalGainsFloat / $count, 1),
                'success_rate' => round((($count - $failures) / $count) * 100, 1),
                'avg_sp_gain' => round($spGainsFloat / $count, 1),
            ];
        }

        return $effectiveness;
    }

    /**
     * Analyze training effectiveness by career phase
     *
     * @return array<string, array{
     *     turn_range: string,
     *     avg_gains: array<string, float>,
     *     efficiency: float,
     *     session_count: int,
     *     recommendations: array<string>
     * }>
     */
    public function analyzeTrainingByPhase(Character $character): array
    {
        $cacheKey = "analytics:training_by_phase:{$character->id}";

        /** @var array<string, array{turn_range: string, avg_gains: array<string, float>, efficiency: float, session_count: int, recommendations: array<string>}> $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($character) {
            $sessions = TrainingSession::where('character_id', $character->id)->get();

            $phases = [
                'junior' => ['min' => 1, 'max' => 24],
                'classic' => ['min' => 25, 'max' => 48],
                'senior' => ['min' => 49, 'max' => 72],
            ];

            $phaseResult = [];

            foreach ($phases as $phase => $range) {
                $phaseSessions = $sessions->filter(function ($s) use ($range) {
                    $turn = $s->turn_number ?? 0;

                    return $turn >= $range['min'] && $turn <= $range['max'];
                });

                if ($phaseSessions->isEmpty()) {
                    $phaseResult[$phase] = [
                        'turn_range' => "{$range['min']}-{$range['max']}",
                        'avg_gains' => array_fill_keys(self::STAT_TYPES, 0.0),
                        'efficiency' => 0.0,
                        'session_count' => 0,
                        'recommendations' => ['No training data for this phase.'],
                    ];

                    continue;
                }

                $avgGains = $this->calculateAverageGainsPerTurn($phaseSessions);
                $efficiency = $this->calculateCareerEfficiency($phaseSessions);

                $phaseResult[$phase] = [
                    'turn_range' => "{$range['min']}-{$range['max']}",
                    'avg_gains' => $avgGains,
                    'efficiency' => $efficiency,
                    'session_count' => $phaseSessions->count(),
                    'recommendations' => $this->generatePhaseRecommendations($phase, $avgGains, $efficiency),
                ];
            }

            return $phaseResult;
        });

        return $result;
    }

    /**
     * Generate recommendations for a specific career phase
     *
     * @param  array<string, float>  $avgGains
     * @return array<string>
     */
    protected function generatePhaseRecommendations(string $phase, array $avgGains, float $efficiency): array
    {
        $recommendations = [];

        // Phase-specific recommendations
        /** @var array<string, array{focus: string, target_efficiency: int}> $phaseTargets */
        $phaseTargets = [
            'junior' => ['focus' => 'Build foundation stats', 'target_efficiency' => 60],
            'classic' => ['focus' => 'Maximize stat growth', 'target_efficiency' => 75],
            'senior' => ['focus' => 'Fine-tune for races', 'target_efficiency' => 70],
        ];

        $target = $phaseTargets[$phase] ?? $phaseTargets['junior'];

        if ($efficiency < $target['target_efficiency']) {
            $efficiencyStr = (string) $efficiency;
            $targetEfficiencyStr = (string) $target['target_efficiency'];
            $recommendations[] = "Efficiency ({$efficiencyStr}%) is below target ({$targetEfficiencyStr}%). {$target['focus']}.";
        }

        // Check for stat imbalances
        $avgTotal = count($avgGains) > 0 ? array_sum($avgGains) / count($avgGains) : 0.0;
        foreach ($avgGains as $stat => $gain) {
            if ($gain < $avgTotal * 0.5) {
                $recommendations[] = "Consider more {$stat} training to balance your build.";
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = "Good progress in {$phase} phase. Continue current strategy.";
        }

        return array_slice($recommendations, 0, 3);
    }

    // =========================================================================
    // GOAL COMPLETION TRACKING
    // =========================================================================

    /**
     * Track goal completion with timeline analysis
     *
     * @return array{
     *     goals_summary: array{total: int, completed: int, in_progress: int, failed: int},
     *     completion_rate: float,
     *     goals_by_type: array<string, array<int, array<string, mixed>>>,
     *     timeline_analysis: array<string, mixed>,
     *     upcoming_deadlines: array<int, array<string, mixed>>
     * }
     */
    public function trackGoalCompletion(Character $character): array
    {
        $cacheKey = "analytics:goal_completion:{$character->id}";

        /** @var array{goals_summary: array{total: int, completed: int, in_progress: int, failed: int}, completion_rate: float, goals_by_type: array<string, array<int, array<string, mixed>>>, timeline_analysis: array<string, mixed>, upcoming_deadlines: array<int, array<string, mixed>>} $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($character) {
            /** @var array<string, mixed> $goals */
            $goals = $character->goals ?? [];
            $currentTurn = $character->current_turn ?? 1;

            if (empty($goals) || ! is_array($goals)) {
                return $this->getEmptyGoalCompletionResult();
            }

            // Parse and analyze goals
            $goalAnalysis = $this->analyzeGoals($goals, $character);

            // Calculate timeline analysis
            $timelineAnalysis = $this->analyzeGoalTimeline($goals, $currentTurn, $character);

            // Get upcoming deadlines
            $upcomingDeadlines = $this->getUpcomingDeadlines($goals, $currentTurn);

            return [
                'goals_summary' => $goalAnalysis['summary'],
                'completion_rate' => $goalAnalysis['completion_rate'],
                'goals_by_type' => $goalAnalysis['by_type'],
                'timeline_analysis' => $timelineAnalysis,
                'upcoming_deadlines' => $upcomingDeadlines,
            ];
        });

        return $result;
    }

    /**
     * Analyze goals and their completion status
     *
     * @param  array<string, mixed>  $goals
     * @return array{summary: array{total: int, completed: int, in_progress: int, failed: int}, completion_rate: float, by_type: array<string, array<int, array<string, mixed>>>}
     */
    protected function analyzeGoals(array $goals, Character $character): array
    {
        /** @var array{total: int, completed: int, in_progress: int, failed: int} $summary */
        $summary = ['total' => 0, 'completed' => 0, 'in_progress' => 0, 'failed' => 0];
        /** @var array<string, array<int, array<string, mixed>>> $byType */
        $byType = [
            'stat_goals' => [],
            'race_goals' => [],
            'skill_goals' => [],
            'other_goals' => [],
        ];

        // Analyze stat goals
        if (isset($goals['target_stats']) && is_array($goals['target_stats'])) {
            foreach ($goals['target_stats'] as $stat => $target) {
                if (! is_string($stat) || ! is_numeric($target) || $target <= 0) {
                    continue;
                }

                $current = $character->getStat($stat);
                $status = $this->determineGoalStatus($current, (int) $target);

                $byType['stat_goals'][] = [
                    'name' => ucfirst($stat),
                    'target' => $target,
                    'current' => $current,
                    'progress' => min(100, round(($current / (int) $target) * 100, 1)),
                    'status' => $status,
                ];

                $summary['total']++;
                $summary[$status]++;
            }
        }

        // Analyze race goals
        if (isset($goals['race_goals']) && is_array($goals['race_goals'])) {
            foreach ($goals['race_goals'] as $raceGoal) {
                if (! is_array($raceGoal)) {
                    continue;
                }
                $status = ($raceGoal['completed'] ?? false) ? 'completed' : 'in_progress';

                $byType['race_goals'][] = [
                    'name' => $raceGoal['race_name'] ?? 'Unknown Race',
                    'target_position' => $raceGoal['target_position'] ?? 1,
                    'deadline_turn' => $raceGoal['deadline_turn'] ?? null,
                    'status' => $status,
                ];

                $summary['total']++;
                $summary[$status]++;
            }
        }

        // Analyze skill goals
        if (isset($goals['skill_goals']) && is_array($goals['skill_goals'])) {
            foreach ($goals['skill_goals'] as $skillGoal) {
                if (! is_array($skillGoal)) {
                    continue;
                }
                $status = ($skillGoal['acquired'] ?? false) ? 'completed' : 'in_progress';

                $byType['skill_goals'][] = [
                    'name' => $skillGoal['skill_name'] ?? 'Unknown Skill',
                    'priority' => $skillGoal['priority'] ?? 'medium',
                    'status' => $status,
                ];

                $summary['total']++;
                $summary[$status]++;
            }
        }

        $completionRate = $summary['total'] > 0
            ? round(($summary['completed'] / $summary['total']) * 100, 1)
            : 0.0;

        /** @var array{total: int, completed: int, in_progress: int, failed: int} $summaryResult */
        $summaryResult = $summary;

        return [
            'summary' => $summaryResult,
            'completion_rate' => $completionRate,
            'by_type' => $byType,
        ];
    }

    /**
     * Determine goal status based on current vs target
     */
    protected function determineGoalStatus(int $current, int $target): string
    {
        $progress = ($current / max(1, $target)) * 100;

        return match (true) {
            $progress >= 100 => 'completed',
            $progress >= 80 => 'in_progress',
            $progress >= 50 => 'in_progress',
            default => 'in_progress',
        };
    }

    /**
     * Analyze goal timeline and projected completion
     *
     * @param  array<string, mixed>  $goals
     * @return array{
     *     current_turn: int,
     *     total_turns: int,
     *     turns_remaining: int,
     *     projected_completion: array<string, mixed>,
     *     at_risk_goals: array<int, array<string, mixed>>
     * }
     */
    protected function analyzeGoalTimeline(array $goals, int $currentTurn, Character $character): array
    {
        $totalTurns = match ($character->scenario_type) {
            'unity_cup' => 72,
            default => 72,
        };

        $turnsRemaining = max(0, $totalTurns - $currentTurn);

        // Calculate projected completion for stat goals
        $projectedCompletion = [];
        $atRiskGoals = [];

        if (isset($goals['target_stats']) && is_array($goals['target_stats'])) {
            // Get recent training rate
            $recentSessions = TrainingSession::where('character_id', $character->id)
                ->orderBy('turn_number', 'desc')
                ->limit(10)
                ->get();

            $avgGainsPerTurn = $this->calculateAverageGainsPerTurn($recentSessions);

            foreach ($goals['target_stats'] as $stat => $target) {
                if (! is_string($stat) || ! is_numeric($target) || $target <= 0) {
                    continue;
                }

                $current = $character->getStat($stat);
                $remaining = max(0, (int) $target - $current);
                $avgGain = $avgGainsPerTurn[$stat] ?? 0.0;

                if ($avgGain > 0) {
                    $turnsNeeded = (int) ceil($remaining / $avgGain);
                    $projectedTurn = $currentTurn + $turnsNeeded;

                    $projectedCompletion[$stat] = [
                        'current' => $current,
                        'target' => $target,
                        'remaining' => $remaining,
                        'avg_gain_per_turn' => $avgGain,
                        'turns_needed' => $turnsNeeded,
                        'projected_completion_turn' => $projectedTurn,
                        'will_complete_in_time' => $projectedTurn <= $totalTurns,
                    ];

                    if ($projectedTurn > $totalTurns) {
                        $atRiskGoals[] = [
                            'type' => 'stat',
                            'name' => ucfirst($stat),
                            'shortfall' => $remaining - (int) ($turnsRemaining * $avgGain),
                            'turns_over' => $projectedTurn - $totalTurns,
                        ];
                    }
                } else {
                    $projectedCompletion[$stat] = [
                        'current' => $current,
                        'target' => $target,
                        'remaining' => $remaining,
                        'avg_gain_per_turn' => 0,
                        'turns_needed' => null,
                        'projected_completion_turn' => null,
                        'will_complete_in_time' => $current >= (int) $target,
                    ];

                    if ($current < (int) $target) {
                        $atRiskGoals[] = [
                            'type' => 'stat',
                            'name' => ucfirst($stat),
                            'shortfall' => $remaining,
                            'reason' => 'No recent training data',
                        ];
                    }
                }
            }
        }

        return [
            'current_turn' => $currentTurn,
            'total_turns' => $totalTurns,
            'turns_remaining' => $turnsRemaining,
            'projected_completion' => $projectedCompletion,
            'at_risk_goals' => $atRiskGoals,
        ];
    }

    /**
     * Get upcoming goal deadlines
     *
     * @param  array<string, mixed>  $goals
     * @return array<int, array{name: string, deadline_turn: int, turns_until: int, type: string}>
     */
    protected function getUpcomingDeadlines(array $goals, int $currentTurn): array
    {
        $deadlines = [];

        // Check race goals for deadlines
        if (isset($goals['race_goals']) && is_array($goals['race_goals'])) {
            foreach ($goals['race_goals'] as $raceGoal) {
                if (! is_array($raceGoal)) {
                    continue;
                }
                if (isset($raceGoal['deadline_turn']) && is_numeric($raceGoal['deadline_turn']) && ! ($raceGoal['completed'] ?? false)) {
                    $turnsUntil = (int) $raceGoal['deadline_turn'] - $currentTurn;
                    if ($turnsUntil > 0) {
                        $deadlines[] = [
                            'name' => is_string($raceGoal['race_name'] ?? null) ? $raceGoal['race_name'] : 'Unknown Race',
                            'deadline_turn' => (int) $raceGoal['deadline_turn'],
                            'turns_until' => $turnsUntil,
                            'type' => 'race',
                        ];
                    }
                }
            }
        }

        // Sort by turns until deadline
        usort($deadlines, fn ($a, $b) => $a['turns_until'] <=> $b['turns_until']);

        return array_slice($deadlines, 0, 5);
    }

    // =========================================================================
    // PREDICTION ACCURACY MEASUREMENT
    // =========================================================================

    /**
     * Measure prediction accuracy by comparing predicted vs actual outcomes
     *
     * @return array{
     *     overall_accuracy: float,
     *     stat_prediction_accuracy: array<string, float>,
     *     training_type_accuracy: array<string, array<string, mixed>>,
     *     race_prediction_accuracy: array<string, mixed>,
     *     improvement_tracking: array<string, mixed>,
     *     accuracy_trend: array<string, mixed>
     * }
     */
    public function measurePredictionAccuracy(Character $character): array
    {
        $cacheKey = "analytics:prediction_accuracy:{$character->id}";

        /** @var array{overall_accuracy: float, stat_prediction_accuracy: array<string, float>, training_type_accuracy: array<string, array<string, mixed>>, race_prediction_accuracy: array<string, mixed>, improvement_tracking: array<string, mixed>, accuracy_trend: array<string, mixed>} $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($character) {
            $sessions = TrainingSession::where('character_id', $character->id)
                ->whereNotNull('training_metadata')
                ->get();

            $races = Race::where('character_id', $character->id)
                ->whereNotNull('performance_analysis')
                ->get();

            if ($sessions->isEmpty() && $races->isEmpty()) {
                return $this->getEmptyPredictionAccuracyResult();
            }

            // Calculate stat prediction accuracy
            $statAccuracy = $this->calculateStatPredictionAccuracy($sessions);

            // Calculate training type accuracy
            $trainingTypeAccuracy = $this->calculateTrainingTypePredictionAccuracy($sessions);

            // Calculate race prediction accuracy
            $raceAccuracy = $this->calculateRacePredictionAccuracy($races);

            // Calculate overall accuracy
            $overallAccuracy = $this->calculateOverallPredictionAccuracy($statAccuracy, $raceAccuracy);

            // Track improvement over time
            $improvementTracking = $this->trackPredictionImprovement($sessions, $races);

            // Calculate accuracy trend
            $accuracyTrend = $this->calculateAccuracyTrend($sessions);

            return [
                'overall_accuracy' => $overallAccuracy,
                'stat_prediction_accuracy' => $statAccuracy,
                'training_type_accuracy' => $trainingTypeAccuracy,
                'race_prediction_accuracy' => $raceAccuracy,
                'improvement_tracking' => $improvementTracking,
                'accuracy_trend' => $accuracyTrend,
            ];
        });

        return $result;
    }

    /**
     * Calculate stat prediction accuracy from training sessions
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array<string, float>
     */
    protected function calculateStatPredictionAccuracy(Collection $sessions): array
    {
        $accuracy = [];

        foreach (self::STAT_TYPES as $stat) {
            /** @var array<int, int|float> $predictions */
            $predictions = [];
            /** @var array<int, int|float> $actuals */
            $actuals = [];

            foreach ($sessions as $session) {
                /** @var array<string, mixed> $metadata */
                $metadata = $session->training_metadata ?? [];

                // Check if we have prediction data
                if (is_array($metadata)
                    && isset($metadata['predicted_gains'])
                    && is_array($metadata['predicted_gains'])
                    && isset($metadata['predicted_gains'][$stat])
                    && is_numeric($metadata['predicted_gains'][$stat])
                    && isset($session->{"{$stat}_gain"})
                ) {
                    $predictions[] = (float) $metadata['predicted_gains'][$stat];
                    $actuals[] = (float) $session->{"{$stat}_gain"};
                }
            }

            if (count($predictions) > 0) {
                $accuracy[$stat] = $this->calculateMeanAbsolutePercentageAccuracy($predictions, $actuals);
            } else {
                $accuracy[$stat] = 0.0;
            }
        }

        return $accuracy;
    }

    /**
     * Calculate training type prediction accuracy
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array<string, array{count: int, accuracy: float, avg_deviation: float}>
     */
    protected function calculateTrainingTypePredictionAccuracy(Collection $sessions): array
    {
        $trainingTypes = ['speed', 'stamina', 'power', 'guts', 'wit'];
        $accuracy = [];

        foreach ($trainingTypes as $type) {
            $typeSessions = $sessions->where('training_type', $type);
            $count = $typeSessions->count();

            if ($count === 0) {
                $accuracy[$type] = [
                    'count' => 0,
                    'accuracy' => 0.0,
                    'avg_deviation' => 0.0,
                ];

                continue;
            }

            $totalDeviation = 0.0;
            $validPredictions = 0;

            foreach ($typeSessions as $session) {
                /** @var array<string, mixed> $metadata */
                $metadata = $session->training_metadata ?? [];

                if (is_array($metadata) && isset($metadata['predicted_total_gain']) && is_numeric($metadata['predicted_total_gain'])) {
                    $actualTotal = ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                        + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);

                    $deviation = abs((float) $metadata['predicted_total_gain'] - $actualTotal);
                    $totalDeviation += $deviation;
                    $validPredictions++;
                }
            }

            $avgDeviation = $validPredictions > 0 ? round($totalDeviation / $validPredictions, 2) : 0.0;

            // Calculate accuracy as inverse of deviation (max 100%)
            // Assuming ideal deviation is 0, and 30 points deviation = 0% accuracy
            $accuracyScore = $validPredictions > 0
                ? max(0, min(100, round(100 - ($avgDeviation / 30 * 100), 1)))
                : 0.0;

            $accuracy[$type] = [
                'count' => $count,
                'accuracy' => $accuracyScore,
                'avg_deviation' => $avgDeviation,
            ];
        }

        return $accuracy;
    }

    /**
     * Calculate race prediction accuracy
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Race>  $races
     * @return array{
     *     position_accuracy: float,
     *     win_prediction_accuracy: float,
     *     total_races: int,
     *     correct_predictions: int,
     *     position_deviation: float
     * }
     */
    protected function calculateRacePredictionAccuracy(Collection $races): array
    {
        if ($races->isEmpty()) {
            return [
                'position_accuracy' => 0.0,
                'win_prediction_accuracy' => 0.0,
                'total_races' => 0,
                'correct_predictions' => 0,
                'position_deviation' => 0.0,
            ];
        }

        $totalRaces = $races->count();
        $correctPositionPredictions = 0;
        $correctWinPredictions = 0;
        $totalPositionDeviation = 0.0;
        $validPredictions = 0;

        foreach ($races as $race) {
            /** @var array<string, mixed> $analysis */
            $analysis = $race->performance_analysis ?? [];

            if (is_array($analysis)
                && isset($analysis['predicted_position'])
                && is_numeric($analysis['predicted_position'])
                && isset($race->finish_position)
            ) {
                $predictedPosition = (int) $analysis['predicted_position'];
                $actualPosition = $race->finish_position;

                $deviation = abs($predictedPosition - $actualPosition);
                $totalPositionDeviation += $deviation;
                $validPredictions++;

                // Exact position match
                if ($predictedPosition === $actualPosition) {
                    $correctPositionPredictions++;
                }

                // Win prediction (predicted 1st and finished 1st)
                if ($predictedPosition === 1 && $actualPosition === 1) {
                    $correctWinPredictions++;
                }
            }
        }

        $positionAccuracy = $validPredictions > 0
            ? round(($correctPositionPredictions / $validPredictions) * 100, 1)
            : 0.0;

        $winPredictionAccuracy = $validPredictions > 0
            ? round(($correctWinPredictions / $validPredictions) * 100, 1)
            : 0.0;

        $avgPositionDeviation = $validPredictions > 0
            ? round($totalPositionDeviation / $validPredictions, 2)
            : 0.0;

        return [
            'position_accuracy' => $positionAccuracy,
            'win_prediction_accuracy' => $winPredictionAccuracy,
            'total_races' => $totalRaces,
            'correct_predictions' => $correctPositionPredictions,
            'position_deviation' => $avgPositionDeviation,
        ];
    }

    /**
     * Calculate overall prediction accuracy
     *
     * @param  array<string, float>  $statAccuracy
     * @param  array<string, mixed>  $raceAccuracy
     */
    protected function calculateOverallPredictionAccuracy(array $statAccuracy, array $raceAccuracy): float
    {
        $statAvg = count($statAccuracy) > 0
            ? array_sum($statAccuracy) / count($statAccuracy)
            : 0.0;

        $raceAvgValue = $raceAccuracy['position_accuracy'] ?? 0.0;
        $raceAvg = is_numeric($raceAvgValue) ? (float) $raceAvgValue : 0.0;

        // Weight: 60% training predictions, 40% race predictions
        $overall = ($statAvg * 0.6) + ($raceAvg * 0.4);

        return round($overall, 1);
    }

    /**
     * Track prediction improvement over time
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\TrainingSession>  $sessions
     * @param  \Illuminate\Support\Collection<int, \App\Models\Race>  $races
     * @return array{
     *     early_accuracy: float,
     *     recent_accuracy: float,
     *     improvement_percentage: float,
     *     trend: string,
     *     learning_rate: float
     * }
     */
    protected function trackPredictionImprovement(Collection $sessions, Collection $races): array
    {
        // Combine and sort by date
        /** @var \Illuminate\Support\Collection<int, array{date: mixed, accuracy: float, type: string}> $allPredictions */
        $allPredictions = collect();

        foreach ($sessions as $session) {
            /** @var array<string, mixed> $metadata */
            $metadata = $session->training_metadata ?? [];
            if (is_array($metadata) && isset($metadata['predicted_total_gain']) && is_numeric($metadata['predicted_total_gain'])) {
                $actualTotal = ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                    + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);

                $deviation = abs((float) $metadata['predicted_total_gain'] - $actualTotal);
                $accuracy = max(0.0, 100.0 - ($deviation / 30 * 100));

                $allPredictions->push([
                    'date' => $session->created_at,
                    'accuracy' => $accuracy,
                    'type' => 'training',
                ]);
            }
        }

        foreach ($races as $race) {
            /** @var array<string, mixed> $analysis */
            $analysis = $race->performance_analysis ?? [];
            if (is_array($analysis)
                && isset($analysis['predicted_position'])
                && is_numeric($analysis['predicted_position'])
                && isset($race->finish_position)
            ) {
                $deviation = abs((int) $analysis['predicted_position'] - $race->finish_position);
                // Position accuracy: 0 deviation = 100%, 5+ deviation = 0%
                $accuracy = max(0.0, 100.0 - ($deviation * 20));

                $allPredictions->push([
                    'date' => $race->created_at,
                    'accuracy' => $accuracy,
                    'type' => 'race',
                ]);
            }
        }

        if ($allPredictions->count() < 4) {
            return [
                'early_accuracy' => 0.0,
                'recent_accuracy' => 0.0,
                'improvement_percentage' => 0.0,
                'trend' => 'insufficient_data',
                'learning_rate' => 0.0,
            ];
        }

        // Sort by date
        $sorted = $allPredictions->sortBy('date')->values();

        // Split into early and recent halves
        $midpoint = (int) floor($sorted->count() / 2);
        $earlyPredictions = $sorted->slice(0, $midpoint);
        $recentPredictions = $sorted->slice($midpoint);

        $earlyAccuracyVal = $earlyPredictions->avg('accuracy');
        $recentAccuracyVal = $recentPredictions->avg('accuracy');
        $earlyAccuracy = is_numeric($earlyAccuracyVal) ? (float) $earlyAccuracyVal : 0.0;
        $recentAccuracy = is_numeric($recentAccuracyVal) ? (float) $recentAccuracyVal : 0.0;

        $improvementPercentage = $earlyAccuracy > 0
            ? round((($recentAccuracy - $earlyAccuracy) / $earlyAccuracy) * 100, 1)
            : 0.0;

        $trend = match (true) {
            $improvementPercentage > 10 => 'improving',
            $improvementPercentage < -10 => 'declining',
            default => 'stable',
        };

        // Calculate learning rate (improvement per 10 predictions)
        $learningRate = $sorted->count() > 0
            ? round($improvementPercentage / ($sorted->count() / 10), 2)
            : 0.0;

        return [
            'early_accuracy' => round($earlyAccuracy, 1),
            'recent_accuracy' => round($recentAccuracy, 1),
            'improvement_percentage' => $improvementPercentage,
            'trend' => $trend,
            'learning_rate' => $learningRate,
        ];
    }

    /**
     * Calculate accuracy trend over time periods
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array{
     *     weekly_accuracy: array<string, float>,
     *     monthly_accuracy: array<string, float>,
     *     trend_direction: string,
     *     consistency_score: float
     * }
     */
    protected function calculateAccuracyTrend(Collection $sessions): array
    {
        /** @var array<string, float> $weeklyAccuracy */
        $weeklyAccuracy = [];
        /** @var array<string, float> $monthlyAccuracy */
        $monthlyAccuracy = [];

        // Group sessions by week
        $sessionsByWeek = $sessions->groupBy(function ($session) {
            return $session->created_at?->format('Y-W') ?? 'unknown';
        });

        foreach ($sessionsByWeek as $week => $weekSessions) {
            if (! is_string($week) || $week === 'unknown') {
                continue;
            }

            /** @var array<int, float> $accuracies */
            $accuracies = [];
            foreach ($weekSessions as $session) {
                /** @var array<string, mixed> $metadata */
                $metadata = $session->training_metadata ?? [];
                if (is_array($metadata) && isset($metadata['predicted_total_gain']) && is_numeric($metadata['predicted_total_gain'])) {
                    $actualTotal = ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                        + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);

                    $deviation = abs((float) $metadata['predicted_total_gain'] - $actualTotal);
                    $accuracies[] = max(0.0, 100.0 - ($deviation / 30 * 100));
                }
            }

            if (count($accuracies) > 0) {
                $weeklyAccuracy[$week] = round(array_sum($accuracies) / count($accuracies), 1);
            }
        }

        // Group sessions by month
        $sessionsByMonth = $sessions->groupBy(function ($session) {
            return $session->created_at?->format('Y-m') ?? 'unknown';
        });

        foreach ($sessionsByMonth as $month => $monthSessions) {
            if (! is_string($month) || $month === 'unknown') {
                continue;
            }

            /** @var array<int, float> $accuracies */
            $accuracies = [];
            foreach ($monthSessions as $session) {
                /** @var array<string, mixed> $metadata */
                $metadata = $session->training_metadata ?? [];
                if (is_array($metadata) && isset($metadata['predicted_total_gain']) && is_numeric($metadata['predicted_total_gain'])) {
                    $actualTotal = ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                        + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);

                    $deviation = abs((float) $metadata['predicted_total_gain'] - $actualTotal);
                    $accuracies[] = max(0.0, 100.0 - ($deviation / 30 * 100));
                }
            }

            if (count($accuracies) > 0) {
                $monthlyAccuracy[$month] = round(array_sum($accuracies) / count($accuracies), 1);
            }
        }

        // Calculate trend direction
        $weeklyValues = array_values($weeklyAccuracy);
        $trendDirection = 'stable';

        if (count($weeklyValues) >= 2) {
            $firstHalf = array_slice($weeklyValues, 0, (int) ceil(count($weeklyValues) / 2));
            $secondHalf = array_slice($weeklyValues, (int) ceil(count($weeklyValues) / 2));

            $firstAvg = count($firstHalf) > 0 ? array_sum($firstHalf) / count($firstHalf) : 0;
            $secondAvg = count($secondHalf) > 0 ? array_sum($secondHalf) / count($secondHalf) : 0;

            $trendDirection = match (true) {
                $secondAvg - $firstAvg > 5 => 'improving',
                $firstAvg - $secondAvg > 5 => 'declining',
                default => 'stable',
            };
        }

        // Calculate consistency score (inverse of standard deviation)
        $consistencyScore = 0.0;
        if (count($weeklyValues) > 1) {
            $mean = array_sum($weeklyValues) / count($weeklyValues);
            $variance = array_sum(array_map(fn ($v) => pow($v - $mean, 2), $weeklyValues)) / count($weeklyValues);
            $stdDev = sqrt($variance);

            // Convert to consistency score (0-100, where 100 = perfectly consistent)
            $consistencyScore = max(0, min(100, round(100 - $stdDev, 1)));
        }

        return [
            'weekly_accuracy' => $weeklyAccuracy,
            'monthly_accuracy' => $monthlyAccuracy,
            'trend_direction' => $trendDirection,
            'consistency_score' => $consistencyScore,
        ];
    }

    /**
     * Calculate Mean Absolute Percentage Accuracy
     *
     * @param  array<int, int|float>  $predictions
     * @param  array<int, int|float>  $actuals
     */
    protected function calculateMeanAbsolutePercentageAccuracy(array $predictions, array $actuals): float
    {
        if (count($predictions) !== count($actuals) || count($predictions) === 0) {
            return 0.0;
        }

        $totalError = 0.0;
        $validCount = 0;

        for ($i = 0; $i < count($predictions); $i++) {
            $predicted = (float) $predictions[$i];
            $actual = (float) $actuals[$i];

            // Avoid division by zero
            if ($actual > 0) {
                $error = abs(($actual - $predicted) / $actual) * 100;
                $totalError += $error;
                $validCount++;
            } elseif ($predicted === 0.0 && $actual === 0.0) {
                // Perfect prediction for zero values
                $validCount++;
            }
        }

        if ($validCount === 0) {
            return 0.0;
        }

        // Convert error to accuracy (100% - error%)
        $mape = $totalError / $validCount;

        return max(0, min(100, round(100 - $mape, 1)));
    }

    /**
     * Get comprehensive analytics summary for a character
     *
     * @return array{
     *     career_performance: array<string, mixed>,
     *     training_effectiveness: array<string, mixed>,
     *     goal_completion: array<string, mixed>,
     *     prediction_accuracy: array<string, mixed>,
     *     overall_score: float,
     *     recommendations: array<string>
     * }
     */
    public function getComprehensiveAnalytics(Character $character): array
    {
        $careerPerformance = $this->calculateCareerPerformanceMetrics($character);
        $trainingEffectiveness = $this->calculateStatEfficiency($character);
        $goalCompletion = $this->trackGoalCompletion($character);
        $predictionAccuracy = $this->measurePredictionAccuracy($character);

        // Calculate overall score (weighted average)
        $overallScore = $this->calculateOverallAnalyticsScore(
            $careerPerformance,
            $trainingEffectiveness,
            $goalCompletion,
            $predictionAccuracy
        );

        // Generate recommendations based on analytics
        $recommendations = $this->generateAnalyticsRecommendations(
            $careerPerformance,
            $trainingEffectiveness,
            $goalCompletion,
            $predictionAccuracy
        );

        return [
            'career_performance' => $careerPerformance,
            'training_effectiveness' => $trainingEffectiveness,
            'goal_completion' => $goalCompletion,
            'prediction_accuracy' => $predictionAccuracy,
            'overall_score' => $overallScore,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Calculate overall analytics score
     *
     * @param  array<string, mixed>  $careerPerformance
     * @param  array<string, mixed>  $trainingEffectiveness
     * @param  array<string, mixed>  $goalCompletion
     * @param  array<string, mixed>  $predictionAccuracy
     */
    protected function calculateOverallAnalyticsScore(
        array $careerPerformance,
        array $trainingEffectiveness,
        array $goalCompletion,
        array $predictionAccuracy
    ): float {
        $careerEfficiency = isset($careerPerformance['overall_efficiency']) && is_numeric($careerPerformance['overall_efficiency'])
            ? (float) $careerPerformance['overall_efficiency']
            : 0.0;
        $trainingRating = isset($trainingEffectiveness['efficiency_rating']) && is_numeric($trainingEffectiveness['efficiency_rating'])
            ? (float) $trainingEffectiveness['efficiency_rating']
            : 0.0;
        $goalsRate = isset($goalCompletion['completion_rate']) && is_numeric($goalCompletion['completion_rate'])
            ? (float) $goalCompletion['completion_rate']
            : 0.0;
        $predictionAcc = isset($predictionAccuracy['overall_accuracy']) && is_numeric($predictionAccuracy['overall_accuracy'])
            ? (float) $predictionAccuracy['overall_accuracy']
            : 0.0;

        // Weighted average: Career 30%, Training 30%, Goals 25%, Prediction 15%
        $weightedScore = ($careerEfficiency * 0.30)
            + ($trainingRating * 0.30)
            + ($goalsRate * 0.25)
            + ($predictionAcc * 0.15);

        return round($weightedScore, 1);
    }

    /**
     * Generate analytics-based recommendations
     *
     * @param  array<string, mixed>  $careerPerformance
     * @param  array<string, mixed>  $trainingEffectiveness
     * @param  array<string, mixed>  $goalCompletion
     * @param  array<string, mixed>  $predictionAccuracy
     * @return array<string>
     */
    protected function generateAnalyticsRecommendations(
        array $careerPerformance,
        array $trainingEffectiveness,
        array $goalCompletion,
        array $predictionAccuracy
    ): array {
        $recommendations = [];

        // Career performance recommendations
        $successRate = isset($careerPerformance['success_rate']) && is_numeric($careerPerformance['success_rate'])
            ? (float) $careerPerformance['success_rate']
            : 0.0;
        if ($successRate < 50) {
            $recommendations[] = 'Career success rate is below 50%. Review your training strategies and goal setting.';
        }

        $perfTrend = is_array($careerPerformance['performance_trend'] ?? null)
            ? ($careerPerformance['performance_trend']['trend'] ?? '')
            : '';
        if ($perfTrend === 'declining') {
            $recommendations[] = 'Performance is declining. Consider adjusting your approach based on successful past careers.';
        }

        // Training effectiveness recommendations
        $efficiencyRating = isset($trainingEffectiveness['efficiency_rating']) && is_numeric($trainingEffectiveness['efficiency_rating'])
            ? (float) $trainingEffectiveness['efficiency_rating']
            : 0.0;
        if ($efficiencyRating < 60) {
            $recommendations[] = 'Training efficiency is low. Focus on friendship training and support card synergies.';
        }

        $bestType = is_string($trainingEffectiveness['best_training_type'] ?? null)
            ? $trainingEffectiveness['best_training_type']
            : '';
        if ($bestType !== '' && $bestType !== 'N/A') {
            $recommendations[] = "Your most effective training type is {$bestType}. Consider building around this strength.";
        }

        // Goal completion recommendations
        $timelineAnalysis = is_array($goalCompletion['timeline_analysis'] ?? null) ? $goalCompletion['timeline_analysis'] : [];
        $atRiskGoals = is_array($timelineAnalysis['at_risk_goals'] ?? null) ? $timelineAnalysis['at_risk_goals'] : [];
        if (count($atRiskGoals) > 0) {
            $goalNames = array_column($atRiskGoals, 'name');
            $recommendations[] = 'At-risk goals: '.implode(', ', array_slice($goalNames, 0, 3)).'. Prioritize these in upcoming turns.';
        }

        // Prediction accuracy recommendations
        $overallAccuracy = isset($predictionAccuracy['overall_accuracy']) && is_numeric($predictionAccuracy['overall_accuracy'])
            ? (float) $predictionAccuracy['overall_accuracy']
            : 0.0;
        if ($overallAccuracy < 70) {
            $recommendations[] = 'Prediction accuracy is below 70%. The system is still learning your play patterns.';
        }

        $improvementTracking = is_array($predictionAccuracy['improvement_tracking'] ?? null)
            ? $predictionAccuracy['improvement_tracking']
            : [];
        $improvementTrend = is_string($improvementTracking['trend'] ?? null) ? $improvementTracking['trend'] : '';
        if ($improvementTrend === 'improving') {
            $recommendations[] = 'Prediction accuracy is improving! Continue providing feedback on training outcomes.';
        }

        // Limit to top 5 recommendations
        return array_slice($recommendations, 0, 5);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Generate efficiency improvement suggestions
     *
     * @param  array<string, float>  $averageGains
     * @return array<string>
     */
    protected function generateEfficiencySuggestions(array $averageGains, Character $character): array
    {
        $suggestions = [];

        // Find weakest stat
        if (count($averageGains) > 0) {
            $minGain = min($averageGains);
            $weakestStats = array_keys($averageGains, $minGain);
            $weakestStat = $weakestStats[0] ?? null;
            if ($weakestStat !== null && is_string($weakestStat) && $minGain < 3) {
                $suggestions[] = "Consider more {$weakestStat} training to balance your build.";
            }
        }

        // Check if any stat is significantly behind goals
        /** @var array<string, mixed> $goals */
        $goals = $character->goals ?? [];
        if (is_array($goals) && isset($goals['target_stats']) && is_array($goals['target_stats'])) {
            foreach ($goals['target_stats'] as $stat => $target) {
                if (! is_string($stat) || ! is_numeric($target) || (int) $target <= 0) {
                    continue;
                }
                $current = $character->getStat($stat);
                $targetInt = (int) $target;
                if (($current / $targetInt) < 0.5) {
                    $percentage = (string) round(($current / $targetInt) * 100);
                    $suggestions[] = "Focus on {$stat} training - currently at {$percentage}% of target.";
                }
            }
        }

        // General suggestions based on efficiency
        $totalAvg = array_sum($averageGains);
        if ($totalAvg < 20) {
            $suggestions[] = 'Overall training efficiency is low. Prioritize friendship training and support card bonuses.';
        }

        return array_slice($suggestions, 0, 3);
    }

    /**
     * Get empty career performance result
     *
     * @return array<string, mixed>
     */
    protected function getEmptyCareerPerformanceResult(): array
    {
        return [
            'overall_efficiency' => 0.0,
            'success_rate' => 0.0,
            'completion_rate' => 0.0,
            'average_final_grade' => 'N/A',
            'total_careers' => 0,
            'completed_careers' => 0,
            'metrics_by_scenario' => [],
            'performance_trend' => [
                'trend' => 'insufficient_data',
                'improvement_rate' => 0.0,
                'recent_performance' => [],
            ],
        ];
    }

    /**
     * Get empty efficiency result
     *
     * @return array<string, mixed>
     */
    protected function getEmptyEfficiencyResult(): array
    {
        return [
            'average_gains_per_turn' => array_fill_keys(self::STAT_TYPES, 0.0),
            'efficiency_rating' => 0.0,
            'best_training_type' => 'N/A',
            'improvement_suggestions' => ['No training data available. Start training to see analytics.'],
            'stat_gain_distribution' => [],
            'training_type_effectiveness' => [],
        ];
    }

    /**
     * Get empty goal completion result
     *
     * @return array<string, mixed>
     */
    protected function getEmptyGoalCompletionResult(): array
    {
        return [
            'goals_summary' => ['total' => 0, 'completed' => 0, 'in_progress' => 0, 'failed' => 0],
            'completion_rate' => 0.0,
            'goals_by_type' => [],
            'timeline_analysis' => [
                'current_turn' => 1,
                'total_turns' => 72,
                'turns_remaining' => 71,
                'projected_completion' => [],
                'at_risk_goals' => [],
            ],
            'upcoming_deadlines' => [],
        ];
    }

    /**
     * Get empty prediction accuracy result
     *
     * @return array<string, mixed>
     */
    protected function getEmptyPredictionAccuracyResult(): array
    {
        return [
            'overall_accuracy' => 0.0,
            'stat_prediction_accuracy' => array_fill_keys(self::STAT_TYPES, 0.0),
            'training_type_accuracy' => [],
            'race_prediction_accuracy' => [
                'position_accuracy' => 0.0,
                'win_prediction_accuracy' => 0.0,
                'total_races' => 0,
                'correct_predictions' => 0,
                'position_deviation' => 0.0,
            ],
            'improvement_tracking' => [
                'early_accuracy' => 0.0,
                'recent_accuracy' => 0.0,
                'improvement_percentage' => 0.0,
                'trend' => 'insufficient_data',
                'learning_rate' => 0.0,
            ],
            'accuracy_trend' => [
                'weekly_accuracy' => [],
                'monthly_accuracy' => [],
                'trend_direction' => 'stable',
                'consistency_score' => 0.0,
            ],
        ];
    }

    /**
     * Clear analytics cache for a character
     */
    public function clearCache(Character $character): void
    {
        $cacheKeys = [
            "analytics:career_performance:{$character->id}",
            "analytics:stat_efficiency:{$character->id}",
            "analytics:training_by_phase:{$character->id}",
            "analytics:goal_completion:{$character->id}",
            "analytics:prediction_accuracy:{$character->id}",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
