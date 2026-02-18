<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\PredictionAccuracy;

use function Pest\Laravel\assertDatabaseHas;

describe('PredictionAccuracy Model', function () {
    it('can be created with valid attributes', function () {
        $career = Career::factory()->create();

        $prediction = PredictionAccuracy::create([
            'career_id' => $career->id,
            'turn_number' => 15,
            'prediction_type' => 'training_gain',
            'predicted_value' => ['speed' => 50, 'stamina' => 45],
            'actual_value' => ['speed' => 48, 'stamina' => 47],
            'accuracy_score' => 0.92,
            'model_version' => 'ollama-v1.0',
        ]);

        expect($prediction)->toBeInstanceOf(PredictionAccuracy::class)
            ->and($prediction->career_id)->toBe($career->id)
            ->and($prediction->turn_number)->toBe(15)
            ->and($prediction->prediction_type)->toBe('training_gain')
            ->and($prediction->accuracy_score)->toBe(0.92)
            ->and($prediction->model_version)->toBe('ollama-v1.0');

        assertDatabaseHas('ucp_prediction_accuracy', [
            'career_id' => $career->id,
            'turn_number' => 15,
            'prediction_type' => 'training_gain',
        ]);
    });

    it('belongs to a career', function () {
        $career = Career::factory()->create();
        $prediction = PredictionAccuracy::factory()->forCareer($career)->create();

        expect($prediction->career)->toBeInstanceOf(Career::class)
            ->and($prediction->career->id)->toBe($career->id);
    });

    it('casts predicted_value to array', function () {
        $prediction = PredictionAccuracy::factory()->create([
            'predicted_value' => ['speed' => 50, 'stamina' => 45],
        ]);

        expect($prediction->predicted_value)->toBeArray()
            ->and($prediction->predicted_value)->toHaveKey('speed')
            ->and($prediction->predicted_value['speed'])->toBe(50);
    });

    it('casts actual_value to array', function () {
        $prediction = PredictionAccuracy::factory()->create([
            'actual_value' => ['speed' => 48, 'stamina' => 47],
        ]);

        expect($prediction->actual_value)->toBeArray()
            ->and($prediction->actual_value)->toHaveKey('speed')
            ->and($prediction->actual_value['speed'])->toBe(48);
    });

    it('casts accuracy_score to float', function () {
        $prediction = PredictionAccuracy::factory()->create([
            'accuracy_score' => 0.92,
        ]);

        expect($prediction->accuracy_score)->toBeFloat()
            ->and($prediction->accuracy_score)->toBe(0.92);
    });

    it('casts created_at to datetime', function () {
        $prediction = PredictionAccuracy::factory()->create();

        expect($prediction->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    });
});

describe('PredictionAccuracy Scopes', function () {
    it('can scope by career', function () {
        $career1 = Career::factory()->create();
        $career2 = Career::factory()->create();

        PredictionAccuracy::factory()->forCareer($career1)->count(3)->create();
        PredictionAccuracy::factory()->forCareer($career2)->count(2)->create();

        $predictions = PredictionAccuracy::forCareer($career1->id)->get();

        expect($predictions)->toHaveCount(3)
            ->and($predictions->every(fn ($p) => $p->career_id === $career1->id))->toBeTrue();
    });

    it('can scope by turn', function () {
        PredictionAccuracy::factory()->forTurn(10)->count(2)->create();
        PredictionAccuracy::factory()->forTurn(15)->count(3)->create();

        $predictions = PredictionAccuracy::forTurn(15)->get();

        expect($predictions)->toHaveCount(3)
            ->and($predictions->every(fn ($p) => $p->turn_number === 15))->toBeTrue();
    });

    it('can scope by prediction type', function () {
        PredictionAccuracy::factory()->trainingGain()->count(2)->create();
        PredictionAccuracy::factory()->racePlacement()->count(3)->create();

        $predictions = PredictionAccuracy::ofType('race_placement')->get();

        expect($predictions)->toHaveCount(3)
            ->and($predictions->every(fn ($p) => $p->prediction_type === 'race_placement'))->toBeTrue();
    });

    it('can scope by model version', function () {
        PredictionAccuracy::factory()->modelVersion('ollama-v1.0')->count(2)->create();
        PredictionAccuracy::factory()->modelVersion('bedrock-claude-v3')->count(3)->create();

        $predictions = PredictionAccuracy::forModelVersion('ollama-v1.0')->get();

        expect($predictions)->toHaveCount(2)
            ->and($predictions->every(fn ($p) => $p->model_version === 'ollama-v1.0'))->toBeTrue();
    });

    it('can scope by accuracy above threshold', function () {
        PredictionAccuracy::factory()->create(['accuracy_score' => 0.95]);
        PredictionAccuracy::factory()->create(['accuracy_score' => 0.85]);
        PredictionAccuracy::factory()->create(['accuracy_score' => 0.75]);
        PredictionAccuracy::factory()->create(['accuracy_score' => 0.65]);

        $predictions = PredictionAccuracy::accurateAbove(0.8)->get();

        expect($predictions)->toHaveCount(2)
            ->and($predictions->every(fn ($p) => $p->accuracy_score >= 0.8))->toBeTrue();
    });

    it('can scope by accuracy below threshold', function () {
        PredictionAccuracy::factory()->create(['accuracy_score' => 0.95]);
        PredictionAccuracy::factory()->create(['accuracy_score' => 0.85]);
        PredictionAccuracy::factory()->create(['accuracy_score' => 0.75]);
        PredictionAccuracy::factory()->create(['accuracy_score' => 0.65]);

        $predictions = PredictionAccuracy::accurateBelow(0.8)->get();

        expect($predictions)->toHaveCount(2)
            ->and($predictions->every(fn ($p) => $p->accuracy_score < 0.8))->toBeTrue();
    });

    it('can order by accuracy score', function () {
        PredictionAccuracy::factory()->create(['accuracy_score' => 0.75]);
        PredictionAccuracy::factory()->create(['accuracy_score' => 0.95]);
        PredictionAccuracy::factory()->create(['accuracy_score' => 0.85]);

        $predictions = PredictionAccuracy::orderByAccuracy('desc')->get();

        expect($predictions->first()->accuracy_score)->toBe(0.95)
            ->and($predictions->last()->accuracy_score)->toBe(0.75);
    });

    it('can order by turn number', function () {
        PredictionAccuracy::factory()->forTurn(20)->create();
        PredictionAccuracy::factory()->forTurn(10)->create();
        PredictionAccuracy::factory()->forTurn(15)->create();

        $predictions = PredictionAccuracy::orderByTurn('asc')->get();

        expect($predictions->first()->turn_number)->toBe(10)
            ->and($predictions->last()->turn_number)->toBe(20);
    });

    it('can order by creation date', function () {
        $old = PredictionAccuracy::factory()->create(['created_at' => now()->subDays(2)]);
        $new = PredictionAccuracy::factory()->create(['created_at' => now()]);
        $middle = PredictionAccuracy::factory()->create(['created_at' => now()->subDay()]);

        $predictions = PredictionAccuracy::orderByDate('desc')->get();

        expect($predictions->first()->id)->toBe($new->id)
            ->and($predictions->last()->id)->toBe($old->id);
    });
});

describe('PredictionAccuracy Helper Methods', function () {
    it('can check if prediction is accurate', function () {
        $accurate = PredictionAccuracy::factory()->create(['accuracy_score' => 0.85]);
        $inaccurate = PredictionAccuracy::factory()->create(['accuracy_score' => 0.75]);

        expect($accurate->isAccurate())->toBeTrue()
            ->and($inaccurate->isAccurate())->toBeFalse();
    });

    it('can check if prediction is highly accurate', function () {
        $highlyAccurate = PredictionAccuracy::factory()->create(['accuracy_score' => 0.95]);
        $accurate = PredictionAccuracy::factory()->create(['accuracy_score' => 0.85]);

        expect($highlyAccurate->isHighlyAccurate())->toBeTrue()
            ->and($accurate->isHighlyAccurate())->toBeFalse();
    });

    it('can check if prediction is inaccurate', function () {
        $inaccurate = PredictionAccuracy::factory()->create(['accuracy_score' => 0.55]);
        $accurate = PredictionAccuracy::factory()->create(['accuracy_score' => 0.85]);

        expect($inaccurate->isInaccurate())->toBeTrue()
            ->and($accurate->isInaccurate())->toBeFalse();
    });

    it('can get accuracy as percentage', function () {
        $prediction = PredictionAccuracy::factory()->create(['accuracy_score' => 0.8542]);

        expect($prediction->getAccuracyPercentage())->toBe('85.4%');
    });

    it('can get accuracy level', function () {
        $excellent = PredictionAccuracy::factory()->create(['accuracy_score' => 0.96]);
        $veryGood = PredictionAccuracy::factory()->create(['accuracy_score' => 0.88]);
        $good = PredictionAccuracy::factory()->create(['accuracy_score' => 0.78]);
        $fair = PredictionAccuracy::factory()->create(['accuracy_score' => 0.68]);
        $poor = PredictionAccuracy::factory()->create(['accuracy_score' => 0.55]);
        $veryPoor = PredictionAccuracy::factory()->create(['accuracy_score' => 0.35]);

        expect($excellent->getAccuracyLevel())->toBe('Excellent')
            ->and($veryGood->getAccuracyLevel())->toBe('Very Good')
            ->and($good->getAccuracyLevel())->toBe('Good')
            ->and($fair->getAccuracyLevel())->toBe('Fair')
            ->and($poor->getAccuracyLevel())->toBe('Poor')
            ->and($veryPoor->getAccuracyLevel())->toBe('Very Poor');
    });

    it('can get summary', function () {
        $prediction = PredictionAccuracy::factory()->create([
            'prediction_type' => 'training_gain',
            'turn_number' => 15,
            'accuracy_score' => 0.88,
        ]);

        $summary = $prediction->getSummary();

        expect($summary)->toContain('training_gain')
            ->and($summary)->toContain('Turn 15')
            ->and($summary)->toContain('88.0%')
            ->and($summary)->toContain('Very Good');
    });

    it('can calculate differences between predicted and actual values', function () {
        $prediction = PredictionAccuracy::factory()->create([
            'predicted_value' => ['speed' => 50, 'stamina' => 40],
            'actual_value' => ['speed' => 48, 'stamina' => 42],
        ]);

        $differences = $prediction->getDifferences();

        expect($differences)->toHaveKey('speed')
            ->and($differences['speed']['predicted'])->toBe(50)
            ->and($differences['speed']['actual'])->toBe(48)
            ->and($differences['speed']['difference'])->toBe(-2)
            ->and($differences['speed']['percentage_error'])->toBe(4.0);
    });

    it('can calculate average percentage error', function () {
        $prediction = PredictionAccuracy::factory()->create([
            'predicted_value' => ['speed' => 50, 'stamina' => 40],
            'actual_value' => ['speed' => 48, 'stamina' => 42],
        ]);

        $avgError = $prediction->getAveragePercentageError();

        // (4% + 5%) / 2 = 4.5%
        expect($avgError)->toBeGreaterThan(4.0)
            ->and($avgError)->toBeLessThan(5.0);
    });

    it('handles non-numeric values in differences calculation', function () {
        $prediction = PredictionAccuracy::factory()->create([
            'predicted_value' => ['speed' => 50, 'placement' => 'first'],
            'actual_value' => ['speed' => 48, 'placement' => 'second'],
        ]);

        $differences = $prediction->getDifferences();

        expect($differences)->toHaveKey('speed')
            ->and($differences)->not->toHaveKey('placement');
    });

    it('handles missing keys in actual value', function () {
        $prediction = PredictionAccuracy::factory()->create([
            'predicted_value' => ['speed' => 50, 'stamina' => 40],
            'actual_value' => ['speed' => 48],
        ]);

        $differences = $prediction->getDifferences();

        expect($differences)->toHaveKey('speed')
            ->and($differences)->not->toHaveKey('stamina');
    });
});

describe('PredictionAccuracy Factory States', function () {
    it('can create highly accurate predictions', function () {
        $prediction = PredictionAccuracy::factory()->highlyAccurate()->create();

        expect($prediction->accuracy_score)->toBeGreaterThanOrEqual(0.9)
            ->and($prediction->accuracy_score)->toBeLessThanOrEqual(1.0);
    });

    it('can create accurate predictions', function () {
        $prediction = PredictionAccuracy::factory()->accurate()->create();

        expect($prediction->accuracy_score)->toBeGreaterThanOrEqual(0.8)
            ->and($prediction->accuracy_score)->toBeLessThan(0.9);
    });

    it('can create inaccurate predictions', function () {
        $prediction = PredictionAccuracy::factory()->inaccurate()->create();

        expect($prediction->accuracy_score)->toBeGreaterThanOrEqual(0.3)
            ->and($prediction->accuracy_score)->toBeLessThan(0.6);
    });

    it('can create training gain predictions', function () {
        $prediction = PredictionAccuracy::factory()->trainingGain()->create();

        expect($prediction->prediction_type)->toBe('training_gain')
            ->and($prediction->predicted_value)->toBeArray()
            ->and($prediction->actual_value)->toBeArray();
    });

    it('can create skill hint predictions', function () {
        $prediction = PredictionAccuracy::factory()->skillHint()->create();

        expect($prediction->prediction_type)->toBe('skill_hint')
            ->and($prediction->predicted_value)->toHaveKey('skill_id')
            ->and($prediction->predicted_value)->toHaveKey('hint_level');
    });

    it('can create race placement predictions', function () {
        $prediction = PredictionAccuracy::factory()->racePlacement()->create();

        expect($prediction->prediction_type)->toBe('race_placement')
            ->and($prediction->predicted_value)->toHaveKey('placement')
            ->and($prediction->predicted_value)->toHaveKey('win_probability');
    });

    it('can create bond increase predictions', function () {
        $prediction = PredictionAccuracy::factory()->bondIncrease()->create();

        expect($prediction->prediction_type)->toBe('bond_increase')
            ->and($prediction->predicted_value)->toBeArray();
    });
});
