<?php

declare(strict_types=1);

use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Models\PredictionAccuracy;
use App\Services\GameMechanicsEngine;
use App\Services\PredictionAccuracyTracker;
use App\Services\RuleBasedAdvisor;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\Recommendation;
use App\ValueObjects\TrainingOutcome;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Property 15: Prediction Accuracy Recording
 *
 * Validates: Requirements 3.8
 *
 * Predicted outcomes must be recorded and compared with actual outcomes.
 */
describe('Property 15: Prediction Accuracy Recording', function () {
    beforeEach(function () {
        // Mock the dependencies to avoid AWS credential issues
        $this->mock(\App\Services\Neuron\NeuronAIService::class);
        $this->mock(RuleBasedAdvisor::class);
        $this->mock(GameMechanicsEngine::class);
        $this->mock(\App\Services\CriticalSituationDetector::class);

        // Use real PredictionAccuracyTracker for testing
        $this->accuracyTracker = app(PredictionAccuracyTracker::class);
        $this->advisoryService = app(TrainingAdvisoryService::class);
    });

    it('records training outcomes and calculates accuracy', function () {
        // Create a career run first
        $career = \App\Models\Career::factory()->create();
        $careerId = $career->id;
        $turnNumber = 15;

        // Create a recommendation
        $recommendation = new Recommendation(
            type: RecommendationType::TRAINING_FACILITY,
            priority: Priority::HIGH,
            action: 'Speed Training',
            reasoning: 'Test recommendation',
            expectedOutcomes: [
                'stat_gains' => [
                    'speed' => 45,
                    'stamina' => 10,
                    'power' => 15,
                ],
            ],
            risks: [],
            confidenceScore: 0.90
        );

        // Create actual outcome
        $actualOutcome = new TrainingOutcome(
            turnNumber: $turnNumber,
            facility: 'speed',
            statGains: [
                'speed' => 42,
                'stamina' => 12,
                'power' => 14,
            ],
            bondIncreases: [],
            skillHints: [],
            wasFailure: false,
            wasInjury: false,
            energyChange: -20
        );

        // Record the outcome
        $this->accuracyTracker->recordTrainingOutcome(
            $careerId,
            $turnNumber,
            $recommendation,
            $actualOutcome,
            'test-model-v1'
        );

        // Verify prediction was recorded
        $prediction = PredictionAccuracy::where('career_id', $careerId)
            ->where('turn_number', $turnNumber)
            ->first();

        expect($prediction)->not->toBeNull();
        expect($prediction->prediction_type)->toBe(RecommendationType::TRAINING_FACILITY->value);
        expect($prediction->accuracy_score)->toBeGreaterThan(0.0);
        expect($prediction->accuracy_score)->toBeLessThanOrEqual(1.0);
    })->group('property', 'prediction-accuracy');

    it('retrieves accuracy metrics for a career run', function () {
        // Create a career run first
        $career = \App\Models\Career::factory()->create();
        $careerId = $career->id;

        // Record multiple predictions
        for ($turn = 1; $turn <= 5; $turn++) {
            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Speed Training',
                reasoning: 'Test recommendation',
                expectedOutcomes: [
                    'stat_gains' => [
                        'speed' => 45,
                        'stamina' => 10,
                    ],
                ],
                risks: [],
                confidenceScore: 0.90
            );

            $actualOutcome = new TrainingOutcome(
                turnNumber: $turn,
                facility: 'speed',
                statGains: [
                    'speed' => 43,
                    'stamina' => 11,
                ],
                bondIncreases: [],
                skillHints: [],
                wasFailure: false,
                wasInjury: false,
                energyChange: -20
            );

            $this->accuracyTracker->recordTrainingOutcome(
                $careerId,
                $turn,
                $recommendation,
                $actualOutcome,
                'test-model-v1'
            );
        }

        // Get accuracy metrics
        $metrics = $this->advisoryService->getPredictionAccuracy(
            $careerId,
            RecommendationType::TRAINING_FACILITY
        );

        expect($metrics->totalPredictions)->toBe(5);
        expect($metrics->averageAccuracy)->toBeGreaterThan(0.0);
        expect($metrics->averageAccuracy)->toBeLessThanOrEqual(1.0);
        expect($metrics->hasPredictions())->toBeTrue();
    })->group('property', 'prediction-accuracy');

    it('tracks accuracy by recommendation type', function () {
        // Create a career run first
        $career = \App\Models\Career::factory()->create();
        $careerId = $career->id;

        // Record training facility predictions
        for ($turn = 1; $turn <= 3; $turn++) {
            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Speed Training',
                reasoning: 'Test',
                expectedOutcomes: [
                    'stat_gains' => ['speed' => 45],
                ],
                risks: [],
                confidenceScore: 0.90
            );

            $actualOutcome = new TrainingOutcome(
                turnNumber: $turn,
                facility: 'speed',
                statGains: ['speed' => 43],
                bondIncreases: [],
                skillHints: [],
                wasFailure: false,
                wasInjury: false,
                energyChange: -20
            );

            $this->accuracyTracker->recordTrainingOutcome(
                $careerId,
                $turn,
                $recommendation,
                $actualOutcome,
                'test-model-v1'
            );
        }

        // Get metrics for training facility type
        $metrics = $this->advisoryService->getPredictionAccuracy(
            $careerId,
            RecommendationType::TRAINING_FACILITY
        );

        expect($metrics->totalPredictions)->toBe(3);
        expect($metrics->accuracyByType)->toHaveKey(RecommendationType::TRAINING_FACILITY->value);
    })->group('property', 'prediction-accuracy');

    it('returns empty metrics when no predictions exist', function () {
        $careerId = 999; // Non-existent career

        $metrics = $this->advisoryService->getPredictionAccuracy(
            $careerId,
            RecommendationType::TRAINING_FACILITY
        );

        expect($metrics->totalPredictions)->toBe(0);
        expect($metrics->hasPredictions())->toBeFalse();
    })->group('property', 'prediction-accuracy');

    it('calculates accuracy correctly for perfect predictions', function () {
        // Create a career run first
        $career = \App\Models\Career::factory()->create();
        $careerId = $career->id;
        $turnNumber = 1;

        // Create a recommendation with exact predictions
        $recommendation = new Recommendation(
            type: RecommendationType::TRAINING_FACILITY,
            priority: Priority::HIGH,
            action: 'Speed Training',
            reasoning: 'Test',
            expectedOutcomes: [
                'stat_gains' => [
                    'speed' => 45,
                    'stamina' => 10,
                ],
            ],
            risks: [],
            confidenceScore: 0.90
        );

        // Create actual outcome matching predictions exactly
        $actualOutcome = new TrainingOutcome(
            turnNumber: $turnNumber,
            facility: 'speed',
            statGains: [
                'speed' => 45,
                'stamina' => 10,
            ],
            bondIncreases: [],
            skillHints: [],
            wasFailure: false,
            wasInjury: false,
            energyChange: -20
        );

        // Record the outcome
        $this->accuracyTracker->recordTrainingOutcome(
            $careerId,
            $turnNumber,
            $recommendation,
            $actualOutcome,
            'test-model-v1'
        );

        // Get the prediction
        $prediction = PredictionAccuracy::where('career_id', $careerId)
            ->where('turn_number', $turnNumber)
            ->first();

        // Perfect prediction should have accuracy of 1.0
        expect($prediction->accuracy_score)->toBe(1.0);
    })->group('property', 'prediction-accuracy');
});
