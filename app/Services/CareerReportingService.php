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
 * Career Reporting Service
 *
 * Provides intelligent reporting capabilities including:
 * - Career summary reports with key insights and recommendations
 * - Improvement recommendations based on historical performance
 * - Exportable reports in multiple formats (PDF, CSV, JSON)
 * - Automated insights generation using statistical analysis
 *
 * Requirements: 15.5, 25.5 (Task 5.2.4)
 */
class CareerReportingService
{
    /**
     * Cache TTL for reports (1 hour)
     */
    protected const CACHE_TTL = 3600;

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
     * Grade values for conversion
     *
     * @var array<string, int>
     */
    protected const GRADE_VALUES = [
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

    public function __construct(
        protected CareerAnalyticsService $analyticsService
    ) {}

    // =========================================================================
    // CAREER SUMMARY REPORT GENERATION
    // =========================================================================

    /**
     * Generate comprehensive career summary report with key insights
     *
     * @return array{
     *     report_metadata: array<string, mixed>,
     *     executive_summary: array<string, mixed>,
     *     performance_overview: array<string, mixed>,
     *     training_analysis: array<string, mixed>,
     *     race_analysis: array<string, mixed>,
     *     skill_analysis: array<string, mixed>,
     *     key_insights: array<string>,
     *     recommendations: array<string>,
     *     statistical_summary: array<string, mixed>
     * }
     */
    public function generateCareerSummaryReport(Career $career): array
    {
        $cacheKey = "report:career_summary:{$career->id}";

        /** @var array{report_metadata: array<string, mixed>, executive_summary: array<string, mixed>, performance_overview: array<string, mixed>, training_analysis: array<string, mixed>, race_analysis: array<string, mixed>, skill_analysis: array<string, mixed>, key_insights: array<string>, recommendations: array<string>, statistical_summary: array<string, mixed>} $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($career): array {
            $career->load(['character', 'trainingSessions', 'races']);

            /** @var Collection<int, \App\Models\TrainingSession> $sessions */
            $sessions = $career->trainingSessions;
            /** @var Collection<int, \App\Models\Race> $races */
            $races = $career->races;
            $character = $career->character;

            // Build report sections
            $reportMetadata = $this->buildReportMetadata($career);
            $executiveSummary = $this->buildExecutiveSummary($career, $sessions, $races);
            $performanceOverview = $this->buildPerformanceOverview($career, $sessions, $races);
            $trainingAnalysis = $this->buildTrainingAnalysis($sessions);
            $raceAnalysis = $this->buildRaceAnalysis($races);
            $skillAnalysis = $this->buildSkillAnalysis($sessions, $character);
            $statisticalSummary = $this->buildStatisticalSummary($sessions, $races);

            // Generate insights and recommendations
            $keyInsights = $this->generateKeyInsights($performanceOverview, $trainingAnalysis, $raceAnalysis);
            $recommendations = $this->generateImprovementRecommendations($career, $performanceOverview, $trainingAnalysis);

            return [
                'report_metadata' => $reportMetadata,
                'executive_summary' => $executiveSummary,
                'performance_overview' => $performanceOverview,
                'training_analysis' => $trainingAnalysis,
                'race_analysis' => $raceAnalysis,
                'skill_analysis' => $skillAnalysis,
                'key_insights' => $keyInsights,
                'recommendations' => $recommendations,
                'statistical_summary' => $statisticalSummary,
            ];
        });

        return $result;
    }

    /**
     * Build report metadata
     *
     * @return array{
     *     report_id: string,
     *     generated_at: string,
     *     career_id: int,
     *     character_name: string,
     *     scenario_type: string,
     *     report_version: string
     * }
     */
    protected function buildReportMetadata(Career $career): array
    {
        return [
            'report_id' => 'RPT-'.strtoupper(substr(md5((string) $career->id.now()->timestamp), 0, 8)),
            'generated_at' => now()->toIso8601String(),
            'career_id' => $career->id,
            'character_name' => $career->character->name ?? 'Unknown',
            'scenario_type' => $career->scenario_type ?? 'ura_finale',
            'report_version' => '1.0.0',
        ];
    }

    /**
     * Build executive summary
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @param  Collection<int, \App\Models\Race>  $races
     * @return array{
     *     career_status: string,
     *     overall_grade: string,
     *     total_turns: int,
     *     completion_percentage: float,
     *     highlight_stats: array<string, mixed>,
     *     key_achievements: array<string>
     * }
     */
    protected function buildExecutiveSummary(Career $career, Collection $sessions, Collection $races): array
    {
        $maxTurnFromSessions = $sessions->max('turn_number');
        $totalTurns = $career->current_turn ?? (is_int($maxTurnFromSessions) ? $maxTurnFromSessions : 0);
        $maxTurns = 72;
        /** @phpstan-ignore greater.alwaysTrue (kept for clarity when maxTurns might change) */
        $completionPercentage = $maxTurns > 0 ? round(($totalTurns / $maxTurns) * 100, 1) : 0.0;

        // Calculate overall grade
        $overallGrade = $this->calculateOverallGrade($sessions, $races);

        // Get highlight stats
        $highlightStats = $this->getHighlightStats($sessions);

        // Get key achievements
        $keyAchievements = $this->getKeyAchievements($career, $sessions, $races);

        return [
            'career_status' => $career->completed_at ? 'completed' : 'in_progress',
            'overall_grade' => $overallGrade,
            'total_turns' => $totalTurns,
            'completion_percentage' => $completionPercentage,
            'highlight_stats' => $highlightStats,
            'key_achievements' => $keyAchievements,
        ];
    }

    /**
     * Calculate overall grade based on performance
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @param  Collection<int, \App\Models\Race>  $races
     */
    protected function calculateOverallGrade(Collection $sessions, Collection $races): string
    {
        $totalStatGains = 0;
        foreach ($sessions as $session) {
            /** @var \App\Models\TrainingSession $session */
            $totalStatGains += ($session->speed_gain ?? 0)
                + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0)
                + ($session->guts_gain ?? 0)
                + ($session->wit_gain ?? 0);
        }

        $turnCount = $sessions->count();
        $avgGainPerTurn = $turnCount > 0 ? $totalStatGains / $turnCount : 0;

        // Factor in race performance
        $raceWinRate = $races->count() > 0
            ? ($races->where('won_race', true)->count() / $races->count()) * 100
            : 0;

        // Calculate composite score
        $score = ($avgGainPerTurn * 2) + ($raceWinRate * 0.5);

        return match (true) {
            $score >= 80 => 'SS',
            $score >= 70 => 'S',
            $score >= 60 => 'A+',
            $score >= 50 => 'A',
            $score >= 40 => 'B+',
            $score >= 30 => 'B',
            $score >= 20 => 'C+',
            $score >= 15 => 'C',
            $score >= 10 => 'D+',
            $score >= 5 => 'D',
            default => 'E',
        };
    }

    /**
     * Get highlight stats from training sessions
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array{best_stat: string, best_value: int, total_gains: int, avg_per_turn: float, has_data: bool}
     */
    protected function getHighlightStats(Collection $sessions): array
    {
        if ($sessions->isEmpty()) {
            return [
                'best_stat' => 'n/a',
                'best_value' => 0,
                'total_gains' => 0,
                'avg_per_turn' => 0.0,
                'has_data' => false,
            ];
        }

        $statTotals = array_fill_keys(self::STAT_TYPES, 0);

        /** @var TrainingSession $session */
        foreach ($sessions as $session) {
            foreach (self::STAT_TYPES as $stat) {
                $statTotals[$stat] += $session->{"{$stat}_gain"} ?? 0;
            }
        }

        arsort($statTotals);
        $bestStat = array_key_first($statTotals);
        $totalGains = array_sum($statTotals);
        $turnCount = $sessions->count();

        return [
            'best_stat' => $bestStat ?? 'speed',
            'best_value' => $statTotals[$bestStat] ?? 0,
            'total_gains' => $totalGains,
            'avg_per_turn' => $turnCount > 0 ? round($totalGains / $turnCount, 2) : 0.0,
            'has_data' => true,
        ];
    }

    /**
     * Get key achievements from career
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @param  Collection<int, \App\Models\Race>  $races
     * @return array<string>
     */
    protected function getKeyAchievements(Career $career, Collection $sessions, Collection $races): array
    {
        $achievements = [];

        // Check for race wins
        $wins = $races->where('won_race', true)->count();
        if ($wins > 0) {
            $achievements[] = "Won {$wins} race(s)";
        }

        // Check for G1 wins
        $g1Wins = $races->where('race_grade', '=', 'G1')->where('won_race', true)->count();
        if ($g1Wins > 0) {
            $achievements[] = "Won {$g1Wins} G1 race(s)";
        }

        // Check for high efficiency
        $totalGains = 0;
        foreach ($sessions as $session) {
            /** @var \App\Models\TrainingSession $session */
            $totalGains += ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);
        }
        $avgGain = $sessions->count() > 0 ? $totalGains / $sessions->count() : 0;
        if ($avgGain >= 25) {
            $achievements[] = 'High training efficiency achieved';
        }

        // Check for no failures
        $failures = $sessions->where('training_failed', true)->count();
        if ($failures === 0 && $sessions->count() > 10) {
            $achievements[] = 'Perfect training record (no failures)';
        }

        // Check for friendship training
        $friendshipCount = $sessions->where('friendship_training', true)->count();
        if ($friendshipCount >= 10) {
            $achievements[] = "Achieved {$friendshipCount} friendship training sessions";
        }

        return array_slice($achievements, 0, 5);
    }

    /**
     * Build performance overview
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @param  Collection<int, \App\Models\Race>  $races
     * @return array{
     *     efficiency_rating: float,
     *     stat_distribution: array<string, int>,
     *     training_success_rate: float,
     *     race_win_rate: float,
     *     sp_earned: int,
     *     phase_performance: array<string, array<string, mixed>>
     * }
     */
    protected function buildPerformanceOverview(Career $career, Collection $sessions, Collection $races): array
    {
        // Calculate efficiency
        $totalGains = 0;
        $turnCount = $sessions->count();
        foreach ($sessions as $session) {
            /** @var \App\Models\TrainingSession $session */
            $totalGains += ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);
        }
        $idealTotal = $turnCount * 30;
        $efficiencyRating = $idealTotal > 0 ? min(100, round(($totalGains / $idealTotal) * 100, 1)) : 0.0;

        // Stat distribution
        $statDistribution = array_fill_keys(self::STAT_TYPES, 0);
        /** @var TrainingSession $session */
        foreach ($sessions as $session) {
            foreach (self::STAT_TYPES as $stat) {
                $statDistribution[$stat] += $session->{"{$stat}_gain"} ?? 0;
            }
        }

        // Training success rate
        $failures = $sessions->where('training_failed', true)->count();
        $trainingSuccessRate = $turnCount > 0 ? round((($turnCount - $failures) / $turnCount) * 100, 1) : 100.0;

        // Race win rate
        $raceCount = $races->count();
        $raceWins = $races->where('won_race', true)->count();
        $raceWinRate = $raceCount > 0 ? round(($raceWins / $raceCount) * 100, 1) : 0.0;

        // SP earned
        /** @var int|float $sessionSpSum */
        $sessionSpSum = $sessions->sum('sp_gain');
        /** @var int|float $raceSpSum */
        $raceSpSum = $races->sum('sp_reward');
        $spEarned = (int) $sessionSpSum + (int) $raceSpSum;

        // Phase performance
        $phasePerformance = $this->calculatePhasePerformance($sessions);

        return [
            'efficiency_rating' => $efficiencyRating,
            'stat_distribution' => $statDistribution,
            'training_success_rate' => $trainingSuccessRate,
            'race_win_rate' => $raceWinRate,
            'sp_earned' => $spEarned,
            'phase_performance' => $phasePerformance,
        ];
    }

    /**
     * Calculate performance by career phase
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array<string, array{turns: int, efficiency: float, avg_gains: array<string, float>}>
     */
    protected function calculatePhasePerformance(Collection $sessions): array
    {
        $phasePerformance = [];

        foreach (self::CAREER_PHASES as $phase => $range) {
            $phaseSessions = $sessions->filter(function ($s) use ($range) {
                /** @var \App\Models\TrainingSession $s */
                $turn = $s->turn_number ?? 0;

                return $turn >= $range['min'] && $turn <= $range['max'];
            });

            $turnCount = $phaseSessions->count();
            $totalGains = 0;
            $statGains = array_fill_keys(self::STAT_TYPES, 0);

            /** @var TrainingSession $session */
            foreach ($phaseSessions as $session) {
                foreach (self::STAT_TYPES as $stat) {
                    $gain = $session->{"{$stat}_gain"} ?? 0;
                    $statGains[$stat] += $gain;
                    $totalGains += $gain;
                }
            }

            $idealTotal = $turnCount * 30;
            $efficiency = $idealTotal > 0 ? min(100, round(($totalGains / $idealTotal) * 100, 1)) : 0.0;

            $avgGains = array_map(
                fn ($total) => $turnCount > 0 ? round($total / $turnCount, 2) : 0.0,
                $statGains
            );

            $phasePerformance[$phase] = [
                'turns' => $turnCount,
                'efficiency' => $efficiency,
                'avg_gains' => $avgGains,
            ];
        }

        return $phasePerformance;
    }

    /**
     * Build training analysis
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array{
     *     total_sessions: int,
     *     training_type_breakdown: array<string, array<string, mixed>>,
     *     best_training_type: string,
     *     worst_training_type: string,
     *     friendship_training_stats: array<string, mixed>,
     *     failure_analysis: array<string, mixed>
     * }
     */
    protected function buildTrainingAnalysis(Collection $sessions): array
    {
        $totalSessions = $sessions->count();

        // Training type breakdown
        $typeBreakdown = $this->calculateTrainingTypeBreakdown($sessions);

        // Find best and worst training types
        $typeEfficiencies = [];
        foreach ($typeBreakdown as $type => $data) {
            $count = $data['count'];
            if ($count > 0) {
                $typeEfficiencies[$type] = $data['avg_total_gain'];
            }
        }

        arsort($typeEfficiencies);
        $bestType = array_key_first($typeEfficiencies) ?? 'speed';
        asort($typeEfficiencies);
        $worstType = array_key_first($typeEfficiencies) ?? 'rest';

        // Friendship training stats
        $friendshipStats = $this->calculateFriendshipTrainingStats($sessions);

        // Failure analysis
        $failureAnalysis = $this->analyzeTrainingFailures($sessions);

        return [
            'total_sessions' => $totalSessions,
            'training_type_breakdown' => $typeBreakdown,
            'best_training_type' => $bestType,
            'worst_training_type' => $worstType,
            'friendship_training_stats' => $friendshipStats,
            'failure_analysis' => $failureAnalysis,
        ];
    }

    /**
     * Calculate training type breakdown
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array<string, array{count: int, percentage: float, avg_total_gain: float, total_gains: array<string, int>}>
     */
    protected function calculateTrainingTypeBreakdown(Collection $sessions): array
    {
        $types = ['speed', 'stamina', 'power', 'guts', 'wit', 'rest'];
        $breakdown = [];
        $totalSessions = $sessions->count();

        foreach ($types as $type) {
            $typeSessions = $sessions->where('training_type', $type);
            $count = $typeSessions->count();

            $totalGains = array_fill_keys(self::STAT_TYPES, 0);
            $totalAllGains = 0;

            /** @var TrainingSession $session */
            foreach ($typeSessions as $session) {
                foreach (self::STAT_TYPES as $stat) {
                    $gain = $session->{"{$stat}_gain"} ?? 0;
                    $totalGains[$stat] += $gain;
                    $totalAllGains += $gain;
                }
            }

            $breakdown[$type] = [
                'count' => $count,
                'percentage' => $totalSessions > 0 ? round(($count / $totalSessions) * 100, 1) : 0.0,
                'avg_total_gain' => $count > 0 ? round($totalAllGains / $count, 2) : 0.0,
                'total_gains' => $totalGains,
            ];
        }

        return $breakdown;
    }

    /**
     * Calculate friendship training statistics
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array{count: int, percentage: float, avg_bonus: float, total_bonus_gains: int}
     */
    protected function calculateFriendshipTrainingStats(Collection $sessions): array
    {
        $friendshipSessions = $sessions->where('friendship_training', true);
        $count = $friendshipSessions->count();
        $totalSessions = $sessions->count();

        $totalBonusGains = 0;
        foreach ($friendshipSessions as $session) {
            /** @var \App\Models\TrainingSession $session */
            $totalBonusGains += ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);
        }

        return [
            'count' => $count,
            'percentage' => $totalSessions > 0 ? round(($count / $totalSessions) * 100, 1) : 0.0,
            'avg_bonus' => $count > 0 ? round($totalBonusGains / $count, 2) : 0.0,
            'total_bonus_gains' => $totalBonusGains,
        ];
    }

    /**
     * Analyze training failures
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array{total_failures: int, failure_rate: float, failures_by_type: array<string, int>, failure_turns: array<int>}
     */
    protected function analyzeTrainingFailures(Collection $sessions): array
    {
        $failures = $sessions->where('training_failed', true);
        $totalFailures = $failures->count();
        $totalSessions = $sessions->count();

        /** @var array<string, int> $failuresByType */
        $failuresByType = [];
        /** @var array<int> $failureTurns */
        $failureTurns = [];

        foreach ($failures as $failure) {
            /** @var \App\Models\TrainingSession $failure */
            $type = $failure->training_type ?? 'unknown';
            $failuresByType[$type] = ($failuresByType[$type] ?? 0) + 1;
            $failureTurns[] = $failure->turn_number ?? 0;
        }

        return [
            'total_failures' => $totalFailures,
            'failure_rate' => $totalSessions > 0 ? round(($totalFailures / $totalSessions) * 100, 2) : 0.0,
            'failures_by_type' => $failuresByType,
            'failure_turns' => $failureTurns,
        ];
    }

    /**
     * Build race analysis
     *
     * @param  Collection<int, \App\Models\Race>  $races
     * @return array{
     *     total_races: int,
     *     wins: int,
     *     win_rate: float,
     *     avg_position: float,
     *     performance_by_grade: array<string, array<string, mixed>>,
     *     performance_by_distance: array<string, array<string, mixed>>,
     *     best_race: array<string, mixed>|null,
     *     worst_race: array<string, mixed>|null
     * }
     */
    protected function buildRaceAnalysis(Collection $races): array
    {
        $totalRaces = $races->count();
        $wins = $races->where('won_race', true)->count();
        $winRate = $totalRaces > 0 ? round(($wins / $totalRaces) * 100, 1) : 0.0;
        $avgPosition = $totalRaces > 0 ? round((float) $races->avg('finish_position'), 2) : 0.0;

        // Performance by grade
        $performanceByGrade = $this->calculateRacePerformanceByGrade($races);

        // Performance by distance
        $performanceByDistance = $this->calculateRacePerformanceByDistance($races);

        // Best and worst races
        $bestRace = $this->findBestRace($races);
        $worstRace = $this->findWorstRace($races);

        return [
            'total_races' => $totalRaces,
            'wins' => $wins,
            'win_rate' => $winRate,
            'avg_position' => $avgPosition,
            'performance_by_grade' => $performanceByGrade,
            'performance_by_distance' => $performanceByDistance,
            'best_race' => $bestRace,
            'worst_race' => $worstRace,
        ];
    }

    /**
     * Calculate race performance by grade
     *
     * @param  Collection<int, \App\Models\Race>  $races
     * @return array<string, array{count: int, wins: int, win_rate: float, avg_position: float}>
     */
    protected function calculateRacePerformanceByGrade(Collection $races): array
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
                'avg_position' => $count > 0 ? round((float) $gradeRaces->avg('finish_position'), 2) : 0.0,
            ];
        }

        return $performance;
    }

    /**
     * Calculate race performance by distance category
     *
     * @param  Collection<int, \App\Models\Race>  $races
     * @return array<string, array{count: int, wins: int, win_rate: float, avg_position: float}>
     */
    protected function calculateRacePerformanceByDistance(Collection $races): array
    {
        $distanceCategories = [
            'short' => ['min' => 1000, 'max' => 1400],
            'mile' => ['min' => 1401, 'max' => 1800],
            'intermediate' => ['min' => 1801, 'max' => 2400],
            'long' => ['min' => 2401, 'max' => 4000],
        ];

        $performance = [];

        foreach ($distanceCategories as $category => $range) {
            $categoryRaces = $races->filter(function ($race) use ($range) {
                /** @var \App\Models\Race $race */
                $distance = $race->distance ?? 0;

                return $distance >= $range['min'] && $distance <= $range['max'];
            });

            $count = $categoryRaces->count();
            $wins = $categoryRaces->where('won_race', true)->count();

            $performance[$category] = [
                'count' => $count,
                'wins' => $wins,
                'win_rate' => $count > 0 ? round(($wins / $count) * 100, 1) : 0.0,
                'avg_position' => $count > 0 ? round((float) $categoryRaces->avg('finish_position'), 2) : 0.0,
            ];
        }

        return $performance;
    }

    /**
     * Find best race performance
     *
     * @param  Collection<int, \App\Models\Race>  $races
     * @return array{race_name: string, grade: string, position: int, turn: int}|null
     */
    protected function findBestRace(Collection $races): ?array
    {
        $bestRace = $races->where('won_race', true)
            ->sortBy(function ($race) {
                /** @var \App\Models\Race $race */
                $gradeOrder = ['G1' => 1, 'G2' => 2, 'G3' => 3, 'OP' => 4, 'Pre-OP' => 5];

                return $gradeOrder[$race->race_grade] ?? 6;
            })
            ->first();

        if (! $bestRace) {
            $bestRace = $races->sortBy('finish_position')->first();
        }

        if (! $bestRace) {
            return null;
        }

        /** @var \App\Models\Race $bestRace */
        return [
            'race_name' => $bestRace->race_name ?? 'Unknown Race',
            'grade' => $bestRace->race_grade ?? 'Unknown',
            'position' => $bestRace->finish_position ?? 0,
            'turn' => $bestRace->turn_number ?? 0,
        ];
    }

    /**
     * Find worst race performance
     *
     * @param  Collection<int, \App\Models\Race>  $races
     * @return array{race_name: string, grade: string, position: int, turn: int}|null
     */
    protected function findWorstRace(Collection $races): ?array
    {
        $worstRace = $races->sortByDesc('finish_position')->first();

        if (! $worstRace) {
            return null;
        }

        /** @var \App\Models\Race $worstRace */
        return [
            'race_name' => $worstRace->race_name ?? 'Unknown Race',
            'grade' => $worstRace->race_grade ?? 'Unknown',
            'position' => $worstRace->finish_position ?? 0,
            'turn' => $worstRace->turn_number ?? 0,
        ];
    }

    /**
     * Build skill analysis
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array{
     *     skills_acquired: int,
     *     total_sp_spent: int,
     *     hint_efficiency: float,
     *     skill_sources: array<string, int>
     * }
     */
    protected function buildSkillAnalysis(Collection $sessions, ?Character $character): array
    {
        $skillsAcquired = 0;
        $totalSpSpent = 0;
        /** @var array<string, int> $skillSources */
        $skillSources = [];

        foreach ($sessions as $session) {
            /** @var \App\Models\TrainingSession $session */
            /** @var array<mixed> $hints */
            $hints = $session->skill_hints_obtained ?? [];
            $skillsAcquired += count($hints);

            foreach ($hints as $hint) {
                if (is_array($hint)) {
                    $source = isset($hint['source']) && is_string($hint['source']) ? $hint['source'] : 'training';
                    $skillSources[$source] = ($skillSources[$source] ?? 0) + 1;
                }
            }
        }

        // Get SP spent from character if available
        if ($character !== null) {
            $acquisitions = $character->skillAcquisitions ?? collect();
            /** @var int|float $spSum */
            $spSum = $acquisitions->sum('final_sp_cost');
            $totalSpSpent = (int) $spSum;
        }

        // Calculate hint efficiency (skills per 10 training sessions)
        $hintEfficiency = $sessions->count() > 0
            ? round(($skillsAcquired / $sessions->count()) * 10, 2)
            : 0.0;

        return [
            'skills_acquired' => $skillsAcquired,
            'total_sp_spent' => $totalSpSpent,
            'hint_efficiency' => $hintEfficiency,
            'skill_sources' => $skillSources,
        ];
    }

    /**
     * Build statistical summary using statistical analysis
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @param  Collection<int, \App\Models\Race>  $races
     * @return array{
     *     stat_statistics: array<string, array<string, mixed>>,
     *     training_statistics: array<string, mixed>,
     *     race_statistics: array<string, mixed>,
     *     correlation_analysis: array<string, mixed>,
     *     has_training_data: bool
     * }
     */
    protected function buildStatisticalSummary(Collection $sessions, Collection $races): array
    {
        return [
            'stat_statistics' => $this->calculateStatStatistics($sessions),
            'training_statistics' => $this->calculateTrainingStatistics($sessions),
            'race_statistics' => $this->calculateRaceStatistics($races),
            'correlation_analysis' => $this->performCorrelationAnalysis($sessions, $races),
            'has_training_data' => $sessions->isNotEmpty(),
        ];
    }

    /**
     * Calculate statistical measures for each stat
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array<string, array{mean: float, median: float, std_dev: float, min: int, max: int, total: int}>
     */
    protected function calculateStatStatistics(Collection $sessions): array
    {
        $statistics = [];

        foreach (self::STAT_TYPES as $stat) {
            /** @var list<int> $gains */
            $gains = $sessions->pluck("{$stat}_gain")->filter()->values()->toArray();

            if (empty($gains)) {
                $statistics[$stat] = [
                    'mean' => 0.0,
                    'median' => 0.0,
                    'std_dev' => 0.0,
                    'min' => 0,
                    'max' => 0,
                    'total' => 0,
                ];

                continue;
            }

            /** @var list<int> $gains */
            $gains = array_map('intval', $gains);
            sort($gains);
            $count = count($gains);
            $mean = array_sum($gains) / $count;

            // Calculate median - gains is non-empty at this point
            /** @var int $lowerIndex */
            $lowerIndex = (int) ($count / 2) - 1;
            /** @var int $upperIndex */
            $upperIndex = (int) ($count / 2);
            $median = $count % 2 === 0
                ? ($gains[$lowerIndex] + $gains[$upperIndex]) / 2
                : $gains[(int) floor($count / 2)];

            // Calculate standard deviation
            $variance = array_sum(array_map(fn (int $g): float => pow($g - $mean, 2), $gains)) / $count;
            $stdDev = sqrt($variance);

            // min/max are safe since gains is non-empty
            /** @var non-empty-list<int> $nonEmptyGains */
            $nonEmptyGains = $gains;
            $statistics[$stat] = [
                'mean' => round($mean, 2),
                'median' => round($median, 2),
                'std_dev' => round($stdDev, 2),
                'min' => min($nonEmptyGains),
                'max' => max($nonEmptyGains),
                'total' => array_sum($gains),
            ];
        }

        return $statistics;
    }

    /**
     * Calculate training statistics
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @return array{
     *     sessions_per_phase: array<string, int>,
     *     avg_gains_trend: array<int, float>,
     *     consistency_score: float,
     *     peak_performance_turn: int
     * }
     */
    protected function calculateTrainingStatistics(Collection $sessions): array
    {
        // Sessions per phase
        /** @var array<string, int> $sessionsPerPhase */
        $sessionsPerPhase = [];
        foreach (self::CAREER_PHASES as $phase => $range) {
            $sessionsPerPhase[$phase] = $sessions->filter(function ($s) use ($range) {
                /** @var \App\Models\TrainingSession $s */
                $turn = $s->turn_number ?? 0;

                return $turn >= $range['min'] && $turn <= $range['max'];
            })->count();
        }

        // Average gains trend (by 10-turn blocks)
        /** @var array<int, float> $avgGainsTrend */
        $avgGainsTrend = [];
        for ($block = 0; $block < 8; $block++) {
            $startTurn = $block * 10 + 1;
            $endTurn = ($block + 1) * 10;

            $blockSessions = $sessions->filter(function ($s) use ($startTurn, $endTurn) {
                /** @var \App\Models\TrainingSession $s */
                $turn = $s->turn_number ?? 0;

                return $turn >= $startTurn && $turn <= $endTurn;
            });

            $totalGains = 0;
            foreach ($blockSessions as $session) {
                /** @var \App\Models\TrainingSession $session */
                $totalGains += ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                    + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);
            }

            $avgGainsTrend[$block] = $blockSessions->count() > 0
                ? round($totalGains / $blockSessions->count(), 2)
                : 0.0;
        }

        // Consistency score (inverse of coefficient of variation)
        /** @var array<int> $allGains */
        $allGains = [];
        foreach ($sessions as $session) {
            /** @var \App\Models\TrainingSession $session */
            $allGains[] = ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);
        }

        $consistencyScore = 0.0;
        if (count($allGains) > 1) {
            $mean = array_sum($allGains) / count($allGains);
            if ($mean > 0) {
                $variance = array_sum(array_map(fn ($g) => pow($g - $mean, 2), $allGains)) / count($allGains);
                $cv = sqrt($variance) / $mean;
                $consistencyScore = max(0, min(100, round((1 - $cv) * 100, 1)));
            }
        }

        // Peak performance turn
        $peakTurn = 0;
        $peakGain = 0;
        foreach ($sessions as $session) {
            /** @var \App\Models\TrainingSession $session */
            $totalGain = ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);
            if ($totalGain > $peakGain) {
                $peakGain = $totalGain;
                $peakTurn = $session->turn_number ?? 0;
            }
        }

        return [
            'sessions_per_phase' => $sessionsPerPhase,
            'avg_gains_trend' => $avgGainsTrend,
            'consistency_score' => $consistencyScore,
            'peak_performance_turn' => $peakTurn,
        ];
    }

    /**
     * Calculate race statistics
     *
     * @param  Collection<int, \App\Models\Race>  $races
     * @return array{
     *     position_distribution: array<int, int>,
     *     win_streak: int,
     *     avg_position_trend: array<int, float>
     * }
     */
    protected function calculateRaceStatistics(Collection $races): array
    {
        // Position distribution
        /** @var array<int, int> $positionDistribution */
        $positionDistribution = [];
        foreach ($races as $race) {
            /** @var \App\Models\Race $race */
            $position = $race->finish_position ?? 0;
            $positionDistribution[$position] = ($positionDistribution[$position] ?? 0) + 1;
        }
        ksort($positionDistribution);

        // Calculate win streak
        $winStreak = 0;
        $currentStreak = 0;
        $sortedRaces = $races->sortBy('turn_number');
        foreach ($sortedRaces as $race) {
            /** @var \App\Models\Race $race */
            if ($race->won_race) {
                $currentStreak++;
                $winStreak = max($winStreak, $currentStreak);
            } else {
                $currentStreak = 0;
            }
        }

        // Average position trend (by phase)
        /** @var array<int, float> $avgPositionTrend */
        $avgPositionTrend = [];
        $phaseIndex = 0;
        foreach (self::CAREER_PHASES as $phase => $range) {
            $phaseRaces = $races->filter(function ($r) use ($range) {
                /** @var \App\Models\Race $r */
                $turn = $r->turn_number ?? 0;

                return $turn >= $range['min'] && $turn <= $range['max'];
            });

            $avgPositionTrend[$phaseIndex] = $phaseRaces->count() > 0
                ? round((float) $phaseRaces->avg('finish_position'), 2)
                : 0.0;
            $phaseIndex++;
        }

        return [
            'position_distribution' => $positionDistribution,
            'win_streak' => $winStreak,
            'avg_position_trend' => $avgPositionTrend,
        ];
    }

    /**
     * Perform correlation analysis between training and race performance
     *
     * @param  Collection<int, \App\Models\TrainingSession>  $sessions
     * @param  Collection<int, \App\Models\Race>  $races
     * @return array{
     *     training_race_correlation: float,
     *     stat_win_correlations: array<string, float>,
     *     efficiency_win_correlation: float
     * }
     */
    protected function performCorrelationAnalysis(Collection $sessions, Collection $races): array
    {
        if ($sessions->isEmpty() || $races->isEmpty()) {
            return [
                'training_race_correlation' => 0.0,
                'stat_win_correlations' => array_fill_keys(self::STAT_TYPES, 0.0),
                'efficiency_win_correlation' => 0.0,
            ];
        }

        // Calculate training efficiency before each race
        /** @var array<float> $trainingEfficiencies */
        $trainingEfficiencies = [];
        /** @var array<int> $raceResults */
        $raceResults = [];

        foreach ($races as $race) {
            /** @var \App\Models\Race $race */
            $raceTurn = $race->turn_number ?? 0;
            $priorSessions = $sessions->filter(fn ($s) => ($s->turn_number ?? 0) < $raceTurn);

            if ($priorSessions->isEmpty()) {
                continue;
            }

            $totalGains = 0;
            foreach ($priorSessions as $session) {
                /** @var \App\Models\TrainingSession $session */
                $totalGains += ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                    + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);
            }

            $efficiency = $priorSessions->count() > 0 ? $totalGains / $priorSessions->count() : 0;
            $trainingEfficiencies[] = $efficiency;
            $raceResults[] = $race->won_race ? 1 : 0;
        }

        // Calculate correlation
        $trainingRaceCorrelation = $this->calculateCorrelation($trainingEfficiencies, $raceResults);

        // Calculate stat-win correlations
        /** @var array<string, float> $statWinCorrelations */
        $statWinCorrelations = [];
        foreach (self::STAT_TYPES as $stat) {
            /** @var array<float> $statGains */
            $statGains = [];
            /** @var array<int> $wins */
            $wins = [];

            foreach ($races as $race) {
                /** @var Race $race */
                $raceTurn = $race->turn_number ?? 0;
                $priorSessions = $sessions->filter(fn ($s) => ($s->turn_number ?? 0) < $raceTurn);

                /** @var int|float $sumResult */
                $sumResult = $priorSessions->sum("{$stat}_gain");
                $statTotal = (float) $sumResult;
                $statGains[] = $statTotal;
                $wins[] = $race->won_race ? 1 : 0;
            }

            $statWinCorrelations[$stat] = $this->calculateCorrelation($statGains, $wins);
        }

        return [
            'training_race_correlation' => $trainingRaceCorrelation,
            'stat_win_correlations' => $statWinCorrelations,
            'efficiency_win_correlation' => $trainingRaceCorrelation,
        ];
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
        $sumSqX = 0;
        $sumSqY = 0;

        for ($i = 0; $i < $n; $i++) {
            $diffX = $x[$i] - $meanX;
            $diffY = $y[$i] - $meanY;
            $numerator += $diffX * $diffY;
            $sumSqX += $diffX * $diffX;
            $sumSqY += $diffY * $diffY;
        }

        $denominator = sqrt($sumSqX * $sumSqY);

        return $denominator > 0 ? round($numerator / $denominator, 3) : 0.0;
    }

    // =========================================================================
    // KEY INSIGHTS GENERATION
    // =========================================================================

    /**
     * Generate key insights from performance data using statistical analysis
     *
     * @param  array<string, mixed>  $performanceOverview
     * @param  array<string, mixed>  $trainingAnalysis
     * @param  array<string, mixed>  $raceAnalysis
     * @return array<string>
     */
    protected function generateKeyInsights(array $performanceOverview, array $trainingAnalysis, array $raceAnalysis): array
    {
        $totalSessions = isset($trainingAnalysis['total_sessions']) && is_int($trainingAnalysis['total_sessions'])
            ? $trainingAnalysis['total_sessions']
            : 0;
        $totalRaces = isset($raceAnalysis['total_races']) && is_int($raceAnalysis['total_races'])
            ? $raceAnalysis['total_races']
            : 0;

        if ($totalSessions === 0 && $totalRaces === 0) {
            return [
                'No training sessions or races are recorded yet. Continue your run to unlock detailed insights.',
                'Track a few turns of training to start seeing meaningful efficiency and stat trend analysis.',
            ];
        }

        $insights = [];

        // Efficiency insights
        $efficiency = isset($performanceOverview['efficiency_rating']) && is_numeric($performanceOverview['efficiency_rating'])
            ? (float) $performanceOverview['efficiency_rating']
            : 0.0;
        if ($efficiency >= 80) {
            $insights[] = "Excellent training efficiency ({$efficiency}%) - significantly above average performance.";
        } elseif ($efficiency >= 60) {
            $insights[] = "Good training efficiency ({$efficiency}%) - solid performance with room for optimization.";
        } elseif ($efficiency < 50) {
            $insights[] = "Training efficiency ({$efficiency}%) is below optimal - consider focusing on friendship training.";
        }

        // Stat distribution insights
        /** @var array<string, int> $statDistribution */
        $statDistribution = isset($performanceOverview['stat_distribution']) && is_array($performanceOverview['stat_distribution'])
            ? $performanceOverview['stat_distribution']
            : [];
        if (! empty($statDistribution)) {
            arsort($statDistribution);
            $topStat = array_key_first($statDistribution);
            if ($topStat !== null && isset($statDistribution[$topStat])) {
                $topValue = $statDistribution[$topStat];
                $totalStats = array_sum($statDistribution);

                if ($totalStats > 0) {
                    $topPercentage = round(($topValue / $totalStats) * 100, 1);
                    if ($topPercentage > 35) {
                        $insights[] = ucfirst((string) $topStat)." is your dominant stat ({$topPercentage}% of total gains) - consider if this aligns with your race goals.";
                    }
                }
            }
        }

        // Phase performance insights
        /** @var array<string, array<string, mixed>> $phasePerformance */
        $phasePerformance = isset($performanceOverview['phase_performance']) && is_array($performanceOverview['phase_performance'])
            ? $performanceOverview['phase_performance']
            : [];
        if (! empty($phasePerformance)) {
            $phaseEfficiencies = array_column($phasePerformance, 'efficiency');
            if (count($phaseEfficiencies) >= 2) {
                $firstKey = array_key_first($phaseEfficiencies);
                $lastKey = array_key_last($phaseEfficiencies);
                $firstPhase = $firstKey !== null && isset($phaseEfficiencies[$firstKey]) && is_numeric($phaseEfficiencies[$firstKey])
                    ? (float) $phaseEfficiencies[$firstKey]
                    : 0.0;
                $lastPhase = $lastKey !== null && isset($phaseEfficiencies[$lastKey]) && is_numeric($phaseEfficiencies[$lastKey])
                    ? (float) $phaseEfficiencies[$lastKey]
                    : 0.0;

                if ($lastPhase > $firstPhase + 10) {
                    $insights[] = 'Performance improved significantly in later phases - strong finish to the career.';
                } elseif ($firstPhase > $lastPhase + 10) {
                    $insights[] = 'Performance declined in later phases - consider pacing strategies for future careers.';
                }
            }
        }

        // Training type insights
        $bestType = isset($trainingAnalysis['best_training_type']) && is_string($trainingAnalysis['best_training_type'])
            ? $trainingAnalysis['best_training_type']
            : '';
        $worstType = isset($trainingAnalysis['worst_training_type']) && is_string($trainingAnalysis['worst_training_type'])
            ? $trainingAnalysis['worst_training_type']
            : '';
        if ($bestType !== '' && $worstType !== '' && $bestType !== $worstType) {
            $insights[] = ucfirst($bestType)." training was most effective; {$worstType} training showed lowest returns.";
        }

        // Friendship training insights
        /** @var array<string, mixed> $friendshipStats */
        $friendshipStats = isset($trainingAnalysis['friendship_training_stats']) && is_array($trainingAnalysis['friendship_training_stats'])
            ? $trainingAnalysis['friendship_training_stats']
            : [];
        $friendshipPercentage = isset($friendshipStats['percentage']) && is_numeric($friendshipStats['percentage'])
            ? (float) $friendshipStats['percentage']
            : 0.0;
        if ($friendshipPercentage >= 20) {
            $insights[] = "Strong friendship training utilization ({$friendshipPercentage}%) - maximizing support card bonuses.";
        } elseif ($friendshipPercentage < 10) {
            $insights[] = "Low friendship training rate ({$friendshipPercentage}%) - prioritize building support card bonds earlier.";
        }

        // Failure insights
        /** @var array<string, mixed> $failureAnalysis */
        $failureAnalysis = isset($trainingAnalysis['failure_analysis']) && is_array($trainingAnalysis['failure_analysis'])
            ? $trainingAnalysis['failure_analysis']
            : [];
        $failureRate = isset($failureAnalysis['failure_rate']) && is_numeric($failureAnalysis['failure_rate'])
            ? (float) $failureAnalysis['failure_rate']
            : 0.0;
        if ($failureRate > 5) {
            $insights[] = "Training failure rate ({$failureRate}%) is elevated - monitor energy levels more closely.";
        } elseif ($failureRate === 0.0) {
            $insights[] = 'Perfect training record with no failures - excellent energy management.';
        }

        // Race insights
        $winRate = isset($raceAnalysis['win_rate']) && is_numeric($raceAnalysis['win_rate'])
            ? (float) $raceAnalysis['win_rate']
            : 0.0;
        if ($totalRaces > 0) {
            if ($winRate >= 70) {
                $insights[] = "Outstanding race performance ({$winRate}% win rate) - dominant competitive showing.";
            } elseif ($winRate >= 50) {
                $insights[] = "Solid race performance ({$winRate}% win rate) - competitive in most matchups.";
            } elseif ($winRate < 30 && $totalRaces >= 5) {
                $insights[] = "Race win rate ({$winRate}%) suggests stat or strategy misalignment with race requirements.";
            }
        }

        // Grade-specific insights
        /** @var array<string, array<string, mixed>> $gradePerformance */
        $gradePerformance = isset($raceAnalysis['performance_by_grade']) && is_array($raceAnalysis['performance_by_grade'])
            ? $raceAnalysis['performance_by_grade']
            : [];
        if (isset($gradePerformance['G1']) && is_array($gradePerformance['G1'])) {
            $g1Data = $gradePerformance['G1'];
            $g1Count = isset($g1Data['count']) && is_int($g1Data['count']) ? $g1Data['count'] : 0;
            if ($g1Count > 0) {
                $g1WinRate = isset($g1Data['win_rate']) && is_numeric($g1Data['win_rate']) ? (float) $g1Data['win_rate'] : 0.0;
                if ($g1WinRate >= 50) {
                    $insights[] = "Strong G1 performance ({$g1WinRate}% win rate) - competitive at the highest level.";
                }
            }
        }

        return array_slice($insights, 0, 8);
    }

    // =========================================================================
    // IMPROVEMENT RECOMMENDATIONS
    // =========================================================================

    /**
     * Generate improvement recommendations based on historical performance
     *
     * @param  array<string, mixed>  $performanceOverview
     * @param  array<string, mixed>  $trainingAnalysis
     * @return array<string>
     */
    protected function generateImprovementRecommendations(Career $career, array $performanceOverview, array $trainingAnalysis): array
    {
        $totalSessions = isset($trainingAnalysis['total_sessions']) && is_int($trainingAnalysis['total_sessions'])
            ? $trainingAnalysis['total_sessions']
            : 0;
        if ($totalSessions === 0) {
            return [
                'Start by recording your first few training turns so recommendations can adapt to your run pattern.',
                'Prioritize early friendship bond setup to improve future training consistency and gains.',
            ];
        }

        $recommendations = [];

        // Efficiency-based recommendations
        $efficiency = isset($performanceOverview['efficiency_rating']) && is_numeric($performanceOverview['efficiency_rating'])
            ? (float) $performanceOverview['efficiency_rating']
            : 0.0;
        if ($efficiency < 60) {
            $recommendations[] = 'Focus on maximizing friendship training opportunities by prioritizing support card bond building in early turns.';
        }
        if ($efficiency < 50) {
            $recommendations[] = 'Consider adjusting support card deck composition to improve training synergies.';
        }
        if ($efficiency >= 60 && $efficiency < 90) {
            $recommendations[] = 'Good efficiency, but there is room for improvement - focus on high-value training opportunities.';
        }

        // Stat balance recommendations
        /** @var array<string, int> $statDistribution */
        $statDistribution = isset($performanceOverview['stat_distribution']) && is_array($performanceOverview['stat_distribution'])
            ? $performanceOverview['stat_distribution']
            : [];
        if (! empty($statDistribution)) {
            $total = array_sum($statDistribution);
            if ($total > 0) {
                foreach ($statDistribution as $stat => $value) {
                    $percentage = ($value / $total) * 100;
                    if ($percentage < 10) {
                        $recommendations[] = "Consider increasing {$stat} training - currently underrepresented in your build.";
                    }
                }
            }
        }

        // Phase-specific recommendations
        /** @var array<string, array<string, mixed>> $phasePerformance */
        $phasePerformance = isset($performanceOverview['phase_performance']) && is_array($performanceOverview['phase_performance'])
            ? $performanceOverview['phase_performance']
            : [];
        foreach ($phasePerformance as $phase => $data) {
            if (is_array($data)) {
                $phaseEfficiency = isset($data['efficiency']) && is_numeric($data['efficiency']) ? (float) $data['efficiency'] : 0.0;
                $phaseTurns = isset($data['turns']) && is_int($data['turns']) ? $data['turns'] : 0;
                if ($phaseEfficiency < 50 && $phaseTurns > 5) {
                    $recommendations[] = "Improve {$phase} phase performance by focusing on high-value training opportunities.";
                }
            }
        }

        // Training type recommendations
        /** @var array<string, array<string, mixed>> $typeBreakdown */
        $typeBreakdown = isset($trainingAnalysis['training_type_breakdown']) && is_array($trainingAnalysis['training_type_breakdown'])
            ? $trainingAnalysis['training_type_breakdown']
            : [];
        $restData = isset($typeBreakdown['rest']) && is_array($typeBreakdown['rest']) ? $typeBreakdown['rest'] : [];
        $restPercentage = isset($restData['percentage']) && is_numeric($restData['percentage']) ? (float) $restData['percentage'] : 0.0;
        if ($restPercentage > 15) {
            $recommendations[] = "High rest frequency ({$restPercentage}%) - improve energy management to reduce rest turns.";
        }

        // Failure-based recommendations
        /** @var array<string, mixed> $failureAnalysis */
        $failureAnalysis = isset($trainingAnalysis['failure_analysis']) && is_array($trainingAnalysis['failure_analysis'])
            ? $trainingAnalysis['failure_analysis']
            : [];
        $failureRate = isset($failureAnalysis['failure_rate']) && is_numeric($failureAnalysis['failure_rate'])
            ? (float) $failureAnalysis['failure_rate']
            : 0.0;
        if ($failureRate > 3) {
            $recommendations[] = 'Reduce training failures by resting when energy drops below 30% or motivation is low.';
        }

        /** @var array<string, int> $failuresByType */
        $failuresByType = isset($failureAnalysis['failures_by_type']) && is_array($failureAnalysis['failures_by_type'])
            ? $failureAnalysis['failures_by_type']
            : [];
        if (! empty($failuresByType)) {
            arsort($failuresByType);
            $worstType = array_key_first($failuresByType);
            if ($worstType !== null && isset($failuresByType[$worstType]) && $failuresByType[$worstType] >= 2) {
                $recommendations[] = "Be cautious with {$worstType} training - highest failure rate among training types.";
            }
        }

        // Friendship training recommendations
        /** @var array<string, mixed> $friendshipStats */
        $friendshipStats = isset($trainingAnalysis['friendship_training_stats']) && is_array($trainingAnalysis['friendship_training_stats'])
            ? $trainingAnalysis['friendship_training_stats']
            : [];
        $friendshipPercentage = isset($friendshipStats['percentage']) && is_numeric($friendshipStats['percentage'])
            ? (float) $friendshipStats['percentage']
            : 0.0;
        if ($friendshipPercentage < 15) {
            $recommendations[] = 'Increase friendship training frequency by building support card bonds earlier in the career.';
        }

        // Scenario-specific recommendations
        if ($career->scenario_type === 'unity_cup') {
            $recommendations[] = 'For Unity Cup, prioritize Spirit Burst opportunities and team synergy training.';
        }

        // Ensure at least one recommendation if efficiency is below 95%
        if (empty($recommendations) && $efficiency < 95) {
            $recommendations[] = 'Continue optimizing training choices to maximize stat gains per turn.';
        }

        return array_slice($recommendations, 0, 6);
    }

    // =========================================================================
    // EXPORT FUNCTIONALITY
    // =========================================================================

    /**
     * Export report to JSON format
     */
    public function exportToJson(Career $career): string
    {
        $report = $this->generateCareerSummaryReport($career);
        $encoded = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $encoded !== false ? $encoded : '{}';
    }

    /**
     * Export report to CSV format
     *
     * @return array{headers: array<string>, rows: array<array<string|int|float>>}
     */
    public function exportToCsv(Career $career): array
    {
        $report = $this->generateCareerSummaryReport($career);

        $headers = [
            'Metric',
            'Value',
            'Category',
        ];

        /** @var array<array<string|int|float>> $rows */
        $rows = [];

        // Metadata - safely extract values
        /** @var array{report_id?: string, generated_at?: string, character_name?: string, scenario_type?: string} $metadata */
        $metadata = $report['report_metadata'];
        $rows[] = ['Report ID', $metadata['report_id'] ?? '', 'Metadata'];
        $rows[] = ['Generated At', $metadata['generated_at'] ?? '', 'Metadata'];
        $rows[] = ['Character Name', $metadata['character_name'] ?? '', 'Metadata'];
        $rows[] = ['Scenario Type', $metadata['scenario_type'] ?? '', 'Metadata'];

        // Executive Summary
        /** @var array{career_status?: string, overall_grade?: string, total_turns?: int, completion_percentage?: float} $execSummary */
        $execSummary = $report['executive_summary'];
        $rows[] = ['Career Status', $execSummary['career_status'] ?? '', 'Summary'];
        $rows[] = ['Overall Grade', $execSummary['overall_grade'] ?? '', 'Summary'];
        $rows[] = ['Total Turns', $execSummary['total_turns'] ?? 0, 'Summary'];
        $rows[] = ['Completion %', $execSummary['completion_percentage'] ?? 0.0, 'Summary'];

        // Performance Overview
        /** @var array{efficiency_rating?: float, training_success_rate?: float, race_win_rate?: float, sp_earned?: int, stat_distribution?: array<string, int>} $perfOverview */
        $perfOverview = $report['performance_overview'];
        $rows[] = ['Efficiency Rating', $perfOverview['efficiency_rating'] ?? 0.0, 'Performance'];
        $rows[] = ['Training Success Rate', $perfOverview['training_success_rate'] ?? 0.0, 'Performance'];
        $rows[] = ['Race Win Rate', $perfOverview['race_win_rate'] ?? 0.0, 'Performance'];
        $rows[] = ['SP Earned', $perfOverview['sp_earned'] ?? 0, 'Performance'];

        // Stat Distribution
        /** @var array<string, int> $statDistribution */
        $statDistribution = $perfOverview['stat_distribution'] ?? [];
        foreach ($statDistribution as $stat => $value) {
            $rows[] = [ucfirst($stat).' Gains', $value, 'Stats'];
        }

        // Training Analysis
        /** @var array{total_sessions?: int, best_training_type?: string, friendship_training_stats?: array{count?: int}, failure_analysis?: array{total_failures?: int}} $trainingAnalysis */
        $trainingAnalysis = $report['training_analysis'];
        $rows[] = ['Total Training Sessions', $trainingAnalysis['total_sessions'] ?? 0, 'Training'];
        $rows[] = ['Best Training Type', $trainingAnalysis['best_training_type'] ?? '', 'Training'];
        /** @var array{count?: int} $friendshipStats */
        $friendshipStats = $trainingAnalysis['friendship_training_stats'] ?? [];
        $rows[] = ['Friendship Training Count', $friendshipStats['count'] ?? 0, 'Training'];
        /** @var array{total_failures?: int} $failureAnalysis */
        $failureAnalysis = $trainingAnalysis['failure_analysis'] ?? [];
        $rows[] = ['Training Failures', $failureAnalysis['total_failures'] ?? 0, 'Training'];

        // Race Analysis
        /** @var array{total_races?: int, wins?: int, avg_position?: float} $raceAnalysis */
        $raceAnalysis = $report['race_analysis'];
        $rows[] = ['Total Races', $raceAnalysis['total_races'] ?? 0, 'Races'];
        $rows[] = ['Race Wins', $raceAnalysis['wins'] ?? 0, 'Races'];
        $rows[] = ['Average Position', $raceAnalysis['avg_position'] ?? 0.0, 'Races'];

        // Insights
        /** @var string $insight */
        foreach ($report['key_insights'] as $index => $insight) {
            $rows[] = ['Insight '.($index + 1), $insight, 'Insights'];
        }

        // Recommendations
        /** @var string $recommendation */
        foreach ($report['recommendations'] as $index => $recommendation) {
            $rows[] = ['Recommendation '.($index + 1), $recommendation, 'Recommendations'];
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * Export report to PDF-ready format (structured data for PDF generation)
     *
     * @return array{
     *     title: string,
     *     subtitle: string,
     *     generated_at: string,
     *     sections: list<array{title: string, content: mixed}>
     * }
     */
    public function exportToPdfFormat(Career $career): array
    {
        $report = $this->generateCareerSummaryReport($career);

        /** @var array{character_name?: string, scenario_type?: string, generated_at?: string} $metadata */
        $metadata = $report['report_metadata'];

        /** @var array{career_status?: string, overall_grade?: string, total_turns?: int, completion_percentage?: float, key_achievements?: array<string>} $execSummary */
        $execSummary = $report['executive_summary'];

        /** @var array{efficiency_rating?: float, training_success_rate?: float, race_win_rate?: float, sp_earned?: int, stat_distribution?: array<string, int>} $perfOverview */
        $perfOverview = $report['performance_overview'];

        /** @var array{total_sessions?: int, best_training_type?: string, friendship_training_stats?: array{count?: int}, failure_analysis?: array{failure_rate?: float}} $trainingAnalysis */
        $trainingAnalysis = $report['training_analysis'];

        /** @var array{total_races?: int, wins?: int, win_rate?: float, avg_position?: float} $raceAnalysis */
        $raceAnalysis = $report['race_analysis'];

        $characterName = $metadata['character_name'] ?? 'Unknown';
        $scenarioType = $metadata['scenario_type'] ?? 'unknown';

        $totalTurns = $execSummary['total_turns'] ?? 0;
        $completionPct = $execSummary['completion_percentage'] ?? 0.0;
        $efficiencyRating = $perfOverview['efficiency_rating'] ?? 0.0;
        $trainingSuccessRate = $perfOverview['training_success_rate'] ?? 0.0;
        $raceWinRate = $perfOverview['race_win_rate'] ?? 0.0;
        $spEarned = $perfOverview['sp_earned'] ?? 0;

        /** @var array<string, int> $statDistribution */
        $statDistribution = $perfOverview['stat_distribution'] ?? [];

        $totalSessions = $trainingAnalysis['total_sessions'] ?? 0;
        $bestTrainingType = $trainingAnalysis['best_training_type'] ?? '';
        /** @var array{count?: int} $friendshipStats */
        $friendshipStats = $trainingAnalysis['friendship_training_stats'] ?? [];
        $friendshipCount = $friendshipStats['count'] ?? 0;
        /** @var array{failure_rate?: float} $failureAnalysis */
        $failureAnalysis = $trainingAnalysis['failure_analysis'] ?? [];
        $failureRate = $failureAnalysis['failure_rate'] ?? 0.0;

        return [
            'title' => 'Career Summary Report',
            'subtitle' => sprintf(
                '%s - %s Career',
                $characterName,
                ucfirst(str_replace('_', ' ', $scenarioType))
            ),
            'generated_at' => $metadata['generated_at'] ?? '',
            'sections' => [
                [
                    'title' => 'Executive Summary',
                    'content' => [
                        'Status' => ucfirst($execSummary['career_status'] ?? ''),
                        'Overall Grade' => $execSummary['overall_grade'] ?? '',
                        'Progress' => "{$totalTurns} turns ({$completionPct}%)",
                        'Key Achievements' => $execSummary['key_achievements'] ?? [],
                    ],
                ],
                [
                    'title' => 'Performance Overview',
                    'content' => [
                        'Efficiency Rating' => "{$efficiencyRating}%",
                        'Training Success Rate' => "{$trainingSuccessRate}%",
                        'Race Win Rate' => "{$raceWinRate}%",
                        'Total SP Earned' => number_format($spEarned),
                    ],
                ],
                [
                    'title' => 'Stat Distribution',
                    'content' => array_map(
                        fn (string $stat, int $value): array => [ucfirst($stat) => number_format($value)],
                        array_keys($statDistribution),
                        array_values($statDistribution)
                    ),
                ],
                [
                    'title' => 'Training Analysis',
                    'content' => [
                        'Total Sessions' => $totalSessions,
                        'Best Training Type' => ucfirst($bestTrainingType),
                        'Friendship Training' => "{$friendshipCount} sessions",
                        'Failure Rate' => "{$failureRate}%",
                    ],
                ],
                [
                    'title' => 'Race Performance',
                    'content' => [
                        'Total Races' => $raceAnalysis['total_races'] ?? 0,
                        'Wins' => $raceAnalysis['wins'] ?? 0,
                        'Win Rate' => ($raceAnalysis['win_rate'] ?? 0.0).'%',
                        'Average Position' => $raceAnalysis['avg_position'] ?? 0.0,
                    ],
                ],
                [
                    'title' => 'Key Insights',
                    'content' => $report['key_insights'],
                ],
                [
                    'title' => 'Recommendations',
                    'content' => $report['recommendations'],
                ],
            ],
        ];
    }

    // =========================================================================
    // CHARACTER-LEVEL REPORTING
    // =========================================================================

    /**
     * Generate comprehensive character report across all careers
     *
     * @return array{
     *     character_info: array<string, mixed>,
     *     career_history: array<array<string, mixed>>,
     *     aggregate_statistics: array<string, mixed>,
     *     performance_trends: array<string, mixed>,
     *     improvement_areas: array<string>,
     *     strengths: array<string>
     * }
     */
    public function generateCharacterReport(Character $character): array
    {
        $cacheKey = "report:character:{$character->id}";

        /** @var array{character_info: array<string, mixed>, career_history: array<array<string, mixed>>, aggregate_statistics: array<string, mixed>, performance_trends: array<string, mixed>, improvement_areas: array<string>, strengths: array<string>} $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($character): array {
            /** @var Collection<int, Career> $careers */
            $careers = Career::where('character_id', $character->id)
                ->with(['trainingSessions', 'races'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Character info
            $characterInfo = [
                'id' => $character->id,
                'name' => $character->name,
                'scenario_type' => $character->scenario_type,
                'current_stats' => $character->current_stats ?? [],
                'total_careers' => $careers->count(),
                'completed_careers' => $careers->whereNotNull('completed_at')->count(),
            ];

            // Career history summary
            $careerHistory = $this->buildCareerHistory($careers);

            // Aggregate statistics across all careers
            $aggregateStatistics = $this->buildAggregateStatistics($careers);

            // Performance trends over time
            $performanceTrends = $this->buildPerformanceTrends($careers);

            // Identify improvement areas and strengths
            $improvementAreas = $this->identifyImprovementAreas($aggregateStatistics, $performanceTrends);
            $strengths = $this->identifyStrengths($aggregateStatistics, $performanceTrends);

            return [
                'character_info' => $characterInfo,
                'career_history' => $careerHistory,
                'aggregate_statistics' => $aggregateStatistics,
                'performance_trends' => $performanceTrends,
                'improvement_areas' => $improvementAreas,
                'strengths' => $strengths,
            ];
        });

        return $result;
    }

    /**
     * Build career history summary
     *
     * @param  Collection<int, Career>  $careers
     * @return array<array{career_id: int, name: string, status: string, grade: string, efficiency: float, win_rate: float, started_at: string|null, completed_at: string|null}>
     */
    protected function buildCareerHistory(Collection $careers): array
    {
        $history = [];

        foreach ($careers as $career) {
            /** @var Career $career */
            /** @var Collection<int, TrainingSession> $sessions */
            $sessions = $career->trainingSessions;
            /** @var Collection<int, Race> $races */
            $races = $career->races;

            // Calculate efficiency
            $totalGains = 0;
            /** @var TrainingSession $session */
            foreach ($sessions as $session) {
                $totalGains += ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                    + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);
            }
            $idealTotal = $sessions->count() * 30;
            $efficiency = $idealTotal > 0 ? min(100.0, round(($totalGains / $idealTotal) * 100, 1)) : 0.0;

            // Calculate win rate
            $winRate = $races->count() > 0
                ? round(($races->where('won_race', true)->count() / $races->count()) * 100, 1)
                : 0.0;

            $startedAt = $career->started_at;
            $completedAt = $career->completed_at;

            $history[] = [
                'career_id' => $career->id,
                'name' => $career->career_name ?? "Career #{$career->id}",
                'status' => $completedAt !== null ? 'completed' : 'in_progress',
                'grade' => $this->calculateOverallGrade($sessions, $races),
                'efficiency' => $efficiency,
                'win_rate' => $winRate,
                'started_at' => $startedAt instanceof \Illuminate\Support\Carbon ? $startedAt->format('Y-m-d') : null,
                'completed_at' => $completedAt instanceof \Illuminate\Support\Carbon ? $completedAt->format('Y-m-d') : null,
            ];
        }

        return $history;
    }

    /**
     * Build aggregate statistics across all careers
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     total_training_sessions: int,
     *     total_races: int,
     *     total_wins: int,
     *     overall_win_rate: float,
     *     avg_efficiency: float,
     *     total_stat_gains: array<string, int>,
     *     avg_stat_gains_per_career: array<string, float>
     * }
     */
    protected function buildAggregateStatistics(Collection $careers): array
    {
        $totalSessions = 0;
        $totalRaces = 0;
        $totalWins = 0;
        /** @var array<float> $efficiencies */
        $efficiencies = [];
        /** @var array<string, int> $statGains */
        $statGains = array_fill_keys(self::STAT_TYPES, 0);

        foreach ($careers as $career) {
            /** @var Career $career */
            /** @var Collection<int, TrainingSession> $sessions */
            $sessions = $career->trainingSessions;
            /** @var Collection<int, Race> $races */
            $races = $career->races;

            $totalSessions += $sessions->count();
            $totalRaces += $races->count();
            $totalWins += $races->where('won_race', true)->count();

            // Calculate efficiency
            $careerGains = 0;
            /** @var TrainingSession $session */
            foreach ($sessions as $session) {
                foreach (self::STAT_TYPES as $stat) {
                    $gain = (int) ($session->{"{$stat}_gain"} ?? 0);
                    $statGains[$stat] += $gain;
                    $careerGains += $gain;
                }
            }

            $idealTotal = $sessions->count() * 30;
            if ($idealTotal > 0) {
                $efficiencies[] = min(100.0, ($careerGains / $idealTotal) * 100);
            }
        }

        $careerCount = $careers->count();

        return [
            'total_training_sessions' => $totalSessions,
            'total_races' => $totalRaces,
            'total_wins' => $totalWins,
            'overall_win_rate' => $totalRaces > 0 ? round(($totalWins / $totalRaces) * 100, 1) : 0.0,
            'avg_efficiency' => count($efficiencies) > 0 ? round(array_sum($efficiencies) / count($efficiencies), 1) : 0.0,
            'total_stat_gains' => $statGains,
            'avg_stat_gains_per_career' => array_map(
                fn (int $total): float => $careerCount > 0 ? round($total / $careerCount, 1) : 0.0,
                $statGains
            ),
        ];
    }

    /**
     * Build performance trends over careers
     *
     * @param  Collection<int, Career>  $careers
     * @return array{
     *     efficiency_trend: array<float>,
     *     win_rate_trend: array<float>,
     *     trend_direction: string,
     *     improvement_rate: float
     * }
     */
    protected function buildPerformanceTrends(Collection $careers): array
    {
        /** @var array<float> $efficiencyTrend */
        $efficiencyTrend = [];
        /** @var array<float> $winRateTrend */
        $winRateTrend = [];

        /** @var Collection<int, Career> $sortedCareers */
        $sortedCareers = $careers->sortBy('created_at');

        foreach ($sortedCareers as $career) {
            /** @var Career $career */
            /** @var Collection<int, TrainingSession> $sessions */
            $sessions = $career->trainingSessions;
            /** @var Collection<int, Race> $races */
            $races = $career->races;

            // Calculate efficiency
            $totalGains = 0;
            /** @var TrainingSession $session */
            foreach ($sessions as $session) {
                $totalGains += ($session->speed_gain ?? 0) + ($session->stamina_gain ?? 0)
                    + ($session->power_gain ?? 0) + ($session->guts_gain ?? 0) + ($session->wit_gain ?? 0);
            }
            $idealTotal = $sessions->count() * 30;
            $efficiencyTrend[] = $idealTotal > 0 ? min(100.0, round(($totalGains / $idealTotal) * 100, 1)) : 0.0;

            // Calculate win rate
            $winRateTrend[] = $races->count() > 0
                ? round(($races->where('won_race', true)->count() / $races->count()) * 100, 1)
                : 0.0;
        }

        // Determine trend direction
        $trendDirection = 'stable';
        $improvementRate = 0.0;

        if (count($efficiencyTrend) >= 2) {
            $midpoint = (int) floor(count($efficiencyTrend) / 2);
            /** @var array<float> $firstHalf */
            $firstHalf = array_slice($efficiencyTrend, 0, $midpoint);
            /** @var array<float> $secondHalf */
            $secondHalf = array_slice($efficiencyTrend, $midpoint);

            $firstAvg = count($firstHalf) > 0 ? array_sum($firstHalf) / count($firstHalf) : 0.0;
            $secondAvg = count($secondHalf) > 0 ? array_sum($secondHalf) / count($secondHalf) : 0.0;

            $improvementRate = $firstAvg > 0 ? round((($secondAvg - $firstAvg) / $firstAvg) * 100, 1) : 0.0;

            $trendDirection = match (true) {
                $improvementRate > 10 => 'improving',
                $improvementRate < -10 => 'declining',
                default => 'stable',
            };
        }

        return [
            'efficiency_trend' => $efficiencyTrend,
            'win_rate_trend' => $winRateTrend,
            'trend_direction' => $trendDirection,
            'improvement_rate' => $improvementRate,
        ];
    }

    /**
     * Identify areas for improvement
     *
     * @param  array{avg_efficiency?: float, overall_win_rate?: float, avg_stat_gains_per_career?: array<string, float>}  $aggregateStatistics
     * @param  array{trend_direction?: string}  $performanceTrends
     * @return array<string>
     */
    protected function identifyImprovementAreas(array $aggregateStatistics, array $performanceTrends): array
    {
        $areas = [];

        // Check overall efficiency
        $avgEfficiency = $aggregateStatistics['avg_efficiency'] ?? 0.0;
        if ($avgEfficiency < 60) {
            $areas[] = 'Training efficiency is below optimal - focus on friendship training and support card synergies.';
        }

        // Check win rate
        $winRate = $aggregateStatistics['overall_win_rate'] ?? 0.0;
        if ($winRate < 40) {
            $areas[] = 'Race win rate needs improvement - ensure stats meet race requirements before competing.';
        }

        // Check stat balance
        $statGains = $aggregateStatistics['avg_stat_gains_per_career'] ?? [];
        if (! empty($statGains)) {
            $avgGain = array_sum($statGains) / count($statGains);
            foreach ($statGains as $stat => $gain) {
                if ($gain < $avgGain * 0.5) {
                    $areas[] = ucfirst((string) $stat).' training is significantly underutilized compared to other stats.';
                }
            }
        }

        // Check trend
        if (($performanceTrends['trend_direction'] ?? '') === 'declining') {
            $areas[] = 'Performance is declining over recent careers - review and adjust strategies.';
        }

        return array_slice($areas, 0, 5);
    }

    /**
     * Identify strengths
     *
     * @param  array{avg_efficiency?: float, overall_win_rate?: float, avg_stat_gains_per_career?: array<string, float>, total_training_sessions?: int}  $aggregateStatistics
     * @param  array{trend_direction?: string, improvement_rate?: float}  $performanceTrends
     * @return array<string>
     */
    protected function identifyStrengths(array $aggregateStatistics, array $performanceTrends): array
    {
        $strengths = [];

        // Check overall efficiency
        $avgEfficiency = $aggregateStatistics['avg_efficiency'] ?? 0.0;
        if ($avgEfficiency >= 75) {
            $strengths[] = 'Excellent training efficiency - consistently maximizing stat gains.';
        }

        // Check win rate
        $winRate = $aggregateStatistics['overall_win_rate'] ?? 0.0;
        if ($winRate >= 60) {
            $strengths[] = 'Strong race performance - competitive in most matchups.';
        }

        // Check stat specialization
        $statGains = $aggregateStatistics['avg_stat_gains_per_career'] ?? [];
        if (! empty($statGains)) {
            arsort($statGains);
            $topStat = array_key_first($statGains);
            $topValue = is_numeric($statGains[$topStat] ?? null) ? (float) $statGains[$topStat] : 0.0;
            $total = array_sum($statGains);

            if ($total > 0 && ($topValue / $total) > 0.25 && is_string($topStat)) {
                $strengths[] = 'Strong '.ucfirst($topStat).' specialization - clear build focus.';
            }
        }

        // Check improvement trend
        if (($performanceTrends['trend_direction'] ?? '') === 'improving') {
            $rate = $performanceTrends['improvement_rate'] ?? 0.0;
            $strengths[] = "Consistent improvement trend ({$rate}% improvement rate) - learning from past careers.";
        }

        // Check total experience
        $totalSessions = $aggregateStatistics['total_training_sessions'] ?? 0;
        if ($totalSessions >= 200) {
            $strengths[] = 'Extensive experience with '.number_format($totalSessions).' training sessions completed.';
        }

        return array_slice($strengths, 0, 5);
    }

    // =========================================================================
    // CACHE MANAGEMENT
    // =========================================================================

    /**
     * Clear report cache for a career
     */
    public function clearCareerReportCache(Career $career): void
    {
        Cache::forget("report:career_summary:{$career->id}");
    }

    /**
     * Clear report cache for a character
     */
    public function clearCharacterReportCache(Character $character): void
    {
        Cache::forget("report:character:{$character->id}");

        // Also clear all career reports for this character
        /** @var Collection<int, int> $careerIds */
        $careerIds = Career::where('character_id', $character->id)->pluck('id');
        foreach ($careerIds as $careerId) {
            /** @var int $careerId */
            Cache::forget("report:career_summary:{$careerId}");
        }
    }
}
