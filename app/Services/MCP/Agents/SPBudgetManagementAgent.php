<?php

namespace App\Services\MCP\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use App\Services\SkillHintService;
use Illuminate\Support\Collection;

/**
 * SP Budget Management Agent
 *
 * Provides hint collection optimization and cost tracking for SP budget management.
 * Analyzes available SP, planned skill acquisitions, and hint collection opportunities
 * to maximize SP efficiency and ensure optimal resource allocation.
 */
class SPBudgetManagementAgent
{
    protected MCPClientService $mcpClient;

    protected SkillHintService $hintService;

    /**
     * SP cost categories for budget allocation
     *
     * @var array<string, array{min: int, max: int}>
     */
    protected array $spCostCategories = [
        'normal' => ['min' => 120, 'max' => 180],
        'rare' => ['min' => 180, 'max' => 240],
        'unique' => ['min' => 100, 'max' => 300],
    ];

    public function __construct(MCPClientService $mcpClient, SkillHintService $hintService)
    {
        $this->mcpClient = $mcpClient;
        $this->hintService = $hintService;
    }

    /**
     * Analyze SP budget and provide comprehensive management recommendations
     *
     * @param  array<string, mixed>  $context
     * @return array{
     *     budget_status: array<string, mixed>,
     *     allocation_plan: array<string, mixed>,
     *     hint_optimization: array<string, mixed>,
     *     cost_tracking: array<string, mixed>,
     *     recommendations: array<string, string>,
     *     confidence: float
     * }
     */
    public function analyzeSPBudget(Character $character, Collection $targetSkills, array $context = []): array
    {
        // Calculate current budget status
        $budgetStatus = $this->calculateBudgetStatus($character, $targetSkills);

        // Create allocation plan
        $allocationPlan = $this->createAllocationPlan($character, $targetSkills, $budgetStatus);

        // Analyze hint optimization opportunities
        $hintOptimization = $this->analyzeHintOptimization($character, $targetSkills);

        // Track costs and savings
        $costTracking = $this->trackCosts($character, $targetSkills, $hintOptimization);

        // Generate recommendations
        $recommendations = $this->generateRecommendations(
            $character,
            $budgetStatus,
            $allocationPlan,
            $hintOptimization
        );

        // Calculate confidence score
        $confidence = $this->calculateConfidence($budgetStatus, $hintOptimization);

        return [
            'budget_status' => $budgetStatus,
            'allocation_plan' => $allocationPlan,
            'hint_optimization' => $hintOptimization,
            'cost_tracking' => $costTracking,
            'recommendations' => $recommendations,
            'confidence' => $confidence,
        ];
    }

    /**
     * Calculate current SP budget status
     *
     * @return array<string, mixed>
     */
    protected function calculateBudgetStatus(Character $character, Collection $targetSkills): array
    {
        // Get current SP from character
        $currentSP = $character->current_sp ?? 0;

        // Calculate total base cost of target skills
        $totalBaseCost = $targetSkills->sum('base_sp_cost');

        // Calculate potential savings from hints
        $potentialSavings = 0;
        foreach ($targetSkills as $skill) {
            $hints = $this->hintService->getHintsForSkill($character, $skill);
            $hintCount = $hints->count();
            $discount = min(0.40, $hintCount * 0.20);
            $potentialSavings += $skill->base_sp_cost * $discount;
        }

        // Calculate estimated final cost
        $estimatedFinalCost = $totalBaseCost - $potentialSavings;

        // Calculate budget surplus/deficit
        $budgetBalance = $currentSP - $estimatedFinalCost;

        // Determine budget status
        $status = match (true) {
            $budgetBalance >= $estimatedFinalCost * 0.5 => 'excellent',
            $budgetBalance >= $estimatedFinalCost * 0.2 => 'good',
            $budgetBalance >= 0 => 'adequate',
            $budgetBalance >= -$estimatedFinalCost * 0.2 => 'tight',
            default => 'insufficient',
        };

        return [
            'current_sp' => $currentSP,
            'total_base_cost' => $totalBaseCost,
            'potential_savings' => (int) $potentialSavings,
            'estimated_final_cost' => (int) $estimatedFinalCost,
            'budget_balance' => (int) $budgetBalance,
            'status' => $status,
            'utilization_percentage' => $currentSP > 0 ? round(($estimatedFinalCost / $currentSP) * 100, 1) : 0,
        ];
    }

    /**
     * Create SP allocation plan
     *
     * @param  array<string, mixed>  $budgetStatus
     * @return array<string, mixed>
     */
    protected function createAllocationPlan(
        Character $character,
        Collection $targetSkills,
        array $budgetStatus
    ): array {
        $currentSP = $budgetStatus['current_sp'];
        $allocations = [];
        $remainingSP = $currentSP;

        // Sort skills by priority (considering hints and SP cost)
        $sortedSkills = $targetSkills->sortByDesc(function ($skill) use ($character) {
            $hints = $this->hintService->getHintsForSkill($character, $skill);
            $hintCount = $hints->count();

            // Priority score: max discount skills first, then high-cost skills
            $priorityScore = 0;

            if ($hintCount >= 2) {
                $priorityScore += 1000; // Max discount reached
            } elseif ($hintCount === 1) {
                $priorityScore += 500; // One hint away from max
            }

            $priorityScore += $skill->base_sp_cost;

            return $priorityScore;
        });

        // Allocate SP to skills
        foreach ($sortedSkills as $skill) {
            $hints = $this->hintService->getHintsForSkill($character, $skill);
            $hintCount = $hints->count();
            $finalCost = $this->hintService->calculateFinalCost($skill, $hintCount);

            $canAfford = $remainingSP >= $finalCost;

            $allocations[] = [
                'skill_id' => $skill->id,
                'skill_name' => $skill->name,
                'base_cost' => $skill->base_sp_cost,
                'hint_count' => $hintCount,
                'discount_percentage' => min(40, $hintCount * 20),
                'final_cost' => $finalCost,
                'can_afford' => $canAfford,
                'priority' => $this->calculateSkillPriority($skill, $hintCount),
            ];

            if ($canAfford) {
                $remainingSP -= $finalCost;
            }
        }

        // Calculate allocation statistics
        $affordableSkills = collect($allocations)->where('can_afford', true)->count();
        $totalAffordableCost = collect($allocations)->where('can_afford', true)->sum('final_cost');

        return [
            'allocations' => $allocations,
            'remaining_sp' => $remainingSP,
            'affordable_skills' => $affordableSkills,
            'total_skills' => $targetSkills->count(),
            'total_affordable_cost' => $totalAffordableCost,
            'allocation_efficiency' => $currentSP > 0 ? round(($totalAffordableCost / $currentSP) * 100, 1) : 0,
        ];
    }

    /**
     * Calculate skill priority for allocation
     */
    protected function calculateSkillPriority(object $skill, int $hintCount): string
    {
        // Max discount reached
        if ($hintCount >= 2) {
            return 'critical';
        }

        // One hint away from max
        if ($hintCount === 1) {
            return 'high';
        }

        // High SP cost with no hints
        if ($skill->base_sp_cost >= 180 && $hintCount === 0) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Analyze hint optimization opportunities
     *
     * @return array<string, mixed>
     */
    protected function analyzeHintOptimization(Character $character, Collection $targetSkills): array
    {
        $hintStats = $this->hintService->getHintStatistics($character);

        $optimizationOpportunities = [];
        $maxDiscountSkills = [];
        $oneHintAwaySkills = [];
        $noHintHighCostSkills = [];

        foreach ($targetSkills as $skill) {
            $hints = $this->hintService->getHintsForSkill($character, $skill);
            $hintCount = $hints->count();

            if ($hintCount >= 2) {
                $maxDiscountSkills[] = [
                    'skill_name' => $skill->name,
                    'hint_count' => $hintCount,
                    'savings' => (int) ($skill->base_sp_cost * 0.4),
                ];
            } elseif ($hintCount === 1) {
                $oneHintAwaySkills[] = [
                    'skill_name' => $skill->name,
                    'potential_savings' => (int) ($skill->base_sp_cost * 0.2),
                ];
            } elseif ($hintCount === 0 && $skill->base_sp_cost >= 180) {
                $noHintHighCostSkills[] = [
                    'skill_name' => $skill->name,
                    'base_cost' => $skill->base_sp_cost,
                    'max_potential_savings' => (int) ($skill->base_sp_cost * 0.4),
                ];
            }
        }

        return [
            'hint_statistics' => $hintStats,
            'max_discount_skills' => $maxDiscountSkills,
            'one_hint_away_skills' => $oneHintAwaySkills,
            'no_hint_high_cost_skills' => $noHintHighCostSkills,
            'optimization_score' => $this->calculateOptimizationScore($hintStats, $targetSkills->count()),
        ];
    }

    /**
     * Calculate optimization score
     */
    protected function calculateOptimizationScore(array $hintStats, int $totalSkills): int
    {
        $score = 0;

        // Score based on skills with max discount
        $maxDiscountSkills = $hintStats['skills_with_max_discount'];
        if ($totalSkills > 0) {
            $score += (int) (($maxDiscountSkills / $totalSkills) * 40);
        }

        // Score based on total SP saved
        $totalSpSaved = $hintStats['total_sp_saved'];
        $score += min(30, (int) ($totalSpSaved / 10));

        // Score based on hint collection rate
        $totalHints = $hintStats['total_hints'];
        $score += min(30, (int) ($totalHints / 2));

        return min(100, $score);
    }

    /**
     * Track costs and savings
     *
     * @param  array<string, mixed>  $hintOptimization
     * @return array<string, mixed>
     */
    protected function trackCosts(
        Character $character,
        Collection $targetSkills,
        array $hintOptimization
    ): array {
        $totalBaseCost = $targetSkills->sum('base_sp_cost');
        $totalFinalCost = 0;
        $totalSavings = 0;

        foreach ($targetSkills as $skill) {
            $hints = $this->hintService->getHintsForSkill($character, $skill);
            $hintCount = $hints->count();
            $finalCost = $this->hintService->calculateFinalCost($skill, $hintCount);

            $totalFinalCost += $finalCost;
            $totalSavings += $skill->base_sp_cost - $finalCost;
        }

        $savingsPercentage = $totalBaseCost > 0 ? ($totalSavings / $totalBaseCost) * 100 : 0;

        // Calculate potential additional savings
        $potentialAdditionalSavings = 0;
        foreach ($hintOptimization['one_hint_away_skills'] as $skill) {
            $potentialAdditionalSavings += $skill['potential_savings'];
        }

        return [
            'total_base_cost' => $totalBaseCost,
            'total_final_cost' => $totalFinalCost,
            'total_savings' => (int) $totalSavings,
            'savings_percentage' => round($savingsPercentage, 1),
            'potential_additional_savings' => $potentialAdditionalSavings,
            'efficiency_grade' => $this->getEfficiencyGrade($savingsPercentage),
            'cost_breakdown' => [
                'normal_skills' => $this->getCostByCategory($targetSkills, 'normal'),
                'rare_skills' => $this->getCostByCategory($targetSkills, 'rare'),
                'unique_skills' => $this->getCostByCategory($targetSkills, 'unique'),
            ],
        ];
    }

    /**
     * Get cost breakdown by skill category
     */
    protected function getCostByCategory(Collection $skills, string $category): array
    {
        $categorySkills = $skills->filter(function ($skill) use ($category) {
            $cost = $skill->base_sp_cost;
            $range = $this->spCostCategories[$category] ?? null;

            if (! $range) {
                return false;
            }

            return $cost >= $range['min'] && $cost <= $range['max'];
        });

        return [
            'count' => $categorySkills->count(),
            'total_base_cost' => $categorySkills->sum('base_sp_cost'),
        ];
    }

    /**
     * Get efficiency grade based on savings percentage
     */
    protected function getEfficiencyGrade(float $savingsPercentage): string
    {
        return match (true) {
            $savingsPercentage >= 35 => 'S',
            $savingsPercentage >= 30 => 'A',
            $savingsPercentage >= 25 => 'B',
            $savingsPercentage >= 20 => 'C',
            default => 'D',
        };
    }

    /**
     * Generate budget management recommendations
     *
     * @param  array<string, mixed>  $budgetStatus
     * @param  array<string, mixed>  $allocationPlan
     * @param  array<string, mixed>  $hintOptimization
     * @return array<string, string>
     */
    protected function generateRecommendations(
        Character $character,
        array $budgetStatus,
        array $allocationPlan,
        array $hintOptimization
    ): array {
        $recommendations = [];

        // Budget status recommendations
        $status = $budgetStatus['status'];
        $recommendations['budget'] = match ($status) {
            'excellent' => 'Excellent SP budget! You can afford all target skills with room to spare.',
            'good' => 'Good SP budget. You can afford most target skills with careful planning.',
            'adequate' => 'Adequate SP budget. Prioritize skills with maximum discounts.',
            'tight' => 'Tight SP budget. Focus on hint collection before skill acquisition.',
            'insufficient' => 'Insufficient SP budget. Delay skill acquisition and maximize hint collection.',
        };

        // Hint optimization recommendations
        $maxDiscountCount = count($hintOptimization['max_discount_skills']);
        $oneHintAwayCount = count($hintOptimization['one_hint_away_skills']);

        if ($maxDiscountCount > 0) {
            $recommendations['max_discount'] = "You have {$maxDiscountCount} skill(s) with maximum discount. Acquire these immediately for optimal SP efficiency.";
        }

        if ($oneHintAwayCount > 0) {
            $recommendations['one_hint_away'] = "You have {$oneHintAwayCount} skill(s) one hint away from maximum discount. Prioritize hint collection for these skills.";
        }

        // Allocation efficiency recommendations
        $affordableSkills = $allocationPlan['affordable_skills'];
        $totalSkills = $allocationPlan['total_skills'];

        if ($affordableSkills < $totalSkills) {
            $shortfall = $totalSkills - $affordableSkills;
            $recommendations['shortfall'] = "You can currently afford {$affordableSkills}/{$totalSkills} target skills. Focus on hint collection to reduce costs for the remaining {$shortfall} skill(s).";
        }

        // Optimization score recommendations
        $optimizationScore = $hintOptimization['optimization_score'];
        if ($optimizationScore < 50) {
            $recommendations['optimization'] = 'Low hint optimization score. Increase hint collection efforts to improve SP efficiency.';
        } elseif ($optimizationScore >= 80) {
            $recommendations['optimization'] = 'Excellent hint optimization! Continue current strategy.';
        }

        return $recommendations;
    }

    /**
     * Calculate confidence score for recommendations
     *
     * @param  array<string, mixed>  $budgetStatus
     * @param  array<string, mixed>  $hintOptimization
     */
    protected function calculateConfidence(array $budgetStatus, array $hintOptimization): float
    {
        $confidence = 1.0;

        // Reduce confidence if budget status is uncertain
        if ($budgetStatus['status'] === 'tight' || $budgetStatus['status'] === 'insufficient') {
            $confidence *= 0.8;
        }

        // Reduce confidence if optimization score is low
        $optimizationScore = $hintOptimization['optimization_score'];
        if ($optimizationScore < 50) {
            $confidence *= 0.9;
        }

        // Increase confidence if many skills have max discount
        $maxDiscountCount = count($hintOptimization['max_discount_skills']);
        if ($maxDiscountCount > 3) {
            $confidence = min(1.0, $confidence * 1.1);
        }

        return round($confidence, 2);
    }

    /**
     * Calculate SP earning rate and projections
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function calculateSPProjections(Character $character, array $context = []): array
    {
        // Get current SP
        $currentSP = $character->current_sp ?? 0;

        // Estimate SP earning rate (based on typical career progression)
        $careerStage = $character->career_stage ?? 'junior';
        $turnsRemaining = $context['turns_remaining'] ?? 30;

        // Average SP per turn by career stage
        $spPerTurn = match ($careerStage) {
            'junior' => 15,
            'classic' => 20,
            'senior' => 25,
            default => 18,
        };

        // Calculate projections
        $projectedSP = $currentSP + ($spPerTurn * $turnsRemaining);
        $conservativeProjection = $currentSP + ($spPerTurn * 0.8 * $turnsRemaining);
        $optimisticProjection = $currentSP + ($spPerTurn * 1.2 * $turnsRemaining);

        return [
            'current_sp' => $currentSP,
            'sp_per_turn' => $spPerTurn,
            'turns_remaining' => $turnsRemaining,
            'projected_sp' => (int) $projectedSP,
            'conservative_projection' => (int) $conservativeProjection,
            'optimistic_projection' => (int) $optimisticProjection,
            'earning_rate' => 'average',
        ];
    }
}
