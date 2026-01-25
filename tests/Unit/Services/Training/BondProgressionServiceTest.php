<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\SupportCard;
use App\Models\SupportDeck;
use App\Services\Training\BondProgressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new BondProgressionService;
});

describe('BondProgressionService', function () {
    it('updates bond levels for participating cards', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create([
            'character_id' => $character->id,
            'is_active' => true,
        ]);

        $card1 = SupportCard::factory()->create();
        $card2 = SupportCard::factory()->create();
        $card3 = SupportCard::factory()->create();

        $deck->supportCards()->attach($card1->id, ['position' => 1, 'bond_level' => 50]);
        $deck->supportCards()->attach($card2->id, ['position' => 2, 'bond_level' => 20]);
        $deck->supportCards()->attach($card3->id, ['position' => 3, 'bond_level' => 90]);

        $participatingCards = [$card1->id, $card2->id];

        $result = $this->service->updateBondLevels($deck, $participatingCards);

        expect($result['updated_cards'])->toHaveCount(2)
            ->and($result['total_updated'])->toBe(2);

        // Check card1 (bond 50 -> 55)
        $card1Update = collect($result['updated_cards'])->firstWhere('card_id', $card1->id);
        expect($card1Update['bond_before'])->toBe(50)
            ->and($card1Update['bond_after'])->toBe(55)
            ->and($card1Update['bond_gain'])->toBe(5);

        // Check card2 (bond 20 -> 27, increased gain for low bond)
        $card2Update = collect($result['updated_cards'])->firstWhere('card_id', $card2->id);
        expect($card2Update['bond_before'])->toBe(20)
            ->and($card2Update['bond_after'])->toBe(27)
            ->and($card2Update['bond_gain'])->toBe(7);

        // Verify database updates
        $deck->refresh();
        expect($deck->supportCards->find($card1->id)->pivot->bond_level)->toBe(55);
        expect($deck->supportCards->find($card2->id)->pivot->bond_level)->toBe(27);
        expect($deck->supportCards->find($card3->id)->pivot->bond_level)->toBe(90); // Unchanged
    });

    it('caps bond level at 100', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create([
            'character_id' => $character->id,
            'is_active' => true,
        ]);

        $card = SupportCard::factory()->create();
        $deck->supportCards()->attach($card->id, ['position' => 1, 'bond_level' => 98]);

        $result = $this->service->updateBondLevels($deck, [$card->id]);

        $cardUpdate = $result['updated_cards'][0];
        expect($cardUpdate['bond_before'])->toBe(98)
            ->and($cardUpdate['bond_after'])->toBe(100) // Capped at 100
            ->and($cardUpdate['bond_gain'])->toBeGreaterThan(0);

        $deck->refresh();
        expect($deck->supportCards->find($card->id)->pivot->bond_level)->toBe(100);
    });

    it('handles empty participating cards list', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create([
            'character_id' => $character->id,
            'is_active' => true,
        ]);

        $card = SupportCard::factory()->create();
        $deck->supportCards()->attach($card->id, ['position' => 1, 'bond_level' => 50]);

        // Empty list means all cards participate
        $result = $this->service->updateBondLevels($deck, []);

        expect($result['updated_cards'])->toHaveCount(1)
            ->and($result['total_updated'])->toBe(1);
    });

    it('sets bond level for a specific card', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create([
            'character_id' => $character->id,
            'is_active' => true,
        ]);

        $card = SupportCard::factory()->create();
        $deck->supportCards()->attach($card->id, ['position' => 1, 'bond_level' => 50]);

        $success = $this->service->setBondLevel($deck, $card->id, 75);

        expect($success)->toBeTrue();

        $deck->refresh();
        expect($deck->supportCards->find($card->id)->pivot->bond_level)->toBe(75);
    });

    it('gets bond level for a specific card', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create([
            'character_id' => $character->id,
            'is_active' => true,
        ]);

        $card = SupportCard::factory()->create();
        $deck->supportCards()->attach($card->id, ['position' => 1, 'bond_level' => 65]);

        $bondLevel = $this->service->getBondLevel($deck, $card->id);

        expect($bondLevel)->toBe(65);
    });

    it('gets bond summary for deck', function () {
        $character = Character::factory()->create();
        $deck = SupportDeck::factory()->create([
            'character_id' => $character->id,
            'is_active' => true,
        ]);

        $card1 = SupportCard::factory()->create();
        $card2 = SupportCard::factory()->create();
        $card3 = SupportCard::factory()->create();

        $deck->supportCards()->attach($card1->id, ['position' => 1, 'bond_level' => 85]);
        $deck->supportCards()->attach($card2->id, ['position' => 2, 'bond_level' => 75]);
        $deck->supportCards()->attach($card3->id, ['position' => 3, 'bond_level' => 90]);

        $summary = $this->service->getBondSummary($deck);

        expect($summary['total_cards'])->toBe(3)
            ->and($summary['average_bond'])->toBe(83.3) // (85 + 75 + 90) / 3
            ->and($summary['friendship_cards'])->toBe(2) // card1 and card3 are at 80+
            ->and($summary['has_friendship_training'])->toBeFalse(); // Need 3+ cards at 80+
    });
});
