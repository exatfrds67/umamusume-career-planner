<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Priority Enum
 *
 * Defines priority levels for recommendations and alerts in the
 * AI-Powered Training Advisory System.
 *
 * Priority levels are ordered from highest to lowest urgency:
 * CRITICAL > HIGH > MEDIUM > LOW
 *
 * @see \App\Services\TrainingAdvisoryService
 * @see \App\Models\AdvisoryRecommendation
 * @see \App\Models\CriticalAlert
 */
enum Priority: string
{
    /**
     * Critical priority - requires immediate attention
     *
     * Used for situations that could significantly impact career run success
     * if not addressed immediately (e.g., stamina crisis, energy critical).
     */
    case CRITICAL = 'critical';

    /**
     * High priority - should be addressed soon
     *
     * Used for important recommendations that should be followed to optimize
     * career run outcomes (e.g., Friendship Training available, gold skill purchase).
     */
    case HIGH = 'high';

    /**
     * Medium priority - helpful but not urgent
     *
     * Used for beneficial recommendations that can improve outcomes but are
     * not time-sensitive (e.g., facility level balancing, bond building).
     */
    case MEDIUM = 'medium';

    /**
     * Low priority - optional optimization
     *
     * Used for minor optimizations and suggestions that have minimal impact
     * on overall career run success.
     */
    case LOW = 'low';

    /**
     * Get a human-readable label for the priority level
     */
    public function label(): string
    {
        return match ($this) {
            self::CRITICAL => 'Critical',
            self::HIGH => 'High',
            self::MEDIUM => 'Medium',
            self::LOW => 'Low',
        };
    }

    /**
     * Get a description of the priority level
     */
    public function description(): string
    {
        return match ($this) {
            self::CRITICAL => 'Requires immediate attention',
            self::HIGH => 'Should be addressed soon',
            self::MEDIUM => 'Helpful but not urgent',
            self::LOW => 'Optional optimization',
        };
    }

    /**
     * Get a color class for UI display
     */
    public function color(): string
    {
        return match ($this) {
            self::CRITICAL => 'red',
            self::HIGH => 'orange',
            self::MEDIUM => 'yellow',
            self::LOW => 'gray',
        };
    }

    /**
     * Get a numeric weight for sorting (higher = more urgent)
     */
    public function weight(): int
    {
        return match ($this) {
            self::CRITICAL => 4,
            self::HIGH => 3,
            self::MEDIUM => 2,
            self::LOW => 1,
        };
    }

    /**
     * Check if this priority is higher than another priority
     */
    public function isHigherThan(self $other): bool
    {
        return $this->weight() > $other->weight();
    }

    /**
     * Check if this priority is lower than another priority
     */
    public function isLowerThan(self $other): bool
    {
        return $this->weight() < $other->weight();
    }

    /**
     * Check if this priority is equal to another priority
     */
    public function isEqualTo(self $other): bool
    {
        return $this === $other;
    }

    /**
     * Check if this priority is at least as high as another priority
     */
    public function isAtLeast(self $other): bool
    {
        return $this->weight() >= $other->weight();
    }

    /**
     * Check if this priority is at most as high as another priority
     */
    public function isAtMost(self $other): bool
    {
        return $this->weight() <= $other->weight();
    }

    /**
     * Check if this priority is critical
     */
    public function isCritical(): bool
    {
        return $this === self::CRITICAL;
    }

    /**
     * Check if this priority is high or critical
     */
    public function isHighOrCritical(): bool
    {
        return $this->isAtLeast(self::HIGH);
    }

    /**
     * Check if this priority is low
     */
    public function isLow(): bool
    {
        return $this === self::LOW;
    }

    /**
     * Compare two priorities and return the higher one
     */
    public static function max(self $a, self $b): self
    {
        return $a->weight() >= $b->weight() ? $a : $b;
    }

    /**
     * Compare two priorities and return the lower one
     */
    public static function min(self $a, self $b): self
    {
        return $a->weight() <= $b->weight() ? $a : $b;
    }

    /**
     * Get all priority levels ordered from highest to lowest
     *
     * @return array<self>
     */
    public static function ordered(): array
    {
        return [
            self::CRITICAL,
            self::HIGH,
            self::MEDIUM,
            self::LOW,
        ];
    }

    /**
     * Get all priority levels
     *
     * @return array<self>
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Get all priority values
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $priority) => $priority->value, self::cases());
    }
}
