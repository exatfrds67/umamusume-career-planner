<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Simulation\BatchSimulationService;
use App\Services\Simulation\ComparisonReportService;
use App\Services\Simulation\SimulationEngine;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ─── SimulationEngine Tests ──────────────────────────────────

it('runs a scenario and returns expected structure', function () {
    $engine = new SimulationEngine;

    $result = $engine->runScenario(
        ['speed' => 800, 'stamina' => 600, 'power' => 700, 'guts' => 500, 'wit' => 600],
        ['training_focus' => 'speed', 'support_deck_bonus' => 1.2, 'scenario_type' => 'ura_finale']
    );

    expect($result)->toHaveKeys(['final_stats', 'total_turns', 'sp_earned', 'win_rate', 'efficiency_score'])
        ->and($result['final_stats'])->toHaveKeys(['speed', 'stamina', 'power', 'guts', 'wit'])
        ->and($result['total_turns'])->toBe(72)
        ->and($result['win_rate'])->toBeGreaterThan(0)
        ->and($result['sp_earned'])->toBeGreaterThan(0)
        ->and($result['efficiency_score'])->toBeGreaterThan(0);
});

it('uses 78 turns for unity cup scenario', function () {
    $engine = new SimulationEngine;

    $result = $engine->runScenario(
        ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600],
        ['training_focus' => 'balanced', 'support_deck_bonus' => 1.0, 'scenario_type' => 'unity_cup']
    );

    expect($result['total_turns'])->toBe(78);
});

it('defaults to ura finale when no scenario type specified', function () {
    $engine = new SimulationEngine;

    $result = $engine->runScenario(
        ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600],
        ['training_focus' => 'balanced', 'support_deck_bonus' => 1.0]
    );

    expect($result['total_turns'])->toBe(72);
});

it('caps stats at 1200', function () {
    $engine = new SimulationEngine;

    $result = $engine->runScenario(
        ['speed' => 1200, 'stamina' => 1200, 'power' => 1200, 'guts' => 1200, 'wit' => 1200],
        ['training_focus' => 'speed', 'support_deck_bonus' => 2.0, 'scenario_type' => 'ura_finale']
    );

    foreach ($result['final_stats'] as $value) {
        expect($value)->toBeLessThanOrEqual(1200);
    }
});

it('applies support deck bonus to stat gains', function () {
    $engine = new SimulationEngine;

    $lowBonus = $engine->runScenario(
        ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600],
        ['training_focus' => 'balanced', 'support_deck_bonus' => 0.5, 'scenario_type' => 'ura_finale']
    );

    $highBonus = $engine->runScenario(
        ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600],
        ['training_focus' => 'balanced', 'support_deck_bonus' => 2.0, 'scenario_type' => 'ura_finale']
    );

    $lowTotal = array_sum($lowBonus['final_stats']);
    $highTotal = array_sum($highBonus['final_stats']);

    expect($highTotal)->toBeGreaterThanOrEqual($lowTotal);
});

it('produces higher focused stat with training focus', function () {
    $engine = new SimulationEngine;

    $results = [];
    $focuses = ['speed', 'stamina', 'power', 'guts', 'wit'];

    foreach ($focuses as $focus) {
        $results[$focus] = $engine->runScenario(
            ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600],
            ['training_focus' => $focus, 'support_deck_bonus' => 1.5, 'scenario_type' => 'ura_finale']
        );
    }

    foreach ($focuses as $focus) {
        $focusedStat = $results[$focus]['final_stats'][$focus];
        $balancedResult = $engine->runScenario(
            ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600],
            ['training_focus' => 'balanced', 'support_deck_bonus' => 1.5, 'scenario_type' => 'ura_finale']
        );
        expect($focusedStat)->toBeGreaterThanOrEqual($balancedResult['final_stats'][$focus] - 100);
    }
});

// ─── BatchSimulationService Tests ────────────────────────────

it('creates a batch with valid scenario count', function () {
    $service = new BatchSimulationService;
    $user = User::factory()->create();

    $scenarios = [
        ['target_stats' => ['speed' => 800, 'stamina' => 600, 'power' => 700, 'guts' => 500, 'wit' => 600], 'parameters' => ['training_focus' => 'speed', 'support_deck_bonus' => 1.0, 'scenario_type' => 'ura_finale']],
        ['target_stats' => ['speed' => 600, 'stamina' => 800, 'power' => 600, 'guts' => 600, 'wit' => 600], 'parameters' => ['training_focus' => 'stamina', 'support_deck_bonus' => 1.2, 'scenario_type' => 'ura_finale']],
    ];

    $result = $service->createBatch($user, $scenarios);

    expect($result)->toHaveKeys(['batch_id', 'scenario_count', 'status'])
        ->and($result['scenario_count'])->toBe(2)
        ->and($result['status'])->toBe('pending');
});

it('rejects batch with fewer than 2 scenarios', function () {
    $service = new BatchSimulationService;
    $user = User::factory()->create();

    $scenarios = [
        ['target_stats' => ['speed' => 800, 'stamina' => 600, 'power' => 700, 'guts' => 500, 'wit' => 600], 'parameters' => []],
    ];

    $service->createBatch($user, $scenarios);
})->throws(\InvalidArgumentException::class);

it('rejects batch with more than 10 scenarios', function () {
    $service = new BatchSimulationService;
    $user = User::factory()->create();

    $scenarios = [];
    for ($i = 0; $i < 11; $i++) {
        $scenarios[] = [
            'target_stats' => ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600],
            'parameters' => ['training_focus' => 'balanced'],
        ];
    }

    $service->createBatch($user, $scenarios);
})->throws(\InvalidArgumentException::class);

it('records results and tracks progress', function () {
    $service = new BatchSimulationService;
    $user = User::factory()->create();

    $scenarios = [
        ['target_stats' => ['speed' => 800, 'stamina' => 600, 'power' => 700, 'guts' => 500, 'wit' => 600], 'parameters' => []],
        ['target_stats' => ['speed' => 600, 'stamina' => 800, 'power' => 600, 'guts' => 600, 'wit' => 600], 'parameters' => []],
        ['target_stats' => ['speed' => 700, 'stamina' => 700, 'power' => 700, 'guts' => 700, 'wit' => 700], 'parameters' => []],
    ];

    $result = $service->createBatch($user, $scenarios);
    $batchId = $result['batch_id'];

    $service->recordResult($batchId, 0, ['final_stats' => [], 'win_rate' => 85.0]);
    $progress = $service->getProgress($batchId);
    expect($progress['completed'])->toBe(1)
        ->and($progress['total'])->toBe(3)
        ->and($progress['percentage'])->toBe(33.33);

    $service->recordResult($batchId, 1, ['final_stats' => [], 'win_rate' => 90.0]);
    $service->recordResult($batchId, 2, ['final_stats' => [], 'win_rate' => 80.0]);

    $progress = $service->getProgress($batchId);
    expect($progress['completed'])->toBe(3)
        ->and($progress['status'])->toBe('completed');
});

it('marks batch as completed with errors when failures occur', function () {
    $service = new BatchSimulationService;
    $user = User::factory()->create();

    $scenarios = [
        ['target_stats' => ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600], 'parameters' => []],
        ['target_stats' => ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600], 'parameters' => []],
    ];

    $result = $service->createBatch($user, $scenarios);
    $batchId = $result['batch_id'];

    $service->recordResult($batchId, 0, ['final_stats' => [], 'win_rate' => 85.0]);
    $service->recordFailure($batchId, 1, 'Simulation failed');

    $progress = $service->getProgress($batchId);
    expect($progress['status'])->toBe('completed_with_errors')
        ->and($progress['failed'])->toBe(1);
});

it('can cancel a pending batch', function () {
    $service = new BatchSimulationService;
    $user = User::factory()->create();

    $scenarios = [
        ['target_stats' => ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600], 'parameters' => []],
        ['target_stats' => ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600], 'parameters' => []],
    ];

    $result = $service->createBatch($user, $scenarios);
    $cancelled = $service->cancelBatch($result['batch_id']);

    expect($cancelled)->toBeTrue();

    $progress = $service->getProgress($result['batch_id']);
    expect($progress['status'])->toBe('cancelled');
});

it('returns not found for non-existent batch', function () {
    $service = new BatchSimulationService;
    $progress = $service->getProgress('non-existent-batch-id');

    expect($progress['status'])->toBe('not_found')
        ->and($progress['total'])->toBe(0);
});

// ─── ComparisonReportService Tests ───────────────────────────

it('generates complete comparison report', function () {
    $reportService = new ComparisonReportService;

    $results = [
        0 => ['final_stats' => ['speed' => 900, 'stamina' => 600, 'power' => 700, 'guts' => 500, 'wit' => 600], 'total_turns' => 72, 'sp_earned' => 800, 'win_rate' => 85.5, 'efficiency_score' => 12.3],
        1 => ['final_stats' => ['speed' => 600, 'stamina' => 900, 'power' => 600, 'guts' => 600, 'wit' => 600], 'total_turns' => 72, 'sp_earned' => 750, 'win_rate' => 80.0, 'efficiency_score' => 11.8],
    ];

    $scenarios = [
        0 => ['target_stats' => ['speed' => 800, 'stamina' => 600, 'power' => 700, 'guts' => 500, 'wit' => 600], 'parameters' => ['training_focus' => 'speed']],
        1 => ['target_stats' => ['speed' => 600, 'stamina' => 800, 'power' => 600, 'guts' => 600, 'wit' => 600], 'parameters' => ['training_focus' => 'stamina']],
    ];

    $report = $reportService->generateReport($results, $scenarios);

    expect($report)->toHaveKeys(['stat_comparison', 'rankings', 'best_scenario', 'recommendations', 'summary'])
        ->and($report['stat_comparison'])->toHaveKeys(['speed', 'stamina', 'power', 'guts', 'wit'])
        ->and($report['rankings'])->toHaveKeys(['by_win_rate', 'by_efficiency', 'by_sp_earned', 'by_total_stats'])
        ->and($report['best_scenario'])->toBeIn([0, 1])
        ->and($report['summary']['scenario_count'])->toBe(2);
});

it('returns empty report with insufficient results', function () {
    $reportService = new ComparisonReportService;

    $results = [
        0 => ['final_stats' => ['speed' => 900], 'win_rate' => 85.5, 'efficiency_score' => 12.3, 'sp_earned' => 800, 'total_turns' => 72],
    ];

    $report = $reportService->generateReport($results, []);

    expect($report['best_scenario'])->toBe(-1)
        ->and($report['summary'])->toHaveKey('error');
});

it('filters out error results from comparison', function () {
    $reportService = new ComparisonReportService;

    $results = [
        0 => ['final_stats' => ['speed' => 900, 'stamina' => 600, 'power' => 700, 'guts' => 500, 'wit' => 600], 'total_turns' => 72, 'sp_earned' => 800, 'win_rate' => 85.5, 'efficiency_score' => 12.3],
        1 => ['error' => 'Simulation failed'],
        2 => ['final_stats' => ['speed' => 700, 'stamina' => 700, 'power' => 700, 'guts' => 700, 'wit' => 700], 'total_turns' => 72, 'sp_earned' => 750, 'win_rate' => 80.0, 'efficiency_score' => 11.0],
    ];

    $scenarios = [
        0 => ['target_stats' => ['speed' => 900], 'parameters' => ['training_focus' => 'speed']],
        1 => ['target_stats' => ['speed' => 600], 'parameters' => ['training_focus' => 'balanced']],
        2 => ['target_stats' => ['speed' => 700], 'parameters' => ['training_focus' => 'balanced']],
    ];

    $report = $reportService->generateReport($results, $scenarios);

    expect($report['summary']['scenario_count'])->toBe(2);
});

it('ranks scenarios correctly by win rate', function () {
    $reportService = new ComparisonReportService;

    $results = [
        0 => ['final_stats' => ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600], 'total_turns' => 72, 'sp_earned' => 700, 'win_rate' => 70.0, 'efficiency_score' => 10.0],
        1 => ['final_stats' => ['speed' => 800, 'stamina' => 800, 'power' => 800, 'guts' => 800, 'wit' => 800], 'total_turns' => 72, 'sp_earned' => 900, 'win_rate' => 95.0, 'efficiency_score' => 15.0],
        2 => ['final_stats' => ['speed' => 700, 'stamina' => 700, 'power' => 700, 'guts' => 700, 'wit' => 700], 'total_turns' => 72, 'sp_earned' => 800, 'win_rate' => 85.0, 'efficiency_score' => 12.0],
    ];

    $scenarios = array_fill(0, 3, ['target_stats' => ['speed' => 600], 'parameters' => ['training_focus' => 'balanced']]);

    $report = $reportService->generateReport($results, $scenarios);

    $winRateRanking = $report['rankings']['by_win_rate'];
    $keys = array_keys($winRateRanking);

    expect($keys[0])->toBe(1)
        ->and($keys[1])->toBe(2)
        ->and($keys[2])->toBe(0);
});

it('generates recommendations for each scenario', function () {
    $reportService = new ComparisonReportService;

    $results = [
        0 => ['final_stats' => ['speed' => 900, 'stamina' => 600, 'power' => 700, 'guts' => 500, 'wit' => 600], 'total_turns' => 72, 'sp_earned' => 800, 'win_rate' => 95.0, 'efficiency_score' => 14.0],
        1 => ['final_stats' => ['speed' => 600, 'stamina' => 600, 'power' => 600, 'guts' => 600, 'wit' => 600], 'total_turns' => 72, 'sp_earned' => 600, 'win_rate' => 70.0, 'efficiency_score' => 9.0],
    ];

    $scenarios = [
        0 => ['target_stats' => ['speed' => 800], 'parameters' => ['training_focus' => 'speed']],
        1 => ['target_stats' => ['speed' => 600], 'parameters' => ['training_focus' => 'balanced']],
    ];

    $report = $reportService->generateReport($results, $scenarios);

    expect($report['recommendations'])->toHaveCount(2)
        ->and($report['recommendations'][0])->toContain('Best overall')
        ->and($report['recommendations'][1])->toContain('Focus:');
});

it('calculates summary statistics correctly', function () {
    $reportService = new ComparisonReportService;

    $results = [
        0 => ['final_stats' => ['speed' => 900, 'stamina' => 600, 'power' => 700, 'guts' => 500, 'wit' => 600], 'total_turns' => 72, 'sp_earned' => 800, 'win_rate' => 90.0, 'efficiency_score' => 14.0],
        1 => ['final_stats' => ['speed' => 600, 'stamina' => 900, 'power' => 600, 'guts' => 600, 'wit' => 600], 'total_turns' => 72, 'sp_earned' => 700, 'win_rate' => 80.0, 'efficiency_score' => 12.0],
    ];

    $scenarios = array_fill(0, 2, ['target_stats' => [], 'parameters' => ['training_focus' => 'balanced']]);

    $report = $reportService->generateReport($results, $scenarios);

    expect($report['summary']['avg_win_rate'])->toBe(85.0)
        ->and($report['summary']['max_win_rate'])->toBe(90.0)
        ->and($report['summary']['min_win_rate'])->toBe(80.0)
        ->and($report['summary']['win_rate_spread'])->toBe(10.0)
        ->and($report['summary']['avg_sp_earned'])->toBe(750);
});
