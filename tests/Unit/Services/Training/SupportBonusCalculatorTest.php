<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\SupportCard;
use App\Models\SupportDeck;
use App\Services\Training\SupportBonusCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->calculator = new SupportBonusCalculator;
});

describe('SupportBonusCalculator', function () {
    it('calculates bonuses for empty deck', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create(['character_id' => $character->id]);

        $bonuses = $this->calculator->calculateBonuses($deck, 'speed');

        expect($bonuses['base_bonus'])->toBe(0)
            ->and($bonuses['final_bonus'])->toBe(0)
            ->and($bonuses['is_friendship'])->toBeFalse()
            ->and($bonuses['active_cards'])->toBeEmpty();
    });

    it('calculates bonuses for SSR card', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create(['character_id' => $character->id]);
        $card = SupportCard::factory()->create(['rarity' => 'SSR', 'limit_break' => 0]);

        $deck->supportCards()->attach($card->id, ['position' => 1, 'bond_level' => 50]);

        $bonuses = $this->calculator->calculateBonuses($deck, 'speed');

        expect($bonuses['base_bonus'])->toBe(10.0) // SSR base bonus
            ->and($bonuses['is_friendship'])->toBeFalse() // Need 3+ cards at 80+ bond
            ->and($bonuses['active_cards'])->toHaveCount(1);
    });

    it('calculates bonuses for SR card', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create(['character_id' => $character->id]);
        $card = SupportCard::factory()->create(['rarity' => 'SR', 'limit_break' => 0]);

        $deck->supportCards()->attach($card->id, ['position' => 1, 'bond_level' => 50]);

        $bonuses = $this->calculator->calculateBonuses($deck, 'speed');

        expect($bonuses['base_bonus'])->toBe(7.0); // SR base bonus
    });

    it('calculates bonuses for R card', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create(['character_id' => $character->id]);
        $card = SupportCard::factory()->create(['rarity' => 'R', 'limit_break' => 0]);

        $deck->supportCards()->attach($card->id, ['position' => 1, 'bond_level' => 50]);

        $bonuses = $this->calculator->calculateBonuses($deck, 'speed');

        expect($bonuses['base_bonus'])->toBe(5.0); // R base bonus
    });

    it('applies limit break multiplier', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create(['character_id' => $character->id]);
        $card = SupportCard::factory()->create(['rarity' => 'SSR', 'limit_break' => 4]);

        $deck->supportCards()->attach($card->id, ['position' => 1, 'bond_level' => 50]);

        $bonuses = $this->calculator->calculateBonuses($deck, 'speed');

        expect($bonuses['base_bonus'])->toBe(14.0); // 10% + (4 × 10%) = 14%
    });

    it('applies friendship multiplier with 3+ cards at 80+ bond', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create(['character_id' => $character->id]);

        for ($i = 1; $i <= 3; $i++) {
            $card = SupportCard::factory()->create(['rarity' => 'SSR', 'limit_break' => 0]);
            $deck->supportCards()->attach($card->id, ['position' => $i, 'bond_level' => 85]);
        }

        $bonuses = $this->calculator->calculateBonuses($deck, 'speed');

        expect($bonuses['base_bonus'])->toBe(30.0) // 3 SSR cards × 10%
            ->and($bonuses['friendship_multiplier'])->toBe(1.2)
            ->and($bonuses['final_bonus'])->toBe(36.0) // 30 × 1.2
            ->and($bonuses['is_friendship'])->toBeTrue()
            ->and($bonuses['friendship_card_count'])->toBe(3);
    });

    it('does not apply friendship multiplier with less than 3 cards at 80+ bond', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create(['character_id' => $character->id]);

        for ($i = 1; $i <= 2; $i++) {
            $card = SupportCard::factory()->create(['rarity' => 'SSR', 'limit_break' => 0]);
            $deck->supportCards()->attach($card->id, ['position' => $i, 'bond_level' => 85]);
        }

        $bonuses = $this->calculator->calculateBonuses($deck, 'speed');

        expect($bonuses['is_friendship'])->toBeFalse()
            ->and($bonuses['friendship_multiplier'])->toBe(1.0)
            ->and($bonuses['final_bonus'])->toBe(20.0); // 2 × 10%, no friendship bonus
    });

    it('applies bonuses to stat gains', function () {
        $baseGains = [
            'speed' => 20,
            'power' => 5,
        ];

        $bonuses = [
            'final_bonus' => 20.0, // 20% bonus
            'friendship_multiplier' => 1.0,
        ];

        $finalGains = $this->calculator->applyBonusesToGains($baseGains, $bonuses);

        expect($finalGains['speed'])->toBe(24) // 20 × 1.2
            ->and($finalGains['power'])->toBe(6); // 5 × 1.2
    });

    it('applies bonuses to multiple stats', function () {
        $baseGains = [
            'speed' => 20,
            'stamina' => 15,
            'power' => 10,
            'guts' => 5,
            'wit' => 8,
        ];

        $bonuses = [
            'final_bonus' => 30.0, // 30% bonus
            'friendship_multiplier' => 1.0,
        ];

        $finalGains = $this->calculator->applyBonusesToGains($baseGains, $bonuses);

        expect($finalGains['speed'])->toBe(26) // 20 × 1.3
            ->and($finalGains['stamina'])->toBe(20) // 15 × 1.3 = 19.5, rounded to 20
            ->and($finalGains['power'])->toBe(13) // 10 × 1.3
            ->and($finalGains['guts'])->toBe(7) // 5 × 1.3 = 6.5, rounded to 7
            ->and($finalGains['wit'])->toBe(10); // 8 × 1.3 = 10.4, rounded to 10
    });
});
