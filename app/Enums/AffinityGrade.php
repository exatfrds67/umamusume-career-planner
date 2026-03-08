<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Affinity Grade Enum
 *
 * Represents the affinity rating between a parent and child character
 * in the Inheritance system. Higher affinity improves spark quality.
 *
 * @see \App\Services\InheritanceEventService
 */
enum AffinityGrade: string
{
    /**
     * High Affinity (◎ double circle)
     *
     * Increased spark star level probability, bonus skill options.
     */
    case High = 'high';

    /**
     * Standard Affinity (○ single circle)
     *
     * Normal spark probabilities.
     */
    case Standard = 'standard';

    /**
     * Low Affinity (△ triangle)
     *
     * Reduced spark probabilities, fewer skill options.
     */
    case Low = 'low';

    /**
     * Get a human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::High => 'High Affinity',
            self::Standard => 'Standard Affinity',
            self::Low => 'Low Affinity',
        };
    }

    /**
     * Get the Japanese symbol for this grade.
     */
    public function symbol(): string
    {
        return match ($this) {
            self::High => '◎',
            self::Standard => '○',
            self::Low => '△',
        };
    }

    /**
     * Get the star level probability modifier.
     * Values > 1.0 increase chance of higher star levels.
     */
    public function starLevelModifier(): float
    {
        return match ($this) {
            self::High => 1.3,
            self::Standard => 1.0,
            self::Low => 0.7,
        };
    }
}
