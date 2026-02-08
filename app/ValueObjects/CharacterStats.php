<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * Character Stats Value Object
 *
 * Represents the five core stats in Umamusume Pretty Derby.
 * All stats have a range of 0-1200, with values above 1200 subject to
 * diminishing returns (soft cap - values above 1200 count as half).
 *
 * @see \App\Services\GameMechanicsEngine
 * @see \App\ValueObjects\TrainingContext
 */
final readonly class CharacterStats
{
    /**
     * Minimum stat value
     */
    public const MIN_STAT = 0;

    /**
     * Soft cap threshold - values above this count as half effectiveness
     */
    public const SOFT_CAP = 1200;

    /**
     * Maximum practical stat value
     */
    public const MAX_STAT = 1500;

    /**
     * Create new Character Stats
     *
     * @param  int  $speed  Speed stat (affects race speed and acceleration)
     * @param  int  $stamina  Stamina stat (affects endurance and distance capability)
     * @param  int  $power  Power stat (affects acceleration and uphill performance)
     * @param  int  $guts  Guts stat (affects late-race performance and recovery)
     * @param  int  $wisdom  Wisdom stat (affects skill activation rate and training gains)
     *
     * @throws InvalidArgumentException If any stat is out of valid range
     */
    public function __construct(
        public int $speed,
        public int $stamina,
        public int $power,
        public int $guts,
        public int $wisdom,
    ) {
        $this->validate();
    }

    /**
     * Validate all stats are within acceptable range
     *
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        $stats = [
            'speed' => $this->speed,
            'stamina' => $this->stamina,
            'power' => $this->power,
            'guts' => $this->guts,
            'wisdom' => $this->wisdom,
        ];

        foreach ($stats as $name => $value) {
            if ($value < self::MIN_STAT) {
                throw new InvalidArgumentException(
                    "Stat '{$name}' cannot be less than ".self::MIN_STAT.", got {$value}"
                );
            }

            if ($value > self::MAX_STAT) {
                throw new InvalidArgumentException(
                    "Stat '{$name}' cannot exceed ".self::MAX_STAT.", got {$value}"
                );
            }
        }
    }

    /**
     * Calculate effective stat value considering soft cap
     *
     * Values above 1200 count as half effectiveness.
     * Example: 1300 speed = 1200 + (100 / 2) = 1250 effective
     */
    public function getEffectiveSpeed(): int
    {
        return $this->calculateEffectiveStat($this->speed);
    }

    /**
     * Calculate effective stamina considering soft cap
     */
    public function getEffectiveStamina(): int
    {
        return $this->calculateEffectiveStat($this->stamina);
    }

    /**
     * Calculate effective power considering soft cap
     */
    public function getEffectivePower(): int
    {
        return $this->calculateEffectiveStat($this->power);
    }

    /**
     * Calculate effective guts considering soft cap
     */
    public function getEffectiveGuts(): int
    {
        return $this->calculateEffectiveStat($this->guts);
    }

    /**
     * Calculate effective wisdom considering soft cap
     */
    public function getEffectiveWisdom(): int
    {
        return $this->calculateEffectiveStat($this->wisdom);
    }

    /**
     * Calculate effective stat value with soft cap applied
     */
    private function calculateEffectiveStat(int $value): int
    {
        if ($value <= self::SOFT_CAP) {
            return $value;
        }

        $excess = $value - self::SOFT_CAP;

        return self::SOFT_CAP + (int) ($excess / 2);
    }

    /**
     * Get total stat sum (raw values)
     */
    public function getTotal(): int
    {
        return $this->speed + $this->stamina + $this->power + $this->guts + $this->wisdom;
    }

    /**
     * Get total effective stat sum (with soft cap applied)
     */
    public function getEffectiveTotal(): int
    {
        return $this->getEffectiveSpeed()
            + $this->getEffectiveStamina()
            + $this->getEffectivePower()
            + $this->getEffectiveGuts()
            + $this->getEffectiveWisdom();
    }

    /**
     * Check if a specific stat is at or above soft cap
     */
    public function isAtSoftCap(string $stat): bool
    {
        return match ($stat) {
            'speed' => $this->speed >= self::SOFT_CAP,
            'stamina' => $this->stamina >= self::SOFT_CAP,
            'power' => $this->power >= self::SOFT_CAP,
            'guts' => $this->guts >= self::SOFT_CAP,
            'wisdom' => $this->wisdom >= self::SOFT_CAP,
            default => false,
        };
    }

    /**
     * Check if any stat is at or above soft cap
     */
    public function hasAnySoftCapped(): bool
    {
        return $this->speed >= self::SOFT_CAP
            || $this->stamina >= self::SOFT_CAP
            || $this->power >= self::SOFT_CAP
            || $this->guts >= self::SOFT_CAP
            || $this->wisdom >= self::SOFT_CAP;
    }

    /**
     * Get all stats that are at or above soft cap
     *
     * @return array<string>
     */
    public function getSoftCappedStats(): array
    {
        $capped = [];

        if ($this->speed >= self::SOFT_CAP) {
            $capped[] = 'speed';
        }
        if ($this->stamina >= self::SOFT_CAP) {
            $capped[] = 'stamina';
        }
        if ($this->power >= self::SOFT_CAP) {
            $capped[] = 'power';
        }
        if ($this->guts >= self::SOFT_CAP) {
            $capped[] = 'guts';
        }
        if ($this->wisdom >= self::SOFT_CAP) {
            $capped[] = 'wisdom';
        }

        return $capped;
    }

    /**
     * Get a specific stat value by name
     *
     * @throws InvalidArgumentException If stat name is invalid
     */
    public function getStat(string $name): int
    {
        return match ($name) {
            'speed' => $this->speed,
            'stamina' => $this->stamina,
            'power' => $this->power,
            'guts' => $this->guts,
            'wisdom' => $this->wisdom,
            default => throw new InvalidArgumentException("Invalid stat name: {$name}"),
        };
    }

    /**
     * Get a specific effective stat value by name
     *
     * @throws InvalidArgumentException If stat name is invalid
     */
    public function getEffectiveStat(string $name): int
    {
        return match ($name) {
            'speed' => $this->getEffectiveSpeed(),
            'stamina' => $this->getEffectiveStamina(),
            'power' => $this->getEffectivePower(),
            'guts' => $this->getEffectiveGuts(),
            'wisdom' => $this->getEffectiveWisdom(),
            default => throw new InvalidArgumentException("Invalid stat name: {$name}"),
        };
    }

    /**
     * Create stats with a specific stat increased
     *
     * @throws InvalidArgumentException If stat name is invalid or new value is out of range
     */
    public function withIncreasedStat(string $name, int $amount): self
    {
        return match ($name) {
            'speed' => new self($this->speed + $amount, $this->stamina, $this->power, $this->guts, $this->wisdom),
            'stamina' => new self($this->speed, $this->stamina + $amount, $this->power, $this->guts, $this->wisdom),
            'power' => new self($this->speed, $this->stamina, $this->power + $amount, $this->guts, $this->wisdom),
            'guts' => new self($this->speed, $this->stamina, $this->power, $this->guts + $amount, $this->wisdom),
            'wisdom' => new self($this->speed, $this->stamina, $this->power, $this->guts, $this->wisdom + $amount),
            default => throw new InvalidArgumentException("Invalid stat name: {$name}"),
        };
    }

    /**
     * Create stats from an array
     *
     * @param  array<string, int>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            speed: $data['speed'] ?? 0,
            stamina: $data['stamina'] ?? 0,
            power: $data['power'] ?? 0,
            guts: $data['guts'] ?? 0,
            wisdom: $data['wisdom'] ?? 0,
        );
    }

    /**
     * Convert stats to an array
     *
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'speed' => $this->speed,
            'stamina' => $this->stamina,
            'power' => $this->power,
            'guts' => $this->guts,
            'wisdom' => $this->wisdom,
        ];
    }

    /**
     * Convert stats to an array with effective values
     *
     * @return array<string, int>
     */
    public function toEffectiveArray(): array
    {
        return [
            'speed' => $this->getEffectiveSpeed(),
            'stamina' => $this->getEffectiveStamina(),
            'power' => $this->getEffectivePower(),
            'guts' => $this->getEffectiveGuts(),
            'wisdom' => $this->getEffectiveWisdom(),
        ];
    }

    /**
     * Create zero stats (all stats at 0)
     */
    public static function zero(): self
    {
        return new self(0, 0, 0, 0, 0);
    }

    /**
     * Create stats at soft cap (all stats at 1200)
     */
    public static function softCap(): self
    {
        return new self(
            self::SOFT_CAP,
            self::SOFT_CAP,
            self::SOFT_CAP,
            self::SOFT_CAP,
            self::SOFT_CAP
        );
    }

    /**
     * Compare stats with another CharacterStats instance
     *
     * @return array<string, int> Difference for each stat (positive = this is higher)
     */
    public function compareTo(self $other): array
    {
        return [
            'speed' => $this->speed - $other->speed,
            'stamina' => $this->stamina - $other->stamina,
            'power' => $this->power - $other->power,
            'guts' => $this->guts - $other->guts,
            'wisdom' => $this->wisdom - $other->wisdom,
        ];
    }

    /**
     * Check if all stats meet or exceed target stats
     */
    public function meetsTargets(self $targets): bool
    {
        return $this->speed >= $targets->speed
            && $this->stamina >= $targets->stamina
            && $this->power >= $targets->power
            && $this->guts >= $targets->guts
            && $this->wisdom >= $targets->wisdom;
    }

    /**
     * Get stats that don't meet targets
     *
     * @return array<string, array{current: int, target: int, deficit: int}>
     */
    public function getDeficits(self $targets): array
    {
        $deficits = [];

        if ($this->speed < $targets->speed) {
            $deficits['speed'] = [
                'current' => $this->speed,
                'target' => $targets->speed,
                'deficit' => $targets->speed - $this->speed,
            ];
        }

        if ($this->stamina < $targets->stamina) {
            $deficits['stamina'] = [
                'current' => $this->stamina,
                'target' => $targets->stamina,
                'deficit' => $targets->stamina - $this->stamina,
            ];
        }

        if ($this->power < $targets->power) {
            $deficits['power'] = [
                'current' => $this->power,
                'target' => $targets->power,
                'deficit' => $targets->power - $this->power,
            ];
        }

        if ($this->guts < $targets->guts) {
            $deficits['guts'] = [
                'current' => $this->guts,
                'target' => $targets->guts,
                'deficit' => $targets->guts - $this->guts,
            ];
        }

        if ($this->wisdom < $targets->wisdom) {
            $deficits['wisdom'] = [
                'current' => $this->wisdom,
                'target' => $targets->wisdom,
                'deficit' => $targets->wisdom - $this->wisdom,
            ];
        }

        return $deficits;
    }
}
