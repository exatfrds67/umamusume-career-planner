<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Deck Management Service
 * Handles 6-card deck configuration, validation, and management
 */
class DeckManagementService
{
    /**
     * Maximum number of cards in a deck
     */
    private const MAX_DECK_SIZE = 6;

    /**
     * Maximum number of owned cards (excluding friend card)
     */
    private const MAX_OWNED_CARDS = 5;

    /**
     * Maximum number of friend cards
     */
    private const MAX_FRIEND_CARDS = 1;

    /**
     * Get the complete deck for a character
     *
     * @return Collection<int, CharacterSupportCard>
     */
    public function getDeck(int $characterId): Collection
    {
        return CharacterSupportCard::where('character_id', $characterId)
            ->with('supportCard')
            ->orderBy('position_slot')
            ->get();
    }

    /**
     * Add a card to the deck
     *
     * @throws ValidationException
     */
    public function addCardToDeck(
        int $characterId,
        int $supportCardId,
        int $positionSlot,
        bool $isFriendCard = false,
        int $limitBreakLevel = 0
    ): CharacterSupportCard {
        // Validate deck constraints
        $this->validateDeckConstraints($characterId, $positionSlot, $isFriendCard);

        // Validate limit break level
        $this->validateLimitBreakLevel($supportCardId, $limitBreakLevel);

        try {
            $characterSupportCard = CharacterSupportCard::create([
                'character_id' => $characterId,
                'support_card_id' => $supportCardId,
                'position_slot' => $positionSlot,
                'is_friend_card' => $isFriendCard,
                'limit_break_level' => $limitBreakLevel,
                'friendship_level' => 0,
            ]);

            Log::info('Card added to deck', [
                'character_id' => $characterId,
                'support_card_id' => $supportCardId,
                'position_slot' => $positionSlot,
                'is_friend_card' => $isFriendCard,
            ]);

            return $characterSupportCard;
        } catch (\Exception $e) {
            Log::error('Failed to add card to deck', [
                'error' => $e->getMessage(),
                'character_id' => $characterId,
                'support_card_id' => $supportCardId,
            ]);

            throw $e;
        }
    }

    /**
     * Remove a card from the deck
     */
    public function removeCardFromDeck(int $characterId, int $positionSlot): bool
    {
        try {
            $deleted = CharacterSupportCard::where('character_id', $characterId)
                ->where('position_slot', $positionSlot)
                ->delete();

            if ($deleted) {
                Log::info('Card removed from deck', [
                    'character_id' => $characterId,
                    'position_slot' => $positionSlot,
                ]);
            }

            return (bool) $deleted;
        } catch (\Exception $e) {
            Log::error('Failed to remove card from deck', [
                'error' => $e->getMessage(),
                'character_id' => $characterId,
                'position_slot' => $positionSlot,
            ]);

            return false;
        }
    }

    /**
     * Swap two cards in the deck
     */
    public function swapCards(int $characterId, int $position1, int $position2): bool
    {
        try {
            return DB::transaction(function () use ($characterId, $position1, $position2) {
                $card1 = CharacterSupportCard::where('character_id', $characterId)
                    ->where('position_slot', $position1)
                    ->lockForUpdate()
                    ->first();

                $card2 = CharacterSupportCard::where('character_id', $characterId)
                    ->where('position_slot', $position2)
                    ->lockForUpdate()
                    ->first();

                if (! $card1 || ! $card2) {
                    throw new \Exception('One or both cards not found');
                }

                // Temporarily set to negative values to avoid unique constraint violation
                $card1->position_slot = -$position1;
                $card1->save();

                $card2->position_slot = $position1;
                $card2->save();

                $card1->position_slot = $position2;
                $card1->save();

                Log::info('Cards swapped in deck', [
                    'character_id' => $characterId,
                    'position1' => $position1,
                    'position2' => $position2,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Failed to swap cards', [
                'error' => $e->getMessage(),
                'character_id' => $characterId,
                'position1' => $position1,
                'position2' => $position2,
            ]);

            return false;
        }
    }

    /**
     * Replace a card in the deck
     */
    public function replaceCard(
        int $characterId,
        int $positionSlot,
        int $newSupportCardId,
        int $limitBreakLevel = 0
    ): ?CharacterSupportCard {
        try {
            if ($newSupportCardId < 0) {
                throw new \InvalidArgumentException('Support card ID must be non-negative.');
            }

            return DB::transaction(function () use ($characterId, $positionSlot, $newSupportCardId, $limitBreakLevel) {
                $existingCard = CharacterSupportCard::where('character_id', $characterId)
                    ->where('position_slot', $positionSlot)
                    ->lockForUpdate()
                    ->first();

                if (! $existingCard) {
                    throw new \Exception('Card not found at position');
                }

                $isFriendCard = $existingCard->is_friend_card;

                // Validate limit break level
                $this->validateLimitBreakLevel($newSupportCardId, $limitBreakLevel);

                $existingCard->support_card_id = $newSupportCardId;
                $existingCard->limit_break_level = $limitBreakLevel;
                $existingCard->friendship_level = 0; // Reset friendship when replacing
                $existingCard->save();

                Log::info('Card replaced in deck', [
                    'character_id' => $characterId,
                    'position_slot' => $positionSlot,
                    'new_support_card_id' => $newSupportCardId,
                ]);

                return $existingCard;
            });
        } catch (\Exception $e) {
            Log::error('Failed to replace card', [
                'error' => $e->getMessage(),
                'character_id' => $characterId,
                'position_slot' => $positionSlot,
            ]);

            return null;
        }
    }

    /**
     * Clear the entire deck
     */
    public function clearDeck(int $characterId): bool
    {
        try {
            CharacterSupportCard::where('character_id', $characterId)->delete();

            Log::info('Deck cleared', ['character_id' => $characterId]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear deck', [
                'error' => $e->getMessage(),
                'character_id' => $characterId,
            ]);

            return false;
        }
    }

    /**
     * Validate deck constraints
     *
     * @throws ValidationException
     */
    private function validateDeckConstraints(int $characterId, int $positionSlot, bool $isFriendCard): void
    {
        // Validate position slot
        if ($positionSlot < 1 || $positionSlot > self::MAX_DECK_SIZE) {
            throw ValidationException::withMessages([
                'position_slot' => 'Position slot must be between 1 and '.self::MAX_DECK_SIZE,
            ]);
        }

        // Check if position is already occupied
        $existingCard = CharacterSupportCard::where('character_id', $characterId)
            ->where('position_slot', $positionSlot)
            ->first();

        if ($existingCard) {
            throw ValidationException::withMessages([
                'position_slot' => "Position slot {$positionSlot} is already occupied",
            ]);
        }

        // Check deck size
        $currentDeckSize = CharacterSupportCard::where('character_id', $characterId)->count();

        if ($currentDeckSize >= self::MAX_DECK_SIZE) {
            throw ValidationException::withMessages([
                'deck_size' => 'Deck is full. Maximum '.self::MAX_DECK_SIZE.' cards allowed',
            ]);
        }

        // Check friend card constraint
        if ($isFriendCard) {
            $friendCardCount = CharacterSupportCard::where('character_id', $characterId)
                ->where('is_friend_card', true)
                ->count();

            if ($friendCardCount >= self::MAX_FRIEND_CARDS) {
                throw ValidationException::withMessages([
                    'friend_card' => 'Only '.self::MAX_FRIEND_CARDS.' friend card allowed per deck',
                ]);
            }
        } else {
            $ownedCardCount = CharacterSupportCard::where('character_id', $characterId)
                ->where('is_friend_card', false)
                ->count();

            if ($ownedCardCount >= self::MAX_OWNED_CARDS) {
                throw ValidationException::withMessages([
                    'owned_cards' => 'Maximum '.self::MAX_OWNED_CARDS.' owned cards allowed per deck',
                ]);
            }
        }
    }

    /**
     * Validate limit break level
     *
     * @throws ValidationException
     */
    private function validateLimitBreakLevel(int $supportCardId, int $limitBreakLevel): void
    {
        $supportCard = SupportCardDefinition::findOrFail($supportCardId);

        if ($limitBreakLevel < 0 || $limitBreakLevel > $supportCard->max_limit_break) {
            throw ValidationException::withMessages([
                'limit_break_level' => "Limit break level must be between 0 and {$supportCard->max_limit_break}",
            ]);
        }
    }

    /**
     * Get deck statistics
     *
     * @return array{total_cards: int, owned_cards: int, friend_cards: int, card_types: array<string, int>, average_limit_break: float, average_friendship: float, rarity_distribution: array<string, int>}
     */
    public function getDeckStatistics(int $characterId): array
    {
        $deck = $this->getDeck($characterId);

        $stats = [
            'total_cards' => $deck->count(),
            'owned_cards' => $deck->where('is_friend_card', false)->count(),
            'friend_cards' => $deck->where('is_friend_card', true)->count(),
            'card_types' => [],
            'average_limit_break' => 0,
            'average_friendship' => 0,
            'rarity_distribution' => [],
        ];

        if ($deck->isEmpty()) {
            return $stats;
        }

        // Card type distribution
        /** @var array<string, int> $cardTypes */
        $cardTypes = $deck->groupBy('supportCard.card_type')
            ->map->count()
            ->toArray();
        $stats['card_types'] = $cardTypes;

        // Average limit break level
        $stats['average_limit_break'] = round($deck->avg('limit_break_level') ?? 0, 2);

        // Average friendship level
        $stats['average_friendship'] = round($deck->avg('friendship_level') ?? 0, 2);

        // Rarity distribution
        /** @var array<string, int> $rarityDistribution */
        $rarityDistribution = $deck->groupBy('supportCard.rarity')
            ->map->count()
            ->toArray();
        $stats['rarity_distribution'] = $rarityDistribution;

        return $stats;
    }

    /**
     * Check if deck is valid (has exactly 6 cards with proper constraints)
     *
     * @return array{is_valid: bool, errors: list<string>, warnings: list<string>}
     */
    public function validateDeck(int $characterId): array
    {
        $deck = $this->getDeck($characterId);

        $validation = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
        ];

        // Check deck size
        if ($deck->count() < self::MAX_DECK_SIZE) {
            $validation['is_valid'] = false;
            $validation['errors'][] = 'Deck must have exactly '.self::MAX_DECK_SIZE." cards. Current: {$deck->count()}";
        } elseif ($deck->count() > self::MAX_DECK_SIZE) {
            $validation['is_valid'] = false;
            $validation['errors'][] = 'Deck has too many cards. Maximum: '.self::MAX_DECK_SIZE.". Current: {$deck->count()}";
        }

        // Check friend card constraint
        $friendCardCount = $deck->where('is_friend_card', true)->count();
        if ($friendCardCount === 0) {
            $validation['warnings'][] = 'No friend card in deck. Consider adding one for additional bonuses.';
        } elseif ($friendCardCount > self::MAX_FRIEND_CARDS) {
            $validation['is_valid'] = false;
            $validation['errors'][] = 'Too many friend cards. Maximum: '.self::MAX_FRIEND_CARDS.". Current: {$friendCardCount}";
        }

        // Check owned card constraint
        $ownedCardCount = $deck->where('is_friend_card', false)->count();
        if ($ownedCardCount > self::MAX_OWNED_CARDS) {
            $validation['is_valid'] = false;
            $validation['errors'][] = 'Too many owned cards. Maximum: '.self::MAX_OWNED_CARDS.". Current: {$ownedCardCount}";
        }

        // Check position slots
        $positions = $deck->pluck('position_slot')->sort()->values()->toArray();
        $expectedPositions = range(1, self::MAX_DECK_SIZE);
        if ($positions !== $expectedPositions) {
            $validation['is_valid'] = false;
            $validation['errors'][] = 'Invalid position slots. Expected: '.implode(', ', $expectedPositions).'. Found: '.implode(', ', $positions);
        }

        return $validation;
    }
}
