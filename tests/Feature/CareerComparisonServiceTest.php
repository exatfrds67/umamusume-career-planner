<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\CareerComparisonService;

beforeEach(function () {
    $this->service = new CareerComparisonService;
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
});

describe('Career Comparison Service', function () {
    describe('compareCareers', function () {
        it('requires at least 2 careers for comparison', function () {
            $career = Career::factory()->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
            ]);

            $result = $this->service->compareCareers([$career->id]);

            expect($result)->toHaveKey('message')
                ->and($result['careers'])->toBeEmpty();
        });

        it('compares multiple careers successfully', function () {
            $careers = Career::factory()->count(3)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
                'scenario_type' => 'ura_finale',
            ]);

            // Add training sessions to each career
            foreach ($careers as $career) {
                TrainingSession::factory()->count(10)->create([
                    'career_id' => $career->id,
                    'character_id' => $this->character->id,
                ]);
            }

            $careerIds = $careers->pluck('id')->toArray();
            $result = $this->service->compareCareers($careerIds);

            expect($result)->toHaveKey('careers')
                ->and($result)->toHaveKey('comparison_summary')
                ->and($result)->toHaveKey('stat_comparison')
                ->and($result)->toHaveKey('training_comparison')
                ->and($result)->toHaveKey('race_comparison')
                ->and($result)->toHaveKey('key_differences')
                ->and($result)->toHaveKey('best_performer')
                ->and(count($result['careers']))->toBe(3);
        });

        it('calculates comparison summary correctly', function () {
            $careers = Career::factory()->count(2)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
            ]);

            foreach ($careers as $career) {
                TrainingSession::factory()->count(5)->create([
                    'career_id' => $career->id,
                    'character_id' => $this->character->id,
                    'speed_gain' => 10,
                    'stamina_gain' => 8,
                    'power_gain' => 6,
                    'guts_gain' => 4,
                    'wit_gain' => 2,
                ]);
            }

            $careerIds = $careers->pluck('id')->toArray();
            $result = $this->service->compareCareers($careerIds);

            expect($result['comparison_summary'])->toHaveKey('total_careers')
                ->and($result['comparison_summary'])->toHaveKey('avg_efficiency')
                ->and($result['comparison_summary'])->toHaveKey('avg_total_stats')
                ->and($result['comparison_summary']['total_careers'])->toBe(2);
        });

        it('identifies best performer', function () {
            // Create two careers with different performance
            $career1 = Career::factory()->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
            ]);

            $career2 = Career::factory()->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
            ]);

            // Career 1 has better training
            TrainingSession::factory()->count(10)->create([
                'career_id' => $career1->id,
                'character_id' => $this->character->id,
                'speed_gain' => 15,
                'stamina_gain' => 12,
                'power_gain' => 10,
                'guts_gain' => 8,
                'wit_gain' => 5,
                'training_failed' => false,
            ]);

            // Career 2 has worse training
            TrainingSession::factory()->count(10)->create([
                'career_id' => $career2->id,
                'character_id' => $this->character->id,
                'speed_gain' => 5,
                'stamina_gain' => 4,
                'power_gain' => 3,
                'guts_gain' => 2,
                'wit_gain' => 1,
                'training_failed' => false,
            ]);

            $result = $this->service->compareCareers([$career1->id, $career2->id]);

            expect($result['best_performer'])->toHaveKey('career_id')
                ->and($result['best_performer'])->toHaveKey('score')
                ->and($result['best_performer']['career_id'])->toBe($career1->id);
        });
    });

    describe('identifySuccessPatterns', function () {
        it('identifies training patterns from careers', function () {
            $careers = Career::factory()->count(3)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
                'completed_at' => now(),
            ]);

            foreach ($careers as $career) {
                TrainingSession::factory()->count(15)->create([
                    'career_id' => $career->id,
                    'character_id' => $this->character->id,
                    'training_type' => fake()->randomElement(['speed', 'stamina', 'power', 'guts', 'wit']),
                ]);
            }

            $careerIds = $careers->pluck('id')->toArray();
            $result = $this->service->identifySuccessPatterns($careerIds);

            expect($result)->toHaveKey('training_patterns')
                ->and($result)->toHaveKey('race_strategy_patterns')
                ->and($result)->toHaveKey('decision_sequences')
                ->and($result)->toHaveKey('success_correlations')
                ->and($result)->toHaveKey('recommended_patterns');
        });

        it('returns empty result for insufficient data', function () {
            $result = $this->service->identifySuccessPatterns([]);

            expect($result['recommended_patterns'])->toContain('Insufficient data for pattern analysis');
        });
    });

    describe('analyzeSuccessFactors', function () {
        it('requires minimum 5 careers for analysis', function () {
            $careers = Career::factory()->count(3)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
            ]);

            $careerIds = $careers->pluck('id')->toArray();
            $result = $this->service->analyzeSuccessFactors($careerIds);

            expect($result['actionable_insights'])->toContain('Insufficient data for success factor analysis (minimum 5 careers required)');
        });

        it('calculates factor importance with sufficient data', function () {
            $careers = Career::factory()->count(6)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
                'completed_at' => now(),
            ]);

            foreach ($careers as $index => $career) {
                // Create varying performance levels
                $multiplier = $index + 1;
                TrainingSession::factory()->count(10)->create([
                    'career_id' => $career->id,
                    'character_id' => $this->character->id,
                    'speed_gain' => 5 * $multiplier,
                    'stamina_gain' => 4 * $multiplier,
                    'power_gain' => 3 * $multiplier,
                    'guts_gain' => 2 * $multiplier,
                    'wit_gain' => 1 * $multiplier,
                ]);
            }

            $careerIds = $careers->pluck('id')->toArray();
            $result = $this->service->analyzeSuccessFactors($careerIds);

            expect($result)->toHaveKey('success_factors')
                ->and($result)->toHaveKey('factor_importance')
                ->and($result)->toHaveKey('correlation_matrix')
                ->and($result)->toHaveKey('actionable_insights');
        });
    });

    describe('performStatisticalTests', function () {
        it('requires minimum 5 careers for statistical testing', function () {
            $careers = Career::factory()->count(3)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
            ]);

            $careerIds = $careers->pluck('id')->toArray();
            $result = $this->service->performStatisticalTests($careerIds);

            expect($result['overall_significance']['sufficient_data'])->toBeFalse();
        });

        it('performs t-tests with sufficient data', function () {
            // Create successful careers
            $successfulCareers = Career::factory()->count(3)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
                'completed_at' => now(),
                'performance_analysis' => ['goals_achieved' => true],
            ]);

            // Create unsuccessful careers
            $unsuccessfulCareers = Career::factory()->count(3)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
                'completed_at' => now(),
                'performance_analysis' => ['goals_achieved' => false],
            ]);

            // Add training data
            foreach ($successfulCareers as $career) {
                TrainingSession::factory()->count(10)->create([
                    'career_id' => $career->id,
                    'character_id' => $this->character->id,
                    'speed_gain' => 15,
                    'stamina_gain' => 12,
                ]);
            }

            foreach ($unsuccessfulCareers as $career) {
                TrainingSession::factory()->count(10)->create([
                    'career_id' => $career->id,
                    'character_id' => $this->character->id,
                    'speed_gain' => 5,
                    'stamina_gain' => 4,
                ]);
            }

            $allCareerIds = $successfulCareers->pluck('id')
                ->merge($unsuccessfulCareers->pluck('id'))
                ->toArray();

            $result = $this->service->performStatisticalTests($allCareerIds);

            expect($result)->toHaveKey('t_tests')
                ->and($result)->toHaveKey('chi_square_tests')
                ->and($result)->toHaveKey('effect_sizes')
                ->and($result)->toHaveKey('confidence_intervals')
                ->and($result)->toHaveKey('overall_significance')
                ->and($result['overall_significance']['sufficient_data'])->toBeTrue();
        });

        it('calculates effect sizes correctly', function () {
            // Create successful careers (goals_achieved = true)
            $successfulCareers = Career::factory()->count(3)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
                'completed_at' => now(),
                'performance_analysis' => ['goals_achieved' => true],
            ]);

            // Create unsuccessful careers (goals_achieved = false)
            $unsuccessfulCareers = Career::factory()->count(3)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
                'completed_at' => now(),
                'performance_analysis' => ['goals_achieved' => false],
            ]);

            // Add high-performing training to successful careers
            foreach ($successfulCareers as $career) {
                TrainingSession::factory()->count(10)->create([
                    'career_id' => $career->id,
                    'character_id' => $this->character->id,
                    'speed_gain' => 20,
                    'stamina_gain' => 18,
                    'power_gain' => 15,
                    'guts_gain' => 12,
                    'wit_gain' => 10,
                ]);
            }

            // Add low-performing training to unsuccessful careers
            foreach ($unsuccessfulCareers as $career) {
                TrainingSession::factory()->count(10)->create([
                    'career_id' => $career->id,
                    'character_id' => $this->character->id,
                    'speed_gain' => 5,
                    'stamina_gain' => 4,
                    'power_gain' => 3,
                    'guts_gain' => 2,
                    'wit_gain' => 1,
                ]);
            }

            $allCareerIds = $successfulCareers->pluck('id')
                ->merge($unsuccessfulCareers->pluck('id'))
                ->toArray();

            $result = $this->service->performStatisticalTests($allCareerIds);

            // Verify the result structure
            expect($result)->toHaveKey('effect_sizes')
                ->and($result['overall_significance']['sufficient_data'])->toBeTrue();

            // With distinct successful/unsuccessful careers, effect_sizes should not be empty
            expect($result['effect_sizes'])->not->toBeEmpty();

            // Verify each effect size entry has the correct structure
            foreach ($result['effect_sizes'] as $factor => $data) {
                expect($data)->toHaveKey('cohens_d')
                    ->and($data)->toHaveKey('effect_size')
                    ->and($data)->toHaveKey('interpretation')
                    ->and($data['effect_size'])->toBeIn(['negligible', 'small', 'medium', 'large']);
            }
        });
    });

    describe('getComprehensiveAnalysis', function () {
        it('returns all analysis components', function () {
            $careers = Career::factory()->count(6)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
                'completed_at' => now(),
            ]);

            foreach ($careers as $career) {
                TrainingSession::factory()->count(10)->create([
                    'career_id' => $career->id,
                    'character_id' => $this->character->id,
                ]);

                Race::factory()->count(3)->create([
                    'career_id' => $career->id,
                    'character_id' => $this->character->id,
                ]);
            }

            $careerIds = $careers->pluck('id')->toArray();
            $result = $this->service->getComprehensiveAnalysis($careerIds);

            expect($result)->toHaveKey('comparison')
                ->and($result)->toHaveKey('patterns')
                ->and($result)->toHaveKey('success_factors')
                ->and($result)->toHaveKey('statistical_tests');
        });
    });

    describe('clearCache', function () {
        it('clears cache without errors', function () {
            $careers = Career::factory()->count(2)->create([
                'character_id' => $this->character->id,
                'user_id' => $this->user->id,
            ]);

            $careerIds = $careers->pluck('id')->toArray();

            // This should not throw any exceptions
            $this->service->clearCache($careerIds);

            expect(true)->toBeTrue();
        });
    });
});

describe('Career Comparison API', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->character = Character::factory()->create(['user_id' => $this->user->id]);
    });

    it('requires authentication for comparison endpoints', function () {
        $response = $this->postJson('/api/careers/comparison/compare', [
            'career_ids' => [1, 2],
        ]);

        $response->assertUnauthorized();
    });

    it('validates career_ids are required', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/comparison/compare', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['career_ids']);
    });

    it('validates minimum 2 careers for comparison', function () {
        $career = Career::factory()->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/comparison/compare', [
                'career_ids' => [$career->id],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['career_ids']);
    });

    it('compares careers successfully via API', function () {
        $careers = Career::factory()->count(2)->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
        ]);

        foreach ($careers as $career) {
            TrainingSession::factory()->count(5)->create([
                'career_id' => $career->id,
                'character_id' => $this->character->id,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/comparison/compare', [
                'career_ids' => $careers->pluck('id')->toArray(),
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'careers',
                    'comparison_summary',
                    'stat_comparison',
                    'training_comparison',
                    'race_comparison',
                    'key_differences',
                    'best_performer',
                ],
            ]);
    });

    it('returns patterns via API', function () {
        $careers = Career::factory()->count(3)->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
        ]);

        foreach ($careers as $career) {
            TrainingSession::factory()->count(10)->create([
                'career_id' => $career->id,
                'character_id' => $this->character->id,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/comparison/patterns', [
                'career_ids' => $careers->pluck('id')->toArray(),
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'training_patterns',
                    'race_strategy_patterns',
                    'decision_sequences',
                    'success_correlations',
                    'recommended_patterns',
                ],
            ]);
    });

    it('validates minimum 5 careers for success factors', function () {
        $careers = Career::factory()->count(3)->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/comparison/success-factors', [
                'career_ids' => $careers->pluck('id')->toArray(),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['career_ids']);
    });

    it('returns comprehensive analysis via API', function () {
        $careers = Career::factory()->count(6)->create([
            'character_id' => $this->character->id,
            'user_id' => $this->user->id,
        ]);

        foreach ($careers as $career) {
            TrainingSession::factory()->count(5)->create([
                'career_id' => $career->id,
                'character_id' => $this->character->id,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->postJson('/api/careers/comparison/comprehensive', [
                'career_ids' => $careers->pluck('id')->toArray(),
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'comparison',
                    'patterns',
                    'success_factors',
                    'statistical_tests',
                ],
            ]);
    });
});
