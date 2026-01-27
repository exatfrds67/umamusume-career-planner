<?php

declare(strict_types=1);

/**
 * Support Deck Service Tests
 *
 * Tests for the SupportDeckService including:
 * - Deck validation (6 cards, 1 friend card max, no duplicates)
 * - Deck saving
 * - Deck tier calculation
 * - Card recommendations
 */

use App\Models\Character;
use App\Models\SupportCardDefinition;
use App\Models\User;
use App\Services\SupportDeckService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
    $this->supportCards = SupportCardDefinition::factory()->count(10)->create();
    $this->service = app(SupportDeckService::class);
});

describe('Deck Validation', function () {
    it('validates deck with exactly 6 cards', function () {
        $cards = $this->supportCards->take(6)->map(fn ($card, $index) => [
            'support_card_id' => $card->id,
            'is_friend_card' => $index === 5,
        ])->toArray();

        $result = $this->service->validateDeck($cards);

        expect($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty();
    });

    it('rejects deck with less than 6 cards', function () {
        $cards = $this->supportCards->take(4)->map(fn ($card) => [
            'support_card_id' => $card->id,
            'is_friend_card' => false,
        ])->toArray();

        $result = $this->service->validateDeck($cards);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Deck must contain exactly 6 cards');
    });

    it('rejects deck with more than 1 friend card', function () {
        $cards = $this->supportCards->take(6)->map(fn ($card, $index) => [
            'support_card_id' => $card->id,
            'is_friend_card' => $index >= 4, // 2 friend cards
        ])->toArray();

        $result = $this->service->validateDeck($cards);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Deck can only have 1 friend card');
    });

    it('rejects deck with duplicate owned cards', function () {
        $card = $this->supportCards->first();
        $cards = [
            ['support_card_id' => $card->id, 'is_friend_card' => false],
            ['support_card_id' => $card->id, 'is_friend_card' => false], // Duplicate
            ['support_card_id' => $this->supportCards[1]->id, 'is_friend_card' => false],
            ['support_card_id' => $this->supportCards[2]->id, 'is_friend_card' => false],
            ['support_card_id' => $this->supportCards[3]->id, 'is_friend_card' => false],
            ['support_card_id' => $this->supportCards[4]->id, 'is_friend_card' => true],
        ];

        $result = $this->service->validateDeck($cards);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Deck cannot contain duplicate cards (except friend cards)');
    });

    it('warns about low specialization diversity', function () {
        // Create cards with same type
        $sameTypeCards = SupportCardDefinition::factory()->count(6)->create([
            'card_type' => 'speed',
        ]);

        $cards = $sameTypeCards->map(fn ($card, $index) => [
            'support_card_id' => $card->id,
            'is_friend_card' => $index === 5,
        ])->toArray();

        $result = $this->service->validateDeck($cards);

        expect($result['warnings'])->not->toBeEmpty();
    });
});

describe('Deck Saving', function () {
    it('saves deck configuration for character', function () {
        $cards = $this->supportCards->take(6)->map(fn ($card, $index) => [
            'support_card_id' => $card->id,
            'is_friend_card' => $index === 5,
            'limit_break_level' => 0,
        ])->toArray();

        $result = $this->service->saveDeck($this->character, $cards);

        expect($result)->toBeTrue();

        // Verify cards were saved
        $savedCards = $this->character->supportCards()->count();
        expect($savedCards)->toBe(6);
    });

    it('replaces existing deck when saving new one', function () {
        // Save first deck
        $firstDeck = $this->supportCards->take(6)->map(fn ($card, $index) => [
            'support_card_id' => $card->id,
            'is_friend_card' => $index === 5,
        ])->toArray();

        $this->service->saveDeck($this->character, $firstDeck);

        // Save second deck with different cards
        $newCards = SupportCardDefinition::factory()->count(6)->create();
        $secondDeck = $newCards->map(fn ($card, $index) => [
            'support_card_id' => $card->id,
            'is_friend_card' => $index === 5,
        ])->toArray();

        $this->service->saveDeck($this->character, $secondDeck);

        // Verify only 6 cards exist (not 12)
        $savedCards = $this->character->supportCards()->count();
        expect($savedCards)->toBe(6);
    });

    it('returns false for invalid deck', function () {
        $cards = $this->supportCards->take(4)->map(fn ($card) => [
            'support_card_id' => $card->id,
            'is_friend_card' => false,
        ])->toArray();

        $result = $this->service->saveDeck($this->character, $cards);

        expect($result)->toBeFalse();
    });
});

describe('Deck Tier Calculation', function () {
    it('calculates deck tier based on card quality', function () {
        // Create high-tier cards and save to character
        $highTierCards = SupportCardDefinition::factory()->count(6)->create([
            'meta_tier' => 'S+',
            'rarity' => 'SSR',
        ]);

        $cards = $highTierCards->map(fn ($card, $index) => [
            'support_card_id' => $card->id,
            'is_friend_card' => $index === 5,
        ])->toArray();

        $this->service->saveDeck($this->character, $cards);

        $tier = $this->service->calculateDeckTier($this->character->fresh());

        expect($tier)->toBeIn(['S+', 'S', 'A', 'B', 'C']);
    });

    it('returns Unranked for empty deck', function () {
        $tier = $this->service->calculateDeckTier($this->character);

        expect($tier)->toBe('Unranked');
    });

    it('calculates lower tier for lower quality cards', function () {
        // Create low-tier cards
        $lowTierCards = SupportCardDefinition::factory()->count(6)->create([
            'meta_tier' => 'C',
            'rarity' => 'R',
        ]);

        $cards = $lowTierCards->map(fn ($card, $index) => [
            'support_card_id' => $card->id,
            'is_friend_card' => $index === 5,
        ])->toArray();

        $this->service->saveDeck($this->character, $cards);

        $tier = $this->service->calculateDeckTier($this->character->fresh());

        expect($tier)->toBe('C');
    });
});

describe('Deck Recommendations', function () {
    it('returns card recommendations based on character needs', function () {
        // Create some high-tier cards for recommendations
        SupportCardDefinition::factory()->count(5)->create([
            'meta_tier' => 'S+',
            'is_active' => true,
        ]);

        $recommendations = $this->service->getRecommendations($this->character);

        expect($recommendations)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    });

    it('filters recommendations by focus stat', function () {
        // Create cards of different types
        SupportCardDefinition::factory()->count(3)->create([
            'card_type' => 'speed',
            'meta_tier' => 'S',
            'is_active' => true,
        ]);

        SupportCardDefinition::factory()->count(3)->create([
            'card_type' => 'stamina',
            'meta_tier' => 'S',
            'is_active' => true,
        ]);

        $recommendations = $this->service->getRecommendations($this->character, 'speed');

        // All recommendations should be speed type
        $allSpeed = $recommendations->every(fn ($card) => $card->card_type === 'speed');
        expect($allSpeed)->toBeTrue();
    });
});
