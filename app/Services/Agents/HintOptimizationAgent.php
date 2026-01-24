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
     *
     * @param  Collection<int, \App\Models\Skill>  $targetSkills
     * @param  Collection<int, \App\Models\SupportCard>  $supportCards
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
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
     *
     * @param  Collection<int, \App\Models\Skill>  $targetSkills
     * @param  Collection<int, \App\Models\SupportCard>  $supportCards
     * @return array{skill_hint_data: array<int, mixed>, hint_statistics: mixed, collection_strategies: mixed, support_cards: array<int, array<string, mixed>>}
     */
    private function gatherHintData(
        Character $character,
        Collection $targetSkills,
        Collection $supportCards
    ): array {
        $skillHintData = [];

        foreach ($targetSkills as $skill) {
            /** @var \App\Models\Skill|null $skillModel */
            $skillModel = $skill;
            $costBreakdown = \call_user_func([$this->hintService, 'getCostBreakdown'], $character, $skillModel);
            $skillHintData[] = $costBreakdown;
        }

        // Get hint statistics
        $hintStats = \call_user_func([$this->hintService, 'getHintStatistics'], $character);

        // Get hint collection strategies
        $strategies = \call_user_func([$this->hintService, 'getHintCollectionStrategy'], $character, $targetSkills);

        /** @var array<int, array<string, mixed>> $supportCardData */
        $supportCardData = $supportCards->map(function ($card): array {
            /** @var \App\Models\SupportCard $card */
            return [
                'id' => $card->id,
                'name' => $card->name,
                'specialization' => $card->specialization,
                'friendship_level' => $card->friendship_level,
                'limit_break_level' => $card->limit_break_level,
            ];
        })->toArray();

        return [
            'skill_hint_data' => $skillHintData,
            'hint_statistics' => $hintStats,
            'collection_strategies' => $strategies,
            'support_cards' => $supportCardData,
        ];
    }

    /**
     * Perform MCP-powered analysis for advanced insights.
     *
     * @param  array<string, mixed>  $hintData
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
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
     *
     * @param  array<string, mixed>  $hintData
     * @return array<string, mixed>
     */
    private function getLocalAnalysis(array $hintData): array
    {
        $insights = [];
        $prioritySkills = [];
        $trainingRecommendations = [];

        // Analyze hint collection efficiency
        $hintStats = is_array($hintData['hint_statistics']) ? $hintData['hint_statistics'] : [];
        $totalHints = isset($hintStats['total_hints']) && is_int($hintStats['total_hints']) ? $hintStats['total_hints'] : 0;
        $unusedHints = isset($hintStats['unused_hints']) && is_int($hintStats['unused_hints']) ? $hintStats['unused_hints'] : 0;
        $skillsWithMaxDiscount = isset($hintStats['skills_with_max_discount']) && is_int($hintStats['skills_with_max_discount']) ? $hintStats['skills_with_max_discount'] : 0;

        if ($skillsWithMaxDiscount > 0) {
            $insights[] = "You have {$skillsWithMaxDiscount} skill(s) with maximum discount (40%). Consider acquiring these skills soon to maximize SP efficiency.";
        }

        if ($unusedHints > 0) {
            $insights[] = "You have {$unusedHints} unused hints. Make sure to acquire the corresponding skills before the hints expire or become less valuable.";
        }

        // Identify priority skills
        $skillHintData = is_array($hintData['skill_hint_data']) ? $hintData['skill_hint_data'] : [];
        foreach ($skillHintData as $skillData) {
            if (! is_array($skillData)) {
                continue;
            }
            $hintCount = isset($skillData['hint_count']) && is_int($skillData['hint_count']) ? $skillData['hint_count'] : 0;
            if ($hintCount === 1) {
                $skillName = isset($skillData['skill_name']) && is_string($skillData['skill_name']) ? $skillData['skill_name'] : 'Unknown';
                $baseSpCost = isset($skillData['base_sp_cost']) && is_numeric($skillData['base_sp_cost']) ? (int) $skillData['base_sp_cost'] : 0;
                $prioritySkills[] = [
                    'skill_name' => $skillName,
                    'reason' => 'One hint away from maximum discount',
                    'potential_savings' => (int) ($baseSpCost * 0.2),
                ];
            }
        }

        // Generate training recommendations
        $supportCards = is_array($hintData['support_cards']) ? $hintData['support_cards'] : [];
        /** @var array<string> $specializations */
        $specializations = [];
        foreach ($supportCards as $card) {
            if (is_array($card) && isset($card['specialization']) && is_string($card['specialization'])) {
                $specializations[] = $card['specialization'];
            }
        }
        $supportCardTypes = array_unique($specializations);
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
     *
     * @param  array<string, mixed>  $hintData
     */
    private function calculateOptimizationScore(array $hintData): int
    {
        $score = 0;
        $maxScore = 100;

        $hintStats = is_array($hintData['hint_statistics']) ? $hintData['hint_statistics'] : [];
        $skillHintData = is_array($hintData['skill_hint_data']) ? $hintData['skill_hint_data'] : [];

        // Score based on skills with max discount
        $skillsWithMaxDiscount = isset($hintStats['skills_with_max_discount']) && is_int($hintStats['skills_with_max_discount']) ? $hintStats['skills_with_max_discount'] : 0;
        $totalSkills = count($skillHintData);

        if ($totalSkills > 0) {
            $score += (int) (($skillsWithMaxDiscount / $totalSkills) * 40);
        }

        // Score based on total SP saved
        $totalSpSaved = isset($hintStats['total_sp_saved']) && is_numeric($hintStats['total_sp_saved']) ? (int) $hintStats['total_sp_saved'] : 0;
        if ($totalSpSaved > 0) {
            $score += min(30, (int) ($totalSpSaved / 10));
        }

        // Score based on hint collection rate
        $totalHints = isset($hintStats['total_hints']) && is_numeric($hintStats['total_hints']) ? (int) $hintStats['total_hints'] : 0;
        if ($totalHints > 0) {
            $score += min(30, (int) ($totalHints / 2));
        }

        return min($maxScore, $score);
    }

    /**
     * Build analysis prompt for MCP agent.
     *
     * @param  array<string, mixed>  $hintData
     * @param  array<string, mixed>  $context
     */
    private function buildAnalysisPrompt(array $hintData, array $context): string
    {
        $hintStats = is_array($hintData['hint_statistics']) ? $hintData['hint_statistics'] : [];
        $skillHintData = is_array($hintData['skill_hint_data']) ? $hintData['skill_hint_data'] : [];
        $supportCards = is_array($hintData['support_cards']) ? $hintData['support_cards'] : [];

        $totalHints = isset($hintStats['total_hints']) && is_scalar($hintStats['total_hints']) ? (string) $hintStats['total_hints'] : '0';
        $unusedHints = isset($hintStats['unused_hints']) && is_scalar($hintStats['unused_hints']) ? (string) $hintStats['unused_hints'] : '0';
        $skillsMaxDiscount = isset($hintStats['skills_with_max_discount']) && is_scalar($hintStats['skills_with_max_discount']) ? (string) $hintStats['skills_with_max_discount'] : '0';
        $totalSpSaved = isset($hintStats['total_sp_saved']) && is_scalar($hintStats['total_sp_saved']) ? (string) $hintStats['total_sp_saved'] : '0';

        $prompt = "Analyze skill hint collection opportunities and provide strategic recommendations.\n\n";

        $prompt .= "## Current Hint Status\n";
        $prompt .= "Total hints collected: {$totalHints}\n";
        $prompt .= "Unused hints: {$unusedHints}\n";
        $prompt .= "Skills with max discount: {$skillsMaxDiscount}\n";
        $prompt .= "Total SP saved: {$totalSpSaved}\n\n";

        $prompt .= "## Target Skills\n";
        foreach ($skillHintData as $skillData) {
            if (! is_array($skillData)) {
                continue;
            }
            $skillName = isset($skillData['skill_name']) && is_scalar($skillData['skill_name']) ? (string) $skillData['skill_name'] : 'Unknown';
            $hintCount = isset($skillData['hint_count']) && is_scalar($skillData['hint_count']) ? (string) $skillData['hint_count'] : '0';
            $discountPct = isset($skillData['discount_percentage']) && is_scalar($skillData['discount_percentage']) ? (string) $skillData['discount_percentage'] : '0';
            $finalCost = isset($skillData['final_sp_cost']) && is_scalar($skillData['final_sp_cost']) ? (string) $skillData['final_sp_cost'] : '0';
            $prompt .= "- {$skillName}: ";
            $prompt .= "{$hintCount} hints, ";
            $prompt .= "{$discountPct}% discount, ";
            $prompt .= "Final cost: {$finalCost} SP\n";
        }

        $prompt .= "\n## Support Cards\n";
        foreach ($supportCards as $card) {
            if (! is_array($card)) {
                continue;
            }
            $cardName = isset($card['name']) && is_scalar($card['name']) ? (string) $card['name'] : 'Unknown';
            $spec = isset($card['specialization']) && is_scalar($card['specialization']) ? (string) $card['specialization'] : 'Unknown';
            $friendship = isset($card['friendship_level']) && is_scalar($card['friendship_level']) ? (string) $card['friendship_level'] : '0';
            $lb = isset($card['limit_break_level']) && is_scalar($card['limit_break_level']) ? (string) $card['limit_break_level'] : '0';
            $prompt .= "- {$cardName} ({$spec}): ";
            $prompt .= "Friendship {$friendship}%, ";
            $prompt .= "LB{$lb}\n";
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
     *
     * @param  array<string, mixed>  $hintData
     * @param  array<string, mixed>  $mcpAnalysis
     * @return array<int, array<string, mixed>>
     */
    private function generateRecommendations(array $hintData, array $mcpAnalysis): array
    {
        $recommendations = [];

        $skillHintData = is_array($hintData['skill_hint_data']) ? $hintData['skill_hint_data'] : [];

        // High priority: Skills with 1 hint (one more for max discount)
        /** @var array<int|string, array{hint_count: int, skill_name?: string, base_sp_cost?: int|float}> $oneHintSkills */
        $oneHintSkills = array_filter(
            $skillHintData,
            fn ($skill) => is_array($skill) && isset($skill['hint_count']) && $skill['hint_count'] === 1
        );

        if (! empty($oneHintSkills)) {
            $recommendations[] = [
                'type' => 'urgent',
                'title' => 'One Hint Away from Maximum Discount',
                'description' => 'These skills need just one more hint to reach 40% discount',
                'skills' => array_map(fn ($s) => isset($s['skill_name']) && is_scalar($s['skill_name']) ? (string) $s['skill_name'] : 'Unknown', $oneHintSkills),
                'potential_savings' => (int) array_sum(array_map(
                    fn ($s) => isset($s['base_sp_cost']) && is_numeric($s['base_sp_cost']) ? $s['base_sp_cost'] * 0.2 : 0,
                    $oneHintSkills
                )),
                'priority' => 'high',
            ];
        }

        // Medium priority: Skills with no hints but high SP cost
        /** @var array<int|string, array{hint_count: int, base_sp_cost: int|float, skill_name?: string}> $noHintHighCostSkills */
        $noHintHighCostSkills = array_filter(
            $skillHintData,
            fn ($skill) => is_array($skill) && isset($skill['hint_count'], $skill['base_sp_cost']) && $skill['hint_count'] === 0 && is_numeric($skill['base_sp_cost']) && $skill['base_sp_cost'] >= 180
        );

        if (! empty($noHintHighCostSkills)) {
            $recommendations[] = [
                'type' => 'opportunity',
                'title' => 'High-Value Hint Collection Opportunities',
                'description' => 'Expensive skills with no hints yet - maximum savings potential',
                'skills' => array_map(fn ($s) => isset($s['skill_name']) && is_scalar($s['skill_name']) ? (string) $s['skill_name'] : 'Unknown', $noHintHighCostSkills),
                'potential_savings' => (int) array_sum(array_map(
                    fn ($s) => $s['base_sp_cost'] * 0.4,
                    $noHintHighCostSkills
                )),
                'priority' => 'medium',
            ];
        }

        // Skills ready to acquire (max discount reached)
        /** @var array<int|string, array{max_discount_reached: bool, skill_name?: string, final_sp_cost?: int|float}> $maxDiscountSkills */
        $maxDiscountSkills = array_filter(
            $skillHintData,
            fn ($skill) => is_array($skill) && isset($skill['max_discount_reached']) && $skill['max_discount_reached'] === true
        );

        if (! empty($maxDiscountSkills)) {
            $recommendations[] = [
                'type' => 'ready',
                'title' => 'Skills Ready for Acquisition',
                'description' => 'Maximum discount achieved - optimal time to acquire',
                'skills' => array_map(fn ($s) => isset($s['skill_name']) && is_scalar($s['skill_name']) ? (string) $s['skill_name'] : 'Unknown', $maxDiscountSkills),
                'total_cost' => (int) array_sum(array_map(fn ($s) => isset($s['final_sp_cost']) && is_numeric($s['final_sp_cost']) ? (int) $s['final_sp_cost'] : 0, $maxDiscountSkills)),
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
                'confidence' => $mcpAnalysis['confidence'] ?? 0.0,
                'priority' => 'info',
            ];
        }

        return $recommendations;
    }

    /**
     * Get fallback recommendations when MCP is unavailable.
     *
     * @param  Collection<int, \App\Models\Skill>  $targetSkills
     * @return array<int, array{skill_name: string, recommendation: string, priority: string}>
     */
    private function getFallbackRecommendations(Character $character, Collection $targetSkills): array
    {
        /** @var array<int, array<string, mixed>>|mixed $strategies */
        $strategies = \call_user_func([$this->hintService, 'getHintCollectionStrategy'], $character, $targetSkills);
        if (! is_array($strategies)) {
            return [];
        }

        return array_map(function ($strategy): array {
            if (! is_array($strategy)) {
                return ['skill_name' => 'Unknown', 'recommendation' => '', 'priority' => 'low'];
            }

            return [
                'skill_name' => isset($strategy['skill_name']) && is_string($strategy['skill_name']) ? $strategy['skill_name'] : 'Unknown',
                'recommendation' => isset($strategy['recommendation']) && is_string($strategy['recommendation']) ? $strategy['recommendation'] : '',
                'priority' => isset($strategy['priority']) && is_string($strategy['priority']) ? $strategy['priority'] : 'low',
            ];
        }, $strategies);
    }

    /**
     * Calculate optimal hint collection sequence.
     *
     * @param  Collection<int, \App\Models\Skill>  $targetSkills
     * @return array{sequence: array<int, array<string, mixed>>, total_turns_needed: int, turns_available: int, feasible: bool}
     */
    public function calculateOptimalSequence(
        Character $character,
        Collection $targetSkills,
        int $availableTurns
    ): array {
        $sequence = [];
        $currentTurn = 1;

        // Sort skills by hint collection priority
        $sortedSkills = $targetSkills->sortByDesc(function ($skill) use ($character): int {
            /** @var \App\Models\Skill $skill */
            $hints = $this->hintService->getHintsForSkill($character, $skill);
            $hintCount = $hints->count();

            // Priority score: skills with 1 hint get highest priority
            if ($hintCount === 1) {
                return 1000 + (int) $skill->base_sp_cost;
            }

            // Then skills with 0 hints and high SP cost
            if ($hintCount === 0) {
                return 500 + (int) $skill->base_sp_cost;
            }

            // Already at max discount
            return 0;
        });

        foreach ($sortedSkills as $skill) {
            /** @var \App\Models\Skill $skill */
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
     *
     * @param  Collection<int, \App\Models\Skill>  $acquiredSkills
     * @return array<string, mixed>
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
            /** @var \App\Models\Skill $skill */
            $hints = $this->hintService->getHintsForSkill($character, $skill);
            $hintCount = $hints->count();

            $totalBaseCost += (int) $skill->base_sp_cost;
            $finalCost = $this->hintService->calculateFinalCost($skill, $hintCount);
            $totalFinalCost += $finalCost;
            $totalSpSaved += (int) $skill->base_sp_cost - $finalCost;

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
            : 0.0;

        return [
            'total_skills' => $acquiredSkills->count(),
            'total_base_cost' => $totalBaseCost,
            'total_final_cost' => $totalFinalCost,
            'total_sp_saved' => $totalSpSaved,
            'efficiency_percentage' => round($efficiencyPercentage, 2),
            'skills_with_max_discount' => $skillsWithMaxDiscount,
            'skills_with_partial_discount' => $skillsWithPartialDiscount,
            'skills_with_no_discount' => $skillsWithNoDiscount,
            'optimization_grade' => $this->getOptimizationGrade((float) $efficiencyPercentage),
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
