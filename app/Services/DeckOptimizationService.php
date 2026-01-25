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
    public function analyzeDeckComposition(int $characterId): array
    {
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
    private function calculateStatCoverage(\Illuminate\Support\Collection $deck): array
    {
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
     *
     * @param  array<string, int>  $coverage
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
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CharacterSupportCard>  $deck
     * @return array<string, mixed>
     */
    private function calculateSkillProvisionCoverage(\Illuminate\Support\Collection $deck): array
    {
        /** @var array<int, string> $allSkills */
        $allSkills = [];
        /** @var array<string, array<int, string>> $skillsByType */
        $skillsByType = [
            'speed' => [],
            'passive' => [],
            'recovery' => [],
            'debuff' => [],
        ];

        foreach ($deck as $card) {
            /** @var array<int, string> $skillsProvided */
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
     *
     * @param  array<string, array<int, string>>  $skillsByType
     */
    private function calculateSkillDiversityScore(array $skillsByType): float
    {
        $typesWithSkills = count(array_filter($skillsByType, fn ($skills) => count($skills) > 0));
        $totalTypes = count($skillsByType);

        return round(($typesWithSkills / $totalTypes) * 100, 1);
    }

    /**
     * Identify gaps in deck composition
     *
     * @param  array<string, mixed>  $statCoverage
     * @param  array<string, mixed>  $skillProvisionCoverage
     * @return array<string, mixed>
     */
    private function identifyGaps(array $statCoverage, array $skillProvisionCoverage): array
    {
        /** @var array<int, string> $missingStats */
        $missingStats = [];
        /** @var array<int, string> $weakStats */
        $weakStats = [];

        /** @var array<string, int> $counts */
        $counts = is_array($statCoverage['counts'] ?? null) ? $statCoverage['counts'] : [];
        foreach ($counts as $stat => $count) {
            if ($stat === 'friend') {
                continue;
            }

            if ($count === 0) {
                $missingStats[] = $stat;
            } elseif ($count < self::MIN_CARDS_PER_STAT) {
                $weakStats[] = $stat;
            }
        }

        /** @var array<int, string> $missingSkillTypes */
        $missingSkillTypes = [];
        /** @var array<string, array<int, string>> $skillsByType */
        $skillsByType = is_array($skillProvisionCoverage['skills_by_type'] ?? null) ? $skillProvisionCoverage['skills_by_type'] : [];
        foreach ($skillsByType as $type => $skills) {
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
     *
     * @param  array<string, mixed>  $statCoverage
     * @param  array<string, mixed>  $skillProvisionCoverage
     */
    private function calculateCoverageScore(array $statCoverage, array $skillProvisionCoverage): float
    {
        // Stat coverage score (60% weight)
        $isBalanced = (bool) ($statCoverage['is_balanced'] ?? false);
        $statScore = $isBalanced ? 60 : 30;

        // Skill diversity score (40% weight)
        $diversityScore = is_numeric($skillProvisionCoverage['skill_diversity_score'] ?? null)
            ? (float) $skillProvisionCoverage['skill_diversity_score']
            : 0.0;
        $skillScore = ($diversityScore / 100) * 40;

        return round($statScore + $skillScore, 1);
    }

    /**
     * Generate composition recommendations
     *
     * @param  array<string, mixed>  $gaps
     * @return array<int, string>
     */
    private function generateCompositionRecommendations(array $gaps, float $coverageScore): array
    {
        /** @var array<int, string> $recommendations */
        $recommendations = [];

        if ($coverageScore >= self::OPTIMAL_STAT_COVERAGE) {
            $recommendations[] = 'Excellent deck composition! Your stat coverage and skill diversity are well-balanced.';
        } elseif ($coverageScore >= 60) {
            $recommendations[] = 'Good deck composition with room for optimization.';
        } else {
            $recommendations[] = 'Deck composition needs improvement for optimal performance.';
        }

        /** @var array<int, string> $missingStats */
        $missingStats = is_array($gaps['missing_stats'] ?? null) ? $gaps['missing_stats'] : [];
        if (! empty($missingStats)) {
            $recommendations[] = 'Add cards for missing stat types: '.implode(', ', $missingStats);
        }

        /** @var array<int, string> $weakStats */
        $weakStats = is_array($gaps['weak_stats'] ?? null) ? $gaps['weak_stats'] : [];
        if (! empty($weakStats)) {
            $recommendations[] = 'Consider adding more cards for: '.implode(', ', $weakStats);
        }

        /** @var array<int, string> $missingSkillTypes */
        $missingSkillTypes = is_array($gaps['missing_skill_types'] ?? null) ? $gaps['missing_skill_types'] : [];
        if (! empty($missingSkillTypes)) {
            $recommendations[] = 'Improve skill diversity by adding '.implode(', ', $missingSkillTypes).' skills';
        }

        return $recommendations;
    }

    /**
     * Analyze synergy between cards in the deck
     *
     * @return array<string, mixed>
     */
    public function analyzeDeckSynergy(int $characterId): array
    {
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
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CharacterSupportCard>  $deck
     * @return array<int, array<string, mixed>>
     */
    private function identifySynergyPairs(\Illuminate\Support\Collection $deck): array
    {
        /** @var array<string, array<string, mixed>> $pairs */
        $pairs = [];

        foreach ($deck as $card1) {
            $synergyData = $this->metaService->getCardSynergies($card1->support_card_id);

            $synergyCardsData = $synergyData['synergy_cards'] ?? [];
            if ($synergyCardsData instanceof Collection) {
                $synergyCards = $synergyCardsData->toArray();
            } elseif (is_array($synergyCardsData)) {
                $synergyCards = $synergyCardsData;
            } else {
                $synergyCards = [];
            }
            /** @var array<int, array<string, mixed>> $synergyCards */
            foreach ($synergyCards as $synergyCard) {
                /** @var int|null $synergyCardId */
                $synergyCardId = null;
                if (is_array($synergyCard) && isset($synergyCard['id'])) {
                    $rawId = $synergyCard['id'];
                    if (is_int($rawId) || is_string($rawId)) {
                        $synergyCardId = (int) $rawId;
                    }
                }
                if ($synergyCardId === null) {
                    continue;
                }

                // Check if synergy card is in the deck
                $matchingCard = $deck->first(function ($card2) use ($synergyCardId) {
                    return $card2->support_card_id === $synergyCardId;
                });

                if ($matchingCard !== null && $matchingCard->supportCard !== null) {
                    $card1SupportCard = $card1->supportCard;
                    if ($card1SupportCard === null) {
                        continue;
                    }
                    $pairKey = min($card1->support_card_id, $matchingCard->support_card_id).'-'.
                        max($card1->support_card_id, $matchingCard->support_card_id);

                    if (! isset($pairs[$pairKey])) {
                        $pairs[$pairKey] = [
                            'card1' => $card1SupportCard->name,
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
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CharacterSupportCard>  $deck
     * @return array<string, mixed>
     */
    private function analyzeStrategicAlignment(\Illuminate\Support\Collection $deck): array
    {
        /** @var array<string, int> $cardTypes */
        $cardTypes = $deck->pluck('supportCard.card_type')->countBy()->toArray();
        /** @var array<string, int> $metaTiers */
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
     *
     * @param  array<string, int>  $cardTypes
     */
    private function determinePrimaryStrategy(array $cardTypes): string
    {
        if (empty($cardTypes)) {
            return 'undefined';
        }

        arsort($cardTypes);
        $dominantType = array_key_first($cardTypes);
        /** @var int $dominantCount */
        $dominantCount = $cardTypes[$dominantType] ?? 0;

        if ($dominantCount >= 3) {
            return ucfirst((string) $dominantType).' Focus';
        }

        if (count($cardTypes) >= 4) {
            return 'Balanced';
        }

        return 'Mixed';
    }

    /**
     * Calculate strategic alignment score
     *
     * @param  array<string, int>  $cardTypes
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
     *
     * @param  array<int, array<string, mixed>>  $synergyPairs
     * @param  array<string, mixed>  $strategicAlignment
     */
    private function calculateSynergyScore(array $synergyPairs, array $strategicAlignment): float
    {
        // Synergy pairs contribute 50%
        $pairScore = min(50, count($synergyPairs) * 10);

        // Strategic alignment contributes 50%
        $alignmentValue = is_numeric($strategicAlignment['alignment_score'] ?? null)
            ? (float) $strategicAlignment['alignment_score']
            : 0.0;
        $alignmentScore = ($alignmentValue / 100) * 50;

        return round($pairScore + $alignmentScore, 1);
    }

    /**
     * Generate synergy recommendations
     *
     * @param  array<int, array<string, mixed>>  $synergyPairs
     * @param  array<string, mixed>  $strategicAlignment
     * @return array<int, string>
     */
    private function generateSynergyRecommendations(array $synergyPairs, array $strategicAlignment): array
    {
        /** @var array<int, string> $recommendations */
        $recommendations = [];

        if (count($synergyPairs) === 0) {
            $recommendations[] = 'No synergy pairs detected. Consider adding cards with known synergies.';
        } elseif (count($synergyPairs) >= 2) {
            $recommendations[] = 'Excellent synergy! Your deck has '.count($synergyPairs).' synergy pairs.';
        } else {
            $recommendations[] = 'Good synergy detected. Consider adding more synergistic cards.';
        }

        $isWellAligned = (bool) ($strategicAlignment['is_well_aligned'] ?? false);
        $primaryStrategy = is_string($strategicAlignment['primary_strategy'] ?? null)
            ? $strategicAlignment['primary_strategy']
            : 'unknown';
        if (! $isWellAligned) {
            $recommendations[] = 'Strategic alignment could be improved. Current strategy: '.$primaryStrategy;
        }

        return $recommendations;
    }

    /**
     * Optimize deck for meta tier and character build compatibility
     *
     * @return array<string, mixed>
     */
    public function optimizeForMetaTier(int $characterId): array
    {
        $character = Character::findOrFail($characterId);
        $deck = $this->deckService->getDeck($characterId);

        $metaScore = $this->calculateMetaScore($deck);
        $buildCompatibility = $this->analyzeBuildCompatibility($character, $deck);
        $optimizationSuggestions = $this->generateOptimizationSuggestions($character, $metaScore, $buildCompatibility);

        return [
            'meta_score' => $metaScore,
            'build_compatibility' => $buildCompatibility,
            'optimization_suggestions' => $optimizationSuggestions,
            'recommended_replacements' => $this->suggestReplacements($character, $deck),
        ];
    }

    /**
     * Calculate meta score for the deck
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CharacterSupportCard>  $deck
     * @return array<string, mixed>
     */
    private function calculateMetaScore(\Illuminate\Support\Collection $deck): array
    {
        if ($deck->isEmpty()) {
            return [
                'total_score' => 0,
                'average_tier_weight' => 0,
                'tier_breakdown' => [],
            ];
        }

        $totalWeight = 0.0;
        /** @var array<string, int> $tierBreakdown */
        $tierBreakdown = [];

        foreach ($deck as $card) {
            /** @var string $tier */
            $tier = $card->supportCard->meta_tier ?? 'C';
            $weight = self::META_TIER_WEIGHTS[$tier] ?? 1.0;
            $totalWeight += $weight;

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
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CharacterSupportCard>  $deck
     * @return array<string, mixed>
     */
    private function analyzeBuildCompatibility(Character $character, \Illuminate\Support\Collection $deck): array
    {
        /** @var array<string, mixed> $statPriorities */
        $statPriorities = is_array($character->stat_priorities) ? $character->stat_priorities : [];
        /** @var string|null $scenario */
        $scenario = $character->scenario_type;

        $compatibilityScore = 0;
        /** @var array<int, string> $matches */
        $matches = [];
        /** @var array<int, string> $mismatches */
        $mismatches = [];

        foreach ($deck as $card) {
            $supportCard = $card->supportCard;
            if ($supportCard === null) {
                continue;
            }
            $cardType = strtolower($supportCard->card_type ?? '');

            // Check if card type matches stat priorities
            if (in_array($cardType, array_map('strtolower', array_keys($statPriorities)))) {
                $compatibilityScore += 20;
                $matches[] = $supportCard->name.' ('.$cardType.')';
            } else {
                $mismatches[] = $supportCard->name.' ('.$cardType.')';
            }

            // Check scenario compatibility
            /** @var array<int, string> $recommendedScenarios */
            $recommendedScenarios = is_array($supportCard->recommended_scenarios ?? null)
                ? $supportCard->recommended_scenarios
                : [];
            if ($scenario !== null && in_array($scenario, $recommendedScenarios)) {
                $compatibilityScore += 10;
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
     *
     * @param  array<string, mixed>  $metaScore
     * @param  array<string, mixed>  $buildCompatibility
     * @return array<int, array<string, string>>
     */
    private function generateOptimizationSuggestions(Character $character, array $metaScore, array $buildCompatibility): array
    {
        /** @var array<int, array<string, string>> $suggestions */
        $suggestions = [];

        // Meta tier suggestions
        $totalScore = is_numeric($metaScore['total_score'] ?? null) ? (float) $metaScore['total_score'] : 0.0;
        if ($totalScore < 60) {
            $suggestions[] = [
                'type' => 'meta_tier',
                'priority' => 'high',
                'message' => 'Consider upgrading to higher tier cards (S+ or S tier) for better performance',
            ];
        }

        // Build compatibility suggestions
        $isCompatible = (bool) ($buildCompatibility['is_compatible'] ?? false);
        if (! $isCompatible) {
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
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CharacterSupportCard>  $deck
     * @return array<int, array<string, mixed>>
     */
    private function suggestReplacements(Character $character, \Illuminate\Support\Collection $deck): array
    {
        /** @var array<int, array<string, mixed>> $replacements */
        $replacements = [];
        /** @var array<string, mixed> $charStatPriorities */
        $charStatPriorities = is_array($character->stat_priorities) ? $character->stat_priorities : [];
        /** @var array<int, string> $statPriorities */
        $statPriorities = array_keys($charStatPriorities);

        foreach ($deck as $card) {
            $supportCard = $card->supportCard;
            if ($supportCard === null) {
                continue;
            }
            /** @var string $tier */
            $tier = $supportCard->meta_tier ?? 'C';

            // Suggest replacement for low-tier cards
            if (in_array($tier, ['B', 'C'])) {
                /** @var string|null $cardType */
                $cardType = $supportCard->card_type;
                if ($cardType === null) {
                    continue;
                }
                /** @var \Illuminate\Support\Collection<int, mixed> $betterCards */
                $betterCards = $this->metaService->getTopCardsByType($cardType, 3);

                if ($betterCards->isNotEmpty()) {
                    $replacements[] = [
                        'current_card' => $supportCard->name,
                        'current_tier' => $tier,
                        'position_slot' => $card->position_slot,
                        'suggested_replacements' => $betterCards->map(
                            function ($betterCard): array {
                                if ($betterCard instanceof SupportCardDefinition) {
                                    return [
                                        'id' => $betterCard->id,
                                        'name' => $betterCard->name,
                                        'tier' => $betterCard->meta_tier,
                                        'rarity' => $betterCard->rarity,
                                    ];
                                }

                                if (is_array($betterCard)) {
                                    $id = is_scalar($betterCard['id'] ?? null) ? (int) $betterCard['id'] : 0;
                                    $name = is_string($betterCard['name'] ?? null) ? $betterCard['name'] : 'Unknown';
                                    $tier = is_string($betterCard['meta_tier'] ?? null) ? $betterCard['meta_tier'] : null;
                                    $rarity = is_string($betterCard['rarity'] ?? null) ? $betterCard['rarity'] : null;

                                    return [
                                        'id' => $id,
                                        'name' => $name,
                                        'tier' => $tier,
                                        'rarity' => $rarity,
                                    ];
                                }

                                if (is_object($betterCard)) {
                                    $id = isset($betterCard->id) && is_scalar($betterCard->id) ? (int) $betterCard->id : 0;
                                    $name = isset($betterCard->name) && is_string($betterCard->name) ? $betterCard->name : 'Unknown';
                                    $tier = isset($betterCard->meta_tier) && is_string($betterCard->meta_tier) ? $betterCard->meta_tier : null;
                                    $rarity = isset($betterCard->rarity) && is_string($betterCard->rarity) ? $betterCard->rarity : null;

                                    return [
                                        'id' => $id,
                                        'name' => $name,
                                        'tier' => $tier,
                                        'rarity' => $rarity,
                                    ];
                                }

                                return [
                                    'id' => 0,
                                    'name' => 'Unknown',
                                    'tier' => null,
                                    'rarity' => null,
                                ];
                            }
                        )->toArray(),
                    ];
                }
            }
        }

        return $replacements;
    }

    /**
     * Generate deck recommendations based on character goals and scenario
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function recommendDeck(int $characterId, array $options = []): array
    {
        $character = Character::findOrFail($characterId);
        /** @var string|null $scenario */
        $scenario = $character->scenario_type;
        /** @var array<string, mixed> $statPriorities */
        $statPriorities = is_array($character->stat_priorities) ? $character->stat_priorities : [];

        $jsonOptions = json_encode($options);
        $cacheKey = "deck_recommendations_{$characterId}_".md5($jsonOptions !== false ? $jsonOptions : '');

        /** @var array<string, mixed> $result */
        $result = Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($character, $scenario, $statPriorities): array {
            $recommendedCards = $this->selectOptimalCards($statPriorities);

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

        return is_array($result) ? $result : [];
    }

    /**
     * Select optimal cards for the character
     *
     * @param  array<string, mixed>  $statPriorities
     * @return array<int, array<string, mixed>>
     */
    private function selectOptimalCards(array $statPriorities): array
    {
        /** @var array<int, array<string, mixed>> $selectedCards */
        $selectedCards = [];
        /** @var array<string, \Illuminate\Support\Collection<int, SupportCardDefinition>> $cardsByType */
        $cardsByType = [];

        // Get top cards for each stat priority
        foreach (array_keys($statPriorities) as $stat) {
            if (! is_string($stat)) {
                continue;
            }
            $topCards = $this->metaService->getTopCardsByType($stat, 5);
            $cardsByType[$stat] = $topCards;
        }

        // Select cards based on priorities (simplified algorithm)
        /** @var array<int, string> $priorityStats */
        $priorityStats = [];
        foreach (array_slice(array_keys($statPriorities), 0, 3) as $stat) {
            if (is_string($stat)) {
                $priorityStats[] = $stat;
            }
        }

        foreach ($priorityStats as $index => $stat) {
            $cards = $cardsByType[$stat] ?? collect();

            if ($cards->isNotEmpty()) {
                // Select 2 cards for highest priority, 1 for others
                $count = $index === 0 ? 2 : 1;

                /** @var SupportCardDefinition $card */
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
        $this->fillRemainingSlots($selectedCards, $cardsByType, $priorityStats);

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
     *
     * @param  array<int, array<string, mixed>>  $recommendedCards
     * @return array<string, mixed>
     */
    private function estimatePerformance(array $recommendedCards): array
    {
        $metaTierScore = 0.0;
        /** @var array<string, int> $tierCounts */
        $tierCounts = [];

        foreach ($recommendedCards as $card) {
            /** @var string $tier */
            $tier = is_array($card) && is_string($card['meta_tier'] ?? null) ? $card['meta_tier'] : 'C';
            $metaTierScore += (self::META_TIER_WEIGHTS[$tier] ?? 1.0);

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
     *
     * @param  array<int, array<string, mixed>>  $recommendedCards
     * @return array<int, string>
     */
    private function generateRecommendationReasoning(Character $character, array $recommendedCards): array
    {
        /** @var array<int, string> $reasoning */
        $reasoning = [];

        /** @var string $scenarioType */
        $scenarioType = is_string($character->scenario_type) ? $character->scenario_type : 'unknown';
        $reasoning[] = "Deck optimized for {$scenarioType} scenario";

        /** @var array<string, mixed> $statPriorities */
        $statPriorities = is_array($character->stat_priorities) ? $character->stat_priorities : [];
        if (! empty($statPriorities)) {
            /** @var array<int, string> $topStats */
            $topStats = [];
            foreach (array_slice(array_keys($statPriorities), 0, 2) as $stat) {
                if (is_string($stat)) {
                    $topStats[] = $stat;
                }
            }
            if (! empty($topStats)) {
                $reasoning[] = 'Prioritizes '.implode(' and ', $topStats).' development';
            }
        }

        /** @var array<string, int> $tierCounts */
        $tierCounts = [];
        foreach ($recommendedCards as $card) {
            /** @var string $tier */
            $tier = is_array($card) && is_string($card['meta_tier'] ?? null) ? $card['meta_tier'] : 'C';
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
     *
     * @return array<string, mixed>
     */
    public function getComprehensiveAnalysis(int $characterId): array
    {
        return [
            'composition' => $this->analyzeDeckComposition($characterId),
            'synergy' => $this->analyzeDeckSynergy($characterId),
            'recommendations' => $this->recommendDeck($characterId),
            'meta_optimization' => $this->optimizeForMetaTier($characterId),
            'friendship_overview' => $this->friendshipService->getDeckFriendshipOverview($characterId),
            'deck_statistics' => $this->deckService->getDeckStatistics($characterId),
        ];
    }

    /**
     * Fill remaining card slots to reach 5 cards
     *
     * @param  array<int, array<string, mixed>>  $selectedCards
     * @param  array<string, \Illuminate\Support\Collection<int, SupportCardDefinition>>  $cardsByType
     * @param  array<int, string>  $priorityStats
     */
    private function fillRemainingSlots(array &$selectedCards, array $cardsByType, array $priorityStats): void
    {
        // Try to fill from priority stat cards first
        foreach ($priorityStats as $stat) {
            if (count($selectedCards) >= 5) {
                return;
            }

            $cards = $cardsByType[$stat] ?? collect();
            $alreadySelected = array_column($selectedCards, 'id');

            /** @var SupportCardDefinition $card */
            foreach ($cards as $card) {
                if (count($selectedCards) >= 5) {
                    return;
                }
                if (! in_array($card->id, $alreadySelected)) {
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
                }
            }
        }

        // If still not enough cards, fill from all available
        if (count($selectedCards) < 5) {
            $allAvailableCards = SupportCardDefinition::where('is_active', '=', true)
                ->where('card_type', '!=', 'friend')
                ->orderBy('meta_tier', 'asc')
                ->limit(5 - count($selectedCards))
                ->get();

            $alreadySelected = array_column($selectedCards, 'id');

            /** @var SupportCardDefinition $card */
            foreach ($allAvailableCards as $card) {
                if (count($selectedCards) >= 5) {
                    return;
                }
                if (! in_array($card->id, $alreadySelected)) {
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
    }
}
