<?php

declare(strict_types=1);

use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Models\Career;
use App\Models\PredictionAccuracy;
use App\Services\PredictionAccuracyTracker;
use App\ValueObjects\AccuracyMetrics;
use App\ValueObjects\RaceResult;
use App\ValueObjects\RaceStrategy;
use App\ValueObjects\Recommendation;
use App\ValueObjects\TrainingOutcome;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PredictionAccuracyTracker', function () {
    beforeEach(function () {
        $this->tracker = app(PredictionAccuracyTracker::class);
        $this->career = Career::factory()->create();
    });

    describe('recordTrainingOutcome', function () {
        it('records training outcome with accurate prediction', function () {
            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Speed Training',
                reasoning: 'Test reasoning',
                expectedOutcomes: [
                    'stat_gains' => [
                        'speed' => 50,
                        'power' => 10,
                    ],
                ],
                risks: [],
            );

            $actual = new TrainingOutcome(
                turnNumber: 15,
                facility: 'speed',
                statGains: [
                    'speed' => 48,
                    'power' => 11,
                ],
            );

            $result = $this->tracker->recordTrainingOutcome(
                $this->career->id,
                15,
                $recommendation,
                $actual,
                'test-model-v1'
            );

            expect($result)->toBeInstanceOf(PredictionAccuracy::class)
                ->and($result->career_id)->toBe($this->career->id)
                ->and($result->turn_number)->toBe(15)
                ->and($result->prediction_type)->toBe(RecommendationType::TRAINING_FACILITY->value)
                ->and($result->accuracy_score)->toBeGreaterThan(0.9)
                ->and($result->model_version)->toBe('test-model-v1');
        });

        it('records training outcome with inaccurate prediction', function () {
            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Speed Training',
                reasoning: 'Test reasoning',
                expectedOutcomes: [
                    'stat_gains' => [
                        'speed' => 50,
                        'power' => 10,
                    ],
                ],
                risks: [],
            );

            $actual = new TrainingOutcome(
                turnNumber: 15,
                facility: 'speed',
                statGains: [
                    'speed' => 25, // 50% off
                    'power' => 5,  // 50% off
                ],
            );

            $result = $this->tracker->recordTrainingOutcome(
                $this->career->id,
                15,
                $recommendation,
                $actual,
                'test-model-v1'
            );

            expect($result->accuracy_score)->toBeLessThan(0.6);
        });

        it('handles empty predicted outcomes', function () {
            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Speed Training',
                reasoning: 'Test reasoning',
                expectedOutcomes: [],
                risks: [],
            );

            $actual = new TrainingOutcome(
                turnNumber: 15,
                facility: 'speed',
                statGains: ['speed' => 50],
            );

            $result = $this->tracker->recordTrainingOutcome(
                $this->career->id,
                15,
                $recommendation,
                $actual,
                'test-model-v1'
            );

            expect($result->accuracy_score)->toBe(0.0);
        });
    });

    describe('recordRaceOutcome', function () {
        it('records race outcome with accurate win prediction', function () {
            $strategy = new RaceStrategy(
                raceId: 1,
                recommendedStyle: 'escape',
                reasoning: 'Test reasoning',
                winProbability: 0.8,
                readinessAssessment: ['overall' => 'ready'],
                modelVersion: 'test-model-v1',
            );

            $actual = new RaceResult(
                raceId: 1,
                placement: 1,
                totalCompetitors: 18,
                runningStyle: 'escape',
                wasWin: true,
                wasPlaced: true,
            );

            $result = $this->tracker->recordRaceOutcome(
                $this->career->id,
                $strategy,
                $actual
            );

            expect($result)->toBeInstanceOf(PredictionAccuracy::class)
                ->and($result->career_id)->toBe($this->career->id)
                ->and($result->prediction_type)->toBe(RecommendationType::RACE_STRATEGY->value)
                ->and($result->accuracy_score)->toBe(1.0)
                ->and($result->model_version)->toBe('test-model-v1');
        });

        it('records race outcome with inaccurate prediction', function () {
            $strategy = new RaceStrategy(
                raceId: 1,
                recommendedStyle: 'escape',
                reasoning: 'Test reasoning',
                winProbability: 0.8,
                readinessAssessment: ['overall' => 'ready'],
                modelVersion: 'test-model-v1',
            );

            $actual = new RaceResult(
                raceId: 1,
                placement: 10,
                totalCompetitors: 18,
                runningStyle: 'escape',
                wasWin: false,
                wasPlaced: false,
            );

            $result = $this->tracker->recordRaceOutcome(
                $this->career->id,
                $strategy,
                $actual
            );

            expect($result->accuracy_score)->toBe(0.0);
        });

        it('handles medium probability predictions correctly', function () {
            $strategy = new RaceStrategy(
                raceId: 1,
                recommendedStyle: 'escape',
                reasoning: 'Test reasoning',
                winProbability: 0.5,
                readinessAssessment: ['overall' => 'ready'],
                modelVersion: 'test-model-v1',
            );

            $actual = new RaceResult(
                raceId: 1,
                placement: 3,
                totalCompetitors: 18,
                runningStyle: 'escape',
                wasWin: false,
                wasPlaced: true,
            );

            $result = $this->tracker->recordRaceOutcome(
                $this->career->id,
                $strategy,
                $actual
            );

            expect($result->accuracy_score)->toBe(0.8);
        });
    });

    describe('getPredictionAccuracy', function () {
        it('returns empty metrics when no predictions exist', function () {
            $metrics = $this->tracker->getPredictionAccuracy($this->career->id);

            expect($metrics)->toBeInstanceOf(AccuracyMetrics::class)
                ->and($metrics->totalPredictions)->toBe(0)
                ->and($metrics->hasPredictions())->toBeFalse();
        });

        it('calculates metrics for all predictions', function () {
            // Create multiple predictions
            PredictionAccuracy::factory()->forCareer($this->career)->create([
                'accuracy_score' => 0.95,
                'prediction_type' => RecommendationType::TRAINING_FACILITY->value,
            ]);
            PredictionAccuracy::factory()->forCareer($this->career)->create([
                'accuracy_score' => 0.85,
                'prediction_type' => RecommendationType::TRAINING_FACILITY->value,
            ]);
            PredictionAccuracy::factory()->forCareer($this->career)->create([
                'accuracy_score' => 0.75,
                'prediction_type' => RecommendationType::RACE_STRATEGY->value,
            ]);

            $metrics = $this->tracker->getPredictionAccuracy($this->career->id);

            expect($metrics->totalPredictions)->toBe(3)
                ->and($metrics->averageAccuracy)->toBeGreaterThan(0.8)
                ->and($metrics->minAccuracy)->toBe(0.75)
                ->and($metrics->maxAccuracy)->toBe(0.95)
                ->and($metrics->accuratePredictions)->toBe(2) // Only 0.95 and 0.85 are >= 0.8
                ->and($metrics->inaccuratePredictions)->toBe(0); // None are < 0.6
        });

        it('filters predictions by type', function () {
            PredictionAccuracy::factory()->forCareer($this->career)->create([
                'prediction_type' => RecommendationType::TRAINING_FACILITY->value,
            ]);
            PredictionAccuracy::factory()->forCareer($this->career)->create([
                'prediction_type' => RecommendationType::RACE_STRATEGY->value,
            ]);

            $metrics = $this->tracker->getPredictionAccuracy(
                $this->career->id,
                RecommendationType::TRAINING_FACILITY
            );

            expect($metrics->totalPredictions)->toBe(1);
        });

        it('flags for improvement when accuracy is low', function () {
            // Create predictions with low accuracy
            for ($i = 0; $i < 5; $i++) {
                PredictionAccuracy::factory()->forCareer($this->career)->create([
                    'accuracy_score' => 0.6,
                ]);
            }

            $metrics = $this->tracker->getPredictionAccuracy($this->career->id);

            expect($metrics->needsImprovement)->toBeTrue()
                ->and($metrics->improvementReason)->toContain('below threshold');
        });

        it('does not flag for improvement with insufficient predictions', function () {
            // Create only 3 predictions (below minimum of 5)
            for ($i = 0; $i < 3; $i++) {
                PredictionAccuracy::factory()->forCareer($this->career)->create([
                    'accuracy_score' => 0.6,
                ]);
            }

            $metrics = $this->tracker->getPredictionAccuracy($this->career->id);

            expect($metrics->needsImprovement)->toBeFalse();
        });
    });

    describe('flagModelForReview', function () {
        it('returns false when insufficient predictions', function () {
            PredictionAccuracy::factory()->count(3)->create([
                'model_version' => 'test-model-v1',
                'accuracy_score' => 0.5,
            ]);

            $shouldFlag = $this->tracker->flagModelForReview('test-model-v1');

            expect($shouldFlag)->toBeFalse();
        });

        it('returns true when accuracy is below threshold', function () {
            PredictionAccuracy::factory()->count(10)->create([
                'model_version' => 'test-model-v1',
                'accuracy_score' => 0.6,
            ]);

            $shouldFlag = $this->tracker->flagModelForReview('test-model-v1');

            expect($shouldFlag)->toBeTrue();
        });

        it('returns false when accuracy is above threshold', function () {
            PredictionAccuracy::factory()->count(10)->create([
                'model_version' => 'test-model-v1',
                'accuracy_score' => 0.9,
            ]);

            $shouldFlag = $this->tracker->flagModelForReview('test-model-v1');

            expect($shouldFlag)->toBeFalse();
        });

        it('accepts custom accuracy threshold', function () {
            PredictionAccuracy::factory()->count(10)->create([
                'model_version' => 'test-model-v1',
                'accuracy_score' => 0.8,
            ]);

            // Should not flag with default threshold (0.75)
            expect($this->tracker->flagModelForReview('test-model-v1'))->toBeFalse();

            // Should flag with custom threshold (0.85)
            expect($this->tracker->flagModelForReview('test-model-v1', 0.85))->toBeTrue();
        });
    });

    describe('getModelAccuracy', function () {
        it('returns empty metrics when no predictions exist', function () {
            $metrics = $this->tracker->getModelAccuracy('nonexistent-model');

            expect($metrics)->toBeInstanceOf(AccuracyMetrics::class)
                ->and($metrics->totalPredictions)->toBe(0);
        });

        it('calculates metrics for specific model version', function () {
            PredictionAccuracy::factory()->count(5)->create([
                'model_version' => 'test-model-v1',
                'accuracy_score' => 0.9,
            ]);
            PredictionAccuracy::factory()->count(3)->create([
                'model_version' => 'test-model-v2',
                'accuracy_score' => 0.5,
            ]);

            $metrics = $this->tracker->getModelAccuracy('test-model-v1');

            expect($metrics->totalPredictions)->toBe(5)
                ->and($metrics->averageAccuracy)->toBe(0.9)
                ->and($metrics->needsImprovement)->toBeFalse();
        });
    });

    describe('getRecentAccuracy', function () {
        it('returns empty metrics when no recent predictions', function () {
            PredictionAccuracy::factory()->create([
                'created_at' => now()->subDays(10),
            ]);

            $metrics = $this->tracker->getRecentAccuracy(7);

            expect($metrics->totalPredictions)->toBe(0);
        });

        it('calculates metrics for recent predictions', function () {
            PredictionAccuracy::factory()->count(3)->create([
                'created_at' => now()->subDays(2),
                'accuracy_score' => 0.9,
            ]);
            PredictionAccuracy::factory()->count(2)->create([
                'created_at' => now()->subDays(10),
                'accuracy_score' => 0.5,
            ]);

            $metrics = $this->tracker->getRecentAccuracy(7);

            expect($metrics->totalPredictions)->toBe(3)
                ->and($metrics->averageAccuracy)->toBe(0.9);
        });

        it('filters by model version', function () {
            PredictionAccuracy::factory()->count(3)->create([
                'created_at' => now()->subDays(2),
                'model_version' => 'test-model-v1',
            ]);
            PredictionAccuracy::factory()->count(2)->create([
                'created_at' => now()->subDays(2),
                'model_version' => 'test-model-v2',
            ]);

            $metrics = $this->tracker->getRecentAccuracy(7, 'test-model-v1');

            expect($metrics->totalPredictions)->toBe(3);
        });
    });
});
