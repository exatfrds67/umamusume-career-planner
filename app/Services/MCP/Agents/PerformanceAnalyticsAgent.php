<?php

namespace App\Services\MCP\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;

/**
 * Performance Analytics Agent
 *
 * Provides energy and mood management recommendations with performance tracking.
 * Analyzes training effectiveness, identifies patterns, and optimizes
 * character performance through data-driven insights.
 */
class PerformanceAnalyticsAgent
{
    protected MCPClientService $mcpClient;

    /**
     * Mood status effects on training
     *
     * @var array<string, array{multiplier: float, energy_cost_modifier: float, failure_risk_modifier: float}>
     */
    protected array $moodEffects = [
        'great' => ['multiplier' => 1.20, 'energy_cost_modifier' => 0.90, 'failure_risk_modifier' => 0.50],
        'good' => ['multiplier' => 1.10, 'energy_cost_modifier' => 0.95, 'failure_risk_modifier' => 0.75],
        'normal' => ['multiplier' => 1.00, 'energy_cost_modifier' => 1.00, 'failure_risk_modifier' => 1.00],
        'bad' => ['multiplier' => 0.90, 'energy_cost_modifier' => 1.05, 'failure_risk_modifier' => 1.25],
        'awful' => ['multiplier' => 0.80, 'energy_cost_modifier' => 1.10, 'failure_risk_modifier' => 1.50],
    ];

    /**
     * Condition effects on performance
     *
     * @var array<string, array{type: string, effect: string, impact: float}>
     */
    protected array $conditionEffects = [
        // Positive conditions
        'charming' => ['type' => 'positive', 'effect' => '+2 bond gain', 'impact' => 0.10],
        'sharp' => ['type' => 'positive', 'effect' => '-10% skill costs', 'impact' => 0.15],
        'practice_perfect' => ['type' => 'positive', 'effect' => '-2% failure rate', 'impact' => 0.08],
        // Negative conditions
        'practice_poor' => ['type' => 'negative', 'effect' => '+2% failure rate', 'impact' => -0.08],
        'migraine' => ['type' => 'negative', 'effect' => 'Mood resistance', 'impact' => -0.10],
        'dry_skin' => ['type' => 'negative', 'effect' => 'Motivation decrease', 'impact' => -0.12],
    ];

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
    }

    /**
     * Analyze character performance and provide recommendations
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     energy_analysis: array<string, mixed>,
     *     mood_analysis: array<string, mixed>,
     *     condition_analysis: array<string, mixed>,
     *     performance_metrics: array<string, mixed>,
     *     recommendations: array<string, string>,
     *     optimization_score: float
     * }
     */
    public function analyzePerformance(): array
        // Analyze energy state
        $energyAnalysis = $this->analyzeEnergyState($character);

        // Analyze mood state
        $moodAnalysis = $this->analyzeMoodState($character);

        // Analyze active conditions
        $conditionAnalysis = $this->analyzeConditions($character, $context);

        // Calculate performance metrics
        $performanceMetrics = $this->calculatePerformanceMetrics(
            $character,
            $energyAnalysis,
            $moodAnalysis,
            $conditionAnalysis
        );

        // Generate recommendations
        $recommendations = $this->generatePerformanceRecommendations(
            $character,
            $energyAnalysis,
            $moodAnalysis,
            $conditionAnalysis,
            $performanceMetrics
        );

        // Calculate optimization score
        $optimizationScore = $this->calculateOptimizationScore(
            $energyAnalysis,
            $moodAnalysis,
            $performanceMetrics
        );

        return [
            'energy_analysis' => $energyAnalysis,
            'mood_analysis' => $moodAnalysis,
            'condition_analysis' => $conditionAnalysis,
            'performance_metrics' => $performanceMetrics,
            'recommendations' => $recommendations,
            'optimization_score' => $optimizationScore,
        ];
    }

    /**
     * Analyze energy state and management
     *
     * @return array<string, mixed>
     */
    protected function analyzeEnergyState(): array
        $energyLevel = $character->energy_level ?? 100;

        // Determine energy status
        $status = $this->getEnergyStatus($energyLevel);

        // Calculate training capacity
        $trainingCapacity = $this->calculateTrainingCapacity($energyLevel);

        // Calculate failure risk
        $failureRisk = $this->calculateFailureRisk($energyLevel);

        // Determine recovery strategy
        $recoveryStrategy = $this->determineRecoveryStrategy($energyLevel);

        // Calculate optimal energy range
        $optimalRange = $this->getOptimalEnergyRange();

        return [
            'current_energy' => $energyLevel,
            'status' => $status,
            'training_capacity' => $trainingCapacity,
            'failure_risk' => $failureRisk,
            'recovery_strategy' => $recoveryStrategy,
            'optimal_range' => $optimalRange,
            'is_optimal' => $energyLevel >= $optimalRange['min'] && $energyLevel <= $optimalRange['max'],
        ];
    }

    /**
     * Get energy status category
     */
    protected function getEnergyStatus(int $energyLevel): string
    {
        return match (true) {
            $energyLevel >= 80 => 'excellent',
            $energyLevel >= 60 => 'good',
            $energyLevel >= 40 => 'moderate',
            $energyLevel >= 20 => 'low',
            default => 'critical',
        };
    }

    /**
     * Calculate training capacity based on energy
     *
     * @return array{trainings_available: int, max_intensity: string}
     */
    protected function calculateTrainingCapacity(): array
        // Each training costs ~20 energy
        $trainingsAvailable = (int) floor($energyLevel / 20);

        // Determine max intensity
        $maxIntensity = match (true) {
            $energyLevel >= 80 => 'high',
            $energyLevel >= 60 => 'medium',
            $energyLevel >= 40 => 'low',
            default => 'rest_only',
        };

        return [
            'trainings_available' => $trainingsAvailable,
            'max_intensity' => $maxIntensity,
        ];
    }

    /**
     * Calculate failure risk based on energy
     */
    protected function calculateFailureRisk(int $energyLevel): float
    {
        return match (true) {
            $energyLevel >= 70 => 0.0,
            $energyLevel >= 50 => 0.05,
            $energyLevel >= 30 => 0.15,
            $energyLevel >= 10 => 0.30,
            default => 0.50,
        };
    }

    /**
     * Determine recovery strategy
     *
     * @return array{action: string, turns_needed: int, priority: string}
     */
    protected function determineRecoveryStrategy(): array
        return match (true) {
            $energyLevel < 20 => [
                'action' => 'immediate_rest',
                'turns_needed' => 2,
                'priority' => 'critical',
            ],
            $energyLevel < 40 => [
                'action' => 'rest_soon',
                'turns_needed' => 1,
                'priority' => 'high',
            ],
            $energyLevel < 60 => [
                'action' => 'plan_rest',
                'turns_needed' => 1,
                'priority' => 'medium',
            ],
            default => [
                'action' => 'no_rest_needed',
                'turns_needed' => 0,
                'priority' => 'low',
            ],
        };
    }

    /**
     * Get optimal energy range
     *
     * @return array{min: int, max: int}
     */
    protected function getOptimalEnergyRange(): array
        return ['min' => 60, 'max' => 100];
    }

    /**
     * Analyze mood state and effects
     *
     * @return array<string, mixed>
     */
    protected function analyzeMoodState(): array
        $moodStatus = $character->mood_status ?? 'normal';

        // Get mood effects
        $effects = $this->moodEffects[$moodStatus] ?? $this->moodEffects['normal'];

        // Calculate mood impact on training
        $trainingImpact = $this->calculateMoodTrainingImpact($effects);

        // Determine mood improvement strategy
        $improvementStrategy = $this->determineMoodImprovementStrategy($moodStatus);

        // Calculate mood stability
        $stability = $this->calculateMoodStability($moodStatus);

        return [
            'current_mood' => $moodStatus,
            'effects' => $effects,
            'training_impact' => $trainingImpact,
            'improvement_strategy' => $improvementStrategy,
            'stability' => $stability,
            'is_optimal' => $moodStatus === 'great' || $moodStatus === 'good',
        ];
    }

    /**
     * Calculate mood impact on training
     *
     * @param  array{multiplier: float, energy_cost_modifier: float, failure_risk_modifier: float}  $effects
     * @return array<string, mixed>
     */
    protected function calculateMoodTrainingImpact(): array
        $multiplier = $effects['multiplier'];
        $impactPercent = ($multiplier - 1.0) * 100;

        return [
            'stat_gain_modifier' => $multiplier,
            'impact_percent' => round($impactPercent, 1),
            'energy_cost_modifier' => $effects['energy_cost_modifier'],
            'failure_risk_modifier' => $effects['failure_risk_modifier'],
            'description' => $this->getMoodImpactDescription($impactPercent),
        ];
    }

    /**
     * Get mood impact description
     */
    protected function getMoodImpactDescription(float $impactPercent): string
    {
        return match (true) {
            $impactPercent >= 15 => 'Significantly increased training effectiveness',
            $impactPercent >= 5 => 'Moderately increased training effectiveness',
            $impactPercent > -5 => 'Normal training effectiveness',
            $impactPercent > -15 => 'Moderately decreased training effectiveness',
            default => 'Significantly decreased training effectiveness',
        };
    }

    /**
     * Determine mood improvement strategy
     *
     * @return array{action: string, priority: string, methods: array<string>}
     */
    protected function determineMoodImprovementStrategy(): array
        return match ($moodStatus) {
            'awful' => [
                'action' => 'urgent_improvement',
                'priority' => 'critical',
                'methods' => ['rest', 'events', 'mood_items'],
            ],
            'bad' => [
                'action' => 'improvement_recommended',
                'priority' => 'high',
                'methods' => ['rest', 'events'],
            ],
            'normal' => [
                'action' => 'maintain_or_improve',
                'priority' => 'medium',
                'methods' => ['positive_events', 'success_in_races'],
            ],
            'good', 'great' => [
                'action' => 'maintain',
                'priority' => 'low',
                'methods' => ['continue_current_strategy'],
            ],
            default => [
                'action' => 'monitor',
                'priority' => 'medium',
                'methods' => ['balanced_approach'],
            ],
        };
    }

    /**
     * Calculate mood stability
     */
    protected function calculateMoodStability(string $moodStatus): string
    {
        return match ($moodStatus) {
            'great' => 'stable_positive',
            'good' => 'stable',
            'normal' => 'neutral',
            'bad' => 'unstable',
            'awful' => 'very_unstable',
            default => 'unknown',
        };
    }

    /**
     * Analyze active conditions
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function analyzeConditions(): array
        $activeConditions = $context['active_conditions'] ?? [];

        $positiveConditions = [];
        $negativeConditions = [];
        $totalImpact = 0.0;

        foreach ($activeConditions as $condition) {
            $conditionData = $this->conditionEffects[$condition] ?? null;

            if ($conditionData) {
                if ($conditionData['type'] === 'positive') {
                    $positiveConditions[] = [
                        'name' => $condition,
                        'effect' => $conditionData['effect'],
                        'impact' => $conditionData['impact'],
                    ];
                } else {
                    $negativeConditions[] = [
                        'name' => $condition,
                        'effect' => $conditionData['effect'],
                        'impact' => $conditionData['impact'],
                    ];
                }

                $totalImpact = ($totalImpact ?? 0) + $conditionData['impact'];
            }
        }

        return [
            'active_conditions' => $activeConditions,
            'positive_conditions' => $positiveConditions,
            'negative_conditions' => $negativeConditions,
            'total_impact' => round($totalImpact, 2),
            'net_effect' => $this->getNetConditionEffect($totalImpact),
        ];
    }

    /**
     * Get net condition effect description
     */
    protected function getNetConditionEffect(float $totalImpact): string
    {
        return match (true) {
            $totalImpact >= 0.15 => 'strongly_positive',
            $totalImpact >= 0.05 => 'positive',
            $totalImpact > -0.05 => 'neutral',
            $totalImpact > -0.15 => 'negative',
            default => 'strongly_negative',
        };
    }

    /**
     * Calculate performance metrics
     *
     * @param  array<string, mixed>  $energyAnalysis
     * @param  array<string, mixed>  $moodAnalysis
     * @param  array<string, mixed>  $conditionAnalysis
     * @return array<string, mixed>
     */
    protected function calculatePerformanceMetrics(): array
        // Calculate overall training effectiveness
        $baseEffectiveness = 1.0;
        $moodMultiplier = $moodAnalysis['effects']['multiplier'];
        $conditionImpact = $conditionAnalysis['total_impact'];
        $energyModifier = $this->getEnergyEffectivenessModifier($energyAnalysis['current_energy']);

        $overallEffectiveness = $baseEffectiveness * $moodMultiplier * (1 + $conditionImpact) * $energyModifier;

        // Calculate risk factors
        $riskFactors = $this->calculateRiskFactors($energyAnalysis, $moodAnalysis, $conditionAnalysis);

        // Calculate optimization potential
        $optimizationPotential = $this->calculateOptimizationPotential(
            $energyAnalysis,
            $moodAnalysis,
            $conditionAnalysis
        );

        return [
            'overall_effectiveness' => round($overallEffectiveness, 2),
            'effectiveness_percent' => round(($overallEffectiveness - 1.0) * 100, 1),
            'risk_factors' => $riskFactors,
            'optimization_potential' => $optimizationPotential,
            'performance_rating' => $this->getPerformanceRating($overallEffectiveness),
        ];
    }

    /**
     * Get energy effectiveness modifier
     */
    protected function getEnergyEffectivenessModifier(int $energyLevel): float
    {
        return match (true) {
            $energyLevel >= 80 => 1.0,
            $energyLevel >= 60 => 0.95,
            $energyLevel >= 40 => 0.85,
            $energyLevel >= 20 => 0.70,
            default => 0.50,
        };
    }

    /**
     * Calculate risk factors
     *
     * @param  array<string, mixed>  $energyAnalysis
     * @param  array<string, mixed>  $moodAnalysis
     * @param  array<string, mixed>  $conditionAnalysis
     * @return array<string, mixed>
     */
    protected function calculateRiskFactors(): array
        $risks = [];

        // Energy risk
        if ($energyAnalysis['failure_risk'] > 0.15) {
            $risks[] = [
                'type' => 'energy',
                'severity' => 'high',
                'description' => 'High training failure risk due to low energy',
            ];
        }

        // Mood risk
        if ($moodAnalysis['current_mood'] === 'awful' || $moodAnalysis['current_mood'] === 'bad') {
            $risks[] = [
                'type' => 'mood',
                'severity' => 'medium',
                'description' => 'Reduced training effectiveness due to poor mood',
            ];
        }

        // Condition risk
        if (count($conditionAnalysis['negative_conditions']) > 0) {
            $risks[] = [
                'type' => 'conditions',
                'severity' => 'medium',
                'description' => 'Negative conditions affecting performance',
            ];
        }

        return [
            'risks' => $risks,
            'risk_count' => count($risks),
            'overall_risk_level' => $this->getOverallRiskLevel(count($risks)),
        ];
    }

    /**
     * Get overall risk level
     */
    protected function getOverallRiskLevel(int $riskCount): string
    {
        return match (true) {
            $riskCount >= 3 => 'critical',
            $riskCount >= 2 => 'high',
            $riskCount >= 1 => 'medium',
            default => 'low',
        };
    }

    /**
     * Calculate optimization potential
     *
     * @param  array<string, mixed>  $energyAnalysis
     * @param  array<string, mixed>  $moodAnalysis
     * @param  array<string, mixed>  $conditionAnalysis
     * @return array<string, mixed>
     */
    protected function calculateOptimizationPotential(): array
        $improvements = [];

        // Energy optimization
        if (! $energyAnalysis['is_optimal']) {
            $improvements['energy'] = [
                'current' => $energyAnalysis['current_energy'],
                'optimal' => $energyAnalysis['optimal_range'],
                'potential_gain' => 0.15,
            ];
        }

        // Mood optimization
        if (! $moodAnalysis['is_optimal']) {
            $improvements['mood'] = [
                'current' => $moodAnalysis['current_mood'],
                'optimal' => 'great',
                'potential_gain' => 0.20,
            ];
        }

        // Condition optimization
        if (count($conditionAnalysis['negative_conditions']) > 0) {
            $improvements['conditions'] = [
                'negative_count' => count($conditionAnalysis['negative_conditions']),
                'potential_gain' => 0.10,
            ];
        }

        $totalPotential = array_sum(array_column($improvements, 'potential_gain'));

        return [
            'improvements' => $improvements,
            'total_potential_gain' => round($totalPotential, 2),
            'optimization_priority' => $this->getOptimizationPriority($totalPotential),
        ];
    }

    /**
     * Get optimization priority
     */
    protected function getOptimizationPriority(float $totalPotential): string
    {
        return match (true) {
            $totalPotential >= 0.30 => 'critical',
            $totalPotential >= 0.15 => 'high',
            $totalPotential >= 0.05 => 'medium',
            default => 'low',
        };
    }

    /**
     * Get performance rating
     */
    protected function getPerformanceRating(float $effectiveness): string
    {
        return match (true) {
            $effectiveness >= 1.15 => 'excellent',
            $effectiveness >= 1.05 => 'good',
            $effectiveness >= 0.95 => 'average',
            $effectiveness >= 0.85 => 'below_average',
            default => 'poor',
        };
    }

    /**
     * Generate performance recommendations
     *
     * @param  array<string, mixed>  $energyAnalysis
     * @param  array<string, mixed>  $moodAnalysis
     * @param  array<string, mixed>  $conditionAnalysis
     * @param  array<string, mixed>  $performanceMetrics
     * @return array<string, string>
     */
    protected function generatePerformanceRecommendations(): array
        $recommendations = [];

        // Energy recommendations
        $energyStatus = $energyAnalysis['status'];
        if ($energyStatus === 'critical' || $energyStatus === 'low') {
            $recommendations['energy'] = 'URGENT: Rest immediately. Energy is too low for safe training.';
        } elseif ($energyStatus === 'moderate') {
            $recommendations['energy'] = 'Plan rest within next 1-2 turns to maintain optimal energy levels.';
        } elseif ($energyStatus === 'good') {
            $recommendations['energy'] = 'Energy levels are good. Continue current training pace.';
        }

        // Mood recommendations
        $moodStatus = $moodAnalysis['current_mood'];
        if ($moodStatus === 'awful' || $moodStatus === 'bad') {
            $recommendations['mood'] = 'Poor mood significantly reduces training effectiveness. Prioritize mood improvement.';
        } elseif ($moodStatus === 'great') {
            $recommendations['mood'] = 'Excellent mood! This is the optimal time for intensive training.';
        }

        // Condition recommendations
        if (count($conditionAnalysis['negative_conditions']) > 0) {
            $recommendations['conditions'] = 'Negative conditions active. Consider waiting or using items to remove them.';
        } elseif (count($conditionAnalysis['positive_conditions']) > 0) {
            $recommendations['conditions'] = 'Positive conditions active! Take advantage of enhanced training effectiveness.';
        }

        // Performance optimization recommendations
        $optimizationPotential = $performanceMetrics['optimization_potential'];
        if ($optimizationPotential['total_potential_gain'] > 0.20) {
            $recommendations['optimization'] = 'Significant optimization potential available. Address energy, mood, and conditions for maximum effectiveness.';
        }

        // Overall strategy recommendation
        $effectiveness = $performanceMetrics['overall_effectiveness'];
        if ($effectiveness >= 1.15) {
            $recommendations['strategy'] = 'Performance is excellent! This is an ideal time for high-priority training.';
        } elseif ($effectiveness < 0.90) {
            $recommendations['strategy'] = 'Performance is suboptimal. Focus on recovery and state management before intensive training.';
        }

        return $recommendations;
    }

    /**
     * Calculate optimization score
     *
     * @param  array<string, mixed>  $energyAnalysis
     * @param  array<string, mixed>  $moodAnalysis
     * @param  array<string, mixed>  $performanceMetrics
     */
    protected function calculateOptimizationScore(
        array $energyAnalysis,
        array $moodAnalysis,
        array $performanceMetrics
    ): float {
        $score = 0.0;

        // Energy component (40%)
        $energyScore = $energyAnalysis['current_energy'] / 100;
        $score = ($score ?? 0) + $energyScore * 0.4;

        // Mood component (30%)
        $moodMultiplier = $moodAnalysis['effects']['multiplier'];
        $moodScore = ($moodMultiplier - 0.8) / 0.4; // Normalize 0.8-1.2 to 0-1
        $score = ($score ?? 0) + $moodScore * 0.3;

        // Performance component (30%)
        $effectiveness = $performanceMetrics['overall_effectiveness'];
        $performanceScore = min(1.0, $effectiveness / 1.2); // Normalize with 1.2 as max
        $score = ($score ?? 0) + $performanceScore * 0.3;

        return round(min(1.0, $score), 2);
    }
}
