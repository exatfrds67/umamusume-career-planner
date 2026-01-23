<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

describe('Support Card API Endpoints', function (): void {
    describe('GET /api/v1/support-cards', function (): void {
        it('returns list of support card definitions', function (): void {
            SupportCardDefinition::factory()->count(10)->create(['is_active' => true]);

            $response = $this->getJson('/api/v1/support-cards');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'name', 'card_type', 'rarity'],
                    ],
                ]);
        });

        it('filters by card type', function (): void {
            SupportCardDefinition::factory()->count(5)->create([
                'card_type' => 'speed',
                'is_active' => true,
            ]);
            SupportCardDefinition::factory()->count(3)->create([
                'card_type' => 'stamina',
                'is_active' => true,
            ]);

            $response = $this->getJson('/api/v1/support-cards?card_type=speed');

            $response->assertSuccessful()
                ->assertJsonCount(5, 'data');
        });

        it('filters by rarity', function (): void {
            SupportCardDefinition::factory()->count(4)->create([
                'rarity' => 'SSR',
                'is_active' => true,
            ]);
            SupportCardDefinition::factory()->count(6)->create([
                'rarity' => 'SR',
                'is_active' => true,
            ]);

            $response = $this->getJson('/api/v1/support-cards?rarity=SSR');

            $response->assertSuccessful()
                ->assertJsonCount(4, 'data');
        });

        it('filters by meta tier', function (): void {
            SupportCardDefinition::factory()->count(3)->create([
                'meta_tier' => 'S+',
                'is_active' => true,
            ]);
            SupportCardDefinition::factory()->count(5)->create([
                'meta_tier' => 'A',
                'is_active' => true,
            ]);

            $response = $this->getJson('/api/v1/support-cards?meta_tier='.urlencode('S+'));

            $response->assertSuccessful()
                ->assertJsonCount(3, 'data');
        });

        it('searches by name', function (): void {
            SupportCardDefinition::factory()->create([
                'name' => 'Speed Star Card',
                'is_active' => true,
            ]);
            SupportCardDefinition::factory()->create([
                'name' => 'Stamina Boost Card',
                'is_active' => true,
            ]);

            $response = $this->getJson('/api/v1/support-cards?search=Speed');

            $response->assertSuccessful()
                ->assertJsonCount(1, 'data');
        });
    });

    describe('GET /api/v1/support-cards/{id}', function (): void {
        it('returns support card details', function (): void {
            $card = SupportCardDefinition::factory()->create();

            $response = $this->getJson("/api/v1/support-cards/{$card->id}");

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'card_type',
                        'rarity',
                        'meta_tier',
                        'skill_hints_provided',
                    ],
                ]);
        });

        it('returns 404 for non-existent card', function (): void {
            $response = $this->getJson('/api/v1/support-cards/99999');

            $response->assertNotFound();
        });
    });

    describe('GET /api/v1/support-cards/meta-ranking', function (): void {
        it('returns cards ranked by meta tier', function (): void {
            SupportCardDefinition::factory()->count(3)->create([
                'meta_tier' => 'S+',
                'is_active' => true,
            ]);
            SupportCardDefinition::factory()->count(5)->create([
                'meta_tier' => 'S',
                'is_active' => true,
            ]);

            $response = $this->getJson('/api/v1/support-cards/meta-ranking');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        'S+',
                        'S',
                    ],
                ]);
        });
    });

    describe('GET /api/v1/support-cards/{id}/synergies', function (): void {
        it('returns synergy data for card', function (): void {
            $card = SupportCardDefinition::factory()->create();

            $response = $this->getJson("/api/v1/support-cards/{$card->id}/synergies");

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        'synergy_cards',
                        'synergy_score',
                    ],
                ]);
        });
    });

    describe('Character Support Card Deck', function (): void {
        describe('GET /api/v1/characters/{id}/deck', function (): void {
            it('returns character deck', function (): void {
                $character = Character::factory()->create(['user_id' => $this->user->id]);

                for ($i = 1; $i <= 6; $i++) {
                    $card = SupportCardDefinition::factory()->create();
                    CharacterSupportCard::factory()->create([
                        'character_id' => $character->id,
                        'support_card_id' => $card->id,
                        'position_slot' => $i,
                    ]);
                }

                $response = $this->getJson("/api/v1/characters/{$character->id}/deck");

                $response->assertSuccessful()
                    ->assertJsonCount(6, 'data');
            });
        });

        describe('POST /api/v1/characters/{id}/deck', function (): void {
            it('adds card to deck', function (): void {
                $character = Character::factory()->create(['user_id' => $this->user->id]);
                $card = SupportCardDefinition::factory()->create();

                $response = $this->postJson("/api/v1/characters/{$character->id}/deck", [
                    'support_card_id' => $card->id,
                    'position_slot' => 1,
                ]);

                $response->assertCreated();

                expect(CharacterSupportCard::where('character_id', $character->id)
                    ->where('support_card_id', $card->id)
                    ->exists())->toBeTrue();
            });

            it('validates position slot range', function (): void {
                $character = Character::factory()->create(['user_id' => $this->user->id]);
                $card = SupportCardDefinition::factory()->create();

                $response = $this->postJson("/api/v1/characters/{$character->id}/deck", [
                    'support_card_id' => $card->id,
                    'position_slot' => 7,
                ]);

                $response->assertUnprocessable()
                    ->assertJsonValidationErrors(['position_slot']);
            });
        });

        describe('DELETE /api/v1/characters/{id}/deck/{slot}', function (): void {
            it('removes card from deck slot', function (): void {
                $character = Character::factory()->create(['user_id' => $this->user->id]);
                $card = SupportCardDefinition::factory()->create();

                CharacterSupportCard::factory()->create([
                    'character_id' => $character->id,
                    'support_card_id' => $card->id,
                    'position_slot' => 1,
                ]);

                $response = $this->deleteJson("/api/v1/characters/{$character->id}/deck/1");

                $response->assertSuccessful();

                expect(CharacterSupportCard::where('character_id', $character->id)
                    ->where('position_slot', 1)
                    ->exists())->toBeFalse();
            });
        });
    });
});
