<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Mood Enum
 *
 * Defines the mood states for characters in Umamusume Pretty Derby.
 * Mood affects training effectiveness and stat gain multipliers.
 *
 * @see \App\Services\GameMechanicsEngine
 * @see \App\Services\TrainingAdvisoryService
 */
enum Mood: string
{
    /**
     * Very Bad mood (絶不調)
     *
     * Training effectiveness: 70% (-30%)
     * Requires recreation to improve
     */
    case VERY_BAD = 'very_bad';

    /**
     * Bad mood (不調)
     *
     * Training effectiveness: 85% (-15%)
     * Recommend recreation
     */
    case BAD = 'bad';

    /**
     * Normal mood (普通)
     *
     * Training effectiveness: 100% (baseline)
     * Standard state
     */
    case NORMAL = 'normal';

    /**
     * Good mood (好調)
     *
     * Training effectiveness: 105% (+5%)
     * Positive state
     */
    case GOOD = 'good';

    /**
     * Great mood (絶好調)
     *
     * Training effectiveness: 110% (+10%)
     * Optimal state for training
     */
    case GREAT = 'great';

    /**
     * Get a human-readable label for the mood
     */
    public function label(): string
    {
        return match ($this) {
            self::VERY_BAD => 'Very Bad',
            self::BAD => 'Bad',
            self::NORMAL => 'Normal',
            self::GOOD => 'Good',
            self::GREAT => 'Great',
        };
    }

    /**
     * Get the Japanese name for the mood
     */
    public function japaneseName(): string
    {
        return match ($this) {
            self::VERY_BAD => '絶不調',
            self::BAD => '不調',
            self::NORMAL => '普通',
            self::GOOD => '好調',
            self::GREAT => '絶好調',
        };
    }

    /**
     * Get a description of the mood
     */
    public function description(): string
    {
        return match ($this) {
            self::VERY_BAD => 'Very Bad - Training effectiveness 70% (-30%)',
            self::BAD => 'Bad - Training effectiveness 85% (-15%)',
            self::NORMAL => 'Normal - Training effectiveness 100% (baseline)',
            self::GOOD => 'Good - Training effectiveness 105% (+5%)',
            self::GREAT => 'Great - Training effectiveness 110% (+10%)',
        };
    }

    /**
     * Get the training effectiveness multiplier for this mood
     *
     * 1.0 = 100% effectiveness (Normal)
     * <1.0 = reduced effectiveness
     * >1.0 = increased effectiveness
     */
    public function effectivenessMultiplier(): float
    {
        return match ($this) {
            self::VERY_BAD => 0.70,
            self::BAD => 0.85,
            self::NORMAL => 1.00,
            self::GOOD => 1.05,
            self::GREAT => 1.10,
        };
    }

    /**
     * Get the percentage change from normal
     *
     * Returns a signed integer (-30, -15, 0, +5, +10)
     */
    public function percentageChange(): int
    {
        return match ($this) {
            self::VERY_BAD => -30,
            self::BAD => -15,
            self::NORMAL => 0,
            self::GOOD => 5,
            self::GREAT => 10,
        };
    }

    /**
     * Get a color class for UI display
     */
    public function color(): string
    {
        return match ($this) {
            self::VERY_BAD => 'red',
            self::BAD => 'orange',
            self::NORMAL => 'gray',
            self::GOOD => 'blue',
            self::GREAT => 'green',
        };
    }

    /**
     * Get an emoji representation
     */
    public function emoji(): string
    {
        return match ($this) {
            self::VERY_BAD => '😞',
            self::BAD => '😕',
            self::NORMAL => '😐',
            self::GOOD => '😊',
            self::GREAT => '😄',
        };
    }

    /**
     * Check if this mood is poor (Bad or Very Bad)
     */
    public function isPoor(): bool
    {
        return in_array($this, [self::VERY_BAD, self::BAD], true);
    }

    /**
     * Check if this mood is positive (Good or Great)
     */
    public function isPositive(): bool
    {
        return in_array($this, [self::GOOD, self::GREAT], true);
    }

    /**
     * Check if this mood is normal
     */
    public function isNormal(): bool
    {
        return $this === self::NORMAL;
    }

    /**
     * Check if recreation is recommended
     */
    public function needsRecreation(): bool
    {
        return $this->isPoor();
    }

    /**
     * Get a numeric weight for sorting (higher = better mood)
     */
    public function weight(): int
    {
        return match ($this) {
            self::VERY_BAD => 1,
            self::BAD => 2,
            self::NORMAL => 3,
            self::GOOD => 4,
            self::GREAT => 5,
        };
    }

    /**
     * Compare two moods
     */
    public function isBetterThan(self $other): bool
    {
        return $this->weight() > $other->weight();
    }

    /**
     * Compare two moods
     */
    public function isWorseThan(self $other): bool
    {
        return $this->weight() < $other->weight();
    }

    /**
     * Get all moods in order (worst to best)
     *
     * @return array<self>
     */
    public static function ordered(): array
    {
        return [
            self::VERY_BAD,
            self::BAD,
            self::NORMAL,
            self::GOOD,
            self::GREAT,
        ];
    }

    /**
     * Get all moods
     *
     * @return array<self>
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Get all mood values
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $mood) => $mood->value, self::cases());
    }
}
