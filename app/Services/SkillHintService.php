<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillHint;
use App\Models\SupportCardDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Skill Hint Service
 *
 * Manages skill hint tracking, cost reduction calculations, and hint optimization.
 * Implements 20% SP cost reduction per duplicate hint with 40% maximum discount.
 */
class SkillHintService
{
    /**
     * Maximum number of hints that provide discount (40% max = 2 hints × 20%)
     */
    private const MAX_DISCOUNT_HINTS = 2;

    /**
     * Discount percentage per hint
     */
    private const DISCOUNT_PER_HINT = 20.0;

    /**
     * Maximum discount percentage
     */
    private const MAX_DISCOUNT_PERCENTAGE = 40.0;

    /**
     * Create a new skill hint for a character.
     *
     * @param  array<string, mixed>  $additionalData
     */
    public function createHint(
        Character $character,
        Skill $skill,
        string $sourceType,
        string $sourceName,
        ?int $sourceId = null,
        array $additionalData = []
    ): SkillHint {
        // Calculate discount percentage based on existing hints
        $existingHints = $this->getHintsForSkill($character, $skill);
        $discountPercentage = $this->calculateDiscountPercentage($existingHints->count() + 1);

        $hintData = array_merge([
            'character_id' => $character->id,
            'skill_id' => $skill->id,
            'source_type' => $sourceType,
            'source_name' => $sourceName,
            'source_id' => $sourceId,
            'discount_percentage' => $discountPercentage,
            'is_used' => false,
            'turn_obtained' => $additionalData['turn_obtained'] ?? 1, // Default to turn 1 if not provided
            'career_phase' => $additionalData['career_phase'] ?? 'junior', // Default to junior phase
        ], $additionalData);

        $hint = SkillHint::create($hintData);

        Log::info('Skill hint created', [
            'character_id' => $character->id,
            'skill_id' => $skill->id,
            'source_type' => $sourceType,
            'hint_count' => $existingHints->count() + 1,
            'discount_percentage' => $discountPercentage,
        ]);

        return $hint;
    }

    /**
     * Get all hints for a specific skill and character.
     *
     * @return Collection<int, SkillHint>
     */
    public function getHintsForSkill(Character $character, Skill $skill): Collection
    {
        return SkillHint::where('character_id', $character->id)
            ->where('skill_id', $skill->id)
            ->orderBy('turn_obtained')
            ->get();
    }

    /**
     * Get unused hints for a specific skill and character.
     *
     * @return Collection<int, SkillHint>
     */
    public function getUnusedHintsForSkill(Character $character, Skill $skill): Collection
    {
        return SkillHint::where('character_id', $character->id)
            ->where('skill_id', $skill->id)
            ->unused()
            ->orderBy('turn_obtained')
            ->get();
    }

    /**
     * Calculate the discount percentage based on hint count.
     */
    public function calculateDiscountPercentage(int $hintCount): float
    {
        $effectiveHints = min($hintCount, self::MAX_DISCOUNT_HINTS);

        return min($effectiveHints * self::DISCOUNT_PER_HINT, self::MAX_DISCOUNT_PERCENTAGE);
    }

    /**
     * Calculate the final SP cost for a skill with hints.
     */
    public function calculateFinalCost(Skill $skill, int $hintCount): int
    {
        $discountPercentage = $this->calculateDiscountPercentage($hintCount);
        $discount = ($skill->base_sp_cost * $discountPercentage) / 100;

        return (int) ($skill->base_sp_cost - $discount);
    }

    /**
     * Calculate SP saved with hints.
     */
    public function calculateSpSaved(Skill $skill, int $hintCount): int
    {
        return $skill->base_sp_cost - $this->calculateFinalCost($skill, $hintCount);
    }

    /**
     * Get cost breakdown for a skill with current hints.
     *
     * @return array{
     *   skill_id: int,
     *   skill_name: string,
     *   base_sp_cost: int,
     *   hint_count: int,
     *   discount_percentage: float,
     *   final_sp_cost: int,
     *   sp_saved: int,
     *   max_discount_reached: bool,
     *   hints: array<int, array{id: int, source_type: string, source_name: string, turn_obtained: int, guaranteed: bool}>
     * }
     */
    public function getCostBreakdown(Character $character, Skill $skill): array
    {
        $hints = $this->getUnusedHintsForSkill($character, $skill);
        $hintCount = $hints->count();
        $discountPercentage = $this->calculateDiscountPercentage($hintCount);
        $finalCost = $this->calculateFinalCost($skill, $hintCount);
        $spSaved = $this->calculateSpSaved($skill, $hintCount);

        return [
            'skill_id' => $skill->id,
            'skill_name' => $skill->name,
            'base_sp_cost' => $skill->base_sp_cost,
            'hint_count' => $hintCount,
            'discount_percentage' => (float) $discountPercentage, // Ensure float type
            'final_sp_cost' => $finalCost,
            'sp_saved' => $spSaved,
            'max_discount_reached' => $hintCount >= self::MAX_DISCOUNT_HINTS,
            'hints' => $hints->map(function ($hint) {
                return [
                    'id' => $hint->id,
                    'source_type' => $hint->source_type,
                    'source_name' => $hint->source_name,
                    'turn_obtained' => $hint->turn_obtained,
                    'guaranteed' => $hint->guaranteed_hint,
                ];
            })->toArray(),
        ];
    }

    /**
     * Mark hints as used when a skill is acquired.
     */
    public function markHintsAsUsed(Character $character, Skill $skill): int
    {
        $hints = $this->getUnusedHintsForSkill($character, $skill);

        foreach ($hints as $hint) {
            $hint->markAsUsed();
        }

        Log::info('Skill hints marked as used', [
            'character_id' => $character->id,
            'skill_id' => $skill->id,
            'hints_used' => $hints->count(),
        ]);

        return $hints->count();
    }

    /**
     * Predict hint opportunities from support cards during training.
     *
     * @param  Collection<int, SupportCardDefinition>  $supportCards
     * @return array<int, array{
     *   skill_id: int,
     *   skill_name: string,
     *   support_card_id: int,
     *   support_card_name: string,
     *   guaranteed: bool,
     *   probability: float,
     *   current_hints: int,
     *   potential_discount: float,
     *   sp_savings: int,
     *   max_discount_reached: bool
     * }>
     */
    public function predictHintOpportunities(
        Character $character,
        string $trainingType,
        Collection $supportCards
    ): array {
        $opportunities = [];

        foreach ($supportCards as $supportCard) {
            // Check if support card provides hints for this training type
            $providedSkills = $this->getSkillsProvidedByCard($supportCard, $trainingType);

            foreach ($providedSkills as $skillData) {
                $skill = Skill::find($skillData['skill_id']);
                if (! $skill) {
                    continue;
                }

                $existingHints = $this->getHintsForSkill($character, $skill);
                $isGuaranteed = $this->isGuaranteedHint($supportCard, $trainingType, $skill);

                $opportunities[] = [
                    'skill_id' => $skill->id,
                    'skill_name' => $skill->name,
                    'support_card_id' => $supportCard->id,
                    'support_card_name' => $supportCard->name,
                    'guaranteed' => $isGuaranteed,
                    'probability' => $isGuaranteed ? 100.0 : $this->calculateHintProbability($supportCard, $skill),
                    'current_hints' => $existingHints->count(),
                    'potential_discount' => $this->calculateDiscountPercentage($existingHints->count() + 1),
                    'sp_savings' => $this->calculateSpSaved($skill, $existingHints->count() + 1),
                    'max_discount_reached' => $existingHints->count() >= self::MAX_DISCOUNT_HINTS,
                ];
            }
        }

        // Sort by guaranteed first, then by SP savings
        usort($opportunities, function ($a, $b) {
            if ($a['guaranteed'] !== $b['guaranteed']) {
                return $b['guaranteed'] <=> $a['guaranteed'];
            }

            return $b['sp_savings'] <=> $a['sp_savings'];
        });

        return $opportunities;
    }

    /**
     * Check if a hint is guaranteed (red "!" indicator).
     */
    private function isGuaranteedHint(SupportCardDefinition $supportCard, string $trainingType, Skill $skill): bool
    {
        // Guaranteed hints occur when:
        // 1. Support card specialization matches training type
        // 2. Support card is at high friendship level (80%+)
        // 3. Skill is in the card's primary skill provision list

        $specializationMatches = strtolower($supportCard->specialization) === strtolower($trainingType);
        $highFriendship = $supportCard->friendship_level >= 80;
        $isPrimarySkill = $this->isPrimarySkillForCard($supportCard, $skill);

        return $specializationMatches && $highFriendship && $isPrimarySkill;
    }

    /**
     * Calculate hint probability for non-guaranteed opportunities.
     */
    private function calculateHintProbability(SupportCardDefinition $supportCard, Skill $skill): float
    {
        $baseProbability = 30.0; // Base 30% chance

        // Friendship level bonus (0-50% bonus)
        $friendshipBonus = ($supportCard->friendship_level / 100) * 50;

        // Limit break bonus (5% per level)
        $limitBreakBonus = $supportCard->limit_break_level * 5;

        // Skill rarity penalty (rare skills are harder to get hints for)
        $rarityPenalty = match ($skill->rarity) {
            'unique' => -20,
            'rare' => -10,
            'normal' => 0,
            default => 0,
        };

        $totalProbability = $baseProbability + $friendshipBonus + $limitBreakBonus + $rarityPenalty;

        return max(0.0, min(100.0, $totalProbability));
    }

    /**
     * Get skills provided by a support card for a specific training type.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getSkillsProvidedByCard(SupportCardDefinition $supportCard, string $trainingType): array
    {
        $supportCardSources = $supportCard->skill_provision ?? [];

        if (empty($supportCardSources)) {
            return [];
        }

        // Filter skills that match the training type
        return array_filter($supportCardSources, function ($skillData) use ($trainingType) {
            return isset($skillData['training_type']) &&
                strtolower($skillData['training_type']) === strtolower($trainingType);
        });
    }

    /**
     * Check if a skill is a primary skill for a support card.
     */
    private function isPrimarySkillForCard(SupportCardDefinition $supportCard, Skill $skill): bool
    {
        $skillProvision = $supportCard->skill_provision ?? [];

        foreach ($skillProvision as $skillData) {
            if (
                isset($skillData['skill_id']) &&
                $skillData['skill_id'] === $skill->id &&
                ($skillData['is_primary'] ?? false)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get hint collection strategy recommendations.
     *
     * @param  Collection<int, Skill>  $targetSkills
     * @return array<int, array{skill_id: int, skill_name: string, status: string, recommendation: string, priority: string, current_hints: int, final_cost?: int, potential_savings?: int}>
     */
    public function getHintCollectionStrategy(Character $character, Collection $targetSkills): array
    {
        $strategies = [];

        foreach ($targetSkills as $skill) {
            $existingHints = $this->getHintsForSkill($character, $skill);
            $hintCount = $existingHints->count();

            if ($hintCount >= self::MAX_DISCOUNT_HINTS) {
                $strategies[] = [
                    'skill_id' => $skill->id,
                    'skill_name' => $skill->name,
                    'status' => 'max_discount',
                    'recommendation' => 'Maximum discount reached. Ready to acquire.',
                    'priority' => 'high',
                    'current_hints' => $hintCount,
                    'final_cost' => $this->calculateFinalCost($skill, $hintCount),
                ];
            } elseif ($hintCount === 1) {
                $strategies[] = [
                    'skill_id' => $skill->id,
                    'skill_name' => $skill->name,
                    'status' => 'one_more_hint',
                    'recommendation' => 'Collect one more hint for maximum discount (40%).',
                    'priority' => 'medium',
                    'current_hints' => $hintCount,
                    'potential_savings' => $this->calculateSpSaved($skill, 2) - $this->calculateSpSaved($skill, 1),
                ];
            } else {
                $strategies[] = [
                    'skill_id' => $skill->id,
                    'skill_name' => $skill->name,
                    'status' => 'collect_hints',
                    'recommendation' => 'Collect hints before acquiring to maximize SP savings.',
                    'priority' => 'low',
                    'current_hints' => $hintCount,
                    'potential_savings' => $this->calculateSpSaved($skill, 2),
                ];
            }
        }

        // Sort by priority
        $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
        usort($strategies, function ($a, $b) use ($priorityOrder) {
            return ($priorityOrder[$b['priority']] ?? 0) <=> ($priorityOrder[$a['priority']] ?? 0);
        });

        return $strategies;
    }

    /**
     * Get comprehensive hint statistics for a character.
     *
     * @return array<string, mixed>
     */
    public function getHintStatistics(Character $character): array
    {
        $allHints = SkillHint::where('character_id', $character->id)->with('skill')->get();
        $unusedHints = $allHints->where('is_used', false);
        $usedHints = $allHints->where('is_used', true);

        // Group by source type
        $sourceTypeDistribution = $allHints->groupBy('source_type')->map->count();

        // Calculate total SP saved
        $totalSpSaved = $usedHints->sum(function ($hint) {
            $skill = $hint->skill;
            if (! $skill) {
                return 0;
            }

            return ($skill->base_sp_cost * $hint->discount_percentage) / 100;
        });

        // Skills with max discount
        $skillsWithMaxDiscount = $unusedHints->groupBy('skill_id')
            ->filter(function ($hints) {
                return $hints->count() >= self::MAX_DISCOUNT_HINTS;
            })
            ->count();

        return [
            'total_hints' => $allHints->count(),
            'unused_hints' => $unusedHints->count(),
            'used_hints' => $usedHints->count(),
            'guaranteed_hints' => $allHints->where('guaranteed_hint', true)->count(),
            'source_distribution' => $sourceTypeDistribution->toArray(),
            'total_sp_saved' => (int) $totalSpSaved,
            'skills_with_max_discount' => $skillsWithMaxDiscount,
            'average_hints_per_skill' => $allHints->count() > 0
                ? round($allHints->groupBy('skill_id')->map->count()->avg(), 2)
                : 0,
        ];
    }
}
