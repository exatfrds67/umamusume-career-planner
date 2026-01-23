<?php

/**
 * @property App\Services\MCP\MCPClientService&Mockery\MockInterface $mcpClient
 * @property App\Services\AI\Agents\TrainingOptimizationAgent&Mockery\MockInterface $trainingAgent
 * @property App\Services\AI\Agents\CareerStrategyAgent&Mockery\MockInterface $careerAgent
 * @property App\Services\AI\Agents\RaceAnalysisAgent&Mockery\MockInterface $raceAgent
 * @property App\Services\AI\Agents\SkillManagementAgent&Mockery\MockInterface $skillAgent
 * @property App\Services\AI\Agents\AgentOrchestrationService $orchestration
 * @property App\Models\Character $character
 */

use App\Models\Character;
use App\Services\AI\Agents\AgentOrchestrationService;
use App\Services\AI\Agents\CareerStrategyAgent;
use App\Services\AI\Agents\RaceAnalysisAgent;
use App\Services\AI\Agents\SkillManagementAgent;
use App\Services\AI\Agents\TrainingOptimizationAgent;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    /** @var TrainingOptimizationAgent&Mockery\MockInterface $trainingAgent */
    $trainingAgent = Mockery::mock(TrainingOptimizationAgent::class);
    /** @var CareerStrategyAgent&Mockery\MockInterface $careerAgent */
    $careerAgent = Mockery::mock(CareerStrategyAgent::class);
    /** @var RaceAnalysisAgent&Mockery\MockInterface $raceAgent */
    $raceAgent = Mockery::mock(RaceAnalysisAgent::class);
    /** @var SkillManagementAgent&Mockery\MockInterface $skillAgent */
    $skillAgent = Mockery::mock(SkillManagementAgent::class);

    $this->mcpClient = $mcpClient;
    $this->trainingAgent = $trainingAgent;
    $this->careerAgent = $careerAgent;
    $this->raceAgent = $raceAgent;
    $this->skillAgent = $skillAgent;

    $this->orchestration = new AgentOrchestrationService(
        $mcpClient,
        $trainingAgent,
        $careerAgent,
        $raceAgent,
        $skillAgent
    );

    $this->character = Character::factory()->create([
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 450,
            'guts' => 300,
            'wit' => 350,
        ],
    ]);
});

afterEach(function () {
    Mockery::close();
});

it('executes comprehensive analysis with all agents', function () {
    Config::set('ai.agents.orchestration.enabled', true);

    // Mock career agent
    $this->careerAgent->shouldReceive('createCareerPlan')
        ->once()
        ->andReturn([
            'plan' => ['strategy' => 'balanced'],
            'milestones' => [],
            'race_schedule' => [],
            'confidence' => 0.85,
        ]);

    // Mock training agent
    $this->trainingAgent->shouldReceive('optimizeTrainingSequence')
        ->once()
        ->andReturn([
            'sequence' => [],
            'expected_outcomes' => [],
            'confidence' => 0.85,
        ]);

    // Mock race agent
    $this->raceAgent->shouldReceive('recommendRaceStrategy')
        ->once()
        ->andReturn([
            'strategy' => [],
            'running_style' => 'pace_chaser',
            'confidence' => 0.85,
        ]);

    // Mock skill agent
    $this->skillAgent->shouldReceive('recommendSkillBuild')
        ->once()
        ->andReturn([
            'build' => [],
            'core_skills' => [],
            'confidence' => 0.85,
        ]);

    $result = $this->orchestration->executeComprehensiveAnalysis(
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
        ->and(count($result['workflow']))->toBeGreaterThan(0)
        ->and($result['metadata']['agents_used'])->toBe(4);
});

it('executes parallel workflow successfully', function () {
    Config::set('ai.agents.orchestration.enabled', true);

    $this->trainingAgent->shouldReceive('analyzeTrainingOptions')
        ->once()
        ->andReturn(['recommendations' => [], 'confidence' => 0.85]);

    $this->careerAgent->shouldReceive('optimizeGoalPriorities')
        ->once()
        ->andReturn(['priorities' => [], 'confidence' => 0.85]);

    $tasks = [
        'training_analysis' => [
            'agent' => 'training',
            'action' => 'analyze',
            'training_options' => [],
        ],
        'goal_optimization' => [
            'agent' => 'career',
            'action' => 'optimize_goals',
            'goals' => [],
        ],
    ];

    $result = $this->orchestration->executeParallelWorkflow($this->character, $tasks);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['results', 'workflow', 'confidence'])
        ->and($result['results'])->toHaveKeys(['training_analysis', 'goal_optimization'])
        ->and($result['metadata']['tasks_executed'])->toBe(2);
});

it('executes sequential workflow with context sharing', function () {
    Config::set('ai.agents.orchestration.enabled', true);

    $this->careerAgent->shouldReceive('createCareerPlan')
        ->once()
        ->andReturn([
            'plan' => ['strategy' => 'balanced'],
            'confidence' => 0.85,
            'context_updates' => ['career_strategy' => 'balanced'],
        ]);

    $this->trainingAgent->shouldReceive('optimizeTrainingSequence')
        ->once()
        ->andReturn([
            'sequence' => [],
            'confidence' => 0.85,
        ]);

    $steps = [
        [
            'agent' => 'career',
            'action' => 'plan',
            'goals' => [],
        ],
        [
            'agent' => 'training',
            'action' => 'optimize_sequence',
            'turns' => 10,
        ],
    ];

    $result = $this->orchestration->executeSequentialWorkflow($this->character, $steps);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['results', 'shared_context', 'workflow', 'confidence'])
        ->and($result['results'])->toBeArray()
        ->and(count($result['results']))->toBe(2)
        ->and($result['shared_context'])->toHaveKey('career_strategy');
});

it('handles training task execution', function () {
    Config::set('ai.agents.orchestration.enabled', true);

    $this->trainingAgent->shouldReceive('analyzeTrainingOptions')
        ->once()
        ->andReturn(['recommendations' => [], 'confidence' => 0.85]);

    $tasks = [
        'training' => [
            'agent' => 'training',
            'action' => 'analyze',
            'training_options' => [],
        ],
    ];

    $result = $this->orchestration->executeParallelWorkflow($this->character, $tasks);

    expect($result['results']['training'])->toBeArray()
        ->and($result['results']['training'])->toHaveKey('recommendations');
});

it('handles career task execution', function () {
    Config::set('ai.agents.orchestration.enabled', true);

    $this->careerAgent->shouldReceive('createCareerPlan')
        ->once()
        ->andReturn(['plan' => [], 'confidence' => 0.85]);

    $tasks = [
        'career' => [
            'agent' => 'career',
            'action' => 'plan',
            'goals' => [],
        ],
    ];

    $result = $this->orchestration->executeParallelWorkflow($this->character, $tasks);

    expect($result['results']['career'])->toBeArray()
        ->and($result['results']['career'])->toHaveKey('plan');
});

it('handles race task execution', function () {
    Config::set('ai.agents.orchestration.enabled', true);

    $this->raceAgent->shouldReceive('analyzeRacePreparation')
        ->once()
        ->andReturn(['readiness' => [], 'confidence' => 0.85]);

    $tasks = [
        'race' => [
            'agent' => 'race',
            'action' => 'analyze',
            'race_details' => [],
        ],
    ];

    $result = $this->orchestration->executeParallelWorkflow($this->character, $tasks);

    expect($result['results']['race'])->toBeArray()
        ->and($result['results']['race'])->toHaveKey('readiness');
});

it('handles skill task execution', function () {
    Config::set('ai.agents.orchestration.enabled', true);

    $this->skillAgent->shouldReceive('optimizeSPAllocation')
        ->once()
        ->andReturn(['allocation' => [], 'confidence' => 0.85]);

    $tasks = [
        'skill' => [
            'agent' => 'skill',
            'action' => 'optimize',
            'available_skills' => [],
        ],
    ];

    $result = $this->orchestration->executeParallelWorkflow($this->character, $tasks);

    expect($result['results']['skill'])->toBeArray()
        ->and($result['results']['skill'])->toHaveKey('allocation');
});

it('calculates overall confidence correctly', function () {
    Config::set('ai.agents.orchestration.enabled', true);

    $this->careerAgent->shouldReceive('createCareerPlan')
        ->once()
        ->andReturn(['plan' => [], 'confidence' => 0.9]);

    $this->trainingAgent->shouldReceive('optimizeTrainingSequence')
        ->once()
        ->andReturn(['sequence' => [], 'confidence' => 0.8]);

    $this->raceAgent->shouldReceive('recommendRaceStrategy')
        ->once()
        ->andReturn(['strategy' => [], 'confidence' => 0.85]);

    $this->skillAgent->shouldReceive('recommendSkillBuild')
        ->once()
        ->andReturn(['build' => [], 'confidence' => 0.75]);

    $result = $this->orchestration->executeComprehensiveAnalysis($this->character);

    // Average: (0.9 + 0.8 + 0.85 + 0.75) / 4 = 0.825
    expect($result['confidence'])->toBeGreaterThanOrEqual(0.824)->toBeLessThanOrEqual(0.826);
});

it('returns status for all agents', function () {
    $this->trainingAgent->shouldReceive('getStatus')
        ->once()
        ->andReturn(['enabled' => true, 'available' => true]);

    $this->careerAgent->shouldReceive('getStatus')
        ->once()
        ->andReturn(['enabled' => true, 'available' => true]);

    $this->raceAgent->shouldReceive('getStatus')
        ->once()
        ->andReturn(['enabled' => true, 'available' => true]);

    $this->skillAgent->shouldReceive('getStatus')
        ->once()
        ->andReturn(['enabled' => true, 'available' => true]);

    $this->mcpClient->shouldReceive('getAIServicesStatus')
        ->once()
        ->andReturn(['strands_agents' => true, 'agentcore' => true]);

    $status = $this->orchestration->getStatus();

    expect($status)->toBeArray()
        ->and($status)->toHaveKeys(['enabled', 'agents', 'mcp_status'])
        ->and($status['agents'])->toHaveKeys(['training', 'career', 'race', 'skill']);
});

it('returns default analysis when orchestration is disabled', function () {
    Config::set('ai.agents.orchestration.enabled', false);

    $result = $this->orchestration->executeComprehensiveAnalysis($this->character);

    expect($result)->toBeArray()
        ->and($result['metadata']['fallback'])->toBeTrue()
        ->and($result['confidence'])->toBe(0.5);
});
