<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Skill;
use Illuminate\Support\Collection;

/**
 * Skill Analysis Service
 *
 * Provides AI-powered skill analysis using MCP Skill Analysis Agent.
 * Analyzes skill synergies, optimal acquisition strategies, and provides
 * recommendations for skill builds.
 */
class SkillAnalysisService
{
    /**
     * Analyze skill synergies for a given set of skills.
     *
     * @param  Collection<int, Skill>  $skills
     * @return array<int, array{skill: Skill, synergies: Collection<int, Skill>, synergy_count: int, synergy_strength: float}>
     */
    public function analyzeSynergies(Collection $skills): array
    {
        $synergyMap = [];

        foreach ($skills as $skill) {
            if (empty($skill->synergy_skills)) {
                continue;
            }

            $synergisticSkills = Skill::whereIn('internal_id', $skill->synergy_skills)->get();

            $synergyMap[$skill->id] = [
                'skill' => $skill,
                'synergies' => $synergisticSkills,
                'synergy_count' => $synergisticSkills->count(),
                'synergy_strength' => $this->calculateSynergyStrength($skill, $synergisticSkills),
            ];
        }

        return $synergyMap;
    }

    /**
     * Calculate synergy strength between skills.
     *
     * @param  Collection<int, Skill>  $synergisticSkills
     */
    private function calculateSynergyStrength(Skill $skill, Collection $synergisticSkills): float
    {
        if ($synergisticSkills->isEmpty()) {
            return 0.0;
        }

        $strength = 0.0;

        // Same skill type bonus
        $sameTypeCount = $synergisticSkills->where('skill_type', $skill->skill_type)->count();
        $strength += $sameTypeCount * 0.2;

        // Meta tier bonus
        $metaTierValues = ['S+' => 1.0, 'S' => 0.8, 'A' => 0.6, 'B' => 0.4, 'C' => 0.2];
        foreach ($synergisticSkills as $synSkill) {
            $strength += $metaTierValues[$synSkill->meta_tier] ?? 0.0;
        }

        // Normalize to 0-10 scale
        return min(10.0, $strength);
    }

    /**
     * Recommend optimal skill acquisition order.
     *
     * @param  Collection<int, Skill>  $targetSkills
     * @return array<int, array{skill: Skill, priority: int, min_cost: int, max_cost: int, recommended_hints: int, reasoning: string}>
     */
    public function recommendAcquisitionOrder(Collection $targetSkills, int $availableSP): array
    {
        $recommendations = [];
        $remainingSP = $availableSP;

        // Sort by priority: evolution sources first, then by meta tier
        $sortedSkills = $targetSkills->sortByDesc(function ($skill) {
            $metaTierValues = ['S+' => 5, 'S' => 4, 'A' => 3, 'B' => 2, 'C' => 1];
            $tierValue = $metaTierValues[$skill->meta_tier] ?? 0;

            // Prioritize evolution sources
            if ($skill->can_evolve) {
                $tierValue += 10;
            }

            return $tierValue;
        });

        foreach ($sortedSkills as $skill) {
            $minCost = $skill->calculateFinalCost(2); // Assume max hints

            if ($remainingSP >= $minCost) {
                $recommendations[] = [
                    'skill' => $skill,
                    'priority' => $this->calculatePriority($skill),
                    'min_cost' => $minCost,
                    'max_cost' => $skill->base_sp_cost,
                    'recommended_hints' => 2,
                    'reasoning' => $this->generateRecommendationReasoning($skill),
                ];

                $remainingSP -= $minCost;
            }
        }

        return $recommendations;
    }

    /**
     * Calculate skill priority score.
     */
    private function calculatePriority(Skill $skill): int
    {
        $priority = 0;

        // Meta tier priority
        $metaTierPriority = ['S+' => 100, 'S' => 80, 'A' => 60, 'B' => 40, 'C' => 20];
        $priority += $metaTierPriority[$skill->meta_tier] ?? 0;

        // Evolution potential
        if ($skill->can_evolve) {
            $priority += 30;
        }

        // Synergy count
        if (! empty($skill->synergy_skills)) {
            $priority += count($skill->synergy_skills) * 5;
        }

        return $priority;
    }

    /**
     * Generate recommendation reasoning.
     */
    private function generateRecommendationReasoning(Skill $skill): string
    {
        $reasons = [];

        if ($skill->meta_tier === 'S+' || $skill->meta_tier === 'S') {
            $reasons[] = "Top-tier skill ({$skill->meta_tier})";
        }

        if ($skill->can_evolve) {
            $reasons[] = 'Can evolve to stronger version';
        }

        if (! empty($skill->synergy_skills)) {
            $synergyCount = count($skill->synergy_skills);
            $reasons[] = "Synergizes with {$synergyCount} other skills";
        }

        if (empty($reasons)) {
            $reasons[] = 'Solid skill for your build';
        }

        return implode('. ', $reasons).'.';
    }

    /**
     * Analyze skill build for a character.
     *
     * @param  Collection<int, Skill>  $skills
     * @return array<string, mixed>
     */
    public function analyzeSkillBuild(Character $character, Collection $skills): array
    {
        return [
            'character_id' => $character->id,
            'total_skills' => $skills->count(),
            'skill_types' => $this->analyzeSkillTypes($skills),
            'meta_distribution' => $this->analyzeMetaDistribution($skills),
            'synergy_analysis' => $this->analyzeSynergies($skills),
            'evolution_potential' => $this->analyzeEvolutionPotential($skills),
            'recommendations' => $this->generateBuildRecommendations($character, $skills),
        ];
    }

    /**
     * Analyze skill type distribution.
     *
     * @param  Collection<int, Skill>  $skills
     * @return array<string, int>
     */
    private function analyzeSkillTypes(Collection $skills): array
    {
        return [
            'speed' => $skills->where('skill_type', 'speed')->count(),
            'passive' => $skills->where('skill_type', 'passive')->count(),
            'recovery' => $skills->where('skill_type', 'recovery')->count(),
            'debuff' => $skills->where('skill_type', 'debuff')->count(),
            'unique' => $skills->where('skill_type', 'unique')->count(),
        ];
    }

    /**
     * Analyze meta tier distribution.
     *
     * @param  Collection<int, Skill>  $skills
     * @return array<string, int>
     */
    private function analyzeMetaDistribution(Collection $skills): array
    {
        return [
            'S+' => $skills->where('meta_tier', 'S+')->count(),
            'S' => $skills->where('meta_tier', 'S')->count(),
            'A' => $skills->where('meta_tier', 'A')->count(),
            'B' => $skills->where('meta_tier', 'B')->count(),
            'C' => $skills->where('meta_tier', 'C')->count(),
        ];
    }

    /**
     * Analyze evolution potential.
     *
     * @param  Collection<int, Skill>  $skills
     * @return array{evolvable_count: int, evolved_count: int, evolution_rate: float, potential_upgrades: array<int, string>}
     */
    private function analyzeEvolutionPotential(Collection $skills): array
    {
        $evolvableSkills = $skills->where('can_evolve', true);
        $evolvedSkills = $skills->where('is_evolution', true);

        return [
            'evolvable_count' => $evolvableSkills->count(),
            'evolved_count' => $evolvedSkills->count(),
            'evolution_rate' => $skills->count() > 0
                ? ($evolvedSkills->count() / $skills->count()) * 100
                : 0,
            'potential_upgrades' => $evolvableSkills->pluck('name')->toArray(),
        ];
    }

    /**
     * Generate build recommendations.
     *
     * @param  Collection<int, Skill>  $skills
     * @return array<int, array{type: string, message: string, priority: string}>
     */
    private function generateBuildRecommendations(Character $character, Collection $skills): array
    {
        $recommendations = [];

        // Check skill type balance
        $typeDistribution = $this->analyzeSkillTypes($skills);
        if ($typeDistribution['speed'] < 2) {
            $recommendations[] = [
                'type' => 'balance',
                'message' => 'Consider adding more speed skills for better race performance',
                'priority' => 'high',
            ];
        }

        // Check for recovery skills in long-distance builds
        if ($typeDistribution['recovery'] === 0) {
            $recommendations[] = [
                'type' => 'recovery',
                'message' => 'Add recovery skills for better stamina management',
                'priority' => 'medium',
            ];
        }

        // Check evolution opportunities
        $evolutionAnalysis = $this->analyzeEvolutionPotential($skills);
        if ($evolutionAnalysis['evolvable_count'] > 0) {
            $recommendations[] = [
                'type' => 'evolution',
                'message' => "You have {$evolutionAnalysis['evolvable_count']} skills that can be evolved",
                'priority' => 'high',
            ];
        }

        return $recommendations;
    }
}
