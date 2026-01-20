<?php

use App\Models\AIConversation;
use App\Models\Character;
use App\Models\ConversationMessage;
use App\Models\User;
use App\Services\AI\AgentFeedbackService;
use App\Services\AI\ConversationAnalyticsService;
use App\Services\AI\ConversationManagementService;
use App\Services\AI\WorkflowExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;


beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
    $this->conversationService = app(ConversationManagementService::class);
    $this->analyticsService = app(ConversationAnalyticsService::class);
    $this->exportService = app(WorkflowExportService::class);
    $this->feedbackService = app(AgentFeedbackService::class);
});

describe('ConversationManagementService', function () {
    test('can create a new conversation', function () {
        $conversation = $this->conversationService->createConversation(
            $this->user,
            'career_planning',
            'Test Conversation',
            ['character_id' => $this->character->id],
            ['model' => 'test-model', 'version' => '1.0']
        );

        expect($conversation)->toBeInstanceOf(AIConversation::class)
            ->and($conversation->user_id)->toBe($this->user->id)
            ->and($conversation->conversation_type)->toBe('career_planning')
            ->and($conversation->conversation_title)->toBe('Test Conversation')
            ->and($conversation->status)->toBe('active')
            ->and($conversation->message_count)->toBe(0);
    });

    test('can add a message to conversation', function () {
        $conversation = $this->conversationService->createConversation(
            $this->user,
            'career_planning'
        );

        $message = $this->conversationService->addMessage(
            $conversation,
            'user',
            'Hello, I need help with training',
            null,
            null,
            null,
            [],
            [],
            []
        );

        expect($message)->toBeInstanceOf(ConversationMessage::class)
            ->and($message->conversation_id)->toBe($conversation->id)
            ->and($message->message_type)->toBe('user')
            ->and($message->message_content)->toBe('Hello, I need help with training')
            ->and($message->status)->toBe('completed');

        $conversation->refresh();
        expect($conversation->message_count)->toBe(1);
    });

    test('can add message with agent attribution', function () {
        $conversation = $this->conversationService->createConversation(
            $this->user,
            'training_advice'
        );

        $message = $this->conversationService->addMessage(
            $conversation,
            'ai',
            'I recommend focusing on Speed training',
            'training_agent_001',
            'TrainingOptimizationAgent',
            'Training Optimizer',
            ['analyze_stats', 'predict_gains'],
            ['analyze_stats' => ['success' => true], 'predict_gains' => ['success' => true]],
            ['ai_model' => 'claude-3.5-sonnet', 'processing_time' => 2.5, 'tokens_used' => 150]
        );

        expect($message->agent_id)->toBe('training_agent_001')
            ->and($message->agent_type)->toBe('TrainingOptimizationAgent')
            ->and($message->agent_name)->toBe('Training Optimizer')
            ->and($message->tools_used)->toHaveCount(2)
            ->and($message->tool_call_count)->toBe(2)
            ->and($message->ai_model_used)->toBe('claude-3.5-sonnet')
            ->and($message->processing_time)->toBe(2.5)
            ->and($message->tokens_used)->toBe(150);
    });

    test('can create conversation branch', function () {
        $conversation = $this->conversationService->createConversation(
            $this->user,
            'career_planning'
        );

        $parentMessage = $this->conversationService->addMessage(
            $conversation,
            'ai',
            'Here are two strategies you could follow',
            'career_agent_001',
            'CareerStrategyAgent',
            'Career Planner'
        );

        $branch = $this->conversationService->createBranch(
            $parentMessage,
            'Exploring alternative strategy',
            ['Strategy A: Speed focus', 'Strategy B: Stamina focus']
        );

        expect($branch)->toHaveKeys(['branch_id', 'parent_message_id', 'reason', 'alternatives'])
            ->and($branch['parent_message_id'])->toBe($parentMessage->id)
            ->and($branch['reason'])->toBe('Exploring alternative strategy')
            ->and($branch['alternatives'])->toHaveCount(2);

        $parentMessage->refresh();
        expect($parentMessage->is_branch_point)->toBeTrue()
            ->and($parentMessage->branch_metadata)->toHaveKey('reason');
    });

    test('can add message to branch', function () {
        $conversation = $this->conversationService->createConversation(
            $this->user,
            'career_planning'
        );

        $parentMessage = $this->conversationService->addMessage(
            $conversation,
            'ai',
            'Parent message'
        );

        $branch = $this->conversationService->createBranch(
            $parentMessage,
            'Testing branch'
        );

        $branchMessage = $this->conversationService->addBranchMessage(
            $conversation,
            $branch['branch_id'],
            $parentMessage->id,
            'user',
            'Let me try Strategy A'
        );

        expect($branchMessage->branch_id)->toBe($branch['branch_id'])
            ->and($branchMessage->parent_message_id)->toBe($parentMessage->id)
            ->and($branchMessage->branch_depth)->toBe(1);
    });

    test('can get conversation history', function () {
        $conversation = $this->conversationService->createConversation(
            $this->user,
            'training_advice'
        );

        // Add multiple messages
        $this->conversationService->addMessage($conversation, 'user', 'Message 1');
        $this->conversationService->addMessage($conversation, 'ai', 'Response 1', 'agent1', 'TestAgent', 'Test Agent');
        $this->conversationService->addMessage($conversation, 'user', 'Message 2');

        $history = $this->conversationService->getConversationHistory($conversation);

        expect($history)->toHaveCount(3)
            ->and($history->first())->toHaveKeys(['id', 'type', 'content', 'sent_at', 'agent', 'quality', 'branching']);
    });

    test('can get conversation analytics', function () {
        $conversation = $this->conversationService->createConversation(
            $this->user,
            'training_advice'
        );

        // Add messages with ratings
        $message1 = $this->conversationService->addMessage($conversation, 'user', 'Question');
        $message2 = $this->conversationService->addMessage($conversation, 'ai', 'Answer', 'agent1', 'TestAgent', 'Test Agent');
        $message2->update(['quality_rating' => 5, 'is_helpful' => true]);

        $analytics = $this->conversationService->getConversationAnalytics($conversation);

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
        ])
            ->and($analytics['total_messages'])->toBe(2)
            ->and($analytics['user_messages'])->toBe(1)
            ->and($analytics['ai_messages'])->toBe(1);
    });

    test('can export workflow', function () {
        $conversation = $this->conversationService->createConversation(
            $this->user,
            'career_planning',
            'Test Workflow'
        );

        $this->conversationService->addMessage($conversation, 'user', 'Question');
        $this->conversationService->addMessage($conversation, 'ai', 'Answer');

        $workflow = $this->conversationService->exportWorkflow($conversation);

        expect($workflow)->toHaveKeys([
            'conversation_id',
            'title',
            'type',
            'created_at',
            'messages',
            'analytics',
            'metadata',
        ])
            ->and($workflow['title'])->toBe('Test Workflow')
            ->and($workflow['messages'])->toHaveCount(2);
    });

    test('can add agent feedback', function () {
        $conversation = $this->conversationService->createConversation(
            $this->user,
            'training_advice'
        );

        $message = $this->conversationService->addMessage(
            $conversation,
            'ai',
            'Training recommendation',
            'agent1',
            'TestAgent',
            'Test Agent'
        );

        $this->conversationService->addAgentFeedback(
            $message,
            4,
            'Very helpful!',
            ['accuracy' => 'high', 'clarity' => 'good']
        );

        $message->refresh();
        expect($message->quality_rating)->toBe(4)
            ->and($message->user_feedback)->toBe('Very helpful!')
            ->and($message->is_helpful)->toBeTrue();
    });
});

describe('ConversationAnalyticsService', function () {
    test('can get agent effectiveness metrics', function () {
        $conversation = AIConversation::factory()->create(['user_id' => $this->user->id]);

        // Create messages with ratings
        ConversationMessage::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'message_type' => 'ai',
            'agent_id' => 'test_agent',
            'quality_rating' => 4,
            'is_helpful' => true,
        ]);

        $effectiveness = $this->analyticsService->getAgentEffectiveness('test_agent');

        expect($effectiveness)->toHaveKeys([
            'agent_id',
            'timeframe',
            'total_messages',
            'response_quality',
            'user_satisfaction',
            'tool_effectiveness',
            'performance_metrics',
            'improvement_trends',
        ])
            ->and($effectiveness['total_messages'])->toBe(5);
    });

    test('can get user satisfaction metrics', function () {
        $conversation = AIConversation::factory()->create([
            'user_id' => $this->user->id,
            'conversation_type' => 'training_advice',
        ]);

        ConversationMessage::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'quality_rating' => 5,
            'is_helpful' => true,
        ]);

        $satisfaction = $this->analyticsService->getUserSatisfaction($this->user->id, 'training_advice');

        expect($satisfaction)->toHaveKeys([
            'user_id',
            'conversation_type',
            'total_conversations',
            'overall_satisfaction',
            'satisfaction_by_type',
            'satisfaction_trends',
            'feedback_summary',
        ]);
    });

    test('can get conversation completion metrics', function () {
        AIConversation::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        AIConversation::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $metrics = $this->analyticsService->getConversationCompletionMetrics();

        expect($metrics)->toHaveKeys([
            'total_conversations',
            'by_status',
            'completion_rate',
            'avg_duration',
            'avg_messages_per_conversation',
            'abandonment_analysis',
        ])
            ->and($metrics['total_conversations'])->toBe(5);
    });

    test('can get tool usage analytics', function () {
        $conversation = AIConversation::factory()->create(['user_id' => $this->user->id]);

        ConversationMessage::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'tools_used' => ['tool1', 'tool2'],
            'tool_call_count' => 2,
        ]);

        $analytics = $this->analyticsService->getToolUsageAnalytics();

        expect($analytics)->toHaveKeys([
            'timeframe',
            'total_tool_calls',
            'messages_with_tools',
            'tool_breakdown',
            'tool_success_rates',
            'tool_performance',
            'tool_combinations',
        ])
            ->and($analytics['total_tool_calls'])->toBe(6);
    });

    test('can get branching analytics', function () {
        $conversation = AIConversation::factory()->create(['user_id' => $this->user->id]);

        // Create branch points
        ConversationMessage::factory()->count(2)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'is_branch_point' => true,
        ]);

        // Create branched messages
        ConversationMessage::factory()->count(4)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'branch_id' => 'branch_1',
        ]);

        $analytics = $this->analyticsService->getBranchingAnalytics();

        expect($analytics)->toHaveKeys([
            'total_branch_points',
            'total_branches',
            'branch_utilization',
            'branch_depth_analysis',
            'branch_effectiveness',
            'popular_branch_reasons',
        ])
            ->and($analytics['total_branch_points'])->toBe(2);
    });

    test('can get cost analytics', function () {
        $conversation = AIConversation::factory()->create(['user_id' => $this->user->id]);

        ConversationMessage::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'cost_estimate' => 0.001,
        ]);

        $analytics = $this->analyticsService->getCostAnalytics();

        expect($analytics)->toHaveKeys([
            'timeframe',
            'total_cost',
            'total_messages',
            'avg_cost_per_message',
            'cost_by_agent',
            'cost_by_model',
            'cost_trends',
            'cost_optimization_opportunities',
        ])
            ->and($analytics['total_cost'])->toBe(0.005);
    });

    test('can get dashboard metrics', function () {
        AIConversation::factory()->count(3)->create(['user_id' => $this->user->id]);

        $metrics = $this->analyticsService->getDashboardMetrics();

        expect($metrics)->toHaveKeys([
            'overview',
            'agent_performance',
            'user_satisfaction',
            'tool_usage',
            'cost_summary',
            'recent_activity',
        ])
            ->and($metrics['overview']['total_conversations'])->toBe(3);
    });
});

describe('WorkflowExportService', function () {
    test('can export workflow as JSON', function () {
        $conversation = AIConversation::factory()->create(['user_id' => $this->user->id]);
        ConversationMessage::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
        ]);

        $json = $this->exportService->exportAsJson($conversation);

        expect($json)->toBeArray()
            ->and($json)->toHaveKeys(['conversation_id', 'title', 'type', 'messages', 'analytics']);
    });

    test('can export workflow as Markdown', function () {
        $conversation = AIConversation::factory()->create([
            'user_id' => $this->user->id,
            'conversation_title' => 'Test Conversation',
        ]);

        ConversationMessage::factory()->count(2)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
        ]);

        $markdown = $this->exportService->exportAsMarkdown($conversation);

        expect($markdown)->toBeString()
            ->and($markdown)->toContain('# Test Conversation')
            ->and($markdown)->toContain('## Messages');
    });

    test('can save workflow to file', function () {
        $conversation = AIConversation::factory()->create(['user_id' => $this->user->id]);
        ConversationMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
        ]);

        $filename = $this->exportService->saveToFile($conversation, 'json');

        expect($filename)->toBeString()
            ->and($filename)->toContain('.json')
            ->and(Storage::disk('local')->exists("exports/{$filename}"))->toBeTrue();

        // Cleanup
        Storage::disk('local')->delete("exports/{$filename}");
    });
});

describe('AgentFeedbackService', function () {
    test('can record feedback for agent message', function () {
        $conversation = AIConversation::factory()->create(['user_id' => $this->user->id]);
        $message = ConversationMessage::factory()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'agent_id' => 'test_agent',
        ]);

        $this->feedbackService->recordFeedback(
            $message,
            5,
            'Excellent response!',
            ['accuracy' => 'high']
        );

        $message->refresh();
        expect($message->quality_rating)->toBe(5)
            ->and($message->user_feedback)->toBe('Excellent response!')
            ->and($message->is_helpful)->toBeTrue();
    });

    test('can get agent performance summary', function () {
        $conversation = AIConversation::factory()->create(['user_id' => $this->user->id]);

        ConversationMessage::factory()->count(5)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'agent_id' => 'test_agent',
            'quality_rating' => 4,
            'is_helpful' => true,
        ]);

        $summary = $this->feedbackService->getAgentPerformanceSummary('test_agent');

        expect($summary)->toHaveKeys([
            'agent_id',
            'total_messages',
            'avg_rating',
            'helpful_rate',
            'rating_distribution',
            'recent_trend',
            'improvement_areas',
            'strengths',
        ])
            ->and($summary['total_messages'])->toBe(5)
            ->and($summary['avg_rating'])->toBe(4.0);
    });

    test('can get improvement recommendations', function () {
        $conversation = AIConversation::factory()->create(['user_id' => $this->user->id]);

        // Create low-rated messages with feedback
        ConversationMessage::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'agent_id' => 'test_agent',
            'quality_rating' => 2,
            'user_feedback' => 'Response was too slow',
        ]);

        $recommendations = $this->feedbackService->getImprovementRecommendations('test_agent');

        expect($recommendations)->toBeArray();
    });

    test('can get feedback trends', function () {
        $conversation = AIConversation::factory()->create(['user_id' => $this->user->id]);

        ConversationMessage::factory()->count(10)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'agent_id' => 'test_agent',
            'quality_rating' => 4,
        ]);

        $trends = $this->feedbackService->getFeedbackTrends('test_agent', '30d');

        expect($trends)->toHaveKeys([
            'agent_id',
            'timeframe',
            'trends',
            'overall_improvement',
        ]);
    });

    test('can compare multiple agents', function () {
        $conversation = AIConversation::factory()->create(['user_id' => $this->user->id]);

        // Create messages for different agents
        ConversationMessage::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'agent_id' => 'agent1',
            'quality_rating' => 5,
        ]);

        ConversationMessage::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'agent_id' => 'agent2',
            'quality_rating' => 3,
        ]);

        $comparison = $this->feedbackService->compareAgents(['agent1', 'agent2']);

        expect($comparison)->toHaveKeys([
            'agents',
            'rankings',
            'best_performer',
            'needs_improvement',
        ])
            ->and($comparison['agents'])->toHaveCount(2);
    });
});
