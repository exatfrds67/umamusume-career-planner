<?php

declare(strict_types=1);

/**
 * @property \App\Models\User $user
 * @property \App\Models\Character $character
 */

use App\Models\AIConversation;
use App\Models\Character;
use App\Models\User;
use App\Services\MCP\AgentOrchestrationService;
use App\Services\MCP\AgentRoutingService;
use App\Services\MCP\MCPMonitoringService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
});

describe('AI Chat Interface', function () {
    it('displays the chat interface for authenticated users', function () {
        actingAs($this->user)
            ->get(route('ai.chat'))
            ->assertOk()
            ->assertViewIs('ai.chat')
            ->assertSee('AI Career Assistant');
    });

    it('displays character context when character_id is provided', function () {
        actingAs($this->user)
            ->get(route('ai.chat', ['character_id' => $this->character->id]))
            ->assertOk()
            ->assertViewHas('character', function ($character) {
                return $character->id === $this->character->id;
            });
    });

    it('requires authentication to access chat interface', function () {
        get(route('ai.chat'))
            ->assertRedirect(route('login'));
    });

    it('prevents accessing other users characters', function () {
        $otherUser = User::factory()->create();
        $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);

        actingAs($this->user)
            ->get(route('ai.chat', ['character_id' => $otherCharacter->id]))
            ->assertNotFound();
    });
});

describe('Send Message API', function () {
    it('sends a message and receives AI response', function () {
        // Mock the routing service
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('routeRequest')
                ->once()
                ->andReturn([
                    'content' => 'This is a test AI response',
                    'model' => 'llama3.3',
                    'provider' => 'ollama',
                    'processing_time' => 1.5,
                    'tokens' => 150,
                    'cost' => 0.0,
                    'conversation_id' => 'test-conv-id',
                ]);
        });

        actingAs($this->user)
            ->post(route('api.ai.chat.message'), [
                'message' => 'What training should I focus on?',
                'character_id' => $this->character->id,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'metadata' => [
                    'model',
                    'provider',
                    'processing_time',
                    'tokens',
                    'cost',
                ],
                'conversation_id',
            ]);
    });

    it('validates message length', function () {
        actingAs($this->user)
            ->post(route('api.ai.chat.message'), [
                'message' => str_repeat('a', 2001), // Exceeds 2000 char limit
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    });

    it('requires message field', function () {
        actingAs($this->user)
            ->post(route('api.ai.chat.message'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    });

    it('logs conversation to database', function () {
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('routeRequest')
                ->once()
                ->andReturn([
                    'content' => 'Test response',
                    'model' => 'llama3.3',
                    'provider' => 'ollama',
                    'processing_time' => 1.0,
                    'conversation_id' => 'test-conv-id',
                ]);
        });

        actingAs($this->user)
            ->post(route('api.ai.chat.message'), [
                'message' => 'Test message',
                'character_id' => $this->character->id,
            ]);

        expect(AIConversation::count())->toBe(2); // User message + AI response
        expect(AIConversation::where('message_type', 'user')->count())->toBe(1);
        expect(AIConversation::where('message_type', 'ai')->count())->toBe(1);
    });

    it('includes character context in request', function () {
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('routeRequest')
                ->once()
                ->with(
                    \Mockery::any(),
                    \Mockery::on(function ($context) {
                        return isset($context['character'])
                            && $context['character']['id'] === $this->character->id;
                    }),
                    \Mockery::any(),
                    \Mockery::any()
                )
                ->andReturn([
                    'content' => 'Response with context',
                    'model' => 'llama3.3',
                    'provider' => 'ollama',
                    'processing_time' => 1.0,
                    'conversation_id' => 'test-conv-id',
                ]);
        });

        actingAs($this->user)
            ->post(route('api.ai.chat.message'), [
                'message' => 'Test with character context',
                'character_id' => $this->character->id,
            ])
            ->assertOk();
    });

    it('handles provider preference', function () {
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('routeRequest')
                ->once()
                ->with(
                    \Mockery::any(),
                    \Mockery::any(),
                    'bedrock',
                    \Mockery::any()
                )
                ->andReturn([
                    'content' => 'Bedrock response',
                    'model' => 'claude-3.5-sonnet',
                    'provider' => 'bedrock',
                    'processing_time' => 2.0,
                    'conversation_id' => 'test-conv-id',
                ]);
        });

        actingAs($this->user)
            ->post(route('api.ai.chat.message'), [
                'message' => 'Test with bedrock',
                'provider' => 'bedrock',
            ])
            ->assertOk();
    });

    it('handles agent type selection', function () {
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('routeRequest')
                ->once()
                ->with(
                    \Mockery::any(),
                    \Mockery::any(),
                    \Mockery::any(),
                    'training'
                )
                ->andReturn([
                    'content' => 'Training agent response',
                    'model' => 'training-agent',
                    'provider' => 'agent',
                    'agent' => 'training',
                    'processing_time' => 1.5,
                    'conversation_id' => 'test-conv-id',
                ]);
        });

        actingAs($this->user)
            ->post(route('api.ai.chat.message'), [
                'message' => 'Training advice please',
                'agent_type' => 'training',
            ])
            ->assertOk();
    });
});

describe('Server Status API', function () {
    it('returns MCP server status', function () {
        $this->mock(MCPMonitoringService::class, function ($mock) {
            $mock->shouldReceive('getServerStatus')
                ->once()
                ->andReturn([
                    'ollama' => [
                        'display_name' => 'Ollama',
                        'status' => 'healthy',
                        'response_time' => 50,
                        'uptime' => '99.9%',
                    ],
                    'bedrock' => [
                        'display_name' => 'AWS Bedrock',
                        'status' => 'healthy',
                        'response_time' => 150,
                        'uptime' => '99.5%',
                    ],
                ]);
        });

        actingAs($this->user)
            ->get(route('api.ai.chat.server-status'))
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'servers',
                'timestamp',
            ]);
    });

    it('handles server status errors gracefully', function () {
        $this->mock(MCPMonitoringService::class, function ($mock) {
            $mock->shouldReceive('getServerStatus')
                ->once()
                ->andThrow(new \Exception('Server unavailable'));
        });

        actingAs($this->user)
            ->get(route('api.ai.chat.server-status'))
            ->assertStatus(500)
            ->assertJson([
                'success' => false,
                'servers' => [],
            ]);
    });
});

describe('Workflow Status API', function () {
    it('returns current workflow status', function () {
        $this->mock(AgentOrchestrationService::class, function ($mock) {
            $mock->shouldReceive('getCurrentWorkflow')
                ->once()
                ->with($this->user->id)
                ->andReturn([
                    'name' => 'Training Optimization',
                    'status' => 'running',
                    'steps' => [
                        [
                            'id' => 1,
                            'name' => 'Analyze current stats',
                            'status' => 'completed',
                            'agent' => 'training-agent',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Calculate predictions',
                            'status' => 'running',
                            'agent' => 'training-agent',
                            'progress' => 50,
                        ],
                    ],
                ]);
        });

        actingAs($this->user)
            ->get(route('api.ai.chat.workflow-status'))
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'workflow' => [
                    'name',
                    'status',
                    'steps',
                ],
                'timestamp',
            ]);
    });

    it('returns null when no active workflow', function () {
        $this->mock(AgentOrchestrationService::class, function ($mock) {
            $mock->shouldReceive('getCurrentWorkflow')
                ->once()
                ->with($this->user->id)
                ->andReturn(null);
        });

        actingAs($this->user)
            ->get(route('api.ai.chat.workflow-status'))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'workflow' => null,
            ]);
    });
});

describe('Tool Usage API', function () {
    it('returns active tool usage', function () {
        $this->mock(MCPMonitoringService::class, function ($mock) {
            $mock->shouldReceive('getActiveTools')
                ->once()
                ->with($this->user->id)
                ->andReturn([
                    [
                        'id' => 1,
                        'name' => 'training-calculator',
                        'status' => 'running',
                        'duration' => 2.5,
                    ],
                ]);
        });

        actingAs($this->user)
            ->get(route('api.ai.chat.tool-usage'))
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'tools',
                'timestamp',
            ]);
    });

    it('returns empty array when no active tools', function () {
        $this->mock(MCPMonitoringService::class, function ($mock) {
            $mock->shouldReceive('getActiveTools')
                ->once()
                ->with($this->user->id)
                ->andReturn([]);
        });

        actingAs($this->user)
            ->get(route('api.ai.chat.tool-usage'))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'tools' => [],
            ]);
    });
});

describe('User Preferences API', function () {
    it('returns default preferences for new users', function () {
        actingAs($this->user)
            ->get(route('api.ai.chat.preferences.get'))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'preferences' => [
                    'provider' => 'ollama',
                    'model' => 'llama3.3',
                    'selected_agent' => null,
                    'auto_fallback' => true,
                ],
            ]);
    });

    it('saves user preferences', function () {
        actingAs($this->user)
            ->post(route('api.ai.chat.preferences.update'), [
                'provider' => 'bedrock',
                'model' => 'claude-3.5-sonnet',
                'selected_agent' => 'training',
                'auto_fallback' => false,
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // Verify preferences were saved
        $cacheKey = 'ai_chat_preferences_'.$this->user->id;
        $preferences = Cache::get($cacheKey);

        expect($preferences)->toMatchArray([
            'provider' => 'bedrock',
            'model' => 'claude-3.5-sonnet',
            'selected_agent' => 'training',
            'auto_fallback' => false,
        ]);
    });

    it('retrieves saved preferences', function () {
        // Save preferences first
        $cacheKey = 'ai_chat_preferences_'.$this->user->id;
        Cache::put($cacheKey, [
            'provider' => 'bedrock',
            'model' => 'claude-3.5-sonnet',
        ], now()->addDays(30));

        actingAs($this->user)
            ->get(route('api.ai.chat.preferences.get'))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'preferences' => [
                    'provider' => 'bedrock',
                    'model' => 'claude-3.5-sonnet',
                ],
            ]);
    });
});
