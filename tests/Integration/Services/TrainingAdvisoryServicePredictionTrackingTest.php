<?php

declare(strict_types=1);

use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Models\PredictionAccuracy;
use App\Services\CriticalSituationDetector;
use App\Services\GameMechanicsEngine;
use App\Services\Neuron\NeuronAIService;
use App\Services\PredictionAccuracyTracker;
use App\Services\RuleBasedAdvisor;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\RaceResult;
use App\ValueObjects\RaceStrategy;
use App\ValueObjects\Recommendation;
use App\ValueObjects\TrainingOutcome;

describe('TrainingAdvisoryService - Prediction Tracking Integration', function () {
    beforeEach(function () {
        // Set default database connection to sqlite for this test
        config(['database.default' => 'sqlite']);

        // Run migrations to create tables
        $this->artisan('migrate', ['--database' => 'sqlite']);

        // Disable foreign keys to simplify test setup
        \Illuminate\Support\Facades\DB::connection('sqlite')->statement('PRAGMA foreign_keys = OFF');

        // Use factories to create required records with all necessary fields
        $user = \App\Models\User::factory()->create(['id' => 1]);
        $character = \App\Models\Character::factory()->create(['id' => 1, 'user_id' => 1]);
        $career = \App\Models\Career::factory()->create([
            'id' => 1,
            'user_id' => 1,
            'character_id' => 1,
        ]);

        // Keep foreign keys disabled for the test
        // This allows us to insert prediction records without all the complex relationships

        // Create service with real dependencies
        $this->neuronAIService = Mockery::mock(NeuronAIService::class);
        $this->ruleBasedAdvisor = Mockery::mock(RuleBasedAdvisor::class);
        $this->mechanicsEngine = Mockery::mock(GameMechanicsEngine::class);
        $this->accuracyTracker = new PredictionAccuracyTracker;
        $this->criticalDetector = Mockery::mock(CriticalSituationDetector::class);
        $this->recommendationCache = Mockery::mock(\App\Services\RecommendationCacheService::class);
        $this->performanceMonitor = Mockery::mock(\App\Services\AdvisoryPerformanceMonitor::class);

        $this->service = new TrainingAdvisoryService(
            $this->neuronAIService,
            $this->ruleBasedAdvisor,
            $this->mechanicsEngine,
            $this->accuracyTracker,
            $this->criticalDetector,
            $this->recommendationCache,
            $this->performanceMonitor
        );
    });

    describe('recordTrainingOutcome', function () {
        it('successfully records training outcome with valid data', function () {
            // Arrange
            $careerId = 1;
            $turnNumber = 15;

            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Speed Training',
                reasoning: 'Test reasoning',
                expectedOutcomes: [
                    'stat_gains' => [
                        'speed' => 50,
                        'stamina' => 10,
                    ],
                ],
                risks: [],
                confidenceScore: 0.9,
                source: 'ai'
            );

            $actual = new TrainingOutcome(
                turnNumber: $turnNumber,
                facility: 'speed',
                statGains: [
                    'speed' => 48,
                    'stamina' => 12,
                ],
                bondIncreases: [],
                skillHints: [],
                wasFailure: false
            );

            // Act - call the tracker directly to see if it works
            $result = $this->accuracyTracker->recordTrainingOutcome(
                $careerId,
                $turnNumber,
                $recommendation,
                $actual,
                'ai-v1'
            );

            // Assert that the tracker returned a model
            expect($result)->toBeInstanceOf(PredictionAccuracy::class);
            expect($result->id)->not->toBeNull();

            // Assert database has the record
            $this->assertDatabaseHas('ucp_prediction_accuracy', [
                'career_id' => $careerId,
                'turn_number' => $turnNumber,
                'prediction_type' => RecommendationType::TRAINING_FACILITY->value,
                'model_version' => 'ai-v1',
            ]);

            $prediction = PredictionAccuracy::where('career_id', $careerId)
                ->where('turn_number', $turnNumber)
                ->first();

            expect($prediction)->not->toBeNull();
            expect($prediction->accuracy_score)->toBeGreaterThan(0.0);
            expect($prediction->accuracy_score)->toBeLessThanOrEqual(1.0);
        });

        it('handles tracker errors gracefully without throwing', function () {
            // Arrange
            $careerId = 1;
            $turnNumber = 15;

            // Mock the tracker to throw an exception
            $mockTracker = Mockery::mock(PredictionAccuracyTracker::class);
            $mockTracker->shouldReceive('recordTrainingOutcome')
                ->andThrow(new \RuntimeException('Database error'));

            $service = new TrainingAdvisoryService(
                $this->neuronAIService,
                $this->ruleBasedAdvisor,
                $this->mechanicsEngine,
                $mockTracker,
                $this->criticalDetector,
                Mockery::mock(\App\Services\RecommendationCacheService::class),
                Mockery::mock(\App\Services\AdvisoryPerformanceMonitor::class)
            );

            $recommendation = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Speed Training',
                reasoning: 'Test reasoning',
                expectedOutcomes: ['stat_gains' => ['speed' => 50]],
                risks: [],
                confidenceScore: 0.9,
                source: 'ai'
            );

            $actual = new TrainingOutcome(
                turnNumber: $turnNumber,
                facility: 'speed',
                statGains: ['speed' => 48],
                bondIncreases: [],
                skillHints: [],
                wasFailure: false
            );

            // Act - should not throw exception
            expect(fn () => $service->recordTrainingOutcome($careerId, $turnNumber, $recommendation, $actual))
                ->not->toThrow(\Exception::class);
        });

        it('records multiple training outcomes for the same career', function () {
            // Arrange
            $careerId = 1;

            // Record first outcome
            $recommendation1 = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Speed Training',
                reasoning: 'Test reasoning',
                expectedOutcomes: ['stat_gains' => ['speed' => 50]],
                risks: [],
                confidenceScore: 0.9,
                source: 'ai'
            );

            $actual1 = new TrainingOutcome(
                turnNumber: 15,
                facility: 'speed',
                statGains: ['speed' => 48],
                bondIncreases: [],
                skillHints: [],
                wasFailure: false
            );

            $this->service->recordTrainingOutcome($careerId, 15, $recommendation1, $actual1);

            // Record second outcome
            $recommendation2 = new Recommendation(
                type: RecommendationType::TRAINING_FACILITY,
                priority: Priority::HIGH,
                action: 'Stamina Training',
                reasoning: 'Test reasoning',
                expectedOutcomes: ['stat_gains' => ['stamina' => 60]],
                risks: [],
                confidenceScore: 0.85,
                source: 'rule-based'
            );

            $actual2 = new TrainingOutcome(
                turnNumber: 16,
                facility: 'stamina',
                statGains: ['stamina' => 55],
                bondIncreases: [],
                skillHints: [],
                wasFailure: false
            );

            $this->service->recordTrainingOutcome($careerId, 16, $recommendation2, $actual2);

            // Assert
            $this->assertDatabaseCount('ucp_prediction_accuracy', 2);

            $predictions = PredictionAccuracy::where('career_id', $careerId)->get();
            expect($predictions)->toHaveCount(2);
            expect($predictions->pluck('turn_number')->toArray())->toBe([15, 16]);
        });
    });

    describe('recordRaceOutcome', function () {
        it('successfully records race outcome with valid data', function () {
            // Arrange
            $careerId = 1;
            $raceId = 5;

            $strategy = new RaceStrategy(
                raceId: $raceId,
                recommendedStyle: 'escape',
                reasoning: 'Test reasoning',
                winProbability: 0.75,
                readinessAssessment: ['stamina' => 'sufficient'],
                risks: [],
                preparationChecklist: [],
                predictedOutcomes: [],
                modelVersion: 'test-model-v1'
            );

            $actual = new RaceResult(
                raceId: $raceId,
                placement: 1,
                totalCompetitors: 18,
                runningStyle: 'escape',
                wasWin: true,
                wasPlaced: true
            );

            // Act
            $this->service->recordRaceOutcome($careerId, $strategy, $actual);

            // Assert
            $this->assertDatabaseHas('ucp_prediction_accuracy', [
                'career_id' => $careerId,
                'prediction_type' => RecommendationType::RACE_STRATEGY->value,
                'model_version' => 'test-model-v1',
            ]);

            $prediction = PredictionAccuracy::where('career_id', $careerId)
                ->where('prediction_type', RecommendationType::RACE_STRATEGY->value)
                ->first();

            expect($prediction)->not->toBeNull();
            expect($prediction->accuracy_score)->toBeGreaterThan(0.0);
            expect($prediction->accuracy_score)->toBeLessThanOrEqual(1.0);
        });

        it('handles tracker errors gracefully without throwing', function () {
            // Arrange
            $careerId = 1;
            $raceId = 5;

            // Mock the tracker to throw an exception
            $mockTracker = Mockery::mock(PredictionAccuracyTracker::class);
            $mockTracker->shouldReceive('recordRaceOutcome')
                ->andThrow(new \RuntimeException('Database error'));

            $service = new TrainingAdvisoryService(
                $this->neuronAIService,
                $this->ruleBasedAdvisor,
                $this->mechanicsEngine,
                $mockTracker,
                $this->criticalDetector,
                Mockery::mock(\App\Services\RecommendationCacheService::class),
                Mockery::mock(\App\Services\AdvisoryPerformanceMonitor::class)
            );

            $strategy = new RaceStrategy(
                raceId: $raceId,
                recommendedStyle: 'escape',
                reasoning: 'Test reasoning',
                winProbability: 0.75,
                readinessAssessment: ['stamina' => 'sufficient'],
                risks: [],
                preparationChecklist: [],
                predictedOutcomes: [],
                modelVersion: 'test-model-v1'
            );

            $actual = new RaceResult(
                raceId: $raceId,
                placement: 1,
                totalCompetitors: 18,
                runningStyle: 'escape',
                wasWin: true,
                wasPlaced: true
            );

            // Act - should not throw exception
            expect(fn () => $service->recordRaceOutcome($careerId, $strategy, $actual))
                ->not->toThrow(\Exception::class);
        });

        it('records multiple race outcomes for the same career', function () {
            // Arrange
            $careerId = 1;

            // Record first race outcome
            $strategy1 = new RaceStrategy(
                raceId: 5,
                recommendedStyle: 'escape',
                reasoning: 'Test reasoning',
                winProbability: 0.75,
                readinessAssessment: ['stamina' => 'sufficient'],
                risks: [],
                preparationChecklist: [],
                predictedOutcomes: [],
                modelVersion: 'test-model-v1'
            );

            $actual1 = new RaceResult(
                raceId: 5,
                placement: 1,
                totalCompetitors: 18,
                runningStyle: 'escape',
                wasWin: true,
                wasPlaced: true
            );

            $this->service->recordRaceOutcome($careerId, $strategy1, $actual1);

            // Record second race outcome
            $strategy2 = new RaceStrategy(
                raceId: 6,
                recommendedStyle: 'lead',
                reasoning: 'Test reasoning',
                winProbability: 0.60,
                readinessAssessment: ['stamina' => 'sufficient'],
                risks: [],
                preparationChecklist: [],
                predictedOutcomes: [],
                modelVersion: 'test-model-v1'
            );

            $actual2 = new RaceResult(
                raceId: 6,
                placement: 3,
                totalCompetitors: 18,
                runningStyle: 'lead',
                wasWin: false,
                wasPlaced: true
            );

            $this->service->recordRaceOutcome($careerId, $strategy2, $actual2);

            // Assert
            $this->assertDatabaseCount('ucp_prediction_accuracy', 2);

            $predictions = PredictionAccuracy::where('career_id', $careerId)
                ->where('prediction_type', RecommendationType::RACE_STRATEGY->value)
                ->get();

            expect($predictions)->toHaveCount(2);
        });
    });
});
