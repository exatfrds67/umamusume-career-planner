<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\TrainingPrediction;
use App\Models\User;
use App\Services\AI\ABTestingService;
use App\Services\AI\ModelRetrainingService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ─── Prediction Recording Tests ──────────────────────────────

it('records a prediction with all fields', function () {
    $user = User::factory()->create();
    $career = Career::factory()->create(['user_id' => $user->id]);
    $service = new ModelRetrainingService;

    $prediction = $service->recordPrediction(
        careerId: $career->id,
        userId: $user->id,
        turnNumber: 5,
        predictionType: 'speed',
        predictedValue: ['speed' => 800, 'stamina' => 600],
        confidenceScore: 0.85,
        modelVersion: 'v1.0',
    );

    expect($prediction)->toBeInstanceOf(TrainingPrediction::class)
        ->and($prediction->career_id)->toBe($career->id)
        ->and($prediction->user_id)->toBe($user->id)
        ->and($prediction->turn_number)->toBe(5)
        ->and($prediction->prediction_type)->toBe('speed')
        ->and($prediction->predicted_value)->toBe(['speed' => 800, 'stamina' => 600])
        ->and($prediction->confidence_score)->toBe('0.8500')
        ->and($prediction->model_version)->toBe('v1.0');
});

it('records actual result and calculates accuracy', function () {
    $user = User::factory()->create();
    $career = Career::factory()->create(['user_id' => $user->id]);
    $service = new ModelRetrainingService;

    $prediction = $service->recordPrediction(
        careerId: $career->id,
        userId: $user->id,
        turnNumber: 5,
        predictionType: 'speed',
        predictedValue: ['speed' => 800, 'stamina' => 600],
        confidenceScore: 0.85,
    );

    $updated = $service->recordActualResult($prediction, ['speed' => 780, 'stamina' => 620]);

    expect($updated->actual_value)->toBe(['speed' => 780, 'stamina' => 620])
        ->and($updated->accuracy_score)->not->toBeNull()
        ->and((float) $updated->accuracy_score)->toBeGreaterThan(0);
});

it('calculates accuracy score correctly for matching values', function () {
    $service = new ModelRetrainingService;

    $score = $service->calculateAccuracyScore(
        ['speed' => 800, 'stamina' => 600],
        ['speed' => 800, 'stamina' => 600]
    );

    expect($score)->toBe(1.0);
});

it('calculates accuracy score correctly for different values', function () {
    $service = new ModelRetrainingService;

    $score = $service->calculateAccuracyScore(
        ['speed' => 800],
        ['speed' => 400]
    );

    expect($score)->toBeGreaterThan(0)
        ->and($score)->toBeLessThan(1);
});

it('returns zero accuracy for empty arrays', function () {
    $service = new ModelRetrainingService;

    expect($service->calculateAccuracyScore([], []))->toBe(0.0)
        ->and($service->calculateAccuracyScore(['speed' => 800], []))->toBe(0.0);
});

// ─── Accuracy Metric Tests ───────────────────────────────────

it('calculates RMSE for a model version', function () {
    $user = User::factory()->create();
    $career = Career::factory()->create(['user_id' => $user->id]);

    TrainingPrediction::factory()->count(5)->create([
        'career_id' => $career->id,
        'user_id' => $user->id,
        'model_version' => 'v1.0',
        'predicted_value' => ['speed' => 800],
        'actual_value' => ['speed' => 780],
        'accuracy_score' => 0.975,
    ]);

    $service = new ModelRetrainingService;
    $rmse = $service->calculateRMSE('v1.0');

    expect($rmse)->toBeGreaterThan(0)
        ->and($rmse)->toBe(20.0);
});

it('calculates MAE for a model version', function () {
    $user = User::factory()->create();
    $career = Career::factory()->create(['user_id' => $user->id]);

    TrainingPrediction::factory()->count(5)->create([
        'career_id' => $career->id,
        'user_id' => $user->id,
        'model_version' => 'v1.0',
        'predicted_value' => ['speed' => 800],
        'actual_value' => ['speed' => 780],
        'accuracy_score' => 0.975,
    ]);

    $service = new ModelRetrainingService;
    $mae = $service->calculateMAE('v1.0');

    expect($mae)->toBe(20.0);
});

it('calculates MAPE for a model version', function () {
    $user = User::factory()->create();
    $career = Career::factory()->create(['user_id' => $user->id]);

    TrainingPrediction::factory()->count(3)->create([
        'career_id' => $career->id,
        'user_id' => $user->id,
        'model_version' => 'v1.0',
        'predicted_value' => ['speed' => 800],
        'actual_value' => ['speed' => 780],
        'accuracy_score' => 0.975,
    ]);

    $service = new ModelRetrainingService;
    $mape = $service->calculateMAPE('v1.0');

    expect($mape)->toBeGreaterThan(0);
});

it('returns zero metrics when no predictions exist', function () {
    $service = new ModelRetrainingService;

    expect($service->calculateRMSE('nonexistent'))->toBe(0.0)
        ->and($service->calculateMAE('nonexistent'))->toBe(0.0)
        ->and($service->calculateMAPE('nonexistent'))->toBe(0.0);
});

it('gets accuracy by prediction type', function () {
    $user = User::factory()->create();
    $career = Career::factory()->create(['user_id' => $user->id]);

    TrainingPrediction::factory()->count(3)->create([
        'career_id' => $career->id,
        'user_id' => $user->id,
        'model_version' => 'v1.0',
        'prediction_type' => 'speed',
        'predicted_value' => ['speed' => 800],
        'actual_value' => ['speed' => 780],
        'accuracy_score' => 0.95,
    ]);

    TrainingPrediction::factory()->count(2)->create([
        'career_id' => $career->id,
        'user_id' => $user->id,
        'model_version' => 'v1.0',
        'prediction_type' => 'stamina',
        'predicted_value' => ['stamina' => 600],
        'actual_value' => ['stamina' => 550],
        'accuracy_score' => 0.85,
    ]);

    $service = new ModelRetrainingService;
    $metrics = $service->getAccuracyByType('v1.0');

    expect($metrics)->toHaveKeys(['speed', 'stamina'])
        ->and($metrics['speed']['count'])->toBe(3)
        ->and($metrics['stamina']['count'])->toBe(2);
});

it('triggers retraining at threshold', function () {
    $service = new ModelRetrainingService;

    expect($service->shouldRetrain('v1.0'))->toBeFalse();
});

it('tracks active model version', function () {
    $service = new ModelRetrainingService;

    $service->setActiveModelVersion('v2.0');
    expect($service->getActiveModelVersion())->toBe('v2.0');

    $service->setActiveModelVersion('v1.0');
    expect($service->getActiveModelVersion())->toBe('v1.0');
});

// ─── A/B Testing Service Tests ───────────────────────────────

it('starts an A/B test', function () {
    $service = new ABTestingService;

    $test = $service->startTest('v1.0', 'v2.0');

    expect($test)->toHaveKeys(['test_id', 'variant_a', 'variant_b', 'status'])
        ->and($test['variant_a'])->toBe('v1.0')
        ->and($test['variant_b'])->toBe('v2.0')
        ->and($test['status'])->toBe('running');
});

it('routes traffic to either variant', function () {
    $service = new ABTestingService;
    $service->startTest('v1.0', 'v2.0');

    $testId = 'v1.0_vs_v2.0';
    $results = [];

    for ($i = 0; $i < 100; $i++) {
        $variant = $service->routeTraffic($testId);
        $results[$variant] = ($results[$variant] ?? 0) + 1;
    }

    expect($results)->toHaveKey('v1.0')
        ->and($results)->toHaveKey('v2.0');
});

it('evaluates test with insufficient samples', function () {
    $service = new ABTestingService;
    $service->startTest('v1.0', 'v2.0');

    $result = $service->evaluateTest('v1.0_vs_v2.0');

    expect($result['winner'])->toBeNull()
        ->and($result['evaluation'])->toContain('Insufficient');
});

it('stops a test and applies winner', function () {
    $service = new ABTestingService;
    $service->startTest('v1.0', 'v2.0');

    $stopped = $service->stopTest('v1.0_vs_v2.0', 'v2.0');

    expect($stopped)->toBeTrue();

    $testData = $service->getTest('v1.0_vs_v2.0');
    expect($testData['status'])->toBe('completed')
        ->and($testData['applied_version'])->toBe('v2.0');
});

it('checks if test is running', function () {
    $service = new ABTestingService;
    $service->startTest('v1.0', 'v2.0');

    expect($service->isTestRunning('v1.0_vs_v2.0'))->toBeTrue();

    $service->stopTest('v1.0_vs_v2.0');
    expect($service->isTestRunning('v1.0_vs_v2.0'))->toBeFalse();
});

it('returns false when stopping non-existent test', function () {
    $service = new ABTestingService;
    expect($service->stopTest('nonexistent'))->toBeFalse();
});

it('evaluates non-existent test gracefully', function () {
    $service = new ABTestingService;
    $result = $service->evaluateTest('nonexistent');

    expect($result['winner'])->toBeNull()
        ->and($result['evaluation'])->toBe('Test not found');
});

// ─── TrainingPrediction Model Tests ──────────────────────────

it('detects predictions with actual results', function () {
    $prediction = TrainingPrediction::factory()->create();
    expect($prediction->hasActualResult())->toBeFalse();

    $withResult = TrainingPrediction::factory()->withActualResult()->create();
    expect($withResult->hasActualResult())->toBeTrue();
});

it('creates A/B test predictions via factory', function () {
    $prediction = TrainingPrediction::factory()->abTest('B')->create();

    expect($prediction->is_ab_test)->toBeTrue()
        ->and($prediction->ab_variant)->toBe('B');
});
