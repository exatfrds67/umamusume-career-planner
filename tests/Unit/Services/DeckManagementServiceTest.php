<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use App\Services\DeckManagementService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Validation\ValidationException;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->deckService = new DeckManagementService;
    $this->character = Character::factory()->create();
});

describe('DeckManagementService', function () {
    describe('getDeck', function () {
        it('returns empty collection for character with no cards', function () {
            $deck = $this->deckService->getDeck($this->character->id);

            expect($deck)->toBeEmpty();
        });

        it('returns cards ordered by position slot', function () {
            $card1 = SupportCardDefinition::factory()->create();
            $card2 = SupportCardDefinition::factory()->create();

            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $card2->id,
                'position_slot' => 2,
            ]);

            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $card1->id,
                'position_slot' => 1,
            ]);

            $deck = $this->deckService->getDeck($this->character->id);

            expect($deck)->toHaveCount(2);
            expect($deck->first()->position_slot)->toBe(1);
            expect($deck->last()->position_slot)->toBe(2);
        });
    });

    describe('addCardToDeck', function () {
        it('adds a card to the deck successfully', function () {
            $supportCard = SupportCardDefinition::factory()->create([
                'max_limit_break' => 4,
            ]);

            $result = $this->deckService->addCardToDeck(
                $this->character->id,
                $supportCard->id,
                1,
                false,
                2
            );

            expect($result)->toBeInstanceOf(CharacterSupportCard::class);
            expect($result->position_slot)->toBe(1);
            expect($result->limit_break_level)->toBe(2);
            expect($result->is_friend_card)->toBeFalse();
        });

        it('throws exception for invalid position slot', function () {
            $supportCard = SupportCardDefinition::factory()->create();

            $this->deckService->addCardToDeck(
                $this->character->id,
                $supportCard->id,
                7, // Invalid: max is 6
                false
            );
        })->throws(ValidationException::class);

        it('throws exception for occupied position slot', function () {
            $supportCard1 = SupportCardDefinition::factory()->create();
            $supportCard2 = SupportCardDefinition::factory()->create();

            $this->deckService->addCardToDeck(
                $this->character->id,
                $supportCard1->id,
                1,
                false
            );

            $this->deckService->addCardToDeck(
                $this->character->id,
                $supportCard2->id,
                1, // Same position
                false
            );
        })->throws(ValidationException::class);

        it('throws exception when deck is full', function () {
            // Fill deck with 6 cards
            for ($i = 1; $i <= 6; $i++) {
                $card = SupportCardDefinition::factory()->create();
                CharacterSupportCard::factory()->create([
                    'character_id' => $this->character->id,
                    'support_card_id' => $card->id,
                    'position_slot' => $i,
                    'is_friend_card' => $i === 6,
                ]);
            }

            $newCard = SupportCardDefinition::factory()->create();

            // This should fail - deck is full
            $this->deckService->addCardToDeck(
                $this->character->id,
                $newCard->id,
                7,
                false
            );
        })->throws(ValidationException::class);

        it('enforces single friend card constraint', function () {
            $friendCard1 = SupportCardDefinition::factory()->create();
            $friendCard2 = SupportCardDefinition::factory()->create();

            $this->deckService->addCardToDeck(
                $this->character->id,
                $friendCard1->id,
                1,
                true // Friend card
            );

            $this->deckService->addCardToDeck(
                $this->character->id,
                $friendCard2->id,
                2,
                true // Second friend card - should fail
            );
        })->throws(ValidationException::class);

        it('enforces max 5 owned cards constraint', function () {
            // Add 5 owned cards
            for ($i = 1; $i <= 5; $i++) {
                $card = SupportCardDefinition::factory()->create();
                CharacterSupportCard::factory()->create([
                    'character_id' => $this->character->id,
                    'support_card_id' => $card->id,
                    'position_slot' => $i,
                    'is_friend_card' => false,
                ]);
            }

            $newCard = SupportCardDefinition::factory()->create();

            // 6th owned card should fail
            $this->deckService->addCardToDeck(
                $this->character->id,
                $newCard->id,
                6,
                false // Owned card
            );
        })->throws(ValidationException::class);
    });

    describe('removeCardFromDeck', function () {
        it('removes a card from the deck', function () {
            $supportCard = SupportCardDefinition::factory()->create();

            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $supportCard->id,
                'position_slot' => 1,
            ]);

            $result = $this->deckService->removeCardFromDeck($this->character->id, 1);

            expect($result)->toBeTrue();
            expect($this->deckService->getDeck($this->character->id))->toBeEmpty();
        });

        it('returns false when card not found', function () {
            $result = $this->deckService->removeCardFromDeck($this->character->id, 99);

            expect($result)->toBeFalse();
        });
    });

    describe('swapCards', function () {
        it('swaps two cards in the deck', function () {
            $card1 = SupportCardDefinition::factory()->create();
            $card2 = SupportCardDefinition::factory()->create();

            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $card1->id,
                'position_slot' => 1,
            ]);

            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $card2->id,
                'position_slot' => 2,
            ]);

            $result = $this->deckService->swapCards($this->character->id, 1, 2);

            expect($result)->toBeTrue();

            $deck = $this->deckService->getDeck($this->character->id);
            expect($deck->where('position_slot', 1)->first()->support_card_id)->toBe($card2->id);
            expect($deck->where('position_slot', 2)->first()->support_card_id)->toBe($card1->id);
        });

        it('returns false when one card is missing', function () {
            $card = SupportCardDefinition::factory()->create();

            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $card->id,
                'position_slot' => 1,
            ]);

            $result = $this->deckService->swapCards($this->character->id, 1, 2);

            expect($result)->toBeFalse();
        });
    });

    describe('replaceCard', function () {
        it('replaces a card in the deck', function () {
            $oldCard = SupportCardDefinition::factory()->create();
            $newCard = SupportCardDefinition::factory()->create([
                'max_limit_break' => 4,
            ]);

            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $oldCard->id,
                'position_slot' => 1,
                'friendship_level' => 80,
            ]);

            $result = $this->deckService->replaceCard(
                $this->character->id,
                1,
                $newCard->id,
                3
            );

            expect($result)->toBeInstanceOf(CharacterSupportCard::class);
            expect($result->support_card_id)->toBe($newCard->id);
            expect($result->limit_break_level)->toBe(3);
            expect($result->friendship_level)->toBe(0); // Reset on replace
        });

        it('returns null when position not found', function () {
            $newCard = SupportCardDefinition::factory()->create();

            $result = $this->deckService->replaceCard(
                $this->character->id,
                99,
                $newCard->id
            );

            expect($result)->toBeNull();
        });
    });

    describe('clearDeck', function () {
        it('removes all cards from the deck', function () {
            for ($i = 1; $i <= 3; $i++) {
                $card = SupportCardDefinition::factory()->create();
                CharacterSupportCard::factory()->create([
                    'character_id' => $this->character->id,
                    'support_card_id' => $card->id,
                    'position_slot' => $i,
                ]);
            }

            $result = $this->deckService->clearDeck($this->character->id);

            expect($result)->toBeTrue();
            expect($this->deckService->getDeck($this->character->id))->toBeEmpty();
        });
    });

    describe('getDeckStatistics', function () {
        it('returns statistics for the deck', function () {
            $speedCard = SupportCardDefinition::factory()->create([
                'card_type' => 'speed',
                'rarity' => 'SSR',
            ]);

            $staminaCard = SupportCardDefinition::factory()->create([
                'card_type' => 'stamina',
                'rarity' => 'SR',
            ]);

            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $speedCard->id,
                'position_slot' => 1,
                'limit_break_level' => 4,
                'friendship_level' => 100,
                'is_friend_card' => false,
            ]);

            CharacterSupportCard::factory()->create([
                'character_id' => $this->character->id,
                'support_card_id' => $staminaCard->id,
                'position_slot' => 2,
                'limit_break_level' => 2,
                'friendship_level' => 50,
                'is_friend_card' => true,
            ]);

            $stats = $this->deckService->getDeckStatistics($this->character->id);

            expect($stats['total_cards'])->toBe(2);
            expect($stats['owned_cards'])->toBe(1);
            expect($stats['friend_cards'])->toBe(1);
            expect($stats['average_limit_break'])->toBe(3.0);
            expect($stats['average_friendship'])->toBe(75.0);
        });

        it('returns empty statistics for empty deck', function () {
            $stats = $this->deckService->getDeckStatistics($this->character->id);

            expect($stats['total_cards'])->toBe(0);
            expect($stats['average_limit_break'])->toBe(0);
            expect($stats['average_friendship'])->toBe(0);
        });
    });

    describe('validateDeck', function () {
        it('validates a complete deck', function () {
            // Create a valid 6-card deck
            for ($i = 1; $i <= 6; $i++) {
                $card = SupportCardDefinition::factory()->create();
                CharacterSupportCard::factory()->create([
                    'character_id' => $this->character->id,
                    'support_card_id' => $card->id,
                    'position_slot' => $i,
                    'is_friend_card' => $i === 6,
                ]);
            }

            $validation = $this->deckService->validateDeck($this->character->id);

            expect($validation['is_valid'])->toBeTrue();
            expect($validation['errors'])->toBeEmpty();
        });

        it('reports errors for incomplete deck', function () {
            // Only add 3 cards
            for ($i = 1; $i <= 3; $i++) {
                $card = SupportCardDefinition::factory()->create();
                CharacterSupportCard::factory()->create([
                    'character_id' => $this->character->id,
                    'support_card_id' => $card->id,
                    'position_slot' => $i,
                ]);
            }

            $validation = $this->deckService->validateDeck($this->character->id);

            expect($validation['is_valid'])->toBeFalse();
            expect($validation['errors'])->not->toBeEmpty();
        });

        it('warns when no friend card present', function () {
            // Create 6 owned cards (no friend card)
            for ($i = 1; $i <= 6; $i++) {
                $card = SupportCardDefinition::factory()->create();
                CharacterSupportCard::factory()->create([
                    'character_id' => $this->character->id,
                    'support_card_id' => $card->id,
                    'position_slot' => $i,
                    'is_friend_card' => false,
                ]);
            }

            $validation = $this->deckService->validateDeck($this->character->id);

            expect($validation['warnings'])->not->toBeEmpty();
        });
    });
});
