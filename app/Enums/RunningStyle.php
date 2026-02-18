<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Running Style Enum
 *
 * Defines the running styles (race strategies) in Umamusume Pretty Derby.
 * Each style has different positioning, stamina consumption, and optimal distances.
 *
 * @see \App\Services\GameMechanicsEngine
 * @see \App\Services\RaceStrategist
 */
enum RunningStyle: string
{
    /**
     * Escape / Front Runner (逃げ)
     *
     * Runs at the front from start to finish.
     * Highest stamina consumption, best for Sprint/Mile distances.
     */
    case ESCAPE = 'escape';

    /**
     * Lead / Pace Chaser (先行)
     *
     * Runs near the front, slightly behind the leader.
     * High stamina consumption, versatile across all distances.
     */
    case LEAD = 'lead';

    /**
     * Pace / Late Surger (差し)
     *
     * Runs in the middle of the pack, surges in the final stretch.
     * Moderate stamina consumption, good for Medium/Long distances.
     */
    case PACE = 'pace';

    /**
     * Chase / End Closer (追込)
     *
     * Runs at the back, closes in the final stretch.
     * Lowest stamina consumption, best for Long distances.
     */
    case CHASE = 'chase';

    /**
     * Get a human-readable label for the running style
     */
    public function label(): string
    {
        return match ($this) {
            self::ESCAPE => 'Escape',
            self::LEAD => 'Lead',
            self::PACE => 'Pace',
            self::CHASE => 'Chase',
        };
    }

    /**
     * Get the Japanese name for the running style
     */
    public function japaneseName(): string
    {
        return match ($this) {
            self::ESCAPE => '逃げ',
            self::LEAD => '先行',
            self::PACE => '差し',
            self::CHASE => '追込',
        };
    }

    /**
     * Get a description of the running style
     */
    public function description(): string
    {
        return match ($this) {
            self::ESCAPE => 'Front Runner - Runs at the front from start to finish',
            self::LEAD => 'Pace Chaser - Runs near the front, slightly behind the leader',
            self::PACE => 'Late Surger - Runs in the middle, surges in the final stretch',
            self::CHASE => 'End Closer - Runs at the back, closes in the final stretch',
        };
    }

    /**
     * Get the stamina consumption multiplier for this style
     *
     * Higher multiplier = more stamina consumed
     */
    public function staminaMultiplier(): float
    {
        return match ($this) {
            self::ESCAPE => 1.0,  // Baseline (highest consumption)
            self::LEAD => 0.95,   // 5% less than Escape
            self::PACE => 0.85,   // 15% less than Escape
            self::CHASE => 0.75,  // 25% less than Escape
        };
    }

    /**
     * Get optimal race distances for this style
     *
     * @return array<RaceDistance>
     */
    public function optimalDistances(): array
    {
        return match ($this) {
            self::ESCAPE => [RaceDistance::SPRINT, RaceDistance::MILE],
            self::LEAD => [RaceDistance::SPRINT, RaceDistance::MILE, RaceDistance::MEDIUM],
            self::PACE => [RaceDistance::MILE, RaceDistance::MEDIUM, RaceDistance::LONG],
            self::CHASE => [RaceDistance::MEDIUM, RaceDistance::LONG],
        };
    }

    /**
     * Check if this style is optimal for a given distance
     */
    public function isOptimalForDistance(RaceDistance $distance): bool
    {
        return in_array($distance, $this->optimalDistances(), true);
    }

    /**
     * Get the positioning description for this style
     */
    public function positioning(): string
    {
        return match ($this) {
            self::ESCAPE => 'Front (1st position)',
            self::LEAD => 'Near Front (2nd-3rd position)',
            self::PACE => 'Middle Pack (4th-8th position)',
            self::CHASE => 'Back (9th+ position)',
        };
    }

    /**
     * Get the strategy description for this style
     */
    public function strategy(): string
    {
        return match ($this) {
            self::ESCAPE => 'Lead from start, maintain pace to finish',
            self::LEAD => 'Stay near leader, surge when leader tires',
            self::PACE => 'Conserve energy, surge in final 400m',
            self::CHASE => 'Stay back, explosive finish in final 200m',
        };
    }

    /**
     * Get the stat priority for this style
     *
     * @return array<string, int> [stat_name => priority_weight]
     */
    public function statPriority(): array
    {
        return match ($this) {
            self::ESCAPE => [
                'speed' => 10,
                'stamina' => 10,
                'power' => 7,
                'guts' => 6,
                'wisdom' => 7,
            ],
            self::LEAD => [
                'speed' => 9,
                'stamina' => 9,
                'power' => 8,
                'guts' => 7,
                'wisdom' => 7,
            ],
            self::PACE => [
                'speed' => 8,
                'stamina' => 7,
                'power' => 9,
                'guts' => 8,
                'wisdom' => 8,
            ],
            self::CHASE => [
                'speed' => 7,
                'stamina' => 6,
                'power' => 10,
                'guts' => 9,
                'wisdom' => 8,
            ],
        };
    }

    /**
     * Get all running styles in order (front to back)
     *
     * @return array<self>
     */
    public static function ordered(): array
    {
        return [
            self::ESCAPE,
            self::LEAD,
            self::PACE,
            self::CHASE,
        ];
    }

    /**
     * Get all running styles
     *
     * @return array<self>
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Get all running style values
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $style) => $style->value, self::cases());
    }
}
