<?php

declare(strict_types=1);

/**
 * Training Prediction Tests
 *
 * Tests for the TrainingPredictionService including:
 * - Base predictions without support deck
 * - Predictions with support deck bonuses
 * - Training recommendations
 */

use App\Models\Character;
use App\Models\SupportCardDefinition;
use App\Models\SupportDeck;
use App\Models\User;
use App\Services\TrainingPredictionService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ],
        'energy_level' => 100,
        'mood_status' => 'normal',
    ]);
    $this->service = app(TrainingPredictionService::class);
});

describe('Training Predictions Without Support Deck', function () {
    it('returns base predictions when no support deck is active', function () {
        $predictions = $this->service->getPredictions($this->character);

        expect($predictions)->toHaveKey('has_support_deck')
            ->and($predictions['has_support_deck'])->toBeFalse()
            ->and($predictions)->toHaveKey('predictions')
            ->and($predictions['predictions'])->toHaveKeys(['speed', 'stamina', 'power', 'guts', 'wit']);
    });

    it('returns prediction for specific facility without support deck', function () {
        $prediction = $this->service->getPredictionForFacility($this->character, 'speed');

        expect($prediction)->toHaveKeys(['facility', 'base_gains', 'final_gains', 'support_bonus'])
            ->and($prediction['facility'])->toBe('speed')
            ->and($prediction['base_gains'])->toHaveKey('speed')
            ->and($prediction['base_gains']['speed'])->toBeGreaterThan(0);
    });

    it('returns zero support bonus without deck', function () {
        $prediction = $this->service->getPredictionForFacility($this->character, 'speed');

        expect($prediction['support_bonus'])->toBe(0)
            ->and($prediction['is_friendship'])->toBeFalse()
            ->and($prediction['active_cards'])->toBeEmpty();
    });
});

describe('Training Predictions With Support Deck', function () {
    beforeEach(function () {
        // Create support cards and deck
        $this->supportCards = SupportCardDefinition::factory()->count(6)->create([
            'card_type' => 'speed',
            'rarity' => 'SSR',
        ]);

        $this->deck = SupportDeck::factory()->create([
            'character_id' => $this->character->id,
            'is_active' => true,
        ]);
    });

    it('returns predictions with support deck flag', function () {
        $predictions = $this->service->getPredictions($this->character->fresh());

        expect($predictions)->toHaveKey('predictions')
            ->and($predictions['predictions'])->toHaveKeys(['speed', 'stamina', 'power', 'guts', 'wit']);
    });

    it('returns prediction for specific facility with deck', function () {
        $prediction = $this->service->getPredictionForFacility($this->character->fresh(), 'speed');

        expect($prediction)->toHaveKeys(['facility', 'base_gains', 'final_gains']);
    });
});

describe('Training Recommendation', function () {
    it('returns recommended training based on character goals', function () {
        $recommendation = $this->service->getRecommendedTraining($this->character);

        expect($recommendation)->toHaveKeys(['recommended_facility', 'reason', 'prediction'])
            ->and($recommendation['recommended_facility'])->toBeIn(['speed', 'stamina', 'power', 'guts', 'wit']);
    });

    it('recommends based on lowest stat when no goals set', function () {
        $this->character->update([
            'current_stats' => [
                'speed' => 100,
                'stamina' => 50, // Lowest stat
                'power' => 100,
                'guts' => 100,
                'wit' => 100,
            ],
            'goals' => [],
        ]);

        $recommendation = $this->service->getRecommendedTraining($this->character->fresh());

        expect($recommendation['recommended_facility'])->toBe('stamina')
            ->and($recommendation['reason'])->toContain('Lowest stat');
    });

    it('recommends based on stat gaps when goals are set', function () {
        $this->character->update([
            'current_stats' => [
                'speed' => 100,
                'stamina' => 100,
                'power' => 100,
                'guts' => 100,
                'wit' => 100,
            ],
            'goals' => [
                'target_stats' => [
                    'speed' => 200, // 100 gap
                    'stamina' => 150, // 50 gap
                ],
            ],
        ]);

        $recommendation = $this->service->getRecommendedTraining($this->character->fresh());

        expect($recommendation['recommended_facility'])->toBe('speed')
            ->and($recommendation['reason'])->toContain('Largest gap');
    });

    it('returns all goals met message when targets achieved', function () {
        $this->character->update([
            'current_stats' => [
                'speed' => 200,
                'stamina' => 200,
                'power' => 200,
                'guts' => 200,
                'wit' => 200,
            ],
            'goals' => [
                'target_stats' => [
                    'speed' => 100,
                    'stamina' => 100,
                ],
            ],
        ]);

        $recommendation = $this->service->getRecommendedTraining($this->character->fresh());

        expect($recommendation['reason'])->toBe('All goals met');
    });
});

describe('Base Gains Calculation', function () {
    it('calculates correct base gains for each facility', function () {
        $facilities = ['speed', 'stamina', 'power', 'guts', 'wit'];

        foreach ($facilities as $facility) {
            $prediction = $this->service->getPredictionForFacility($this->character, $facility);

            expect($prediction['base_gains'])->toHaveKey($facility)
                ->and($prediction['base_gains'][$facility])->toBeGreaterThan(0);
        }
    });

    it('applies growth rate multiplier to primary stat', function () {
        $characterWithGrowth = Character::factory()->create([
            'user_id' => $this->user->id,
            'growth_rates' => ['speed' => 2.0], // 2x multiplier
        ]);

        $characterWithoutGrowth = Character::factory()->create([
            'user_id' => $this->user->id,
            'growth_rates' => ['speed' => 1.0], // 1x multiplier
        ]);

        $predictionWithGrowth = $this->service->getPredictionForFacility($characterWithGrowth, 'speed');
        $predictionWithoutGrowth = $this->service->getPredictionForFacility($characterWithoutGrowth, 'speed');

        expect($predictionWithGrowth['base_gains']['speed'])
            ->toBeGreaterThan($predictionWithoutGrowth['base_gains']['speed']);
    });
});
