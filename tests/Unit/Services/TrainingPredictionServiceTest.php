<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\SupportCard;
use App\Models\SupportDeck;
use App\Services\Training\SupportBonusCalculator;
use App\Services\TrainingPredictionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->bonusCalculator = new SupportBonusCalculator;
    $this->service = new TrainingPredictionService($this->bonusCalculator);
});

describe('TrainingPredictionService', function () {
    it('gets base predictions without support deck', function () {
        $character = Character::factory()->create([
            'current_stats' => [
                'speed' => 100,
                'stamina' => 100,
                'power' => 100,
                'guts' => 100,
                'wit' => 100,
            ],
            'growth_rates' => [
                'speed' => 1.0,
                'stamina' => 1.0,
                'power' => 1.0,
                'guts' => 1.0,
                'wit' => 1.0,
            ],
        ]);

        $predictions = $this->service->getPredictions($character);

        expect($predictions['has_support_deck'])->toBeFalse()
            ->and($predictions['predictions'])->toHaveKeys(['speed', 'stamina', 'power', 'guts', 'wit']);

        $speedPrediction = $predictions['predictions']['speed'];
        expect($speedPrediction['facility'])->toBe('speed')
            ->and($speedPrediction['base_gains'])->toHaveKey('speed')
            ->and($speedPrediction['support_bonus'])->toBe(0)
            ->and($speedPrediction['is_friendship'])->toBeFalse();
    });

    it('gets predictions with support deck', function () {
        $character = Character::factory()->create([
            'current_stats' => [
                'speed' => 100,
                'stamina' => 100,
                'power' => 100,
                'guts' => 100,
                'wit' => 100,
            ],
        ]);

        $deck = SupportDeck::factory()->create([
            'character_id' => $character->id,
            'is_active' => true,
        ]);

        $card = SupportCard::factory()->create(['rarity' => 'SSR']);
        $deck->supportCards()->attach($card->id, ['position' => 1, 'bond_level' => 50]);

        $predictions = $this->service->getPredictions($character);

        expect($predictions['has_support_deck'])->toBeTrue()
            ->and($predictions['deck_id'])->toBe($deck->id)
            ->and($predictions['predictions'])->toHaveKeys(['speed', 'stamina', 'power', 'guts', 'wit']);

        $speedPrediction = $predictions['predictions']['speed'];
        expect($speedPrediction['support_bonus'])->toBeGreaterThan(0)
            ->and($speedPrediction['final_gains'])->not->toBe($speedPrediction['base_gains']);
    });

    it('gets prediction for specific facility', function () {
        $character = Character::factory()->create([
            'current_stats' => [
                'speed' => 100,
                'stamina' => 100,
                'power' => 100,
                'guts' => 100,
                'wit' => 100,
            ],
        ]);

        $prediction = $this->service->getPredictionForFacility($character, 'speed');

        expect($prediction['facility'])->toBe('speed')
            ->and($prediction['base_gains'])->toHaveKey('speed')
            ->and($prediction['base_gains'])->toHaveKey('power'); // Speed training also gives power
    });

    it('applies growth rates to base gains', function () {
        $character = Character::factory()->create([
            'current_stats' => [
                'speed' => 100,
                'stamina' => 100,
                'power' => 100,
                'guts' => 100,
                'wit' => 100,
            ],
            'growth_rates' => [
                'speed' => 1.5, // 50% bonus
                'stamina' => 1.0,
                'power' => 1.0,
                'guts' => 1.0,
                'wit' => 1.0,
            ],
        ]);

        $prediction = $this->service->getPredictionForFacility($character, 'speed');

        // Base speed gain is 20, with 1.5x growth rate = 30
        expect($prediction['base_gains']['speed'])->toBe(30);
    });

    it('recommends training based on lowest stat', function () {
        $character = Character::factory()->create([
            'current_stats' => [
                'speed' => 100,
                'stamina' => 50, // Lowest
                'power' => 100,
                'guts' => 100,
                'wit' => 100,
            ],
            'goals' => null, // No goals set
        ]);

        $recommendation = $this->service->getRecommendedTraining($character);

        expect($recommendation['recommended_facility'])->toBe('stamina')
            ->and($recommendation['reason'])->toContain('Lowest stat');
    });

    it('recommends training based on stat gaps', function () {
        $character = Character::factory()->create([
            'current_stats' => [
                'speed' => 100,
                'stamina' => 100,
                'power' => 100,
                'guts' => 100,
                'wit' => 100,
            ],
            'goals' => [
                'target_stats' => [
                    'speed' => 300, // Gap of 200
                    'stamina' => 150, // Gap of 50
                ],
            ],
        ]);

        $recommendation = $this->service->getRecommendedTraining($character);

        expect($recommendation['recommended_facility'])->toBe('speed')
            ->and($recommendation['reason'])->toContain('Largest gap');
    });

    it('recommends training when all goals are met', function () {
        $character = Character::factory()->create([
            'current_stats' => [
                'speed' => 300,
                'stamina' => 300,
                'power' => 300,
                'guts' => 300,
                'wit' => 300,
            ],
            'goals' => [
                'target_stats' => [
                    'speed' => 300,
                    'stamina' => 300,
                ],
            ],
        ]);

        $recommendation = $this->service->getRecommendedTraining($character);

        expect($recommendation['reason'])->toContain('All goals met');
    });
});
