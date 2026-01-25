<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Skill Evolution Service
 *
 * Manages automatic skill evolution from Normal to Rare variants,
 * prerequisite checking, and SP efficiency calculations for evolution paths.
 *
 * Evolution Mechanics:
 * - Normal skills (120-180 SP) can evolve to Rare skills (180-240 SP)
 * - Evolution completely replaces the Normal skill with the Rare version
 * - Prerequisites must be met before evolution can occur
 * - SP efficiency analysis helps decide: evolve existing or acquire Rare directly
 */
class SkillEvolutionService
{
    public function __construct(
        private SkillHintService $hintService
    ) {}

    /**
     * Check if a skill can evolve for a character.
     */
    public function canEvolve(Character $character, Skill $skill): bool
    {
        // Skill must have evolution capability
        if (! $skill->canEvolve()) {
            return false;
        }

        // Character must have acquired the Normal skill
        /** @phpstan-ignore-next-line - Eloquent where() with 2 args is valid */
        $hasAcquired = SkillAcquisition::where('character_id', $character->id)
            ->where('skill_id', $skill->id)
            ->where('is_active', true)
            ->exists();

        if (! $hasAcquired) {
            return false;
        }

        // Check prerequisites for evolution
        return $this->checkEvolutionPrerequisites($character, $skill);
    }

    /**
     * Check if all prerequisites for evolution are met.
     */
    public function checkEvolutionPrerequisites(Character $character, Skill $skill): bool
    {
        $evolutionTarget = $skill->evolutionTarget;

        if (! $evolutionTarget) {
            return false;
        }

        // Check stat requirements
        if (! empty($evolutionTarget->stat_requirements)) {
            foreach ($evolutionTarget->stat_requirements as $stat => $required) {
                $currentStat = $character->current_stats[$stat] ?? 0;
                if ($currentStat < $required) {
                    return false;
                }
            }
        }

        // Check prerequisite skills (if any)
        if (! empty($evolutionTarget->synergy_skills)) {
            /** @phpstan-ignore-next-line - Eloquent whereIn() with 2 args is valid */
            $prerequisiteSkills = Skill::whereIn('internal_id', $evolutionTarget->synergy_skills)
                ->where('rarity', '=', 'normal')
                ->pluck('id');

            if ($prerequisiteSkills->isNotEmpty()) {
                /** @phpstan-ignore-next-line - Eloquent where() with 2 args is valid */
                $acquiredPrerequisites = SkillAcquisition::where('character_id', $character->id)
                    ->whereIn('skill_id', $prerequisiteSkills)
                    ->where('is_active', true)
                    ->count();

                if ($acquiredPrerequisites < $prerequisiteSkills->count()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Automatically evolve a Normal skill to its Rare counterpart.
     *
     * @return array<string, mixed>
     */
    public function evolveSkill(Character $character, Skill $normalSkill): array
    {
        if (! $this->canEvolve($character, $normalSkill)) {
            return [
                'success' => false,
                'message' => 'Skill cannot be evolved at this time',
                'reason' => $this->getEvolutionBlockReason($character, $normalSkill),
            ];
        }

        $rareSkill = $normalSkill->evolutionTarget;
        if (! $rareSkill instanceof Skill) {
            return [
                'success' => false,
                'message' => 'Evolution target skill not found',
                'reason' => 'Evolution target skill not found',
            ];
        }

        // Deactivate the Normal skill acquisition
        /** @phpstan-ignore-next-line - Eloquent where() with 2 args is valid */
        $normalAcquisition = SkillAcquisition::where('character_id', $character->id)
            ->where('skill_id', $normalSkill->id)
            ->where('is_active', true)
            ->first();

        if ($normalAcquisition) {
            /** @phpstan-ignore-next-line - Eloquent Model update() accepts array */
            $normalAcquisition->update(['is_active' => false]);
        }

        // Get hints for the Rare skill
        $rareHints = $this->hintService->getUnusedHintsForSkill($character, $rareSkill);
        $hintCount = $rareHints->count();
        $discountPercentage = $this->hintService->calculateDiscountPercentage($hintCount);
        $finalCost = $this->hintService->calculateFinalCost($rareSkill, $hintCount);
        $spSaved = $this->hintService->calculateSpSaved($rareSkill, $hintCount);

        // Create new acquisition for the Rare skill
        $rareAcquisition = SkillAcquisition::create([
            'character_id' => $character->id,
            'skill_id' => $rareSkill->id,
            'career_id' => $normalAcquisition?->career_id,
            'turn_acquired' => $normalAcquisition?->turn_acquired,
            'career_phase' => $normalAcquisition?->career_phase ?? $character->career_stage,
            'acquisition_method' => 'evolution',
            'base_sp_cost' => $rareSkill->base_sp_cost,
            'hints_used' => $hintCount,
            'total_discount_percentage' => $discountPercentage,
            'final_sp_cost' => $finalCost,
            'sp_saved' => $spSaved,
            'is_evolution' => true,
            'evolved_from_skill_id' => $normalSkill->id,
            'replaced_skill' => true,
            'acquisition_context' => [
                'evolution_type' => 'automatic',
                'normal_skill_id' => $normalSkill->id,
                'normal_skill_name' => $normalSkill->name,
                'rare_skill_id' => $rareSkill->id,
                'rare_skill_name' => $rareSkill->name,
            ],
            'hint_sources' => $rareHints->pluck('source_name')->toArray(),
            'priority_level' => 'high',
            'is_active' => true,
        ]);

        // Mark hints as used
        $this->hintService->markHintsAsUsed($character, $rareSkill);

        Log::info('Skill evolved successfully', [
            'character_id' => $character->id,
            'normal_skill' => $normalSkill->name,
            'rare_skill' => $rareSkill->name,
            'sp_cost' => $finalCost,
            'sp_saved' => $spSaved,
        ]);

        return [
            'success' => true,
            'message' => "Successfully evolved {$normalSkill->name} to {$rareSkill->name}",
            'normal_skill' => $normalSkill,
            'rare_skill' => $rareSkill,
            'acquisition' => $rareAcquisition,
            'sp_cost' => $finalCost,
            'sp_saved' => $spSaved,
            'hints_used' => $hintCount,
        ];
    }

    /**
     * Get the reason why a skill cannot evolve.
     */
    private function getEvolutionBlockReason(Character $character, Skill $skill): string
    {
        if (! $skill->canEvolve()) {
            return 'Skill does not have an evolution path';
        }

        /** @phpstan-ignore-next-line - Eloquent where() with 2 args is valid */
        $hasAcquired = SkillAcquisition::where('character_id', $character->id)
            ->where('skill_id', $skill->id)
            ->where('is_active', true)
            ->exists();

        if (! $hasAcquired) {
            return 'Normal skill has not been acquired yet';
        }

        $evolutionTarget = $skill->evolutionTarget;

        if (! $evolutionTarget) {
            return 'Evolution target skill not found';
        }

        // Check stat requirements
        if (! empty($evolutionTarget->stat_requirements)) {
            foreach ($evolutionTarget->stat_requirements as $stat => $required) {
                $currentStat = $character->current_stats[$stat] ?? 0;
                if ($currentStat < $required) {
                    return "Insufficient {$stat}: {$currentStat}/{$required} required";
                }
            }
        }

        // Check prerequisite skills
        if (! empty($evolutionTarget->synergy_skills)) {
            /** @phpstan-ignore-next-line - Eloquent whereIn() with 2 args is valid */
            $prerequisiteSkills = Skill::whereIn('internal_id', $evolutionTarget->synergy_skills)
                ->where('rarity', '=', 'normal')
                ->get();

            if ($prerequisiteSkills->isNotEmpty()) {
                $missingSkills = [];
                foreach ($prerequisiteSkills as $prereqSkill) {
                    /** @phpstan-ignore-next-line - Eloquent where() with 2 args is valid */
                    $hasPrereq = SkillAcquisition::where('character_id', $character->id)
                        ->where('skill_id', $prereqSkill->id)
                        ->where('is_active', true)
                        ->exists();

                    if (! $hasPrereq) {
                        $missingSkills[] = $prereqSkill->name;
                    }
                }

                if (! empty($missingSkills)) {
                    return 'Missing prerequisite skills: '.implode(', ', $missingSkills);
                }
            }
        }

        return 'Unknown reason';
    }

    /**
     * Calculate SP efficiency: evolution vs direct acquisition.
     *
     * @return array<string, mixed>
     */
    public function calculateEvolutionEfficiency(Character $character, Skill $normalSkill): array
    {
        $rareSkill = $normalSkill->evolutionTarget;

        if (! $rareSkill) {
            return [
                'has_evolution' => false,
                'message' => 'Skill does not have an evolution path',
            ];
        }

        // Get current hints for both skills
        $normalHints = $this->hintService->getUnusedHintsForSkill($character, $normalSkill);
        $rareHints = $this->hintService->getUnusedHintsForSkill($character, $rareSkill);

        // Calculate costs for evolution path
        $normalCost = $this->hintService->calculateFinalCost($normalSkill, $normalHints->count());
        $rareCostAfterEvolution = $this->hintService->calculateFinalCost($rareSkill, $rareHints->count());
        $evolutionPathTotalCost = $normalCost + $rareCostAfterEvolution;

        // Calculate cost for direct Rare acquisition
        $directRareCost = $this->hintService->calculateFinalCost($rareSkill, $rareHints->count());

        // Calculate savings
        $spSavings = $directRareCost - $evolutionPathTotalCost;
        $isEvolutionBetter = $spSavings > 0;

        return [
            'has_evolution' => true,
            'normal_skill' => [
                'id' => $normalSkill->id,
                'name' => $normalSkill->name,
                'base_cost' => $normalSkill->base_sp_cost,
                'hints' => $normalHints->count(),
                'final_cost' => $normalCost,
            ],
            'rare_skill' => [
                'id' => $rareSkill->id,
                'name' => $rareSkill->name,
                'base_cost' => $rareSkill->base_sp_cost,
                'hints' => $rareHints->count(),
                'final_cost' => $rareCostAfterEvolution,
            ],
            'evolution_path' => [
                'total_cost' => $evolutionPathTotalCost,
                'steps' => [
                    "1. Acquire {$normalSkill->name}: {$normalCost} SP",
                    "2. Evolve to {$rareSkill->name}: {$rareCostAfterEvolution} SP",
                ],
            ],
            'direct_acquisition' => [
                'total_cost' => $directRareCost,
                'description' => "Acquire {$rareSkill->name} directly: {$directRareCost} SP",
            ],
            'comparison' => [
                'sp_savings' => $spSavings,
                'is_evolution_better' => $isEvolutionBetter,
                'recommendation' => $isEvolutionBetter
                    ? "Evolution path saves {$spSavings} SP - recommended"
                    : 'Direct acquisition is more efficient by '.abs($spSavings).' SP',
            ],
        ];
    }

    /**
     * Get all available evolution opportunities for a character.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEvolutionOpportunities(Character $character): array
    {
        // Get all Normal skills that can evolve
        /** @phpstan-ignore-next-line - Eloquent where() with 2 args is valid */
        $evolvableSkills = Skill::where('can_evolve', true)
            ->where('rarity', '=', 'normal')
            ->with('evolutionTarget')
            ->get();

        $opportunities = [];

        foreach ($evolvableSkills as $skill) {
            $canEvolve = $this->canEvolve($character, $skill);
            $efficiency = $this->calculateEvolutionEfficiency($character, $skill);

            $opportunities[] = [
                'skill' => $skill,
                'can_evolve_now' => $canEvolve,
                'block_reason' => $canEvolve ? null : $this->getEvolutionBlockReason($character, $skill),
                'efficiency' => $efficiency,
                'priority' => $this->calculateEvolutionPriority($skill, $efficiency, $canEvolve),
            ];
        }

        // Sort by priority (high to low)
        usort($opportunities, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        return $opportunities;
    }

    /**
     * Calculate evolution priority score.
     *
     * @param  array<string, mixed>  $efficiency
     */
    private function calculateEvolutionPriority(Skill $skill, array $efficiency, bool $canEvolve): int
    {
        $priority = 0;

        // Can evolve now gets highest priority
        if ($canEvolve) {
            $priority = ($priority ?? 0) + 100;
        }

        // SP savings bonus
        if (isset($efficiency['comparison']['sp_savings']) && $efficiency['comparison']['sp_savings'] > 0) {
            $priority = ($priority ?? 0) + min(50, $efficiency['comparison']['sp_savings']);
        }

        // Meta tier bonus
        $metaTierBonus = ['S+' => 30, 'S' => 25, 'A' => 20, 'B' => 10, 'C' => 5];
        $priority = ($priority ?? 0) + $metaTierBonus[$skill->meta_tier] ?? 0;

        return $priority;
    }

    /**
     * Get evolution chain for a skill (all related skills in evolution path).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getEvolutionChain(Skill $skill): array
    {
        $chain = [];

        // Get the source (Normal) skill
        if ($skill->evolutionSource) {
            $chain[] = [
                'skill' => $skill->evolutionSource,
                'position' => 'source',
                'rarity' => $skill->evolutionSource->rarity,
            ];
        } elseif ($skill->rarity === 'normal' && $skill->can_evolve) {
            $chain[] = [
                'skill' => $skill,
                'position' => 'source',
                'rarity' => $skill->rarity,
            ];
        }

        // Add current skill if it's not already added
        if ($skill->rarity === 'rare' && $skill->is_evolution) {
            $chain[] = [
                'skill' => $skill,
                'position' => 'target',
                'rarity' => $skill->rarity,
            ];
        }

        // Get the target (Rare) skill
        if ($skill->evolutionTarget && $skill->rarity === 'normal') {
            $chain[] = [
                'skill' => $skill->evolutionTarget,
                'position' => 'target',
                'rarity' => $skill->evolutionTarget->rarity,
            ];
        }

        return $chain;
    }

    /**
     * Plan optimal skill evolution timing for a character.
     *
     * @param  Collection<int, Skill>  $targetSkills
     * @return array<int, array<string, mixed>>
     */
    public function planEvolutionTiming(Character $character, Collection $targetSkills): array
    {
        $plan = [];

        foreach ($targetSkills as $skill) {
            if (! $skill->canEvolve()) {
                continue;
            }

            $canEvolveNow = $this->canEvolve($character, $skill);
            $efficiency = $this->calculateEvolutionEfficiency($character, $skill);

            $timing = [
                'skill' => $skill,
                'evolution_target' => $skill->evolutionTarget,
                'can_evolve_now' => $canEvolveNow,
                'efficiency_analysis' => $efficiency,
            ];

            if ($canEvolveNow) {
                $timing['recommendation'] = 'Ready to evolve now';
                $timing['timing'] = 'immediate';
            } else {
                $blockReason = $this->getEvolutionBlockReason($character, $skill);
                $timing['recommendation'] = "Wait until: {$blockReason}";
                $timing['timing'] = 'delayed';
            }

            $plan[] = $timing;
        }

        // Sort by timing (immediate first)
        usort($plan, function ($a, $b) {
            if ($a['timing'] === $b['timing']) {
                return 0;
            }

            return $a['timing'] === 'immediate' ? -1 : 1;
        });

        return $plan;
    }

    /**
     * Get comprehensive evolution roadmap for a character.
     *
     * @return array<string, mixed>
     */
    public function getEvolutionRoadmap(Character $character): array
    {
        $opportunities = $this->getEvolutionOpportunities($character);

        // Separate into categories
        $readyToEvolve = array_filter($opportunities, fn ($opp) => $opp['can_evolve_now']);
        $pendingPrerequisites = array_filter($opportunities, fn ($opp) => ! $opp['can_evolve_now']);

        // Calculate total potential SP savings
        $totalPotentialSavings = array_reduce($opportunities, function ($carry, $opp) {
            if (isset($opp['efficiency']['comparison']['sp_savings']) && $opp['efficiency']['comparison']['sp_savings'] > 0) {
                return $carry + $opp['efficiency']['comparison']['sp_savings'];
            }

            return $carry;
        }, 0);

        return [
            'character_id' => $character->id,
            'total_evolution_opportunities' => count($opportunities),
            'ready_to_evolve' => count($readyToEvolve),
            'pending_prerequisites' => count($pendingPrerequisites),
            'total_potential_sp_savings' => $totalPotentialSavings,
            'immediate_opportunities' => array_values($readyToEvolve),
            'future_opportunities' => array_values($pendingPrerequisites),
            'recommendations' => $this->generateRoadmapRecommendations($readyToEvolve, $pendingPrerequisites),
        ];
    }

    /**
     * Generate recommendations for evolution roadmap.
     *
     * @param  array<int, array<string, mixed>>  $readyToEvolve
     * @param  array<int, array<string, mixed>>  $pendingPrerequisites
     * @return array<int, array<string, mixed>>
     */
    private function generateRoadmapRecommendations(array $readyToEvolve, array $pendingPrerequisites): array
    {
        $recommendations = [];

        if (! empty($readyToEvolve)) {
            $topPriority = reset($readyToEvolve);
            $recommendations[] = [
                'type' => 'immediate_action',
                'priority' => 'high',
                'message' => "Evolve {$topPriority['skill']->name} immediately for maximum benefit",
                'skill_id' => $topPriority['skill']->id,
            ];
        }

        if (! empty($pendingPrerequisites)) {
            $recommendations[] = [
                'type' => 'preparation',
                'priority' => 'medium',
                'message' => count($pendingPrerequisites).' skills can be evolved after meeting prerequisites',
                'count' => count($pendingPrerequisites),
            ];
        }

        if (empty($readyToEvolve) && empty($pendingPrerequisites)) {
            $recommendations[] = [
                'type' => 'info',
                'priority' => 'low',
                'message' => 'No evolution opportunities available at this time',
            ];
        }

        return $recommendations;
    }
}
