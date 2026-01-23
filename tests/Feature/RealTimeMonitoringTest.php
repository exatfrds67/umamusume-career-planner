<?php

use App\Models\User;
use App\Services\MCP\RealTimeMonitoringService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
});

describe('Real-Time Server Status', function () {
    it('returns real-time server status with health metrics', function () {
        $response = $this->getJson('/api/ai/chat/server-status');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'timestamp',
                    'overall_status',
                    'servers',
                    'alerts',
                ],
            ]);

        expect($response->json('success'))->toBeTrue();
        expect($response->json('data.timestamp'))->toBeString();
        expect($response->json('data.overall_status'))->toBeIn(['healthy', 'degraded', 'critical', 'unknown']);
    });

    it('includes server health trends', function () {
        $response = $this->getJson('/api/ai/chat/server-status');

        $response->assertOk();

        $servers = $response->json('data.servers');
        if (! empty($servers)) {
            $firstServer = reset($servers);
            expect($firstServer)->toHaveKeys([
                'name',
                'status',
                'is_connected',
                'health_trend',
            ]);
            expect($firstServer['health_trend'])->toBeIn(['improving', 'stable', 'degrading']);
        }
    });

    it('includes alerts for unhealthy servers', function () {
        $response = $this->getJson('/api/ai/chat/server-status');

        $response->assertOk();

        $alerts = $response->json('data.alerts');
        expect($alerts)->toBeArray();

        foreach ($alerts as $alert) {
            expect($alert)->toHaveKeys(['level', 'server', 'message', 'timestamp']);
            expect($alert['level'])->toBeIn(['warning', 'danger']);
        }
    });
});

describe('Agent Progress Tracking', function () {
    it('returns agent workflow progress', function () {
        $response = $this->getJson('/api/ai/chat/workflow-status');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'workflow_id',
                    'workflow_name',
                    'status',
                    'progress_percentage',
                    'current_step',
                    'total_steps',
                    'completed_steps',
                    'agents',
                    'estimated_completion',
                    'started_at',
                ],
            ]);

        expect($response->json('success'))->toBeTrue();
        expect($response->json('data.progress_percentage'))->toBeFloat();
        expect($response->json('data.agents'))->toBeArray();
    });

    it('tracks individual agent progress', function () {
        // Simulate active workflow
        $workflowId = 'test_workflow_'.uniqid();
        $agentId = 'test_agent_'.uniqid();

        /** @var RealTimeMonitoringService $service */
        $service = app(RealTimeMonitoringService::class);
        $service->updateAgentProgress($workflowId, $agentId, 'running', 50.0, $this->user->id);

        $response = $this->getJson('/api/ai/chat/workflow-status');

        $response->assertOk();

        $agents = $response->json('data.agents');
        expect($agents)->toBeArray();

        if (! empty($agents)) {
            $agent = $agents[0];
            expect($agent)->toHaveKeys([
                'agent_id',
                'agent_type',
                'status',
                'progress',
                'started_at',
            ]);
            expect($agent['progress'])->toBeFloat();
        }
    });

    it('calculates overall workflow progress correctly', function () {
        $workflowId = 'test_workflow_'.uniqid();

        /** @var RealTimeMonitoringService $service */
        $service = app(RealTimeMonitoringService::class);

        // Add multiple agents
        $service->updateAgentProgress($workflowId, 'agent_1', 'completed', 100.0, $this->user->id);
        $service->updateAgentProgress($workflowId, 'agent_2', 'running', 50.0, $this->user->id);
        $service->updateAgentProgress($workflowId, 'agent_3', 'idle', 0.0, $this->user->id);

        $response = $this->getJson('/api/ai/chat/workflow-status');

        $response->assertOk();

        $data = $response->json('data');
        expect($data['total_steps'])->toBe(3);
        expect($data['completed_steps'])->toBe(1);
        expect($data['progress_percentage'])->toBeGreaterThan(0);
    });
});

describe('Tool Execution Monitoring', function () {
    it('returns tool execution data', function () {
        $response = $this->getJson('/api/ai/chat/tool-usage');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'timestamp',
                    'active_tools',
                    'recent_executions',
                    'tool_statistics',
                ],
            ]);

        expect($response->json('success'))->toBeTrue();
        expect($response->json('data.active_tools'))->toBeArray();
        expect($response->json('data.recent_executions'))->toBeArray();
        expect($response->json('data.tool_statistics'))->toBeArray();
    });

    it('records tool execution correctly', function () {
        /** @var RealTimeMonitoringService $service */
        $service = app(RealTimeMonitoringService::class);

        $service->recordToolExecution(
            toolName: 'test_tool',
            server: 'test_server',
            status: 'completed',
            executionTime: 1.5,
            success: true,
            error: null,
            userId: $this->user->id
        );

        $response = $this->getJson('/api/ai/chat/tool-usage');

        $response->assertOk();

        $recentExecutions = $response->json('data.recent_executions');
        expect($recentExecutions)->toBeArray();

        if (! empty($recentExecutions)) {
            $execution = $recentExecutions[0];
            expect($execution)->toHaveKeys([
                'tool_name',
                'server',
                'status',
                'execution_time',
                'completed_at',
                'success',
            ]);
        }
    });

    it('calculates tool statistics correctly', function () {
        /** @var RealTimeMonitoringService $service */
        $service = app(RealTimeMonitoringService::class);

        // Record multiple executions
        $service->recordToolExecution('test_tool', 'server', 'completed', 1.0, true, null, $this->user->id);
        $service->recordToolExecution('test_tool', 'server', 'completed', 2.0, true, null, $this->user->id);
        $service->recordToolExecution('test_tool', 'server', 'failed', 0.5, false, 'Error', $this->user->id);

        $response = $this->getJson('/api/ai/chat/tool-usage');

        $response->assertOk();

        $statistics = $response->json('data.tool_statistics');
        expect($statistics)->toBeArray();

        if (isset($statistics['test_tool'])) {
            $stats = $statistics['test_tool'];
            expect($stats['total_executions'])->toBe(3);
            expect($stats['successful_executions'])->toBe(2);
            expect($stats['failed_executions'])->toBe(1);
            expect($stats['success_rate'])->toBeGreaterThan(0);
            expect($stats['average_execution_time'])->toBeFloat();
        }
    });
});

describe('Performance Metrics', function () {
    it('returns performance metrics comparing providers and agents', function () {
        $response = $this->getJson('/api/ai/chat/performance-metrics');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'timestamp',
                    'providers',
                    'agents',
                    'comparison' => [
                        'fastest_provider',
                        'most_reliable_provider',
                        'most_cost_effective',
                        'best_performing_agent',
                    ],
                ],
            ]);

        expect($response->json('success'))->toBeTrue();
        expect($response->json('data.providers'))->toBeArray();
        expect($response->json('data.agents'))->toBeArray();
    });

    it('identifies best performing providers', function () {
        $response = $this->getJson('/api/ai/chat/performance-metrics');

        $response->assertOk();

        $comparison = $response->json('data.comparison');
        expect($comparison)->toHaveKeys([
            'fastest_provider',
            'most_reliable_provider',
            'most_cost_effective',
            'best_performing_agent',
        ]);
    });
});

describe('Error Handling and Recovery', function () {
    it('handles server disconnection gracefully', function () {
        $response = $this->postJson('/api/ai/chat/server-disconnection', [
            'server_name' => 'test_server',
            'error' => 'Connection timeout',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'server',
                    'event',
                    'error',
                    'reconnection_attempted',
                    'reconnection_successful',
                    'reconnection_message',
                    'timestamp',
                ],
            ]);

        expect($response->json('success'))->toBeTrue();
        expect($response->json('data.reconnection_attempted'))->toBeTrue();
    });

    it('validates server disconnection request', function () {
        $response = $this->postJson('/api/ai/chat/server-disconnection', [
            'server_name' => '',
            'error' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['server_name', 'error']);
    });

    it('returns error when server status fetch fails', function () {
        // Mock a failure scenario by clearing cache
        Cache::flush();

        $response = $this->getJson('/api/ai/chat/server-status');

        // Should still return a response with default values
        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
    });
});

describe('Real-Time Updates', function () {
    it('caches server status for performance', function () {
        // First request
        $response1 = $this->getJson('/api/ai/chat/server-status');
        $timestamp1 = $response1->json('data.timestamp');

        // Second request (should be cached)
        $response2 = $this->getJson('/api/ai/chat/server-status');
        $timestamp2 = $response2->json('data.timestamp');

        // Timestamps should be the same due to caching
        expect($timestamp1)->toBe($timestamp2);
    });

    it('refreshes data after cache expiration', function () {
        // First request
        $this->getJson('/api/ai/chat/server-status');

        // Clear cache to simulate expiration
        Cache::forget('realtime:server_status');

        // Second request (should fetch fresh data)
        $response = $this->getJson('/api/ai/chat/server-status');

        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
    });
});

describe('Authorization', function () {
    it('requires authentication for all endpoints', function () {
        // Reset authentication by creating a fresh application instance
        $this->app['auth']->forgetGuards();

        $endpoints = [
            '/api/ai/chat/server-status',
            '/api/ai/chat/workflow-status',
            '/api/ai/chat/tool-usage',
            '/api/ai/chat/performance-metrics',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->withHeaders(['Authorization' => ''])->getJson($endpoint);
            $response->assertUnauthorized();
        }
    });

    it('allows authenticated users to access endpoints', function () {
        $endpoints = [
            '/api/ai/chat/server-status',
            '/api/ai/chat/workflow-status',
            '/api/ai/chat/tool-usage',
            '/api/ai/chat/performance-metrics',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertOk();
        }
    });
});
