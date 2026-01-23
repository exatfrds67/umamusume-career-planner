<?php

namespace App\Services\MCP\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use App\Services\SkillEvolutionService;
use Illuminate\Support\Collection;

/**
 * Long-term Development Agent
 *
 * Provides skill roadmaps with milestone tracking for long-term character development.
 * Analyzes career progression, skill acquisition timing, and strategic milestones
 * to create comprehensive development plans that optimize character growth over time.
 */
class LongTermDevelopmentAgent
{
    protected MCPClientService $mcpClient;

    protected SkillEvolutionService $evolutionService;

    /**
     * Career stages and their typical characteristics
     *
     * @var array<string, array{turns: int, sp_range: array{min: int, max: int}, priority: string}>
     */
    protected array $careerStages = [
        'junior' => [
            'turns' => 24,
            'sp_range' => ['min' => 0, 'max' => 400],
            'priority' => 'foundation_building',
        ],
        'classic' => [
            'turns' => 24,
            'sp_range' => ['min' => 400, 'max' => 800],
            'priority' => 'skill_acquisition',
        ],
        'senior' => [
            'turns' => 24,
            'sp_range' => ['min' => 800, 'max' => 1200],
            'priority' => 'optimization',
        ],
    ];

    /**
     * Milestone types and their importance
     *
     * @var array<string, int>
     */
    protected array $milestoneWeights = [
        'stat_breakpoint' => 10,
        'skill_evolution' => 8,
        'max_hint_discount' => 7,
        'race_qualification' => 6,
        'sp_threshold' => 5,
    ];

    public function __construct(MCPClientService $mcpClient, SkillEvolutionService $evolutionService)
    {
        $this->mcpClient = $mcpClient;
        $this->evolutionService = $evolutionService;
    }

    /**
     * Analyze long-term development and provide comprehensive roadmap
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     development_roadmap: array<string, mixed>,
     *     milestone_tracking: array<string, mixed>,
     *     phase_planning: array<string, mixed>,
     *     risk_assessment: array<string, mixed>,
     *     recommendations: array<string, string>,
     *     confidence: float
     * }
     */
    public function analyzeLongTermDevelopment(): array
        // Create development roadmap
        $developmentRoadmap = $this->createDevelopmentRoadmap($character, $targetSkills, $goals);

        // Track milestones
        $milestoneTracking = $this->trackMilestones($character, $targetSkills, $goals);

        // Plan development phases
        $phasePlanning = $this->planDevelopmentPhases($character, $developmentRoadmap, $milestoneTracking);

        // Assess risks
        $riskAssessment = $this->assessRisks($character, $developmentRoadmap, $context);

        // Generate recommendations
        $recommendations = $this->generateRecommendations(
            $developmentRoadmap,
            $milestoneTracking,
            $riskAssessment
        );

        // Calculate confidence score
        $confidence = $this->calculateConfidence($developmentRoadmap, $riskAssessment);

        return [
            'development_roadmap' => $developmentRoadmap,
            'milestone_tracking' => $milestoneTracking,
            'phase_planning' => $phasePlanning,
            'risk_assessment' => $riskAssessment,
            'recommendations' => $recommendations,
            'confidence' => $confidence,
        ];
    }

    /**
     * Create comprehensive development roadmap
     *
     * @param  array<string, mixed>  $goals
     * @return array<string, mixed>
     */
    protected function createDevelopmentRoadmap(): array
        // Determine current career stage
        $currentStage = $character->career_stage ?? 'junior';
        $stageInfo = $this->careerStages[$currentStage] ?? $this->careerStages['junior'];

        // Calculate remaining turns
        $turnsRemaining = $this->calculateRemainingTurns($character, $currentStage);

        // Create skill acquisition timeline
        $skillTimeline = $this->createSkillTimeline($character, $targetSkills, $turnsRemaining);

        // Create stat development timeline
        $statTimeline = $this->createStatTimeline($character, $goals, $turnsRemaining);

        // Identify critical decision points
        $decisionPoints = $this->identifyCriticalDecisionPoints($skillTimeline, $statTimeline);

        return [
            'current_stage' => $currentStage,
            'turns_remaining' => $turnsRemaining,
            'skill_timeline' => $skillTimeline,
            'stat_timeline' => $statTimeline,
            'decision_points' => $decisionPoints,
            'estimated_completion' => $this->estimateCompletionTurn($skillTimeline, $statTimeline),
        ];
    }

    /**
     * Calculate remaining turns in career
     */
    protected function calculateRemainingTurns(Character $character, string $currentStage): int
    {
        $stageInfo = $this->careerStages[$currentStage] ?? $this->careerStages['junior'];
        $totalTurns = $stageInfo['turns'];

        // Estimate current turn (simplified)
        $currentTurn = $character->current_turn ?? 0;

        return max(0, $totalTurns - $currentTurn);
    }

    /**
     * Create skill acquisition timeline
     *
     * @return array<string, mixed>
     */
    protected function createSkillTimeline(): array
        $timeline = [];
        $currentTurn = 1;

        // Sort skills by priority
        $sortedSkills = $this->prioritizeSkills($character, $targetSkills);

        foreach ($sortedSkills as $skill) {
            if ($currentTurn > $turnsRemaining) {
                break;
            }

            // Estimate turns needed for hint collection
            $hintsNeeded = $this->calculateHintsNeeded($character, $skill);
            $hintCollectionTurns = $hintsNeeded * 2; // Estimate 2 turns per hint

            // Add to timeline
            $timeline[] = [
                'turn_range' => [$currentTurn, $currentTurn + $hintCollectionTurns],
                'action' => 'hint_collection',
                'skill_name' => $skill->name,
                'hints_needed' => $hintsNeeded,
            ];

            $currentTurn = ($currentTurn ?? 0) + $hintCollectionTurns;

            // Add acquisition turn
            $timeline[] = [
                'turn' => $currentTurn,
                'action' => 'skill_acquisition',
                'skill_name' => $skill->name,
                'estimated_cost' => $this->estimateSkillCost($character, $skill),
            ];

            $currentTurn = ($currentTurn ?? 0) + 1;
        }

        return [
            'timeline' => $timeline,
            'total_turns_needed' => $currentTurn - 1,
            'feasibility' => ($currentTurn - 1) <= $turnsRemaining ? 'feasible' : 'requires_adjustment',
        ];
    }

    /**
     * Prioritize skills for acquisition
     */
    protected function prioritizeSkills(Character $character, Collection $targetSkills): Collection
    {
        return $targetSkills->sortByDesc(function ($skill) use ($character) {
            $score = 0;

            // Priority for skills with hints
            $hints = $character->skillHints()->where('skill_id', $skill->id)->count();
            $score = ($score ?? 0) + $hints * 50;

            // Priority for high-cost skills
            $score = ($score ?? 0) + $skill->base_sp_cost / 10;

            // Priority for meta-tier skills
            $metaTier = $skill->meta_tier ?? 'C';
            $score = ($score ?? 0) + match ($metaTier) {
                'SS' => 100,
                'S' => 80,
                'A' => 60,
                default => 0,
            };

            return $score;
        });
    }

    /**
     * Calculate hints needed for skill
     */
    protected function calculateHintsNeeded(Character $character, object $skill): int
    {
        $currentHints = $character->skillHints()->where('skill_id', $skill->id)->count();

        return max(0, 2 - $currentHints); // Need 2 hints for max discount
    }

    /**
     * Estimate skill cost after hint collection
     */
    protected function estimateSkillCost(Character $character, object $skill): int
    {
        $currentHints = $character->skillHints()->where('skill_id', $skill->id)->count();
        $hintsAfterCollection = min(2, $currentHints + $this->calculateHintsNeeded($character, $skill));

        $discount = min(0.40, $hintsAfterCollection * 0.20);

        return (int) ($skill->base_sp_cost * (1 - $discount));
    }

    /**
     * Create stat development timeline
     *
     * @param  array<string, mixed>  $goals
     * @return array<string, mixed>
     */
    protected function createStatTimeline(): array
        $currentStats = $character->current_stats ?? [];
        $targetStats = $goals['target_stats'] ?? [];

        $timeline = [];

        foreach ($targetStats as $stat => $target) {
            $current = $currentStats[$stat] ?? 0;
            $gap = max(0, $target - $current);

            if ($gap === 0) {
                continue;
            }

            // Estimate turns needed (assuming ~20 points per turn)
            $turnsNeeded = ceil($gap / 20);

            // Identify breakpoints
            $breakpoints = [];
            if ($current < 900 && $target >= 900) {
                $breakpoints[] = ['value' => 900, 'turn' => ceil((900 - $current) / 20)];
            }
            if ($current < 1200 && $target >= 1200) {
                $breakpoints[] = ['value' => 1200, 'turn' => ceil((1200 - $current) / 20)];
            }

            $timeline[] = [
                'stat' => $stat,
                'current' => $current,
                'target' => $target,
                'gap' => $gap,
                'turns_needed' => $turnsNeeded,
                'breakpoints' => $breakpoints,
            ];
        }

        return [
            'timeline' => $timeline,
            'total_turns_needed' => max(array_column($timeline, 'turns_needed')),
            'feasibility' => max(array_column($timeline, 'turns_needed')) <= $turnsRemaining ? 'feasible' : 'requires_adjustment',
        ];
    }

    /**
     * Identify critical decision points
     *
     * @param  array<string, mixed>  $skillTimeline
     * @param  array<string, mixed>  $statTimeline
     * @return array<string, mixed>
     */
    protected function identifyCriticalDecisionPoints(): array
        $decisionPoints = [];

        // Add stat breakpoint decisions
        foreach ($statTimeline['timeline'] as $statPlan) {
            foreach ($statPlan['breakpoints'] as $breakpoint) {
                $decisionPoints[] = [
                    'turn' => $breakpoint['turn'],
                    'type' => 'stat_breakpoint',
                    'description' => "Reach {$statPlan['stat']} {$breakpoint['value']} breakpoint",
                    'importance' => 'high',
                ];
            }
        }

        // Add skill acquisition decisions
        foreach ($skillTimeline['timeline'] as $event) {
            if ($event['action'] === 'skill_acquisition') {
                $decisionPoints[] = [
                    'turn' => $event['turn'],
                    'type' => 'skill_acquisition',
                    'description' => "Acquire {$event['skill_name']}",
                    'importance' => 'medium',
                ];
            }
        }

        // Sort by turn
        usort($decisionPoints, fn ($a, $b) => $a['turn'] <=> $b['turn']);

        return $decisionPoints;
    }

    /**
     * Estimate completion turn
     *
     * @param  array<string, mixed>  $skillTimeline
     * @param  array<string, mixed>  $statTimeline
     */
    protected function estimateCompletionTurn(array $skillTimeline, array $statTimeline): int
    {
        $skillCompletion = $skillTimeline['total_turns_needed'];
        $statCompletion = $statTimeline['total_turns_needed'];

        return max($skillCompletion, $statCompletion);
    }

    /**
     * Track milestones
     *
     * @param  array<string, mixed>  $goals
     * @return array<string, mixed>
     */
    protected function trackMilestones(): array
        $milestones = [];

        // Stat milestones
        $statMilestones = $this->trackStatMilestones($character, $goals);
        $milestones = array_merge($milestones, $statMilestones);

        // Skill milestones
        $skillMilestones = $this->trackSkillMilestones($character, $targetSkills);
        $milestones = array_merge($milestones, $skillMilestones);

        // Calculate overall progress
        $overallProgress = $this->calculateOverallProgress($milestones);

        return [
            'milestones' => $milestones,
            'completed_count' => count(array_filter($milestones, fn ($m) => $m['completed'])),
            'total_count' => count($milestones),
            'overall_progress' => $overallProgress,
        ];
    }

    /**
     * Track stat milestones
     *
     * @param  array<string, mixed>  $goals
     * @return array<string, mixed>
     */
    protected function trackStatMilestones(): array
        $milestones = [];
        $currentStats = $character->current_stats ?? [];
        $targetStats = $goals['target_stats'] ?? [];

        foreach ($targetStats as $stat => $target) {
            $current = $currentStats[$stat] ?? 0;

            // 900 breakpoint milestone
            if ($target >= 900) {
                $milestones[] = [
                    'type' => 'stat_breakpoint',
                    'description' => "{$stat} reaches 900",
                    'completed' => $current >= 900,
                    'progress' => min(100, ($current / 900) * 100),
                    'importance' => 'high',
                ];
            }

            // 1200 breakpoint milestone
            if ($target >= 1200) {
                $milestones[] = [
                    'type' => 'stat_breakpoint',
                    'description' => "{$stat} reaches 1200",
                    'completed' => $current >= 1200,
                    'progress' => min(100, ($current / 1200) * 100),
                    'importance' => 'high',
                ];
            }

            // Target milestone
            $milestones[] = [
                'type' => 'stat_target',
                'description' => "{$stat} reaches target ({$target})",
                'completed' => $current >= $target,
                'progress' => $target > 0 ? min(100, ($current / $target) * 100) : 100,
                'importance' => 'medium',
            ];
        }

        return $milestones;
    }

    /**
     * Track skill milestones
     *
     * @return array<string, mixed>
     */
    protected function trackSkillMilestones(): array
        $milestones = [];

        foreach ($targetSkills as $skill) {
            $hints = $character->skillHints()->where('skill_id', $skill->id)->count();
            $acquired = $character->skills()->where('skill_id', $skill->id)->exists();

            // Hint collection milestone
            $milestones[] = [
                'type' => 'max_hint_discount',
                'description' => "Collect 2 hints for {$skill->name}",
                'completed' => $hints >= 2,
                'progress' => ($hints / 2) * 100,
                'importance' => 'medium',
            ];

            // Skill acquisition milestone
            $milestones[] = [
                'type' => 'skill_acquisition',
                'description' => "Acquire {$skill->name}",
                'completed' => $acquired,
                'progress' => $acquired ? 100 : 0,
                'importance' => 'medium',
            ];
        }

        return $milestones;
    }

    /**
     * Calculate overall progress
     *
     * @param  array<string, mixed>  $milestones
     */
    protected function calculateOverallProgress(array $milestones): float
    {
        if (empty($milestones)) {
            return 0.0;
        }

        $totalProgress = 0;
        foreach ($milestones as $milestone) {
            $totalProgress = ($totalProgress ?? 0) + $milestone['progress'];
        }

        return round($totalProgress / count($milestones), 1);
    }

    /**
     * Plan development phases
     *
     * @param  array<string, mixed>  $developmentRoadmap
     * @param  array<string, mixed>  $milestoneTracking
     * @return array<string, mixed>
     */
    protected function planDevelopmentPhases(): array
        $currentStage = $developmentRoadmap['current_stage'];
        $turnsRemaining = $developmentRoadmap['turns_remaining'];

        // Divide remaining turns into phases
        $phases = $this->divideTurnsIntoPhases($turnsRemaining);

        // Assign milestones to phases
        $phasePlans = $this->assignMilestonesToPhases($phases, $milestoneTracking);

        return [
            'phases' => $phasePlans,
            'current_phase' => $this->determineCurrentPhase($phases, $milestoneTracking),
        ];
    }

    /**
     * Divide turns into development phases
     *
     * @return array<string, array{start: int, end: int, focus: string}>
     */
    protected function divideTurnsIntoPhases(): array
        $phaseLength = ceil($turnsRemaining / 3);

        return [
            'early' => [
                'start' => 1,
                'end' => $phaseLength,
                'focus' => 'foundation_and_hint_collection',
            ],
            'mid' => [
                'start' => $phaseLength + 1,
                'end' => $phaseLength * 2,
                'focus' => 'skill_acquisition_and_stat_building',
            ],
            'late' => [
                'start' => ($phaseLength * 2) + 1,
                'end' => $turnsRemaining,
                'focus' => 'optimization_and_final_push',
            ],
        ];
    }

    /**
     * Assign milestones to phases
     *
     * @param  array<string, array{start: int, end: int, focus: string}>  $phases
     * @param  array<string, mixed>  $milestoneTracking
     * @return array<string, mixed>
     */
    protected function assignMilestonesToPhases(): array
        $phasePlans = [];

        foreach ($phases as $phaseName => $phaseInfo) {
            $phaseMilestones = array_filter(
                $milestoneTracking['milestones'],
                fn ($m) => ! $m['completed']
            );

            $phasePlans[$phaseName] = [
                ...$phaseInfo,
                'milestones' => array_slice($phaseMilestones, 0, 3),
                'priority' => $this->determinePhasePriority($phaseName),
            ];
        }

        return $phasePlans;
    }

    /**
     * Determine phase priority
     */
    protected function determinePhasePriority(string $phaseName): string
    {
        return match ($phaseName) {
            'early' => 'hint_collection',
            'mid' => 'skill_acquisition',
            'late' => 'optimization',
            default => 'balanced',
        };
    }

    /**
     * Determine current phase
     *
     * @param  array<string, array{start: int, end: int, focus: string}>  $phases
     * @param  array<string, mixed>  $milestoneTracking
     */
    protected function determineCurrentPhase(array $phases, array $milestoneTracking): string
    {
        $progress = $milestoneTracking['overall_progress'];

        return match (true) {
            $progress < 33 => 'early',
            $progress < 66 => 'mid',
            default => 'late',
        };
    }

    /**
     * Assess development risks
     *
     * @param  array<string, mixed>  $developmentRoadmap
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function assessRisks(): array
        $risks = [];

        // Time constraint risk
        $skillFeasibility = $developmentRoadmap['skill_timeline']['feasibility'];
        $statFeasibility = $developmentRoadmap['stat_timeline']['feasibility'];

        if ($skillFeasibility !== 'feasible' || $statFeasibility !== 'feasible') {
            $risks[] = [
                'type' => 'time_constraint',
                'severity' => 'high',
                'description' => 'Insufficient turns to complete all planned development',
                'mitigation' => 'Prioritize critical skills and stats, defer optional goals',
            ];
        }

        // SP budget risk
        $currentSP = $character->current_sp ?? 0;
        $estimatedCost = $this->estimateTotalSkillCost($character, $developmentRoadmap);

        if ($currentSP < $estimatedCost * 0.5) {
            $risks[] = [
                'type' => 'sp_budget',
                'severity' => 'medium',
                'description' => 'SP budget may be insufficient for all planned skills',
                'mitigation' => 'Maximize hint collection, prioritize high-value skills',
            ];
        }

        // Energy management risk
        $energyLevel = $character->energy_level ?? 100;
        if ($energyLevel < 50) {
            $risks[] = [
                'type' => 'energy_management',
                'severity' => 'low',
                'description' => 'Current energy level requires attention',
                'mitigation' => 'Schedule rest periods, avoid high-risk training',
            ];
        }

        return [
            'risks' => $risks,
            'risk_count' => count($risks),
            'highest_severity' => $this->getHighestSeverity($risks),
        ];
    }

    /**
     * Estimate total skill cost
     *
     * @param  array<string, mixed>  $developmentRoadmap
     */
    protected function estimateTotalSkillCost(Character $character, array $developmentRoadmap): int
    {
        $totalCost = 0;

        foreach ($developmentRoadmap['skill_timeline']['timeline'] as $event) {
            if ($event['action'] === 'skill_acquisition') {
                $totalCost = ($totalCost ?? 0) + $event['estimated_cost'];
            }
        }

        return $totalCost;
    }

    /**
     * Get highest severity from risks
     *
     * @param  array<string, mixed>  $risks
     */
    protected function getHighestSeverity(array $risks): string
    {
        if (empty($risks)) {
            return 'none';
        }

        $severities = array_column($risks, 'severity');

        if (in_array('high', $severities)) {
            return 'high';
        }

        if (in_array('medium', $severities)) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Generate long-term recommendations
     *
     * @param  array<string, mixed>  $developmentRoadmap
     * @param  array<string, mixed>  $milestoneTracking
     * @param  array<string, mixed>  $riskAssessment
     * @return array<string, string>
     */
    protected function generateRecommendations(): array
        $recommendations = [];

        // Progress recommendations
        $progress = $milestoneTracking['overall_progress'];
        $recommendations['progress'] = match (true) {
            $progress >= 75 => 'Excellent progress! Focus on final optimization',
            $progress >= 50 => 'Good progress. Continue with current development plan',
            $progress >= 25 => 'Moderate progress. Increase focus on priority milestones',
            default => 'Early stage. Focus on foundation building and hint collection',
        };

        // Risk mitigation recommendations
        $highestRisk = $riskAssessment['highest_severity'];
        if ($highestRisk === 'high') {
            $recommendations['risk'] = 'High-risk factors detected. Review and adjust development plan';
        } elseif ($highestRisk === 'medium') {
            $recommendations['risk'] = 'Moderate risks present. Monitor progress closely';
        }

        // Timeline recommendations
        $estimatedCompletion = $developmentRoadmap['estimated_completion'];
        $turnsRemaining = $developmentRoadmap['turns_remaining'];

        if ($estimatedCompletion > $turnsRemaining) {
            $recommendations['timeline'] = 'Development plan exceeds available turns. Prioritize critical goals';
        } else {
            $buffer = $turnsRemaining - $estimatedCompletion;
            $recommendations['timeline'] = "Development plan feasible with {$buffer} turn buffer for flexibility";
        }

        return $recommendations;
    }

    /**
     * Calculate confidence score for recommendations
     *
     * @param  array<string, mixed>  $developmentRoadmap
     * @param  array<string, mixed>  $riskAssessment
     */
    protected function calculateConfidence(array $developmentRoadmap, array $riskAssessment): float
    {
        $confidence = 1.0;

        // Reduce confidence if timeline is not feasible
        if ($developmentRoadmap['skill_timeline']['feasibility'] !== 'feasible') {
            $confidence *= 0.7;
        }

        if ($developmentRoadmap['stat_timeline']['feasibility'] !== 'feasible') {
            $confidence *= 0.8;
        }

        // Reduce confidence based on risk severity
        $highestRisk = $riskAssessment['highest_severity'];
        if ($highestRisk === 'high') {
            $confidence *= 0.7;
        } elseif ($highestRisk === 'medium') {
            $confidence *= 0.9;
        }

        return round($confidence, 2);
    }
}
