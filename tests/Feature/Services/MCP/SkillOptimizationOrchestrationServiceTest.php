<?php

/**
 * @property \App\Models\User $user
 * @property \App\Models\Character $character
 * @property \Illuminate\Support\Collection<int, \App\Models\Skill> $targetSkills
 * @property \Illuminate\Support\Collection<int, mixed> $currentSkills
 * @property \Illuminate\Support\Collection<int, mixed> $supportCards
 */

use App\Models\Character;
use App\Models\Skill;
use App\Models\User;
use App\Services\MCP\SkillOptimizationOrchestrationService;

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
        'career_stage' => 'classic',
    ]);

    // Create sample skills
    $this->targetSkills = collect([
        Skill::factory()->create([
            'name' => 'Speed Star',
            'skill_type' => 'speed',
            'base_sp_cost' => 120,
            'rarity' => 'normal',
            'meta_tier' => 'S',
        ]),
        Skill::factory()->create([
            'name' => 'Endurance Master',
            'skill_type' => 'passive',
            'base_sp_cost' => 150,
            'rarity' => 'normal',
            'meta_tier' => 'A',
        ]),
    ]);

    $this->currentSkills = collect([]);
    $this->supportCards = collect([]);
});

test('skill optimization orchestration service executes comprehensive analysis', function () {
    $service = app(SkillOptimizationOrchestrationService::class);

    $context = [
        'current_turn' => 25,
        'total_turns' => 65,
        'available_sp' => 500,
    ];

    $result = $service->executeComprehensiveOptimization(
        $this->character,
        $this->targetSkills,
        $this->supportCards,
        $this->currentSkills,
        [],
        $context
    );

    // Verify all agent results are present
    expect($result)->toHaveKeys([
        'sp_budget',
        'hint_farming',
        'skill_build',
        'long_term_development',
        'integrated_strategy',
        'orchestration_metadata',
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

test('quick optimization recommendation provides fast results', function () {
    $service = app(SkillOptimizationOrchestrationService::class);

    $context = [
        'available_sp' => 500,
    ];

    $result = $service->getQuickOptimizationRecommendation(
        $this->character,
        $this->targetSkills,
        $context
    );

    expect($result)->toHaveKeys([
        'action',
        'reason',
        'confidence',
    ]);

    expect($result['action'])->toBeIn(['acquire_skills', 'collect_hints', 'balanced_development']);
    expect($result['confidence'])->toBeGreaterThanOrEqual(0.0);
    expect($result['confidence'])->toBeLessThanOrEqual(1.0);
});

test('integrated strategy includes priority strategy', function () {
    $service = app(SkillOptimizationOrchestrationService::class);

    $context = [
        'current_turn' => 25,
        'total_turns' => 65,
        'available_sp' => 500,
    ];

    $result = $service->executeComprehensiveOptimization(
        $this->character,
        $this->targetSkills,
        $this->supportCards,
        $this->currentSkills,
        [],
        $context
    );

    expect($result['integrated_strategy'])->toHaveKeys([
        'priority_strategy',
        'action_plan',
        'optimization_score',
        'recommendations',
        'summary',
        'consensus_score',
    ]);

    // Verify priority strategy structure
    expect($result['integrated_strategy']['priority_strategy'])->toHaveKeys([
        'strategy',
        'reason',
        'priority',
        'focus_agent',
    ]);

    // Verify consensus score is valid
    $consensusScore = $result['integrated_strategy']['consensus_score'];
    expect($consensusScore)->toBeGreaterThanOrEqual(0.0);
    expect($consensusScore)->toBeLessThanOrEqual(1.0);
});

test('optimization score is calculated correctly', function () {
    $service = app(SkillOptimizationOrchestrationService::class);

    $context = [
        'current_turn' => 25,
        'total_turns' => 65,
        'available_sp' => 500,
    ];

    $result = $service->executeComprehensiveOptimization(
        $this->character,
        $this->targetSkills,
        $this->supportCards,
        $this->currentSkills,
        [],
        $context
    );

    $optimizationScore = $result['integrated_strategy']['optimization_score'];

    expect($optimizationScore)->toBeInt();
    expect($optimizationScore)->toBeGreaterThanOrEqual(0);
    expect($optimizationScore)->toBeLessThanOrEqual(100);
});

test('action plan includes immediate and long-term actions', function () {
    $service = app(SkillOptimizationOrchestrationService::class);

    $context = [
        'current_turn' => 25,
        'total_turns' => 65,
        'available_sp' => 500,
    ];

    $result = $service->executeComprehensiveOptimization(
        $this->character,
        $this->targetSkills,
        $this->supportCards,
        $this->currentSkills,
        [],
        $context
    );

    $actionPlan = $result['integrated_strategy']['action_plan'];

    expect($actionPlan)->toHaveKeys([
        'immediate_actions',
        'short_term_actions',
        'long_term_actions',
    ]);

    expect($actionPlan['immediate_actions'])->toBeArray();
    expect($actionPlan['short_term_actions'])->toBeArray();
    expect($actionPlan['long_term_actions'])->toBeArray();
});

test('agent statuses are tracked correctly', function () {
    $service = app(SkillOptimizationOrchestrationService::class);

    $context = [
        'current_turn' => 25,
        'total_turns' => 65,
        'available_sp' => 500,
    ];

    $result = $service->executeComprehensiveOptimization(
        $this->character,
        $this->targetSkills,
        $this->supportCards,
        $this->currentSkills,
        [],
        $context
    );

    $agentStatuses = $result['orchestration_metadata']['agent_statuses'];

    expect($agentStatuses)->toHaveKeys([
        'sp_budget',
        'hint_farming',
        'skill_build',
        'long_term_development',
    ]);

    // Each agent status should have status and has_data
    foreach ($agentStatuses as $agentName => $status) {
        expect($status)->toHaveKeys(['status', 'has_data']);
        expect($status['status'])->toBeIn(['success', 'failed']);
        expect($status['has_data'])->toBeBool();
    }
});

test('recommendations are generated for all aspects', function () {
    $service = app(SkillOptimizationOrchestrationService::class);

    $context = [
        'current_turn' => 25,
        'total_turns' => 65,
        'available_sp' => 500,
    ];

    $result = $service->executeComprehensiveOptimization(
        $this->character,
        $this->targetSkills,
        $this->supportCards,
        $this->currentSkills,
        [],
        $context
    );

    $recommendations = $result['integrated_strategy']['recommendations'];

    expect($recommendations)->toBeArray();
    expect($recommendations)->toHaveKey('strategy');
    expect($recommendations['strategy'])->toBeString();
});

test('summary provides concise overview', function () {
    $service = app(SkillOptimizationOrchestrationService::class);

    $context = [
        'current_turn' => 25,
        'total_turns' => 65,
        'available_sp' => 500,
    ];

    $result = $service->executeComprehensiveOptimization(
        $this->character,
        $this->targetSkills,
        $this->supportCards,
        $this->currentSkills,
        [],
        $context
    );

    $summary = $result['integrated_strategy']['summary'];

    expect($summary)->toBeString();
    expect($summary)->not->toBeEmpty();
    expect($summary)->toContain('Strategy:');
});
