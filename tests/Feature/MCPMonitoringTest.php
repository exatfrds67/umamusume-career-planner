<?php

use App\Models\MCPAgent;
use App\Models\MCPServer;
use App\Models\MCPToolUsage;
use App\Models\User;
use App\Models\UserPreference;
use App\Services\MCPMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;


beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
    $this->service = app(MCPMonitoringService::class);
});

describe('MCP Server Management', function () {
    it('can get server status', function () {
        MCPServer::factory()->count(3)->create([
            'status' => 'active',
        ]);

        MCPServer::factory()->create([
            'status' => 'error',
        ]);

        $response = $this->getJson('/api/mcp/monitoring/servers/status');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'active',
                    'inactive',
                    'error',
                    'servers',
                ],
            ]);

        expect($response->json('data.total'))->toBe(4);
        expect($response->json('data.active'))->toBe(3);
        expect($response->json('data.error'))->toBe(1);
    });

    it('can connect to a server', function () {
        $server = MCPServer::factory()->create([
            'status' => 'inactive',
        ]);

        $response = $this->postJson("/api/mcp/monitoring/servers/{$server->id}/connect");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $server->refresh();
        expect($server->status)->toBe('active');
    });

    it('can disconnect from a server', function () {
        $server = MCPServer::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->postJson("/api/mcp/monitoring/servers/{$server->id}/disconnect");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $server->refresh();
        expect($server->status)->toBe('inactive');
    });

    it('can update server configuration', function () {
        $server = MCPServer::factory()->create();

        $config = [
            'timeout' => 30,
            'retry_attempts' => 3,
        ];

        $response = $this->putJson("/api/mcp/monitoring/servers/{$server->id}/config", [
            'config' => $config,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $server->refresh();
        expect($server->server_config)->toHaveKey('timeout', 30);
        expect($server->server_config)->toHaveKey('retry_attempts', 3);
    });
});

describe('Agent Lifecycle Management', function () {
    it('can get agent status', function () {
        MCPAgent::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        MCPAgent::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'terminated',
        ]);

        $response = $this->getJson('/api/mcp/monitoring/agents/status');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total',
                    'active',
                    'terminated',
                    'healthy',
                    'agents',
                ],
            ]);

        expect($response->json('data.total'))->toBe(3);
        expect($response->json('data.active'))->toBe(2);
        expect($response->json('data.terminated'))->toBe(1);
    });

    it('can create a new agent', function () {
        $agentData = [
            'name' => 'Test Agent',
            'type' => 'training-optimizer',
            'model' => 'claude-3-5-sonnet',
            'instructions' => 'Optimize training sequences',
            'tools' => ['training-prediction', 'stat-analysis'],
            'memory_config' => ['max_tokens' => 4000],
        ];

        $response = $this->postJson('/api/mcp/monitoring/agents', $agentData);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'agent_id',
                    'name',
                    'type',
                    'model',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('ucp_mcp_agents', [
            'user_id' => $this->user->id,
            'name' => 'Test Agent',
            'type' => 'training-optimizer',
        ]);
    });

    it('can terminate an agent', function () {
        $agent = MCPAgent::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->postJson("/api/mcp/monitoring/agents/{$agent->id}/terminate");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $agent->refresh();
        expect($agent->status)->toBe('terminated');
        expect($agent->terminated_at)->not->toBeNull();
    });
});

describe('Cost Tracking', function () {
    it('can get cost analytics', function () {
        MCPToolUsage::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'cost_estimate' => 0.05,
            'executed_at' => now(),
        ]);

        $response = $this->getJson('/api/mcp/monitoring/costs?period=month');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'period',
                    'total_cost',
                    'total_tokens',
                    'total_requests',
                    'average_cost_per_request',
                    'cost_by_server',
                    'cost_by_tool',
                    'daily_costs',
                ],
            ]);

        expect($response->json('data.total_cost'))->toBe(0.5);
        expect($response->json('data.total_requests'))->toBe(10);
    });

    it('can filter cost analytics by period', function () {
        // Create tool usage for different periods
        MCPToolUsage::factory()->create([
            'user_id' => $this->user->id,
            'cost_estimate' => 0.10,
            'executed_at' => now()->subDays(2),
        ]);

        MCPToolUsage::factory()->create([
            'user_id' => $this->user->id,
            'cost_estimate' => 0.05,
            'executed_at' => now(),
        ]);

        // Get daily costs
        $response = $this->getJson('/api/mcp/monitoring/costs?period=day');

        $response->assertOk();
        expect($response->json('data.total_cost'))->toBe(0.05);

        // Get weekly costs
        $response = $this->getJson('/api/mcp/monitoring/costs?period=week');

        $response->assertOk();
        expect($response->json('data.total_cost'))->toBe(0.15);
    });
});

describe('Performance Metrics', function () {
    it('can get performance metrics', function () {
        MCPToolUsage::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'execution_status' => 'success',
            'execution_time' => 1.5,
            'executed_at' => now(),
        ]);

        MCPToolUsage::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'execution_status' => 'failure',
            'execution_time' => 0.5,
            'executed_at' => now(),
        ]);

        $response = $this->getJson('/api/mcp/monitoring/performance?period=day');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'period',
                    'total_requests',
                    'successful_requests',
                    'failed_requests',
                    'success_rate',
                    'average_execution_time',
                    'performance_by_server',
                    'slowest_tools',
                ],
            ]);

        expect($response->json('data.total_requests'))->toBe(7);
        expect($response->json('data.successful_requests'))->toBe(5);
        expect($response->json('data.failed_requests'))->toBe(2);
        expect($response->json('data.success_rate'))->toBeGreaterThan(70);
    });
});

describe('Optimization Recommendations', function () {
    it('can get optimization recommendations', function () {
        // Create a server with high failure rate
        MCPServer::factory()->create([
            'server_name' => 'slow-server',
            'total_requests' => 100,
            'failed_requests' => 15,
            'average_response_time' => 6.0,
        ]);

        $response = $this->getJson('/api/mcp/monitoring/recommendations');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'type',
                        'severity',
                        'title',
                        'description',
                        'action',
                    ],
                ],
            ]);

        $recommendations = $response->json('data');
        expect($recommendations)->not->toBeEmpty();
    });

    it('recommends cleanup for inactive agents', function () {
        MCPAgent::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
            'last_health_check' => now()->subDays(2),
        ]);

        $response = $this->getJson('/api/mcp/monitoring/recommendations');

        $response->assertOk();

        $recommendations = $response->json('data');
        $hasCleanupRecommendation = collect($recommendations)->contains(function ($rec) {
            return $rec['type'] === 'agent_lifecycle';
        });

        expect($hasCleanupRecommendation)->toBeTrue();
    });
});

describe('User Preferences', function () {
    it('can get user preferences', function () {
        UserPreference::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'preference_category' => 'mcp',
        ]);

        $response = $this->getJson('/api/mcp/monitoring/preferences?category=mcp');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);

        expect($response->json('data'))->toHaveCount(3);
    });

    it('can update user preference', function () {
        $response = $this->postJson('/api/mcp/monitoring/preferences', [
            'key' => 'default_agent',
            'value' => 'claude-3-5-sonnet',
            'category' => 'mcp',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('ucp_user_preferences', [
            'user_id' => $this->user->id,
            'preference_key' => 'default_agent',
            'preference_category' => 'mcp',
        ]);
    });

    it('updates existing preference instead of creating duplicate', function () {
        UserPreference::factory()->create([
            'user_id' => $this->user->id,
            'preference_category' => 'mcp',
            'preference_key' => 'default_agent',
            'preference_value' => ['value' => 'old-model'],
        ]);

        $response = $this->postJson('/api/mcp/monitoring/preferences', [
            'key' => 'default_agent',
            'value' => 'new-model',
            'category' => 'mcp',
        ]);

        $response->assertOk();

        $preferences = UserPreference::where('user_id', $this->user->id)
            ->where('preference_key', 'default_agent')
            ->get();

        expect($preferences)->toHaveCount(1);
        expect($preferences->first()->preference_value)->toBe('new-model');
    });
});

describe('Tool Usage History', function () {
    it('can get tool usage history', function () {
        MCPToolUsage::factory()->count(50)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/mcp/monitoring/tool-usage?limit=20');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);

        expect($response->json('data'))->toHaveCount(20);
    });

    it('orders tool usage by most recent first', function () {
        $oldest = MCPToolUsage::factory()->create([
            'user_id' => $this->user->id,
            'executed_at' => now()->subHours(2),
        ]);

        $newest = MCPToolUsage::factory()->create([
            'user_id' => $this->user->id,
            'executed_at' => now(),
        ]);

        $response = $this->getJson('/api/mcp/monitoring/tool-usage');

        $response->assertOk();

        $data = $response->json('data');
        expect($data[0]['id'])->toBe($newest->id);
        expect($data[1]['id'])->toBe($oldest->id);
    });
});

describe('Dashboard Integration', function () {
    it('can get comprehensive dashboard data', function () {
        // Create test data
        MCPServer::factory()->count(2)->create();
        MCPAgent::factory()->count(3)->create(['user_id' => $this->user->id]);
        MCPToolUsage::factory()->count(10)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/mcp/monitoring/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'servers',
                    'agents',
                    'costs',
                    'performance',
                    'recommendations',
                ],
            ]);
    });
});
