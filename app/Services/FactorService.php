<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Aptitude;
use App\Models\Character;
use App\Models\Factor;
use Illuminate\Support\Collection;

/**
 * Factor Service
 *
 * Handles factor inheritance calculations and management for Uma Musume characters.
 * Implements the game's factor system including:
 * - Blue Factors: Stat bonuses
 * - Red Factors: Aptitude upgrades
 * - Green Factors: Unique skills
 * - White Factors: Normal skills
 */
class FactorService
{
    /**
     * Stat bonus values by star level for Blue Factors.
     */
    private const STAT_BONUS_VALUES = [
        '1_star' => 5,
        '2_star' => 12,
        '3_star' => 21,
    ];

    /**
     * Aptitude grade progression.
     */
    private const APTITUDE_GRADES = ['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S', 'SS'];

    /**
     * Calculate total stat bonuses from Blue Factors.
     *
     * @return array<string, int>
     */
    public function calculateStatBonuses(Character $character): array
    {
        $bonuses = [
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wit' => 0,
        ];

        $blueFactors = Factor::where('character_id', $character->id)
            ->where('factor_type', 'blue_stats')
            ->where('is_active', true)
            ->get();

        foreach ($blueFactors as $factor) {
            if ($factor->stat_type && isset($bonuses[$factor->stat_type])) {
                $bonuses[$factor->stat_type] += $factor->stat_bonus ?? 0;
            }
        }

        return $bonuses;
    }

    /**
     * Apply stat bonuses to character's current stats.
     *
     * @return array<string, int>
     */
    public function applyStatBonuses(Character $character): array
    {
        $currentStats = $character->current_stats;
        $bonuses = $this->calculateStatBonuses($character);

        return [
            'speed' => ($currentStats['speed'] ?? 0) + $bonuses['speed'],
            'stamina' => ($currentStats['stamina'] ?? 0) + $bonuses['stamina'],
            'power' => ($currentStats['power'] ?? 0) + $bonuses['power'],
            'guts' => ($currentStats['guts'] ?? 0) + $bonuses['guts'],
            'wit' => ($currentStats['wit'] ?? 0) + $bonuses['wit'],
        ];
    }

    /**
     * Calculate aptitude improvements from Red Factors.
     *
     * @return array<string, int>
     */
    public function calculateAptitudeImprovements(Character $character): array
    {
        $improvements = [];

        $redFactors = Factor::where('character_id', $character->id)
            ->where('factor_type', 'red_aptitudes')
            ->where('is_active', true)
            ->get();

        foreach ($redFactors as $factor) {
            if ($factor->aptitude_type) {
                $improvements[$factor->aptitude_type] = ($improvements[$factor->aptitude_type] ?? 0) + ($factor->grade_improvement ?? 0);
            }
        }

        return $improvements;
    }

    /**
     * Apply aptitude improvements to character's aptitudes.
     *
     * @return Collection<int, Aptitude>
     */
    public function applyAptitudeImprovements(Character $character): Collection
    {
        $improvements = $this->calculateAptitudeImprovements($character);
        $aptitudes = $character->aptitudes;

        foreach ($aptitudes as $aptitude) {
            $aptitudeKey = $this->getAptitudeKey($aptitude);

            if (isset($improvements[$aptitudeKey])) {
                $currentGrade = $aptitude->grade;
                $improvedGrade = $this->improveGrade($currentGrade, $improvements[$aptitudeKey]);
                $aptitude->grade = $improvedGrade;
            }
        }

        return $aptitudes;
    }

    /**
     * Get unique skills from Green Factors.
     *
     * @return Collection<int, Factor>
     */
    public function getUniqueSkills(Character $character): Collection
    {
        return Factor::where('character_id', $character->id)
            ->where('factor_type', 'green_unique_skills')
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get normal skills from White Factors.
     *
     * @return Collection<int, Factor>
     */
    public function getNormalSkills(Character $character): Collection
    {
        return Factor::where('character_id', $character->id)
            ->where('factor_type', 'white_normal_skills')
            ->where('is_active', true)
            ->get();
    }

    /**
     * Create a Blue Factor (stat bonus).
     */
    public function createBlueFactor(
        Character $character,
        string $statType,
        string $starLevel,
        string $sourceParent,
        ?string $sourceCharacterName = null,
        ?string $factorName = null
    ): Factor {
        $statBonus = self::STAT_BONUS_VALUES[$starLevel] ?? 0;

        return Factor::create([
            'character_id' => $character->id,
            'factor_type' => 'blue_stats',
            'factor_name' => $factorName ?? ucfirst($statType).' Factor',
            'star_level' => $starLevel,
            'stat_type' => $statType,
            'stat_bonus' => $statBonus,
            'source_parent' => $sourceParent,
            'source_character_name' => $sourceCharacterName,
            'inheritance_rate' => 100.00,
            'affinity_compatible' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Create a Red Factor (aptitude improvement).
     */
    public function createRedFactor(
        Character $character,
        string $aptitudeType,
        int $gradeImprovement,
        string $starLevel,
        string $sourceParent,
        ?string $sourceCharacterName = null,
        ?string $factorName = null
    ): Factor {
        return Factor::create([
            'character_id' => $character->id,
            'factor_type' => 'red_aptitudes',
            'factor_name' => $factorName ?? ucfirst(str_replace('_', ' ', $aptitudeType)).' Aptitude Factor',
            'star_level' => $starLevel,
            'aptitude_type' => $aptitudeType,
            'grade_improvement' => $gradeImprovement,
            'source_parent' => $sourceParent,
            'source_character_name' => $sourceCharacterName,
            'inheritance_rate' => 100.00,
            'affinity_compatible' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Create a Green Factor (unique skill).
     *
     * @param  array<string, mixed>  $skillEffects
     */
    public function createGreenFactor(
        Character $character,
        string $skillName,
        array $skillEffects,
        string $sourceParent,
        ?string $sourceCharacterName = null
    ): Factor {
        return Factor::create([
            'character_id' => $character->id,
            'factor_type' => 'green_unique_skills',
            'factor_name' => $skillName,
            'star_level' => '3_star', // Unique skills always from 3★ characters
            'unique_skill_name' => $skillName,
            'skill_effects' => $skillEffects,
            'source_parent' => $sourceParent,
            'source_character_name' => $sourceCharacterName,
            'inheritance_rate' => 100.00,
            'affinity_compatible' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Create a White Factor (normal skill).
     *
     * @param  array<string, mixed>  $raceBonuses
     */
    public function createWhiteFactor(
        Character $character,
        string $skillName,
        array $raceBonuses,
        string $starLevel,
        string $sourceParent,
        ?string $sourceCharacterName = null
    ): Factor {
        return Factor::create([
            'character_id' => $character->id,
            'factor_type' => 'white_normal_skills',
            'factor_name' => $skillName,
            'star_level' => $starLevel,
            'normal_skill_name' => $skillName,
            'race_bonuses' => $raceBonuses,
            'source_parent' => $sourceParent,
            'source_character_name' => $sourceCharacterName,
            'inheritance_rate' => 100.00,
            'affinity_compatible' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Get aptitude key from Aptitude model.
     */
    private function getAptitudeKey(Aptitude $aptitude): string
    {
        // Map database fields to factor aptitude types
        if ($aptitude->running_style) {
            return match ($aptitude->running_style) {
                'runner' => 'front_runner',
                'leader' => 'pace_chaser',
                'betweener' => 'late_surger',
                'chaser' => 'end_closer',
                default => $aptitude->running_style,
            };
        }

        // Distance/surface combinations
        $distance = $aptitude->distance_type;
        $surface = $aptitude->surface_type;

        if ($surface === 'turf' || $surface === 'dirt') {
            return $surface;
        }

        return $distance;
    }

    /**
     * Improve aptitude grade by specified number of levels.
     */
    private function improveGrade(string $currentGrade, int $improvement): string
    {
        $currentIndex = array_search($currentGrade, self::APTITUDE_GRADES);

        if ($currentIndex === false) {
            return $currentGrade;
        }

        $newIndex = min($currentIndex + $improvement, count(self::APTITUDE_GRADES) - 1);

        return self::APTITUDE_GRADES[$newIndex];
    }

    /**
     * Get all factors for a character grouped by type.
     *
     * @return array<string, Collection<int, Factor>>
     */
    public function getFactorsByType(Character $character): array
    {
        $factors = Factor::where('character_id', $character->id)
            ->where('is_active', true)
            ->get();

        return [
            'blue_stats' => $factors->where('factor_type', 'blue_stats'),
            'red_aptitudes' => $factors->where('factor_type', 'red_aptitudes'),
            'green_unique_skills' => $factors->where('factor_type', 'green_unique_skills'),
            'white_normal_skills' => $factors->where('factor_type', 'white_normal_skills'),
        ];
    }

    /**
     * Calculate total factor count by star level.
     *
     * @return array<string, int>
     */
    public function getFactorCountByStarLevel(Character $character): array
    {
        $factors = Factor::where('character_id', $character->id)
            ->where('is_active', true)
            ->get();

        return [
            '1_star' => $factors->where('star_level', '1_star')->count(),
            '2_star' => $factors->where('star_level', '2_star')->count(),
            '3_star' => $factors->where('star_level', '3_star')->count(),
        ];
    }
}
