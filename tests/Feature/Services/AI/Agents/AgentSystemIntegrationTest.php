<?php

use App\Models\Character;
use App\Services\AI\Agents\AgentOrchestrationService;
use App\Services\AI\Agents\CareerStrategyAgent;
use App\Services\AI\Agents\RaceAnalysisAgent;
use App\Services\AI\Agents\SkillManagementAgent;
use App\Services\AI\Agents\TrainingOptimizationAgent;
use App\Services\MCP\MCPClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;


beforeEach(function () {
    Config::set('ai.agents.training_optimization.enabled', true);
    Config::set('ai.agents.career_strategy.enabled', true);
    Config::set('ai.agents.race_analysis.enabled', true);
    Config::set('ai.agents.skill_management.enabled', true);
    Config::set('ai.agents.orchestration.enabled', true);

    $this->character = Character::factory()->create([
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
        'career_stage' => 'junior',
        'turn_number' => 10,
    ]);
});

it('creates all agent services successfully', function () {
    $mcpClient = app(MCPClientService::class);

    $trainingAgent = new TrainingOptimizationAgent($mcpClient);
    $careerAgent = new CareerStrategyAgent($mcpClient);
    $raceAgent = new RaceAnalysisAgent($mcpClient);
    $skillAgent = new SkillManagementAgent($mcpClient);

    expect($trainingAgent)->toBeInstanceOf(TrainingOptimizationAgent::class)
        ->and($careerAgent)->toBeInstanceOf(CareerStrategyAgent::class)
        ->and($raceAgent)->toBeInstanceOf(RaceAnalysisAgent::class)
        ->and($skillAgent)->toBeInstanceOf(SkillManagementAgent::class);
});

it('creates orchestration service with all agents', function () {
    $mcpClient = app(MCPClientService::class);
    $trainingAgent = new TrainingOptimizationAgent($mcpClient);
    $careerAgent = new CareerStrategyAgent($mcpClient);
    $raceAgent = new RaceAnalysisAgent($mcpClient);
    $skillAgent = new SkillManagementAgent($mcpClient);

    $orchestration = new AgentOrchestrationService(
        $mcpClient,
        $trainingAgent,
        $careerAgent,
        $raceAgent,
        $skillAgent
    );

    expect($orchestration)->toBeInstanceOf(AgentOrchestrationService::class);
});

it('training agent provides fallback recommendations when MCP unavailable', function () {
    $mcpClient = app(MCPClientService::class);
    $trainingAgent = new TrainingOptimizationAgent($mcpClient);

    $trainingOptions = [
        'options' => [
            ['type' => 'speed', 'participants' => 2],
            ['type' => 'stamina', 'participants' => 3],
        ],
    ];

    $result = $trainingAgent->analyzeTrainingOptions(
        $this->character,
        $trainingOptions,
        ['target_speed' => 800]
    );

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['recommendations', 'analysis', 'confidence', 'reasoning'])
        ->and($result['recommendations'])->toBeArray()
        ->and(count($result['recommendations']))->toBe(2);
});

it('career agent creates career plan', function () {
    $mcpClient = app(MCPClientService::class);
    $careerAgent = new CareerStrategyAgent($mcpClient);

    $result = $careerAgent->createCareerPlan(
        $this->character,
        ['target_grade' => 'A', 'focus' => 'speed']
    );

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['plan', 'milestones', 'race_schedule', 'training_priorities', 'confidence'])
        ->and($result['training_priorities'])->toBeArray();
});

it('race agent analyzes race preparation', function () {
    $mcpClient = app(MCPClientService::class);
    $raceAgent = new RaceAnalysisAgent($mcpClient);

    $raceDetails = [
        'name' => 'Japan Cup',
        'grade' => 'G1',
        'distance' => 2400,
        'surface' => 'turf',
    ];

    $result = $raceAgent->analyzeRacePreparation($this->character, $raceDetails);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['readiness', 'recommendations', 'stat_requirements', 'confidence'])
        ->and($result['readiness'])->toBeArray();
});

it('skill agent optimizes SP allocation', function () {
    $mcpClient = app(MCPClientService::class);
    $skillAgent = new SkillManagementAgent($mcpClient);

    $availableSkills = [
        'skills' => [
            ['name' => 'Speed Star', 'sp_cost' => 120, 'type' => 'normal'],
            ['name' => 'Stamina Keeper', 'sp_cost' => 140, 'type' => 'normal'],
        ],
    ];

    $result = $skillAgent->optimizeSPAllocation(
        $this->character,
        $availableSkills,
        ['focus' => 'speed']
    );

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['allocation', 'priority_skills', 'reasoning', 'confidence']);
});

it('orchestration service executes comprehensive analysis', function () {
    $mcpClient = app(MCPClientService::class);
    $trainingAgent = new TrainingOptimizationAgent($mcpClient);
    $careerAgent = new CareerStrategyAgent($mcpClient);
    $raceAgent = new RaceAnalysisAgent($mcpClient);
    $skillAgent = new SkillManagementAgent($mcpClient);

    $orchestration = new AgentOrchestrationService(
        $mcpClient,
        $trainingAgent,
        $careerAgent,
        $raceAgent,
        $skillAgent
    );

    $result = $orchestration->executeComprehensiveAnalysis(
        $this->character,
        ['target_grade' => 'A']
    );

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys([
            'career_plan',
            'training_recommendations',
            'race_strategy',
            'skill_plan',
            'workflow',
            'confidence',
        ])
        ->and($result['workflow'])->toBeArray()
        ->and(count($result['workflow']))->toBeGreaterThan(0);
});

it('orchestration service executes parallel workflow', function () {
    $mcpClient = app(MCPClientService::class);
    $trainingAgent = new TrainingOptimizationAgent($mcpClient);
    $careerAgent = new CareerStrategyAgent($mcpClient);
    $raceAgent = new RaceAnalysisAgent($mcpClient);
    $skillAgent = new SkillManagementAgent($mcpClient);

    $orchestration = new AgentOrchestrationService(
        $mcpClient,
        $trainingAgent,
        $careerAgent,
        $raceAgent,
        $skillAgent
    );

    $tasks = [
        'training' => [
            'agent' => 'training',
            'action' => 'analyze',
            'training_options' => [
                ['type' => 'speed', 'participants' => 2],
            ],
        ],
        'career' => [
            'agent' => 'career',
            'action' => 'plan',
            'goals' => ['target_grade' => 'A'],
        ],
    ];

    $result = $orchestration->executeParallelWorkflow($this->character, $tasks);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['results', 'workflow', 'confidence'])
        ->and($result['results'])->toHaveKeys(['training', 'career'])
        ->and($result['metadata']['tasks_executed'])->toBe(2);
});

it('orchestration service executes sequential workflow with context sharing', function () {
    $mcpClient = app(MCPClientService::class);
    $trainingAgent = new TrainingOptimizationAgent($mcpClient);
    $careerAgent = new CareerStrategyAgent($mcpClient);
    $raceAgent = new RaceAnalysisAgent($mcpClient);
    $skillAgent = new SkillManagementAgent($mcpClient);

    $orchestration = new AgentOrchestrationService(
        $mcpClient,
        $trainingAgent,
        $careerAgent,
        $raceAgent,
        $skillAgent
    );

    $steps = [
        [
            'agent' => 'career',
            'action' => 'plan',
            'goals' => ['target_grade' => 'A'],
        ],
        [
            'agent' => 'training',
            'action' => 'optimize_sequence',
            'turns' => 5,
        ],
    ];

    $result = $orchestration->executeSequentialWorkflow($this->character, $steps);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['results', 'shared_context', 'workflow', 'confidence'])
        ->and($result['results'])->toBeArray()
        ->and(count($result['results']))->toBe(2);
});

it('all agents report correct status', function () {
    $mcpClient = app(MCPClientService::class);
    $trainingAgent = new TrainingOptimizationAgent($mcpClient);
    $careerAgent = new CareerStrategyAgent($mcpClient);
    $raceAgent = new RaceAnalysisAgent($mcpClient);
    $skillAgent = new SkillManagementAgent($mcpClient);

    $trainingStatus = $trainingAgent->getStatus();
    $careerStatus = $careerAgent->getStatus();
    $raceStatus = $raceAgent->getStatus();
    $skillStatus = $skillAgent->getStatus();

    expect($trainingStatus)->toHaveKeys(['enabled', 'available', 'agent_id', 'mcp_server'])
        ->and($careerStatus)->toHaveKeys(['enabled', 'available', 'agent_id', 'mcp_server'])
        ->and($raceStatus)->toHaveKeys(['enabled', 'available', 'agent_id', 'mcp_server'])
        ->and($skillStatus)->toHaveKeys(['enabled', 'available', 'agent_id', 'mcp_server']);
});

it('agents handle Unity Cup scenario correctly', function () {
    $unityCharacter = Character::factory()->create([
        'scenario_type' => 'unity_cup',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 500,
            'power' => 550,
            'guts' => 400,
            'wit' => 450,
        ],
    ]);

    $mcpClient = app(MCPClientService::class);
    $trainingAgent = new TrainingOptimizationAgent($mcpClient);

    $trainingOptions = [
        'options' => [
            ['type' => 'speed', 'participants' => 3, 'spirit_burst_ready' => true],
        ],
    ];

    $result = $trainingAgent->analyzeTrainingOptions(
        $unityCharacter,
        $trainingOptions
    );

    expect($result)->toBeArray()
        ->and($result['metadata']['scenario_type'])->toBe('unity_cup');
});
