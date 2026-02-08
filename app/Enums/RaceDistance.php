<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Race Distance Enum
 *
 * Defines the race distance categories in Umamusume Pretty Derby.
 * Each distance has different stamina requirements and optimal running styles.
 *
 * @see \App\Services\GameMechanicsEngine
 * @see \App\Services\RaceStrategist
 */
enum RaceDistance: string
{
    /**
     * Sprint distance (1000-1400m)
     *
     * Stamina requirement: 350-400 (Escape style)
     * Optimal styles: Escape, Lead
     */
    case SPRINT = 'sprint';

    /**
     * Mile distance (1400-1800m)
     *
     * Stamina requirement: 450-500 (Escape style)
     * Optimal styles: All styles viable
     */
    case MILE = 'mile';

    /**
     * Medium distance (1800-2400m)
     *
     * Stamina requirement: 600-700 (Escape style)
     * Optimal styles: Lead, Pace
     */
    case MEDIUM = 'medium';

    /**
     * Long distance (2400-3600m)
     *
     * Stamina requirement: 850-1000 (Escape style)
     * Optimal styles: Pace, Chase
     */
    case LONG = 'long';

    /**
     * Get a human-readable label for the race distance
     */
    public function label(): string
    {
        return match ($this) {
            self::SPRINT => 'Sprint',
            self::MILE => 'Mile',
            self::MEDIUM => 'Medium',
            self::LONG => 'Long',
        };
    }

    /**
     * Get a description of the race distance
     */
    public function description(): string
    {
        return match ($this) {
            self::SPRINT => 'Sprint distance (1000-1400m)',
            self::MILE => 'Mile distance (1400-1800m)',
            self::MEDIUM => 'Medium distance (1800-2400m)',
            self::LONG => 'Long distance (2400-3600m)',
        };
    }

    /**
     * Get the meter range for this distance
     *
     * @return array{int, int} [min_meters, max_meters]
     */
    public function meterRange(): array
    {
        return match ($this) {
            self::SPRINT => [1000, 1400],
            self::MILE => [1400, 1800],
            self::MEDIUM => [1800, 2400],
            self::LONG => [2400, 3600],
        };
    }

    /**
     * Get the stamina requirement range for Escape running style
     *
     * @return array{int, int} [min_stamina, max_stamina]
     */
    public function staminaRequirement(): array
    {
        return match ($this) {
            self::SPRINT => [350, 400],
            self::MILE => [450, 500],
            self::MEDIUM => [600, 700],
            self::LONG => [850, 1000],
        };
    }

    /**
     * Get the minimum stamina requirement for Escape style
     */
    public function minStamina(): int
    {
        return $this->staminaRequirement()[0];
    }

    /**
     * Get the maximum stamina requirement for Escape style
     */
    public function maxStamina(): int
    {
        return $this->staminaRequirement()[1];
    }

    /**
     * Get the recommended stamina (midpoint of range)
     */
    public function recommendedStamina(): int
    {
        [$min, $max] = $this->staminaRequirement();

        return (int) (($min + $max) / 2);
    }

    /**
     * Get optimal running styles for this distance
     *
     * @return array<RunningStyle>
     */
    public function optimalStyles(): array
    {
        return match ($this) {
            self::SPRINT => [RunningStyle::ESCAPE, RunningStyle::LEAD],
            self::MILE => [RunningStyle::ESCAPE, RunningStyle::LEAD, RunningStyle::PACE, RunningStyle::CHASE],
            self::MEDIUM => [RunningStyle::LEAD, RunningStyle::PACE],
            self::LONG => [RunningStyle::PACE, RunningStyle::CHASE],
        };
    }

    /**
     * Check if a running style is optimal for this distance
     */
    public function isOptimalStyle(RunningStyle $style): bool
    {
        return in_array($style, $this->optimalStyles(), true);
    }

    /**
     * Get the distance category from meters
     */
    public static function fromMeters(int $meters): self
    {
        return match (true) {
            $meters < 1400 => self::SPRINT,
            $meters < 1800 => self::MILE,
            $meters < 2400 => self::MEDIUM,
            default => self::LONG,
        };
    }

    /**
     * Get all race distances in order (shortest to longest)
     *
     * @return array<self>
     */
    public static function ordered(): array
    {
        return [
            self::SPRINT,
            self::MILE,
            self::MEDIUM,
            self::LONG,
        ];
    }

    /**
     * Get all race distances
     *
     * @return array<self>
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Get all race distance values
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $distance) => $distance->value, self::cases());
    }
}
