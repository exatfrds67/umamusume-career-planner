<?php

namespace App\Services\MCP\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;

/**
 * Summer Camp Optimization Agent
 *
 * Specializes in optimizing the 4-turn high-efficiency Summer Camp period.
 * Summer Camp occurs during the summer months and provides enhanced training
 * effectiveness, making it a critical optimization window.
 */
class SummerCampOptimizationAgent
{
    protected MCPClientService $mcpClient;

    /**
     * Summer Camp bonus multipliers
     *
     * @var array<string, float>
     */
    protected array $summerCampBonuses = [
        'stat_gain_multiplier' => 1.5,  // 50% increased stat gains
        'energy_recovery_bonus' => 1.3, // 30% better energy recovery
        'skill_hint_rate_bonus' => 1.4, // 40% increased skill hint rate
        'friendship_gain_bonus' => 1.5, // 50% increased friendship gains
    ];

    /**
     * Summer Camp timing (turn ranges for each year)
     *
     * @var array<string, array{start: int, end: int, turns: int}>
     */
    protected array $summerCampTiming = [
        'junior_year' => ['start' => 8, 'end' => 11, 'turns' => 4],
        'classic_year' => ['start' => 28, 'end' => 31, 'turns' => 4],
        'senior_year' => ['start' => 48, 'end' => 51, 'turns' => 4],
    ];

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
    }

    /**
     * Analyze Summer Camp optimization opportunities
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     summer_camp_status: array<string, mixed>,
     *     optimization_strategy: array<string, mixed>,
     *     priority_actions: array<string, mixed>,
     *     expected_gains: array<string, mixed>,
     *     recommendations: array<string, string>,
     *     efficiency_score: float
     * }
     */
    public function analyzeSummerCampOptimization(Character $character, array $context = []): array
    {
        // Determine Summer Camp status
        $summerCampStatus = $this->determineSummerCampStatus($character, $context);

        // Calculate optimization strategy
        $optimizationStrategy = $this->calculateOptimizationStrategy(
            $character,
            $summerCampStatus
        );

        // Determine priority actions
        $priorityActions = $this->determinePriorityActions(
            $character,
            $summerCampStatus,
            $optimizationStrategy
        );

        // Calculate expected gains
        $expectedGains = $this->calculateExpectedGains(
            $character,
            $summerCampStatus,
            $priorityActions
        );

        // Generate recommendations
        $recommendations = $this->generateSummerCampRecommendations(
            $character,
            $summerCampStatus,
            $optimizationStrategy,
            $priorityActions
        );

        // Calculate efficiency score
        $efficiencyScore = $this->calculateEfficiencyScore(
            $character,
            $summerCampStatus,
            $optimizationStrategy
        );

        return [
            'summer_camp_status' => $summerCampStatus,
            'optimization_strategy' => $optimizationStrategy,
            'priority_actions' => $priorityActions,
            'expected_gains' => $expectedGains,
            'recommendations' => $recommendations,
            'efficiency_score' => $efficiencyScore,
        ];
    }

    /**
     * Determine Summer Camp status
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function determineSummerCampStatus(Character $character, array $context): array
    {
        $currentTurn = isset($context['current_turn']) && is_numeric($context['current_turn']) ? (int) $context['current_turn'] : 1;
        $careerStage = $character->career_stage ?? 'junior';

        // Determine which Summer Camp period we're in or approaching
        $campPeriod = $this->identifyCampPeriod($currentTurn, $careerStage);

        // Check if currently in Summer Camp
        $isInCamp = $this->isInSummerCamp($currentTurn, $campPeriod);

        // Calculate turns until next camp
        $turnsUntilCamp = $this->calculateTurnsUntilCamp($currentTurn, $campPeriod);

        // Calculate turns remaining in current camp
        $turnsRemainingInCamp = $this->calculateTurnsRemainingInCamp($currentTurn, $campPeriod);

        // Determine camp phase
        $campPhase = $this->determineCampPhase($isInCamp, $turnsUntilCamp);

        return [
            'current_turn' => $currentTurn,
            'career_stage' => $careerStage,
            'camp_period' => $campPeriod,
            'is_in_camp' => $isInCamp,
            'turns_until_camp' => $turnsUntilCamp,
            'turns_remaining_in_camp' => $turnsRemainingInCamp,
            'camp_phase' => $campPhase,
            'bonuses' => $this->summerCampBonuses,
        ];
    }

    /**
     * Identify which Summer Camp period is relevant
     *
     * @return array{year: string, start: int, end: int, turns: int}
     */
    protected function identifyCampPeriod(int $currentTurn, string $careerStage): array
    {
        // Determine which year's camp is relevant
        $year = match ($careerStage) {
            'junior' => 'junior_year',
            'classic' => 'classic_year',
            'senior' => 'senior_year',
            default => 'junior_year',
        };

        $timing = $this->summerCampTiming[$year];

        return [
            'year' => $year,
            'start' => $timing['start'],
            'end' => $timing['end'],
            'turns' => $timing['turns'],
        ];
    }

    /**
     * Check if currently in Summer Camp
     *
     * @param  array{year: string, start: int, end: int, turns: int}  $campPeriod
     */
    protected function isInSummerCamp(int $currentTurn, array $campPeriod): bool
    {
        return $currentTurn >= $campPeriod['start'] && $currentTurn <= $campPeriod['end'];
    }

    /**
     * Calculate turns until next Summer Camp
     *
     * @param  array{year: string, start: int, end: int, turns: int}  $campPeriod
     */
    protected function calculateTurnsUntilCamp(int $currentTurn, array $campPeriod): int
    {
        if ($currentTurn < $campPeriod['start']) {
            return $campPeriod['start'] - $currentTurn;
        }

        return 0; // Already in or past camp
    }

    /**
     * Calculate turns remaining in current Summer Camp
     *
     * @param  array{year: string, start: int, end: int, turns: int}  $campPeriod
     */
    protected function calculateTurnsRemainingInCamp(int $currentTurn, array $campPeriod): int
    {
        if ($this->isInSummerCamp($currentTurn, $campPeriod)) {
            return $campPeriod['end'] - $currentTurn + 1;
        }

        return 0;
    }

    /**
     * Determine camp phase
     */
    protected function determineCampPhase(bool $isInCamp, int $turnsUntilCamp): string
    {
        return match (true) {
            $isInCamp => 'active',
            $turnsUntilCamp <= 2 => 'preparation',
            $turnsUntilCamp <= 5 => 'planning',
            default => 'distant',
        };
    }

    /**
     * Calculate optimization strategy
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @return array<string, mixed>
     */
    protected function calculateOptimizationStrategy(Character $character, array $summerCampStatus): array
    {
        $campPhase = isset($summerCampStatus['camp_phase']) && is_string($summerCampStatus['camp_phase']) ? $summerCampStatus['camp_phase'] : 'distant';

        // Strategy varies by phase
        /** @var array<string, mixed> $strategy */
        $strategy = match ($campPhase) {
            'active' => $this->getActiveCampStrategy($character, $summerCampStatus),
            'preparation' => $this->getPreparationStrategy($character, $summerCampStatus),
            'planning' => $this->getPlanningStrategy($character, $summerCampStatus),
            default => $this->getDistantStrategy($character, $summerCampStatus),
        };

        return $strategy;
    }

    /**
     * Get strategy for active Summer Camp
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @return array<string, mixed>
     */
    protected function getActiveCampStrategy(Character $character, array $summerCampStatus): array
    {
        $turnsRemaining = isset($summerCampStatus['turns_remaining_in_camp']) && is_numeric($summerCampStatus['turns_remaining_in_camp'])
            ? (int) $summerCampStatus['turns_remaining_in_camp']
            : 4;

        return [
            'phase' => 'active',
            'focus' => 'maximize_gains',
            'intensity' => 'maximum',
            'turns_remaining' => $turnsRemaining,
            'priorities' => [
                'high_priority_stat_training',
                'skill_hint_collection',
                'friendship_building',
            ],
            'avoid' => [
                'rest_unless_critical',
                'low_value_activities',
                'unnecessary_events',
            ],
        ];
    }

    /**
     * Get preparation strategy (1-2 turns before camp)
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @return array<string, mixed>
     */
    protected function getPreparationStrategy(Character $character, array $summerCampStatus): array
    {
        return [
            'phase' => 'preparation',
            'focus' => 'optimize_state',
            'intensity' => 'moderate',
            'turns_until_camp' => $summerCampStatus['turns_until_camp'] ?? 0,
            'priorities' => [
                'maximize_energy',
                'improve_mood',
                'clear_negative_conditions',
                'position_support_cards',
            ],
            'target_state' => [
                'energy' => 100,
                'mood' => 'great',
                'conditions' => 'positive_only',
            ],
        ];
    }

    /**
     * Get planning strategy (3-5 turns before camp)
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @return array<string, mixed>
     */
    protected function getPlanningStrategy(Character $character, array $summerCampStatus): array
    {
        return [
            'phase' => 'planning',
            'focus' => 'strategic_preparation',
            'intensity' => 'balanced',
            'turns_until_camp' => $summerCampStatus['turns_until_camp'] ?? 0,
            'priorities' => [
                'identify_priority_stats',
                'plan_training_sequence',
                'manage_resources',
                'prepare_support_deck',
            ],
        ];
    }

    /**
     * Get distant strategy (more than 5 turns before camp)
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @return array<string, mixed>
     */
    protected function getDistantStrategy(Character $character, array $summerCampStatus): array
    {
        return [
            'phase' => 'distant',
            'focus' => 'normal_development',
            'intensity' => 'standard',
            'turns_until_camp' => $summerCampStatus['turns_until_camp'] ?? 0,
            'priorities' => [
                'balanced_training',
                'resource_accumulation',
                'goal_progress',
            ],
        ];
    }

    /**
     * Determine priority actions for Summer Camp
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @param  array<string, mixed>  $optimizationStrategy
     * @return array<array-key, array<string, mixed>>
     */
    protected function determinePriorityActions(
        Character $character,
        array $summerCampStatus,
        array $optimizationStrategy
    ): array {
        $campPhase = isset($summerCampStatus['camp_phase']) && is_string($summerCampStatus['camp_phase']) ? $summerCampStatus['camp_phase'] : 'distant';

        if ($campPhase === 'active') {
            return $this->getActiveCampActions($character, $summerCampStatus);
        } elseif ($campPhase === 'preparation') {
            return $this->getPreparationActions($character, $summerCampStatus);
        }

        return $this->getPlanningActions($character, $summerCampStatus);
    }

    /**
     * Get priority actions during active Summer Camp
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @return array<string, array<string, mixed>>
     */
    protected function getActiveCampActions(Character $character, array $summerCampStatus): array
    {
        /** @var array<string, int> $currentStats */
        $currentStats = $character->current_stats ?? [];
        /** @var array<string, mixed> $goals */
        $goals = $character->goals ?? [];
        /** @var array<string, int> $targetStats */
        $targetStats = is_array($goals) && isset($goals['target_stats']) && is_array($goals['target_stats'])
            ? $goals['target_stats']
            : [];

        // Identify stats with largest gaps
        /** @var array<string, int> $statGaps */
        $statGaps = [];
        foreach ($targetStats as $stat => $target) {
            if (! is_string($stat) || ! is_numeric($target)) {
                continue;
            }

            $current = is_array($currentStats) && isset($currentStats[$stat]) ? (int) $currentStats[$stat] : 0;
            $gap = max(0, (int) $target - $current);
            $statGaps[$stat] = $gap;
        }

        arsort($statGaps);
        $topStats = array_slice(array_keys($statGaps), 0, 3);

        return [
            'turn_1' => [
                'action' => 'training',
                'focus' => $topStats[0] ?? 'speed',
                'reason' => 'Maximize gains on highest priority stat',
            ],
            'turn_2' => [
                'action' => 'training',
                'focus' => $topStats[1] ?? 'stamina',
                'reason' => 'Continue high-priority stat development',
            ],
            'turn_3' => [
                'action' => 'training',
                'focus' => $topStats[0] ?? 'speed',
                'reason' => 'Double down on primary stat',
            ],
            'turn_4' => [
                'action' => 'training',
                'focus' => $topStats[2] ?? 'power',
                'reason' => 'Final camp turn optimization',
            ],
        ];
    }

    /**
     * Get preparation actions before Summer Camp
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @return array<int, array<string, mixed>>
     */
    protected function getPreparationActions(Character $character, array $summerCampStatus): array
    {
        $energyLevel = $character->energy_level ?? 100;
        $moodStatus = $character->mood_status ?? 'normal';

        /** @var array<int, array<string, mixed>> $actions */
        $actions = [];

        // Energy preparation
        if ($energyLevel < 80) {
            $actions[] = [
                'priority' => 'critical',
                'action' => 'rest',
                'reason' => 'Maximize energy before Summer Camp',
                'target' => 100,
            ];
        }

        // Mood preparation
        if ($moodStatus !== 'great' && $moodStatus !== 'good') {
            $actions[] = [
                'priority' => 'high',
                'action' => 'improve_mood',
                'reason' => 'Optimize mood for enhanced camp effectiveness',
                'target' => 'great',
            ];
        }

        // Default preparation
        if (empty($actions)) {
            $actions[] = [
                'priority' => 'medium',
                'action' => 'light_training',
                'reason' => 'Maintain readiness while preserving resources',
            ];
        }

        return $actions;
    }

    /**
     * Get planning actions before Summer Camp
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @return array<int, array<string, mixed>>
     */
    protected function getPlanningActions(Character $character, array $summerCampStatus): array
    {
        return [
            [
                'priority' => 'medium',
                'action' => 'identify_goals',
                'reason' => 'Determine which stats to prioritize during camp',
            ],
            [
                'priority' => 'medium',
                'action' => 'optimize_deck',
                'reason' => 'Ensure support card deck is optimized for camp training',
            ],
            [
                'priority' => 'low',
                'action' => 'resource_management',
                'reason' => 'Plan resource allocation for camp period',
            ],
        ];
    }

    /**
     * Calculate expected gains from Summer Camp
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @param  array<string, mixed>  $priorityActions
     * @return array<string, mixed>
     */
    protected function calculateExpectedGains(
        Character $character,
        array $summerCampStatus,
        array $priorityActions
    ): array {
        $campPhase = isset($summerCampStatus['camp_phase']) && is_string($summerCampStatus['camp_phase']) ? $summerCampStatus['camp_phase'] : 'distant';

        if ($campPhase !== 'active') {
            return [
                'estimated_stat_gains' => [],
                'total_gain_potential' => 0,
                'note' => 'Gains calculated when Summer Camp is active',
            ];
        }

        // Base stat gains per training (with Summer Camp bonus)
        $baseGainPerTraining = 10;
        $campMultiplier = $this->summerCampBonuses['stat_gain_multiplier'];
        $enhancedGain = (int) ($baseGainPerTraining * $campMultiplier);

        // Calculate expected gains for 4 turns
        $turnsInCamp = 4;
        $totalGainPerStat = $enhancedGain * $turnsInCamp;

        return [
            'base_gain_per_training' => $baseGainPerTraining,
            'camp_multiplier' => $campMultiplier,
            'enhanced_gain_per_training' => $enhancedGain,
            'turns_in_camp' => $turnsInCamp,
            'total_gain_per_stat' => $totalGainPerStat,
            'estimated_stat_gains' => [
                'primary_stat' => $totalGainPerStat * 2, // 2 trainings focused
                'secondary_stat' => $totalGainPerStat,   // 1 training
                'tertiary_stat' => $totalGainPerStat,    // 1 training
            ],
            'total_gain_potential' => $totalGainPerStat * 4,
            'comparison_to_normal' => [
                'normal_period_gains' => $baseGainPerTraining * $turnsInCamp,
                'camp_gains' => $enhancedGain * $turnsInCamp,
                'advantage' => ($enhancedGain - $baseGainPerTraining) * $turnsInCamp,
            ],
        ];
    }

    /**
     * Generate Summer Camp recommendations
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @param  array<string, mixed>  $optimizationStrategy
     * @param  array<string, mixed>  $priorityActions
     * @return array<string, string>
     */
    protected function generateSummerCampRecommendations(
        Character $character,
        array $summerCampStatus,
        array $optimizationStrategy,
        array $priorityActions
    ): array {
        /** @var array<string, string> $recommendations */
        $recommendations = [];
        $campPhase = isset($summerCampStatus['camp_phase']) && is_string($summerCampStatus['camp_phase']) ? $summerCampStatus['camp_phase'] : 'distant';
        $turnsUntilCamp = isset($summerCampStatus['turns_until_camp']) && is_numeric($summerCampStatus['turns_until_camp']) ? (int) $summerCampStatus['turns_until_camp'] : 0;

        // Phase-specific recommendations
        $recommendations['phase'] = match ($campPhase) {
            'active' => 'Summer Camp is ACTIVE! Focus exclusively on high-priority training. Avoid rest unless energy is critical.',
            'preparation' => "Summer Camp starts in {$turnsUntilCamp} turns. Maximize energy and mood NOW.",
            'planning' => "Summer Camp in {$turnsUntilCamp} turns. Plan your priority stats and prepare support deck.",
            default => "Summer Camp is {$turnsUntilCamp} turns away. Continue normal development.",
        };

        // Energy recommendations
        $energyLevel = $character->energy_level ?? 100;
        if ($campPhase === 'active') {
            if ($energyLevel < 40) {
                $recommendations['energy'] = 'Energy is low during Summer Camp. Rest if below 30, otherwise push through with caution.';
            } else {
                $recommendations['energy'] = 'Maintain energy above 40 to maximize all 4 camp turns.';
            }
        } elseif ($campPhase === 'preparation') {
            $recommendations['energy'] = 'CRITICAL: Reach 100 energy before Summer Camp starts for maximum effectiveness.';
        }

        // Mood recommendations
        if ($campPhase === 'active' || $campPhase === 'preparation') {
            $moodStatus = $character->mood_status ?? 'normal';
            if ($moodStatus !== 'great' && $moodStatus !== 'good') {
                $recommendations['mood'] = 'Improve mood to "Great" for maximum Summer Camp benefits (+20% effectiveness).';
            }
        }

        // Strategy recommendations
        if ($campPhase === 'active') {
            $recommendations['strategy'] = 'This is the most efficient 4-turn period in your career. Focus on stats with largest gaps.';
        }

        return $recommendations;
    }

    /**
     * Calculate efficiency score
     *
     * @param  array<string, mixed>  $summerCampStatus
     * @param  array<string, mixed>  $optimizationStrategy
     */
    protected function calculateEfficiencyScore(
        Character $character,
        array $summerCampStatus,
        array $optimizationStrategy
    ): float {
        $score = 0.0;
        $campPhase = isset($summerCampStatus['camp_phase']) && is_string($summerCampStatus['camp_phase']) ? $summerCampStatus['camp_phase'] : 'distant';

        if ($campPhase === 'active') {
            // Score based on character state during active camp
            $energyLevel = $character->energy_level ?? 100;
            $moodStatus = $character->mood_status ?? 'normal';

            // Energy component (50%)
            $energyScore = $energyLevel / 100;
            $score += $energyScore * 0.5;

            // Mood component (50%)
            $moodScore = match ($moodStatus) {
                'great' => 1.0,
                'good' => 0.8,
                'normal' => 0.6,
                'bad' => 0.4,
                'awful' => 0.2,
                default => 0.6,
            };
            $score += $moodScore * 0.5;
        } elseif ($campPhase === 'preparation') {
            // Score based on readiness for camp
            $energyLevel = $character->energy_level ?? 100;
            $moodStatus = $character->mood_status ?? 'normal';

            $readinessScore = ($energyLevel / 100) * 0.6 + ($moodStatus === 'great' ? 0.4 : 0.2);
            $score = $readinessScore;
        } else {
            // Neutral score for planning/distant phases
            $score = 0.7;
        }

        return round(min(1.0, $score), 2);
    }
}
