<?php

namespace App\Services\MCP\Agents;

use App\Models\Character;
use App\Models\Skill;
use App\Services\MCP\MCPClientService;
use App\Services\SkillHintService;
use Illuminate\Support\Collection;

/**
 * Hint Farming Strategy Agent
 *
 * Provides maximum cost reduction planning through strategic hint farming.
 * Analyzes support card configurations, training opportunities, and hint
 * collection patterns to maximize SP savings through optimal hint acquisition.
 */
class HintFarmingStrategyAgent
{
    protected MCPClientService $mcpClient;

    protected SkillHintService $hintService;

    /**
     * Training types and their typical hint provision rates
     *
     * @var array<string, float>
     */
    protected array $trainingHintRates = [
        'speed' => 0.25,
        'stamina' => 0.25,
        'power' => 0.25,
        'guts' => 0.20,
        'wit' => 0.20,
        'pal' => 0.15,
    ];

    public function __construct(MCPClientService $mcpClient, SkillHintService $hintService)
    {
        $this->mcpClient = $mcpClient;
        $this->hintService = $hintService;
    }

    /**
     * Analyze hint farming opportunities and provide strategic recommendations
     *
     * @param  Collection<int, object>  $targetSkills
     * @param  Collection<int, object>  $supportCards
     * @param  array<string, mixed>  $context
     * @return array{
     *     farming_strategy: array<string, mixed>,
     *     training_priorities: array<string, mixed>,
     *     support_card_optimization: array<string, mixed>,
     *     hint_collection_plan: array<string, mixed>,
     *     recommendations: array<string, string>,
     *     confidence: float
     * }
     */
    public function analyzeHintFarmingStrategy(
        Character $character,
        Collection $targetSkills,
        Collection $supportCards,
        array $context = []
    ): array {
        // Determine optimal farming strategy
        $farmingStrategy = $this->determineFarmingStrategy($character, $targetSkills, $supportCards);

        // Calculate training priorities for hint farming
        $trainingPriorities = $this->calculateTrainingPriorities($targetSkills, $supportCards);

        // Optimize support card configuration
        $supportCardOptimization = $this->optimizeSupportCards($targetSkills, $supportCards);

        // Create hint collection plan
        $hintCollectionPlan = $this->createHintCollectionPlan(
            $character,
            $targetSkills,
            $supportCards,
            $context
        );

        // Generate recommendations
        $recommendations = $this->generateRecommendations(
            $farmingStrategy,
            $trainingPriorities,
            $supportCardOptimization
        );

        // Calculate confidence score
        $confidence = $this->calculateConfidence($supportCards, $hintCollectionPlan);

        return [
            'farming_strategy' => $farmingStrategy,
            'training_priorities' => $trainingPriorities,
            'support_card_optimization' => $supportCardOptimization,
            'hint_collection_plan' => $hintCollectionPlan,
            'recommendations' => $recommendations,
            'confidence' => $confidence,
        ];
    }

    /**
     * Determine optimal hint farming strategy
     *
     * @param  Collection<int, object>  $targetSkills
     * @param  Collection<int, object>  $supportCards
     * @return array<string, mixed>
     */
    protected function determineFarmingStrategy(
        Character $character,
        Collection $targetSkills,
        Collection $supportCards
    ): array {
        // Analyze current hint status
        $hintStats = $this->hintService->getHintStatistics($character);

        // Categorize skills by hint status
        $skillsByHintStatus = $this->categorizeSkillsByHintStatus($character, $targetSkills);

        // Determine strategy type
        $strategyType = $this->determineStrategyType($skillsByHintStatus, $hintStats);

        // Calculate farming intensity
        $farmingIntensity = $this->calculateFarmingIntensity($skillsByHintStatus);

        // Estimate farming duration
        $estimatedDuration = $this->estimateFarmingDuration($skillsByHintStatus, $supportCards);

        return [
            'strategy_type' => $strategyType,
            'farming_intensity' => $farmingIntensity,
            'estimated_duration' => $estimatedDuration,
            'skills_by_status' => $skillsByHintStatus,
            'priority_focus' => $this->determinePriorityFocus($skillsByHintStatus),
        ];
    }

    /**
     * Categorize skills by hint status
     *
     * @param  Collection<int, object>  $targetSkills
     * @return array<string, array<int, array<string, mixed>>>
     */
    protected function categorizeSkillsByHintStatus(Character $character, Collection $targetSkills): array
    {
        /** @var array<string, array<int, array<string, mixed>>> $categories */
        $categories = [
            'max_discount' => [],
            'one_hint_away' => [],
            'partial_hints' => [],
            'no_hints' => [],
        ];

        foreach ($targetSkills as $skill) {
            if (! $skill instanceof Skill) {
                continue;
            }

            $hints = $this->hintService->getHintsForSkill($character, $skill);
            $hintCount = $hints->count();

            $skillId = $skill->id ?? 0;
            $skillName = $skill->name ?? 'Unknown';
            $baseCost = $skill->base_sp_cost ?? 0;

            /** @var array<string, mixed> $skillData */
            $skillData = [
                'skill_id' => $skillId,
                'skill_name' => (string) $skillName,
                'base_cost' => (int) $baseCost,
                'hint_count' => $hintCount,
            ];

            if ($hintCount >= 2) {
                $categories['max_discount'][] = $skillData;
            } elseif ($hintCount === 1) {
                $categories['one_hint_away'][] = $skillData;
            } else {
                $categories['no_hints'][] = $skillData;
            }
        }

        return $categories;
    }

    /**
     * Determine strategy type based on skill distribution
     *
     * @param  array<string, array<int, array<string, mixed>>>  $skillsByHintStatus
     * @param  array<string, mixed>  $hintStats
     */
    protected function determineStrategyType(array $skillsByHintStatus, array $hintStats): string
    {
        $maxDiscountCount = count($skillsByHintStatus['max_discount']);
        $oneHintAwayCount = count($skillsByHintStatus['one_hint_away']);
        $noHintsCount = count($skillsByHintStatus['no_hints']);

        // Aggressive farming: Many skills with no hints
        if ($noHintsCount > 5) {
            return 'aggressive_farming';
        }

        // Focused farming: Many skills one hint away
        if ($oneHintAwayCount > 3) {
            return 'focused_completion';
        }

        // Maintenance farming: Most skills have max discount
        if ($maxDiscountCount > $oneHintAwayCount + $noHintsCount) {
            return 'maintenance';
        }

        // Balanced farming: Mixed distribution
        return 'balanced_farming';
    }

    /**
     * Calculate farming intensity (0.0 = low, 1.0 = high)
     *
     * @param  array<string, array<int, array<string, mixed>>>  $skillsByHintStatus
     */
    protected function calculateFarmingIntensity(array $skillsByHintStatus): float
    {
        $totalSkills = array_sum(array_map('count', $skillsByHintStatus));

        if ($totalSkills === 0) {
            return 0.0;
        }

        $noHintsCount = count($skillsByHintStatus['no_hints']);
        $oneHintAwayCount = count($skillsByHintStatus['one_hint_away']);

        // High intensity if many skills need hints
        $needsHints = $noHintsCount + $oneHintAwayCount;
        $intensity = $needsHints / $totalSkills;

        return round($intensity, 2);
    }

    /**
     * Estimate farming duration in turns
     *
     * @param  array<string, array<int, array<string, mixed>>>  $skillsByHintStatus
     * @param  Collection<int, object>  $supportCards
     */
    protected function estimateFarmingDuration(array $skillsByHintStatus, Collection $supportCards): int
    {
        // Calculate total hints needed
        $hintsNeeded = 0;
        $hintsNeeded += count($skillsByHintStatus['one_hint_away']); // 1 hint each
        $hintsNeeded += count($skillsByHintStatus['no_hints']) * 2; // 2 hints each

        if ($hintsNeeded === 0) {
            return 0;
        }

        // Estimate hint acquisition rate based on support cards
        $avgHintRate = $this->calculateAverageHintRate($supportCards);

        // Estimate turns needed
        $turnsNeeded = (int) ceil($hintsNeeded / max(0.1, $avgHintRate));

        return $turnsNeeded;
    }

    /**
     * Calculate average hint acquisition rate per turn
     *
     * @param  Collection<int, object>  $supportCards
     */
    protected function calculateAverageHintRate(Collection $supportCards): float
    {
        if ($supportCards->isEmpty()) {
            return 0.5; // Default conservative rate
        }

        // Base rate from support cards
        $baseRate = 0.3;

        // Bonus from high friendship levels
        $avgFriendship = $supportCards->avg('friendship_level') ?? 0;
        $friendshipBonus = ((float) $avgFriendship / 100) * 0.2;

        // Bonus from limit breaks
        $avgLimitBreak = $supportCards->avg('limit_break_level') ?? 0;
        $limitBreakBonus = ((float) $avgLimitBreak / 4) * 0.1;

        return $baseRate + $friendshipBonus + $limitBreakBonus;
    }

    /**
     * Determine priority focus for farming
     *
     * @param  array<string, array<int, array<string, mixed>>>  $skillsByHintStatus
     */
    protected function determinePriorityFocus(array $skillsByHintStatus): string
    {
        $oneHintAwayCount = count($skillsByHintStatus['one_hint_away']);
        $noHintsCount = count($skillsByHintStatus['no_hints']);

        if ($oneHintAwayCount > 0) {
            return 'complete_partial_hints';
        }

        if ($noHintsCount > 0) {
            return 'start_new_hints';
        }

        return 'maintain_current_status';
    }

    /**
     * Calculate training priorities for hint farming
     *
     * @param  Collection<int, object>  $targetSkills
     * @param  Collection<int, object>  $supportCards
     * @return array<string, mixed>
     */
    protected function calculateTrainingPriorities(Collection $targetSkills, Collection $supportCards): array
    {
        // Map skills to training types
        $skillsByTrainingType = $this->mapSkillsToTrainingTypes($targetSkills);

        // Calculate priority scores for each training type
        /** @var array<string, float> $trainingScores */
        $trainingScores = [];
        foreach ($skillsByTrainingType as $trainingType => $skills) {
            $score = $this->calculateTrainingTypeScore($trainingType, $skills, $supportCards);
            $trainingScores[$trainingType] = $score;
        }

        // Sort by score (highest first)
        arsort($trainingScores);

        // Create priority list
        /** @var array<int, array<string, mixed>> $priorities */
        $priorities = [];
        $rank = 0;
        foreach ($trainingScores as $trainingType => $score) {
            $rank++;
            $priorities[] = [
                'rank' => $rank,
                'training_type' => $trainingType,
                'score' => $score,
                'skill_count' => count($skillsByTrainingType[$trainingType] ?? []),
                'recommendation' => $this->getTrainingRecommendation($trainingType, $score),
            ];
        }

        return [
            'priorities' => $priorities,
            'top_training_type' => $priorities[0]['training_type'] ?? 'speed',
            'skills_by_training_type' => $skillsByTrainingType,
        ];
    }

    /**
     * Map skills to their associated training types
     *
     * @param  Collection<int, object>  $targetSkills
     * @return array<string, array<int, array<string, mixed>>>
     */
    protected function mapSkillsToTrainingTypes(Collection $targetSkills): array
    {
        /** @var array<string, array<int, array<string, mixed>>> $mapping */
        $mapping = [];

        foreach ($targetSkills as $skill) {
            // Determine training type from skill category or name
            $trainingType = $this->inferTrainingType($skill);

            if (! isset($mapping[$trainingType])) {
                $mapping[$trainingType] = [];
            }

            $skillId = property_exists($skill, 'id') ? $skill->id : 0;
            $skillName = property_exists($skill, 'name') ? $skill->name : 'Unknown';
            $skillBaseCost = property_exists($skill, 'base_sp_cost') ? $skill->base_sp_cost : 0;

            $mapping[$trainingType][] = [
                'skill_id' => is_numeric($skillId) ? (int) $skillId : 0,
                'skill_name' => is_string($skillName) ? $skillName : 'Unknown',
                'base_cost' => is_numeric($skillBaseCost) ? (int) $skillBaseCost : 0,
            ];
        }

        return $mapping;
    }

    /**
     * Infer training type from skill
     */
    protected function inferTrainingType(object $skill): string
    {
        // Check skill category if available
        $category = $skill->category ?? null;
        if (is_string($category)) {
            $categoryLower = strtolower($category);

            return match (true) {
                str_contains($categoryLower, 'speed') => 'speed',
                str_contains($categoryLower, 'stamina') => 'stamina',
                str_contains($categoryLower, 'power') => 'power',
                str_contains($categoryLower, 'guts') => 'guts',
                str_contains($categoryLower, 'wit') => 'wit',
                default => 'speed',
            };
        }

        // Default to speed
        return 'speed';
    }

    /**
     * Calculate score for a training type
     *
     * @param  array<int, array<string, mixed>>  $skills
     * @param  Collection<int, object>  $supportCards
     */
    protected function calculateTrainingTypeScore(
        string $trainingType,
        array $skills,
        Collection $supportCards
    ): float {
        $score = 0.0;

        // Base score from number of skills
        $score += count($skills) * 10;

        // Bonus from high-cost skills
        foreach ($skills as $skill) {
            $baseCostValue = $skill['base_cost'] ?? 0;
            $baseCost = is_numeric($baseCostValue) ? (int) $baseCostValue : 0;
            if ($baseCost >= 180) {
                $score += 5;
            }
        }

        // Bonus from support cards of matching type
        $matchingCards = $supportCards->filter(
            fn ($card) => strtolower((string) ($card->specialization ?? '')) === $trainingType
        );
        $score += $matchingCards->count() * 15;

        // Bonus from high friendship matching cards
        foreach ($matchingCards as $card) {
            $friendshipLevel = $card->friendship_level ?? 0;
            if ($friendshipLevel >= 80) {
                $score += 10;
            }
        }

        return $score;
    }

    /**
     * Get training recommendation based on score
     */
    protected function getTrainingRecommendation(string $trainingType, float $score): string
    {
        if ($score >= 50) {
            return "High priority - {$trainingType} training provides excellent hint opportunities";
        }

        if ($score >= 30) {
            return "Medium priority - {$trainingType} training offers good hint collection potential";
        }

        return "Low priority - {$trainingType} training has limited hint opportunities";
    }

    /**
     * Optimize support card configuration for hint farming
     *
     * @param  Collection<int, object>  $targetSkills
     * @param  Collection<int, object>  $supportCards
     * @return array<string, mixed>
     */
    protected function optimizeSupportCards(Collection $targetSkills, Collection $supportCards): array
    {
        // Analyze current deck
        $deckAnalysis = $this->analyzeDeck($supportCards);

        // Identify gaps in hint coverage
        $hintCoverageGaps = $this->identifyHintCoverageGaps($targetSkills, $supportCards);

        // Generate optimization suggestions
        $optimizationSuggestions = $this->generateOptimizationSuggestions(
            $deckAnalysis,
            $hintCoverageGaps
        );

        return [
            'deck_analysis' => $deckAnalysis,
            'hint_coverage_gaps' => $hintCoverageGaps,
            'optimization_suggestions' => $optimizationSuggestions,
            'optimization_score' => $this->calculateDeckOptimizationScore($deckAnalysis, $hintCoverageGaps),
        ];
    }

    /**
     * Analyze support card deck
     *
     * @param  Collection<int, object>  $supportCards
     * @return array<string, mixed>
     */
    protected function analyzeDeck(Collection $supportCards): array
    {
        /** @var array<string, int> $specializationCounts */
        $specializationCounts = [];
        $avgFriendship = (float) ($supportCards->avg('friendship_level') ?? 0);
        $avgLimitBreak = (float) ($supportCards->avg('limit_break_level') ?? 0);

        foreach ($supportCards as $card) {
            $spec = (string) ($card->specialization ?? 'unknown');
            $specializationCounts[$spec] = ($specializationCounts[$spec] ?? 0) + 1;
        }

        return [
            'total_cards' => $supportCards->count(),
            'specialization_distribution' => $specializationCounts,
            'avg_friendship_level' => round($avgFriendship, 1),
            'avg_limit_break_level' => round($avgLimitBreak, 1),
            'high_friendship_cards' => $supportCards->filter(fn ($c) => ($c->friendship_level ?? 0) >= 80)->count(),
        ];
    }

    /**
     * Identify gaps in hint coverage
     *
     * @param  Collection<int, object>  $targetSkills
     * @param  Collection<int, object>  $supportCards
     * @return array<int, array<string, mixed>>
     */
    protected function identifyHintCoverageGaps(Collection $targetSkills, Collection $supportCards): array
    {
        /** @var array<int, array<string, mixed>> $gaps */
        $gaps = [];

        // Check coverage for each training type
        $skillsByType = $this->mapSkillsToTrainingTypes($targetSkills);

        foreach ($skillsByType as $trainingType => $skills) {
            $matchingCards = $supportCards->filter(
                fn ($card) => strtolower((string) ($card->specialization ?? '')) === $trainingType
            );

            if ($matchingCards->isEmpty()) {
                $gaps[] = [
                    'training_type' => $trainingType,
                    'skill_count' => count($skills),
                    'severity' => 'critical',
                    'recommendation' => "Add {$trainingType} support cards to improve hint collection",
                ];
            } elseif ($matchingCards->count() === 1) {
                $gaps[] = [
                    'training_type' => $trainingType,
                    'skill_count' => count($skills),
                    'severity' => 'moderate',
                    'recommendation' => "Consider adding more {$trainingType} support cards",
                ];
            }
        }

        return $gaps;
    }

    /**
     * Generate optimization suggestions
     *
     * @param  array<string, mixed>  $deckAnalysis
     * @param  array<int, array<string, mixed>>  $hintCoverageGaps
     * @return array<int, string>
     */
    protected function generateOptimizationSuggestions(array $deckAnalysis, array $hintCoverageGaps): array
    {
        /** @var array<int, string> $suggestions */
        $suggestions = [];

        // Friendship level suggestions
        $avgFriendshipValue = $deckAnalysis['avg_friendship_level'] ?? 0;
        $avgFriendship = is_numeric($avgFriendshipValue) ? (float) $avgFriendshipValue : 0.0;
        if ($avgFriendship < 60) {
            $suggestions[] = 'Increase friendship levels through training to improve hint acquisition rates';
        }

        // Coverage gap suggestions
        foreach ($hintCoverageGaps as $gap) {
            $severityValue = $gap['severity'] ?? '';
            $severity = is_string($severityValue) ? $severityValue : '';
            $recommendationValue = $gap['recommendation'] ?? '';
            $recommendation = is_string($recommendationValue) ? $recommendationValue : '';
            if ($severity === 'critical' && $recommendation !== '') {
                $suggestions[] = $recommendation;
            }
        }

        // Specialization balance suggestions
        /** @var array<string, int> $distribution */
        $distribution = is_array($deckAnalysis['specialization_distribution'] ?? null)
            ? $deckAnalysis['specialization_distribution']
            : [];
        if (count($distribution) < 3) {
            $suggestions[] = 'Diversify support card specializations for broader hint coverage';
        }

        return $suggestions;
    }

    /**
     * Calculate deck optimization score
     *
     * @param  array<string, mixed>  $deckAnalysis
     * @param  array<int, array<string, mixed>>  $hintCoverageGaps
     */
    protected function calculateDeckOptimizationScore(array $deckAnalysis, array $hintCoverageGaps): int
    {
        $score = 100;

        // Deduct for low friendship
        $avgFriendshipValue = $deckAnalysis['avg_friendship_level'] ?? 0;
        $avgFriendship = is_numeric($avgFriendshipValue) ? (float) $avgFriendshipValue : 0.0;
        if ($avgFriendship < 80) {
            $score -= (int) ((80 - $avgFriendship) / 2);
        }

        // Deduct for coverage gaps
        foreach ($hintCoverageGaps as $gap) {
            $severityValue = $gap['severity'] ?? '';
            $severity = is_string($severityValue) ? $severityValue : '';
            if ($severity === 'critical') {
                $score -= 20;
            } elseif ($severity === 'moderate') {
                $score -= 10;
            }
        }

        return max(0, $score);
    }

    /**
     * Create hint collection plan
     *
     * @param  Collection<int, object>  $targetSkills
     * @param  Collection<int, object>  $supportCards
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function createHintCollectionPlan(
        Character $character,
        Collection $targetSkills,
        Collection $supportCards,
        array $context
    ): array {
        $turnsAvailableValue = $context['turns_available'] ?? 20;
        $turnsAvailable = is_numeric($turnsAvailableValue) ? (int) $turnsAvailableValue : 20;

        // Categorize skills by hint status
        $skillsByStatus = $this->categorizeSkillsByHintStatus($character, $targetSkills);

        // Create turn-by-turn plan
        /** @var array<int, array<string, mixed>> $plan */
        $plan = [];
        $currentTurn = 1;

        // Priority 1: Complete skills one hint away
        foreach ($skillsByStatus['one_hint_away'] as $skill) {
            if ($currentTurn > $turnsAvailable) {
                break;
            }

            $skillNameValue = $skill['skill_name'] ?? 'Unknown';
            $skillName = is_string($skillNameValue) ? $skillNameValue : 'Unknown';
            $trainingType = $this->inferTrainingType((object) $skill);
            $plan[] = [
                'turn' => $currentTurn,
                'action' => 'collect_hint',
                'skill_name' => $skillName,
                'training_type' => $trainingType,
                'priority' => 'high',
                'reason' => 'Complete maximum discount',
            ];
            $currentTurn++;
        }

        // Priority 2: Start hints for high-cost skills
        $highCostNoHints = array_filter(
            $skillsByStatus['no_hints'],
            function ($skill): bool {
                $baseCostValue = $skill['base_cost'] ?? 0;

                return is_numeric($baseCostValue) && (int) $baseCostValue >= 180;
            }
        );

        foreach ($highCostNoHints as $skill) {
            if ($currentTurn > $turnsAvailable) {
                break;
            }

            $skillNameValue = $skill['skill_name'] ?? 'Unknown';
            $skillName = is_string($skillNameValue) ? $skillNameValue : 'Unknown';
            $trainingType = $this->inferTrainingType((object) $skill);
            $plan[] = [
                'turn' => $currentTurn,
                'action' => 'collect_hint',
                'skill_name' => $skillName,
                'training_type' => $trainingType,
                'priority' => 'medium',
                'reason' => 'High SP cost - maximize savings potential',
            ];
            $currentTurn++;
        }

        return [
            'plan' => $plan,
            'total_turns_planned' => count($plan),
            'turns_available' => $turnsAvailable,
            'feasibility' => count($plan) <= $turnsAvailable ? 'feasible' : 'requires_more_turns',
            'estimated_sp_savings' => $this->estimatePlanSavings($plan, $skillsByStatus),
        ];
    }

    /**
     * Estimate SP savings from hint collection plan
     *
     * @param  array<int, array<string, mixed>>  $plan
     * @param  array<string, array<int, array<string, mixed>>>  $skillsByStatus
     */
    protected function estimatePlanSavings(array $plan, array $skillsByStatus): int
    {
        $savings = 0;

        // Savings from completing one-hint-away skills
        foreach ($skillsByStatus['one_hint_away'] as $skill) {
            $baseCostValue = $skill['base_cost'] ?? 0;
            $baseCost = is_numeric($baseCostValue) ? (int) $baseCostValue : 0;
            $savings += (int) ($baseCost * 0.2);
        }

        return $savings;
    }

    /**
     * Generate farming recommendations
     *
     * @param  array<string, mixed>  $farmingStrategy
     * @param  array<string, mixed>  $trainingPriorities
     * @param  array<string, mixed>  $supportCardOptimization
     * @return array<string, string>
     */
    protected function generateRecommendations(
        array $farmingStrategy,
        array $trainingPriorities,
        array $supportCardOptimization
    ): array {
        /** @var array<string, string> $recommendations */
        $recommendations = [];

        // Strategy recommendations
        $strategyTypeValue = $farmingStrategy['strategy_type'] ?? 'balanced_farming';
        $strategyType = is_string($strategyTypeValue) ? $strategyTypeValue : 'balanced_farming';
        $recommendations['strategy'] = match ($strategyType) {
            'aggressive_farming' => 'Focus heavily on hint collection before skill acquisition',
            'focused_completion' => 'Prioritize completing partial hints for maximum discount',
            'maintenance' => 'Maintain current hint collection rate while acquiring skills',
            default => 'Balance hint collection with skill acquisition',
        };

        // Training priority recommendations
        $topTrainingValue = $trainingPriorities['top_training_type'] ?? 'speed';
        $topTraining = is_string($topTrainingValue) ? $topTrainingValue : 'speed';
        $recommendations['training'] = "Prioritize {$topTraining} training for optimal hint collection";

        // Support card recommendations
        $deckScoreValue = $supportCardOptimization['optimization_score'] ?? 0;
        $deckScore = is_numeric($deckScoreValue) ? (int) $deckScoreValue : 0;
        if ($deckScore < 70) {
            $recommendations['support_cards'] = 'Optimize support card deck to improve hint collection efficiency';
        }

        // Duration recommendations
        $durationValue = $farmingStrategy['estimated_duration'] ?? 0;
        $duration = is_numeric($durationValue) ? (int) $durationValue : 0;
        if ($duration > 15) {
            $recommendations['duration'] = "Hint farming will require approximately {$duration} turns - plan accordingly";
        }

        return $recommendations;
    }

    /**
     * Calculate confidence score for recommendations
     *
     * @param  Collection<int, object>  $supportCards
     * @param  array<string, mixed>  $hintCollectionPlan
     */
    protected function calculateConfidence(Collection $supportCards, array $hintCollectionPlan): float
    {
        $confidence = 1.0;

        // Reduce confidence if support card deck is weak
        if ($supportCards->count() < 4) {
            $confidence *= 0.7;
        }

        // Reduce confidence if plan is not feasible
        $feasibilityValue = $hintCollectionPlan['feasibility'] ?? '';
        $feasibility = is_string($feasibilityValue) ? $feasibilityValue : '';
        if ($feasibility !== 'feasible') {
            $confidence *= 0.8;
        }

        // Reduce confidence if average friendship is low
        $avgFriendship = (float) ($supportCards->avg('friendship_level') ?? 0);
        if ($avgFriendship < 50) {
            $confidence *= 0.9;
        }

        return round($confidence, 2);
    }
}
