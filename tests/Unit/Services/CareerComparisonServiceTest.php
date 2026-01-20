<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\CareerComparisonService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;

uses(DatabaseMigrations::class);

beforeEach(function (): void {
    $this->service = new CareerComparisonService;
    Cache::flush();
});

describe('CareerComparisonService', function (): void {
    describe('compareCareers', function (): void {
        it('returns error when less than 2 careers provided', function (): void {
            $result = $this->service->compareCareers([1]);

            expect($result)->toHaveKey('careers')
                ->and($result['careers'])->toBeEmpty();
        });

        it('compares multiple careers successfully', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career1 = Career::factory()->create([
                'character_id' => $character->id,
                'scenario_type' => 'ura_finale',
                'status' => 'completed',
            ]);

            $career2 = Career::factory()->create([
                'character_id' => $character->id,
                'scenario_type' => 'ura_finale',
                'status' => 'completed',
            ]);

            // Add training sessions
            TrainingSession::factory()->count(5)->create([
                'career_id' => $career1->id,
                'training_type' => 'speed',
                'speed_gain' => 10,
            ]);

            TrainingSession::factory()->count(5)->create([
                'career_id' => $career2->id,
                'training_type' => 'stamina',
                'stamina_gain' => 12,
            ]);

            $result = $this->service->compareCareers([$career1->id, $career2->id]);

            expect($result)->toHaveKey('careers')
                ->and($result)->toHaveKey('comparison_summary')
                ->and($result)->toHaveKey('stat_comparison')
                ->and($result)->toHaveKey('training_comparison')
                ->and($result)->toHaveKey('race_comparison')
                ->and($result)->toHaveKey('key_differences')
                ->and($result)->toHaveKey('best_performer')
                ->and($result['careers'])->toHaveCount(2);
        });

        it('calculates stat comparison correctly', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career1 = Career::factory()->create([
                'character_id' => $character->id,
                'performance_analysis' => [
                    'final_stats' => [
                        'speed' => 800,
                        'stamina' => 600,
                        'power' => 500,
                        'guts' => 400,
                        'wit' => 300,
                    ],
                ],
            ]);

            $career2 = Career::factory()->create([
                'character_id' => $character->id,
                'performance_analysis' => [
                    'final_stats' => [
                        'speed' => 600,
                        'stamina' => 800,
                        'power' => 500,
                        'guts' => 400,
                        'wit' => 300,
                    ],
                ],
            ]);

            $result = $this->service->compareCareers([$career1->id, $career2->id]);

            expect($result['stat_comparison'])->toHaveKey('speed')
                ->and($result['stat_comparison']['speed']['min'])->toBe(600)
                ->and($result['stat_comparison']['speed']['max'])->toBe(800)
                ->and($result['stat_comparison']['speed']['range'])->toBe(200);
        });

        it('identifies key differences between careers', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career1 = Career::factory()->create([
                'character_id' => $character->id,
                'performance_analysis' => [
                    'final_stats' => [
                        'speed' => 1000,
                        'stamina' => 200,
                        'power' => 200,
                        'guts' => 200,
                        'wit' => 200,
                    ],
                ],
            ]);

            $career2 = Career::factory()->create([
                'character_id' => $character->id,
                'performance_analysis' => [
                    'final_stats' => [
                        'speed' => 200,
                        'stamina' => 1000,
                        'power' => 200,
                        'guts' => 200,
                        'wit' => 200,
                    ],
                ],
            ]);

            $result = $this->service->compareCareers([$career1->id, $career2->id]);

            expect($result['key_differences'])->not->toBeEmpty();
        });

        it('determines best performer correctly', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career1 = Career::factory()->create([
                'character_id' => $character->id,
                'career_name' => 'Best Career',
            ]);

            $career2 = Career::factory()->create([
                'character_id' => $character->id,
                'career_name' => 'Average Career',
            ]);

            // Add more successful training to career1
            TrainingSession::factory()->count(20)->create([
                'career_id' => $career1->id,
                'training_type' => 'speed',
                'speed_gain' => 15,
                'training_failed' => false,
            ]);

            TrainingSession::factory()->count(10)->create([
                'career_id' => $career2->id,
                'training_type' => 'speed',
                'speed_gain' => 5,
                'training_failed' => true,
            ]);

            $result = $this->service->compareCareers([$career1->id, $career2->id]);

            expect($result['best_performer'])->toHaveKey('career_id')
                ->and($result['best_performer'])->toHaveKey('score')
                ->and($result['best_performer'])->toHaveKey('strengths');
        });

        it('caches comparison results', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career1 = Career::factory()->create(['character_id' => $character->id]);
            $career2 = Career::factory()->create(['character_id' => $character->id]);

            // First call
            $result1 = $this->service->compareCareers([$career1->id, $career2->id]);

            // Second call should use cache
            $result2 = $this->service->compareCareers([$career1->id, $career2->id]);

            expect($result1)->toEqual($result2);
        });
    });

    describe('identifySuccessPatterns', function (): void {
        it('returns empty result for no careers', function (): void {
            $result = $this->service->identifySuccessPatterns([]);

            expect($result)->toHaveKey('training_patterns')
                ->and($result)->toHaveKey('race_strategy_patterns')
                ->and($result)->toHaveKey('decision_sequences')
                ->and($result)->toHaveKey('success_correlations')
                ->and($result)->toHaveKey('recommended_patterns');
        });

        it('identifies training patterns from successful careers', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            // Create successful career (high efficiency)
            $successfulCareer = Career::factory()->create([
                'character_id' => $character->id,
                'completed_at' => now(),
            ]);

            TrainingSession::factory()->count(30)->create([
                'career_id' => $successfulCareer->id,
                'training_type' => 'speed',
                'speed_gain' => 15,
            ]);

            // Create unsuccessful career (low efficiency)
            $unsuccessfulCareer = Career::factory()->create([
                'character_id' => $character->id,
                'completed_at' => now(),
            ]);

            TrainingSession::factory()->count(10)->create([
                'career_id' => $unsuccessfulCareer->id,
                'training_type' => 'rest',
                'speed_gain' => 0,
            ]);

            $result = $this->service->identifySuccessPatterns([
                $successfulCareer->id,
                $unsuccessfulCareer->id,
            ]);

            expect($result['training_patterns'])->toHaveKey('successful_patterns')
                ->and($result['training_patterns'])->toHaveKey('unsuccessful_patterns')
                ->and($result['training_patterns'])->toHaveKey('key_differences');
        });
    });

    describe('analyzeSuccessFactors', function (): void {
        it('analyzes success factors across careers', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            // Need at least 5 careers for analysis
            $careerIds = [];
            for ($i = 0; $i < 5; $i++) {
                $career = Career::factory()->create([
                    'character_id' => $character->id,
                    'completed_at' => now(),
                ]);

                TrainingSession::factory()->count(20)->create([
                    'career_id' => $career->id,
                ]);

                $careerIds[] = $career->id;
            }

            $result = $this->service->analyzeSuccessFactors($careerIds);

            expect($result)->toHaveKey('success_factors')
                ->and($result)->toHaveKey('factor_importance')
                ->and($result)->toHaveKey('correlation_matrix')
                ->and($result)->toHaveKey('actionable_insights');
        });

        it('returns insufficient data message for less than 5 careers', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career1 = Career::factory()->create(['character_id' => $character->id]);
            $career2 = Career::factory()->create(['character_id' => $character->id]);

            $result = $this->service->analyzeSuccessFactors([$career1->id, $career2->id]);

            expect($result['actionable_insights'])->toContain('Insufficient data for success factor analysis (minimum 5 careers required)');
        });
    });

    describe('training comparison', function (): void {
        it('calculates training type distribution', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career = Career::factory()->create(['character_id' => $character->id]);

            TrainingSession::factory()->count(10)->create([
                'career_id' => $career->id,
                'training_type' => 'speed',
            ]);

            TrainingSession::factory()->count(5)->create([
                'career_id' => $career->id,
                'training_type' => 'stamina',
            ]);

            $career2 = Career::factory()->create(['character_id' => $character->id]);

            TrainingSession::factory()->count(8)->create([
                'career_id' => $career2->id,
                'training_type' => 'power',
            ]);

            $result = $this->service->compareCareers([$career->id, $career2->id]);

            expect($result['training_comparison'])->toHaveKey('training_type_distribution')
                ->and($result['training_comparison'])->toHaveKey('efficiency_comparison')
                ->and($result['training_comparison'])->toHaveKey('phase_comparison');
        });
    });

    describe('race comparison', function (): void {
        it('calculates race performance metrics', function (): void {
            $user = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $user->id]);

            $career1 = Career::factory()->create(['character_id' => $character->id]);
            $career2 = Career::factory()->create(['character_id' => $character->id]);

            Race::factory()->count(5)->create([
                'career_id' => $career1->id,
                'won_race' => true,
                'finish_position' => 1,
                'race_grade' => 'G1',
            ]);

            Race::factory()->count(5)->create([
                'career_id' => $career2->id,
                'won_race' => false,
                'finish_position' => 5,
                'race_grade' => 'G2',
            ]);

            $result = $this->service->compareCareers([$career1->id, $career2->id]);

            expect($result['race_comparison'])->toHaveKey('win_rates')
                ->and($result['race_comparison'])->toHaveKey('avg_positions')
                ->and($result['race_comparison'])->toHaveKey('race_grade_performance')
                ->and($result['race_comparison']['win_rates'][$career1->id])->toBe(100.0)
                ->and($result['race_comparison']['win_rates'][$career2->id])->toBe(0.0);
        });
    });
});
