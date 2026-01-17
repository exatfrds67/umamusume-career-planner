<?php

namespace App\Services\MCP\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;

/**
 * Resource Management Agent
 *
 * Handles turn economy calculations and optimal resource allocation.
 * Manages energy, mood, turn planning, and resource optimization
 * across the 60-70 turn career progression.
 */
class ResourceManagementAgent
{
    protected MCPClientService $mcpClient;

    /**
     * Career phase turn allocations
     *
     * @var array<string, array{turns: int, priority: string}>
     */
    protected array $phaseAllocations = [
        'junior' => ['turns' => 20, 'priority' => 'foundation'],
        'classic' => ['turns' => 25, 'priority' => 'development'],
        'senior' => ['turns' => 20, 'priority' => 'optimization'],
    ];

    /**
     * Energy thresholds for different actions
     *
     * @var array<string, int>
     */
    protected array $energyThresholds = [
        'critical' => 20,
        'low' => 40,
        'moderate' => 60,
        'good' => 80,
    ];

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
    }

    /**
     * Analyze resource allocation and provide optimization recommendations
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     turn_economy: array<string, mixed>,
     *     energy_management: array<string, mixed>,
     *     resource_allocation: array<string, mixed>,
     *     optimization_recommendations: array<string, string>,
     *     efficiency_score: float
     * }
     */
    public function analyzeResourceManagement(Character $character, array $context = []): array
    {
        // Analyze turn economy
        $turnEconomy = $this->analyzeTurnEconomy($character, $context);

        // Analyze energy management
        $energyManagement = $this->analyzeEnergyManagement($character);

        // Calculate optimal resource allocation
        $resourceAllocation = $this->calculateResourceAllocation($character, $turnEconomy);

        // Generate optimization recommendations
        $recommendations = $this->generateOptimizationRecommendations(
            $character,
            $turnEconomy,
            $energyManagement,
            $resourceAllocation
        );

        // Calculate efficiency score
        $efficiencyScore = $this->calculateEfficiencyScore(
            $character,
            $turnEconomy,
            $energyManagement
        );

        return [
            'turn_economy' => $turnEconomy,
            'energy_management' => $energyManagement,
            'resource_allocation' => $resourceAllocation,
            'optimization_recommendations' => $recommendations,
            'efficiency_score' => $efficiencyScore,
        ];
    }

    /**
     * Analyze turn economy across career phases
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function analyzeTurnEconomy(Character $character, array $context = []): array
    {
        $currentTurn = $context['current_turn'] ?? 1;
        $totalTurns = $context['total_turns'] ?? 65;
        $careerStage = $character->career_stage ?? 'junior';

        // Calculate turns remaining in current phase
        $phaseInfo = $this->phaseAllocations[$careerStage] ?? $this->phaseAllocations['junior'];
        $turnsInPhase = $phaseInfo['turns'];
        $phaseStartTurn = $this->getPhaseStartTurn($careerStage);
        $turnsRemainingInPhase = max(0, $phaseStartTurn + $turnsInPhase - $currentTurn);

        // Calculate overall progress
        $progressPercent = ($currentTurn / $totalTurns) * 100;

        // Determine turn allocation efficiency
        $turnEfficiency = $this->calculateTurnEfficiency($character, $currentTurn, $totalTurns);

        return [
            'current_turn' => $currentTurn,
            'total_turns' => $totalTurns,
            'turns_remaining' => $totalTurns - $currentTurn,
            'current_phase' => $careerStage,
            'phase_priority' => $phaseInfo['priority'],
            'turns_in_phase' => $turnsInPhase,
            'turns_remaining_in_phase' => $turnsRemainingInPhase,
            'progress_percent' => round($progressPercent, 1),
            'turn_efficiency' => $turnEfficiency,
            'phase_breakdown' => $this->calculatePhaseBreakdown($currentTurn, $totalTurns),
        ];
    }

    /**
     * Get starting turn for a career phase
     */
    protected function getPhaseStartTurn(string $phase): int
    {
        return match ($phase) {
            'junior' => 1,
            'classic' => 21,
            'senior' => 46,
            default => 1,
        };
    }

    /**
     * Calculate turn efficiency based on stat progress
     */
    protected function calculateTurnEfficiency(Character $character, int $currentTurn, int $totalTurns): float
    {
        $currentStats = $character->current_stats ?? [];
        $goals = $character->goals ?? [];
        $targetStats = $goals['target_stats'] ?? [];

        if (empty($targetStats)) {
            return 0.5; // Neutral efficiency if no goals
        }

        // Calculate average progress toward goals
        $totalProgress = 0;
        $statCount = 0;

        foreach ($targetStats as $stat => $target) {
            $current = $currentStats[$stat] ?? 0;
            if ($target > 0) {
                $progress = min(1.0, $current / $target);
                $totalProgress += $progress;
                $statCount++;
            }
        }

        $averageProgress = $statCount > 0 ? $totalProgress / $statCount : 0;

        // Expected progress based on turn count
        $expectedProgress = $currentTurn / $totalTurns;

        // Efficiency is actual progress relative to expected progress
        $efficiency = $expectedProgress > 0 ? $averageProgress / $expectedProgress : 0;

        return round(min(1.0, $efficiency), 2);
    }

    /**
     * Calculate phase breakdown
     *
     * @return array<string, array{start: int, end: int, turns: int, priority: string}>
     */
    protected function calculatePhaseBreakdown(int $currentTurn, int $totalTurns): array
    {
        return [
            'junior' => [
                'start' => 1,
                'end' => 20,
                'turns' => 20,
                'priority' => 'foundation',
                'completed' => $currentTurn > 20,
            ],
            'classic' => [
                'start' => 21,
                'end' => 45,
                'turns' => 25,
                'priority' => 'development',
                'completed' => $currentTurn > 45,
            ],
            'senior' => [
                'start' => 46,
                'end' => $totalTurns,
                'turns' => $totalTurns - 45,
                'priority' => 'optimization',
                'completed' => $currentTurn >= $totalTurns,
            ],
        ];
    }

    /**
     * Analyze energy management
     *
     * @return array<string, mixed>
     */
    protected function analyzeEnergyManagement(Character $character): array
    {
        $energyLevel = $character->energy_level ?? 100;
        $moodStatus = $character->mood_status ?? 'normal';

        // Determine energy status
        $energyStatus = $this->determineEnergyStatus($energyLevel);

        // Calculate energy recovery needs
        $recoveryNeeded = max(0, 80 - $energyLevel);
        $turnsToRecover = $this->calculateTurnsToRecover($recoveryNeeded);

        // Analyze mood impact
        $moodImpact = $this->analyzeMoodImpact($moodStatus);

        // Calculate sustainable training rate
        $sustainableRate = $this->calculateSustainableTrainingRate($energyLevel, $moodStatus);

        return [
            'current_energy' => $energyLevel,
            'energy_status' => $energyStatus,
            'recovery_needed' => $recoveryNeeded,
            'turns_to_recover' => $turnsToRecover,
            'mood_status' => $moodStatus,
            'mood_impact' => $moodImpact,
            'sustainable_training_rate' => $sustainableRate,
            'rest_recommended' => $energyLevel < $this->energyThresholds['moderate'],
        ];
    }

    /**
     * Determine energy status category
     */
    protected function determineEnergyStatus(int $energyLevel): string
    {
        return match (true) {
            $energyLevel >= $this->energyThresholds['good'] => 'excellent',
            $energyLevel >= $this->energyThresholds['moderate'] => 'good',
            $energyLevel >= $this->energyThresholds['low'] => 'moderate',
            $energyLevel >= $this->energyThresholds['critical'] => 'low',
            default => 'critical',
        };
    }

    /**
     * Calculate turns needed to recover energy
     */
    protected function calculateTurnsToRecover(int $recoveryNeeded): int
    {
        // Rest recovers 50 energy per turn
        return (int) ceil($recoveryNeeded / 50);
    }

    /**
     * Analyze mood impact on training
     *
     * @return array<string, mixed>
     */
    protected function analyzeMoodImpact(string $moodStatus): array
    {
        $impacts = [
            'great' => ['multiplier' => 1.20, 'description' => '+20% training effectiveness'],
            'good' => ['multiplier' => 1.10, 'description' => '+10% training effectiveness'],
            'normal' => ['multiplier' => 1.00, 'description' => 'Normal training effectiveness'],
            'bad' => ['multiplier' => 0.90, 'description' => '-10% training effectiveness'],
            'awful' => ['multiplier' => 0.80, 'description' => '-20% training effectiveness'],
        ];

        return $impacts[$moodStatus] ?? $impacts['normal'];
    }

    /**
     * Calculate sustainable training rate (trainings per turn)
     */
    protected function calculateSustainableTrainingRate(int $energyLevel, string $moodStatus): float
    {
        // Base rate: 1 training per turn
        $baseRate = 1.0;

        // Adjust based on energy level
        $energyModifier = match (true) {
            $energyLevel >= 80 => 1.0,
            $energyLevel >= 60 => 0.9,
            $energyLevel >= 40 => 0.7,
            $energyLevel >= 20 => 0.5,
            default => 0.3,
        };

        // Adjust based on mood
        $moodModifier = match ($moodStatus) {
            'great', 'good' => 1.0,
            'normal' => 0.9,
            'bad' => 0.7,
            'awful' => 0.5,
            default => 0.9,
        };

        return round($baseRate * $energyModifier * $moodModifier, 2);
    }

    /**
     * Calculate optimal resource allocation
     *
     * @param  array<string, mixed>  $turnEconomy
     * @return array<string, mixed>
     */
    protected function calculateResourceAllocation(Character $character, array $turnEconomy): array
    {
        $turnsRemaining = $turnEconomy['turns_remaining'];
        $currentPhase = $turnEconomy['current_phase'];

        // Calculate allocation for different activities
        $allocation = [
            'training' => 0,
            'racing' => 0,
            'rest' => 0,
            'events' => 0,
        ];

        // Phase-specific allocation strategies
        $allocation = match ($currentPhase) {
            'junior' => [
                'training' => (int) ($turnsRemaining * 0.70),
                'racing' => (int) ($turnsRemaining * 0.15),
                'rest' => (int) ($turnsRemaining * 0.10),
                'events' => (int) ($turnsRemaining * 0.05),
            ],
            'classic' => [
                'training' => (int) ($turnsRemaining * 0.65),
                'racing' => (int) ($turnsRemaining * 0.20),
                'rest' => (int) ($turnsRemaining * 0.10),
                'events' => (int) ($turnsRemaining * 0.05),
            ],
            'senior' => [
                'training' => (int) ($turnsRemaining * 0.60),
                'racing' => (int) ($turnsRemaining * 0.25),
                'rest' => (int) ($turnsRemaining * 0.10),
                'events' => (int) ($turnsRemaining * 0.05),
            ],
            default => [
                'training' => (int) ($turnsRemaining * 0.65),
                'racing' => (int) ($turnsRemaining * 0.20),
                'rest' => (int) ($turnsRemaining * 0.10),
                'events' => (int) ($turnsRemaining * 0.05),
            ],
        };

        // Calculate allocation percentages
        $total = array_sum($allocation);
        $percentages = [];
        foreach ($allocation as $activity => $turns) {
            $percentages[$activity] = $total > 0 ? round(($turns / $total) * 100, 1) : 0;
        }

        return [
            'turn_allocation' => $allocation,
            'allocation_percentages' => $percentages,
            'priority_activities' => $this->getPriorityActivities($currentPhase),
        ];
    }

    /**
     * Get priority activities for current phase
     *
     * @return array<string>
     */
    protected function getPriorityActivities(string $phase): array
    {
        return match ($phase) {
            'junior' => ['training', 'foundation_building'],
            'classic' => ['training', 'racing', 'skill_acquisition'],
            'senior' => ['racing', 'optimization', 'final_adjustments'],
            default => ['training', 'balanced_development'],
        };
    }

    /**
     * Generate optimization recommendations
     *
     * @param  array<string, mixed>  $turnEconomy
     * @param  array<string, mixed>  $energyManagement
     * @param  array<string, mixed>  $resourceAllocation
     * @return array<string, string>
     */
    protected function generateOptimizationRecommendations(
        Character $character,
        array $turnEconomy,
        array $energyManagement,
        array $resourceAllocation
    ): array {
        $recommendations = [];

        // Turn economy recommendations
        $turnEfficiency = $turnEconomy['turn_efficiency'];
        if ($turnEfficiency < 0.7) {
            $recommendations['turn_efficiency'] = 'Turn efficiency is low. Focus on high-value training options.';
        } elseif ($turnEfficiency > 1.2) {
            $recommendations['turn_efficiency'] = 'Excellent turn efficiency! Maintain current strategy.';
        }

        // Energy management recommendations
        $energyStatus = $energyManagement['energy_status'];
        if ($energyStatus === 'critical' || $energyStatus === 'low') {
            $recommendations['energy'] = 'Energy is critically low. Rest immediately to avoid training failures.';
        } elseif ($energyStatus === 'moderate') {
            $recommendations['energy'] = 'Energy is moderate. Plan rest turns strategically.';
        }

        // Resource allocation recommendations
        $turnsRemaining = $turnEconomy['turns_remaining'];
        if ($turnsRemaining < 10) {
            $recommendations['urgency'] = 'Final turns approaching. Focus on critical stat gaps and race preparation.';
        } elseif ($turnsRemaining < 20) {
            $recommendations['urgency'] = 'Entering final phase. Prioritize optimization over experimentation.';
        }

        // Phase-specific recommendations
        $currentPhase = $turnEconomy['current_phase'];
        $recommendations['phase'] = match ($currentPhase) {
            'junior' => 'Foundation phase: Build base stats and establish training patterns.',
            'classic' => 'Development phase: Balance training with race participation.',
            'senior' => 'Optimization phase: Fine-tune stats and maximize race performance.',
            default => 'Continue balanced development.',
        };

        return $recommendations;
    }

    /**
     * Calculate overall efficiency score
     *
     * @param  array<string, mixed>  $turnEconomy
     * @param  array<string, mixed>  $energyManagement
     */
    protected function calculateEfficiencyScore(
        Character $character,
        array $turnEconomy,
        array $energyManagement
    ): float {
        $score = 0.0;

        // Turn efficiency component (40%)
        $turnEfficiency = $turnEconomy['turn_efficiency'];
        $score += $turnEfficiency * 0.4;

        // Energy management component (30%)
        $energyLevel = $energyManagement['current_energy'];
        $energyScore = $energyLevel / 100;
        $score += $energyScore * 0.3;

        // Mood component (20%)
        $moodImpact = $energyManagement['mood_impact'];
        $moodScore = ($moodImpact['multiplier'] - 0.8) / 0.4; // Normalize 0.8-1.2 to 0-1
        $score += $moodScore * 0.2;

        // Progress component (10%)
        $progressPercent = $turnEconomy['progress_percent'];
        $progressScore = min(1.0, $progressPercent / 100);
        $score += $progressScore * 0.1;

        return round(min(1.0, $score), 2);
    }
}
