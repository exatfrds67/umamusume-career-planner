<?php

use App\Models\Character;
use App\Models\SupportCardDefinition;
use App\Services\SupportDeckService;

/** @var SupportDeckService $deckService */
$deckService = null;

beforeEach(function () use (&$deckService) {
    $deckService = app(SupportDeckService::class);
});

it('validates deck with exactly 6 cards', function () use (&$deckService) {
    $cards = [
        ['support_card_id' => 1, 'is_friend_card' => false],
        ['support_card_id' => 2, 'is_friend_card' => false],
        ['support_card_id' => 3, 'is_friend_card' => false],
        ['support_card_id' => 4, 'is_friend_card' => false],
        ['support_card_id' => 5, 'is_friend_card' => false],
        ['support_card_id' => 6, 'is_friend_card' => true],
    ];

    $validation = $deckService->validateDeck($cards);

    expect($validation['valid'])->toBeTrue()
        ->and($validation['errors'])->toBeEmpty();
});

it('rejects deck with less than 6 cards', function () use (&$deckService) {
    $cards = [
        ['support_card_id' => 1, 'is_friend_card' => false],
        ['support_card_id' => 2, 'is_friend_card' => false],
    ];

    $validation = $deckService->validateDeck($cards);

    expect($validation['valid'])->toBeFalse()
        ->and($validation['errors'])->toContain('Deck must contain exactly 6 cards');
});

it('rejects deck with more than 1 friend card', function () use (&$deckService) {
    $cards = [
        ['support_card_id' => 1, 'is_friend_card' => false],
        ['support_card_id' => 2, 'is_friend_card' => false],
        ['support_card_id' => 3, 'is_friend_card' => false],
        ['support_card_id' => 4, 'is_friend_card' => false],
        ['support_card_id' => 5, 'is_friend_card' => true],
        ['support_card_id' => 6, 'is_friend_card' => true],
    ];

    $validation = $deckService->validateDeck($cards);

    expect($validation['valid'])->toBeFalse()
        ->and($validation['errors'])->toContain('Deck can only have 1 friend card');
});

it('rejects deck with duplicate cards', function () use (&$deckService) {
    $cards = [
        ['support_card_id' => 1, 'is_friend_card' => false],
        ['support_card_id' => 1, 'is_friend_card' => false], // Duplicate
        ['support_card_id' => 3, 'is_friend_card' => false],
        ['support_card_id' => 4, 'is_friend_card' => false],
        ['support_card_id' => 5, 'is_friend_card' => false],
        ['support_card_id' => 6, 'is_friend_card' => true],
    ];

    $validation = $deckService->validateDeck($cards);

    expect($validation['valid'])->toBeFalse()
        ->and($validation['errors'])->toContain('Deck cannot contain duplicate cards (except friend cards)');
});

it('warns about low type diversity', function () use (&$deckService) {
    // Create support cards with same type
    /** @var array<int, SupportCardDefinition> $speedCards */
    $speedCards = SupportCardDefinition::factory()->count(5)->create([
        'card_type' => 'speed',
        'is_active' => true,
    ])->all();
    $friendCard = SupportCardDefinition::factory()->create([
        'card_type' => 'friend',
        'is_active' => true,
    ]);

    $cards = [
        ['support_card_id' => $speedCards[0]->id, 'is_friend_card' => false],
        ['support_card_id' => $speedCards[1]->id, 'is_friend_card' => false],
        ['support_card_id' => $speedCards[2]->id, 'is_friend_card' => false],
        ['support_card_id' => $speedCards[3]->id, 'is_friend_card' => false],
        ['support_card_id' => $speedCards[4]->id, 'is_friend_card' => false],
        ['support_card_id' => $friendCard->id, 'is_friend_card' => true],
    ];

    $validation = $deckService->validateDeck($cards);

    expect($validation['warnings'])->toContain('Recommend at least 3 different specializations for balanced training');
});

it('saves valid deck configuration', function () use (&$deckService) {
    $character = Character::factory()->create();
    /** @var array<int, SupportCardDefinition> $cards */
    $cards = SupportCardDefinition::factory()->count(6)->create(['is_active' => true])->all();

    $deckCards = [
        ['support_card_id' => $cards[0]->id, 'is_friend_card' => false],
        ['support_card_id' => $cards[1]->id, 'is_friend_card' => false],
        ['support_card_id' => $cards[2]->id, 'is_friend_card' => false],
        ['support_card_id' => $cards[3]->id, 'is_friend_card' => false],
        ['support_card_id' => $cards[4]->id, 'is_friend_card' => false],
        ['support_card_id' => $cards[5]->id, 'is_friend_card' => true],
    ];

    $success = $deckService->saveDeck($character, $deckCards);

    expect($success)->toBeTrue();

    $character->refresh();
    expect($character->supportCards)->toHaveCount(6);
});

it('calculates deck tier rating', function () use (&$deckService) {
    $character = Character::factory()->create();

    // Create cards with different tiers
    /** @var array<int, SupportCardDefinition> $ssPlusCards */
    $ssPlusCards = SupportCardDefinition::factory()->count(2)->create(['meta_tier' => 'S+', 'is_active' => true])->all();
    /** @var array<int, SupportCardDefinition> $sCards */
    $sCards = SupportCardDefinition::factory()->count(2)->create(['meta_tier' => 'S', 'is_active' => true])->all();
    /** @var array<int, SupportCardDefinition> $aCards */
    $aCards = SupportCardDefinition::factory()->count(2)->create(['meta_tier' => 'A', 'is_active' => true])->all();

    $deckCards = [
        ['support_card_id' => $ssPlusCards[0]->id, 'is_friend_card' => false],
        ['support_card_id' => $ssPlusCards[1]->id, 'is_friend_card' => false],
        ['support_card_id' => $sCards[0]->id, 'is_friend_card' => false],
        ['support_card_id' => $sCards[1]->id, 'is_friend_card' => false],
        ['support_card_id' => $aCards[0]->id, 'is_friend_card' => false],
        ['support_card_id' => $aCards[1]->id, 'is_friend_card' => true],
    ];

    $deckService->saveDeck($character, $deckCards);

    $tier = $deckService->calculateDeckTier($character);

    expect($tier)->toBeIn(['S+', 'S', 'A']);
});

it('provides deck recommendations based on focus stat', function () use (&$deckService) {
    $character = Character::factory()->create();

    // Create speed cards
    SupportCardDefinition::factory()->count(3)->create([
        'card_type' => 'speed',
        'meta_tier' => 'S+',
        'is_active' => true,
    ]);

    $recommendations = $deckService->getRecommendations($character, 'speed');

    expect($recommendations)->not->toBeEmpty()
        ->and($recommendations->first()->card_type)->toBe('speed');
});

it('clears existing deck when saving new configuration', function () use (&$deckService) {
    $character = Character::factory()->create();
    $oldCards = SupportCardDefinition::factory()->count(6)->create(['is_active' => true]);
    $newCards = SupportCardDefinition::factory()->count(6)->create(['is_active' => true]);

    // Save initial deck
    $oldDeck = $oldCards->map(fn ($card, $index) => [
        'support_card_id' => $card->id,
        'is_friend_card' => $index === 5,
    ])->toArray();

    $deckService->saveDeck($character, $oldDeck);

    expect($character->supportCards)->toHaveCount(6);

    // Save new deck
    $newDeck = $newCards->map(fn ($card, $index) => [
        'support_card_id' => $card->id,
        'is_friend_card' => $index === 5,
    ])->toArray();

    $deckService->saveDeck($character, $newDeck);

    $character->refresh();
    expect($character->supportCards)->toHaveCount(6);

    // Verify old cards are not in deck
    $currentCardIds = $character->supportCards->pluck('support_card_id')->toArray();
    $oldCardIds = $oldCards->pluck('id')->toArray();

    expect($currentCardIds)->not->toEqual($oldCardIds);
});
