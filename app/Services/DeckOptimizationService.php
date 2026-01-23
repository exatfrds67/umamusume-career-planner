<?php

namespace App\Services;

use App\Models\Character;
use App\Models\SupportCardDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Deck Optimization Service
 * Handles deck composition analysis, synergy calculations,
 * meta tier optimization, and deck recommendations
 */
class DeckOptimizationService
{
    /**
     * Cache duration for optimization data (1 hour)
     */
    private const CACHE_DURATION = 3600;

    /**
     * Minimum recommended cards per stat type
     */
    private const MIN_CARDS_PER_STAT = 1;

    /**
     * Optimal stat coverage threshold (percentage)
     */
    private const OPTIMAL_STAT_COVERAGE = 80;

    /**
     * Meta tier weights for optimization
     */
    private const META_TIER_WEIGHTS = [
        'S+' => 5.0,
        'S' => 4.0,
        'A' => 3.0,
        'B' => 2.0,
        'C' => 1.0,
    ];

    public function __construct(
        private SupportCardMetaService $metaService,
        private DeckManagementService $deckService,
        private FriendshipBondService $friendshipService
    ) {}

    /**
     * Analyze deck composition for stat coverage and skill provision gaps
     *
     * @return array<string, mixed>
     */
    public function analyzeDeckComposition(): array
        $deck = $this->deckService->getDeck($characterId);

        if ($deck->isEmpty()) {
            return [
                'stat_coverage' => [],
                'skill_provision_coverage' => [],
                'gaps' => [
                    'missing_stats' => ['speed', 'stamina', 'power', 'guts', 'wit'],
                    'missing_skills' => [],
                ],
                'coverage_score' => 0,
                'recommendations' => ['Add cards to your deck to begin analysis'],
            ];
        }

        $statCoverage = $this->calculateStatCoverage($deck);
        $skillProvisionCoverage = $this->calculateSkillProvisionCoverage($deck);
        $gaps = $this->identifyGaps($statCoverage, $skillProvisionCoverage);
        $coverageScore = $this->calculateCoverageScore($statCoverage, $skillProvisionCoverage);

        return [
            'stat_coverage' => $statCoverage,
            'skill_provision_coverage' => $skillProvisionCoverage,
            'gaps' => $gaps,
            'coverage_score' => $coverageScore,
            'recommendations' => $this->generateCompositionRecommendations($gaps, $coverageScore),
        ];
    }

    /**
     * Calculate stat coverage from deck
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CharacterSupportCard>  $deck
     * @return array<string, mixed>
     */
    private function calculateStatCoverage(): array
        /** @var array<string, int> $coverage */
        $coverage = [
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wit' => 0,
            'friend' => 0,
        ];

        foreach ($deck as $card) {
            $cardType = strtolower($card->supportCard->card_type ?? '');
            if (isset($coverage[$cardType])) {
                $coverage[$cardType]++;
            }
        }

        return [
            'counts' => $coverage,
            'percentages' => array_map(fn ($count) => round(($count / 6) * 100, 1), $coverage),
            'is_balanced' => $this->isStatCoverageBalanced($coverage),
        ];
    }

    /**
     * Check if stat coverage is balanced
     */
    private function isStatCoverageBalanced(array $coverage): bool
    {
        // Remove friend type from balance check
        $statCounts = array_filter($coverage, fn ($key) => $key !== 'friend', ARRAY_FILTER_USE_KEY);

        // Check if at least 3 different stat types are covered
        $coveredStats = count(array_filter($statCounts, fn ($count) => $count > 0));

        return $coveredStats >= 3;
    }

    /**
     * Calculate skill provision coverage
     */
    private function calculateSkillProvisionCoverage(): array
        $allSkills = [];
        $skillsByType = [
            'speed' => [],
            'passive' => [],
            'recovery' => [],
            'debuff' => [],
        ];

        foreach ($deck as $card) {
            $skillsProvided = $card->supportCard->skill_hints_provided ?? [];

            foreach ($skillsProvided as $skill) {
                if (! in_array($skill, $allSkills)) {
                    $allSkills[] = $skill;
                }

                // Categorize skills (simplified - would need skill database for accurate categorization)
                $skillType = $this->categorizeSkill($skill);
                if (! in_array($skill, $skillsByType[$skillType])) {
                    $skillsByType[$skillType][] = $skill;
                }
            }
        }

        return [
            'total_skills' => count($allSkills),
            'skills_by_type' => $skillsByType,
            'skill_diversity_score' => $this->calculateSkillDiversityScore($skillsByType),
        ];
    }

    /**
     * Categorize skill by type (simplified)
     */
    private function categorizeSkill(string $skillName): string
    {
        // Simplified categorization - would need full skill database
        $skillLower = strtolower($skillName);

        if (str_contains($skillLower, 'speed') || str_contains($skillLower, 'acceleration')) {
            return 'speed';
        }

        if (str_contains($skillLower, 'recover') || str_contains($skillLower, 'heal')) {
            return 'recovery';
        }

        if (str_contains($skillLower, 'debuff') || str_contains($skillLower, 'hinder')) {
            return 'debuff';
        }

        return 'passive';
    }

    /**
     * Calculate skill diversity score
     */
    private function calculateSkillDiversityScore(array $skillsByType): float
    {
        $typesWithSkills = count(array_filter($skillsByType, fn ($skills) => count($skills) > 0));
        $totalTypes = count($skillsByType);

        return round(($typesWithSkills / $totalTypes) * 100, 1);
    }

    /**
     * Identify gaps in deck composition
     */
    private function identifyGaps(): array
        $missingStats = [];
        $weakStats = [];

        foreach ($statCoverage['counts'] as $stat => $count) {
            if ($stat === 'friend') {
                continue;
            }

            if ($count === 0) {
                $missingStats[] = $stat;
            } elseif ($count < self::MIN_CARDS_PER_STAT) {
                $weakStats[] = $stat;
            }
        }

        $missingSkillTypes = [];
        foreach ($skillProvisionCoverage['skills_by_type'] as $type => $skills) {
            if (empty($skills)) {
                $missingSkillTypes[] = $type;
            }
        }

        return [
            'missing_stats' => $missingStats,
            'weak_stats' => $weakStats,
            'missing_skill_types' => $missingSkillTypes,
            'has_critical_gaps' => ! empty($missingStats) || count($weakStats) > 2,
        ];
    }

    /**
     * Calculate overall coverage score
     */
    private function calculateCoverageScore(array $statCoverage, array $skillProvisionCoverage): float
    {
        // Stat coverage score (60% weight)
        $statScore = $statCoverage['is_balanced'] ? 60 : 30;

        // Skill diversity score (40% weight)
        $skillScore = ($skillProvisionCoverage['skill_diversity_score'] / 100) * 40;

        return round($statScore + $skillScore, 1);
    }

    /**
     * Generate composition recommendations
     */
    private function generateCompositionRecommendations(): array
        $recommendations = [];

        if ($coverageScore >= self::OPTIMAL_STAT_COVERAGE) {
            $recommendations[] = 'Excellent deck composition! Your stat coverage and skill diversity are well-balanced.';
        } elseif ($coverageScore >= 60) {
            $recommendations[] = 'Good deck composition with room for optimization.';
        } else {
            $recommendations[] = 'Deck composition needs improvement for optimal performance.';
        }

        if (! empty($gaps['missing_stats'])) {
            $recommendations[] = 'Add cards for missing stat types: '.implode(', ', $gaps['missing_stats']);
        }

        if (! empty($gaps['weak_stats'])) {
            $recommendations[] = 'Consider adding more cards for: '.implode(', ', $gaps['weak_stats']);
        }

        if (! empty($gaps['missing_skill_types'])) {
            $recommendations[] = 'Improve skill diversity by adding '.implode(', ', $gaps['missing_skill_types']).' skills';
        }

        return $recommendations;
    }

    /**
     * Analyze synergy between cards in the deck
     */
    public function analyzeDeckSynergy(): array
        $deck = $this->deckService->getDeck($characterId);

        if ($deck->isEmpty()) {
            return [
                'synergy_score' => 0,
                'synergy_pairs' => [],
                'strategic_alignment' => [],
                'recommendations' => ['Add cards to analyze synergy'],
            ];
        }

        $synergyPairs = $this->identifySynergyPairs($deck);
        $strategicAlignment = $this->analyzeStrategicAlignment($deck);
        $synergyScore = $this->calculateSynergyScore($synergyPairs, $strategicAlignment);

        return [
            'synergy_score' => $synergyScore,
            'synergy_pairs' => $synergyPairs,
            'strategic_alignment' => $strategicAlignment,
            'recommendations' => $this->generateSynergyRecommendations($synergyPairs, $strategicAlignment),
        ];
    }

    /**
     * Identify synergy pairs in the deck
     */
    private function identifySynergyPairs(): array
        $pairs = [];

        foreach ($deck as $card1) {
            $synergyData = $this->metaService->getCardSynergies($card1->support_card_id);

            foreach ($synergyData['synergy_cards'] as $synergyCard) {
                // Check if synergy card is in the deck
                $matchingCard = $deck->first(function ($card2) use ($synergyCard) {
                    return $card2->support_card_id === $synergyCard['id'];
                });

                if ($matchingCard) {
                    $pairKey = min($card1->support_card_id, $matchingCard->support_card_id).'-'.
                        max($card1->support_card_id, $matchingCard->support_card_id);

                    if (! isset($pairs[$pairKey])) {
                        $pairs[$pairKey] = [
                            'card1' => $card1->supportCard->name,
                            'card2' => $matchingCard->supportCard->name,
                            'synergy_type' => 'deck_synergy',
                            'strength' => 'high',
                        ];
                    }
                }
            }
        }

        return array_values($pairs);
    }

    /**
     * Analyze strategic alignment of the deck
     */
    private function analyzeStrategicAlignment(): array
        $cardTypes = $deck->pluck('supportCard.card_type')->countBy()->toArray();
        $metaTiers = $deck->pluck('supportCard.meta_tier')->countBy()->toArray();

        // Determine primary strategy based on card type distribution
        $primaryStrategy = $this->determinePrimaryStrategy($cardTypes);

        // Calculate alignment score
        $alignmentScore = $this->calculateAlignmentScore($cardTypes, $primaryStrategy);

        return [
            'primary_strategy' => $primaryStrategy,
            'card_type_distribution' => $cardTypes,
            'meta_tier_distribution' => $metaTiers,
            'alignment_score' => $alignmentScore,
            'is_well_aligned' => $alignmentScore >= 70,
        ];
    }

    /**
     * Determine primary strategy from card types
     */
    private function determinePrimaryStrategy(array $cardTypes): string
    {
        if (empty($cardTypes)) {
            return 'undefined';
        }

        arsort($cardTypes);
        $dominantType = array_key_first($cardTypes);
        $dominantCount = $cardTypes[$dominantType];

        if ($dominantCount >= 3) {
            return ucfirst($dominantType).' Focus';
        }

        if (count($cardTypes) >= 4) {
            return 'Balanced';
        }

        return 'Mixed';
    }

    /**
     * Calculate strategic alignment score
     */
    private function calculateAlignmentScore(array $cardTypes, string $primaryStrategy): float
    {
        if ($primaryStrategy === 'undefined') {
            return 0;
        }

        // Balanced strategy gets bonus for diversity
        if ($primaryStrategy === 'Balanced') {
            $diversity = count($cardTypes);

            return min(100, $diversity * 20);
        }

        // Focused strategy gets bonus for concentration
        if (str_contains($primaryStrategy, 'Focus')) {
            if (empty($cardTypes)) {
                return 0;
            }

            $maxCount = max($cardTypes);

            return min(100, ($maxCount / 6) * 100);
        }

        // Mixed strategy gets moderate score
        return 60;
    }

    /**
     * Calculate overall synergy score
     */
    private function calculateSynergyScore(array $synergyPairs, array $strategicAlignment): float
    {
        // Synergy pairs contribute 50%
        $pairScore = min(50, count($synergyPairs) * 10);

        // Strategic alignment contributes 50%
        $alignmentScore = ($strategicAlignment['alignment_score'] / 100) * 50;

        return round($pairScore + $alignmentScore, 1);
    }

    /**
     * Generate synergy recommendations
     */
    private function generateSynergyRecommendations(): array
        $recommendations = [];

        if (count($synergyPairs) === 0) {
            $recommendations[] = 'No synergy pairs detected. Consider adding cards with known synergies.';
        } elseif (count($synergyPairs) >= 2) {
            $recommendations[] = 'Excellent synergy! Your deck has '.count($synergyPairs).' synergy pairs.';
        } else {
            $recommendations[] = 'Good synergy detected. Consider adding more synergistic cards.';
        }

        if (! $strategicAlignment['is_well_aligned']) {
            $recommendations[] = 'Strategic alignment could be improved. Current strategy: '.$strategicAlignment['primary_strategy'];
        }

        return $recommendations;
    }

    /**
     * Optimize deck for meta tier and character build compatibility
     */
    public function optimizeForMetaTier(): array
        $character = Character::findOrFail($characterId);
        $deck = $this->deckService->getDeck($characterId);

        $metaScore = $this->calculateMetaScore($deck);
        $buildCompatibility = $this->analyzeBuildCompatibility($character, $deck);
        $optimizationSuggestions = $this->generateOptimizationSuggestions($character, $deck, $metaScore, $buildCompatibility);

        return [
            'meta_score' => $metaScore,
            'build_compatibility' => $buildCompatibility,
            'optimization_suggestions' => $optimizationSuggestions,
            'recommended_replacements' => $this->suggestReplacements($character, $deck),
        ];
    }

    /**
     * Calculate meta score for the deck
     */
    private function calculateMetaScore(): array
        if ($deck->isEmpty()) {
            return [
                'total_score' => 0,
                'average_tier_weight' => 0,
                'tier_breakdown' => [],
            ];
        }

        $totalWeight = 0;
        $tierBreakdown = [];

        foreach ($deck as $card) {
            $tier = $card->supportCard->meta_tier ?? 'C';
            $weight = self::META_TIER_WEIGHTS[$tier] ?? 1.0;
            $totalWeight = ($totalWeight ?? 0) + $weight;

            if (! isset($tierBreakdown[$tier])) {
                $tierBreakdown[$tier] = 0;
            }
            $tierBreakdown[$tier]++;
        }

        $averageWeight = $totalWeight / $deck->count();
        $normalizedScore = ($averageWeight / 5.0) * 100; // Normalize to 0-100

        return [
            'total_score' => round($normalizedScore, 1),
            'average_tier_weight' => round($averageWeight, 2),
            'tier_breakdown' => $tierBreakdown,
        ];
    }

    /**
     * Analyze build compatibility between character and deck
     */
    private function analyzeBuildCompatibility(): array
        $statPriorities = $character->stat_priorities ?? [];
        $scenario = $character->scenario_type;

        $compatibilityScore = 0;
        $matches = [];
        $mismatches = [];

        foreach ($deck as $card) {
            $cardType = strtolower($card->supportCard->card_type ?? '');

            // Check if card type matches stat priorities
            if (in_array($cardType, array_map('strtolower', array_keys($statPriorities)))) {
                $compatibilityScore = ($compatibilityScore ?? 0) + 20;
                $matches[] = $card->supportCard->name.' ('.$cardType.')';
            } else {
                $mismatches[] = $card->supportCard->name.' ('.$cardType.')';
            }

            // Check scenario compatibility
            $recommendedScenarios = $card->supportCard->recommended_scenarios ?? [];
            if (in_array($scenario, $recommendedScenarios)) {
                $compatibilityScore = ($compatibilityScore ?? 0) + 10;
            }
        }

        return [
            'compatibility_score' => min(100, $compatibilityScore),
            'matches' => $matches,
            'mismatches' => $mismatches,
            'is_compatible' => $compatibilityScore >= 60,
        ];
    }

    /**
     * Generate optimization suggestions
     */
    private function generateOptimizationSuggestions(): array
        $suggestions = [];

        // Meta tier suggestions
        if ($metaScore['total_score'] < 60) {
            $suggestions[] = [
                'type' => 'meta_tier',
                'priority' => 'high',
                'message' => 'Consider upgrading to higher tier cards (S+ or S tier) for better performance',
            ];
        }

        // Build compatibility suggestions
        if (! $buildCompatibility['is_compatible']) {
            $suggestions[] = [
                'type' => 'build_compatibility',
                'priority' => 'high',
                'message' => 'Deck does not align well with character stat priorities',
            ];
        }

        // Scenario-specific suggestions
        if ($character->scenario_type === 'unity_cup') {
            $suggestions[] = [
                'type' => 'scenario',
                'priority' => 'medium',
                'message' => 'For Unity Cup, prioritize cards with team synergy and Spirit Burst support',
            ];
        }

        return $suggestions;
    }

    /**
     * Suggest card replacements for optimization
     */
    private function suggestReplacements(): array
        $replacements = [];
        $statPriorities = array_keys($character->stat_priorities ?? []);

        foreach ($deck as $card) {
            $tier = $card->supportCard->meta_tier ?? 'C';

            // Suggest replacement for low-tier cards
            if (in_array($tier, ['B', 'C'])) {
                $cardType = $card->supportCard->card_type;
                $betterCards = $this->metaService->getTopCardsByType($cardType, 3);

                if ($betterCards->isNotEmpty()) {
                    $replacements[] = [
                        'current_card' => $card->supportCard->name,
                        'current_tier' => $tier,
                        'position_slot' => $card->position_slot,
                        'suggested_replacements' => $betterCards->map(function ($betterCard) {
                            return [
                                'id' => $betterCard->id,
                                'name' => $betterCard->name,
                                'tier' => $betterCard->meta_tier,
                                'rarity' => $betterCard->rarity,
                            ];
                        })->toArray(),
                    ];
                }
            }
        }

        return $replacements;
    }

    /**
     * Generate deck recommendations based on character goals and scenario
     */
    public function recommendDeck(): array
        $character = Character::findOrFail($characterId);
        $scenario = $character->scenario_type;
        $statPriorities = $character->stat_priorities ?? [];

        $cacheKey = "deck_recommendations_{$characterId}_".md5(json_encode($options));

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($character, $scenario, $statPriorities, $options) {
            $recommendedCards = $this->selectOptimalCards($character, $statPriorities, $scenario, $options);

            return [
                'character_id' => $character->id,
                'character_name' => $character->name,
                'scenario' => $scenario,
                'stat_priorities' => $statPriorities,
                'recommended_deck' => $recommendedCards,
                'expected_performance' => $this->estimatePerformance($recommendedCards),
                'reasoning' => $this->generateRecommendationReasoning($character, $recommendedCards),
            ];
        });
    }

    /**
     * Select optimal cards for the character
     */
    private function selectOptimalCards(): array
        $selectedCards = [];
        $cardsByType = [];

        // Get top cards for each stat priority
        foreach (array_keys($statPriorities) as $stat) {
            $topCards = $this->metaService->getTopCardsByType($stat, 5);
            $cardsByType[$stat] = $topCards;
        }

        // Select cards based on priorities (simplified algorithm)
        $priorityStats = array_slice(array_keys($statPriorities), 0, 3);

        foreach ($priorityStats as $index => $stat) {
            $cards = $cardsByType[$stat] ?? collect();

            if ($cards->isNotEmpty()) {
                // Select 2 cards for highest priority, 1 for others
                $count = $index === 0 ? 2 : 1;

                foreach ($cards->take($count) as $card) {
                    if (count($selectedCards) < 5) { // Leave room for friend card
                        $selectedCards[] = [
                            'id' => $card->id,
                            'name' => $card->name,
                            'card_type' => $card->card_type,
                            'meta_tier' => $card->meta_tier,
                            'rarity' => $card->rarity,
                            'position_slot' => count($selectedCards) + 1,
                            'is_friend_card' => false,
                            'reason' => "Optimal for {$stat} stat priority",
                        ];
                    }
                }
            }
        }

        // Fill remaining slots if needed (before friend card)
        while (count($selectedCards) < 5) {
            // Get any available card from priority stats
            foreach ($priorityStats as $stat) {
                if (count($selectedCards) >= 5) {
                    break;
                }

                $cards = $cardsByType[$stat] ?? collect();
                $alreadySelected = array_column($selectedCards, 'id');

                foreach ($cards as $card) {
                    if (! in_array($card->id, $alreadySelected) && count($selectedCards) < 5) {
                        $selectedCards[] = [
                            'id' => $card->id,
                            'name' => $card->name,
                            'card_type' => $card->card_type,
                            'meta_tier' => $card->meta_tier,
                            'rarity' => $card->rarity,
                            'position_slot' => count($selectedCards) + 1,
                            'is_friend_card' => false,
                            'reason' => "Additional {$stat} support",
                        ];
                        break;
                    }
                }
            }

            // If still not enough cards, break to avoid infinite loop
            if (count($selectedCards) < 5) {
                $allAvailableCards = SupportCardDefinition::where('is_active', '=', true)
                    ->where('card_type', '!=', 'friend')
                    ->orderBy('meta_tier', 'asc')
                    ->limit(5 - count($selectedCards))
                    ->get();

                $alreadySelected = array_column($selectedCards, 'id');

                foreach ($allAvailableCards as $card) {
                    if (! in_array($card->id, $alreadySelected) && count($selectedCards) < 5) {
                        $selectedCards[] = [
                            'id' => $card->id,
                            'name' => $card->name,
                            'card_type' => $card->card_type,
                            'meta_tier' => $card->meta_tier,
                            'rarity' => $card->rarity,
                            'position_slot' => count($selectedCards) + 1,
                            'is_friend_card' => false,
                            'reason' => 'Filler card for deck completion',
                        ];
                    }
                }
            }

            break; // Prevent infinite loop
        }

        // Add friend card recommendation
        if (count($selectedCards) < 6) {
            $friendCards = $this->metaService->getTopCardsByType('friend', 1);

            if ($friendCards->isNotEmpty()) {
                $friendCard = $friendCards->first();
                $selectedCards[] = [
                    'id' => $friendCard->id,
                    'name' => $friendCard->name,
                    'card_type' => $friendCard->card_type,
                    'meta_tier' => $friendCard->meta_tier,
                    'rarity' => $friendCard->rarity,
                    'position_slot' => 6,
                    'is_friend_card' => true,
                    'reason' => 'Recommended friend card for additional bonuses',
                ];
            }
        }

        return $selectedCards;
    }

    /**
     * Estimate performance of recommended deck
     */
    private function estimatePerformance(): array
        $metaTierScore = 0;
        $tierCounts = [];

        foreach ($recommendedCards as $card) {
            $tier = $card['meta_tier'] ?? 'C';
            $metaTierScore = ($metaTierScore ?? 0) + self::META_TIER_WEIGHTS[$tier] ?? 1.0;

            if (! isset($tierCounts[$tier])) {
                $tierCounts[$tier] = 0;
            }
            $tierCounts[$tier]++;
        }

        $averageWeight = count($recommendedCards) > 0 ? $metaTierScore / count($recommendedCards) : 0;
        $performanceScore = ($averageWeight / 5.0) * 100;

        return [
            'performance_score' => round($performanceScore, 1),
            'meta_tier_distribution' => $tierCounts,
            'expected_outcome' => $this->categorizePerformance($performanceScore),
        ];
    }

    /**
     * Categorize performance based on score
     */
    private function categorizePerformance(float $score): string
    {
        return match (true) {
            $score >= 80 => 'Excellent - Top tier competitive deck',
            $score >= 60 => 'Good - Strong performance expected',
            $score >= 40 => 'Average - Decent performance',
            default => 'Below Average - Consider optimization',
        };
    }

    /**
     * Generate reasoning for recommendations
     */
    private function generateRecommendationReasoning(): array
        $reasoning = [];

        $reasoning[] = "Deck optimized for {$character->scenario_type} scenario";

        $statPriorities = $character->stat_priorities ?? [];
        if (! empty($statPriorities)) {
            $topStats = array_slice(array_keys($statPriorities), 0, 2);
            $reasoning[] = 'Prioritizes '.implode(' and ', $topStats).' development';
        }

        $tierCounts = [];
        foreach ($recommendedCards as $card) {
            $tier = $card['meta_tier'] ?? 'C';
            if (! isset($tierCounts[$tier])) {
                $tierCounts[$tier] = 0;
            }
            $tierCounts[$tier]++;
        }

        $topTierCount = ($tierCounts['S+'] ?? 0) + ($tierCounts['S'] ?? 0);
        if ($topTierCount >= 4) {
            $reasoning[] = 'High meta tier composition for competitive advantage';
        }

        return $reasoning;
    }

    /**
     * Get comprehensive deck analysis
     */
    public function getComprehensiveAnalysis(): array
        return [
            'composition' => $this->analyzeDeckComposition($characterId),
            'synergy' => $this->analyzeDeckSynergy($characterId),
            'recommendations' => $this->recommendDeck($characterId),
            'meta_optimization' => $this->optimizeForMetaTier($characterId),
            'friendship_overview' => $this->friendshipService->getDeckFriendshipOverview($characterId),
            'deck_statistics' => $this->deckService->getDeckStatistics($characterId),
        ];
    }
}
