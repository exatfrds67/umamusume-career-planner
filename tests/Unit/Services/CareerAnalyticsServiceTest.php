<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\TrainingSession;
use App\Services\CareerAnalyticsService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->analyticsService = new CareerAnalyticsService;
    $this->character = Character::factory()->create([
        'current_stats' => [
            'speed' => 600,
            'stamina' => 500,
            'power' => 450,
            'guts' => 400,
            'wit' => 350,
        ],
        'goals' => [
            'target_stats' => [
                'speed' => 800,
                'stamina' => 700,
                'power' => 600,
                'guts' => 500,
                'wit' => 500,
            ],
        ],
        'current_turn' => 30,
    ]);

    // Clear cache before each test
    Cache::flush();
});

describe('CareerAnalyticsService', function () {
    describe('calculateCareerPerformanceMetrics', function () {
        it('returns empty result for character with no careers', function () {
            $result = $this->analyticsService->calculateCareerPerformanceMetrics($this->character);

            expect($result['total_careers'])->toBe(0);
            expect($result['completed_careers'])->toBe(0);
            expect($result['overall_efficiency'])->toBe(0.0);
        });

        it('calculates metrics for completed careers', function () {
            // Create completed careers
            $career1 = Career::factory()->create([
                'character_id' => $this->character->id,
                'scenario_type' => 'ura_finale',
                'completed_at' => now(),
                'performance_analysis' => ['goals_achieved' => true, 'final_grade' => 'A'],
            ]);

            $career2 = Career::factory()->create([
                'character_id' => $this->character->id,
                'scenario_type' => 'unity_cup',
                'completed_at' => now(),
                'performance_analysis' => ['goals_achieved' => false, 'final_grade' => 'B'],
            ]);

            // Create training sessions for careers
            TrainingSession::factory()->count(10)->create([
                'character_id' => $this->character->id,
                'career_id' => $career1->id,
                'speed_gain' => 15,
                'stamina_gain' => 10,
                'power_gain' => 5,
                'guts_gain' => 3,
                'wit_gain' => 2,
            ]);

            $result = $this->analyticsService->calculateCareerPerformanceMetrics($this->character);

            expect($result['total_careers'])->toBe(2);
            expect($result['completed_careers'])->toBe(2);
            expect($result['success_rate'])->toBe(50.0); // 1 of 2 successful
            expect($result['completion_rate'])->toBe(100.0);
        });

        it('calculates metrics by scenario type', function () {
            Career::factory()->create([
                'character_id' => $this->character->id,
                'scenario_type' => 'ura_finale',
                'completed_at' => now(),
            ]);

            Career::factory()->create([
                'character_id' => $this->character->id,
                'scenario_type' => 'unity_cup',
                'completed_at' => now(),
            ]);

            $result = $this->analyticsService->calculateCareerPerformanceMetrics($this->character);

            expect($result['metrics_by_scenario'])->toHaveKeys(['ura_finale', 'unity_cup']);
            expect($result['metrics_by_scenario']['ura_finale']['count'])->toBe(1);
            expect($result['metrics_by_scenario']['unity_cup']['count'])->toBe(1);
        });

        it('calculates performance trend', function () {
            // Create multiple completed careers over time
            for ($i = 0; $i < 5; $i++) {
                $career = Career::factory()->create([
                    'character_id' => $this->character->id,
                    'completed_at' => now()->subDays(30 - ($i * 5)),
                ]);

                TrainingSession::factory()->count(5)->create([
                    'character_id' => $this->character->id,
                    'career_id' => $career->id,
                    'speed_gain' => 10 + $i, // Improving over time
                    'stamina_gain' => 8 + $i,
                ]);
            }

            $result = $this->analyticsService->calculateCareerPerformanceMetrics($this->character);

            expect($result['performance_trend'])->toHaveKeys(['trend', 'improvement_rate', 'recent_performance']);
        });
    });

    describe('calculateStatEfficiency', function () {
        it('returns empty result for character with no training sessions', function () {
            $result = $this->analyticsService->calculateStatEfficiency($this->character);

            expect($result['efficiency_rating'])->toBe(0.0);
            expect($result['average_gains_per_turn'])->toBeArray();
        });

        it('calculates average gains per turn', function () {
            TrainingSession::factory()->count(10)->create([
                'character_id' => $this->character->id,
                'training_type' => 'speed',
                'speed_gain' => 20,
                'stamina_gain' => 5,
                'power_gain' => 3,
                'guts_gain' => 2,
                'wit_gain' => 1,
            ]);

            $result = $this->analyticsService->calculateStatEfficiency($this->character);

            expect($result['average_gains_per_turn']['speed'])->toBe(20.0);
            expect($result['average_gains_per_turn']['stamina'])->toBe(5.0);
            expect($result['best_training_type'])->toBe('speed');
        });

        it('calculates stat gain distribution', function () {
            TrainingSession::factory()->count(20)->create([
                'character_id' => $this->character->id,
                'training_type' => 'speed',
                'speed_gain' => fake()->numberBetween(10, 30),
            ]);

            $result = $this->analyticsService->calculateStatEfficiency($this->character);

            expect($result['stat_gain_distribution'])->toHaveKey('speed');
            expect($result['stat_gain_distribution']['speed'])->toHaveKeys(['min', 'max', 'median', 'std_dev', 'total']);
        });

        it('calculates training type effectiveness', function () {
            // Create sessions for different training types
            foreach (['speed', 'stamina', 'power'] as $type) {
                TrainingSession::factory()->count(5)->create([
                    'character_id' => $this->character->id,
                    'training_type' => $type,
                    "{$type}_gain" => 15,
                    'sp_gain' => 10,
                    'training_failed' => false,
                ]);
            }

            $result = $this->analyticsService->calculateStatEfficiency($this->character);

            expect($result['training_type_effectiveness'])->toHaveKeys(['speed', 'stamina', 'power']);
            foreach (['speed', 'stamina', 'power'] as $type) {
                expect($result['training_type_effectiveness'][$type])->toHaveKeys([
                    'count',
                    'avg_total_gain',
                    'success_rate',
                    'avg_sp_gain',
                ]);
            }
        });

        it('generates improvement suggestions', function () {
            TrainingSession::factory()->count(10)->create([
                'character_id' => $this->character->id,
                'training_type' => 'speed',
                'speed_gain' => 5, // Low gains
            ]);

            $result = $this->analyticsService->calculateStatEfficiency($this->character);

            expect($result['improvement_suggestions'])->toBeArray();
        });
    });

    describe('analyzeTrainingByPhase', function () {
        it('analyzes training by career phase', function () {
            // Create sessions for different phases
            TrainingSession::factory()->count(5)->create([
                'character_id' => $this->character->id,
                'turn_number' => 10, // Junior phase
                'speed_gain' => 15,
            ]);

            TrainingSession::factory()->count(5)->create([
                'character_id' => $this->character->id,
                'turn_number' => 35, // Classic phase
                'speed_gain' => 20,
            ]);

            TrainingSession::factory()->count(5)->create([
                'character_id' => $this->character->id,
                'turn_number' => 55, // Senior phase
                'speed_gain' => 18,
            ]);

            $result = $this->analyticsService->analyzeTrainingByPhase($this->character);

            expect($result)->toHaveKeys(['junior', 'classic', 'senior']);
            foreach (['junior', 'classic', 'senior'] as $phase) {
                expect($result[$phase])->toHaveKeys([
                    'turn_range',
                    'avg_gains',
                    'efficiency',
                    'session_count',
                    'recommendations',
                ]);
            }
        });

        it('generates phase-specific recommendations', function () {
            TrainingSession::factory()->count(10)->create([
                'character_id' => $this->character->id,
                'turn_number' => 10,
                'speed_gain' => 5, // Low efficiency
            ]);

            $result = $this->analyticsService->analyzeTrainingByPhase($this->character);

            expect($result['junior']['recommendations'])->toBeArray();
            expect(count($result['junior']['recommendations']))->toBeGreaterThan(0);
        });
    });

    describe('trackGoalCompletion', function () {
        it('returns empty result for character with no goals', function () {
            $characterNoGoals = Character::factory()->create(['goals' => []]);

            $result = $this->analyticsService->trackGoalCompletion($characterNoGoals);

            expect($result['goals_summary']['total'])->toBe(0);
            expect($result['completion_rate'])->toBe(0.0);
        });

        it('tracks stat goal completion', function () {
            $result = $this->analyticsService->trackGoalCompletion($this->character);

            expect($result['goals_summary'])->toHaveKeys(['total', 'completed', 'in_progress', 'failed']);
            expect($result['goals_by_type'])->toHaveKey('stat_goals');
        });

        it('calculates timeline analysis', function () {
            // Create some training sessions to establish a rate
            TrainingSession::factory()->count(10)->create([
                'character_id' => $this->character->id,
                'speed_gain' => 10,
                'stamina_gain' => 8,
            ]);

            $result = $this->analyticsService->trackGoalCompletion($this->character);

            expect($result['timeline_analysis'])->toHaveKeys([
                'current_turn',
                'total_turns',
                'turns_remaining',
                'projected_completion',
                'at_risk_goals',
            ]);
        });

        it('identifies at-risk goals', function () {
            // Character with high targets and low current stats
            $character = Character::factory()->create([
                'current_stats' => [
                    'speed' => 100,
                    'stamina' => 100,
                ],
                'goals' => [
                    'target_stats' => [
                        'speed' => 1200, // Very high target
                        'stamina' => 1200,
                    ],
                ],
                'current_turn' => 70, // Near end
            ]);

            // Low training rate
            TrainingSession::factory()->count(5)->create([
                'character_id' => $character->id,
                'speed_gain' => 5,
                'stamina_gain' => 5,
            ]);

            $result = $this->analyticsService->trackGoalCompletion($character);

            expect($result['timeline_analysis']['at_risk_goals'])->toBeArray();
        });
    });
});
