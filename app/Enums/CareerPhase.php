<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Career Phase Enum
 *
 * Defines the phases of a career run in Umamusume Pretty Derby.
 * Each phase has different goals, training priorities, and event schedules.
 *
 * @see \App\Services\TrainingAdvisoryService
 */
enum CareerPhase: string
{
    /**
     * Junior Year (Turns 1-24)
     *
     * Focus: Bond building, facility level progression, basic stat foundation
     * Key Events: Summer Training Camp (Early July)
     */
    case JUNIOR = 'junior_year';

    /**
     * Classic Year (Turns 25-48)
     *
     * Focus: Core stat optimization, skill acquisition, race participation
     * Key Events: Classic races, continued training
     */
    case CLASSIC = 'classic_year';

    /**
     * Senior Year (Turns 49-72)
     *
     * Focus: Final stat optimization, skill completion, URA Finals preparation
     * Key Events: Senior races, URA Finals qualification
     */
    case SENIOR = 'senior_year';

    /**
     * URA Finals (Turns 70-72)
     *
     * Focus: Final preparation, three championship races
     * Key Events: URA Finals races (3 races)
     */
    case URA_FINALS = 'ura_finals';

    /**
     * Get a human-readable label for the career phase
     */
    public function label(): string
    {
        return match ($this) {
            self::JUNIOR => 'Junior Year',
            self::CLASSIC => 'Classic Year',
            self::SENIOR => 'Senior Year',
            self::URA_FINALS => 'URA Finals',
        };
    }

    /**
     * Get a description of the career phase
     */
    public function description(): string
    {
        return match ($this) {
            self::JUNIOR => 'Bond building and facility progression (Turns 1-24)',
            self::CLASSIC => 'Core stat optimization and skill acquisition (Turns 25-48)',
            self::SENIOR => 'Final optimization and URA Finals preparation (Turns 49-72)',
            self::URA_FINALS => 'Championship races (Turns 70-72)',
        };
    }

    /**
     * Get the turn range for this phase
     *
     * @return array{int, int} [start_turn, end_turn]
     */
    public function turnRange(): array
    {
        return match ($this) {
            self::JUNIOR => [1, 24],
            self::CLASSIC => [25, 48],
            self::SENIOR => [49, 72],
            self::URA_FINALS => [70, 72],
        };
    }

    /**
     * Get the primary focus areas for this phase
     *
     * @return array<string>
     */
    public function focusAreas(): array
    {
        return match ($this) {
            self::JUNIOR => ['bond_building', 'facility_levels', 'basic_stats'],
            self::CLASSIC => ['stat_optimization', 'skill_acquisition', 'race_participation'],
            self::SENIOR => ['final_optimization', 'skill_completion', 'ura_preparation'],
            self::URA_FINALS => ['final_preparation', 'championship_races'],
        };
    }

    /**
     * Get the recommended stat targets for this phase
     *
     * @return array<string, int>
     */
    public function statTargets(): array
    {
        return match ($this) {
            self::JUNIOR => [
                'speed' => 400,
                'stamina' => 300,
                'power' => 350,
                'guts' => 250,
                'wisdom' => 300,
            ],
            self::CLASSIC => [
                'speed' => 800,
                'stamina' => 600,
                'power' => 600,
                'guts' => 450,
                'wisdom' => 600,
            ],
            self::SENIOR => [
                'speed' => 1200,
                'stamina' => 850,
                'power' => 800,
                'guts' => 600,
                'wisdom' => 800,
            ],
            self::URA_FINALS => [
                'speed' => 1200,
                'stamina' => 1000,
                'power' => 800,
                'guts' => 600,
                'wisdom' => 800,
            ],
        };
    }

    /**
     * Check if a turn number falls within this phase
     */
    public function containsTurn(int $turnNumber): bool
    {
        [$start, $end] = $this->turnRange();

        return $turnNumber >= $start && $turnNumber <= $end;
    }

    /**
     * Get the phase for a given turn number
     */
    public static function fromTurn(int $turnNumber): self
    {
        return match (true) {
            $turnNumber >= 70 => self::URA_FINALS,
            $turnNumber >= 49 => self::SENIOR,
            $turnNumber >= 25 => self::CLASSIC,
            default => self::JUNIOR,
        };
    }

    /**
     * Get the next phase
     */
    public function next(): ?self
    {
        return match ($this) {
            self::JUNIOR => self::CLASSIC,
            self::CLASSIC => self::SENIOR,
            self::SENIOR => self::URA_FINALS,
            self::URA_FINALS => null,
        };
    }

    /**
     * Get the previous phase
     */
    public function previous(): ?self
    {
        return match ($this) {
            self::JUNIOR => null,
            self::CLASSIC => self::JUNIOR,
            self::SENIOR => self::CLASSIC,
            self::URA_FINALS => self::SENIOR,
        };
    }

    /**
     * Check if this is the first phase
     */
    public function isFirst(): bool
    {
        return $this === self::JUNIOR;
    }

    /**
     * Check if this is the last phase
     */
    public function isLast(): bool
    {
        return $this === self::URA_FINALS;
    }

    /**
     * Get all career phases in order
     *
     * @return array<self>
     */
    public static function ordered(): array
    {
        return [
            self::JUNIOR,
            self::CLASSIC,
            self::SENIOR,
            self::URA_FINALS,
        ];
    }

    /**
     * Get all career phases
     *
     * @return array<self>
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Get all career phase values
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $phase) => $phase->value, self::cases());
    }
}
