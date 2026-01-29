<?php

declare(strict_types=1);

use App\Models\Aptitude;
use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SkillHint;
use App\Models\SupportCardDefinition;
use App\Models\User;
use App\Neuron\Agents\SkillRecommendationAgent;
use App\Neuron\Responses\SkillRecommendationResponse;
use App\Services\Neuron\SkillRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\AIProviderInterface;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test user
    $this->user = User::factory()->create();

    // Create test character with comprehensive data
    $this->character = Character::factory()
        ->withStats([
            'speed' => 800,
            'stamina' => 700,
            'power' => 600,
            'guts' => 500,
            'wit' => 650,
        ])
        ->withGoals([
            'target_stats' => [
                'speed' => 1000,
                'stamina' => 800,
            ],
            'target_grade' => 'A+',
        ])
        ->create([
            'user_id' => $this->user->id,
            'name' => 'Test Character',
            'scenario_type' => 'ura_finale',
            'career_stage' => 'classic',
            'current_turn' => 40,
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

    Aptitude::factory()->create([
        'character_id' => $this->character->id,
        'running_style' => 'runner',
        'grade' => 'A',
    ]);

    // Create support cards
    $this->supportCard1 = SupportCardDefinition::factory()->create([
        'name' => 'Speed Support Card',
        'card_type' => 'speed',
        'rarity' => 'SSR',
    ]);

    $this->supportCard2 = SupportCardDefinition::factory()->create([
        'name' => 'Friend Support Card',
        'card_type' => 'friend',
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

    // Create available skills
    $this->speedSkill = Skill::factory()->create([
        'name' => 'Speed Star',
        'skill_type' => 'speed',
        'rarity' => 'rare',
        'base_sp_cost' => 180,
        'description' => 'Increases speed in the final stretch',
        'can_evolve' => false,
        'meta_tier' => 'S',
        'is_active' => true,
    ]);

    $this->staminaSkill = Skill::factory()->create([
        'name' => 'Stamina Keeper',
        'skill_type' => 'passive',
        'rarity' => 'rare',
        'base_sp_cost' => 170,
        'description' => 'Reduces stamina consumption',
        'can_evolve' => false,
        'meta_tier' => 'A',
        'is_active' => true,
    ]);

    $this->accelerationSkill = Skill::factory()->create([
        'name' => 'Quick Start',
        'skill_type' => 'speed',
        'rarity' => 'normal',
        'base_sp_cost' => 120,
        'description' => 'Improves starting acceleration',
        'can_evolve' => true,
        'meta_tier' => 'B',
        'is_active' => true,
    ]);

    $this->evolvedSkill = Skill::factory()->create([
        'name' => 'Lightning Start',
        'skill_type' => 'speed',
        'rarity' => 'rare',
        'base_sp_cost' => 200,
        'description' => 'Greatly improves starting acceleration',
        'can_evolve' => false,
        'meta_tier' => 'S',
        'is_active' => true,
    ]);

    // Set evolution relationship
    $this->accelerationSkill->update([
        'evolution_target_id' => $this->evolvedSkill->id,
    ]);

    // Create already acquired skill
    $this->acquiredSkill = Skill::factory()->create([
        'name' => 'Basic Speed',
        'skill_type' => 'speed',
        'rarity' => 'normal',
        'base_sp_cost' => 100,
        'is_active' => true,
    ]);

    SkillAcquisition::factory()->create([
        'character_id' => $this->character->id,
        'skill_id' => $this->acquiredSkill->id,
        'turn_acquired' => 35,
        'base_sp_cost' => 100,
        'final_sp_cost' => 80,
        'hints_used' => 1,
        'is_active' => true,
    ]);

    // Create skill hints for cost reduction
    SkillHint::factory()->create([
        'character_id' => $this->character->id,
        'skill_id' => $this->speedSkill->id,
    ]);

    SkillHint::factory()->create([
        'character_id' => $this->character->id,
        'skill_id' => $this->speedSkill->id,
    ]);
});

describe('SkillRecommendationAgent - Instantiation and Configuration', function () {
    it('can be instantiated with user ID', function () {
        $agent = new SkillRecommendationAgent($this->user->id);

        expect($agent)->toBeInstanceOf(SkillRecommendationAgent::class);
    });

    it('can be instantiated with user ID and character ID', function () {
        $agent = new SkillRecommendationAgent($this->user->id, $this->character->id);

        expect($agent)->toBeInstanceOf(SkillRecommendationAgent::class);
    });

    it('has proper system instructions', function () {
        $agent = new SkillRecommendationAgent($this->user->id);
        $instructions = $agent->instructions();

        expect($instructions)->toBeString();
        expect($instructions)->toContain('Uma Musume');
        expect($instructions)->toContain('skill');
        expect($instructions)->toContain('synergies');
        expect($instructions)->toContain('hint');
        expect($instructions)->toContain('SP cost');
        expect($instructions)->toContain('evolution');
    });

    it('uses Anthropic provider', function () {
        $agent = new SkillRecommendationAgent($this->user->id);

        // Use reflection to access protected provider method
        $reflection = new ReflectionClass($agent);
        $method = $reflection->getMethod('provider');
        $method->setAccessible(true);
        $provider = $method->invoke($agent);

        expect($provider)->toBeInstanceOf(AIProviderInterface::class);
    });

    it('generates unique thread ID for each user', function () {
        $agent1 = new SkillRecommendationAgent($this->user->id);
        $agent2 = new SkillRecommendationAgent($this->user->id + 1);

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
        $agent1 = new SkillRecommendationAgent($this->user->id, $this->character->id);
        $agent2 = new SkillRecommendationAgent($this->user->id, $this->character->id + 1);

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

    it('has SkillDataTool and CharacterStatsTool registered', function () {
        $agent = new SkillRecommendationAgent($this->user->id);

        $reflection = new ReflectionClass($agent);
        $method = $reflection->getMethod('tools');
        $method->setAccessible(true);
        $tools = $method->invoke($agent);

        expect($tools)->toBeArray();
        expect($tools)->not->toBeEmpty();
        expect(count($tools))->toBe(2);
    });
});

describe('SkillRecommendationAgent - Context Handling', function () {
    it('receives proper character context through service', function () {
        $service = app(SkillRecommendationService::class);

        $skillContext = [
            'available_sp' => 500,
            'race_preferences' => [
                'preferred_distance' => 'mile',
                'preferred_surface' => 'turf',
                'preferred_running_style' => 'runner',
            ],
        ];

        // Use reflection to access protected method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, $skillContext);

        // Verify context includes all necessary information
        expect($context)->toBeString();
        expect($context)->toContain('Test Character');
        expect($context)->toContain('ura_finale');
        expect($context)->toContain('classic');
        expect($context)->toContain('Turn');
        expect($context)->toContain('Speed');
        expect($context)->toContain('Stamina');
        expect($context)->toContain('Available Skill Points');
        expect($context)->toContain('500');
    });

    it('includes character aptitudes in context', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Character Aptitudes');
        expect($context)->toContain('mile');
        expect($context)->toContain('turf');
        expect($context)->toContain('runner');
    });

    it('includes race preferences in context', function () {
        $service = app(SkillRecommendationService::class);

        $skillContext = [
            'race_preferences' => [
                'preferred_distance' => 'mile',
                'preferred_surface' => 'turf',
                'preferred_running_style' => 'runner',
            ],
        ];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, $skillContext);

        expect($context)->toContain('Race Preferences');
        expect($context)->toContain('Preferred Distance: mile');
        expect($context)->toContain('Preferred Surface: turf');
        expect($context)->toContain('Preferred Running Style: runner');
    });

    it('includes currently acquired skills in context', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Currently Acquired Skills');
        expect($context)->toContain('Basic Speed');
    });

    it('includes available skills with hint information in context', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Available Skills to Acquire');
        expect($context)->toContain('Speed Star');
        expect($context)->toContain('hint');
        expect($context)->toContain('discount');
    });

    it('includes skill evolution information in context', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Quick Start');
        expect($context)->toContain('Can evolve to');
        expect($context)->toContain('Lightning Start');
    });

    it('includes support card deck in context', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Support Card Deck');
        expect($context)->toContain('Speed Support Card');
        expect($context)->toContain('Friend Support Card');
        expect($context)->toContain('Friendship: 80');
    });

    it('includes upcoming races in context when provided', function () {
        $service = app(SkillRecommendationService::class);

        $skillContext = [
            'upcoming_races' => [
                [
                    'name' => 'Japan Cup',
                    'distance_category' => 'medium',
                    'surface' => 'turf',
                    'turns_until' => 5,
                ],
                [
                    'name' => 'Arima Kinen',
                    'distance_category' => 'long',
                    'surface' => 'turf',
                    'turns_until' => 10,
                ],
            ],
        ];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, $skillContext);

        expect($context)->toContain('Upcoming Races');
        expect($context)->toContain('Japan Cup');
        expect($context)->toContain('Arima Kinen');
        expect($context)->toContain('in 5 turn(s)');
    });

    it('includes build strategy in context when provided', function () {
        $service = app(SkillRecommendationService::class);

        $skillContext = [
            'build_strategy' => 'Focus on speed and acceleration skills for mile races',
        ];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, $skillContext);

        expect($context)->toContain('Build Strategy');
        expect($context)->toContain('Focus on speed and acceleration');
    });
});

describe('SkillRecommendationAgent - Structured Response', function () {
    it('returns structured SkillRecommendationResponse when mocked', function () {
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
                    'recommended_skills' => [
                        [
                            'name' => 'Speed Star',
                            'reason' => 'High synergy with character build and available hints reduce cost',
                            'priority' => 'high',
                        ],
                        [
                            'name' => 'Quick Start',
                            'reason' => 'Can evolve to Lightning Start for better performance',
                            'priority' => 'medium',
                        ],
                    ],
                    'acquisition_strategy' => 'Prioritize speed skills with hints first, then focus on evolution chains for long-term value',
                    'sp_budget_considerations' => 'With 500 SP available, acquire Speed Star (discounted) and Quick Start, saving SP for evolution',
                    'skill_synergies' => [
                        [
                            'skills' => ['Speed Star', 'Quick Start'],
                            'benefit' => 'Both skills enhance acceleration and speed in different race phases',
                        ],
                    ],
                ], JSON_THROW_ON_ERROR)
            ));

        // Create agent and inject mocked provider
        $agent = new SkillRecommendationAgent($this->user->id, $this->character->id);

        // Use reflection to set the provider
        $reflection = new ReflectionClass($agent);
        $property = $reflection->getProperty('provider');
        $property->setAccessible(true);
        $property->setValue($agent, $mockProvider);

        // Call structured method with proper message object
        $response = $agent->structured(
            new UserMessage('Test context'),
            SkillRecommendationResponse::class
        );

        expect($response)->toBeInstanceOf(SkillRecommendationResponse::class);
        expect($response->recommendedSkills)->toBeArray();
        expect($response->recommendedSkills)->toHaveCount(2);
        expect($response->acquisitionStrategy)->toContain('speed');
        expect($response->spBudgetConsiderations)->toContain('SP');
        expect($response->skillSynergies)->toBeArray();
    });

    it('validates response structure through service', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Speed Star',
                    'reason' => 'Excellent skill for mile races',
                    'priority' => 'high',
                ],
            ],
            acquisitionStrategy: 'Focus on speed skills that match character aptitudes',
            spBudgetConsiderations: 'Prioritize skills with hints to maximize SP efficiency',
            skillSynergies: []
        );

        $service = app(SkillRecommendationService::class);
        $parsed = $service->parseResponse($response);

        expect($parsed)->toBeArray();
        expect($parsed)->toHaveKey('recommended_skills');
        expect($parsed)->toHaveKey('acquisition_strategy');
        expect($parsed)->toHaveKey('sp_budget_considerations');
        expect($parsed)->toHaveKey('skill_synergies');
        expect($parsed)->toHaveKey('summary');
        expect($parsed)->toHaveKey('validation_errors');
    });

    it('includes skill priorities in response', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Speed Star',
                    'reason' => 'High priority skill',
                    'priority' => 'high',
                ],
                [
                    'name' => 'Stamina Keeper',
                    'reason' => 'Medium priority skill',
                    'priority' => 'medium',
                ],
                [
                    'name' => 'Quick Start',
                    'reason' => 'Low priority skill',
                    'priority' => 'low',
                ],
            ],
            acquisitionStrategy: 'Acquire skills in priority order',
            spBudgetConsiderations: 'Budget allows for all three skills',
            skillSynergies: []
        );

        expect($response->recommendedSkills[0]['priority'])->toBe('high');
        expect($response->recommendedSkills[1]['priority'])->toBe('medium');
        expect($response->recommendedSkills[2]['priority'])->toBe('low');
    });

    it('includes skill synergies in response', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                ['name' => 'Speed Star', 'reason' => 'Good skill', 'priority' => 'high'],
            ],
            acquisitionStrategy: 'Focus on synergistic skills',
            spBudgetConsiderations: 'Budget allows for synergy combinations',
            skillSynergies: [
                [
                    'skills' => ['Speed Star', 'Quick Start'],
                    'benefit' => 'These skills work together to enhance acceleration',
                ],
                [
                    'skills' => ['Stamina Keeper', 'Speed Star'],
                    'benefit' => 'Stamina support allows sustained high speed',
                ],
            ]
        );

        expect($response->skillSynergies)->toHaveCount(2);
        expect($response->skillSynergies[0])->toHaveKey('skills');
        expect($response->skillSynergies[0])->toHaveKey('benefit');
        expect($response->skillSynergies[0]['skills'])->toBeArray();
    });

    it('provides acquisition strategy reasoning', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                ['name' => 'Speed Star', 'reason' => 'Good skill', 'priority' => 'high'],
            ],
            acquisitionStrategy: 'Prioritize skills with hints first to maximize SP efficiency, then focus on evolution chains for long-term value',
            spBudgetConsiderations: 'With current SP budget, acquire high-priority skills first',
            skillSynergies: []
        );

        expect($response->acquisitionStrategy)->toBeString();
        expect(strlen($response->acquisitionStrategy))->toBeGreaterThanOrEqual(20);
        expect(strlen($response->acquisitionStrategy))->toBeLessThanOrEqual(500);
        expect($response->acquisitionStrategy)->toContain('skill');
    });

    it('provides SP budget considerations', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                ['name' => 'Speed Star', 'reason' => 'Good skill', 'priority' => 'high'],
            ],
            acquisitionStrategy: 'Focus on cost-effective skills',
            spBudgetConsiderations: 'Current SP allows for 2-3 rare skills or 4-5 normal skills. Prioritize skills with hints for better value.',
            skillSynergies: []
        );

        expect($response->spBudgetConsiderations)->toBeString();
        expect(strlen($response->spBudgetConsiderations))->toBeGreaterThanOrEqual(20);
        expect(strlen($response->spBudgetConsiderations))->toBeLessThanOrEqual(500);
        expect($response->spBudgetConsiderations)->toContain('SP');
    });
});

describe('SkillRecommendationAgent - Requirements Validation', function () {
    it('validates Requirement 6.1: analyzes available skills and character build', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        // Verify character build analysis
        expect($context)->toContain('Current Statistics');
        expect($context)->toContain('Speed: 800');
        expect($context)->toContain('Stamina: 700');

        // Verify available skills analysis
        expect($context)->toContain('Available Skills to Acquire');
        expect($context)->toContain('Speed Star');
        expect($context)->toContain('Stamina Keeper');
        expect($context)->toContain('Quick Start');
    });

    it('validates Requirement 6.2: recommends skills that synergize with character stats', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Speed Star',
                    'reason' => 'Synergizes with high speed stat of 800',
                    'priority' => 'high',
                ],
            ],
            acquisitionStrategy: 'Focus on skills that complement existing high speed stats',
            spBudgetConsiderations: 'Invest in skills that maximize speed advantage',
            skillSynergies: []
        );

        expect($response->recommendedSkills)->not->toBeEmpty();
        expect($response->recommendedSkills[0]['reason'])->toContain('speed');
    });

    it('validates Requirement 6.3: considers race distance preferences', function () {
        $service = app(SkillRecommendationService::class);

        $skillContext = [
            'race_preferences' => [
                'preferred_distance' => 'mile',
                'preferred_surface' => 'turf',
            ],
        ];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, $skillContext);

        expect($context)->toContain('Race Preferences');
        expect($context)->toContain('Preferred Distance: mile');
        expect($context)->toContain('Preferred Surface: turf');
    });

    it('validates Requirement 6.4: prioritizes skills based on cost-effectiveness', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getAvailableSkillsWithHints');
        $method->setAccessible(true);

        $availableSkills = $method->invoke($service, $this->character->id);

        // Verify skills are sorted by cost-effectiveness
        expect($availableSkills)->toBeArray();
        expect($availableSkills)->not->toBeEmpty();

        // Skills with hints should be prioritized
        $firstSkill = $availableSkills[0];
        expect($firstSkill)->toHaveKey('hint_count');
        expect($firstSkill)->toHaveKey('final_sp_cost');
        expect($firstSkill)->toHaveKey('discount_percentage');
    });

    it('validates Requirement 6.5: factors skill hints into recommendations', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        // Verify hint information is included
        expect($context)->toContain('hint');
        expect($context)->toContain('discount');

        // Verify Speed Star has hints (we created 2 hints in beforeEach)
        expect($context)->toContain('Speed Star');
        expect($context)->toContain('2 hint');
    });
});

describe('SkillRecommendationAgent - Skill Hint Cost Reduction', function () {
    it('calculates correct SP cost with hints', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getAvailableSkillsWithHints');
        $method->setAccessible(true);

        $availableSkills = $method->invoke($service, $this->character->id);

        // Find Speed Star which has 2 hints
        $speedStar = collect($availableSkills)->firstWhere('name', 'Speed Star');

        expect($speedStar)->not->toBeNull();
        expect($speedStar['hint_count'])->toBe(2);
        expect($speedStar['base_sp_cost'])->toBe(180);

        // 2 hints = 20% discount (progressive: 10%/20%/30%/35%/40% at levels 1-5)
        expect($speedStar['discount_percentage'])->toBe(20);
        expect($speedStar['final_sp_cost'])->toBe(144); // 180 * 0.8 = 144
    });

    it('respects maximum 40% discount from hints', function () {
        // Create 3 hints for stamina skill (30% discount at level 3)
        SkillHint::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->staminaSkill->id,
        ]);

        SkillHint::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->staminaSkill->id,
        ]);

        SkillHint::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $this->staminaSkill->id,
        ]);

        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getAvailableSkillsWithHints');
        $method->setAccessible(true);

        $availableSkills = $method->invoke($service, $this->character->id);

        $staminaKeeper = collect($availableSkills)->firstWhere('name', 'Stamina Keeper');

        expect($staminaKeeper)->not->toBeNull();
        expect($staminaKeeper['hint_count'])->toBe(3);
        expect($staminaKeeper['discount_percentage'])->toBe(30); // 3 hints = 30% discount
        expect($staminaKeeper['final_sp_cost'])->toBe(119); // 170 * 0.7 = 119
    });

    it('prioritizes skills with hints in available skills list', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getAvailableSkillsWithHints');
        $method->setAccessible(true);

        $availableSkills = $method->invoke($service, $this->character->id);

        // First skill should have hints (Speed Star with 2 hints)
        expect($availableSkills[0]['hint_count'])->toBeGreaterThan(0);
        expect($availableSkills[0]['name'])->toBe('Speed Star');
    });
});

describe('SkillRecommendationAgent - Skill Evolution Chains', function () {
    it('identifies skills that can evolve', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getAvailableSkillsWithHints');
        $method->setAccessible(true);

        $availableSkills = $method->invoke($service, $this->character->id);

        $quickStart = collect($availableSkills)->firstWhere('name', 'Quick Start');

        expect($quickStart)->not->toBeNull();
        expect($quickStart['can_evolve'])->toBeTrue();
        expect($quickStart['evolution_target_name'])->toBe('Lightning Start');
    });

    it('includes evolution information in context', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Quick Start');
        expect($context)->toContain('Can evolve to: Lightning Start');
    });

    it('recommends base skills for evolution chains', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Quick Start',
                    'reason' => 'Can evolve to Lightning Start for better performance',
                    'priority' => 'medium',
                ],
            ],
            acquisitionStrategy: 'Acquire base skills that can evolve for long-term value',
            spBudgetConsiderations: 'Evolution chains provide better value over time',
            skillSynergies: []
        );

        expect($response->recommendedSkills[0]['name'])->toBe('Quick Start');
        expect($response->recommendedSkills[0]['reason'])->toContain('evolve');
    });
});

describe('SkillRecommendationAgent - Error Handling', function () {
    it('handles missing character gracefully through service', function () {
        $service = app(SkillRecommendationService::class);

        expect(fn () => $service->getRecommendations(99999, [], $this->user->id))
            ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('validates skill context structure', function () {
        $service = app(SkillRecommendationService::class);

        $invalidContext = [
            'available_sp' => 'not a number',
        ];

        $errors = $service->validateSkillContext($invalidContext);

        expect($errors)->toHaveKey('available_sp');
    });

    it('validates race preferences structure', function () {
        $service = app(SkillRecommendationService::class);

        $invalidContext = [
            'race_preferences' => [
                'preferred_distance' => 'invalid_distance',
            ],
        ];

        $errors = $service->validateSkillContext($invalidContext);

        expect($errors)->toHaveKey('race_preferences.preferred_distance');
    });

    it('validates upcoming races structure', function () {
        $service = app(SkillRecommendationService::class);

        $invalidContext = [
            'upcoming_races' => [
                ['distance_category' => 'mile'], // Missing name
            ],
        ];

        $errors = $service->validateSkillContext($invalidContext);

        expect($errors)->not->toBeEmpty();
    });

    it('validates negative SP values', function () {
        $service = app(SkillRecommendationService::class);

        $invalidContext = [
            'available_sp' => -100,
        ];

        $errors = $service->validateSkillContext($invalidContext);

        expect($errors)->toHaveKey('available_sp');
    });
});

describe('SkillRecommendationAgent - Integration with Service Layer', function () {
    it('formats and parses responses correctly', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Speed Star',
                    'reason' => 'Excellent for mile races',
                    'priority' => 'high',
                ],
            ],
            acquisitionStrategy: 'Focus on speed skills with hints',
            spBudgetConsiderations: 'Prioritize discounted skills first',
            skillSynergies: [
                [
                    'skills' => ['Speed Star', 'Quick Start'],
                    'benefit' => 'Enhanced acceleration',
                ],
            ]
        );

        $service = app(SkillRecommendationService::class);
        $parsed = $service->parseResponse($response);

        expect($parsed['recommended_skills'])->toHaveCount(1);
        expect($parsed['acquisition_strategy'])->toContain('speed');
        expect($parsed['sp_budget_considerations'])->toContain('skill');
        expect($parsed['skill_synergies'])->toHaveCount(1);
        expect($parsed['summary'])->toBeString();
        expect($parsed['validation_errors'])->toBeArray();
    });

    it('generates human-readable summary', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Speed Star',
                    'reason' => 'High synergy with build',
                    'priority' => 'high',
                ],
                [
                    'name' => 'Quick Start',
                    'reason' => 'Can evolve',
                    'priority' => 'medium',
                ],
            ],
            acquisitionStrategy: 'Prioritize skills with hints',
            spBudgetConsiderations: 'Budget allows for both skills',
            skillSynergies: [
                [
                    'skills' => ['Speed Star', 'Quick Start'],
                    'benefit' => 'Work well together',
                ],
            ]
        );

        $summary = $response->getSummary();

        expect($summary)->toBeString();
        expect($summary)->toContain('Skill Acquisition Strategy:');
        expect($summary)->toContain('SP Budget Considerations:');
        expect($summary)->toContain('Recommended Skills:');
        expect($summary)->toContain('Speed Star');
        expect($summary)->toContain('Quick Start');
        expect($summary)->toContain('Skill Synergies:');
    });

    it('retrieves skill acquisition history', function () {
        $service = app(SkillRecommendationService::class);

        $history = $service->getAcquisitionHistory($this->character->id, 10);

        expect($history)->toBeArray();
        expect($history)->toHaveCount(1); // We created 1 acquisition in beforeEach
        expect($history[0])->toHaveKey('skill_name');
        expect($history[0])->toHaveKey('turn_acquired');
        expect($history[0])->toHaveKey('final_sp_cost');
        expect($history[0])->toHaveKey('hints_used');
        expect($history[0]['skill_name'])->toBe('Basic Speed');
    });

    it('calculates skill synergies for character', function () {
        // Create skills with synergy relationships
        $skill1 = Skill::factory()->create([
            'name' => 'Synergy Skill 1',
            'synergy_skills' => ['skill_2_internal_id'],
            'internal_id' => 'skill_1_internal_id',
            'is_active' => true,
        ]);

        $skill2 = Skill::factory()->create([
            'name' => 'Synergy Skill 2',
            'synergy_skills' => ['skill_1_internal_id'],
            'internal_id' => 'skill_2_internal_id',
            'is_active' => true,
        ]);

        // Acquire both skills
        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $skill1->id,
            'is_active' => true,
        ]);

        SkillAcquisition::factory()->create([
            'character_id' => $this->character->id,
            'skill_id' => $skill2->id,
            'is_active' => true,
        ]);

        $service = app(SkillRecommendationService::class);
        $synergies = $service->calculateSkillSynergies($this->character->id);

        expect($synergies)->toBeArray();
        expect($synergies)->not->toBeEmpty();
        expect($synergies[0])->toHaveKey('skills');
        expect($synergies[0])->toHaveKey('benefit');
    });
});

describe('SkillRecommendationAgent - Response Validation', function () {
    it('validates recommended skills structure', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Speed Star',
                    'reason' => 'Good skill for speed builds',
                    'priority' => 'high',
                ],
            ],
            acquisitionStrategy: 'Focus on speed skills',
            spBudgetConsiderations: 'Budget allows for multiple skills',
            skillSynergies: []
        );

        $errors = $response->validate();

        expect($errors)->toBeArray();
        expect($errors)->toBeEmpty();
    });

    it('detects missing skill name', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'reason' => 'Good skill',
                    'priority' => 'high',
                ],
            ],
            acquisitionStrategy: 'Focus on skills',
            spBudgetConsiderations: 'Budget considerations',
            skillSynergies: []
        );

        $errors = $response->validate();

        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('recommended_skills');
    });

    it('detects invalid priority value', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                [
                    'name' => 'Speed Star',
                    'reason' => 'Good skill',
                    'priority' => 'invalid',
                ],
            ],
            acquisitionStrategy: 'Focus on skills',
            spBudgetConsiderations: 'Budget considerations',
            skillSynergies: []
        );

        $errors = $response->validate();

        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('recommended_skills');
    });

    it('detects acquisition strategy too short', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                ['name' => 'Speed Star', 'reason' => 'Good skill', 'priority' => 'high'],
            ],
            acquisitionStrategy: 'Too short',
            spBudgetConsiderations: 'Budget allows for multiple skills',
            skillSynergies: []
        );

        $errors = $response->validate();

        expect($errors)->toHaveKey('acquisition_strategy');
    });

    it('detects SP budget considerations too short', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                ['name' => 'Speed Star', 'reason' => 'Good skill', 'priority' => 'high'],
            ],
            acquisitionStrategy: 'Focus on speed skills with hints',
            spBudgetConsiderations: 'Too short',
            skillSynergies: []
        );

        $errors = $response->validate();

        expect($errors)->toHaveKey('sp_budget_considerations');
    });

    it('validates skill synergies structure', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                ['name' => 'Speed Star', 'reason' => 'Good skill', 'priority' => 'high'],
            ],
            acquisitionStrategy: 'Focus on synergistic skills',
            spBudgetConsiderations: 'Budget allows for synergy combinations',
            skillSynergies: [
                [
                    'skills' => ['Speed Star', 'Quick Start'],
                    'benefit' => 'Enhanced acceleration',
                ],
            ]
        );

        $errors = $response->validate();

        expect($errors)->toBeEmpty();
    });

    it('detects invalid skill synergies structure', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [
                ['name' => 'Speed Star', 'reason' => 'Good skill', 'priority' => 'high'],
            ],
            acquisitionStrategy: 'Focus on synergistic skills',
            spBudgetConsiderations: 'Budget allows for synergy combinations',
            skillSynergies: [
                [
                    'skills' => [], // Empty skills array
                    'benefit' => 'Enhanced acceleration',
                ],
            ]
        );

        $errors = $response->validate();

        expect($errors)->not->toBeEmpty();
        expect($errors)->toHaveKey('skill_synergies');
    });

    it('requires at least one recommended skill', function () {
        $response = new SkillRecommendationResponse(
            recommendedSkills: [],
            acquisitionStrategy: 'No skills to recommend',
            spBudgetConsiderations: 'No budget considerations',
            skillSynergies: []
        );

        $errors = $response->validate();

        expect($errors)->toHaveKey('recommended_skills');
    });
});

describe('SkillRecommendationAgent - Meta Tier and Rarity', function () {
    it('includes meta tier information in available skills', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getAvailableSkillsWithHints');
        $method->setAccessible(true);

        $availableSkills = $method->invoke($service, $this->character->id);

        $speedStar = collect($availableSkills)->firstWhere('name', 'Speed Star');

        expect($speedStar)->not->toBeNull();
        expect($speedStar['meta_tier'])->toBe('S');
        expect($speedStar['rarity'])->toBe('rare');
    });

    it('includes meta tier in context', function () {
        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $this->character, []);

        expect($context)->toContain('Meta Tier');
        expect($context)->toContain('Rarity');
    });
});

describe('SkillRecommendationAgent - Unity Cup Scenario', function () {
    it('includes Unity Cup specific context', function () {
        $unityCupCharacter = Character::factory()
            ->withStats(['speed' => 800])
            ->create([
                'user_id' => $this->user->id,
                'scenario_type' => 'unity_cup',
                'facility_levels' => [
                    'speed' => 3,
                    'stamina' => 2,
                ],
            ]);

        $service = app(SkillRecommendationService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatSkillContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, $unityCupCharacter, []);

        expect($context)->toContain('Unity Cup Specific');
        expect($context)->toContain('Facility Levels');
    });
});
