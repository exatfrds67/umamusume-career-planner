<?php

declare(strict_types=1);

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * Support Card Deck Value Object
 *
 * Represents a deck of 6 support cards with their bond levels and facility assignments.
 * Support cards provide training bonuses, skill hints, and enable Friendship Training
 * when bond level reaches 80 (orange gauge).
 *
 * @see \App\ValueObjects\TrainingContext
 * @see \App\Services\TrainingAdvisoryService
 */
final readonly class SupportCardDeck
{
    /**
     * Maximum number of cards in a deck
     */
    public const MAX_CARDS = 6;

    /**
     * Bond level threshold for Friendship Training (orange gauge)
     */
    public const FRIENDSHIP_THRESHOLD = 80;

    /**
     * Maximum bond level
     */
    public const MAX_BOND = 100;

    /**
     * Minimum number of cards at bond ≥80 required for Friendship Training activation
     */
    public const MIN_FRIENDSHIP_CARDS = 3;

    /**
     * Create a new Support Card Deck
     *
     * @param  array<SupportCard>  $cards  Array of support cards (max 6)
     *
     * @throws InvalidArgumentException If deck has more than 6 cards
     */
    public function __construct(
        public array $cards,
    ) {
        $this->validate();
    }

    /**
     * Validate deck configuration
     *
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        if (count($this->cards) > self::MAX_CARDS) {
            throw new InvalidArgumentException(
                'Support deck cannot have more than '.self::MAX_CARDS.' cards, got '.count($this->cards)
            );
        }

        foreach ($this->cards as $card) {
            if (! $card instanceof SupportCard) {
                throw new InvalidArgumentException(
                    'All deck cards must be instances of SupportCard'
                );
            }
        }
    }

    /**
     * Check if Friendship Training is available
     *
     * Returns true only when 3 or more cards simultaneously have bond ≥80
     */
    public function hasFriendshipTrainingReady(): bool
    {
        return $this->getFriendshipReadyCount() >= self::MIN_FRIENDSHIP_CARDS;
    }

    /**
     * Get the number of cards ready for Friendship Training
     */
    public function getFriendshipReadyCount(): int
    {
        $count = 0;

        foreach ($this->cards as $card) {
            if ($card->bond >= self::FRIENDSHIP_THRESHOLD) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get cards ready for Friendship Training
     *
     * @return array<SupportCard>
     */
    public function getFriendshipReadyCards(): array
    {
        return array_filter(
            $this->cards,
            fn (SupportCard $card) => $card->bond >= self::FRIENDSHIP_THRESHOLD
        );
    }

    /**
     * Get cards not ready for Friendship Training
     *
     * @return array<SupportCard>
     */
    public function getCardsNotReady(): array
    {
        return array_filter(
            $this->cards,
            fn (SupportCard $card) => $card->bond < self::FRIENDSHIP_THRESHOLD
        );
    }

    /**
     * Get cards assigned to a specific facility
     *
     * @return array<SupportCard>
     */
    public function getCardsAtFacility(string $facility): array
    {
        return array_filter(
            $this->cards,
            fn (SupportCard $card) => $card->facility === $facility
        );
    }

    /**
     * Get the number of cards at a specific facility
     */
    public function getCardCountAtFacility(string $facility): int
    {
        return count($this->getCardsAtFacility($facility));
    }

    /**
     * Get cards at a facility that are ready for Friendship Training
     *
     * @return array<SupportCard>
     */
    public function getFriendshipReadyCardsAtFacility(string $facility): array
    {
        return array_filter(
            $this->getCardsAtFacility($facility),
            fn (SupportCard $card) => $card->bond >= self::FRIENDSHIP_THRESHOLD
        );
    }

    /**
     * Check if Friendship Training is available at a specific facility
     */
    public function hasFriendshipTrainingAtFacility(string $facility): bool
    {
        return count($this->getFriendshipReadyCardsAtFacility($facility)) > 0;
    }

    /**
     * Get average bond level across all cards
     */
    public function getAverageBond(): float
    {
        if (empty($this->cards)) {
            return 0.0;
        }

        $totalBond = array_sum(array_map(fn (SupportCard $card) => $card->bond, $this->cards));

        return $totalBond / count($this->cards);
    }

    /**
     * Get the card with the lowest bond
     */
    public function getLowestBondCard(): ?SupportCard
    {
        if (empty($this->cards)) {
            return null;
        }

        $lowest = $this->cards[0];

        foreach ($this->cards as $card) {
            if ($card->bond < $lowest->bond) {
                $lowest = $card;
            }
        }

        return $lowest;
    }

    /**
     * Get the card with the highest bond
     */
    public function getHighestBondCard(): ?SupportCard
    {
        if (empty($this->cards)) {
            return null;
        }

        $highest = $this->cards[0];

        foreach ($this->cards as $card) {
            if ($card->bond > $highest->bond) {
                $highest = $card;
            }
        }

        return $highest;
    }

    /**
     * Get facility distribution
     *
     * @return array<string, int> Facility name => card count
     */
    public function getFacilityDistribution(): array
    {
        $distribution = [
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wisdom' => 0,
            'friend' => 0,
        ];

        foreach ($this->cards as $card) {
            if (isset($distribution[$card->facility])) {
                $distribution[$card->facility]++;
            }
        }

        return $distribution;
    }

    /**
     * Get the facility with the most cards
     *
     * @return array{facility: string, count: int}
     */
    public function getMostPopulatedFacility(): array
    {
        $distribution = $this->getFacilityDistribution();
        $maxCount = empty($distribution) ? 0 : max($distribution);
        $facility = array_search($maxCount, $distribution, true);

        return [
            'facility' => $facility !== false ? $facility : 'speed',
            'count' => $maxCount,
        ];
    }

    /**
     * Get the facility with the fewest cards
     *
     * @return array{facility: string, count: int}
     */
    public function getLeastPopulatedFacility(): array
    {
        $distribution = $this->getFacilityDistribution();
        $minCount = empty($distribution) ? 0 : min($distribution);
        $facility = array_search($minCount, $distribution, true);

        return [
            'facility' => $facility !== false ? $facility : 'speed',
            'count' => $minCount,
        ];
    }

    /**
     * Check if deck is full (6 cards)
     */
    public function isFull(): bool
    {
        return count($this->cards) === self::MAX_CARDS;
    }

    /**
     * Check if deck is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->cards);
    }

    /**
     * Get the number of cards in the deck
     */
    public function getCardCount(): int
    {
        return count($this->cards);
    }

    /**
     * Calculate multi-training bonus for a facility
     *
     * +5% per card present (max +30% with 6 cards)
     */
    public function getMultiTrainingBonus(string $facility): float
    {
        $cardCount = $this->getCardCountAtFacility($facility);

        return min($cardCount * 0.05, 0.30);
    }

    /**
     * Estimate turns needed to reach Friendship Training threshold
     *
     * Assumes base +7 bond per training, +9 with Charming trait, +12 with hint mark
     *
     * @param  int  $bondGainPerTurn  Average bond gain per turn (default 7)
     * @return array<int, int> Card ID => turns needed
     */
    public function estimateTurnsToFriendship(int $bondGainPerTurn = 7): array
    {
        $estimates = [];

        foreach ($this->cards as $card) {
            if ($card->bond >= self::FRIENDSHIP_THRESHOLD) {
                $estimates[$card->id] = 0; // Already ready
            } else {
                $bondNeeded = self::FRIENDSHIP_THRESHOLD - $card->bond;
                $turnsNeeded = (int) ceil($bondNeeded / $bondGainPerTurn);
                $estimates[$card->id] = $turnsNeeded;
            }
        }

        return $estimates;
    }

    /**
     * Create deck from an array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $cards = [];

        if (isset($data['cards']) && is_array($data['cards'])) {
            foreach ($data['cards'] as $cardData) {
                if (is_array($cardData)) {
                    /** @var array<string, mixed> $cardData */
                    $cards[] = SupportCard::fromArray($cardData);
                }
            }
        }

        return new self($cards);
    }

    /**
     * Convert deck to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'cards' => array_map(fn (SupportCard $card) => $card->toArray(), $this->cards),
        ];
    }

    /**
     * Create an empty deck
     */
    public static function empty(): self
    {
        return new self([]);
    }
}
