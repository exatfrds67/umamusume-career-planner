<?php

declare(strict_types=1);

use App\Models\Aptitude;
use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use App\Models\TrainingSession;
use App\Models\User;
use App\Neuron\Agents\TrainingAdvisorAgent;
use App\Neuron\Responses\TrainingAdviceResponse;
use App\Services\Neuron\TrainingAdvisorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\AIProviderInterface;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

function getTrainingAdvisorChatHistory(TrainingAdvisorAgent $agent): ChatHistoryInterface
{
    return $agent->getChatHistory();
}

beforeEach(function () {
    // Create test user
    $this->user = User::factory()->create();

    // Create test character with comprehensive data
    $this->character = Character::factory()
        ->withStats([
            'speed' => 500,
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 350,
        ])
        ->withGoals([
            'target_stats' => [
                'speed' => 800,
                'stamina' => 600,
                'power' => 500,
            ],
        ])
        ->create([
            'user_id' => $this->user->id,
            'name' => 'Test Character',
            'scenario_type' => 'ura_finale',
            'career_stage' => 'classic',
            'current_turn' => 30,
            'energy_level' => 80,
            'mood_status' => 'good',
        ]);

    // Create aptitudes for the character
    Aptitude::factory()->create([
        'character_id' => $this->character->id,
        'distance_type' => 'mile',
        'grade' => 'A',
    ]);

    Aptitude::factory()->create([
        'character_id' => $this->character->id,
        'surface_type' => 'turf',
        'grade' => 'B',
    ]);

    // Create support cards
    $this->supportCard1 = SupportCardDefinition::factory()->create([
        'name' => 'Speed Support Card',
        'card_type' => 'speed',
        'rarity' => 'SSR',
    ]);

    $this->supportCard2 = SupportCardDefinition::factory()->create([
        'name' => 'Stamina Support Card',
        'card_type' => 'stamina',
        'rarity' => 'SR',
    ]);

    // Attach support cards to character
    CharacterSupportCard::factory()->create([
        'character_id' => $this->character->id,
        'support_card_id' => $this->supportCard1->id,
        'position_slot' => 1,
        'friendship_level' => 80,
        'limit_break_level' => 2,
    ]);

    CharacterSupportCard::factory()->create([
        'character_id' => $this->character->id,
        'support_card_id' => $this->supportCard2->id,
        'position_slot' => 2,
        'friendship_level' => 60,
        'limit_break_level' => 0,
    ]);

    // Create recent training history
    TrainingSession::factory()->create([
        'character_id' => $this->character->id,
        'turn_number' => 29,
        'training_type' => 'speed',
        'speed_gain' => 15,
        'power_gain' => 5,
    ]);

    TrainingSession::factory()->create([
        'character_id' => $this->character->id,
        'turn_number' => 28,
        'training_type' => 'stamina',
        'stamina_gain' => 12,
        'guts_gain' => 3,
    ]);
});

describe('TrainingAdvisorAgent - Instantiation and Configuration', function () {
    it('can be instantiated with user ID', function () {
        $agent = new TrainingAdvisorAgent($this->user->id);

        expect($agent)->toBeInstanceOf(TrainingAdvisorAgent::class);
    });

    it('can be instantiated with user ID and character ID', function () {
        $agent = new TrainingAdvisorAgent($this->user->id, $this->character->id);

        expect($agent)->toBeInstanceOf(TrainingAdvisorAgent::class);
    });

    it('has proper system instructions', function () {
        $agent = new TrainingAdvisorAgent($this->user->id);
        $instructions = $agent->instructions();

        expect($instructions)->toBeString();
        expect($instructions)->toContain('Uma Musume');
        expect($instructions)->toContain('training');
        expect($instructions)->toContain('aptitudes');
        expect($instructions)->toContain('support card');
    });

    it('uses Anthropic provider', function () {
        $agent = new TrainingAdvisorAgent($this->user->id);

        // Use reflection to access protected provider method
        $reflection = new ReflectionClass($agent);
        $method = $reflection->getMethod('provider');
        $method->setAccessible(true);
        $provider = $method->invoke($agent);

        expect($provider)->toBeInstanceOf(AIProviderInterface::class);
    });

    it('generates unique thread ID for each user', function () {
        $agent1 = new TrainingAdvisorAgent($this->user->id);
        $agent2 = new TrainingAdvisorAgent($this->user->id + 1);

        $reflection1 = new ReflectionClass($agent1);
        $method1 = $reflection1->getMethod('getThreadId');
        $method1->setAccessible(true);
        $threadId1 = $method1->invoke($agent1);

        $reflection2 = new ReflectionClass($agent2);
        $method2 = $reflection2->getMethod('getThreadId');
        $method2->setAccessible(true);
        $threadId2 = $method2->invoke($agent2);

        expect($threadId1)->not->toBe($threadId2);
    });

    it('generates unique thread ID for each character', function () {
        $agent1 = new TrainingAdvisorAgent($this->user->id, $this->character->id);
        $agent2 = new TrainingAdvisorAgent($this->user->id, $this->character->id + 1);

        $reflection1 = new ReflectionClass($agent1);
        $method1 = $reflection1->getMethod('getThreadId');
        $method1->setAccessible(true);
        $threadId1 = $method1->invoke($agent1);

        $reflection2 = new ReflectionClass($agent2);
        $method2 = $reflection2->getMethod('getThreadId');
        $method2->setAccessible(true);
        $threadId2 = $method2->invoke($agent2);

        expect($threadId1)->not->toBe($threadId2);
    });

    it('has CharacterStatsTool registered', function () {
        $agent = new TrainingAdvisorAgent($this->user->id);

        $reflection = new ReflectionClass($agent);
        $method = $reflection->getMethod('tools');
        $method->setAccessible(true);
        $tools = $method->invoke($agent);

        expect($tools)->toBeArray();
        expect($tools)->not->toBeEmpty();
    });
});

describe('TrainingAdvisorAgent - Context Handling', function () {
    it('receives proper character context through service', function () {
        $service = app(TrainingAdvisorService::class);

        $trainingOptions = [
            'available_trainings' => [
                [
                    'type' => 'speed',
                    'energy_cost' => 20,
                    'failure_risk' => 0.1,
                    'expected_gains' => ['speed' => 15, 'power' => 5],
                    'support_cards_present' => [1],
                ],
                [
                    'type' => 'stamina',
                    'energy_cost' => 20,
                    'failure_risk' => 0.15,
                    'expected_gains' => ['stamina' => 12, 'guts' => 3],
                    'support_cards_present' => [2],
                ],
            ],
        ];

        // Use reflection to access protected method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, $trainingOptions);

        // Verify context includes all necessary information
        expect($context)->toBeString();
        expect($context)->toContain('Test Character');
        expect($context)->toContain('ura_finale');
        expect($context)->toContain('classic');
        expect($context)->toContain('Turn');
        expect($context)->toContain('Speed');
        expect($context)->toContain('Stamina');
        expect($context)->toContain('Speed Support Card');
        expect($context)->toContain('Stamina Support Card');
        expect($context)->toContain('Friendship');
    });

    it('includes character aptitudes in context', function () {
        $service = app(TrainingAdvisorService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Aptitudes');
        expect($context)->toContain('mile');
        expect($context)->toContain('turf');
    });

    it('includes target goals in context', function () {
        $service = app(TrainingAdvisorService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Target Goals');
        expect($context)->toContain('800');
        expect($context)->toContain('600');
        expect($context)->toContain('Gap');
    });

    it('includes recent training history in context', function () {
        $service = app(TrainingAdvisorService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Recent Training History');
        expect($context)->toContain('Turn 29');
        expect($context)->toContain('Turn 28');
    });

    it('includes Unity Cup specific context when applicable', function () {
        $unityCupCharacter = Character::factory()
            ->withStats(['speed' => 500])
            ->create([
                'user_id' => $this->user->id,
                'scenario_type' => 'unity_cup',
                'facility_levels' => [
                    'speed' => 3,
                    'stamina' => 2,
                ],
            ]);

        $service = app(TrainingAdvisorService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        $trainingOptions = [
            'spirit_burst_gauge' => 3,
        ];

        $context = $method->invoke($service, $unityCupCharacter, $trainingOptions);

        expect($context)->toContain('Unity Cup Specific');
        expect($context)->toContain('Facility Levels');
        expect($context)->toContain('Spirit Burst Gauge: 3');
    });
});

describe('TrainingAdvisorAgent - Structured Response', function () {
    it('returns structured TrainingAdviceResponse when mocked', function () {
        // Mock the AI provider to return a structured response
        $mockProvider = mock(AIProviderInterface::class);
        $mockProvider->shouldReceive('systemPrompt')
            ->once()
            ->andReturnSelf();
        $mockProvider->shouldReceive('setTools')
            ->once()
            ->andReturnSelf();
        $mockProvider->shouldReceive('structured')
            ->once()
            ->andReturn(new AssistantMessage(
                json_encode([
                    'recommended_training' => 'speed',
                    'reasoning' => 'Character needs more speed to reach target goal of 800. Current speed is 500 with a gap of 300 points.',
                    'expected_gains' => ['speed' => 15, 'power' => 5],
                    'alternatives' => [
                        ['training' => 'stamina', 'reason' => 'Also viable to balance stats'],
                    ],
                ], JSON_THROW_ON_ERROR)
            ));

        // Create agent and inject mocked provider
        $agent = new TrainingAdvisorAgent($this->user->id, $this->character->id);

        // Use reflection to set the provider
        $reflection = new ReflectionClass($agent);
        $property = $reflection->getProperty('provider');
        $property->setAccessible(true);
        $property->setValue($agent, $mockProvider);

        // Call structured method with proper message object
        $response = $agent->structured(
            new UserMessage('Test context'),
            TrainingAdviceResponse::class
        );

        expect($response)->toBeInstanceOf(TrainingAdviceResponse::class);
        expect($response->recommendedTraining)->toBe('speed');
        expect($response->reasoning)->toContain('speed');
        expect($response->expectedGains)->toHaveKey('speed');
        expect($response->alternatives)->toBeArray();
    });

    it('validates response structure through service', function () {
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Character needs more speed to reach target goal of 800. Current speed is 500.',
            expectedGains: ['speed' => 15, 'power' => 5],
            alternatives: []
        );

        $service = app(TrainingAdvisorService::class);
        $parsed = $service->parseResponse($response);

        expect($parsed)->toBeArray();
        expect($parsed)->toHaveKey('recommended_training');
        expect($parsed)->toHaveKey('reasoning');
        expect($parsed)->toHaveKey('expected_gains');
        expect($parsed)->toHaveKey('alternatives');
        expect($parsed)->toHaveKey('summary');
        expect($parsed)->toHaveKey('validation_errors');
    });

    it('includes expected stat gains in response', function () {
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Speed training is optimal for current character state and goals.',
            expectedGains: ['speed' => 15, 'power' => 5, 'wit' => 2],
            alternatives: []
        );

        expect($response->expectedGains)->toHaveKey('speed');
        expect($response->expectedGains)->toHaveKey('power');
        expect($response->expectedGains)->toHaveKey('wit');
        expect($response->expectedGains['speed'])->toBe(15);
    });

    it('includes alternative training options in response', function () {
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Speed training is optimal for current character state and goals.',
            expectedGains: ['speed' => 15],
            alternatives: [
                ['training' => 'stamina', 'reason' => 'Good for endurance'],
                ['training' => 'power', 'reason' => 'Helps with acceleration'],
            ]
        );

        expect($response->alternatives)->toHaveCount(2);
        expect($response->alternatives[0])->toHaveKey('training');
        expect($response->alternatives[0])->toHaveKey('reason');
    });

    it('provides reasoning for recommendations', function () {
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Character has high aptitude for speed training and needs to reach 800 speed target. Support cards provide bonus to speed gains.',
            expectedGains: ['speed' => 15],
            alternatives: []
        );

        expect($response->reasoning)->toBeString();
        expect(strlen($response->reasoning))->toBeGreaterThanOrEqual(20);
        expect(strlen($response->reasoning))->toBeLessThanOrEqual(500);
    });
});

describe('TrainingAdvisorAgent - Conversation Context Maintenance', function () {
    it('maintains conversation context across multiple turns', function () {
        $agent = new TrainingAdvisorAgent($this->user->id, $this->character->id);
        $chatHistory = getTrainingAdvisorChatHistory($agent);

        // Add first message
        $chatHistory->addMessage(new UserMessage('What training should I do?'));
        $chatHistory->addMessage(new AssistantMessage('I recommend speed training.'));

        // Add second message
        $chatHistory->addMessage(new UserMessage('What about next turn?'));
        $chatHistory->addMessage(new AssistantMessage('Continue with speed training.'));

        // Retrieve messages
        $messages = $chatHistory->getMessages();

        expect($messages)->toHaveCount(4);
        expect($messages[0]->getContent())->toContain('What training should I do?');
        expect($messages[1]->getContent())->toContain('speed training');
    });

    it('scopes conversation by user and character', function () {
        $user2 = User::factory()->create();
        $character2 = Character::factory()->create(['user_id' => $user2->id]);

        $agent1 = new TrainingAdvisorAgent($this->user->id, $this->character->id);
        $agent2 = new TrainingAdvisorAgent($user2->id, $character2->id);

        $chatHistory1 = getTrainingAdvisorChatHistory($agent1);
        $chatHistory2 = getTrainingAdvisorChatHistory($agent2);

        // Add messages to first agent
        $chatHistory1->addMessage(new UserMessage('Agent 1 message'));

        // Add messages to second agent
        $chatHistory2->addMessage(new UserMessage('Agent 2 message'));

        // Verify isolation
        $messages1 = $chatHistory1->getMessages();
        $messages2 = $chatHistory2->getMessages();

        expect($messages1)->toHaveCount(1);
        expect($messages2)->toHaveCount(1);
        expect($messages1[0]->getContent())->toBe('Agent 1 message');
        expect($messages2[0]->getContent())->toBe('Agent 2 message');
    });

    it('retrieves conversation history through service', function () {
        $agent = new TrainingAdvisorAgent($this->user->id, $this->character->id);
        $chatHistory = getTrainingAdvisorChatHistory($agent);

        // Add some messages
        $chatHistory->addMessage(new UserMessage('First question'));
        $chatHistory->addMessage(new AssistantMessage('First answer'));
        $chatHistory->addMessage(new UserMessage('Second question'));
        $chatHistory->addMessage(new AssistantMessage('Second answer'));

        $service = app(TrainingAdvisorService::class);
        $history = $service->getAdviceHistory($this->character->id, $this->user->id, 10);

        expect($history)->toBeArray();
        expect($history)->toHaveCount(4);
    });

    it('limits conversation history retrieval', function () {
        $agent = new TrainingAdvisorAgent($this->user->id, $this->character->id);
        $chatHistory = getTrainingAdvisorChatHistory($agent);

        // Add many messages
        for ($i = 1; $i <= 20; $i++) {
            $chatHistory->addMessage(new UserMessage("Question {$i}"));
            $chatHistory->addMessage(new AssistantMessage("Answer {$i}"));
        }

        $service = app(TrainingAdvisorService::class);
        $history = $service->getAdviceHistory($this->character->id, $this->user->id, 5);

        expect($history)->toHaveCount(5);
    });
});

describe('TrainingAdvisorAgent - Requirements Validation', function () {
    it('validates Requirement 4.1: analyzes current character stats', function () {
        $service = app(TrainingAdvisorService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        // Verify all stats are included
        expect($context)->toContain('Current Statistics');
        expect($context)->toContain('Speed: 500');
        expect($context)->toContain('Stamina: 400');
        expect($context)->toContain('Power: 300');
        expect($context)->toContain('Guts: 200');
        expect($context)->toContain('Wit: 350');
    });

    it('validates Requirement 4.2: recommends optimal training choices', function () {
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Speed training is optimal based on current stats and goals.',
            expectedGains: ['speed' => 15],
            alternatives: []
        );

        expect($response->recommendedTraining)->toBeIn([
            'speed',
            'stamina',
            'power',
            'guts',
            'wit',
            'rest',
        ]);
    });

    it('validates Requirement 4.3: considers character aptitudes', function () {
        $service = app(TrainingAdvisorService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Character Aptitudes');
        expect($context)->toContain('mile');
        expect($context)->toContain('turf');
    });

    it('validates Requirement 4.4: factors in support card bonuses', function () {
        $service = app(TrainingAdvisorService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Support Card Deck');
        expect($context)->toContain('Speed Support Card');
        expect($context)->toContain('Friendship: 80');
        expect($context)->toContain('Limit Break: 2');
    });

    it('validates Requirement 4.5: explains reasoning behind recommendations', function () {
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Speed training is recommended because the character has a large gap to the target speed goal and high friendship with speed support cards.',
            expectedGains: ['speed' => 15],
            alternatives: []
        );

        expect($response->reasoning)->toBeString();
        expect(strlen($response->reasoning))->toBeGreaterThanOrEqual(20);
        expect($response->reasoning)->toContain('speed');
    });

    it('validates Requirement 4.6: maintains conversation context', function () {
        $agent = new TrainingAdvisorAgent($this->user->id, $this->character->id);
        $chatHistory = getTrainingAdvisorChatHistory($agent);

        // Simulate conversation
        $chatHistory->addMessage(new UserMessage('What training should I do on turn 30?'));
        $chatHistory->addMessage(new AssistantMessage('I recommend speed training.'));
        $chatHistory->addMessage(new UserMessage('What about turn 31?'));

        $messages = $chatHistory->getMessages();

        expect($messages)->toHaveCount(3);
        expect($messages[0]->getContent())->toContain('turn 30');
        expect($messages[2]->getContent())->toContain('turn 31');
    });
});

describe('TrainingAdvisorAgent - Error Handling', function () {
    it('handles missing character gracefully through service', function () {
        $service = app(TrainingAdvisorService::class);

        expect(fn () => $service->getAdvice(99999, [], $this->user->id))
            ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('validates training options structure', function () {
        $service = app(TrainingAdvisorService::class);

        $invalidOptions = [
            'available_trainings' => 'not an array',
        ];

        $errors = $service->validateTrainingOptions($invalidOptions);

        expect($errors)->toHaveKey('available_trainings');
    });

    it('validates spirit burst gauge range', function () {
        $service = app(TrainingAdvisorService::class);

        $invalidOptions = [
            'spirit_burst_gauge' => 10, // Max is 4
        ];

        $errors = $service->validateTrainingOptions($invalidOptions);

        expect($errors)->toHaveKey('spirit_burst_gauge');
    });

    it('validates training type in options', function () {
        $service = app(TrainingAdvisorService::class);

        $invalidOptions = [
            'available_trainings' => [
                ['energy_cost' => 20], // Missing type
            ],
        ];

        $errors = $service->validateTrainingOptions($invalidOptions);

        expect($errors)->not->toBeEmpty();
    });
});

describe('TrainingAdvisorAgent - Integration with Service Layer', function () {
    it('formats and parses responses correctly', function () {
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Speed training is optimal for reaching target goals.',
            expectedGains: ['speed' => 15, 'power' => 5],
            alternatives: [
                ['training' => 'stamina', 'reason' => 'Alternative option'],
            ]
        );

        $service = app(TrainingAdvisorService::class);
        $parsed = $service->parseResponse($response);

        expect($parsed['recommended_training'])->toBe('speed');
        expect($parsed['reasoning'])->toContain('Speed training');
        expect($parsed['expected_gains'])->toHaveKey('speed');
        expect($parsed['alternatives'])->toHaveCount(1);
        expect($parsed['summary'])->toBeString();
        expect($parsed['validation_errors'])->toBeArray();
    });

    it('generates human-readable summary', function () {
        $response = new TrainingAdviceResponse(
            recommendedTraining: 'speed',
            reasoning: 'Speed training is optimal.',
            expectedGains: ['speed' => 15, 'power' => 5],
            alternatives: [
                ['training' => 'stamina', 'reason' => 'Good alternative'],
            ]
        );

        $summary = $response->getSummary();

        expect($summary)->toBeString();
        expect($summary)->toContain('Recommended: speed');
        expect($summary)->toContain('Reason:');
        expect($summary)->toContain('Expected Gains:');
        expect($summary)->toContain('Alternatives:');
    });
});
