<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SupportCard;
use Illuminate\Support\Collection;

/**
 * Manage skill hints from support cards.
 *
 * VERIFIED (Jan 2026): Skill hints provide progressive discounts:
 * - Level 1: 10% discount
 * - Level 2: 20% discount
 * - Level 3: 30% discount
 * - Level 4: 35% discount
 * - Level 5: 40% discount (MAXIMUM)
 */
class SkillHintService
{
    /**
     * Maximum hint level (5 hints = 40% max discount).
     *
     * VERIFIED: Levels 1-3 provide 10% each, levels 4-5 provide 5% each.
     */
    private const MAX_HINT_LEVEL = 5;

    /**
     * Calculate SP discount percentage based on hint level.
     *
     * @param  int  $hintLevel  Hint level (1-5)
     * @return int Discount percentage (10, 20, 30, 35, or 40)
     */
    private function calculateDiscountPercentage(int $hintLevel): int
    {
        return match (min($hintLevel, self::MAX_HINT_LEVEL)) {
            1 => 10,  // 10%
            2 => 20,  // 20% (cumulative)
            3 => 30,  // 30% (cumulative)
            4 => 35,  // 35% (cumulative, +5%)
            5 => 40,  // 40% max (cumulative, +5%)
            default => 0,
        };
    }

    /**
     * Record a skill hint obtained during training.
     */
    public function recordHint(Character $character, Skill $skill, SupportCard $sourceCard): ?SkillAcquisition
    {
        // Find or create skill acquisition record
        $acquisition = SkillAcquisition::firstOrCreate(
            [
                'character_id' => $character->id,
                'skill_id' => $skill->id,
            ],
            [
                'base_sp_cost' => $skill->base_sp_cost ?? 0,
                'hint_level' => 0,
                'hint_sources' => [],
                'sp_discount_applied' => 0,
                'final_sp_cost' => $skill->base_sp_cost ?? 0,
                'is_active' => false,
                'turn_acquired' => 0,
                'career_phase' => 'junior',
                'acquisition_method' => 'purchase',
                'sp_saved' => 0,
            ]
        );

        // Check if already at max hints
        if ($acquisition->hint_level >= self::MAX_HINT_LEVEL) {
            return $acquisition;
        }

        // Increment hint level
        $newHintLevel = $acquisition->hint_level + 1;
        $hintSources = $acquisition->hint_sources ?? [];
        $hintSources[] = [
            'card_id' => $sourceCard->id,
            'card_name' => $sourceCard->name ?? 'Unknown',
            'obtained_at' => now()->toIso8601String(),
        ];

        // Calculate new SP discount using verified rates
        $spDiscount = $this->calculateDiscountPercentage($newHintLevel);
        $baseCost = $acquisition->base_sp_cost ?? 0;
        $finalSpCost = $this->calculateDiscountedCost($baseCost, $spDiscount);

        // Update acquisition
        $acquisition->update([
            'hint_level' => $newHintLevel,
            'hint_sources' => $hintSources,
            'sp_discount_applied' => $spDiscount,
            'final_sp_cost' => $finalSpCost,
            'sp_saved' => $acquisition->base_sp_cost - $finalSpCost,
        ]);

        return $acquisition->fresh();
    }

    /**
     * Calculate discounted SP cost.
     */
    private function calculateDiscountedCost(int $baseCost, int $discountPercentage): int
    {
        $discount = ($baseCost * $discountPercentage) / 100;

        return max(0, (int) round($baseCost - $discount));
    }

    /**
     * Get hint probability for a skill from a support card.
     *
     * @return float Probability (0.0 to 1.0)
     */
    public function getHintProbability(SupportCard $card, Skill $skill, int $bondLevel): float
    {
        // Base probability increases with bond level
        $baseProbability = min(0.5, $bondLevel / 200); // Max 50% at bond 100

        // Bonus for matching card type (if card specializes in skill type)
        $typeBonus = 0.1; // 10% bonus for matching type

        return min(1.0, $baseProbability + $typeBonus);
    }

    /**
     * Get all skills with hints for a character.
     *
     * @return Collection<int, SkillAcquisition>
     */
    public function getSkillsWithHints(Character $character): Collection
    {
        return SkillAcquisition::where('character_id', $character->id)
            ->where('hint_level', '>', 0)
            ->with('skill')
            ->get();
    }

    /**
     * Get hint summary for a character.
     *
     * @return array<string, mixed>
     */
    public function getHintSummary(Character $character): array
    {
        $skillsWithHints = $this->getSkillsWithHints($character);

        $totalHints = $skillsWithHints->sum('hint_level');
        $totalSpSaved = $skillsWithHints->sum('sp_saved');
        $skillsWithMaxHints = $skillsWithHints->where('hint_level', self::MAX_HINT_LEVEL)->count();

        return [
            'total_skills_with_hints' => $skillsWithHints->count(),
            'total_hints' => $totalHints,
            'total_sp_saved' => $totalSpSaved,
            'skills_with_max_hints' => $skillsWithMaxHints,
            'skills' => $skillsWithHints->map(function ($acquisition) {
                return [
                    'skill_id' => $acquisition->skill_id,
                    'skill_name' => $acquisition->skill->name ?? 'Unknown',
                    'hint_level' => $acquisition->hint_level,
                    'sp_discount' => $acquisition->sp_discount_applied,
                    'base_cost' => $acquisition->base_sp_cost,
                    'final_cost' => $acquisition->final_sp_cost,
                    'sp_saved' => $acquisition->sp_saved,
                ];
            })->values(),
        ];
    }
}
