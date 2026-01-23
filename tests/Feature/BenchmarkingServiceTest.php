<?php

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\BenchmarkingService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->service = new BenchmarkingService;
    $this->user = User::factory()->create();
    Cache::flush();
});

// =========================================================================
// COMMUNITY BENCHMARK DATA TESTS
// =========================================================================

describe('Community Benchmark Data', function () {
    it('returns insufficient data result when fewer than 10 careers exist', function () {
        $user = User::factory()->create();
        $character = Character::factory()->for($user)->create();
        Career::factory()->count(5)->for($character)->for($user)->completed()->create();

        $result = $this->service->getCommunityBenchmarks();

        expect($result)
            ->toHaveKey('error')
            ->error->toBe('insufficient_data')
            ->current_sample_size->toBe(5)
            ->required_sample_size->toBe(10);
    });

    it('calculates community benchmarks with sufficient data', function () {
        // Create multiple users with careers
        for ($u = 0; $u < 3; $u++) {
            $user = User::factory()->create();
            $character = Character::factory()->for($user)->create();

            for ($i = 0; $i < 5; $i++) {
                $career = Career::factory()->for($character)->for($user)->completed()->create([
                    'final_speed' => 800 + rand(-100, 100),
                    'final_stamina' => 700 + rand(-100, 100),
                    'final_power' => 600 + rand(-100, 100),
                    'final_guts' => 500 + rand(-100, 100),
                    'final_wit' => 400 + rand(-100, 100),
                ]);

                TrainingSession::factory()->count(10)->for($career)->create([
                    'character_id' => $character->id,
                ]);

                Race::factory()->count(5)->for($career)->create([
                    'character_id' => $character->id,
                ]);
            }
        }

        $result = $this->service->getCommunityBenchmarks();

        expect($result)
            ->toHaveKey('benchmarks')
            ->toHaveKey('sample_size')
            ->toHaveKey('last_updated')
            ->toHaveKey('scenario_benchmarks')
            ->toHaveKey('percentile_thresholds');

        expect($result['sample_size'])->toBeGreaterThanOrEqual(10);
    });

    it('calculates overall benchmarks with statistics', function () {
        // Create 12 careers for benchmarking
        for ($u = 0; $u < 4; $u++) {
            $user = User::factory()->create();
            $character = Character::factory()->for($user)->create();

            for ($i = 0; $i < 3; $i++) {
                $career = Career::factory()->for($character)->for($user)->completed()->create([
                    'final_speed' => 800,
                    'final_stamina' => 700,
                    'final_power' => 600,
                    'final_guts' => 500,
                    'final_wit' => 400,
                ]);

                TrainingSession::factory()->count(10)->for($career)->create([
                    'character_id' => $character->id,
                    'speed_gain' => 10,
                    'stamina_gain' => 8,
                    'power_gain' => 6,
                    'guts_gain' => 4,
                    'wit_gain' => 2,
                ]);

                Race::factory()->count(3)->for($career)->create([
                    'character_id' => $character->id,
                ]);
            }
        }

        $result = $this->service->getCommunityBenchmarks();

        expect($result['benchmarks'])
            ->toHaveKey('efficiency')
            ->toHaveKey('win_rate')
            ->toHaveKey('total_stats')
            ->toHaveKey('stat_averages');

        expect($result['benchmarks']['efficiency'])
            ->toHaveKey('mean')
            ->toHaveKey('median')
            ->toHaveKey('std_dev')
            ->toHaveKey('min')
            ->toHaveKey('max');
    });

    it('calculates benchmarks by scenario type', function () {
        // Create careers for different scenarios
        for ($u = 0; $u < 2; $u++) {
            $user = User::factory()->create();
            $character = Character::factory()->for($user)->create();

            // URA Finale careers
            for ($i = 0; $i < 3; $i++) {
                $career = Career::factory()->for($character)->for($user)->uraFinale()->completed()->create();
                TrainingSession::factory()->count(5)->for($career)->create([
                    'character_id' => $character->id,
                ]);
            }

            // Unity Cup careers
            for ($i = 0; $i < 3; $i++) {
                $career = Career::factory()->for($character)->for($user)->unityCup()->completed()->create();
                TrainingSession::factory()->count(5)->for($career)->create([
                    'character_id' => $character->id,
                ]);
            }
        }

        $result = $this->service->getCommunityBenchmarks();

        expect($result['scenario_benchmarks'])
            ->toHaveKey('ura_finale')
            ->toHaveKey('unity_cup');
    });

    it('calculates percentile thresholds', function () {
        // Create 15 careers with varying performance
        for ($u = 0; $u < 5; $u++) {
            $user = User::factory()->create();
            $character = Character::factory()->for($user)->create();

            for ($i = 0; $i < 3; $i++) {
                $career = Career::factory()->for($character)->for($user)->completed()->create();
                TrainingSession::factory()->count(10)->for($career)->create([
                    'character_id' => $character->id,
                    'speed_gain' => 5 + ($u * 3),
                    'stamina_gain' => 4 + ($u * 2),
                    'power_gain' => 3 + ($u * 2),
                    'guts_gain' => 2 + $u,
                    'wit_gain' => 1 + $u,
                ]);
            }
        }

        $result = $this->service->getCommunityBenchmarks();

        expect($result['percentile_thresholds'])
            ->toHaveKey('efficiency')
            ->toHaveKey('win_rate')
            ->toHaveKey('total_stats');

        // Check that percentile thresholds are in ascending order
        $efficiencyThresholds = $result['percentile_thresholds']['efficiency'];
        expect($efficiencyThresholds[10])->toBeLessThanOrEqual($efficiencyThresholds[50]);
        expect($efficiencyThresholds[50])->toBeLessThanOrEqual($efficiencyThresholds[90]);
    });

    it('caches community benchmark results', function () {
        // Create 10 careers
        for ($u = 0; $u < 5; $u++) {
            $user = User::factory()->create();
            $character = Character::factory()->for($user)->create();

            for ($i = 0; $i < 2; $i++) {
                $career = Career::factory()->for($character)->for($user)->completed()->create();
                TrainingSession::factory()->count(5)->for($career)->create([
                    'character_id' => $character->id,
                ]);
            }
        }

        // First call
        $result1 = $this->service->getCommunityBenchmarks();

        // Second call should return cached result
        $result2 = $this->service->getCommunityBenchmarks();

        expect($result1)->toEqual($result2);
    });
});

// =========================================================================
// USER PERFORMANCE COMPARISON TESTS
// =========================================================================

describe('User Performance Comparison', function () {
    it('returns no user data result when user has no careers', function () {
        $result = $this->service->compareUserPerformance($this->user);

        expect($result)
            ->toHaveKey('error')
            ->error->toBe('no_user_data');
    });

    it('compares user performance against community benchmarks', function () {
        // Create community data (10+ careers from other users)
        for ($u = 0; $u < 5; $u++) {
            $otherUser = User::factory()->create();
            $character = Character::factory()->for($otherUser)->create();

            for ($i = 0; $i < 3; $i++) {
                $career = Career::factory()->for($character)->for($otherUser)->completed()->create([
                    'final_speed' => 800,
                    'final_stamina' => 700,
                    'final_power' => 600,
                    'final_guts' => 500,
                    'final_wit' => 400,
                ]);

                TrainingSession::factory()->count(10)->for($career)->create([
                    'character_id' => $character->id,
                    'speed_gain' => 10,
                    'stamina_gain' => 8,
                    'power_gain' => 6,
                    'guts_gain' => 4,
                    'wit_gain' => 2,
                ]);

                Race::factory()->count(3)->for($career)->create([
                    'character_id' => $character->id,
                ]);
            }
        }

        // Create user's careers
        $character = Character::factory()->for($this->user)->create();
        for ($i = 0; $i < 3; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'final_speed' => 900,
                'final_stamina' => 800,
                'final_power' => 700,
                'final_guts' => 600,
                'final_wit' => 500,
            ]);

            TrainingSession::factory()->count(10)->for($career)->create([
                'character_id' => $character->id,
                'speed_gain' => 12,
                'stamina_gain' => 10,
                'power_gain' => 8,
                'guts_gain' => 6,
                'wit_gain' => 4,
            ]);

            Race::factory()->count(3)->for($career)->create([
                'character_id' => $character->id,
                'won_race' => true,
            ]);
        }

        $result = $this->service->compareUserPerformance($this->user);

        expect($result)
            ->toHaveKey('user_metrics')
            ->toHaveKey('benchmark_comparison')
            ->toHaveKey('percentile_rankings')
            ->toHaveKey('performance_summary')
            ->toHaveKey('improvement_areas');
    });

    it('calculates user metrics correctly', function () {
        $character = Character::factory()->for($this->user)->create();

        for ($i = 0; $i < 5; $i++) {
            $career = Career::factory()->for($character)->for($this->user)->completed()->create([
                'final_speed' => 850,
                'final_stamina' => 750,
                'final_power' => 650,
                'final_guts' => 550,
                'final_wit' => 450,
            ]);

            TrainingSession::factory()->count(10)->for($career)->create([
                'character_id' => $character->id,
                'speed_gain' => 10,
                'stamina_gain' => 8,
                'power_gain' => 6,
                'guts_gain' => 4,
                'wit_gain' => 2,
            ]);

            Race::factory()->count(3)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        // Create community data
        for ($u = 0; $u < 5; $u++) {
            $otherUser = User::factory()->create();
            $otherCharacter = Character::factory()->for($otherUser)->create();

            for ($i = 0; $i < 2; $i++) {
                $career = Career::factory()->for($otherCharacter)->for($otherUser)->completed()->create();
                TrainingSession::factory()->count(5)->for($career)->create([
                    'character_id' => $otherCharacter->id,
                ]);
            }
        }

        $result = $this->service->compareUserPerformance($this->user);

        expect($result['user_metrics'])
            ->toHaveKey('career_count')
            ->toHaveKey('average_efficiency')
            ->toHaveKey('average_win_rate')
            ->toHaveKey('average_total_stats')
            ->toHaveKey('stat_averages')
            ->toHaveKey('best_career')
            ->toHaveKey('recent_trend');

        expect($result['user_metrics']['career_count'])->toBe(5);
    });
});

// =========================================================================
// PERCENTILE RANKING TESTS
// =========================================================================

describe('Percentile Rankings', function () {
    it('calculates user percentile rankings', function () {
        // Create community data with varying performance
        for ($u = 0; $u < 10; $u++) {
            $otherUser = User::factory()->create();
            $character = Character::factory()->for($otherUser)->create();

            $career = Career::factory()->for($character)->for($otherUser)->completed()->create();
            TrainingSession::factory()->count(10)->for($career)->create([
                'character_id' => $character->id,
                'speed_gain' => 5 + $u,
                'stamina_gain' => 4 + $u,
                'power_gain' => 3 + $u,
                'guts_gain' => 2 + $u,
                'wit_gain' => 1 + $u,
            ]);
        }

        // Create user's career with high performance
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->completed()->create();
        TrainingSession::factory()->count(10)->for($career)->create([
            'character_id' => $character->id,
            'speed_gain' => 20,
            'stamina_gain' => 18,
            'power_gain' => 16,
            'guts_gain' => 14,
            'wit_gain' => 12,
        ]);

        $result = $this->service->compareUserPerformance($this->user);

        expect($result['percentile_rankings'])
            ->toHaveKey('efficiency_percentile')
            ->toHaveKey('win_rate_percentile')
            ->toHaveKey('total_stats_percentile')
            ->toHaveKey('overall_percentile')
            ->toHaveKey('ranking_tier');

        // High performer should have high percentile
        expect($result['percentile_rankings']['efficiency_percentile'])->toBeGreaterThanOrEqual(50);
    });

    it('determines ranking tier based on percentile', function () {
        // Create community data
        for ($u = 0; $u < 10; $u++) {
            $otherUser = User::factory()->create();
            $character = Character::factory()->for($otherUser)->create();

            $career = Career::factory()->for($character)->for($otherUser)->completed()->create();
            TrainingSession::factory()->count(10)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        // Create user's career
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->completed()->create();
        TrainingSession::factory()->count(10)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $result = $this->service->compareUserPerformance($this->user);

        expect($result['percentile_rankings']['ranking_tier'])->toBeIn([
            'legendary',
            'elite',
            'expert',
            'advanced',
            'intermediate',
            'developing',
            'beginner',
        ]);
    });
});

// =========================================================================
// PERFORMANCE SUMMARY TESTS
// =========================================================================

describe('Performance Summary', function () {
    it('generates performance summary with strengths and weaknesses', function () {
        // Create community data
        for ($u = 0; $u < 10; $u++) {
            $otherUser = User::factory()->create();
            $character = Character::factory()->for($otherUser)->create();

            $career = Career::factory()->for($character)->for($otherUser)->completed()->create();
            TrainingSession::factory()->count(10)->for($career)->create([
                'character_id' => $character->id,
                'speed_gain' => 10,
                'stamina_gain' => 8,
                'power_gain' => 6,
                'guts_gain' => 4,
                'wit_gain' => 2,
            ]);
            Race::factory()->count(3)->for($career)->create([
                'character_id' => $character->id,
            ]);
        }

        // Create user's career with above-average performance
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->completed()->create();
        TrainingSession::factory()->count(10)->for($career)->create([
            'character_id' => $character->id,
            'speed_gain' => 15,
            'stamina_gain' => 12,
            'power_gain' => 10,
            'guts_gain' => 8,
            'wit_gain' => 6,
        ]);
        Race::factory()->count(3)->for($career)->create([
            'character_id' => $character->id,
            'won_race' => true,
        ]);

        $result = $this->service->compareUserPerformance($this->user);

        expect($result['performance_summary'])
            ->toHaveKey('overall_assessment')
            ->toHaveKey('strengths')
            ->toHaveKey('weaknesses')
            ->toHaveKey('notable_achievements');

        expect($result['performance_summary']['overall_assessment'])->toBeString()->not->toBeEmpty();
    });

    it('identifies improvement areas', function () {
        // Create community data with high performance
        for ($u = 0; $u < 10; $u++) {
            $otherUser = User::factory()->create();
            $character = Character::factory()->for($otherUser)->create();

            $career = Career::factory()->for($character)->for($otherUser)->completed()->create();
            TrainingSession::factory()->count(10)->for($career)->create([
                'character_id' => $character->id,
                'speed_gain' => 15,
                'stamina_gain' => 12,
                'power_gain' => 10,
                'guts_gain' => 8,
                'wit_gain' => 6,
            ]);
        }

        // Create user's career with below-average performance
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->completed()->create();
        TrainingSession::factory()->count(10)->for($career)->create([
            'character_id' => $character->id,
            'speed_gain' => 5,
            'stamina_gain' => 4,
            'power_gain' => 3,
            'guts_gain' => 2,
            'wit_gain' => 1,
        ]);

        $result = $this->service->compareUserPerformance($this->user);

        expect($result['improvement_areas'])->toBeArray();
    });
});

// =========================================================================
// BENCHMARK TREND ANALYSIS TESTS
// =========================================================================

describe('Benchmark Trend Analysis', function () {
    it('returns insufficient data when not enough recent careers', function () {
        $result = $this->service->analyzeBenchmarkTrends();

        expect($result['community_trend'])->toBe('insufficient_data');
    });

    it('analyzes benchmark trends with sufficient data', function () {
        // Create careers over multiple months
        for ($month = 0; $month < 3; $month++) {
            for ($u = 0; $u < 4; $u++) {
                $user = User::factory()->create();
                $character = Character::factory()->for($user)->create();

                $career = Career::factory()->for($character)->for($user)->completed()->create([
                    'completed_at' => now()->subMonths($month),
                ]);

                TrainingSession::factory()->count(10)->for($career)->create([
                    'character_id' => $character->id,
                    'training_type' => ['speed', 'stamina', 'power', 'guts', 'wit'][rand(0, 4)],
                    'speed_gain' => 10 + $month,
                    'stamina_gain' => 8 + $month,
                    'power_gain' => 6 + $month,
                    'guts_gain' => 4 + $month,
                    'wit_gain' => 2 + $month,
                ]);

                Race::factory()->count(3)->for($career)->create([
                    'character_id' => $character->id,
                ]);
            }
        }

        $result = $this->service->analyzeBenchmarkTrends();

        expect($result)
            ->toHaveKey('community_trend')
            ->toHaveKey('efficiency_trend')
            ->toHaveKey('win_rate_trend')
            ->toHaveKey('meta_shifts')
            ->toHaveKey('trend_analysis');

        expect($result['community_trend'])->toBeIn(['improving', 'declining', 'stable', 'insufficient_data']);
    });

    it('identifies meta shifts in training preferences', function () {
        // Create careers with different training preferences over time
        for ($month = 0; $month < 3; $month++) {
            for ($u = 0; $u < 4; $u++) {
                $user = User::factory()->create();
                $character = Character::factory()->for($user)->create();

                $career = Career::factory()->for($character)->for($user)->completed()->create([
                    'completed_at' => now()->subMonths($month),
                ]);

                // Earlier months focus on speed, later months focus on stamina
                $trainingType = $month < 2 ? 'speed' : 'stamina';

                TrainingSession::factory()->count(10)->for($career)->create([
                    'character_id' => $character->id,
                    'training_type' => $trainingType,
                ]);
            }
        }

        $result = $this->service->analyzeBenchmarkTrends();

        expect($result['meta_shifts'])->toBeArray();
        expect($result['trend_analysis'])->toBeString();
    });
});

// =========================================================================
// CACHE MANAGEMENT TESTS
// =========================================================================

describe('Cache Management', function () {
    it('clears benchmark cache', function () {
        // Create community data
        for ($u = 0; $u < 5; $u++) {
            $user = User::factory()->create();
            $character = Character::factory()->for($user)->create();

            for ($i = 0; $i < 2; $i++) {
                $career = Career::factory()->for($character)->for($user)->completed()->create();
                TrainingSession::factory()->count(5)->for($career)->create([
                    'character_id' => $character->id,
                ]);
            }
        }

        // Populate cache
        $this->service->getCommunityBenchmarks();

        // Clear cache
        $this->service->clearCache();

        // Verify cache is cleared
        expect(Cache::has('benchmarks:community'))->toBeFalse();
        expect(Cache::has('benchmarks:trends'))->toBeFalse();
    });

    it('clears user-specific benchmark cache', function () {
        // Create community data
        for ($u = 0; $u < 5; $u++) {
            $user = User::factory()->create();
            $character = Character::factory()->for($user)->create();

            for ($i = 0; $i < 2; $i++) {
                $career = Career::factory()->for($character)->for($user)->completed()->create();
                TrainingSession::factory()->count(5)->for($career)->create([
                    'character_id' => $character->id,
                ]);
            }
        }

        // Create user's career
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->completed()->create();
        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        // Populate cache
        $this->service->compareUserPerformance($this->user);

        // Clear user-specific cache
        $this->service->clearCache($this->user);

        // Verify user cache is cleared
        expect(Cache::has("benchmarks:user_comparison:{$this->user->id}"))->toBeFalse();
    });
});
