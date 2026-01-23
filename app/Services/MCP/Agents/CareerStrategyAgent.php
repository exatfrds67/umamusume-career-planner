<?php

namespace App\Services\MCP\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;

/**
 * Career Strategy Agent
 *
 * Provides goal-based training optimization with stat priority weighting.
 * Analyzes character goals, current progress, and scenario requirements
 * to recommend optimal training sequences and strategic decisions.
 */
class CareerStrategyAgent
{
    protected MCPClientService $mcpClient;

    /**
     * Stat priority weights based on game mechanics
     *
     * @var array<string, int>
     */
    protected array $statPriorityWeights = [
        'speed' => 5,    // ★★★★★ - Top speed, highest priority
        'stamina' => 4,  // ★★★★ - Duration at top speed
        'power' => 3,    // ★★★ - Acceleration rate
        'wit' => 2,      // ★★ - Skill activation, positioning
        'guts' => 1,     // ★ - Final phase performance
    ];

    /**
     * Distance-specific stamina requirements
     *
     * @var array<string, array{career: int, pvp_min: int, pvp_max: int}>
     */
    protected array $staminaRequirements = [
        'sprint' => ['career' => 350, 'pvp_min' => 500, 'pvp_max' => 600],
        'mile' => ['career' => 400, 'pvp_min' => 600, 'pvp_max' => 700],
        'medium' => ['career' => 500, 'pvp_min' => 800, 'pvp_max' => 900],
        'long' => ['career' => 600, 'pvp_min' => 900, 'pvp_max' => 1100],
    ];

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
    }

    /**
     * Analyze character goals and provide strategic recommendations
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     strategy: string,
     *     priority_stats: array<string, array{current: int, target: int, gap: int, priority: int, weight: float}>,
     *     training_focus: array<string, mixed>,
     *     milestone_tracking: array<string, mixed>,
     *     recommendations: array<string, string>,
     *     confidence: float
     * }
     */
    public function analyzeCareerStrategy(): array
        // Determine primary strategy based on scenario and goals
        $strategy = $this->determineStrategy($character);

        // Calculate priority stats with gaps and weights
        $priorityStats = $this->calculatePriorityStats($character);

        // Determine training focus areas
        $trainingFocus = $this->determineTrainingFocus($character, $priorityStats);

        // Track milestones and progress
        $milestoneTracking = $this->trackMilestones($character, $priorityStats);

        // Generate strategic recommendations
        $recommendations = $this->generateRecommendations(
            $character,
            $strategy,
            $priorityStats,
            $trainingFocus
        );

        // Calculate confidence score
        $confidence = $this->calculateConfidence($character, $priorityStats);

        return [
            'strategy' => $strategy,
            'priority_stats' => $priorityStats,
            'training_focus' => $trainingFocus,
            'milestone_tracking' => $milestoneTracking,
            'recommendations' => $recommendations,
            'confidence' => $confidence,
        ];
    }

    /**
     * Determine optimal strategy based on character and scenario
     */
    protected function determineStrategy(Character $character): string
    {
        $scenarioType = $character->scenario_type;
        $goals = $character->goals ?? [];

        // Analyze character aptitudes
        $aptitudes = $character->aptitudes;
        $bestDistance = $this->getBestDistanceAptitude($aptitudes);

        return match ($scenarioType) {
            'unity_cup' => 'team_coordination',
            'ura_finale' => $this->determineUraStrategy($character, $bestDistance),
            default => 'balanced_development',
        };
    }

    /**
     * Determine URA Finale specific strategy
     */
    protected function determineUraStrategy(Character $character, string $bestDistance): string
    {
        $goals = $character->goals ?? [];
        $targetGrade = $goals['target_grade'] ?? 'A';

        // Strategy based on target grade and distance specialization
        return match (true) {
            $targetGrade === 'S' || $targetGrade === 'SS' => 'elite_optimization',
            $bestDistance === 'sprint' || $bestDistance === 'mile' => 'speed_focused',
            $bestDistance === 'long' => 'stamina_focused',
            default => 'balanced_development',
        };
    }

    /**
     * Calculate priority stats with gaps, priorities, and weights
     *
     * @return array<string, array{current: int, target: int, gap: int, priority: int, weight: float}>
     */
    protected function calculatePriorityStats(): array
        $currentStats = $character->current_stats ?? [];
        $goals = $character->goals ?? [];
        $targetStats = $goals['target_stats'] ?? [];

        $priorityStats = [];
        $totalGap = 0;

        // Calculate gaps for each stat
        foreach ($this->statPriorityWeights as $stat => $basePriority) {
            $current = $currentStats[$stat] ?? 0;
            $target = $targetStats[$stat] ?? $this->getDefaultTarget($stat, $character);
            $gap = max(0, $target - $current);

            $priorityStats[$stat] = [
                'current' => $current,
                'target' => $target,
                'gap' => $gap,
                'priority' => $basePriority,
                'weight' => 0.0, // Will be calculated after total gap is known
            ];

            $totalGap = ($totalGap ?? 0) + $gap * $basePriority;
        }

        // Calculate weights based on gap and priority
        foreach ($priorityStats as $stat => &$data) {
            if ($totalGap > 0) {
                (is_array($data) && isset($data['weight']) ? $data['weight'] : null) = ((is_array($data) && isset($data['gap']) ? $data['gap'] : null) * (is_array($data) && isset($data['priority']) ? $data['priority'] : null)) / $totalGap;
            } else {
                (is_array($data) && isset($data['weight']) ? $data['weight'] : null) = 1.0 / count($priorityStats);
            }
        }

        // Sort by weight (highest first)
        uasort($priorityStats, fn ($a, $b) => $b['weight'] <=> $a['weight']);

        return $priorityStats;
    }

    /**
     * Get default target for a stat based on character and scenario
     */
    protected function getDefaultTarget(string $stat, Character $character): int
    {
        $scenarioType = $character->scenario_type;

        // Default targets for A-grade career
        $defaults = [
            'speed' => 900,
            'stamina' => 600,
            'power' => 700,
            'guts' => 400,
            'wit' => 500,
        ];

        // Adjust stamina based on distance specialization
        if ($stat === 'stamina') {
            $bestDistance = $this->getBestDistanceAptitude($character->aptitudes);
            $requirements = $this->staminaRequirements[$bestDistance] ?? $this->staminaRequirements['mile'];

            return $requirements['career'];
        }

        return $defaults[$stat] ?? 600;
    }

    /**
     * Determine training focus areas
     *
     * @param  array<string, array{current: int, target: int, gap: int, priority: int, weight: float}>  $priorityStats
     * @return array<string, mixed>
     */
    protected function determineTrainingFocus(): array
        // Get top 3 priority stats
        $topStats = array_slice(array_keys($priorityStats), 0, 3);

        // Determine focus intensity
        $focusIntensity = $this->calculateFocusIntensity($priorityStats);

        // Determine if balanced or specialized approach
        $approach = $focusIntensity > 0.6 ? 'specialized' : 'balanced';

        return [
            'primary_stats' => $topStats,
            'approach' => $approach,
            'focus_intensity' => $focusIntensity,
            'training_distribution' => $this->calculateTrainingDistribution($priorityStats),
        ];
    }

    /**
     * Calculate focus intensity (0.0 = balanced, 1.0 = highly specialized)
     *
     * @param  array<string, array{current: int, target: int, gap: int, priority: int, weight: float}>  $priorityStats
     */
    protected function calculateFocusIntensity(array $priorityStats): float
    {
        if (empty($priorityStats)) {
            return 0.5;
        }

        // Calculate variance in weights
        $weights = array_column($priorityStats, 'weight');
        $mean = array_sum($weights) / count($weights);
        $variance = array_sum(array_map(fn ($w) => ($w - $mean) ** 2, $weights)) / count($weights);

        // Normalize to 0-1 range
        return min(1.0, $variance * 5);
    }

    /**
     * Calculate recommended training distribution
     *
     * @param  array<string, array{current: int, target: int, gap: int, priority: int, weight: float}>  $priorityStats
     * @return array<string, float>
     */
    protected function calculateTrainingDistribution(): array
        $distribution = [];

        foreach ($priorityStats as $stat => $data) {
            $distribution[$stat] = round((is_array($data) && isset($data['weight']) ? $data['weight'] : null) * 100, 1);
        }

        return $distribution;
    }

    /**
     * Track milestones and progress
     *
     * @param  array<string, array{current: int, target: int, gap: int, priority: int, weight: float}>  $priorityStats
     * @return array<string, mixed>
     */
    protected function trackMilestones(): array
        $milestones = [];

        // Check stat breakpoints (901 and 1200)
        foreach ($priorityStats as $stat => $data) {
            $current = (is_array($data) && isset($data['current']) ? $data['current'] : null);
            $target = (is_array($data) && isset($data['target']) ? $data['target'] : null);

            $milestones[$stat] = [
                'current' => $current,
                'target' => $target,
                'progress_percent' => $target > 0 ? round(($current / $target) * 100, 1) : 100,
                'breakpoints' => [
                    '900' => [
                        'reached' => $current >= 900,
                        'remaining' => max(0, 900 - $current),
                    ],
                    '1200' => [
                        'reached' => $current >= 1200,
                        'remaining' => max(0, 1200 - $current),
                    ],
                ],
            ];
        }

        // Calculate overall progress
        $totalProgress = 0;
        foreach ($milestones as $milestone) {
            $totalProgress = ($totalProgress ?? 0) + $milestone['progress_percent'];
        }
        $overallProgress = count($milestones) > 0 ? $totalProgress / count($milestones) : 0;

        return [
            'stat_milestones' => $milestones,
            'overall_progress' => round($overallProgress, 1),
            'phase' => $this->determineCareerPhase($character),
        ];
    }

    /**
     * Determine current career phase
     */
    protected function determineCareerPhase(Character $character): string
    {
        $careerStage = $character->career_stage ?? 'junior';

        return match ($careerStage) {
            'junior' => 'early_development',
            'classic' => 'mid_development',
            'senior' => 'final_optimization',
            default => 'unknown',
        };
    }

    /**
     * Generate strategic recommendations
     *
     * @param  array<string, array{current: int, target: int, gap: int, priority: int, weight: float}>  $priorityStats
     * @param  array<string, mixed>  $trainingFocus
     * @return array<string, string>
     */
    protected function generateRecommendations(): array
        $recommendations = [];

        // Strategy-specific recommendations
        $recommendations['strategy'] = match ($strategy) {
            'elite_optimization' => 'Focus on maximizing all stats above 900 for elite performance',
            'speed_focused' => 'Prioritize Speed and Power for sprint/mile dominance',
            'stamina_focused' => 'Build high Stamina and Wit for long-distance races',
            'team_coordination' => 'Balance individual stats with team synergy opportunities',
            default => 'Maintain balanced development across all stats',
        };

        // Training focus recommendations
        $topStat = array_key_first($priorityStats);
        $topGap = $priorityStats[$topStat]['gap'] ?? 0;

        if ($topGap > 300) {
            $recommendations['priority'] = "Critical gap in {$topStat} ({$topGap} points). Focus heavily on {$topStat} training.";
        } elseif ($topGap > 150) {
            $recommendations['priority'] = "Significant gap in {$topStat} ({$topGap} points). Prioritize {$topStat} training.";
        } else {
            $recommendations['priority'] = 'Stats are well-balanced. Continue with current training distribution.';
        }

        // Energy and mood recommendations
        $energyLevel = $character->energy_level ?? 100;
        $moodStatus = $character->mood_status ?? 'normal';

        if ($energyLevel < 30) {
            $recommendations['energy'] = 'Energy critically low. Rest is strongly recommended.';
        } elseif ($energyLevel < 50) {
            $recommendations['energy'] = 'Energy moderate. Consider rest if failure risk is high.';
        }

        if ($moodStatus === 'awful' || $moodStatus === 'bad') {
            $recommendations['mood'] = 'Poor mood affects training. Consider mood-improving activities.';
        }

        // Scenario-specific recommendations
        if ($character->scenario_type === 'unity_cup') {
            $recommendations['scenario'] = 'Coordinate with teammates for Spirit Burst opportunities.';
        }

        return $recommendations;
    }

    /**
     * Calculate confidence score for recommendations
     *
     * @param  array<string, array{current: int, target: int, gap: int, priority: int, weight: float}>  $priorityStats
     */
    protected function calculateConfidence(Character $character, array $priorityStats): float
    {
        $confidence = 1.0;

        // Reduce confidence if goals are not well-defined
        $goals = $character->goals ?? [];
        if (empty($goals['target_stats'])) {
            $confidence *= 0.7;
        }

        // Reduce confidence if character state is uncertain
        if (($character->energy_level ?? 100) < 30) {
            $confidence *= 0.8;
        }

        // Reduce confidence if mood is poor
        $moodStatus = $character->mood_status ?? 'normal';
        if ($moodStatus === 'awful' || $moodStatus === 'bad') {
            $confidence *= 0.9;
        }

        return round($confidence, 2);
    }

    /**
     * Get best distance aptitude from character aptitudes
     */
    protected function getBestDistanceAptitude($aptitudes): string
    {
        $distanceGrades = [];

        foreach ($aptitudes as $aptitude) {
            if (in_array($aptitude->distance_type, ['sprint', 'mile', 'medium', 'long'])) {
                $distanceGrades[$aptitude->distance_type] = $this->gradeToNumeric($aptitude->grade);
            }
        }

        if (empty($distanceGrades)) {
            return 'mile'; // Default
        }

        arsort($distanceGrades);

        return array_key_first($distanceGrades);
    }

    /**
     * Convert grade letter to numeric value for comparison
     */
    protected function gradeToNumeric(string $grade): int
    {
        return match ($grade) {
            'SS' => 9,
            'S' => 8,
            'A' => 7,
            'B' => 6,
            'C' => 5,
            'D' => 4,
            'E' => 3,
            'F' => 2,
            'G' => 1,
            default => 0,
        };
    }
}
