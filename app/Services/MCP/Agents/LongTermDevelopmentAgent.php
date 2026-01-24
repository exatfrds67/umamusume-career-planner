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
     * @param  Collection<int, object>  $targetSkills
     * @param  array<string, mixed>  $goals
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
    public function analyzeLongTermDevelopment(
        Character $character,
        Collection $targetSkills,
        array $goals = [],
        array $context = []
    ): array {
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
     * @param  Collection<int, object>  $targetSkills
     * @param  array<string, mixed>  $goals
     * @return array<string, mixed>
     */
    protected function createDevelopmentRoadmap(
        Character $character,
        Collection $targetSkills,
        array $goals
    ): array {
        // Determine current career stage
        $currentStage = $character->career_stage ?? 'junior';

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
     * @param  Collection<int, object>  $targetSkills
     * @return array<string, mixed>
     */
    protected function createSkillTimeline(
        Character $character,
        Collection $targetSkills,
        int $turnsRemaining
    ): array {
        /** @var array<int, array<string, mixed>> $timeline */
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

            $skillName = (string) ($skill->name ?? 'Unknown');

            // Add to timeline
            $timeline[] = [
                'turn_range' => [$currentTurn, $currentTurn + $hintCollectionTurns],
                'action' => 'hint_collection',
                'skill_name' => $skillName,
                'hints_needed' => $hintsNeeded,
            ];

            $currentTurn += $hintCollectionTurns;

            // Add acquisition turn
            $timeline[] = [
                'turn' => $currentTurn,
                'action' => 'skill_acquisition',
                'skill_name' => $skillName,
                'estimated_cost' => $this->estimateSkillCost($character, $skill),
            ];

            $currentTurn++;
        }

        return [
            'timeline' => $timeline,
            'total_turns_needed' => $currentTurn - 1,
            'feasibility' => ($currentTurn - 1) <= $turnsRemaining ? 'feasible' : 'requires_adjustment',
        ];
    }

    /**
     * Prioritize skills for acquisition
     *
     * @param  Collection<int, object>  $targetSkills
     * @return Collection<int, object>
     */
    protected function prioritizeSkills(Character $character, Collection $targetSkills): Collection
    {
        return $targetSkills->sortByDesc(function ($skill) use ($character) {
            $score = 0;

            // Priority for skills with hints
            $skillId = $skill->id ?? 0;
            $baseCost = (int) ($skill->base_sp_cost ?? 0);
            $metaTier = (string) ($skill->meta_tier ?? 'C');

            // Check hints via relationship (if exists)
            $hints = 0;
            if (method_exists($character, 'skillHints')) {
                $hints = $character->skillHints()->where('skill_id', $skillId)->count();
            }
            $score += $hints * 50;

            // Priority for high-cost skills
            $score += (int) ($baseCost / 10);

            // Priority for meta-tier skills
            $score += match ($metaTier) {
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
        $skillId = $skill->id ?? 0;
        $currentHints = 0;

        if (method_exists($character, 'skillHints')) {
            $currentHints = $character->skillHints()->where('skill_id', $skillId)->count();
        }

        return (int) max(0, 2 - $currentHints); // Need 2 hints for max discount
    }

    /**
     * Estimate skill cost after hint collection
     */
    protected function estimateSkillCost(Character $character, object $skill): int
    {
        $skillId = $skill->id ?? 0;
        $baseCost = (int) ($skill->base_sp_cost ?? 0);

        $currentHints = 0;
        if (method_exists($character, 'skillHints')) {
            $currentHints = $character->skillHints()->where('skill_id', $skillId)->count();
        }

        $hintsAfterCollection = min(2, $currentHints + $this->calculateHintsNeeded($character, $skill));

        $discount = min(0.40, $hintsAfterCollection * 0.20);

        return (int) ($baseCost * (1 - $discount));
    }

    /**
     * Create stat development timeline
     *
     * @param  array<string, mixed>  $goals
     * @return array<string, mixed>
     */
    protected function createStatTimeline(
        Character $character,
        array $goals,
        int $turnsRemaining
    ): array {
        /** @var array<string, int> $currentStats */
        $currentStats = $character->current_stats ?? [];
        /** @var array<string, int> $targetStats */
        $targetStats = isset($goals['target_stats']) && is_array($goals['target_stats'])
            ? $goals['target_stats']
            : [];

        /** @var array<int, array<string, mixed>> $timeline */
        $timeline = [];

        foreach ($targetStats as $stat => $target) {
            if (! is_string($stat) || ! is_numeric($target)) {
                continue;
            }

            $currentStatValue = $currentStats[$stat] ?? 0;
            $current = is_numeric($currentStatValue) ? (int) $currentStatValue : 0;
            $targetVal = (int) $target;
            $gap = max(0, $targetVal - $current);

            if ($gap === 0) {
                continue;
            }

            // Estimate turns needed (assuming ~20 points per turn)
            $turnsNeeded = (int) ceil($gap / 20);

            // Identify breakpoints
            /** @var array<int, array<string, int>> $breakpoints */
            $breakpoints = [];
            if ($current < 900 && $targetVal >= 900) {
                $breakpoints[] = ['value' => 900, 'turn' => (int) ceil((900 - $current) / 20)];
            }
            if ($current < 1200 && $targetVal >= 1200) {
                $breakpoints[] = ['value' => 1200, 'turn' => (int) ceil((1200 - $current) / 20)];
            }

            $timeline[] = [
                'stat' => $stat,
                'current' => $current,
                'target' => $targetVal,
                'gap' => $gap,
                'turns_needed' => $turnsNeeded,
                'breakpoints' => $breakpoints,
            ];
        }

        /** @var array<int> $turnsNeededArray */
        $turnsNeededArray = array_column($timeline, 'turns_needed');
        $maxTurnsNeeded = ! empty($turnsNeededArray) ? (int) max($turnsNeededArray) : 0;

        return [
            'timeline' => $timeline,
            'total_turns_needed' => $maxTurnsNeeded,
            'feasibility' => $maxTurnsNeeded <= $turnsRemaining ? 'feasible' : 'requires_adjustment',
        ];
    }

    /**
     * Identify critical decision points
     *
     * @param  array<string, mixed>  $skillTimeline
     * @param  array<string, mixed>  $statTimeline
     * @return array<int, array<string, mixed>>
     */
    protected function identifyCriticalDecisionPoints(array $skillTimeline, array $statTimeline): array
    {
        /** @var array<int, array<string, mixed>> $decisionPoints */
        $decisionPoints = [];

        // Add stat breakpoint decisions
        /** @var array<int, array<string, mixed>> $statTimelineList */
        $statTimelineList = isset($statTimeline['timeline']) && is_array($statTimeline['timeline']) ? $statTimeline['timeline'] : [];
        foreach ($statTimelineList as $statPlan) {
            if (! is_array($statPlan)) {
                continue;
            }
            $statValue = $statPlan['stat'] ?? '';
            $stat = is_string($statValue) ? $statValue : '';
            /** @var array<int, array<string, int>> $breakpoints */
            $breakpoints = isset($statPlan['breakpoints']) && is_array($statPlan['breakpoints']) ? $statPlan['breakpoints'] : [];

            foreach ($breakpoints as $breakpoint) {
                if (! is_array($breakpoint)) {
                    continue;
                }
                $turnValue = $breakpoint['turn'] ?? 0;
                $turn = is_numeric($turnValue) ? (int) $turnValue : 0;
                $valueNum = $breakpoint['value'] ?? 0;
                $value = is_numeric($valueNum) ? (int) $valueNum : 0;

                $decisionPoints[] = [
                    'turn' => $turn,
                    'type' => 'stat_breakpoint',
                    'description' => "Reach {$stat} {$value} breakpoint",
                    'importance' => 'high',
                ];
            }
        }

        // Add skill acquisition decisions
        /** @var array<int, array<string, mixed>> $skillTimelineList */
        $skillTimelineList = isset($skillTimeline['timeline']) && is_array($skillTimeline['timeline']) ? $skillTimeline['timeline'] : [];
        foreach ($skillTimelineList as $event) {
            if (! is_array($event)) {
                continue;
            }
            $actionValue = $event['action'] ?? '';
            $action = is_string($actionValue) ? $actionValue : '';
            if ($action === 'skill_acquisition') {
                $turnValue = $event['turn'] ?? 0;
                $turn = is_numeric($turnValue) ? (int) $turnValue : 0;
                $skillNameValue = $event['skill_name'] ?? 'Unknown';
                $skillName = is_string($skillNameValue) ? $skillNameValue : 'Unknown';

                $decisionPoints[] = [
                    'turn' => $turn,
                    'type' => 'skill_acquisition',
                    'description' => "Acquire {$skillName}",
                    'importance' => 'medium',
                ];
            }
        }

        // Sort by turn
        usort($decisionPoints, function ($a, $b) {
            $turnA = isset($a['turn']) && is_numeric($a['turn']) ? (int) $a['turn'] : 0;
            $turnB = isset($b['turn']) && is_numeric($b['turn']) ? (int) $b['turn'] : 0;

            return $turnA <=> $turnB;
        });

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
        $skillCompletionValue = $skillTimeline['total_turns_needed'] ?? 0;
        $skillCompletion = is_numeric($skillCompletionValue) ? (int) $skillCompletionValue : 0;
        $statCompletionValue = $statTimeline['total_turns_needed'] ?? 0;
        $statCompletion = is_numeric($statCompletionValue) ? (int) $statCompletionValue : 0;

        return max($skillCompletion, $statCompletion);
    }

    /**
     * Track milestones
     *
     * @param  Collection<int, object>  $targetSkills
     * @param  array<string, mixed>  $goals
     * @return array<string, mixed>
     */
    protected function trackMilestones(
        Character $character,
        Collection $targetSkills,
        array $goals
    ): array {
        /** @var array<int, array<string, mixed>> $milestones */
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
            'completed_count' => count(array_filter($milestones, fn ($m) => ($m['completed'] ?? false) === true)),
            'total_count' => count($milestones),
            'overall_progress' => $overallProgress,
        ];
    }

    /**
     * Track stat milestones
     *
     * @param  array<string, mixed>  $goals
     * @return array<int, array<string, mixed>>
     */
    protected function trackStatMilestones(Character $character, array $goals): array
    {
        /** @var array<int, array<string, mixed>> $milestones */
        $milestones = [];
        /** @var array<string, int> $currentStats */
        $currentStats = $character->current_stats ?? [];
        /** @var array<string, int> $targetStats */
        $targetStats = isset($goals['target_stats']) && is_array($goals['target_stats'])
            ? $goals['target_stats']
            : [];

        foreach ($targetStats as $stat => $target) {
            if (! is_string($stat) || ! is_numeric($target)) {
                continue;
            }

            $current = is_array($currentStats) && isset($currentStats[$stat]) ? (int) $currentStats[$stat] : 0;
            $targetVal = (int) $target;

            // 900 breakpoint milestone
            if ($targetVal >= 900) {
                $milestones[] = [
                    'type' => 'stat_breakpoint',
                    'description' => "{$stat} reaches 900",
                    'completed' => $current >= 900,
                    'progress' => min(100.0, ($current / 900) * 100),
                    'importance' => 'high',
                ];
            }

            // 1200 breakpoint milestone
            if ($targetVal >= 1200) {
                $milestones[] = [
                    'type' => 'stat_breakpoint',
                    'description' => "{$stat} reaches 1200",
                    'completed' => $current >= 1200,
                    'progress' => min(100.0, ($current / 1200) * 100),
                    'importance' => 'high',
                ];
            }

            // Target milestone
            $milestones[] = [
                'type' => 'stat_target',
                'description' => "{$stat} reaches target ({$targetVal})",
                'completed' => $current >= $targetVal,
                'progress' => $targetVal > 0 ? min(100.0, ($current / $targetVal) * 100) : 100.0,
                'importance' => 'medium',
            ];
        }

        return $milestones;
    }

    /**
     * Track skill milestones
     *
     * @param  Collection<int, object>  $targetSkills
     * @return array<int, array<string, mixed>>
     */
    protected function trackSkillMilestones(Character $character, Collection $targetSkills): array
    {
        /** @var array<int, array<string, mixed>> $milestones */
        $milestones = [];

        foreach ($targetSkills as $skill) {
            if (! is_object($skill)) {
                continue;
            }

            $skillId = $skill->id ?? 0;
            $skillName = (string) ($skill->name ?? 'Unknown');

            $hints = 0;
            $acquired = false;

            if (method_exists($character, 'skillHints')) {
                $hints = $character->skillHints()->where('skill_id', $skillId)->count();
            }
            $acquired = $character->skills()->where('skill_id', $skillId)->exists();

            // Hint collection milestone
            $milestones[] = [
                'type' => 'max_hint_discount',
                'description' => "Collect 2 hints for {$skillName}",
                'completed' => $hints >= 2,
                'progress' => ($hints / 2) * 100,
                'importance' => 'medium',
            ];

            // Skill acquisition milestone
            $milestones[] = [
                'type' => 'skill_acquisition',
                'description' => "Acquire {$skillName}",
                'completed' => $acquired,
                'progress' => $acquired ? 100.0 : 0.0,
                'importance' => 'medium',
            ];
        }

        return $milestones;
    }

    /**
     * Calculate overall progress
     *
     * @param  array<int, array<string, mixed>>  $milestones
     */
    protected function calculateOverallProgress(array $milestones): float
    {
        if (empty($milestones)) {
            return 0.0;
        }

        $totalProgress = 0.0;
        foreach ($milestones as $milestone) {
            if (is_array($milestone) && isset($milestone['progress'])) {
                $progressValue = $milestone['progress'];
                $totalProgress += is_numeric($progressValue) ? (float) $progressValue : 0.0;
            }
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
    protected function planDevelopmentPhases(
        Character $character,
        array $developmentRoadmap,
        array $milestoneTracking
    ): array {
        $turnsRemainingValue = $developmentRoadmap['turns_remaining'] ?? 24;
        $turnsRemaining = is_numeric($turnsRemainingValue) ? (int) $turnsRemainingValue : 24;

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
    protected function divideTurnsIntoPhases(int $turnsRemaining): array
    {
        $phaseLength = (int) ceil($turnsRemaining / 3);

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
    protected function assignMilestonesToPhases(array $phases, array $milestoneTracking): array
    {
        /** @var array<string, mixed> $phasePlans */
        $phasePlans = [];

        /** @var array<int, array<string, mixed>> $allMilestones */
        $allMilestones = isset($milestoneTracking['milestones']) && is_array($milestoneTracking['milestones']) ? $milestoneTracking['milestones'] : [];

        foreach ($phases as $phaseName => $phaseInfo) {
            $phaseMilestones = array_filter(
                $allMilestones,
                fn ($m) => ($m['completed'] ?? true) === false
            );

            $phasePlans[$phaseName] = [
                'start' => $phaseInfo['start'],
                'end' => $phaseInfo['end'],
                'focus' => $phaseInfo['focus'],
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
        $progressValue = $milestoneTracking['overall_progress'] ?? 0.0;
        $progress = is_numeric($progressValue) ? (float) $progressValue : 0.0;

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
    protected function assessRisks(
        Character $character,
        array $developmentRoadmap,
        array $context
    ): array {
        /** @var array<int, array<string, string>> $risks */
        $risks = [];

        // Time constraint risk
        /** @var array<string, mixed> $skillTimeline */
        $skillTimeline = isset($developmentRoadmap['skill_timeline']) && is_array($developmentRoadmap['skill_timeline']) ? $developmentRoadmap['skill_timeline'] : [];
        /** @var array<string, mixed> $statTimeline */
        $statTimeline = isset($developmentRoadmap['stat_timeline']) && is_array($developmentRoadmap['stat_timeline']) ? $developmentRoadmap['stat_timeline'] : [];

        $skillFeasibilityValue = $skillTimeline['feasibility'] ?? 'feasible';
        $skillFeasibility = is_string($skillFeasibilityValue) ? $skillFeasibilityValue : 'feasible';
        $statFeasibilityValue = $statTimeline['feasibility'] ?? 'feasible';
        $statFeasibility = is_string($statFeasibilityValue) ? $statFeasibilityValue : 'feasible';

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

        /** @var array<string, mixed> $skillTimeline */
        $skillTimeline = isset($developmentRoadmap['skill_timeline']) && is_array($developmentRoadmap['skill_timeline']) ? $developmentRoadmap['skill_timeline'] : [];
        /** @var array<int, array<string, mixed>> $timeline */
        $timeline = isset($skillTimeline['timeline']) && is_array($skillTimeline['timeline']) ? $skillTimeline['timeline'] : [];

        foreach ($timeline as $event) {
            if (! is_array($event)) {
                continue;
            }
            $actionValue = $event['action'] ?? '';
            $action = is_string($actionValue) ? $actionValue : '';
            if ($action === 'skill_acquisition') {
                $estimatedCostValue = $event['estimated_cost'] ?? 0;
                $estimatedCost = is_numeric($estimatedCostValue) ? (int) $estimatedCostValue : 0;
                $totalCost += $estimatedCost;
            }
        }

        return $totalCost;
    }

    /**
     * Get highest severity from risks
     *
     * @param  array<int, array<string, string>>  $risks
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
    protected function generateRecommendations(
        array $developmentRoadmap,
        array $milestoneTracking,
        array $riskAssessment
    ): array {
        /** @var array<string, string> $recommendations */
        $recommendations = [];

        // Progress recommendations
        $progressValue = $milestoneTracking['overall_progress'] ?? 0.0;
        $progress = is_numeric($progressValue) ? (float) $progressValue : 0.0;
        $recommendations['progress'] = match (true) {
            $progress >= 75 => 'Excellent progress! Focus on final optimization',
            $progress >= 50 => 'Good progress. Continue with current development plan',
            $progress >= 25 => 'Moderate progress. Increase focus on priority milestones',
            default => 'Early stage. Focus on foundation building and hint collection',
        };

        // Risk mitigation recommendations
        $highestRiskValue = $riskAssessment['highest_severity'] ?? 'none';
        $highestRisk = is_string($highestRiskValue) ? $highestRiskValue : 'none';
        if ($highestRisk === 'high') {
            $recommendations['risk'] = 'High-risk factors detected. Review and adjust development plan';
        } elseif ($highestRisk === 'medium') {
            $recommendations['risk'] = 'Moderate risks present. Monitor progress closely';
        }

        // Timeline recommendations
        $estimatedCompletionValue = $developmentRoadmap['estimated_completion'] ?? 0;
        $estimatedCompletion = is_numeric($estimatedCompletionValue) ? (int) $estimatedCompletionValue : 0;
        $turnsRemainingValue = $developmentRoadmap['turns_remaining'] ?? 0;
        $turnsRemaining = is_numeric($turnsRemainingValue) ? (int) $turnsRemainingValue : 0;

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
        /** @var array<string, mixed> $skillTimeline */
        $skillTimeline = isset($developmentRoadmap['skill_timeline']) && is_array($developmentRoadmap['skill_timeline']) ? $developmentRoadmap['skill_timeline'] : [];
        /** @var array<string, mixed> $statTimeline */
        $statTimeline = isset($developmentRoadmap['stat_timeline']) && is_array($developmentRoadmap['stat_timeline']) ? $developmentRoadmap['stat_timeline'] : [];

        $skillFeasibilityValue = $skillTimeline['feasibility'] ?? 'feasible';
        $skillFeasibility = is_string($skillFeasibilityValue) ? $skillFeasibilityValue : 'feasible';
        $statFeasibilityValue = $statTimeline['feasibility'] ?? 'feasible';
        $statFeasibility = is_string($statFeasibilityValue) ? $statFeasibilityValue : 'feasible';

        if ($skillFeasibility !== 'feasible') {
            $confidence *= 0.7;
        }

        if ($statFeasibility !== 'feasible') {
            $confidence *= 0.8;
        }

        // Reduce confidence based on risk severity
        $highestRiskValue = $riskAssessment['highest_severity'] ?? 'none';
        $highestRisk = is_string($highestRiskValue) ? $highestRiskValue : 'none';
        if ($highestRisk === 'high') {
            $confidence *= 0.7;
        } elseif ($highestRisk === 'medium') {
            $confidence *= 0.9;
        }

        return round($confidence, 2);
    }
}
