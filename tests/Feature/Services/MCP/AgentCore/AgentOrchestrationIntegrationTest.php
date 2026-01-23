<?php

use App\Models\Character;
use App\Models\MCPAgent;
use App\Models\User;
use App\Services\MCP\AgentCore\AgentCommunicationProtocol;
use App\Services\MCP\AgentCore\AgentCoreService;
use App\Services\MCP\AgentCore\AgentLifecycleManager;
use App\Services\MCP\AgentCore\AgentPerformanceAnalytics;
use App\Services\MCP\AgentCore\MultiAgentWorkflowManager;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('mcp.enabled', true);
    Config::set('mcp.agentcore.enabled', true);
    Config::set('mcp.servers.agentcore-mcp-server.enabled', true);
    Cache::flush();

    // Create test user and character
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Mock MCP Client to return available status
    $this->mock(MCPClientService::class, function ($mock) {
        $mock->shouldReceive('isAgentCoreAvailable')->andReturn(true);
        $mock->shouldReceive('getServerHealth')->andReturn(['status' => 'healthy']);
    });

    // Initialize services
    $this->agentCore = app(AgentCoreService::class);
    $this->communication = app(AgentCommunicationProtocol::class);
    $this->analytics = app(AgentPerformanceAnalytics::class);
    $this->lifecycle = app(AgentLifecycleManager::class);
    $this->workflow = app(MultiAgentWorkflowManager::class);
});

test('complete agent lifecycle from creation to termination', function () {
    // Create agent
    $config = [
        'name' => 'Training Optimizer',
        'type' => 'training_optimizer',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Optimize training sequences for Umamusume characters',
        'tools' => ['training_analysis', 'stat_prediction'],
        'metadata' => ['version' => '1.0'],
    ];

    $creation = $this->lifecycle->createAgent($config);

    if ($creation['status'] === 'failed') {
        dump('Agent creation failed:', $creation);
    }

    expect($creation['status'])->toBe('active')
        ->and($creation['agent_id'])->not->toBeEmpty()
        ->and($creation['database_id'])->toBeGreaterThan(0);

    $agentId = $creation['agent_id'];

    // Monitor agent
    $monitoring = $this->lifecycle->monitorAgent($agentId);

    expect($monitoring['health_status'])->toBe('healthy')
        ->and($monitoring['performance_metrics'])->toBeArray();

    // Terminate agent
    $termination = $this->lifecycle->terminateAgent($agentId);

    expect($termination['status'])->toBe('terminated')
        ->and($termination['resources_cleaned'])->toHaveKeys([
            'agentcore',
            'database',
            'cache',
            'communication',
            'analytics',
        ]);

    // Verify database record
    $agent = MCPAgent::where('agent_id', $agentId)->first();
    expect($agent->status)->toBe('terminated');
});

test('multi-agent sequential workflow execution', function () {
    // Create multiple agents
    $agents = [];
    $agentTypes = ['career_strategy', 'training_optimization', 'race_analysis', 'skill_management'];

    foreach ($agentTypes as $type) {
        $config = [
            'name' => ucfirst(str_replace('_', ' ', $type)).' Agent',
            'type' => $type,
            'model' => 'claude-3-5-sonnet',
            'instructions' => "Provide {$type} analysis",
        ];

        $creation = $this->lifecycle->createAgent($config);
        $agents[$type] = $creation['agent_id'];
    }

    // Execute sequential workflow
    $steps = [
        [
            'agent_id' => $agents['career_strategy'],
            'task' => 'analyze_career',
            'input' => ['character_id' => $this->character->id],
        ],
        [
            'agent_id' => $agents['training_optimization'],
            'task' => 'optimize_training',
            'input' => ['character_id' => $this->character->id],
        ],
        [
            'agent_id' => $agents['race_analysis'],
            'task' => 'analyze_races',
            'input' => ['character_id' => $this->character->id],
        ],
        [
            'agent_id' => $agents['skill_management'],
            'task' => 'optimize_skills',
            'input' => ['character_id' => $this->character->id],
        ],
    ];

    $result = $this->workflow->executeSequentialWorkflow($steps, [
        'character' => $this->character->toArray(),
    ]);

    expect($result['status'])->toBe('completed')
        ->and($result['results'])->toHaveCount(4)
        ->and($result['execution_time'])->toBeFloat()
        ->and($result['total_cost'])->toBeFloat();

    // Cleanup
    foreach ($agents as $agentId) {
        $this->lifecycle->terminateAgent($agentId);
    }
});

test('multi-agent parallel workflow execution', function () {
    // Create multiple agents
    $agents = [];
    $agentTypes = ['training_optimization', 'race_analysis', 'skill_management'];

    foreach ($agentTypes as $type) {
        $config = [
            'name' => ucfirst(str_replace('_', ' ', $type)).' Agent',
            'type' => $type,
            'model' => 'claude-3-5-haiku',
            'instructions' => "Provide {$type} analysis",
        ];

        $creation = $this->lifecycle->createAgent($config);
        $agents[$type] = $creation['agent_id'];
    }

    // Execute parallel workflow
    $tasks = [
        [
            'agent_id' => $agents['training_optimization'],
            'task' => 'optimize_training',
            'input' => ['character_id' => $this->character->id],
        ],
        [
            'agent_id' => $agents['race_analysis'],
            'task' => 'analyze_races',
            'input' => ['character_id' => $this->character->id],
        ],
        [
            'agent_id' => $agents['skill_management'],
            'task' => 'optimize_skills',
            'input' => ['character_id' => $this->character->id],
        ],
    ];

    $result = $this->workflow->executeParallelWorkflow($tasks, [
        'character' => $this->character->toArray(),
    ]);

    expect($result['status'])->toBe('completed')
        ->and($result['results'])->toHaveCount(3)
        ->and($result['execution_time'])->toBeFloat();

    // Cleanup
    foreach ($agents as $agentId) {
        $this->lifecycle->terminateAgent($agentId);
    }
});

test('agent communication and collaboration', function () {
    // Create two agents
    $agent1Config = [
        'name' => 'Agent 1',
        'type' => 'training_optimizer',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Optimize training',
    ];

    $agent2Config = [
        'name' => 'Agent 2',
        'type' => 'race_analyzer',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Analyze races',
    ];

    $agent1 = $this->lifecycle->createAgent($agent1Config);
    $agent2 = $this->lifecycle->createAgent($agent2Config);

    $agent1Id = $agent1['agent_id'];
    $agent2Id = $agent2['agent_id'];

    // Send message from agent1 to agent2
    $message = [
        'type' => 'training_recommendation',
        'data' => ['focus' => 'speed', 'priority' => 'high'],
    ];

    $sendResult = $this->communication->sendMessage($agent1Id, $agent2Id, $message);

    expect($sendResult['status'])->toBe('delivered')
        ->and($sendResult['message_id'])->not->toBeEmpty();

    // Receive messages for agent2
    $messages = $this->communication->receiveMessages($agent2Id);

    expect($messages)->toBeArray()
        ->and(count($messages))->toBeGreaterThan(0)
        ->and($messages[0]['from'])->toBe($agent1Id);

    // Share data
    $sharedData = ['training_plan' => ['week1' => 'speed', 'week2' => 'stamina']];
    $shareResult = $this->communication->shareData($agent1Id, 'training_plan', $sharedData);

    expect($shareResult['status'])->toBe('stored');

    // Retrieve shared data
    $retrieved = $this->communication->retrieveData($agent1Id, 'training_plan');

    expect($retrieved)->toBe($sharedData);

    // Cleanup
    $this->lifecycle->terminateAgent($agent1Id);
    $this->lifecycle->terminateAgent($agent2Id);
});

test('agent performance tracking and analytics', function () {
    // Create agent
    $config = [
        'name' => 'Performance Test Agent',
        'type' => 'training_optimizer',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Optimize training',
    ];

    $creation = $this->lifecycle->createAgent($config);
    $agentId = $creation['agent_id'];

    // Record multiple invocations
    for ($i = 0; $i < 10; $i++) {
        $this->analytics->recordInvocation($agentId, [
            'execution_time' => 0.5 + ($i * 0.1),
            'tokens_used' => 1000 + ($i * 100),
            'cost' => 0.003 + ($i * 0.001),
            'success' => $i < 9, // 9 successes, 1 failure
        ]);
    }

    // Get metrics
    $metrics = $this->analytics->getAgentMetrics($agentId);

    expect($metrics['total_invocations'])->toBe(10)
        ->and($metrics['successful_invocations'])->toBe(9)
        ->and($metrics['failed_invocations'])->toBe(1)
        ->and($metrics['success_rate'])->toBe(0.9)
        ->and($metrics['average_response_time'])->toBeFloat()
        ->and($metrics['total_cost'])->toBeFloat();

    // Get optimization recommendations
    $recommendations = $this->analytics->getOptimizationRecommendations($agentId);

    expect($recommendations)->toHaveKeys(['agent_id', 'recommendations', 'overall_score'])
        ->and($recommendations['recommendations'])->toBeArray()
        ->and($recommendations['overall_score'])->toBeFloat();

    // Cleanup
    $this->lifecycle->terminateAgent($agentId);
});

test('hierarchical workflow with coordinator and workers', function () {
    // Create coordinator agent
    $coordinatorConfig = [
        'name' => 'Coordinator Agent',
        'type' => 'coordinator',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Coordinate multi-agent workflows',
    ];

    $coordinator = $this->lifecycle->createAgent($coordinatorConfig);

    // Create worker agents
    $workers = [];
    for ($i = 1; $i <= 3; $i++) {
        $workerConfig = [
            'name' => "Worker Agent {$i}",
            'type' => 'worker',
            'model' => 'claude-3-5-haiku',
            'instructions' => "Execute subtask {$i}",
        ];

        $worker = $this->lifecycle->createAgent($workerConfig);
        $workers[] = $worker['agent_id'];
    }

    // Execute hierarchical workflow
    $workflowConfig = [
        'coordinator_agent_id' => $coordinator['agent_id'],
        'worker_agents' => $workers,
        'task' => 'comprehensive_analysis',
        'input' => ['character_id' => $this->character->id],
    ];

    $result = $this->workflow->executeHierarchicalWorkflow($workflowConfig);

    expect($result['status'])->toBe('completed')
        ->and($result['coordinator_result'])->toBeArray()
        ->and($result['worker_results'])->toBeArray()
        ->and(count($result['worker_results']))->toBeGreaterThan(0);

    // Cleanup
    $this->lifecycle->terminateAgent($coordinator['agent_id']);
    foreach ($workers as $workerId) {
        $this->lifecycle->terminateAgent($workerId);
    }
});

test('agent restart functionality', function () {
    // Create agent
    $config = [
        'name' => 'Restart Test Agent',
        'type' => 'training_optimizer',
        'model' => 'claude-3-5-sonnet',
        'instructions' => 'Optimize training',
    ];

    $creation = $this->lifecycle->createAgent($config);
    $originalAgentId = $creation['agent_id'];

    // Restart agent
    $restart = $this->lifecycle->restartAgent($originalAgentId);

    expect($restart['status'])->toBe('restarted')
        ->and($restart['agent_id'])->not->toBe($originalAgentId)
        ->and($restart['restart_time'])->toBeFloat();

    // Verify old agent is terminated
    $oldAgent = MCPAgent::where('agent_id', $originalAgentId)->first();
    expect($oldAgent->status)->toBe('terminated');

    // Verify new agent is active
    $newAgent = MCPAgent::where('agent_id', $restart['agent_id'])->first();
    expect($newAgent->status)->toBe('active');

    // Cleanup
    $this->lifecycle->terminateAgent($restart['agent_id']);
});

test('comprehensive career analysis workflow', function () {
    // Create all required agents
    $agentTypes = [
        'career_strategy_agent',
        'training_optimization_agent',
        'race_analysis_agent',
        'skill_management_agent',
    ];

    foreach ($agentTypes as $type) {
        $config = [
            'name' => ucfirst(str_replace('_', ' ', $type)),
            'type' => $type,
            'model' => 'claude-3-5-sonnet',
            'instructions' => "Provide {$type} analysis",
        ];

        $this->lifecycle->createAgent($config);
    }

    // Execute comprehensive career analysis
    $result = $this->workflow->executeCareerAnalysisWorkflow($this->character, [
        'target_grade' => 'A',
        'scenario' => 'ura_finale',
    ]);

    expect($result['status'])->toBe('completed')
        ->and($result['analysis'])->toHaveKeys([
            'career_strategy',
            'training_optimization',
            'race_analysis',
            'skill_management',
            'overall_confidence',
            'integrated_recommendations',
        ])
        ->and($result['execution_time'])->toBeFloat()
        ->and($result['total_cost'])->toBeFloat();

    // Cleanup all agents
    $agents = MCPAgent::where('status', 'active')->get();
    foreach ($agents as $agent) {
        $this->lifecycle->terminateAgent($agent->agent_id);
    }
});

test('agent performance comparison', function () {
    // Create multiple agents with different configurations
    $agents = [];

    $configs = [
        ['name' => 'Fast Agent', 'model' => 'claude-3-5-haiku'],
        ['name' => 'Balanced Agent', 'model' => 'claude-3-5-sonnet'],
        ['name' => 'Powerful Agent', 'model' => 'claude-opus-4-5'],
    ];

    foreach ($configs as $config) {
        $fullConfig = array_merge($config, [
            'type' => 'training_optimizer',
            'instructions' => 'Optimize training',
        ]);

        $creation = $this->lifecycle->createAgent($fullConfig);
        $agents[] = $creation['agent_id'];

        // Record some invocations
        for ($i = 0; $i < 5; $i++) {
            $this->analytics->recordInvocation($creation['agent_id'], [
                'execution_time' => rand(1, 10) / 10,
                'tokens_used' => rand(500, 2000),
                'cost' => rand(1, 10) / 1000,
                'success' => true,
            ]);
        }
    }

    // Compare agents
    $comparison = $this->analytics->compareAgents($agents);

    expect($comparison)->toHaveKeys(['comparison', 'best_performer', 'insights'])
        ->and($comparison['comparison'])->toHaveCount(3)
        ->and($comparison['best_performer'])->toHaveKeys(['agent_id', 'category', 'score'])
        ->and($comparison['insights'])->toBeArray();

    // Cleanup
    foreach ($agents as $agentId) {
        $this->lifecycle->terminateAgent($agentId);
    }
});

test('shared context management in workflows', function () {
    $workflowId = 'test_workflow_'.uniqid();

    // Create shared context
    $initialContext = [
        'character_id' => $this->character->id,
        'goals' => ['target_grade' => 'A'],
    ];

    $creation = $this->communication->createSharedContext($workflowId, $initialContext);

    expect($creation['status'])->toBe('created')
        ->and($creation['context_id'])->not->toBeEmpty();

    // Update shared context
    $updates = [
        'training_focus' => 'speed',
        'race_schedule' => ['week1' => 'G3', 'week2' => 'G2'],
    ];

    $update = $this->communication->updateSharedContext($creation['context_id'], $updates);

    expect($update['status'])->toBe('updated');

    // Retrieve shared context
    $context = $this->communication->getSharedContext($creation['context_id']);

    expect($context)->toHaveKeys(['character_id', 'goals', 'training_focus', 'race_schedule'])
        ->and($context['training_focus'])->toBe('speed');
});

test('lifecycle manager provides accurate statistics', function () {
    // Create multiple agents
    for ($i = 0; $i < 5; $i++) {
        $config = [
            'name' => "Agent {$i}",
            'type' => 'training_optimizer',
            'model' => 'claude-3-5-sonnet',
            'instructions' => 'Optimize training',
        ];

        $this->lifecycle->createAgent($config);
    }

    // Terminate some agents
    $agents = MCPAgent::where('status', 'active')->take(2)->get();
    foreach ($agents as $agent) {
        $this->lifecycle->terminateAgent($agent->agent_id);
    }

    // Get statistics
    $stats = $this->lifecycle->getStatistics();

    expect($stats)->toHaveKeys([
        'total_agents',
        'active_agents',
        'terminated_agents',
        'average_uptime_hours',
    ])
        ->and($stats['total_agents'])->toBeGreaterThanOrEqual(5)
        ->and($stats['active_agents'])->toBeGreaterThanOrEqual(3)
        ->and($stats['terminated_agents'])->toBeGreaterThanOrEqual(2);

    // Cleanup remaining agents
    $remainingAgents = MCPAgent::where('status', 'active')->get();
    foreach ($remainingAgents as $agent) {
        $this->lifecycle->terminateAgent($agent->agent_id);
    }
});
