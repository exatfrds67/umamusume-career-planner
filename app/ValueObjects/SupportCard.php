<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * Support Card Value Object
 *
 * Represents a single support card in a deck with its bond level and facility assignment.
 * Support cards provide training bonuses and enable Friendship Training at bond ≥80.
 *
 * @see \App\ValueObjects\SupportCardDeck
 */
final readonly class SupportCard
{
    /**
     * Minimum bond level
     */
    public const MIN_BOND = 0;

    /**
     * Maximum bond level
     */
    public const MAX_BOND = 100;

    /**
     * Valid facility types
     */
    public const VALID_FACILITIES = [
        'speed',
        'stamina',
        'power',
        'guts',
        'wisdom',
        'friend',
    ];

    /**
     * Create a new Support Card
     *
     * @param  int  $id  Card identifier
     * @param  int  $bond  Current bond level (0-100)
     * @param  string  $facility  Assigned facility (speed, stamina, power, guts, wisdom, friend)
     * @param  int  $limitBreak  Limit break level (0-4 stars)
     * @param  string|null  $name  Card name (optional)
     *
     * @throws InvalidArgumentException If bond or facility is invalid
     */
    public function __construct(
        public int $id,
        public int $bond,
        public string $facility,
        public int $limitBreak = 0,
        public ?string $name = null,
    ) {
        $this->validate();
    }

    /**
     * Validate card properties
     *
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        if ($this->bond < self::MIN_BOND || $this->bond > self::MAX_BOND) {
            throw new InvalidArgumentException(
                'Bond level must be between '.self::MIN_BOND.' and '.self::MAX_BOND.", got {$this->bond}"
            );
        }

        if (! in_array($this->facility, self::VALID_FACILITIES, true)) {
            throw new InvalidArgumentException(
                "Invalid facility '{$this->facility}'. Must be one of: ".implode(', ', self::VALID_FACILITIES)
            );
        }

        if ($this->limitBreak < 0 || $this->limitBreak > 4) {
            throw new InvalidArgumentException(
                "Limit break must be between 0 and 4, got {$this->limitBreak}"
            );
        }
    }

    /**
     * Check if card is ready for Friendship Training (bond ≥80)
     */
    public function isFriendshipReady(): bool
    {
        return $this->bond >= 80;
    }

    /**
     * Get bond progress as a percentage (0.0-1.0)
     */
    public function getBondProgress(): float
    {
        return $this->bond / self::MAX_BOND;
    }

    /**
     * Get bond needed to reach Friendship Training threshold
     */
    public function getBondNeededForFriendship(): int
    {
        return max(0, 80 - $this->bond);
    }

    /**
     * Check if bond is at maximum
     */
    public function isMaxBond(): bool
    {
        return $this->bond === self::MAX_BOND;
    }

    /**
     * Get the bond gauge color
     *
     * - Red: 0-39
     * - Yellow: 40-79
     * - Orange: 80-99 (Friendship Training ready)
     * - Rainbow: 100 (Max bond)
     */
    public function getBondColor(): string
    {
        return match (true) {
            $this->bond === 100 => 'rainbow',
            $this->bond >= 80 => 'orange',
            $this->bond >= 40 => 'yellow',
            default => 'red',
        };
    }

    /**
     * Get the Friendship Training bonus multiplier based on limit breaks
     *
     * - 0 stars: +10%
     * - 1 star: +15%
     * - 2 stars: +20%
     * - 3 stars: +25%
     * - 4 stars: +30%
     */
    public function getFriendshipBonus(): float
    {
        return match ($this->limitBreak) {
            0 => 0.10,
            1 => 0.15,
            2 => 0.20,
            3 => 0.25,
            4 => 0.30,
            default => 0.10,
        };
    }

    /**
     * Check if this is a Friend-type card
     */
    public function isFriendCard(): bool
    {
        return $this->facility === 'friend';
    }

    /**
     * Check if this is a training facility card
     */
    public function isTrainingCard(): bool
    {
        return in_array($this->facility, ['speed', 'stamina', 'power', 'guts', 'wisdom'], true);
    }

    /**
     * Create card from an array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $id = $data['id'] ?? 0;
        $bond = $data['bond'] ?? 0;
        $facility = $data['facility'] ?? 'speed';
        $limitBreak = $data['limit_break'] ?? 0;
        $name = $data['name'] ?? null;

        return new self(
            id: is_numeric($id) ? (int) $id : 0,
            bond: is_numeric($bond) ? (int) $bond : 0,
            facility: is_string($facility) ? $facility : 'speed',
            limitBreak: is_numeric($limitBreak) ? (int) $limitBreak : 0,
            name: is_string($name) ? $name : null,
        );
    }

    /**
     * Convert card to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'bond' => $this->bond,
            'facility' => $this->facility,
            'limit_break' => $this->limitBreak,
            'name' => $this->name,
        ];
    }
}
