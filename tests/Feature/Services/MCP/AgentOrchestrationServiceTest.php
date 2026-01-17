<?php

use App\Models\Character;
use App\Models\User;
use App\Services\MCP\AgentOrchestrationService;
use App\Services\MCP\Agents\CareerStrategyAgent;
use App\Services\MCP\Agents\PerformanceAnalyticsAgent;
use App\Services\MCP\Agents\ResourceManagementAgent;
use App\Services\MCP\Agents\SummerCampOptimizationAgent;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 450,
            'guts' => 300,
            'wit' => 350,
        ],
        'energy_level' => 80,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'goals' => [
            'target_stats' => [
                'speed' => 900,
                'stamina' => 600,
                'power' => 700,
                'guts' => 400,
                'wit' => 500,
            ],
            'target_grade' => 'A',
        ],
    ]);
});

test('agent orchestration service executes comprehensive analysis', function () {
    $service = app(AgentOrchestrationService::class);

    $context = [
        'current_turn' => 25,
        'total_turns' => 65,
    ];

    $result = $service->executeComprehensiveAnalysis($this->character, $context);

    // Verify all agent results are present
    expect($result)->toHaveKeys([
        'career_strategy',
        'resource_management',
        'performance_analytics',
        'summer_camp',
        'integrated_recommendations',
        'orchestration_metadata',
    ]);

    // Verify career strategy results
    expect($result['career_strategy'])->toHaveKeys([
        'strategy',
        'priority_stats',
        'training_focus',
        'milestone_tracking',
        'recommendations',
        'confidence',
    ]);

    // Verify resource management results
    expect($result['resource_management'])->toHaveKeys([
        'turn_economy',
        'energy_management',
        'resource_allocation',
        'optimization_recommendations',
        'efficiency_score',
    ]);

    // Verify performance analytics results
    expect($result['performance_analytics'])->toHaveKeys([
        'energy_analysis',
        'mood_analysis',
        'condition_analysis',
        'performance_metrics',
        'recommendations',
        'optimization_score',
    ]);

    // Verify summer camp results
    expect($result['summer_camp'])->toHaveKeys([
        'summer_camp_status',
        'optimization_strategy',
        'priority_actions',
        'expected_gains',
        'recommendations',
        'efficiency_score',
    ]);

    // Verify integrated recommendations
    expect($result['integrated_recommendations'])->toHaveKeys([
        'priority_recommendation',
        'action_plan',
        'consensus_score',
        'summary',
        'all_recommendations',
    ]);

    // Verify orchestration metadata
    expect($result['orchestration_metadata'])->toHaveKeys([
        'execution_time_seconds',
        'agents_executed',
        'agent_statuses',
        'timestamp',
    ]);

    expect($result['orchestration_metadata']['agents_executed'])->toBe(4);
});

test('career strategy agent analyzes character goals correctly', function () {
    $agent = app(CareerStrategyAgent::class);

    $result = $agent->analyzeCareerStrategy($this->character);

    expect($result)->toHaveKeys([
        'strategy',
        'priority_stats',
        'training_focus',
        'milestone_tracking',
        'recommendations',
        'confidence',
    ]);

    // Verify priority stats calculation
    expect($result['priority_stats'])->toBeArray();
    expect($result['priority_stats'])->not->toBeEmpty();

    // Speed should have highest gap (900 - 500 = 400)
    $priorityStats = array_keys($result['priority_stats']);
    expect($priorityStats[0])->toBe('speed');

    // Verify confidence score is between 0 and 1
    expect($result['confidence'])->toBeGreaterThanOrEqual(0.0);
    expect($result['confidence'])->toBeLessThanOrEqual(1.0);
});

test('resource management agent calculates turn economy correctly', function () {
    $agent = app(ResourceManagementAgent::class);

    $context = [
        'current_turn' => 25,
        'total_turns' => 65,
    ];

    $result = $agent->analyzeResourceManagement($this->character, $context);

    expect($result['turn_economy'])->toHaveKeys([
        'current_turn',
        'total_turns',
        'turns_remaining',
        'current_phase',
        'phase_priority',
        'turns_in_phase',
        'turns_remaining_in_phase',
        'progress_percent',
        'turn_efficiency',
        'phase_breakdown',
    ]);

    expect($result['turn_economy']['current_turn'])->toBe(25);
    expect($result['turn_economy']['total_turns'])->toBe(65);
    expect($result['turn_economy']['turns_remaining'])->toBe(40);
    expect($result['turn_economy']['current_phase'])->toBe('classic');
});

test('performance analytics agent evaluates energy and mood correctly', function () {
    $agent = app(PerformanceAnalyticsAgent::class);

    $result = $agent->analyzePerformance($this->character);

    expect($result['energy_analysis'])->toHaveKeys([
        'current_energy',
        'status',
        'training_capacity',
        'failure_risk',
        'recovery_strategy',
        'optimal_range',
        'is_optimal',
    ]);

    expect($result['energy_analysis']['current_energy'])->toBe(80);
    expect($result['energy_analysis']['status'])->toBe('excellent');

    expect($result['mood_analysis'])->toHaveKeys([
        'current_mood',
        'effects',
        'training_impact',
        'improvement_strategy',
        'stability',
        'is_optimal',
    ]);

    expect($result['mood_analysis']['current_mood'])->toBe('good');
    expect($result['mood_analysis']['is_optimal'])->toBeTrue();
});

test('summer camp agent identifies camp periods correctly', function () {
    $agent = app(SummerCampOptimizationAgent::class);

    // Test during classic year summer camp
    $context = [
        'current_turn' => 29, // Middle of classic summer camp (28-31)
    ];

    $result = $agent->analyzeSummerCampOptimization($this->character, $context);

    expect($result['summer_camp_status'])->toHaveKeys([
        'current_turn',
        'career_stage',
        'camp_period',
        'is_in_camp',
        'turns_until_camp',
        'turns_remaining_in_camp',
        'camp_phase',
        'bonuses',
    ]);

    expect($result['summer_camp_status']['is_in_camp'])->toBeTrue();
    expect($result['summer_camp_status']['camp_phase'])->toBe('active');
    expect($result['summer_camp_status']['turns_remaining_in_camp'])->toBe(3);
});

test('summer camp agent provides preparation recommendations before camp', function () {
    $agent = app(SummerCampOptimizationAgent::class);

    // Test 2 turns before classic summer camp
    $context = [
        'current_turn' => 26, // 2 turns before camp starts at 28
    ];

    $result = $agent->analyzeSummerCampOptimization($this->character, $context);

    expect($result['summer_camp_status']['is_in_camp'])->toBeFalse();
    expect($result['summer_camp_status']['camp_phase'])->toBe('preparation');
    expect($result['summer_camp_status']['turns_until_camp'])->toBe(2);

    // Should recommend preparation actions
    expect($result['recommendations'])->toHaveKey('phase');
    expect($result['recommendations']['phase'])->toContain('Summer Camp starts in 2 turns');
});

test('integrated recommendations prioritize critical situations', function () {
    // Create character with critical energy
    $criticalCharacter = Character::factory()->create([
        'user_id' => $this->user->id,
        'energy_level' => 15, // Critical energy
        'mood_status' => 'normal',
    ]);

    $service = app(AgentOrchestrationService::class);

    $result = $service->executeComprehensiveAnalysis($criticalCharacter);

    $priorityRec = $result['integrated_recommendations']['priority_recommendation'];

    expect($priorityRec['priority'])->toBe('critical');
    expect($priorityRec['action'])->toBe('rest');
    expect($priorityRec['source'])->toBe('performance_analytics');
});

test('integrated recommendations prioritize summer camp when active', function () {
    $service = app(AgentOrchestrationService::class);

    // During classic summer camp
    $context = [
        'current_turn' => 29,
    ];

    $result = $service->executeComprehensiveAnalysis($this->character, $context);

    $priorityRec = $result['integrated_recommendations']['priority_recommendation'];

    expect($priorityRec['priority'])->toBe('critical');
    expect($priorityRec['action'])->toBe('training');
    expect($priorityRec['source'])->toBe('summer_camp');
    expect($priorityRec['reason'])->toContain('Summer Camp active');
});

test('quick recommendation provides fast cached results', function () {
    $service = app(AgentOrchestrationService::class);

    $result = $service->getQuickRecommendation($this->character);

    expect($result)->toHaveKeys([
        'action',
        'reason',
        'confidence',
    ]);

    expect($result['action'])->toBeIn(['training', 'rest']);
    expect($result['confidence'])->toBeGreaterThanOrEqual(0.0);
    expect($result['confidence'])->toBeLessThanOrEqual(1.0);
});

test('consensus score reflects agent agreement', function () {
    $service = app(AgentOrchestrationService::class);

    $result = $service->executeComprehensiveAnalysis($this->character);

    $consensusScore = $result['integrated_recommendations']['consensus_score'];

    expect($consensusScore)->toBeGreaterThanOrEqual(0.0);
    expect($consensusScore)->toBeLessThanOrEqual(1.0);
});
