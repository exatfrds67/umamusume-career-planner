<?php

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\HistoricalTrackingService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->service = new HistoricalTrackingService;
    $this->user = User::factory()->create();
    Cache::flush();
});

// =========================================================================
// LONG-TERM TREND ANALYSIS TESTS
// =========================================================================

describe('Long-Term Trend Analysis', function () {
    it('returns insufficient data result when user has fewer than 5 careers', function () {
        // Create only 3 completed careers
        $character = Character::factory()->for($this->user)->create();
        Career::factory()->count(3)->for($character)->for($this->user)->completed()->create();

        $result = $this->service->analyzeLongTermTrends($this->user);

        expect($result)
            ->toHaveKey('error')
            ->error->toBe('insufficient_data')
            ->current_sample_size->toBe(3)
            ->required_sample_size->toBe(5);
    });

    it('calculates trend summary with sufficient data', function () {
        $character = Character::factory()->for($this->user)->create();

        // Create 6 completed careers with training sessions
        for ($i = 0; $i < 6; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'completed_at' => now()->subDays(30 - ($i * 5)),
                'final_speed' => 800 + ($i * 20),
                'final_stamina' => 700 + ($i * 15),
                'final_power' => 600 + ($i * 10),
                'final_guts' => 500 + ($i * 5),
                'final_wit' => 400 + ($i * 5),
            ]);

            TrainingSession::factory()->count(10)->for($career)->create([
                'character_id' => $character->id,
                'speed_gain' => 10 + $i,
                'stamina_gain' => 8 + $i,
                'power_gain' => 6 + $i,
                'guts_gain' => 4,
                'wit_gain' => 2,
            ]);
        }

        $result = $this->service->analyzeLongTermTrends($this->user);

        expect($result)
            ->toHaveKey('trend_summary')
            ->toHaveKey('performance_evolution')
            ->toHaveKey('stat_trends')
            ->toHaveKey('efficiency_trends')
            ->toHaveKey('race_performance_trends')
            ->toHaveKey('improvement_velocity');

        expect($result['trend_summary'])
            ->toHaveKey('total_careers')
            ->toHaveKey('overall_trend')
            ->toHaveKey('improvement_rate')
            ->toHaveKey('consistency_score');

        expect($result['trend_summary']['total_careers'])->toBe(6);
    });

    it('identifies improving trend when performance increases over time', function () {
        $character = Character::factory()->for($this->user)->create();

        // Create careers with increasing efficiency
        for ($i = 0; $i < 6; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'completed_at' => now()->subDays(30 - ($i * 5)),
            ]);

            // Increasing stat gains over time
            TrainingSession::factory()->count(10)->for($career)->create([
                'character_id' => $character->id,
                'speed_gain' => 5 + ($i * 3),
                'stamina_gain' => 4 + ($i * 2),
                'power_gain' => 3 + ($i * 2),
                'guts_gain' => 2 + $i,
                'wit_gain' => 1 + $i,
            ]);
        }

        $result = $this->service->analyzeLongTermTrends($this->user);

        expect($result['trend_summary']['overall_trend'])->toBeIn(['improving', 'stable']);
    });

    it('calculates performance evolution for each career', function () {
        $character = Character::factory()->for($this->user)->create();

        for ($i = 0; $i < 5; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'completed_at' => now()->subDays(25 - ($i * 5)),
                'final_speed' => 800,
                'final_stamina' => 700,
                'final_power' => 600,
                'final_guts' => 500,
                'final_wit' => 400,
            ]);

            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
            ]);

            Race::factory()->count(3)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        $result = $this->service->analyzeLongTermTrends($this->user);

        expect($result['performance_evolution'])->toBeArray()->toHaveCount(5);

        foreach ($result['performance_evolution'] as $evolution) {
            expect($evolution)
                ->toHaveKey('career_id')
                ->toHaveKey('completed_at')
                ->toHaveKey('efficiency')
                ->toHaveKey('total_stats')
                ->toHaveKey('win_rate')
                ->toHaveKey('score');
        }
    });

    it('calculates stat trends for each stat type', function () {
        $character = Character::factory()->for($this->user)->create();

        for ($i = 0; $i < 5; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'completed_at' => now()->subDays(25 - ($i * 5)),
                'final_speed' => 800 + ($i * 50),
                'final_stamina' => 700 + ($i * 40),
                'final_power' => 600 + ($i * 30),
                'final_guts' => 500 + ($i * 20),
                'final_wit' => 400 + ($i * 10),
            ]);

            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        $result = $this->service->analyzeLongTermTrends($this->user);

        expect($result['stat_trends'])
            ->toHaveKey('speed')
            ->toHaveKey('stamina')
            ->toHaveKey('power')
            ->toHaveKey('guts')
            ->toHaveKey('wit');

        foreach ($result['stat_trends'] as $stat => $trend) {
            expect($trend)
                ->toHaveKey('trend')
                ->toHaveKey('average')
                ->toHaveKey('growth_rate')
                ->toHaveKey('values');
        }
    });

    it('caches long-term trend results', function () {
        $character = Character::factory()->for($this->user)->create();

        for ($i = 0; $i < 5; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create();
            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        // First call
        $result1 = $this->service->analyzeLongTermTrends($this->user);

        // Second call should return cached result
        $result2 = $this->service->analyzeLongTermTrends($this->user);

        expect($result1)->toEqual($result2);
    });
});

// =========================================================================
// SUCCESS RATE WITH CONFIDENCE INTERVALS TESTS
// =========================================================================

describe('Success Rate with Confidence Intervals', function () {
    it('returns insufficient data result when user has fewer than 5 careers', function () {
        $character = Character::factory()->for($this->user)->create();
        Career::factory()->count(3)->for($character)->for($this->user)->completed()->create();

        $result = $this->service->calculateSuccessRatesWithConfidence($this->user);

        expect($result)
            ->toHaveKey('error')
            ->error->toBe('insufficient_data');
    });

    it('calculates overall success rate with confidence intervals', function () {
        $character = Character::factory()->for($this->user)->create();

        // Create 10 careers with varying success
        for ($i = 0; $i < 10; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'performance_analysis' => ['goals_achieved' => $i < 7], // 70% success rate
            ]);

            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
                'speed_gain' => $i < 7 ? 15 : 5,
                'stamina_gain' => $i < 7 ? 12 : 4,
                'power_gain' => $i < 7 ? 10 : 3,
                'guts_gain' => $i < 7 ? 8 : 2,
                'wit_gain' => $i < 7 ? 5 : 1,
            ]);
        }

        $result = $this->service->calculateSuccessRatesWithConfidence($this->user);

        expect($result)
            ->toHaveKey('overall_success_rate')
            ->toHaveKey('success_by_scenario')
            ->toHaveKey('success_factors')
            ->toHaveKey('confidence_analysis');

        expect($result['overall_success_rate'])
            ->toHaveKey('success_rate')
            ->toHaveKey('sample_size')
            ->toHaveKey('confidence_interval_95')
            ->toHaveKey('confidence_interval_99')
            ->toHaveKey('margin_of_error');

        expect($result['overall_success_rate']['sample_size'])->toBe(10);
    });

    it('calculates Wilson confidence intervals correctly', function () {
        $character = Character::factory()->for($this->user)->create();

        // Create 20 careers with 50% success rate
        for ($i = 0; $i < 20; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'performance_analysis' => ['goals_achieved' => $i < 10],
            ]);

            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        $result = $this->service->calculateSuccessRatesWithConfidence($this->user);

        $ci95 = $result['overall_success_rate']['confidence_interval_95'];

        // With 50% success rate and n=20, CI should be roughly 28-72%
        expect($ci95['lower'])->toBeGreaterThan(20);
        expect($ci95['upper'])->toBeLessThan(80);
        expect($ci95['lower'])->toBeLessThan($ci95['upper']);
    });

    it('calculates success rates by scenario type', function () {
        $character = Character::factory()->for($this->user)->create();

        // Create URA Finale careers
        for ($i = 0; $i < 5; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->uraFinale()->completed()->create([
                'performance_analysis' => ['goals_achieved' => $i < 4], // 80% success
            ]);
            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        // Create Unity Cup careers
        for ($i = 0; $i < 5; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->unityCup()->completed()->create([
                'performance_analysis' => ['goals_achieved' => $i < 3], // 60% success
            ]);
            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        $result = $this->service->calculateSuccessRatesWithConfidence($this->user);

        expect($result['success_by_scenario'])
            ->toHaveKey('ura_finale')
            ->toHaveKey('unity_cup');

        expect($result['success_by_scenario']['ura_finale']['sample_size'])->toBe(5);
        expect($result['success_by_scenario']['unity_cup']['sample_size'])->toBe(5);
    });

    it('performs confidence analysis on success metrics', function () {
        $character = Character::factory()->for($this->user)->create();

        for ($i = 0; $i < 15; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'performance_analysis' => ['goals_achieved' => $i % 2 === 0],
            ]);
            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        $result = $this->service->calculateSuccessRatesWithConfidence($this->user);

        expect($result['confidence_analysis'])
            ->toHaveKey('statistical_significance')
            ->toHaveKey('sample_adequacy')
            ->toHaveKey('reliability_score')
            ->toHaveKey('recommendations_for_improvement');

        expect($result['confidence_analysis']['sample_adequacy'])->toBeIn(['excellent', 'good', 'moderate', 'minimal', 'insufficient']);
    });
});

// =========================================================================
// ML MODEL UPDATE RECOMMENDATIONS TESTS
// =========================================================================

describe('ML Model Update Recommendations', function () {
    it('returns insufficient data result when user has fewer than 5 careers', function () {
        $character = Character::factory()->for($this->user)->create();
        Career::factory()->count(3)->for($character)->for($this->user)->completed()->create();

        $result = $this->service->generateMLModelUpdateRecommendations($this->user);

        expect($result)
            ->toHaveKey('error')
            ->error->toBe('insufficient_data');
    });

    it('generates ML model update recommendations with sufficient data', function () {
        $character = Character::factory()->for($this->user)->create();

        for ($i = 0; $i < 10; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'completed_at' => now()->subDays(30 - ($i * 3)),
            ]);

            TrainingSession::factory()->count(5)->for($career)->withPredictions()->create([
                'character_id' => $character->id,
            ]);

            Race::factory()->count(3)->for($career)->withPredictions()->create([
                'character_id' => $character->id,
            ]);
        }

        $result = $this->service->generateMLModelUpdateRecommendations($this->user);

        expect($result)
            ->toHaveKey('model_performance')
            ->toHaveKey('update_recommendations')
            ->toHaveKey('feature_importance')
            ->toHaveKey('data_quality_assessment')
            ->toHaveKey('retraining_priority');
    });

    it('assesses model performance based on prediction accuracy', function () {
        $character = Character::factory()->for($this->user)->create();

        for ($i = 0; $i < 5; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create();

            TrainingSession::factory()->count(5)->for($career)->withPredictions()->create([
                'character_id' => $character->id,
            ]);
        }

        $result = $this->service->generateMLModelUpdateRecommendations($this->user);

        expect($result['model_performance'])
            ->toHaveKey('overall_accuracy')
            ->toHaveKey('stat_prediction_accuracy')
            ->toHaveKey('race_prediction_accuracy')
            ->toHaveKey('accuracy_trend')
            ->toHaveKey('degradation_detected');
    });

    it('calculates feature importance based on success correlation', function () {
        $character = Character::factory()->for($this->user)->create();

        for ($i = 0; $i < 10; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'performance_analysis' => ['goals_achieved' => $i < 5],
            ]);

            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
                'friendship_training' => $i < 5,
            ]);

            Race::factory()->count(3)->for($career)->create([
                'character_id' => $character->id,
                'won_race' => $i < 5,
            ]);
        }

        $result = $this->service->generateMLModelUpdateRecommendations($this->user);

        expect($result['feature_importance'])->toBeArray();

        foreach ($result['feature_importance'] as $feature => $importance) {
            expect($importance)->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(100);
        }
    });

    it('assesses data quality for ML training', function () {
        $character = Character::factory()->for($this->user)->create();

        for ($i = 0; $i < 5; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'final_speed' => 800,
                'final_stamina' => 700,
                'final_power' => 600,
                'final_guts' => 500,
                'final_wit' => 400,
            ]);

            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
            ]);

            Race::factory()->count(3)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        $result = $this->service->generateMLModelUpdateRecommendations($this->user);

        expect($result['data_quality_assessment'])
            ->toHaveKey('completeness')
            ->toHaveKey('consistency')
            ->toHaveKey('recency')
            ->toHaveKey('volume_adequacy')
            ->toHaveKey('issues');

        expect($result['data_quality_assessment']['volume_adequacy'])->toBeIn(['excellent', 'good', 'moderate', 'minimal', 'insufficient']);
    });

    it('determines retraining priority based on all factors', function () {
        $character = Character::factory()->for($this->user)->create();

        for ($i = 0; $i < 5; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create();
            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        $result = $this->service->generateMLModelUpdateRecommendations($this->user);

        expect($result['retraining_priority'])->toBeIn(['critical', 'high', 'medium', 'low']);
    });
});

// =========================================================================
// IMPROVEMENT VELOCITY TESTS
// =========================================================================

describe('Improvement Velocity', function () {
    it('calculates improvement velocity with sufficient data', function () {
        $character = Character::factory()->for($this->user)->create();

        // Create careers with increasing performance
        for ($i = 0; $i < 6; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'completed_at' => now()->subDays(30 - ($i * 5)),
            ]);

            TrainingSession::factory()->count(10)->for($career)->create([
                'character_id' => $character->id,
                'speed_gain' => 8 + ($i * 2),
                'stamina_gain' => 6 + ($i * 2),
                'power_gain' => 5 + $i,
                'guts_gain' => 4 + $i,
                'wit_gain' => 3 + $i,
            ]);
        }

        $result = $this->service->analyzeLongTermTrends($this->user);

        expect($result['improvement_velocity'])
            ->toHaveKey('velocity')
            ->toHaveKey('acceleration')
            ->toHaveKey('projected_next_score')
            ->toHaveKey('time_to_mastery')
            ->toHaveKey('velocity_trend');
    });

    it('identifies accelerating improvement trend', function () {
        $character = Character::factory()->for($this->user)->create();

        // Create careers with accelerating improvement
        for ($i = 0; $i < 6; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'completed_at' => now()->subDays(30 - ($i * 5)),
            ]);

            // Exponentially increasing gains
            $multiplier = pow(1.5, $i);
            TrainingSession::factory()->count(10)->for($career)->create([
                'character_id' => $character->id,
                'speed_gain' => (int) (5 * $multiplier),
                'stamina_gain' => (int) (4 * $multiplier),
                'power_gain' => (int) (3 * $multiplier),
                'guts_gain' => (int) (2 * $multiplier),
                'wit_gain' => (int) (1 * $multiplier),
            ]);
        }

        $result = $this->service->analyzeLongTermTrends($this->user);

        expect($result['improvement_velocity']['velocity'])->toBeGreaterThan(0);
    });
});

// =========================================================================
// CACHE MANAGEMENT TESTS
// =========================================================================

describe('Cache Management', function () {
    it('clears all historical tracking cache for a user', function () {
        $character = Character::factory()->for($this->user)->create();

        for ($i = 0; $i < 5; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create();
            TrainingSession::factory()->count(5)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        // Populate cache
        $this->service->analyzeLongTermTrends($this->user);
        $this->service->calculateSuccessRatesWithConfidence($this->user);
        $this->service->generateMLModelUpdateRecommendations($this->user);

        // Clear cache
        $this->service->clearCache($this->user);

        // Verify cache is cleared
        expect(Cache::has("historical:long_term_trends:{$this->user->id}"))->toBeFalse();
        expect(Cache::has("historical:success_rates:{$this->user->id}"))->toBeFalse();
        expect(Cache::has("historical:ml_recommendations:{$this->user->id}"))->toBeFalse();
    });
});
