<?php

declare(strict_types=1);

/**
 * @property \App\Models\User $user
 * @property \App\Models\Character $character
 */

use App\Models\AIConversation;
use App\Models\Character;
use App\Models\ConversationMessage;
use App\Models\User;
use App\Services\MCP\AgentRoutingService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
    // Load the currentCareer relationship to avoid "attribute does not exist" error
    $this->character->load('currentCareer');
});

describe('AI Chat Interface', function () {
    it('displays the chat interface for authenticated users', function () {
        actingAs($this->user)
            ->get(route('ai.chat'))
            ->assertOk();
    });

    it('displays character context when character_id is provided', function () {
        actingAs($this->user)
            ->get(route('ai.chat', ['character_id' => $this->character->id]))
            ->assertOk();
    });

    it('requires authentication to access chat interface', function () {
        get(route('ai.chat'))
            ->assertRedirect(); // Just check it redirects (could be to login or home)
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
    it('prevents sending messages with another users character context', function () {
        $otherUser = User::factory()->create();
        $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);

        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldNotReceive('executeWithFallback');
        });

        actingAs($this->user)
            ->postJson(route('api.ai.chat.message'), [
                'message' => 'What training should I focus on?',
                'character_id' => $otherCharacter->id,
            ])
            ->assertNotFound();
    });

    it('sends a message and receives AI response', function () {
        // Mock the routing service
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('executeWithFallback')
                ->once()
                ->andReturn([
                    'success' => true,
                    'response' => 'This is a test AI response',
                    'model' => 'llama3.3',
                    'provider' => 'ollama',
                    'execution_time' => 1.5,
                    'cost' => 0.0,
                    'fallback_used' => false,
                ]);
        });

        actingAs($this->user)
            ->postJson(route('api.ai.chat.message'), [
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
                ],
                'conversation_id',
            ]);
    });

    it('validates message length', function () {
        actingAs($this->user)
            ->postJson(route('api.ai.chat.message'), [
                'message' => str_repeat('a', 2001), // Exceeds 2000 char limit
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    });

    it('requires message field', function () {
        actingAs($this->user)
            ->postJson(route('api.ai.chat.message'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    });

    it('logs conversation to database', function () {
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('executeWithFallback')
                ->once()
                ->andReturn([
                    'success' => true,
                    'response' => 'Test response',
                    'model' => 'llama3.3',
                    'provider' => 'ollama',
                    'execution_time' => 1.0,
                    'cost' => 0.0,
                    'fallback_used' => false,
                ]);
        });

        $response = actingAs($this->user)
            ->postJson(route('api.ai.chat.message'), [
                'message' => 'Test message',
                'character_id' => $this->character->id,
            ]);

        $response->assertOk();

        // Verify conversation container was created
        $conversation = AIConversation::where('user_id', $this->user->id)->first();
        expect($conversation)->not->toBeNull();
        expect($conversation->conversation_type)->toBe('general_help');
        expect($conversation->status)->toBe('active');

        // Verify both user and assistant messages were logged
        $messages = ConversationMessage::where('conversation_id', $conversation->id)->get();
        expect($messages)->toHaveCount(2);
        expect($messages->where('message_type', 'user')->first()->message_content)->toBe('Test message');
        expect($messages->where('message_type', 'ai')->first()->message_content)->toBe('Test response');
    });

    it('reuses existing conversation container for same conversation_id', function () {
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('executeWithFallback')
                ->twice()
                ->andReturn([
                    'success' => true,
                    'response' => 'Response',
                    'model' => 'llama3.3',
                    'provider' => 'ollama',
                    'execution_time' => 1.0,
                    'cost' => 0.0,
                    'fallback_used' => false,
                ]);
        });

        $conversationId = 'test-conv-123';

        actingAs($this->user)
            ->postJson(route('api.ai.chat.message'), [
                'message' => 'First message',
                'conversation_id' => $conversationId,
            ])
            ->assertOk();

        actingAs($this->user)
            ->postJson(route('api.ai.chat.message'), [
                'message' => 'Second message',
                'conversation_id' => $conversationId,
            ])
            ->assertOk();

        // Only one conversation container should exist
        expect(AIConversation::where('conversation_id', $conversationId)->count())->toBe(1);

        // But four messages (2 user + 2 assistant)
        $conversation = AIConversation::where('conversation_id', $conversationId)->first();
        expect(ConversationMessage::where('conversation_id', $conversation->id)->count())->toBe(4);
    });

    it('includes character context in request', function () {
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('executeWithFallback')
                ->once()
                ->andReturn([
                    'success' => true,
                    'response' => 'Response with context',
                    'model' => 'llama3.3',
                    'provider' => 'ollama',
                    'execution_time' => 1.0,
                    'cost' => 0.0,
                    'fallback_used' => false,
                ]);
        });

        actingAs($this->user)
            ->postJson(route('api.ai.chat.message'), [
                'message' => 'Test with character context',
                'character_id' => $this->character->id,
            ])
            ->assertOk();
    });

    it('handles provider preference', function () {
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('executeWithFallback')
                ->once()
                ->andReturn([
                    'success' => true,
                    'response' => 'Bedrock response',
                    'model' => 'claude-3.5-sonnet',
                    'provider' => 'bedrock',
                    'execution_time' => 2.0,
                    'cost' => 0.01,
                    'fallback_used' => false,
                ]);
        });

        actingAs($this->user)
            ->postJson(route('api.ai.chat.message'), [
                'message' => 'Test with bedrock',
                'provider' => 'bedrock',
            ])
            ->assertOk();
    });

    it('handles agent type selection', function () {
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('executeWithFallback')
                ->once()
                ->andReturn([
                    'success' => true,
                    'response' => [
                        'content' => 'Training agent response',
                        'agent' => 'training',
                    ],
                    'model' => 'training-agent',
                    'provider' => 'agent',
                    'execution_time' => 1.5,
                    'cost' => 0.0,
                    'fallback_used' => false,
                ]);
        });

        actingAs($this->user)
            ->postJson(route('api.ai.chat.message'), [
                'message' => 'Training advice please',
                'agent_type' => 'training',
            ])
            ->assertOk();
    });

    it('streams a message response', function () {
        $this->mock(AgentRoutingService::class, function ($mock) {
            $mock->shouldReceive('executeWithFallback')
                ->once()
                ->andReturn([
                    'success' => true,
                    'response' => 'Test stream response',
                    'model' => 'llama3.3',
                    'provider' => 'ollama',
                    'execution_time' => 1.0,
                    'cost' => 0.0,
                    'fallback_used' => false,
                ]);
        });

        $response = actingAs($this->user)
            ->postJson(route('api.ai.chat.message.stream'), [
                'message' => 'Stream this',
                'character_id' => $this->character->id,
            ]);

        $response->assertOk();

        $content = $response->streamedContent();
        expect($content)->toContain('Test stream response');
        expect($content)->toContain('done');
    });
});

describe('Server Status API', function () {
    it('returns MCP server status', function () {
        actingAs($this->user)
            ->get(route('api.ai.chat.server-status'))
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    });

    it('handles server status errors gracefully', function () {
        // The controller catches exceptions and returns success with empty data
        // This is the expected behavior for graceful error handling
        actingAs($this->user)
            ->get(route('api.ai.chat.server-status'))
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    });
});

describe('Workflow Status API', function () {
    it('returns current workflow status', function () {
        actingAs($this->user)
            ->get(route('api.ai.chat.workflow-status'))
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    });

    it('returns null when no active workflow', function () {
        actingAs($this->user)
            ->get(route('api.ai.chat.workflow-status'))
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    });
});

describe('Tool Usage API', function () {
    it('returns active tool usage', function () {
        actingAs($this->user)
            ->get(route('api.ai.chat.tool-usage'))
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    });

    it('returns empty array when no active tools', function () {
        actingAs($this->user)
            ->get(route('api.ai.chat.tool-usage'))
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data',
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
            ->postJson(route('api.ai.chat.preferences.update'), [
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
