<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\User;
use App\Services\MCP\AgentContextService;
use App\Services\MCP\AgentMemoryService;
use App\Services\MCP\AgentOrchestrationService;
use App\Services\MCP\CareerStateSyncService;
use App\Services\MCP\WorkflowTemplateService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Character',
        'scenario_type' => 'ura_finale',
    ]);
    $this->career = Career::factory()->create([
        'character_id' => $this->character->id,
        'scenario_type' => 'ura_finale',
    ]);
});

describe('AgentContextService', function () {
    it('builds comprehensive character context', function () {
        $contextService = app(AgentContextService::class);

        $context = $contextService->buildCharacterContext($this->character);

        expect($context)->toBeArray()
            ->and($context)->toHaveKeys([
                'id',
                'name',
                'scenario_type',
                'stats',
                'state',
                'aptitudes',
                'factors',
                'skills',
                'support_cards',
                'goals',
                'career_history',
                'context_generated_at',
            ])
            ->and($context['id'])->toBe($this->character->id)
            ->and($context['name'])->toBe('Test Character')
            ->and($context['stats'])->toHaveKeys(['speed', 'stamina', 'power', 'guts', 'wit']);
    });

    it('builds career context with training history', function () {
        $contextService = app(AgentContextService::class);

        $context = $contextService->buildCareerContext($this->career);

        expect($context)->toBeArray()
            ->and($context)->toHaveKeys([
                'id',
                'character_id',
                'scenario_type',
                'is_active',
                'recent_training',
                'recent_races',
                'recent_events',
                'metrics',
            ])
            ->and($context['character_id'])->toBe($this->character->id)
            ->and($context['is_active'])->toBeTrue();
    });

    it('builds unified context for multi-agent workflows', function () {
        $contextService = app(AgentContextService::class);

        $context = $contextService->buildUnifiedContext(
            $this->character,
            $this->career,
            $this->user
        );

        expect($context)->toBeArray()
            ->and($context)->toHaveKeys(['character', 'career', 'user', 'unified_context'])
            ->and($context['character']['id'])->toBe($this->character->id)
            ->and($context['career']['id'])->toBe($this->career->id)
            ->and($context['user']['id'])->toBe($this->user->id);
    });

    it('caches context for performance', function () {
        $contextService = app(AgentContextService::class);

        // First call - should build context
        $context1 = $contextService->buildCharacterContext($this->character);

        // Second call - should retrieve from cache
        $context2 = $contextService->buildCharacterContext($this->character);

        expect($context1)->toEqual($context2);

        // Verify cache was used
        $cacheKey = "agent_context:character:{$this->character->id}";
        expect(Cache::has($cacheKey))->toBeTrue();
    });

    it('invalidates context cache when requested', function () {
        $contextService = app(AgentContextService::class);

        // Build and cache context
        $contextService->buildCharacterContext($this->character);

        $cacheKey = "agent_context:character:{$this->character->id}";
        expect(Cache::has($cacheKey))->toBeTrue();

        // Invalidate cache
        $contextService->invalidateContext(AgentContextService::CONTEXT_CHARACTER, $this->character->id);

        expect(Cache::has($cacheKey))->toBeFalse();
    });
});

describe('CareerStateSyncService', function () {
    it('synchronizes career state across agents', function () {
        $syncService = app(CareerStateSyncService::class);

        $result = $syncService->synchronizeCareerState($this->career);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['sync_id', 'status', 'career_id', 'state', 'synced_at'])
            ->and($result['status'])->toBe(CareerStateSyncService::SYNC_COMPLETED)
            ->and($result['career_id'])->toBe($this->career->id);
    });

    it('synchronizes character state across agents', function () {
        $syncService = app(CareerStateSyncService::class);

        $result = $syncService->synchronizeCharacterState($this->character);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['sync_id', 'status', 'character_id', 'state', 'synced_at'])
            ->and($result['status'])->toBe(CareerStateSyncService::SYNC_COMPLETED)
            ->and($result['character_id'])->toBe($this->character->id);
    });

    it('allows agents to subscribe to state updates', function () {
        $syncService = app(CareerStateSyncService::class);

        $agentId = 'test_agent_123';
        $stateTypes = ['career', 'character'];

        $result = $syncService->subscribeAgent($agentId, $stateTypes);

        expect($result)->toBeTrue();

        // Verify subscription was stored
        $subscriptions = Cache::get('agent_subscriptions', []);
        expect($subscriptions)->toHaveKey($agentId)
            ->and($subscriptions[$agentId]['state_types'])->toBe($stateTypes);
    });

    it('notifies subscribed agents of state changes', function () {
        $syncService = app(CareerStateSyncService::class);

        // Subscribe agents
        $syncService->subscribeAgent('agent_1', ['career']);
        $syncService->subscribeAgent('agent_2', ['character']);
        $syncService->subscribeAgent('agent_3', ['career', 'character']);

        // Notify state change
        $result = $syncService->notifyStateChange('career', ['test' => 'data']);

        expect($result)->toBeArray()
            ->and($result['state_type'])->toBe('career')
            ->and($result['agents_notified'])->toContain('agent_1', 'agent_3')
            ->and($result['notification_count'])->toBe(2);
    });
});

describe('AgentMemoryService', function () {
    it('stores and retrieves short-term memory', function () {
        $memoryService = app(AgentMemoryService::class);

        $agentId = 'test_agent_123';
        $key = 'test_memory';
        $value = ['data' => 'test value'];

        $stored = $memoryService->storeMemory(
            $agentId,
            AgentMemoryService::MEMORY_SHORT_TERM,
            $key,
            $value
        );

        expect($stored)->toBeTrue();

        $retrieved = $memoryService->retrieveMemory(
            $agentId,
            AgentMemoryService::MEMORY_SHORT_TERM,
            $key
        );

        expect($retrieved)->toEqual($value);
    });

    it('stores and retrieves episodic memories', function () {
        $memoryService = app(AgentMemoryService::class);

        $agentId = 'test_agent_123';
        $episodeId = 'episode_001';
        $episodeData = [
            'action' => 'training_decision',
            'result' => 'success',
        ];

        $stored = $memoryService->storeEpisode($agentId, $episodeId, $episodeData);

        expect($stored)->toBeTrue();

        $episodes = $memoryService->getEpisodes($agentId);

        expect($episodes)->toBeArray()
            ->and($episodes)->not->toBeEmpty()
            ->and($episodes[0]['value']['episode_id'])->toBe($episodeId);
    });

    it('stores and retrieves semantic knowledge', function () {
        $memoryService = app(AgentMemoryService::class);

        $agentId = 'test_agent_123';
        $topic = 'training_optimization';
        $knowledge = [
            'best_practices' => ['focus on speed', 'manage energy'],
            'learned_patterns' => ['pattern1', 'pattern2'],
        ];

        $stored = $memoryService->storeKnowledge($agentId, $topic, $knowledge);

        expect($stored)->toBeTrue();

        $retrieved = $memoryService->getKnowledge($agentId, $topic);

        expect($retrieved)->toEqual($knowledge);
    });

    it('stores and retrieves conversation context', function () {
        $memoryService = app(AgentMemoryService::class);

        $agentId = 'test_agent_123';
        $conversationId = 'conv_001';
        $context = [
            'user_intent' => 'optimize training',
            'previous_messages' => ['message1', 'message2'],
        ];

        $stored = $memoryService->storeConversationContext($agentId, $conversationId, $context);

        expect($stored)->toBeTrue();

        $retrieved = $memoryService->getConversationContext($agentId, $conversationId);

        expect($retrieved)->toEqual($context);
    });

    it('clears specific memory', function () {
        $memoryService = app(AgentMemoryService::class);

        $agentId = 'test_agent_123';
        $key = 'test_memory';
        $value = ['data' => 'test value'];

        $memoryService->storeMemory(
            $agentId,
            AgentMemoryService::MEMORY_SHORT_TERM,
            $key,
            $value
        );

        $cleared = $memoryService->clearMemory(
            $agentId,
            AgentMemoryService::MEMORY_SHORT_TERM,
            $key
        );

        expect($cleared)->toBeTrue();

        $retrieved = $memoryService->retrieveMemory(
            $agentId,
            AgentMemoryService::MEMORY_SHORT_TERM,
            $key
        );

        expect($retrieved)->toBeNull();
    });

    it('provides memory statistics', function () {
        $memoryService = app(AgentMemoryService::class);

        $agentId = 'test_agent_123';

        // Store various memories
        $memoryService->storeMemory($agentId, AgentMemoryService::MEMORY_SHORT_TERM, 'key1', 'value1');
        $memoryService->storeMemory($agentId, AgentMemoryService::MEMORY_SHORT_TERM, 'key2', 'value2');
        $memoryService->storeMemory($agentId, AgentMemoryService::MEMORY_LONG_TERM, 'key3', 'value3');

        $stats = $memoryService->getMemoryStatistics($agentId);

        expect($stats)->toBeArray()
            ->and($stats['agent_id'])->toBe($agentId)
            ->and($stats['total_memories'])->toBe(3)
            ->and($stats['by_type'])->toHaveKey(AgentMemoryService::MEMORY_SHORT_TERM)
            ->and($stats['by_type'][AgentMemoryService::MEMORY_SHORT_TERM])->toBe(2);
    });
});

describe('WorkflowTemplateService', function () {
    it('provides available workflow templates', function () {
        $templateService = app(WorkflowTemplateService::class);

        $templates = $templateService->getAvailableTemplates();

        expect($templates)->toBeArray()
            ->and($templates)->toHaveKeys([
                WorkflowTemplateService::TEMPLATE_TRAINING_OPTIMIZATION,
                WorkflowTemplateService::TEMPLATE_RACE_PREPARATION,
                WorkflowTemplateService::TEMPLATE_SKILL_PLANNING,
                WorkflowTemplateService::TEMPLATE_CAREER_STRATEGY,
            ])
            ->and($templates[WorkflowTemplateService::TEMPLATE_TRAINING_OPTIMIZATION])->toHaveKeys([
                'name',
                'description',
                'agents',
                'pattern',
                'estimated_time',
            ]);
    });

    it('creates custom workflow template', function () {
        $templateService = app(WorkflowTemplateService::class);

        $template = $templateService->createCustomTemplate(
            'Custom Training Template',
            'Custom description',
            ['Agent1', 'Agent2'],
            AgentOrchestrationService::PATTERN_SEQUENTIAL
        );

        expect($template)->toBeArray()
            ->and($template)->toHaveKeys(['id', 'name', 'description', 'agents', 'pattern', 'is_custom'])
            ->and($template['name'])->toBe('Custom Training Template')
            ->and($template['is_custom'])->toBeTrue();
    });
});

describe('Context-Aware Agent Orchestration Integration', function () {
    it('creates context-aware workflow with character state', function () {
        $orchestration = app(AgentOrchestrationService::class);

        $workflow = $orchestration->createContextAwareWorkflow(
            'Test Workflow',
            AgentOrchestrationService::PATTERN_SEQUENTIAL,
            [
                ['id' => 'agent1', 'type' => 'TestAgent'],
            ],
            $this->character,
            $this->career
        );

        expect($workflow)->toBeArray()
            ->and($workflow)->toHaveKeys(['id', 'name', 'pattern', 'agents', 'config'])
            ->and($workflow['config']['context_aware'])->toBeTrue()
            ->and($workflow['config']['character_id'])->toBe($this->character->id)
            ->and($workflow['config']['career_id'])->toBe($this->career->id);

        // Verify context was cached
        $workflowId = $workflow['id'];
        assert(is_string($workflowId) || is_int($workflowId));
        $contextKey = "workflow_context:{$workflowId}";
        expect(Cache::has($contextKey))->toBeTrue();
    });
});
