<?php

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\CareerAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;


beforeEach(function () {
    $this->service = new CareerAnalyticsService;
    $this->user = User::factory()->create();
    Cache::flush();
});

// =========================================================================
// CAREER PERFORMANCE METRICS TESTS
// =========================================================================

describe('Career Performance Metrics', function () {
    it('returns empty result when character has no careers', function () {
        $character = Character::factory()->for($this->user)->create();

        $result = $this->service->calculateCareerPerformanceMetrics($character);

        expect($result)
            ->overall_efficiency->toBe(0.0)
            ->success_rate->toBe(0.0)
            ->completion_rate->toBe(0.0)
            ->average_final_grade->toBe('N/A')
            ->total_careers->toBe(0)
            ->completed_careers->toBe(0);
    });

    it('calculates completion rate correctly', function () {
        $character = Character::factory()->for($this->user)->create();

        // Create 4 careers: 2 completed, 2 active
        Career::factory()->count(2)->for($character)->for($this->user)->completed()->create();
        Career::factory()->count(2)->for($character)->for($this->user)->create(['completed_at' => null]);

        $result = $this->service->calculateCareerPerformanceMetrics($character);

        expect($result)
            ->total_careers->toBe(4)
            ->completed_careers->toBe(2)
            ->completion_rate->toBe(50.0);
    });

    it('calculates metrics by scenario type', function () {
        $character = Character::factory()->for($this->user)->create();

        // Create careers for different scenarios
        Career::factory()->count(2)->for($character)->for($this->user)->uraFinale()->completed()->create();
        Career::factory()->count(1)->for($character)->for($this->user)->unityCup()->completed()->create();

        $result = $this->service->calculateCareerPerformanceMetrics($character);

        expect($result['metrics_by_scenario'])
            ->toHaveKey('ura_finale')
            ->toHaveKey('unity_cup');

        expect($result['metrics_by_scenario']['ura_finale']['count'])->toBe(2);
        expect($result['metrics_by_scenario']['unity_cup']['count'])->toBe(1);
    });

    it('caches career performance metrics', function () {
        $character = Character::factory()->for($this->user)->create();
        Career::factory()->for($character)->for($this->user)->completed()->create();

        // First call
        $result1 = $this->service->calculateCareerPerformanceMetrics($character);

        // Second call should return cached result
        $result2 = $this->service->calculateCareerPerformanceMetrics($character);

        expect($result1)->toEqual($result2);
    });
});

// =========================================================================
// TRAINING EFFECTIVENESS ANALYSIS TESTS
// =========================================================================

describe('Training Effectiveness Analysis', function () {
    it('returns empty result when character has no training sessions', function () {
        $character = Character::factory()->for($this->user)->create();

        $result = $this->service->calculateStatEfficiency($character);

        expect($result)
            ->efficiency_rating->toBe(0.0)
            ->best_training_type->toBe('N/A');
    });

    it('calculates average gains per turn correctly', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create training sessions with known gains
        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
            'training_type' => 'speed',
            'speed_gain' => 10,
            'stamina_gain' => 2,
            'power_gain' => 5,
            'guts_gain' => 0,
            'wit_gain' => 0,
            'total_stat_points_gained' => 17,
        ]);

        $result = $this->service->calculateStatEfficiency($character);

        expect($result['average_gains_per_turn'])
            ->speed->toBe(10.0)
            ->stamina->toBe(2.0)
            ->power->toBe(5.0)
            ->guts->toBe(0.0)
            ->wit->toBe(0.0);
    });

    it('identifies best training type', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create sessions with speed being the best
        TrainingSession::factory()->count(3)->for($career)->create([
            'character_id' => $character->id,
            'training_type' => 'speed',
            'speed_gain' => 15,
            'stamina_gain' => 0,
            'power_gain' => 0,
            'guts_gain' => 0,
            'wit_gain' => 0,
            'total_stat_points_gained' => 15,
        ]);

        $result = $this->service->calculateStatEfficiency($character);

        expect($result['best_training_type'])->toBe('speed');
    });

    it('calculates training type effectiveness', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create sessions for different training types
        TrainingSession::factory()->count(3)->for($career)->trainingType('speed')->create([
            'character_id' => $character->id,
            'total_stat_points_gained' => 20,
        ]);

        TrainingSession::factory()->count(2)->for($career)->trainingType('stamina')->create([
            'character_id' => $character->id,
            'total_stat_points_gained' => 15,
        ]);

        $result = $this->service->calculateStatEfficiency($character);

        expect($result['training_type_effectiveness'])
            ->toHaveKey('speed')
            ->toHaveKey('stamina');

        expect($result['training_type_effectiveness']['speed']['count'])->toBe(3);
        expect($result['training_type_effectiveness']['stamina']['count'])->toBe(2);
    });
});

// =========================================================================
// TRAINING BY PHASE ANALYSIS TESTS
// =========================================================================

describe('Training By Phase Analysis', function () {
    it('analyzes training by career phase', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create sessions for different phases
        TrainingSession::factory()->count(3)->for($career)->create([
            'character_id' => $character->id,
            'turn_number' => 10,
            'career_phase' => 'junior',
        ]);

        TrainingSession::factory()->count(3)->for($career)->create([
            'character_id' => $character->id,
            'turn_number' => 35,
            'career_phase' => 'classic',
        ]);

        $result = $this->service->analyzeTrainingByPhase($character);

        expect($result)
            ->toHaveKey('junior')
            ->toHaveKey('classic')
            ->toHaveKey('senior');

        expect($result['junior']['session_count'])->toBe(3);
        expect($result['classic']['session_count'])->toBe(3);
    });

    it('provides phase-specific recommendations', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
            'turn_number' => 10,
            'career_phase' => 'junior',
            'total_stat_points_gained' => 10, // Low efficiency
        ]);

        $result = $this->service->analyzeTrainingByPhase($character);

        expect($result['junior']['recommendations'])->toBeArray()->not->toBeEmpty();
    });
});

// =========================================================================
// GOAL COMPLETION TRACKING TESTS
// =========================================================================

describe('Goal Completion Tracking', function () {
    it('returns empty result when character has no goals', function () {
        $character = Character::factory()->for($this->user)->create(['goals' => []]);

        $result = $this->service->trackGoalCompletion($character);

        expect($result['goals_summary']['total'])->toBe(0);
        expect($result['completion_rate'])->toBe(0.0);
    });

    it('tracks stat goal completion', function () {
        $character = Character::factory()->for($this->user)->create([
            'current_stats' => [
                'speed' => 800,
                'stamina' => 600,
                'power' => 500,
                'guts' => 400,
                'wit' => 300,
            ],
            'goals' => [
                'target_stats' => [
                    'speed' => 1000,
                    'stamina' => 800,
                    'power' => 700,
                    'guts' => 500,
                    'wit' => 400,
                ],
            ],
        ]);

        $result = $this->service->trackGoalCompletion($character);

        expect($result['goals_by_type']['stat_goals'])->toBeArray()->not->toBeEmpty();
        expect($result['goals_summary']['total'])->toBeGreaterThan(0);
    });

    it('calculates timeline analysis for goals', function () {
        $character = Character::factory()->for($this->user)->create([
            'current_turn' => 30,
            'scenario_type' => 'ura_finale',
            'current_stats' => ['speed' => 500, 'stamina' => 400, 'power' => 300, 'guts' => 200, 'wit' => 100],
            'goals' => [
                'target_stats' => ['speed' => 1000, 'stamina' => 800, 'power' => 600, 'guts' => 400, 'wit' => 300],
            ],
        ]);

        $result = $this->service->trackGoalCompletion($character);

        expect($result['timeline_analysis'])
            ->current_turn->toBe(30)
            ->total_turns->toBe(72)
            ->turns_remaining->toBe(42);
    });

    it('identifies at-risk goals', function () {
        $character = Character::factory()->for($this->user)->create([
            'current_turn' => 70, // Near end
            'current_stats' => ['speed' => 500, 'stamina' => 400, 'power' => 300, 'guts' => 200, 'wit' => 100],
            'goals' => [
                'target_stats' => ['speed' => 1200], // Very high target
            ],
        ]);

        $result = $this->service->trackGoalCompletion($character);

        // With only 2 turns remaining and 700 points needed, this should be at risk
        expect($result['timeline_analysis']['at_risk_goals'])->toBeArray();
    });
});

// =========================================================================
// PREDICTION ACCURACY MEASUREMENT TESTS
// =========================================================================

describe('Prediction Accuracy Measurement', function () {
    it('returns empty result when no prediction data exists', function () {
        $character = Character::factory()->for($this->user)->create();

        $result = $this->service->measurePredictionAccuracy($character);

        expect($result['overall_accuracy'])->toBe(0.0);
        expect($result)->toHaveKey('stat_prediction_accuracy');
        expect($result)->toHaveKey('training_type_accuracy');
        expect($result)->toHaveKey('race_prediction_accuracy');
        expect($result)->toHaveKey('improvement_tracking');
        expect($result)->toHaveKey('accuracy_trend');
    });

    it('calculates stat prediction accuracy from training sessions', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create sessions with prediction metadata
        TrainingSession::factory()->count(5)->for($career)->withPredictions()->create([
            'character_id' => $character->id,
        ]);

        $result = $this->service->measurePredictionAccuracy($character);

        expect($result['stat_prediction_accuracy'])->toBeArray();
        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            expect($result['stat_prediction_accuracy'])->toHaveKey($stat);
        }
    });

    it('calculates race prediction accuracy', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create races with prediction data
        Race::factory()->count(5)->for($career)->withPredictions()->create([
            'character_id' => $character->id,
        ]);

        $result = $this->service->measurePredictionAccuracy($character);

        expect($result['race_prediction_accuracy'])
            ->toHaveKey('position_accuracy')
            ->toHaveKey('win_prediction_accuracy')
            ->toHaveKey('total_races')
            ->toHaveKey('correct_predictions')
            ->toHaveKey('position_deviation');

        expect($result['race_prediction_accuracy']['total_races'])->toBe(5);
    });

    it('tracks prediction improvement over time', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create sessions with prediction metadata over time
        for ($i = 0; $i < 10; $i++) {
            TrainingSession::factory()->withPredictions()->create([
                'career_id' => $career->id,
                'character_id' => $character->id,
                'created_at' => now()->subDays(10 - $i),
            ]);
        }

        $result = $this->service->measurePredictionAccuracy($character);

        expect($result['improvement_tracking'])
            ->toHaveKey('early_accuracy')
            ->toHaveKey('recent_accuracy')
            ->toHaveKey('improvement_percentage')
            ->toHaveKey('trend')
            ->toHaveKey('learning_rate');
    });

    it('calculates accuracy trend over time periods', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create sessions spread over multiple weeks
        for ($week = 0; $week < 4; $week++) {
            TrainingSession::factory()->count(3)->withPredictions()->create([
                'career_id' => $career->id,
                'character_id' => $character->id,
                'created_at' => now()->subWeeks($week),
            ]);
        }

        $result = $this->service->measurePredictionAccuracy($character);

        expect($result['accuracy_trend'])
            ->toHaveKey('weekly_accuracy')
            ->toHaveKey('monthly_accuracy')
            ->toHaveKey('trend_direction')
            ->toHaveKey('consistency_score');
    });
});

// =========================================================================
// COMPREHENSIVE ANALYTICS TESTS
// =========================================================================

describe('Comprehensive Analytics', function () {
    it('returns all analytics components', function () {
        $character = Character::factory()->for($this->user)->create([
            'goals' => [
                'target_stats' => ['speed' => 1000, 'stamina' => 800],
            ],
        ]);
        $career = Career::factory()->for($character)->for($this->user)->completed()->create();

        TrainingSession::factory()->count(5)->for($career)->withPredictions()->create([
            'character_id' => $character->id,
        ]);

        Race::factory()->count(3)->for($career)->withPredictions()->create([
            'character_id' => $character->id,
        ]);

        $result = $this->service->getComprehensiveAnalytics($character);

        expect($result)
            ->toHaveKey('career_performance')
            ->toHaveKey('training_effectiveness')
            ->toHaveKey('goal_completion')
            ->toHaveKey('prediction_accuracy')
            ->toHaveKey('overall_score')
            ->toHaveKey('recommendations');
    });

    it('calculates overall analytics score', function () {
        $character = Character::factory()->for($this->user)->create([
            'goals' => [
                'target_stats' => ['speed' => 1000],
            ],
        ]);
        $career = Career::factory()->for($character)->for($this->user)->completed()->create();

        TrainingSession::factory()->count(10)->for($career)->create([
            'character_id' => $character->id,
            'total_stat_points_gained' => 25,
        ]);

        $result = $this->service->getComprehensiveAnalytics($character);

        expect($result['overall_score'])->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
    });

    it('generates analytics-based recommendations', function () {
        $character = Character::factory()->for($this->user)->create([
            'goals' => [
                'target_stats' => ['speed' => 1200, 'stamina' => 1000],
            ],
        ]);
        $career = Career::factory()->for($character)->for($this->user)->completed()->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
            'total_stat_points_gained' => 10, // Low efficiency
        ]);

        $result = $this->service->getComprehensiveAnalytics($character);

        expect($result['recommendations'])->toBeArray();
    });
});

// =========================================================================
// CACHE MANAGEMENT TESTS
// =========================================================================

describe('Cache Management', function () {
    it('clears all analytics cache for a character', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->completed()->create();

        TrainingSession::factory()->count(3)->for($career)->create([
            'character_id' => $character->id,
        ]);

        // Populate cache
        $this->service->calculateCareerPerformanceMetrics($character);
        $this->service->calculateStatEfficiency($character);
        $this->service->trackGoalCompletion($character);
        $this->service->measurePredictionAccuracy($character);

        // Clear cache
        $this->service->clearCache($character);

        // Verify cache is cleared by checking cache keys don't exist
        expect(Cache::has("analytics:career_performance:{$character->id}"))->toBeFalse();
        expect(Cache::has("analytics:stat_efficiency:{$character->id}"))->toBeFalse();
        expect(Cache::has("analytics:goal_completion:{$character->id}"))->toBeFalse();
        expect(Cache::has("analytics:prediction_accuracy:{$character->id}"))->toBeFalse();
    });
});
