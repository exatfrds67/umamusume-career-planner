<?php

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\CareerAnalyticsService;
use App\Services\CareerReportingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;


beforeEach(function () {
    $this->analyticsService = new CareerAnalyticsService;
    $this->service = new CareerReportingService($this->analyticsService);
    $this->user = User::factory()->create();
    Cache::flush();
});

// =========================================================================
// CAREER SUMMARY REPORT TESTS
// =========================================================================

describe('Career Summary Report Generation', function () {
    it('generates comprehensive career summary report', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(10)->for($career)->create([
            'character_id' => $character->id,
        ]);

        Race::factory()->count(3)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report)
            ->toHaveKey('report_metadata')
            ->toHaveKey('executive_summary')
            ->toHaveKey('performance_overview')
            ->toHaveKey('training_analysis')
            ->toHaveKey('race_analysis')
            ->toHaveKey('skill_analysis')
            ->toHaveKey('key_insights')
            ->toHaveKey('recommendations')
            ->toHaveKey('statistical_summary');
    });

    it('includes correct report metadata', function () {
        $character = Character::factory()->for($this->user)->create(['name' => 'Test Character']);
        $career = Career::factory()->for($character)->for($this->user)->create([
            'scenario_type' => 'ura_finale',
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['report_metadata'])
            ->toHaveKey('report_id')
            ->toHaveKey('generated_at')
            ->career_id->toBe($career->id)
            ->character_name->toBe('Test Character')
            ->scenario_type->toBe('ura_finale')
            ->report_version->toBe('1.0.0');
    });

    it('calculates executive summary correctly', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create([
            'current_turn' => 36,
            'completed_at' => null,
        ]);

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
            'speed_gain' => 10,
            'stamina_gain' => 5,
            'power_gain' => 5,
            'guts_gain' => 3,
            'wit_gain' => 2,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['executive_summary'])
            ->career_status->toBe('in_progress')
            ->total_turns->toBe(36)
            ->toHaveKey('overall_grade')
            ->toHaveKey('completion_percentage')
            ->toHaveKey('highlight_stats')
            ->toHaveKey('key_achievements');
    });

    it('builds performance overview with stat distribution', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(10)->for($career)->create([
            'character_id' => $character->id,
            'speed_gain' => 15,
            'stamina_gain' => 10,
            'power_gain' => 8,
            'guts_gain' => 5,
            'wit_gain' => 3,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['performance_overview'])
            ->toHaveKey('efficiency_rating')
            ->toHaveKey('stat_distribution')
            ->toHaveKey('training_success_rate')
            ->toHaveKey('race_win_rate')
            ->toHaveKey('sp_earned')
            ->toHaveKey('phase_performance');

        expect($report['performance_overview']['stat_distribution'])
            ->speed->toBe(150)
            ->stamina->toBe(100)
            ->power->toBe(80)
            ->guts->toBe(50)
            ->wit->toBe(30);
    });

    it('caches career summary report', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $report1 = $this->service->generateCareerSummaryReport($career);
        $report2 = $this->service->generateCareerSummaryReport($career);

        expect($report1)->toEqual($report2);
    });
});

// =========================================================================
// TRAINING ANALYSIS TESTS
// =========================================================================

describe('Training Analysis', function () {
    it('analyzes training type breakdown', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->trainingType('speed')->create([
            'character_id' => $character->id,
        ]);
        TrainingSession::factory()->count(3)->for($career)->trainingType('stamina')->create([
            'character_id' => $character->id,
        ]);
        TrainingSession::factory()->count(2)->for($career)->trainingType('power')->create([
            'character_id' => $character->id,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['training_analysis'])
            ->total_sessions->toBe(10)
            ->toHaveKey('training_type_breakdown')
            ->toHaveKey('best_training_type')
            ->toHaveKey('worst_training_type')
            ->toHaveKey('friendship_training_stats')
            ->toHaveKey('failure_analysis');

        expect($report['training_analysis']['training_type_breakdown']['speed']['count'])->toBe(5);
        expect($report['training_analysis']['training_type_breakdown']['stamina']['count'])->toBe(3);
        expect($report['training_analysis']['training_type_breakdown']['power']['count'])->toBe(2);
    });

    it('tracks training failures', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(8)->for($career)->create([
            'character_id' => $character->id,
            'training_failed' => false,
        ]);
        TrainingSession::factory()->count(2)->for($career)->create([
            'character_id' => $character->id,
            'training_failed' => true,
            'training_type' => 'speed',
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['training_analysis']['failure_analysis'])
            ->total_failures->toBe(2)
            ->failure_rate->toBe(20.0);
    });

    it('calculates friendship training statistics', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(7)->for($career)->create([
            'character_id' => $character->id,
            'friendship_training' => false,
        ]);
        TrainingSession::factory()->count(3)->for($career)->create([
            'character_id' => $character->id,
            'friendship_training' => true,
            'speed_gain' => 20,
            'stamina_gain' => 15,
            'power_gain' => 10,
            'guts_gain' => 5,
            'wit_gain' => 5,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['training_analysis']['friendship_training_stats'])
            ->count->toBe(3)
            ->percentage->toBe(30.0);
    });
});

// =========================================================================
// RACE ANALYSIS TESTS
// =========================================================================

describe('Race Analysis', function () {
    it('analyzes race performance', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        Race::factory()->count(3)->for($career)->create([
            'character_id' => $character->id,
            'won_race' => true,
            'finish_position' => 1,
        ]);
        Race::factory()->count(2)->for($career)->create([
            'character_id' => $character->id,
            'won_race' => false,
            'finish_position' => 3,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['race_analysis'])
            ->total_races->toBe(5)
            ->wins->toBe(3)
            ->win_rate->toBe(60.0)
            ->toHaveKey('avg_position')
            ->toHaveKey('performance_by_grade')
            ->toHaveKey('performance_by_distance');
    });

    it('analyzes performance by race grade', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        Race::factory()->count(2)->for($career)->create([
            'character_id' => $character->id,
            'race_grade' => 'G1',
            'won_race' => true,
        ]);
        Race::factory()->count(3)->for($career)->create([
            'character_id' => $character->id,
            'race_grade' => 'G2',
            'won_race' => false,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['race_analysis']['performance_by_grade']['G1'])
            ->count->toBe(2)
            ->wins->toBe(2)
            ->win_rate->toBe(100.0);

        expect($report['race_analysis']['performance_by_grade']['G2'])
            ->count->toBe(3)
            ->wins->toBe(0)
            ->win_rate->toBe(0.0);
    });

    it('identifies best and worst races', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        Race::factory()->for($career)->create([
            'character_id' => $character->id,
            'race_name' => 'Best Race',
            'race_grade' => 'G1',
            'won_race' => true,
            'finish_position' => 1,
            'turn_number' => 30,
        ]);
        Race::factory()->for($career)->create([
            'character_id' => $character->id,
            'race_name' => 'Worst Race',
            'race_grade' => 'G3',
            'won_race' => false,
            'finish_position' => 10,
            'turn_number' => 40,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['race_analysis']['best_race'])
            ->race_name->toBe('Best Race')
            ->grade->toBe('G1')
            ->position->toBe(1);

        expect($report['race_analysis']['worst_race'])
            ->race_name->toBe('Worst Race')
            ->position->toBe(10);
    });
});

// =========================================================================
// EXPORT FUNCTIONALITY TESTS
// =========================================================================

describe('Export Functionality', function () {
    it('exports report to JSON format', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $json = $this->service->exportToJson($career);

        expect($json)->toBeString();

        $decoded = json_decode($json, true);
        expect($decoded)
            ->toBeArray()
            ->toHaveKey('report_metadata')
            ->toHaveKey('executive_summary')
            ->toHaveKey('key_insights')
            ->toHaveKey('recommendations');
    });

    it('exports report to CSV format', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $csv = $this->service->exportToCsv($career);

        expect($csv)
            ->toBeArray()
            ->toHaveKey('headers')
            ->toHaveKey('rows');

        expect($csv['headers'])->toBe(['Metric', 'Value', 'Category']);
        expect($csv['rows'])->toBeArray()->not->toBeEmpty();
    });

    it('exports report to PDF format', function () {
        $character = Character::factory()->for($this->user)->create(['name' => 'Test Character']);
        $career = Career::factory()->for($character)->for($this->user)->create([
            'scenario_type' => 'ura_finale',
        ]);

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        $pdf = $this->service->exportToPdfFormat($career);

        expect($pdf)
            ->toBeArray()
            ->toHaveKey('title')
            ->toHaveKey('subtitle')
            ->toHaveKey('sections');

        expect($pdf['title'])->toBe('Career Summary Report');
        expect($pdf['subtitle'])->toContain('Test Character');
        expect($pdf['sections'])->toBeArray()->not->toBeEmpty();
    });
});

// =========================================================================
// CHARACTER REPORT TESTS
// =========================================================================

describe('Character Report Generation', function () {
    it('generates comprehensive character report', function () {
        $character = Character::factory()->for($this->user)->create();

        // Create multiple careers
        $career1 = Career::factory()->for($character)->for($this->user)->create();
        $career2 = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career1)->create([
            'character_id' => $character->id,
        ]);
        TrainingSession::factory()->count(5)->for($career2)->create([
            'character_id' => $character->id,
        ]);

        $report = $this->service->generateCharacterReport($character);

        expect($report)
            ->toHaveKey('character_info')
            ->toHaveKey('career_history')
            ->toHaveKey('aggregate_statistics')
            ->toHaveKey('performance_trends')
            ->toHaveKey('improvement_areas')
            ->toHaveKey('strengths');
    });

    it('includes correct character info', function () {
        $character = Character::factory()->for($this->user)->create([
            'name' => 'Test Character',
            'scenario_type' => 'ura_finale',
        ]);

        Career::factory()->count(3)->for($character)->for($this->user)->create();

        $report = $this->service->generateCharacterReport($character);

        expect($report['character_info'])
            ->id->toBe($character->id)
            ->name->toBe('Test Character')
            ->total_careers->toBe(3);
    });

    it('calculates aggregate statistics across careers', function () {
        $character = Character::factory()->for($this->user)->create();

        $career1 = Career::factory()->for($character)->for($this->user)->create();
        $career2 = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(10)->for($career1)->create([
            'character_id' => $character->id,
        ]);
        TrainingSession::factory()->count(10)->for($career2)->create([
            'character_id' => $character->id,
        ]);

        Race::factory()->count(3)->for($career1)->create([
            'character_id' => $character->id,
            'won_race' => true,
        ]);
        Race::factory()->count(2)->for($career2)->create([
            'character_id' => $character->id,
            'won_race' => false,
        ]);

        $report = $this->service->generateCharacterReport($character);

        expect($report['aggregate_statistics'])
            ->total_training_sessions->toBe(20)
            ->total_races->toBe(5)
            ->total_wins->toBe(3)
            ->overall_win_rate->toBe(60.0);
    });

    it('identifies performance trends', function () {
        $character = Character::factory()->for($this->user)->create();

        // Create careers with improving efficiency
        $career1 = Career::factory()->for($character)->for($this->user)->create([
            'created_at' => now()->subDays(10),
        ]);
        $career2 = Career::factory()->for($character)->for($this->user)->create([
            'created_at' => now()->subDays(5),
        ]);

        TrainingSession::factory()->count(10)->for($career1)->create([
            'character_id' => $character->id,
            'speed_gain' => 5,
            'stamina_gain' => 5,
            'power_gain' => 5,
            'guts_gain' => 5,
            'wit_gain' => 5,
        ]);
        TrainingSession::factory()->count(10)->for($career2)->create([
            'character_id' => $character->id,
            'speed_gain' => 10,
            'stamina_gain' => 10,
            'power_gain' => 10,
            'guts_gain' => 10,
            'wit_gain' => 10,
        ]);

        $report = $this->service->generateCharacterReport($character);

        expect($report['performance_trends'])
            ->toHaveKey('efficiency_trend')
            ->toHaveKey('win_rate_trend')
            ->toHaveKey('trend_direction')
            ->toHaveKey('improvement_rate');
    });
});

// =========================================================================
// CACHE MANAGEMENT TESTS
// =========================================================================

describe('Cache Management', function () {
    it('clears career report cache', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        // Generate report to cache it
        $this->service->generateCareerSummaryReport($career);

        // Clear cache
        $this->service->clearCareerReportCache($career);

        // Verify cache is cleared by checking if new report is generated
        expect(Cache::has("report:career_summary:{$career->id}"))->toBeFalse();
    });

    it('clears character report cache', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
        ]);

        // Generate reports to cache them
        $this->service->generateCharacterReport($character);
        $this->service->generateCareerSummaryReport($career);

        // Clear character cache
        $this->service->clearCharacterReportCache($character);

        // Verify caches are cleared
        expect(Cache::has("report:character:{$character->id}"))->toBeFalse();
        expect(Cache::has("report:career_summary:{$career->id}"))->toBeFalse();
    });
});

// =========================================================================
// STATISTICAL ANALYSIS TESTS
// =========================================================================

describe('Statistical Analysis', function () {
    it('calculates stat statistics correctly', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create sessions with known values for predictable statistics
        TrainingSession::factory()->for($career)->create([
            'character_id' => $character->id,
            'speed_gain' => 10,
        ]);
        TrainingSession::factory()->for($career)->create([
            'character_id' => $character->id,
            'speed_gain' => 20,
        ]);
        TrainingSession::factory()->for($career)->create([
            'character_id' => $character->id,
            'speed_gain' => 30,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['statistical_summary']['stat_statistics']['speed'])
            ->mean->toBe(20.0)
            ->median->toBe(20.0)
            ->min->toBe(10)
            ->max->toBe(30)
            ->total->toBe(60);
    });

    it('performs correlation analysis', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create training sessions before races
        TrainingSession::factory()->count(5)->for($career)->create([
            'character_id' => $character->id,
            'turn_number' => 10,
        ]);

        // Create races after training
        Race::factory()->count(3)->for($career)->create([
            'character_id' => $character->id,
            'turn_number' => 20,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['statistical_summary']['correlation_analysis'])
            ->toHaveKey('training_race_correlation')
            ->toHaveKey('stat_win_correlations')
            ->toHaveKey('efficiency_win_correlation');
    });
});

// =========================================================================
// KEY INSIGHTS AND RECOMMENDATIONS TESTS
// =========================================================================

describe('Insights and Recommendations', function () {
    it('generates key insights based on performance', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create high-efficiency training
        TrainingSession::factory()->count(20)->for($career)->create([
            'character_id' => $character->id,
            'speed_gain' => 30,
            'stamina_gain' => 25,
            'power_gain' => 20,
            'guts_gain' => 15,
            'wit_gain' => 10,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['key_insights'])
            ->toBeArray()
            ->not->toBeEmpty();
    });

    it('generates improvement recommendations', function () {
        $character = Character::factory()->for($this->user)->create();
        $career = Career::factory()->for($character)->for($this->user)->create();

        // Create low-efficiency training
        TrainingSession::factory()->count(20)->for($career)->create([
            'character_id' => $character->id,
            'speed_gain' => 5,
            'stamina_gain' => 5,
            'power_gain' => 5,
            'guts_gain' => 5,
            'wit_gain' => 5,
        ]);

        $report = $this->service->generateCareerSummaryReport($career);

        expect($report['recommendations'])
            ->toBeArray()
            ->not->toBeEmpty();
    });
});
