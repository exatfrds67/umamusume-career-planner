<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use App\Services\SynergyScorer;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->synergyScorer = new SynergyScorer;
});

describe('SynergyScorer', function () {
    describe('calculateDeckScore', function () {
        it('returns zero score for empty deck', function () {
            $character = Character::factory()->create();

            $result = $this->synergyScorer->calculateDeckScore($character);

            expect($result)->toHaveKeys(['score', 'breakdown', 'recommendations']);
            expect((float) $result['score'])->toBe(0.0);
            expect($result['recommendations'])->toContain('Add support cards to your deck');
        });

        it('calculates score with support cards in deck', function () {
            $character = Character::factory()->create();

            // Create support card definitions
            $speedCard = SupportCardDefinition::factory()->create([
                'card_type' => 'speed',
                'meta_tier' => 'S',
            ]);

            $staminaCard = SupportCardDefinition::factory()->create([
                'card_type' => 'stamina',
                'meta_tier' => 'A',
            ]);

            // Add cards to character's deck
            CharacterSupportCard::factory()->create([
                'character_id' => $character->id,
                'support_card_id' => $speedCard->id,
                'position_slot' => 1,
                'limit_break_level' => 4,
                'friendship_level' => 80,
            ]);

            CharacterSupportCard::factory()->create([
                'character_id' => $character->id,
                'support_card_id' => $staminaCard->id,
                'position_slot' => 2,
                'limit_break_level' => 2,
                'friendship_level' => 50,
            ]);

            $result = $this->synergyScorer->calculateDeckScore($character);

            expect($result['score'])->toBeGreaterThan(0);
            expect($result['breakdown'])->toHaveKeys([
                'meta_quality',
                'type_diversity',
                'stat_alignment',
                'limit_break_level',
                'friendship_bonus',
            ]);
        });

        it('provides recommendations based on scores', function () {
            $character = Character::factory()->create([
                'current_stats' => [
                    'speed' => 200,
                    'stamina' => 200,
                    'power' => 200,
                    'guts' => 200,
                    'wit' => 200,
                ],
            ]);

            // Create low-tier cards
            $lowTierCard = SupportCardDefinition::factory()->create([
                'card_type' => 'speed',
                'meta_tier' => 'C',
            ]);

            CharacterSupportCard::factory()->create([
                'character_id' => $character->id,
                'support_card_id' => $lowTierCard->id,
                'position_slot' => 1,
                'limit_break_level' => 0,
                'friendship_level' => 10,
            ]);

            $result = $this->synergyScorer->calculateDeckScore($character);

            expect($result['recommendations'])->toBeArray();
            expect(count($result['recommendations']))->toBeGreaterThan(0);
        });
    });

    describe('score components', function () {
        it('scores meta quality based on card tiers', function () {
            $character = Character::factory()->create();

            // Create S+ tier card
            $topTierCard = SupportCardDefinition::factory()->create([
                'card_type' => 'speed',
                'meta_tier' => 'S+',
            ]);

            CharacterSupportCard::factory()->create([
                'character_id' => $character->id,
                'support_card_id' => $topTierCard->id,
                'position_slot' => 1,
            ]);

            $result = $this->synergyScorer->calculateDeckScore($character);

            // S+ tier should give 100 meta quality score
            expect($result['breakdown']['meta_quality'])->toBe(100.0);
        });

        it('scores type diversity based on unique card types', function () {
            $character = Character::factory()->create();

            // Create cards of different types
            $types = ['speed', 'stamina', 'power', 'guts', 'wit', 'friend'];
            $position = 1;

            foreach ($types as $type) {
                $card = SupportCardDefinition::factory()->create([
                    'card_type' => $type,
                    'meta_tier' => 'A',
                ]);

                CharacterSupportCard::factory()->create([
                    'character_id' => $character->id,
                    'support_card_id' => $card->id,
                    'position_slot' => $position++,
                ]);
            }

            $result = $this->synergyScorer->calculateDeckScore($character);

            // 6 unique types should give 100% diversity
            expect($result['breakdown']['type_diversity'])->toBe(100.0);
        });

        it('scores limit break levels correctly', function () {
            $character = Character::factory()->create();

            $card = SupportCardDefinition::factory()->create([
                'card_type' => 'speed',
                'meta_tier' => 'S',
            ]);

            // Max limit break (4)
            CharacterSupportCard::factory()->create([
                'character_id' => $character->id,
                'support_card_id' => $card->id,
                'position_slot' => 1,
                'limit_break_level' => 4,
            ]);

            $result = $this->synergyScorer->calculateDeckScore($character);

            // 4/4 limit break should give 100%
            expect($result['breakdown']['limit_break_level'])->toBe(100.0);
        });

        it('scores friendship levels correctly', function () {
            $character = Character::factory()->create();

            $card = SupportCardDefinition::factory()->create([
                'card_type' => 'speed',
                'meta_tier' => 'S',
            ]);

            // Max friendship (100)
            CharacterSupportCard::factory()->create([
                'character_id' => $character->id,
                'support_card_id' => $card->id,
                'position_slot' => 1,
                'friendship_level' => 100,
            ]);

            $result = $this->synergyScorer->calculateDeckScore($character);

            // 100/100 friendship should give 100%
            expect($result['breakdown']['friendship_bonus'])->toBe(100.0);
        });
    });
});
