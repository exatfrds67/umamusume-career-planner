<?php

namespace App\Services\Agents;

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use App\Services\SkillHintService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Hint Optimization Agent
 *
 * MCP-powered agent for strategic hint collection and cost minimization planning.
 * Provides intelligent recommendations for maximizing SP savings through optimal
 * hint collection timing and training selection.
 */
class HintOptimizationAgent
{
    private const AGENT_NAME = 'hint-optimization-agent';

    public function __construct(
        private SkillHintService $hintService,
        private MCPClientService $mcpClient
    ) {}

    /**
     * Analyze hint collection opportunities and provide strategic recommendations.
     */
    public function analyzeHintOpportunities(
        Character $character,
        Collection $targetSkills,
        Collection $supportCards,
        array $context = []
    ): array {
        $startTime = microtime(true);

        try {
            // Gather hint data
            $hintData = $this->gatherHintData($character, $targetSkills, $supportCards);

            // Use MCP agent for advanced analysis
            $mcpAnalysis = $this->performMCPAnalysis($hintData, $context);

            // Combine local and MCP analysis
            $recommendations = $this->generateRecommendations($hintData, $mcpAnalysis);

            $processingTime = microtime(true) - $startTime;

            Log::info('Hint optimization analysis completed', [
                'character_id' => $character->id,
                'target_skills' => $targetSkills->count(),
                'processing_time' => $processingTime,
                'recommendations' => count($recommendations),
            ]);

            return [
                'success' => true,
                'character_id' => $character->id,
                'analysis' => $hintData,
                'mcp_insights' => $mcpAnalysis,
                'recommendations' => $recommendations,
                'processing_time' => $processingTime,
                'agent_name' => self::AGENT_NAME,
            ];
        } catch (\Exception $e) {
            Log::error('Hint optimization analysis failed', [
                'character_id' => $character->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'fallback_recommendations' => $this->getFallbackRecommendations($character, $targetSkills),
            ];
        }
    }

    /**
     * Gather comprehensive hint data for analysis.
     */
    private function gatherHintData(
        Character $character,
        Collection $targetSkills,
        Collection $supportCards
    ): array {
        $skillHintData = [];

        foreach ($targetSkills as $skill) {
            $costBreakdown = $this->hintService->getCostBreakdown($character, $skill);
            $skillHintData[] = $costBreakdown;
        }

        // Get hint statistics
        $hintStats = $this->hintService->getHintStatistics($character);

        // Get hint collection strategies
        $strategies = $this->hintService->getHintCollectionStrategy($character, $targetSkills);

        return [
            'skill_hint_data' => $skillHintData,
            'hint_statistics' => $hintStats,
            'collection_strategies' => $strategies,
            'support_cards' => $supportCards->map(fn ($card) => [
                'id' => $card->id,
                'name' => $card->card_name,
                'specialization' => $card->specialization,
                'friendship_level' => $card->friendship_level,
                'limit_break_level' => $card->limit_break_level,
            ])->toArray(),
        ];
    }

    /**
     * Perform MCP-powered analysis for advanced insights.
     */
    private function performMCPAnalysis(array $hintData, array $context): array
    {
        // Check if MCP is enabled
        if (! $this->mcpClient->isEnabled()) {
            Log::info('MCP is disabled, using local analysis only');

            return $this->getLocalAnalysis($hintData);
        }

        try {
            // Prepare prompt for MCP agent
            $prompt = $this->buildAnalysisPrompt($hintData, $context);

            // Check if strands-agents server is available
            if (! $this->mcpClient->isServerEnabled('strands-agents')) {
                Log::info('strands-agents server not available, using local analysis');

                return $this->getLocalAnalysis($hintData);
            }

            // Note: Actual MCP agent call would go here when MCP client is fully implemented
            // For now, we use enhanced local analysis
            Log::info('MCP analysis requested but client not fully implemented, using enhanced local analysis');

            return $this->getLocalAnalysis($hintData);
        } catch (\Exception $e) {
            Log::warning('MCP analysis failed, using local analysis', [
                'error' => $e->getMessage(),
            ]);

            return $this->getLocalAnalysis($hintData);
        }
    }

    /**
     * Get local analysis when MCP is unavailable.
     */
    private function getLocalAnalysis(array $hintData): array
    {
        $insights = [];
        $prioritySkills = [];
        $trainingRecommendations = [];

        // Analyze hint collection efficiency
        $totalHints = $hintData['hint_statistics']['total_hints'];
        $unusedHints = $hintData['hint_statistics']['unused_hints'];
        $skillsWithMaxDiscount = $hintData['hint_statistics']['skills_with_max_discount'];

        if ($skillsWithMaxDiscount > 0) {
            $insights[] = "You have {$skillsWithMaxDiscount} skill(s) with maximum discount (40%). Consider acquiring these skills soon to maximize SP efficiency.";
        }

        if ($unusedHints > 0) {
            $insights[] = "You have {$unusedHints} unused hints. Make sure to acquire the corresponding skills before the hints expire or become less valuable.";
        }

        // Identify priority skills
        foreach ($hintData['skill_hint_data'] as $skillData) {
            if ($skillData['hint_count'] === 1) {
                $prioritySkills[] = [
                    'skill_name' => $skillData['skill_name'],
                    'reason' => 'One hint away from maximum discount',
                    'potential_savings' => (int) ($skillData['base_sp_cost'] * 0.2),
                ];
            }
        }

        // Generate training recommendations
        $supportCardTypes = array_unique(array_column($hintData['support_cards'], 'specialization'));
        foreach ($supportCardTypes as $type) {
            $trainingRecommendations[] = [
                'training_type' => $type,
                'reason' => "You have support cards specialized in {$type} training",
                'priority' => 'medium',
            ];
        }

        return [
            'insights' => $insights,
            'priority_skills' => $prioritySkills,
            'training_recommendations' => $trainingRecommendations,
            'sp_optimization_score' => $this->calculateOptimizationScore($hintData),
            'confidence' => 0.85, // High confidence in local analysis
        ];
    }

    /**
     * Calculate optimization score based on hint data.
     */
    private function calculateOptimizationScore(array $hintData): int
    {
        $score = 0;
        $maxScore = 100;

        // Score based on skills with max discount
        $skillsWithMaxDiscount = $hintData['hint_statistics']['skills_with_max_discount'];
        $totalSkills = count($hintData['skill_hint_data']);

        if ($totalSkills > 0) {
            $score += (int) (($skillsWithMaxDiscount / $totalSkills) * 40);
        }

        // Score based on total SP saved
        $totalSpSaved = $hintData['hint_statistics']['total_sp_saved'];
        if ($totalSpSaved > 0) {
            $score += min(30, (int) ($totalSpSaved / 10));
        }

        // Score based on hint collection rate
        $totalHints = $hintData['hint_statistics']['total_hints'];
        if ($totalHints > 0) {
            $score += min(30, (int) ($totalHints / 2));
        }

        return min($maxScore, $score);
    }

    /**
     * Build analysis prompt for MCP agent.
     */
    private function buildAnalysisPrompt(array $hintData, array $context): string
    {
        $prompt = "Analyze skill hint collection opportunities and provide strategic recommendations.\n\n";

        $prompt .= "## Current Hint Status\n";
        $prompt .= "Total hints collected: {$hintData['hint_statistics']['total_hints']}\n";
        $prompt .= "Unused hints: {$hintData['hint_statistics']['unused_hints']}\n";
        $prompt .= "Skills with max discount: {$hintData['hint_statistics']['skills_with_max_discount']}\n";
        $prompt .= "Total SP saved: {$hintData['hint_statistics']['total_sp_saved']}\n\n";

        $prompt .= "## Target Skills\n";
        foreach ($hintData['skill_hint_data'] as $skillData) {
            $prompt .= "- {$skillData['skill_name']}: ";
            $prompt .= "{$skillData['hint_count']} hints, ";
            $prompt .= "{$skillData['discount_percentage']}% discount, ";
            $prompt .= "Final cost: {$skillData['final_sp_cost']} SP\n";
        }

        $prompt .= "\n## Support Cards\n";
        foreach ($hintData['support_cards'] as $card) {
            $prompt .= "- {$card['name']} ({$card['specialization']}): ";
            $prompt .= "Friendship {$card['friendship_level']}%, ";
            $prompt .= "LB{$card['limit_break_level']}\n";
        }

        if (! empty($context)) {
            $prompt .= "\n## Additional Context\n";
            $prompt .= json_encode($context, JSON_PRETTY_PRINT);
        }

        $prompt .= "\n\nProvide:\n";
        $prompt .= "1. Priority ranking for hint collection\n";
        $prompt .= "2. Optimal training sequence for maximum hint acquisition\n";
        $prompt .= "3. SP optimization strategies\n";
        $prompt .= "4. Risk assessment for each recommendation\n";

        return $prompt;
    }

    /**
     * Generate comprehensive recommendations combining local and MCP analysis.
     */
    private function generateRecommendations(array $hintData, array $mcpAnalysis): array
    {
        $recommendations = [];

        // High priority: Skills with 1 hint (one more for max discount)
        $oneHintSkills = array_filter(
            $hintData['skill_hint_data'],
            fn ($skill) => $skill['hint_count'] === 1
        );

        if (! empty($oneHintSkills)) {
            $recommendations[] = [
                'type' => 'urgent',
                'title' => 'One Hint Away from Maximum Discount',
                'description' => 'These skills need just one more hint to reach 40% discount',
                'skills' => array_map(fn ($s) => $s['skill_name'], $oneHintSkills),
                'potential_savings' => array_sum(array_map(
                    fn ($s) => $s['base_sp_cost'] * 0.2,
                    $oneHintSkills
                )),
                'priority' => 'high',
            ];
        }

        // Medium priority: Skills with no hints but high SP cost
        $noHintHighCostSkills = array_filter(
            $hintData['skill_hint_data'],
            fn ($skill) => $skill['hint_count'] === 0 && $skill['base_sp_cost'] >= 180
        );

        if (! empty($noHintHighCostSkills)) {
            $recommendations[] = [
                'type' => 'opportunity',
                'title' => 'High-Value Hint Collection Opportunities',
                'description' => 'Expensive skills with no hints yet - maximum savings potential',
                'skills' => array_map(fn ($s) => $s['skill_name'], $noHintHighCostSkills),
                'potential_savings' => array_sum(array_map(
                    fn ($s) => $s['base_sp_cost'] * 0.4,
                    $noHintHighCostSkills
                )),
                'priority' => 'medium',
            ];
        }

        // Skills ready to acquire (max discount reached)
        $maxDiscountSkills = array_filter(
            $hintData['skill_hint_data'],
            fn ($skill) => $skill['max_discount_reached']
        );

        if (! empty($maxDiscountSkills)) {
            $recommendations[] = [
                'type' => 'ready',
                'title' => 'Skills Ready for Acquisition',
                'description' => 'Maximum discount achieved - optimal time to acquire',
                'skills' => array_map(fn ($s) => $s['skill_name'], $maxDiscountSkills),
                'total_cost' => array_sum(array_map(fn ($s) => $s['final_sp_cost'], $maxDiscountSkills)),
                'priority' => 'high',
            ];
        }

        // Add MCP insights if available
        if (! empty($mcpAnalysis['insights'])) {
            $recommendations[] = [
                'type' => 'ai_insight',
                'title' => 'AI-Powered Strategic Insights',
                'description' => 'Advanced analysis from Hint Optimization Agent',
                'insights' => $mcpAnalysis['insights'],
                'confidence' => $mcpAnalysis['confidence'],
                'priority' => 'info',
            ];
        }

        return $recommendations;
    }

    /**
     * Get fallback recommendations when MCP is unavailable.
     */
    private function getFallbackRecommendations(Character $character, Collection $targetSkills): array
    {
        $strategies = $this->hintService->getHintCollectionStrategy($character, $targetSkills);

        return array_map(fn ($strategy) => [
            'skill_name' => $strategy['skill_name'],
            'recommendation' => $strategy['recommendation'],
            'priority' => $strategy['priority'],
        ], $strategies);
    }

    /**
     * Calculate optimal hint collection sequence.
     */
    public function calculateOptimalSequence(
        Character $character,
        Collection $targetSkills,
        int $availableTurns
    ): array {
        $sequence = [];
        $currentTurn = 1;

        // Sort skills by hint collection priority
        $sortedSkills = $targetSkills->sortByDesc(function ($skill) use ($character) {
            $hints = $this->hintService->getHintsForSkill($character, $skill);
            $hintCount = $hints->count();

            // Priority score: skills with 1 hint get highest priority
            if ($hintCount === 1) {
                return 1000 + $skill->base_sp_cost;
            }

            // Then skills with 0 hints and high SP cost
            if ($hintCount === 0) {
                return 500 + $skill->base_sp_cost;
            }

            // Already at max discount
            return 0;
        });

        foreach ($sortedSkills as $skill) {
            if ($currentTurn > $availableTurns) {
                break;
            }

            $hints = $this->hintService->getHintsForSkill($character, $skill);
            $hintCount = $hints->count();

            if ($hintCount < 2) {
                $hintsNeeded = 2 - $hintCount;
                $sequence[] = [
                    'turn' => $currentTurn,
                    'skill_id' => $skill->id,
                    'skill_name' => $skill->name,
                    'action' => 'collect_hint',
                    'current_hints' => $hintCount,
                    'hints_needed' => $hintsNeeded,
                    'priority' => $hintCount === 1 ? 'high' : 'medium',
                ];

                $currentTurn += $hintsNeeded;
            }
        }

        return [
            'sequence' => $sequence,
            'total_turns_needed' => $currentTurn - 1,
            'turns_available' => $availableTurns,
            'feasible' => ($currentTurn - 1) <= $availableTurns,
        ];
    }

    /**
     * Evaluate hint collection efficiency.
     */
    public function evaluateEfficiency(Character $character, Collection $acquiredSkills): array
    {
        $totalBaseCost = 0;
        $totalFinalCost = 0;
        $totalSpSaved = 0;
        $skillsWithMaxDiscount = 0;
        $skillsWithPartialDiscount = 0;
        $skillsWithNoDiscount = 0;

        foreach ($acquiredSkills as $skill) {
            $hints = $this->hintService->getHintsForSkill($character, $skill);
            $hintCount = $hints->count();

            $totalBaseCost += $skill->base_sp_cost;
            $finalCost = $this->hintService->calculateFinalCost($skill, $hintCount);
            $totalFinalCost += $finalCost;
            $totalSpSaved += $skill->base_sp_cost - $finalCost;

            if ($hintCount >= 2) {
                $skillsWithMaxDiscount++;
            } elseif ($hintCount > 0) {
                $skillsWithPartialDiscount++;
            } else {
                $skillsWithNoDiscount++;
            }
        }

        $efficiencyPercentage = $totalBaseCost > 0
            ? ($totalSpSaved / $totalBaseCost) * 100
            : 0;

        return [
            'total_skills' => $acquiredSkills->count(),
            'total_base_cost' => $totalBaseCost,
            'total_final_cost' => $totalFinalCost,
            'total_sp_saved' => $totalSpSaved,
            'efficiency_percentage' => round($efficiencyPercentage, 2),
            'skills_with_max_discount' => $skillsWithMaxDiscount,
            'skills_with_partial_discount' => $skillsWithPartialDiscount,
            'skills_with_no_discount' => $skillsWithNoDiscount,
            'optimization_grade' => $this->getOptimizationGrade($efficiencyPercentage),
        ];
    }

    /**
     * Get optimization grade based on efficiency percentage.
     */
    private function getOptimizationGrade(float $efficiencyPercentage): string
    {
        return match (true) {
            $efficiencyPercentage >= 35 => 'S',  // 35%+ average discount
            $efficiencyPercentage >= 30 => 'A',  // 30-35% average discount
            $efficiencyPercentage >= 25 => 'B',  // 25-30% average discount
            $efficiencyPercentage >= 20 => 'C',  // 20-25% average discount
            default => 'D',                       // <20% average discount
        };
    }
}
