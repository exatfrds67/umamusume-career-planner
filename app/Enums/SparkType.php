<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Spark Type Enum
 *
 * Defines the four spark types that can occur during Inspiration Events
 * in the Inheritance (Legacy) system.
 *
 * @see \App\Services\InheritanceEventService
 */
enum SparkType: string
{
    /**
     * Blue Spark — Stat Bonus
     *
     * Provides a direct flat stat bonus (Speed, Stamina, Power, Guts, or Wit).
     * The most common spark type. Scales with star level.
     */
    case Blue = 'blue';

    /**
     * Pink Spark — Skill Inheritance
     *
     * Inherits one or more skills from the parent's skill set.
     * The rarest spark type. Higher star = more or better skills.
     */
    case Pink = 'pink';

    /**
     * Green Spark — Growth Rate Bonus
     *
     * Increases the character's growth rate multiplier for one or more stats.
     * Highest long-term value due to compounding across remaining turns.
     */
    case Green = 'green';

    /**
     * White Spark — SP Bonus
     *
     * Grants a lump sum of Skill Points used to purchase skills.
     * Lower priority than Green or Blue from high-stat parents.
     */
    case White = 'white';

    /**
     * Get a human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Blue => 'Stat Bonus',
            self::Pink => 'Skill Inheritance',
            self::Green => 'Growth Rate Bonus',
            self::White => 'SP Bonus',
        };
    }

    /**
     * Get a description of this spark type's benefit.
     */
    public function description(): string
    {
        return match ($this) {
            self::Blue => 'Direct flat stat bonus applied at the Inspiration Event',
            self::Pink => 'Inherits skills from the parent character',
            self::Green => 'Increases growth rate multiplier for remaining turns',
            self::White => 'Grants a lump sum of Skill Points (SP)',
        };
    }

    /**
     * Get the CSS color class for UI display.
     */
    public function color(): string
    {
        return match ($this) {
            self::Blue => 'blue',
            self::Pink => 'pink',
            self::Green => 'green',
            self::White => 'gray',
        };
    }
}
