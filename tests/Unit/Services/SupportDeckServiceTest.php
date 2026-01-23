<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use App\Models\User;
use App\Services\SupportDeckService;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->service = new SupportDeckService;
    Cache::flush();
});

describe('SupportDeckService', function (): void {
    describe('validateDeck', function (): void {
        it('validates complete deck with 6 cards', function (): void {
            $cards = [];
            for ($i = 0; $i < 6; $i++) {
                $supportCard = SupportCardDefinition::factory()->create([
                    'card_type' => ['speed', 'stamina', 'power', 'guts', 'wit', 'friend'][$i],
                ]);
                $cards[] = [
                    'support_card_id' => $supportCard->id,
                    'is_friend_card' => $i === 5,
                ];
            }

            $result = $this->service->validateDeck($cards);

            expect($result['valid'])->toBeTrue()
                ->and($result['errors'])->toBeEmpty();
        });

        it('returns error for incomplete deck', function (): void {
            $supportCard = SupportCardDefinition::factory()->create();
            $cards = [
                ['support_card_id' => $supportCard->id, 'is_friend_card' => false],
            ];

            $result = $this->service->validateDeck($cards);

            expect($result['valid'])->toBeFalse()
                ->and($result['errors'])->not->toBeEmpty();
        });

        it('returns error for too many friend cards', function (): void {
            $cards = [];
            for ($i = 0; $i < 6; $i++) {
                $supportCard = SupportCardDefinition::factory()->create();
                $cards[] = [
                    'support_card_id' => $supportCard->id,
                    'is_friend_card' => $i < 2, // 2 friend cards
                ];
            }

            $result = $this->service->validateDeck($cards);

            expect($result['valid'])->toBeFalse()
                ->and($result['errors'])->toContain('Deck can only have 1 friend card');
        });

        it('returns error for duplicate cards', function (): void {
            $supportCard = SupportCardDefinition::factory()->create();
            $cards = [];
            for ($i = 0; $i < 6; $i++) {
                $cards[] = [
                    'support_card_id' => $supportCard->id,
                    'is_friend_card' => false,
                ];
            }

            $result = $this->service->validateDeck($cards);

            expect($result['valid'])->toBeFalse()
                ->and($result['errors'])->toContain('Deck cannot contain duplicate cards (except friend cards)');
        });

        it('returns warning for low specialization diversity', function (): void {
            $cards = [];
            for ($i = 0; $i < 6; $i++) {
                $supportCard = SupportCardDefinition::factory()->create([
                    'card_type' => 'speed', // All same type
                ]);
                $cards[] = [
                    'support_card_id' => $supportCard->id,
                    'is_friend_card' => false,
                ];
            }

            $result = $this->service->validateDeck($cards);

            expect($result['warnings'])->not->toBeEmpty();
        });
    });

    describe('saveDeck', function (): void {
        it('saves valid deck for character', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $cards = [];
            for ($i = 0; $i < 6; $i++) {
                $supportCard = SupportCardDefinition::factory()->create([
                    'card_type' => ['speed', 'stamina', 'power', 'guts', 'wit', 'friend'][$i],
                ]);
                $cards[] = [
                    'support_card_id' => $supportCard->id,
                    'is_friend_card' => $i === 5,
                ];
            }

            $result = $this->service->saveDeck($character, $cards);

            expect($result)->toBeTrue();
            expect($character->supportCards()->count())->toBe(6);
        });

        it('returns false for invalid deck', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $supportCard = SupportCardDefinition::factory()->create();
            $cards = [
                ['support_card_id' => $supportCard->id, 'is_friend_card' => false],
            ];

            $result = $this->service->saveDeck($character, $cards);

            expect($result)->toBeFalse();
        });

        it('replaces existing deck when saving new one', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            // Create initial deck
            for ($i = 1; $i <= 6; $i++) {
                $supportCard = SupportCardDefinition::factory()->create();
                CharacterSupportCard::factory()->create([
                    'character_id' => $character->id,
                    'support_card_id' => $supportCard->id,
                    'position_slot' => $i,
                ]);
            }

            expect($character->supportCards()->count())->toBe(6);

            // Save new deck
            $newCards = [];
            for ($i = 0; $i < 6; $i++) {
                $supportCard = SupportCardDefinition::factory()->create([
                    'card_type' => ['speed', 'stamina', 'power', 'guts', 'wit', 'friend'][$i],
                ]);
                $newCards[] = [
                    'support_card_id' => $supportCard->id,
                    'is_friend_card' => $i === 5,
                ];
            }

            $result = $this->service->saveDeck($character, $newCards);

            expect($result)->toBeTrue();
            expect($character->supportCards()->count())->toBe(6);
        });
    });

    describe('getRecommendations', function (): void {
        it('returns recommendations for character', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            SupportCardDefinition::factory()->count(5)->create([
                'card_type' => 'speed',
                'meta_tier' => 'S+',
                'is_active' => true,
            ]);

            $result = $this->service->getRecommendations($character);

            expect($result)->not->toBeEmpty();
        });

        it('filters recommendations by focus stat', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            SupportCardDefinition::factory()->count(5)->create([
                'card_type' => 'speed',
                'meta_tier' => 'S+',
                'is_active' => true,
            ]);

            SupportCardDefinition::factory()->count(5)->create([
                'card_type' => 'stamina',
                'meta_tier' => 'S+',
                'is_active' => true,
            ]);

            $result = $this->service->getRecommendations($character, 'speed');

            expect($result->every(fn ($card) => $card->card_type === 'speed'))->toBeTrue();
        });
    });

    describe('calculateDeckTier', function (): void {
        it('returns Unranked for empty deck', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $result = $this->service->calculateDeckTier($character);

            expect($result)->toBe('Unranked');
        });

        it('calculates tier based on card meta tiers', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            // Create deck with S+ cards
            for ($i = 1; $i <= 6; $i++) {
                $supportCard = SupportCardDefinition::factory()->create([
                    'meta_tier' => 'S+',
                ]);
                CharacterSupportCard::factory()->create([
                    'character_id' => $character->id,
                    'support_card_id' => $supportCard->id,
                    'position_slot' => $i,
                ]);
            }

            $result = $this->service->calculateDeckTier($character);

            expect($result)->toBe('S+');
        });
    });
});
