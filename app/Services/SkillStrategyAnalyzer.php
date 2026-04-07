<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aptitude;
use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use Illuminate\Support\Collection;

class SkillStrategyAnalyzer
{
    /**
     * @param  Collection<int, Skill>  $skills
     */
    public function detectUniqueSkill(Collection $skills): ?Skill
    {
        return $skills->first(function (Skill $skill): bool {
            return $skill->character_exclusive !== null || $skill->skill_type === 'unique';
        });
    }

    /**
     * @param  Collection<int, Skill>  $skills
     * @return array{score: float, is_supported: bool, missing_conditions: array<int, string>, recommendations: array<int, string>}
     */
    public function analyzeUniqueSkillCenterpiece(Character $character, Skill $uniqueSkill, Collection $skills): array
    {
        $conditions = is_array($uniqueSkill->activation_conditions) ? $uniqueSkill->activation_conditions : [];
        $supportingSkills = $skills->filter(fn (Skill $skill): bool => $skill->id !== $uniqueSkill->id);

        $score = 55.0;
        $missingConditions = [];
        $recommendations = [];

        if ($conditions === []) {
            return [
                'score' => 70.0,
                'is_supported' => true,
                'missing_conditions' => [],
                'recommendations' => [],
            ];
        }

        if (isset($conditions['phase'])) {
            $phase = (string) $conditions['phase'];
            $matchingPhaseSkillCount = $supportingSkills->filter(function (Skill $skill) use ($phase): bool {
                $skillConditions = is_array($skill->activation_conditions) ? $skill->activation_conditions : [];

                return ($skillConditions['phase'] ?? null) === $phase;
            })->count();

            if ($matchingPhaseSkillCount > 0) {
                $score += min(15.0, $matchingPhaseSkillCount * 7.5);
            } else {
                $missingConditions[] = str_replace('_', ' ', $phase).' setup';
                $recommendations[] = 'Add a supporting skill that activates during '.str_replace('_', ' ', $phase);
            }
        }

        if (isset($conditions['running_style'])) {
            $requiredStyle = (string) $conditions['running_style'];
            $characterStyle = $this->resolveRunningStyle($character);

            if ($requiredStyle === 'any' || $requiredStyle === $characterStyle) {
                $score += 15.0;
            } else {
                $missingConditions[] = $requiredStyle.' running style alignment';
                $recommendations[] = 'Adjust the build to support '.$requiredStyle.' style unique skill activation';
            }
        }

        if (isset($conditions['distance'])) {
            $requiredDistance = (string) $conditions['distance'];
            $characterDistance = $this->resolveDistance($character);

            if ($requiredDistance === $characterDistance) {
                $score += 10.0;
            } else {
                $missingConditions[] = $requiredDistance.' distance alignment';
                $recommendations[] = 'Tune the build toward '.$requiredDistance.' races to match the unique skill';
            }
        }

        if (isset($conditions['position'])) {
            $position = strtolower((string) $conditions['position']);
            $positionSupportCount = $supportingSkills->filter(function (Skill $skill) use ($position): bool {
                $payload = strtolower(implode(' ', array_filter([
                    $skill->name,
                    $skill->description,
                    is_array($skill->effects) ? implode(' ', array_map('strval', $skill->effects)) : null,
                ])));

                return str_contains($payload, 'position')
                    || str_contains($payload, 'corner')
                    || str_contains($payload, $position)
                    || in_array($skill->skill_type, ['speed', 'passive', 'recovery'], true);
            })->count();

            if ($positionSupportCount > 0) {
                $score += min(10.0, $positionSupportCount * 5.0);
            } else {
                $missingConditions[] = 'race positioning support';
                $recommendations[] = 'Add positioning or pace-control skills to set up the unique skill';
            }
        }

        $score = min(100.0, round($score, 1));

        return [
            'score' => $score,
            'is_supported' => $missingConditions === [],
            'missing_conditions' => $missingConditions,
            'recommendations' => array_values(array_unique($recommendations)),
        ];
    }

    /**
     * @param  Collection<int, SkillAcquisition>  $acquisitions
     * @return array{total_sp_spent: int, total_sp_saved: int, avg_hint_level: float, avg_discount: float, efficiency_score: float}
     */
    public function calculateSPEfficiency(Collection $acquisitions): array
    {
        if ($acquisitions->isEmpty()) {
            return [
                'total_sp_spent' => 0,
                'total_sp_saved' => 0,
                'avg_hint_level' => 0.0,
                'avg_discount' => 0.0,
                'efficiency_score' => 50.0,
            ];
        }

        $totalSpent = 0;
        $totalSaved = 0;
        $hintLevels = [];
        $discounts = [];

        foreach ($acquisitions as $acquisition) {
            $totalSpent += (int) ($acquisition->final_sp_cost ?? $acquisition->sp_cost ?? 0);
            $totalSaved += (int) ($acquisition->sp_saved ?? 0);

            $hintLevel = $this->resolveHintLevel($acquisition);
            $hintLevels[] = $hintLevel;
            $discounts[] = (float) ($acquisition->total_discount_percentage ?? $acquisition->sp_discount_applied ?? 0.0);
        }

        $avgHintLevel = round(array_sum($hintLevels) / count($hintLevels), 1);
        $avgDiscount = round(array_sum($discounts) / count($discounts), 1);

        $efficiencyScore = 50.0 + min(50.0, $avgDiscount * 1.25);

        if ($avgHintLevel >= 3.0) {
            $efficiencyScore += 5.0;
        }

        return [
            'total_sp_spent' => $totalSpent,
            'total_sp_saved' => $totalSaved,
            'avg_hint_level' => $avgHintLevel,
            'avg_discount' => $avgDiscount,
            'efficiency_score' => round(min(100.0, $efficiencyScore), 1),
        ];
    }

    /**
     * @return array{skills_with_hints: int, avg_pool_size: float, consistency_score: float}
     */
    public function analyzeSupportDeckHintPools(Character $character): array
    {
        $supportCards = $character->supportCards;

        if ($supportCards->isEmpty()) {
            return [
                'skills_with_hints' => 0,
                'avg_pool_size' => 0.0,
                'consistency_score' => 50.0,
            ];
        }

        $poolSizes = [];
        $matchedSkills = 0;

        foreach ($supportCards as $supportCardLink) {
            if (! $supportCardLink instanceof CharacterSupportCard || $supportCardLink->supportCard === null) {
                continue;
            }

            $pool = $supportCardLink->supportCard->skill_hints_provided;
            if (! is_array($pool)) {
                continue;
            }

            $poolSizes[] = count($pool);
        }

        $skills = $character->skillAcquisitions->loadMissing('skill')->pluck('skill')->filter();

        foreach ($skills as $skill) {
            foreach ($supportCards as $supportCardLink) {
                $pool = $supportCardLink->supportCard?->skill_hints_provided;

                if (! is_array($pool)) {
                    continue;
                }

                $poolText = strtolower(implode(' ', array_map('strval', $pool)));
                if (str_contains($poolText, strtolower($skill->name))) {
                    $matchedSkills++;
                    break;
                }
            }
        }

        if ($poolSizes === []) {
            return [
                'skills_with_hints' => $matchedSkills,
                'avg_pool_size' => 0.0,
                'consistency_score' => 50.0,
            ];
        }

        $avgPoolSize = round(array_sum($poolSizes) / count($poolSizes), 1);

        $consistencyScore = match (true) {
            $avgPoolSize <= 3 => 100.0,
            $avgPoolSize <= 5 => 80.0,
            $avgPoolSize <= 8 => 65.0,
            $avgPoolSize <= 12 => 45.0,
            default => 25.0,
        };

        return [
            'skills_with_hints' => $matchedSkills,
            'avg_pool_size' => $avgPoolSize,
            'consistency_score' => $consistencyScore,
        ];
    }

    /**
     * @param  Collection<int, SkillAcquisition>  $acquisitions
     * @return array{wasteful_purchases: array<int, string>, sp_waste_total: int}
     */
    public function flagSPWaste(Collection $acquisitions, int $availableSP): array
    {
        $wastefulPurchases = [];
        $wasteTotal = 0;

        foreach ($acquisitions as $acquisition) {
            if (! $acquisition->relationLoaded('skill')) {
                $acquisition->load('skill');
            }

            $skill = $acquisition->skill;
            if ($skill === null) {
                continue;
            }

            $hintLevel = $this->resolveHintLevel($acquisition);
            $priorityLevel = strtolower((string) ($acquisition->priority_level ?? ''));
            $metaTier = strtoupper((string) ($skill->meta_tier ?? ''));

            $isLowValue = in_array($priorityLevel, ['', 'low'], true)
                || in_array($metaTier, ['', 'C', 'B'], true);

            if ($skill->skill_type === 'unique' || $skill->character_exclusive !== null) {
                continue;
            }

            if ($hintLevel <= 1 && $isLowValue) {
                $recommendedCost = $skill->calculateFinalCost(3);
                $paidCost = (int) ($acquisition->final_sp_cost ?? $acquisition->base_sp_cost ?? $skill->base_sp_cost);
                $waste = max(0, $paidCost - $recommendedCost);

                if ($waste > 0) {
                    $wasteTotal += $waste;
                    $wastefulPurchases[] = $skill->name.' was purchased at low hint efficiency';
                }
            }
        }

        if ($availableSP < 100 && $wasteTotal > 0) {
            $wastefulPurchases[] = 'Low remaining SP magnifies earlier inefficient purchases';
        }

        return [
            'wasteful_purchases' => array_values(array_unique($wastefulPurchases)),
            'sp_waste_total' => $wasteTotal,
        ];
    }

    private function resolveHintLevel(SkillAcquisition $acquisition): int
    {
        $hintLevel = $acquisition->hints_used;

        if ($hintLevel === null && isset($acquisition->hint_level)) {
            $hintLevel = (int) $acquisition->hint_level;
        }

        if ($hintLevel !== null) {
            return max(0, min(5, (int) $hintLevel));
        }

        $discount = (float) ($acquisition->total_discount_percentage ?? $acquisition->sp_discount_applied ?? 0.0);

        return match (true) {
            $discount >= 40.0 => 5,
            $discount >= 35.0 => 4,
            $discount >= 30.0 => 3,
            $discount >= 20.0 => 2,
            $discount >= 10.0 => 1,
            default => 0,
        };
    }

    private function resolveRunningStyle(Character $character): string
    {
        $bestAptitude = $character->aptitudes
            ->where('running_style', '!=', null)
            ->sortByDesc(fn (Aptitude $aptitude): int => $this->gradeRank($aptitude->grade))
            ->first();

        return $bestAptitude?->running_style ?? 'lead';
    }

    private function resolveDistance(Character $character): string
    {
        $bestAptitude = $character->aptitudes
            ->where('distance_type', '!=', null)
            ->sortByDesc(fn (Aptitude $aptitude): int => $this->gradeRank($aptitude->grade))
            ->first();

        return $bestAptitude?->distance_type ?? 'medium';
    }

    private function gradeRank(?string $grade): int
    {
        return match ($grade) {
            'SS' => 10,
            'S' => 9,
            'A' => 8,
            'B' => 7,
            'C' => 6,
            'D' => 5,
            'E' => 4,
            'F' => 3,
            'G' => 2,
            default => 0,
        };
    }
}
