<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AffinityGrade;
use App\Enums\SparkType;
use App\Models\Career;
use App\Models\InheritanceEvent;
use App\Models\ParentCharacter;
use Illuminate\Support\Collection;

/**
 * Inheritance Event Service
 *
 * Handles the Inspiration Events / Inheritance system including:
 * - Spark generation (Blue/Pink/Green/White)
 * - Star level probability based on parent stats and affinity
 * - Affinity calculation between parent and child characters
 * - Application of inheritance bonuses to the career run
 */
class InheritanceEventService
{
    /**
     * Event timings mapped to event numbers.
     * Event 1: Start of career (Junior)
     * Event 2: Year 2 late March (Classic)
     * Event 3: Year 3 late March (Senior)
     */
    public const EVENT_TIMINGS = [
        1 => ['label' => 'Career Start', 'phase' => 'junior', 'approximate_turn' => 1],
        2 => ['label' => 'Year 2 Late March', 'phase' => 'classic', 'approximate_turn' => 24],
        3 => ['label' => 'Year 3 Late March', 'phase' => 'senior', 'approximate_turn' => 48],
    ];

    /**
     * Blue spark stat bonus values by star level.
     */
    private const BLUE_SPARK_BONUSES = [
        1 => 9,
        2 => 18,
        3 => 27,
    ];

    /**
     * Green spark growth rate bonus by star level (percentage).
     */
    private const GREEN_SPARK_GROWTH_RATES = [
        1 => 1.0,
        2 => 2.0,
        3 => 3.0,
    ];

    /**
     * White spark SP bonus by star level.
     */
    private const WHITE_SPARK_SP_BONUSES = [
        1 => 20,
        2 => 40,
        3 => 60,
    ];

    /**
     * Base spark type probabilities (normalized weights).
     */
    private const SPARK_TYPE_WEIGHTS = [
        'blue' => 50,
        'green' => 25,
        'white' => 18,
        'pink' => 7,
    ];

    /**
     * Stat threshold for 3-star eligibility on Blue sparks.
     */
    private const HIGH_STAT_THRESHOLD = 1100;

    /**
     * Mid-range stat threshold.
     */
    private const MID_STAT_THRESHOLD = 600;

    /**
     * Generate a spark result for an Inspiration Event.
     *
     * @return array{spark_type: SparkType, star_level: int, target_stat: string|null, stat_bonus: int|null, growth_rate_bonus: float|null, sp_bonus: int|null, inherited_skill_name?: string|null, inherited_skill_data?: array<string, mixed>|null}
     */
    public function generateSpark(ParentCharacter $parent, int $eventNumber): array
    {
        $sparkType = $this->rollSparkType();
        $starLevel = $this->rollStarLevel($sparkType, $parent);

        /** @var array{spark_type: SparkType, star_level: int, target_stat: string|null, stat_bonus: int|null, growth_rate_bonus: float|null, sp_bonus: int|null, inherited_skill_name?: string|null, inherited_skill_data?: array<string, mixed>|null} $spark */
        $spark = match ($sparkType) {
            SparkType::Blue => $this->buildBlueSpark($parent, $starLevel),
            SparkType::Pink => $this->buildPinkSpark($parent, $starLevel),
            SparkType::Green => $this->buildGreenSpark($parent, $starLevel),
            SparkType::White => $this->buildWhiteSpark($starLevel),
        };

        return $spark;
    }

    /**
     * Create an InheritanceEvent record for a career.
     *
     * @param  array<string, mixed>|null  $inheritedSkillData
     */
    public function createEvent(
        Career $career,
        ParentCharacter $parent,
        int $eventNumber,
        SparkType $sparkType,
        int $starLevel,
        ?string $targetStat = null,
        ?int $statBonus = null,
        ?float $growthRateBonus = null,
        ?int $spBonus = null,
        ?string $inheritedSkillName = null,
        ?array $inheritedSkillData = null,
    ): InheritanceEvent {
        return InheritanceEvent::create([
            'career_id' => $career->id,
            'parent_character_id' => $parent->id,
            'event_number' => $eventNumber,
            'spark_type' => $sparkType,
            'star_level' => $starLevel,
            'target_stat' => $targetStat,
            'stat_bonus' => $statBonus,
            'growth_rate_bonus' => $growthRateBonus,
            'sp_bonus' => $spBonus,
            'inherited_skill_name' => $inheritedSkillName,
            'inherited_skill_data' => $inheritedSkillData,
            'is_applied' => false,
        ]);
    }

    /**
     * Generate and persist a full Inspiration Event.
     */
    public function triggerInspirationEvent(Career $career, ParentCharacter $parent, int $eventNumber): InheritanceEvent
    {
        $spark = $this->generateSpark($parent, $eventNumber);

        return $this->createEvent(
            career: $career,
            parent: $parent,
            eventNumber: $eventNumber,
            sparkType: $spark['spark_type'],
            starLevel: $spark['star_level'],
            targetStat: $spark['target_stat'],
            statBonus: $spark['stat_bonus'],
            growthRateBonus: $spark['growth_rate_bonus'],
            spBonus: $spark['sp_bonus'],
            inheritedSkillName: $spark['inherited_skill_name'] ?? null,
            inheritedSkillData: $spark['inherited_skill_data'] ?? null,
        );
    }

    /**
     * Apply an inheritance event's bonus to the career.
     *
     * @return array<string, mixed>
     */
    public function applyEvent(InheritanceEvent $event): array
    {
        if ($event->is_applied) {
            return ['success' => false, 'reason' => 'Event already applied'];
        }

        $result = match ($event->spark_type) {
            SparkType::Blue => $this->applyBlueSpark($event),
            SparkType::Pink => $this->applyPinkSpark($event),
            SparkType::Green => $this->applyGreenSpark($event),
            SparkType::White => $this->applyWhiteSpark($event),
        };

        $event->is_applied = true;
        $event->save();

        return $result;
    }

    /**
     * Get all Inspiration Events for a career.
     *
     * @return Collection<int, InheritanceEvent>
     */
    public function getEventsForCareer(Career $career): Collection
    {
        return $career->inheritanceEvents()
            ->with('parentCharacter')
            ->orderBy('event_number')
            ->get();
    }

    /**
     * Calculate affinity grade between a parent and the career's character.
     */
    public function calculateAffinity(ParentCharacter $parent, Career $career): AffinityGrade
    {
        $score = 0;

        // Distance match: parent and child prefer same distance
        $character = $career->character;
        $characterAttributes = $character?->getAttributes() ?? [];

        $childDistance = $characterAttributes['preferred_distance'] ?? null;
        if ($childDistance && $parent->preferred_distance === $childDistance) {
            $score += 3;
        }

        // Running style match
        $childStyle = $characterAttributes['running_style'] ?? null;
        if ($childStyle && $parent->running_style === $childStyle) {
            $score += 2;
        }

        // Scenario match
        if ($parent->scenario_type === $career->scenario_type) {
            $score += 1;
        }

        return match (true) {
            $score >= 5 => AffinityGrade::High,
            $score >= 2 => AffinityGrade::Standard,
            default => AffinityGrade::Low,
        };
    }

    /**
     * Get the Blue spark stat bonus for a given star level.
     */
    public function getBlueSparkBonus(int $starLevel): int
    {
        return self::BLUE_SPARK_BONUSES[$starLevel] ?? 0;
    }

    /**
     * Get the Green spark growth rate bonus for a given star level.
     */
    public function getGreenSparkGrowthRate(int $starLevel): float
    {
        return self::GREEN_SPARK_GROWTH_RATES[$starLevel] ?? 0.0;
    }

    /**
     * Get the White spark SP bonus for a given star level.
     */
    public function getWhiteSparkSpBonus(int $starLevel): int
    {
        return self::WHITE_SPARK_SP_BONUSES[$starLevel] ?? 0;
    }

    /**
     * Roll a spark type based on weighted probabilities.
     */
    private function rollSparkType(): SparkType
    {
        $total = array_sum(self::SPARK_TYPE_WEIGHTS);
        $roll = random_int(1, $total);
        $cumulative = 0;

        foreach (self::SPARK_TYPE_WEIGHTS as $type => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return SparkType::from($type);
            }
        }

        return SparkType::Blue;
    }

    /**
     * Roll a star level (1-3) based on parent stats and affinity.
     */
    private function rollStarLevel(SparkType $sparkType, ParentCharacter $parent): int
    {
        $affinityGrade = $parent->affinity_grade instanceof AffinityGrade
            ? $parent->affinity_grade
            : AffinityGrade::from((string) $parent->affinity_grade);

        $affinityModifier = $affinityGrade->starLevelModifier();

        $probabilities = $this->getStarProbabilities($sparkType, $parent);

        // Apply affinity modifier to 3-star probability
        $probabilities[3] = (int) round($probabilities[3] * $affinityModifier);

        $total = array_sum($probabilities);
        $roll = random_int(1, max($total, 1));
        $cumulative = 0;

        foreach ($probabilities as $level => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $level;
            }
        }

        return 1;
    }

    /**
     * Get base star level probabilities for a spark type.
     *
     * @return array<int, int>
     */
    private function getStarProbabilities(SparkType $sparkType, ParentCharacter $parent): array
    {
        if ($sparkType === SparkType::Blue) {
            $highestStat = $parent->getHighestStat()['value'];

            if ($highestStat > self::HIGH_STAT_THRESHOLD) {
                return [1 => 15, 2 => 35, 3 => 50];
            }

            if ($highestStat >= self::MID_STAT_THRESHOLD) {
                return [1 => 20, 2 => 70, 3 => 10];
            }

            return [1 => 70, 2 => 20, 3 => 10];
        }

        if ($sparkType === SparkType::Green) {
            return [1 => 50, 2 => 35, 3 => 15];
        }

        if ($sparkType === SparkType::Pink) {
            return [1 => 50, 2 => 35, 3 => 15];
        }

        // White
        return [1 => 35, 2 => 40, 3 => 25];
    }

    /**
     * Build a Blue spark result.
     *
     * @return array<string, mixed>
     */
    private function buildBlueSpark(ParentCharacter $parent, int $starLevel): array
    {
        $highestStat = $parent->getHighestStat();

        return [
            'spark_type' => SparkType::Blue,
            'star_level' => $starLevel,
            'target_stat' => $highestStat['stat'],
            'stat_bonus' => self::BLUE_SPARK_BONUSES[$starLevel],
            'growth_rate_bonus' => null,
            'sp_bonus' => null,
        ];
    }

    /**
     * Build a Pink spark result.
     *
     * @return array<string, mixed>
     */
    private function buildPinkSpark(ParentCharacter $parent, int $starLevel): array
    {
        $skillPool = $parent->skill_pool ?? [];
        $inheritedSkill = ! empty($skillPool) ? $skillPool[array_rand($skillPool)] : null;

        return [
            'spark_type' => SparkType::Pink,
            'star_level' => $starLevel,
            'target_stat' => null,
            'stat_bonus' => null,
            'growth_rate_bonus' => null,
            'sp_bonus' => null,
            'inherited_skill_name' => is_array($inheritedSkill) ? ($inheritedSkill['name'] ?? null) : null,
            'inherited_skill_data' => is_array($inheritedSkill) ? $inheritedSkill : null,
        ];
    }

    /**
     * Build a Green spark result.
     *
     * @return array<string, mixed>
     */
    private function buildGreenSpark(ParentCharacter $parent, int $starLevel): array
    {
        $targetStat = $parent->getHighestStat()['stat'];

        return [
            'spark_type' => SparkType::Green,
            'star_level' => $starLevel,
            'target_stat' => $targetStat,
            'stat_bonus' => null,
            'growth_rate_bonus' => self::GREEN_SPARK_GROWTH_RATES[$starLevel],
            'sp_bonus' => null,
        ];
    }

    /**
     * Build a White spark result.
     *
     * @return array<string, mixed>
     */
    private function buildWhiteSpark(int $starLevel): array
    {
        return [
            'spark_type' => SparkType::White,
            'star_level' => $starLevel,
            'target_stat' => null,
            'stat_bonus' => null,
            'growth_rate_bonus' => null,
            'sp_bonus' => self::WHITE_SPARK_SP_BONUSES[$starLevel],
        ];
    }

    /**
     * Apply a Blue spark — adds flat stat bonus.
     *
     * @return array<string, mixed>
     */
    private function applyBlueSpark(InheritanceEvent $event): array
    {
        return [
            'success' => true,
            'type' => 'stat_bonus',
            'stat' => $event->target_stat,
            'value' => $event->stat_bonus,
            'description' => sprintf(
                '+%d %s from %d★ Blue spark',
                $event->stat_bonus,
                ucfirst($event->target_stat ?? ''),
                $event->star_level,
            ),
        ];
    }

    /**
     * Apply a Pink spark — skill inheritance.
     *
     * @return array<string, mixed>
     */
    private function applyPinkSpark(InheritanceEvent $event): array
    {
        return [
            'success' => true,
            'type' => 'skill_inheritance',
            'skill_name' => $event->inherited_skill_name,
            'skill_data' => $event->inherited_skill_data,
            'description' => sprintf(
                'Inherited skill "%s" from %d★ Pink spark',
                $event->inherited_skill_name ?? 'Unknown',
                $event->star_level,
            ),
        ];
    }

    /**
     * Apply a Green spark — growth rate bonus.
     *
     * @return array<string, mixed>
     */
    private function applyGreenSpark(InheritanceEvent $event): array
    {
        return [
            'success' => true,
            'type' => 'growth_rate_bonus',
            'stat' => $event->target_stat,
            'value' => (float) $event->growth_rate_bonus,
            'description' => sprintf(
                '+%.1f%% %s growth rate from %d★ Green spark',
                (float) $event->growth_rate_bonus,
                ucfirst($event->target_stat ?? ''),
                $event->star_level,
            ),
        ];
    }

    /**
     * Apply a White spark — SP bonus.
     *
     * @return array<string, mixed>
     */
    private function applyWhiteSpark(InheritanceEvent $event): array
    {
        return [
            'success' => true,
            'type' => 'sp_bonus',
            'value' => $event->sp_bonus,
            'description' => sprintf(
                '+%d SP from %d★ White spark',
                $event->sp_bonus,
                $event->star_level,
            ),
        ];
    }
}
