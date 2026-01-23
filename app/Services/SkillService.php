<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Skill Management Service
 *
 * Handles skill evolution logic (Normal → Rare) and Hint Level discounts.
 * Implements 20% SP reduction per duplicate hint (40% max).
 *
 * Requirements: Task 3.2
 */
class SkillService
{
    /**
     * Hint discount percentage per duplicate (20%)
     */
    protected const HINT_DISCOUNT_PERCENT = 20;

    /**
     * Maximum hint discount (40% = 2 hints)
     */
    protected const MAX_HINT_DISCOUNT = 40;

    /**
     * Calculate the final SP cost with hint discounts
     *
     * @param  int  $baseCost  Base SP cost of the skill
     * @param  int  $hintCount  Number of hint duplicates obtained
     * @return int Final SP cost after discount
     */
    public function calculateFinalCost(int $baseCost, int $hintCount): int
    {
        // Maximum 2 hints effective (40% max discount)
        $effectiveHints = min($hintCount, 2);
        $discountPercent = $effectiveHints * self::HINT_DISCOUNT_PERCENT;

        // Calculate discounted cost
        $discount = (int) (($baseCost * $discountPercent) / 100);

        return max(0, $baseCost - $discount);
    }

    /**
     * Get discount percentage for a given hint count
     */
    public function getDiscountPercentage(int $hintCount): float
    {
        $effectiveHints = min($hintCount, 2);

        return (float) ($effectiveHints * self::HINT_DISCOUNT_PERCENT);
    }

    /**
     * Process skill evolution (Normal → Rare)
     *
     * @param  Skill  $skill  The skill to evolve
     * @param  Character  $character  The character acquiring the evolved skill
     * @return Skill|null The evolved skill or null if evolution not possible
     */
    public function evolveSkill(Skill $skill, Character $character): ?Skill
    {
        // Check if skill can evolve
        if (! $skill->canEvolve()) {
            return null;
        }

        // Get the evolution target
        $evolvedSkill = $skill->evolutionTarget;
        if (! $evolvedSkill) {
            return null;
        }

        // Process evolution within transaction
        return DB::transaction(function () use ($skill, $evolvedSkill, $character) {
            // Deactivate the original skill acquisition if exists
            SkillAcquisition::where('character_id', '=', $character->id, 'and')
                ->where('skill_id', '=', $skill->id, 'and')
                ->update(['is_active' => false]);

            // Create the evolved skill acquisition
            SkillAcquisition::create([
                'character_id' => $character->id,
                'skill_id' => $evolvedSkill->id,
                'career_id' => $character->currentCareer?->id,
                'turn_acquired' => $character->current_turn ?? 1,
                'career_phase' => $character->career_stage ?? 'junior',
                'acquisition_method' => 'evolution',
                'base_sp_cost' => $evolvedSkill->base_sp_cost,
                'final_sp_cost' => $evolvedSkill->base_sp_cost,
                'is_evolution' => true,
                'is_active' => true,
            ]);

            return $evolvedSkill;
        });
    }

    /**
     * Get evolution opportunities for a character
     *
     * @return Collection<int, Skill>
     */
    public function getEvolutionOpportunities(Character $character): Collection
    {
        // Get all active skills for the character that can evolve
        return $character->skills()
            ->wherePivot('is_active', true)
            ->where('can_evolve', true)
            ->whereNotNull('evolution_target_id')
            ->with('evolutionTarget')
            ->get();
    }

    /**
     * Calculate SP saved with current hint levels
     *
     * @return int SP saved
     */
    public function calculateSpSaved(Skill $skill, Character $character): int
    {
        $hintCount = $skill->hints()
            ->where('character_id', $character->id)
            ->count();

        return $skill->base_sp_cost - $this->calculateFinalCost($skill->base_sp_cost, $hintCount);
    }

    /**
     * Get skill recommendations based on character stats and goals
     *
     * @param  int  $maxRecommendations  Maximum number of recommendations
     * @return Collection<int, array{skill: Skill, priority: string, reason: string, cost: int}>
     */
    public function getRecommendations(Character $character, int $maxRecommendations = 5): Collection
    {
        $availableSp = $character->available_sp ?? 0;
        $priorities = $character->stat_priorities ?? [];

        // Get skills character doesn't have yet
        $acquiredSkillIds = $character->skills()->pluck('ucp_skills.id')->toArray();

        return Skill::whereNotIn('id', $acquiredSkillIds, 'and')
            ->where('is_active', true)
            ->where('base_sp_cost', '<=', $availableSp)
            ->orderBy('meta_tier', 'desc')
            ->orderBy('base_sp_cost', 'asc')
            ->take($maxRecommendations)
            ->get()
            ->map(function (Skill $skill) use ($priorities) {
                return [
                    'skill' => $skill,
                    'priority' => $this->determinePriority($skill, $priorities),
                    'reason' => $this->generateRecommendationReason($skill, $priorities),
                    'cost' => $skill->base_sp_cost,
                ];
            });
    }

    /**
     * Determine skill priority based on character priorities
     *
     * @param  array<string, mixed>  $priorities
     */
    protected function determinePriority(Skill $skill, array $priorities): string
    {
        // Check if skill's primary stat matches high priority
        $skillType = $skill->skill_type ?? 'general';

        if (isset($priorities[$skillType]) && $priorities[$skillType] >= 4) {
            return 'high';
        }

        if ($skill->meta_tier === 'S' || $skill->meta_tier === 'A') {
            return 'high';
        }

        return 'medium';
    }

    /**
     * Generate recommendation reason
     *
     * @param  array<string, mixed>  $priorities
     */
    protected function generateRecommendationReason(Skill $skill, array $priorities): string
    {
        $reasons = [];

        if ($skill->meta_tier === 'S') {
            $reasons[] = 'Meta-defining skill';
        } elseif ($skill->meta_tier === 'A') {
            $reasons[] = 'High-tier skill';
        }

        if ($skill->can_evolve) {
            $reasons[] = 'Has evolution path';
        }

        if (empty($reasons)) {
            $reasons[] = 'Good value for SP cost';
        }

        return implode('. ', $reasons).'.';
    }
}
