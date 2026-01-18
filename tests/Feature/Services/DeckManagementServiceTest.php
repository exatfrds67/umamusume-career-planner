<?php

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use App\Models\User;
use App\Services\DeckManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new DeckManagementService;

    // Create a test user
    $this->user = User::factory()->create();

    // Create a test character using factory
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Create test support cards
    $this->speedCard = SupportCardDefinition::create([
        'name' => 'Test Speed Card',
        'internal_id' => 'TEST_SPEED_1',
        'card_type' => 'speed',
        'rarity' => 'SSR',
        'max_limit_break' => 4,
        'is_active' => true,
    ]);

    $this->staminaCard = SupportCardDefinition::create([
        'name' => 'Test Stamina Card',
        'internal_id' => 'TEST_STAMINA_1',
        'card_type' => 'stamina',
        'rarity' => 'SSR',
        'max_limit_break' => 4,
        'is_active' => true,
    ]);

    $this->powerCard = SupportCardDefinition::create([
        'name' => 'Test Power Card',
        'internal_id' => 'TEST_POWER_1',
        'card_type' => 'power',
        'rarity' => 'SR',
        'max_limit_break' => 4,
        'is_active' => true,
    ]);

    $this->friendCard = SupportCardDefinition::create([
        'name' => 'Test Friend Card',
        'internal_id' => 'TEST_FRIEND_1',
        'card_type' => 'friend',
        'rarity' => 'SSR',
        'max_limit_break' => 4,
        'is_active' => true,
    ]);
});

it('can add a card to the deck', function () {
    $characterSupportCard = $this->service->addCardToDeck(
        $this->character->id,
        $this->speedCard->id,
        1,
        false,
        2
    );

    expect($characterSupportCard)->toBeInstanceOf(CharacterSupportCard::class)
        ->and($characterSupportCard->character_id)->toBe($this->character->id)
        ->and($characterSupportCard->support_card_id)->toBe($this->speedCard->id)
        ->and($characterSupportCard->position_slot)->toBe(1)
        ->and($characterSupportCard->is_friend_card)->toBeFalse()
        ->and($characterSupportCard->limit_break_level)->toBe(2)
        ->and($characterSupportCard->friendship_level)->toBe(0);
});

it('can get the complete deck', function () {
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 1);
    $this->service->addCardToDeck($this->character->id, $this->staminaCard->id, 2);

    $deck = $this->service->getDeck($this->character->id);

    expect($deck)->toHaveCount(2)
        ->and($deck->first()->position_slot)->toBe(1)
        ->and($deck->last()->position_slot)->toBe(2);
});

it('enforces maximum deck size of 6 cards', function () {
    // Add 6 cards
    for ($i = 1; $i <= 6; $i++) {
        $this->service->addCardToDeck($this->character->id, $this->speedCard->id, $i);
    }

    // Try to add 7th card
    $this->service->addCardToDeck($this->character->id, $this->staminaCard->id, 7);
})->throws(ValidationException::class);

it('enforces maximum of 5 owned cards', function () {
    // Add 5 owned cards
    for ($i = 1; $i <= 5; $i++) {
        $this->service->addCardToDeck($this->character->id, $this->speedCard->id, $i, false);
    }

    // Try to add 6th owned card
    $this->service->addCardToDeck($this->character->id, $this->staminaCard->id, 6, false);
})->throws(ValidationException::class);

it('enforces maximum of 1 friend card', function () {
    // Add 1 friend card
    $this->service->addCardToDeck($this->character->id, $this->friendCard->id, 1, true);

    // Try to add 2nd friend card
    $this->service->addCardToDeck($this->character->id, $this->friendCard->id, 2, true);
})->throws(ValidationException::class);

it('prevents duplicate position slots', function () {
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 1);

    // Try to add another card at position 1
    $this->service->addCardToDeck($this->character->id, $this->staminaCard->id, 1);
})->throws(ValidationException::class);

it('validates position slot range', function () {
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 0);
})->throws(ValidationException::class);

it('validates limit break level', function () {
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 1, false, 5);
})->throws(ValidationException::class);

it('can remove a card from the deck', function () {
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 1);

    $result = $this->service->removeCardFromDeck($this->character->id, 1);

    expect($result)->toBeTrue();

    $deck = $this->service->getDeck($this->character->id);
    expect($deck)->toHaveCount(0);
});

it('can swap two cards in the deck', function () {
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 1);
    $this->service->addCardToDeck($this->character->id, $this->staminaCard->id, 2);

    $result = $this->service->swapCards($this->character->id, 1, 2);

    expect($result)->toBeTrue();

    $deck = $this->service->getDeck($this->character->id);

    expect($deck->where('position_slot', 1)->first()->support_card_id)->toBe($this->staminaCard->id)
        ->and($deck->where('position_slot', 2)->first()->support_card_id)->toBe($this->speedCard->id);
});

it('can replace a card in the deck', function () {
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 1, false, 2);

    $result = $this->service->replaceCard($this->character->id, 1, $this->powerCard->id, 3);

    expect($result)->toBeInstanceOf(CharacterSupportCard::class)
        ->and($result->support_card_id)->toBe($this->powerCard->id)
        ->and($result->limit_break_level)->toBe(3)
        ->and($result->friendship_level)->toBe(0); // Friendship resets on replace
});

it('can clear the entire deck', function () {
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 1);
    $this->service->addCardToDeck($this->character->id, $this->staminaCard->id, 2);

    $result = $this->service->clearDeck($this->character->id);

    expect($result)->toBeTrue();

    $deck = $this->service->getDeck($this->character->id);
    expect($deck)->toHaveCount(0);
});

it('can get deck statistics', function () {
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 1, false, 3);
    $this->service->addCardToDeck($this->character->id, $this->staminaCard->id, 2, false, 2);
    $this->service->addCardToDeck($this->character->id, $this->powerCard->id, 3, false, 1);
    $this->service->addCardToDeck($this->character->id, $this->friendCard->id, 4, true, 4);

    $stats = $this->service->getDeckStatistics($this->character->id);

    expect($stats)->toBeArray()
        ->and($stats['total_cards'])->toBe(4)
        ->and($stats['owned_cards'])->toBe(3)
        ->and($stats['friend_cards'])->toBe(1)
        ->and($stats['average_limit_break'])->toBe(2.5)
        ->and($stats['card_types'])->toHaveKey('speed')
        ->and($stats['card_types'])->toHaveKey('stamina')
        ->and($stats['card_types'])->toHaveKey('power')
        ->and($stats['card_types'])->toHaveKey('friend')
        ->and($stats['rarity_distribution'])->toHaveKey('SSR')
        ->and($stats['rarity_distribution'])->toHaveKey('SR');
});

it('can validate a complete deck', function () {
    // Add 5 owned cards + 1 friend card
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 1, false);
    $this->service->addCardToDeck($this->character->id, $this->staminaCard->id, 2, false);
    $this->service->addCardToDeck($this->character->id, $this->powerCard->id, 3, false);
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 4, false);
    $this->service->addCardToDeck($this->character->id, $this->staminaCard->id, 5, false);
    $this->service->addCardToDeck($this->character->id, $this->friendCard->id, 6, true);

    $validation = $this->service->validateDeck($this->character->id);

    expect($validation['is_valid'])->toBeTrue()
        ->and($validation['errors'])->toBeEmpty();
});

it('detects incomplete deck', function () {
    $this->service->addCardToDeck($this->character->id, $this->speedCard->id, 1);

    $validation = $this->service->validateDeck($this->character->id);

    expect($validation['is_valid'])->toBeFalse()
        ->and($validation['errors'])->not->toBeEmpty();
});

it('validates deck with friend card', function () {
    // Add 5 owned cards + 1 friend card (valid deck)
    for ($i = 1; $i <= 5; $i++) {
        $this->service->addCardToDeck($this->character->id, $this->speedCard->id, $i, false);
    }
    $this->service->addCardToDeck($this->character->id, $this->friendCard->id, 6, true);

    $validation = $this->service->validateDeck($this->character->id);

    // Should be valid with friend card
    expect($validation['is_valid'])->toBeTrue()
        ->and($validation['warnings'])->toBeEmpty();
});
