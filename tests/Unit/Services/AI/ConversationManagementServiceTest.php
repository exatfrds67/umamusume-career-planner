<?php

declare(strict_types=1);

use App\Models\AIConversation;
use App\Models\ConversationMessage;
use App\Models\User;
use App\Services\AI\ConversationManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = new ConversationManagementService;
});

describe('ConversationManagementService', function () {
    describe('createConversation', function () {
        it('creates a new conversation successfully', function () {
            $conversation = $this->service->createConversation(
                $this->user,
                'career_planning',
                'My Career Plan'
            );

            expect($conversation)->toBeInstanceOf(AIConversation::class);
            expect($conversation->user_id)->toBe($this->user->id);
            expect($conversation->conversation_type)->toBe('career_planning');
            expect($conversation->conversation_title)->toBe('My Career Plan');
            expect($conversation->status)->toBe('active');
            expect($conversation->message_count)->toBe(0);
            expect($conversation->conversation_id)->not->toBeNull();
        });

        it('generates default title when not provided', function () {
            $conversation = $this->service->createConversation(
                $this->user,
                'career_planning'
            );

            expect($conversation->conversation_title)->toBe('Career Planning Session');
        });

        it('stores context entities and AI configuration', function () {
            $contextEntities = ['character_id' => 1, 'career_run_id' => 5];
            $aiConfiguration = ['model' => 'claude-3-5-sonnet', 'version' => '2.0'];

            $conversation = $this->service->createConversation(
                $this->user,
                'training_advice',
                null,
                $contextEntities,
                $aiConfiguration
            );

            expect($conversation->context_entities)->toBe($contextEntities);
            expect($conversation->ai_model)->toBe('claude-3-5-sonnet');
            expect($conversation->ai_version)->toBe('2.0');
            expect($conversation->ai_configuration)->toBe($aiConfiguration);
        });
    });

    describe('addMessage', function () {
        it('adds message to conversation', function () {
            $conversation = $this->service->createConversation($this->user, 'general_help');

            $message = $this->service->addMessage(
                $conversation,
                'user',
                'How do I optimize stats?',
                null,
                null,
                null,
                [],
                [],
                []
            );

            expect($message)->toBeInstanceOf(ConversationMessage::class);
            expect($message->conversation_id)->toBe($conversation->id);
            expect($message->message_type)->toBe('user');
            expect($message->message_content)->toBe('How do I optimize stats?');
            expect($message->status)->toBe('completed');

            $conversation->refresh();
            expect($conversation->message_count)->toBe(1);
        });

        it('adds AI message with agent attribution and tool usage', function () {
            $conversation = $this->service->createConversation($this->user, 'skill_optimization');

            $toolsUsed = [
                ['name' => 'skill_calculator', 'parameters' => ['skill_id' => 123]],
            ];
            $toolResults = [
                'skill_calculator' => ['success' => true, 'result' => 'SP: 120'],
            ];
            $metadata = [
                'ai_model' => 'claude-3-5-sonnet',
                'processing_time' => 1.5,
                'tokens_used' => 250,
                'cost_estimate' => 0.015,
            ];

            $message = $this->service->addMessage(
                $conversation,
                'ai',
                'Based on analysis, prioritize Speed training',
                'agent-001',
                'training_advisor',
                'Training Advisor Agent',
                $toolsUsed,
                $toolResults,
                $metadata
            );

            expect($message->agent_id)->toBe('agent-001');
            expect($message->agent_type)->toBe('training_advisor');
            expect($message->agent_name)->toBe('Training Advisor Agent');
            expect($message->tools_used)->toBe($toolsUsed);
            expect($message->tool_results)->toBe($toolResults);
            expect($message->tool_call_count)->toBe(1);
            expect($message->ai_model_used)->toBe('claude-3-5-sonnet');
            expect($message->processing_time)->toBe(1.5);
            expect($message->tokens_used)->toBe(250);
            expect((float) $message->cost_estimate)->toBe(0.015);
        });

        it('updates conversation last activity time', function () {
            $conversation = $this->service->createConversation($this->user, 'race_strategy');
            $initialActivity = $conversation->last_activity_at;

            sleep(1);

            $this->service->addMessage($conversation, 'user', 'What race should I enter?');

            $conversation->refresh();
            expect($conversation->last_activity_at->isAfter($initialActivity))->toBeTrue();
        });
    });

    describe('createBranch', function () {
        it('creates conversation branch at message', function () {
            $conversation = $this->service->createConversation($this->user, 'career_planning');
            $message = $this->service->addMessage($conversation, 'user', 'Should I train Speed or Stamina?');

            $alternatives = [
                ['option' => 'speed_focus', 'description' => 'Focus on Speed training'],
                ['option' => 'stamina_focus', 'description' => 'Focus on Stamina training'],
            ];

            $branch = $this->service->createBranch($message, 'Multiple strategy options', $alternatives);

            expect($branch)->toHaveKeys(['branch_id', 'parent_message_id', 'reason', 'alternatives']);
            expect($branch['reason'])->toBe('Multiple strategy options');
            expect($branch['alternatives'])->toBe($alternatives);

            $message->refresh();
            expect($message->is_branch_point)->toBeTrue();
            expect($message->branch_metadata)->toHaveKey('reason');
        });
    });

    describe('getConversationHistory', function () {
        it('retrieves conversation history with agent attribution', function () {
            $conversation = $this->service->createConversation($this->user, 'training_advice');

            $this->service->addMessage($conversation, 'user', 'How do I train effectively?');
            $this->service->addMessage(
                $conversation,
                'ai',
                'Focus on balanced training',
                'agent-123',
                'training_advisor',
                'Training Expert'
            );

            $history = $this->service->getConversationHistory($conversation);

            expect($history->count())->toBe(2);
            expect($history[0]['type'])->toBe('user');
            expect($history[1]['type'])->toBe('ai');
            expect($history[1]['agent']['id'])->toBe('agent-123');
            expect($history[1]['agent']['type'])->toBe('training_advisor');
            expect($history[1]['agent']['name'])->toBe('Training Expert');
        });

        it('includes tool usage when requested', function () {
            $conversation = $this->service->createConversation($this->user, 'skill_optimization');

            $toolsUsed = [['name' => 'skill_calculator', 'input' => 'test']];
            $toolResults = ['skill_calculator' => ['success' => true]];

            $this->service->addMessage(
                $conversation,
                'ai',
                'Calculated results',
                'agent-456',
                'skill_agent',
                'Skill Agent',
                $toolsUsed,
                $toolResults
            );

            $history = $this->service->getConversationHistory($conversation, true);

            expect($history[0])->toHaveKey('tools');
            expect($history[0]['tools']['used'])->toBe($toolsUsed);
            expect($history[0]['tools']['results'])->toBe($toolResults);
            expect($history[0]['tools']['count'])->toBe(1);
        });
    });

    describe('getConversationAnalytics', function () {
        it('generates comprehensive analytics', function () {
            $conversation = $this->service->createConversation($this->user, 'career_planning');

            $this->service->addMessage($conversation, 'user', 'What should I do?');
            $this->service->addMessage($conversation, 'ai', 'Train Speed', 'agent-1', 'advisor', 'Advisor');
            $this->service->addMessage($conversation, 'user', 'How much?');
            $this->service->addMessage($conversation, 'ai', 'Focus 70%', 'agent-1', 'advisor', 'Advisor');

            $analytics = $this->service->getConversationAnalytics($conversation);

            expect($analytics)->toHaveKeys([
                'conversation_id',
                'total_messages',
                'user_messages',
                'ai_messages',
                'agent_breakdown',
                'tool_usage',
                'quality_metrics',
                'branching_stats',
                'performance',
                'cost_analysis',
            ]);
            expect($analytics['total_messages'])->toBe(4);
            expect($analytics['user_messages'])->toBe(2);
            expect($analytics['ai_messages'])->toBe(2);
        });

        it('calculates tool usage statistics', function () {
            $conversation = $this->service->createConversation($this->user, 'debugging');

            $toolsUsed = [
                ['name' => 'calculator'],
                ['name' => 'database_query'],
            ];
            $toolResults = [
                'calculator' => ['success' => true],
                'database_query' => ['success' => false],
            ];

            $this->service->addMessage(
                $conversation,
                'ai',
                'Results analyzed',
                'agent-789',
                'debug_agent',
                'Debug Agent',
                $toolsUsed,
                $toolResults
            );

            $analytics = $this->service->getConversationAnalytics($conversation);

            expect($analytics['tool_usage']['total_tool_calls'])->toBe(2);
            expect($analytics['tool_usage']['unique_tools'])->toBe(2);
        });
    });

    describe('exportWorkflow', function () {
        it('exports conversation workflow with metadata', function () {
            $conversation = $this->service->createConversation($this->user, 'race_strategy', 'My Strategy');

            $this->service->addMessage($conversation, 'user', 'Which race?');
            $this->service->addMessage($conversation, 'ai', 'Try the G1 race', 'agent-999', 'race_agent', 'Race Planner');

            $workflow = $this->service->exportWorkflow($conversation, 'json');

            expect($workflow)->toHaveKeys([
                'conversation_id',
                'title',
                'type',
                'created_at',
                'messages',
                'analytics',
                'metadata',
            ]);
            expect($workflow['title'])->toBe('My Strategy');
            expect($workflow['type'])->toBe('race_strategy');
            expect($workflow['metadata']['format'])->toBe('json');
            expect($workflow['metadata']['version'])->toBe('1.0');
            expect($workflow['messages'])->toHaveCount(2);
        });
    });

    describe('addAgentFeedback', function () {
        it('adds quality rating and feedback to message', function () {
            // create, add message, add feedback, store learning, log feedback

            $conversation = $this->service->createConversation($this->user, 'training_advice');
            $message = $this->service->addMessage(
                $conversation,
                'ai',
                'Train Speed today',
                'agent-001',
                'advisor',
                'Advisor'
            );

            $this->service->addAgentFeedback($message, 5, 'Very helpful!', ['keep_detailed_explanations']);

            $message->refresh();
            expect($message->quality_rating)->toBe(5);
            expect($message->user_feedback)->toBe('Very helpful!');
            expect($message->is_helpful)->toBeTrue();
        });

        it('marks low-rated messages as not helpful', function () {
            $conversation = $this->service->createConversation($this->user, 'general_help');
            $message = $this->service->addMessage($conversation, 'ai', 'Generic advice', 'agent-002', 'helper', 'Helper');

            $this->service->addAgentFeedback($message, 2, 'Too vague', []);

            $message->refresh();
            expect($message->quality_rating)->toBe(2);
            expect($message->is_helpful)->toBeFalse();
        });
    });
});
