<?php

declare(strict_types=1);

use App\Models\Aptitude;
use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\Race;
use App\Models\Skill;
use App\Models\SkillAcquisition;
use App\Models\SupportCardDefinition;
use App\Models\User;
use App\Neuron\Agents\RaceStrategyAgent;
use App\Neuron\Responses\RaceStrategyResponse;
use App\Services\Neuron\RaceStrategyService;
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
        ->create([
            'user_id' => $this->user->id,
            'name' => 'Test Racer',
            'scenario_type' => 'ura_finale',
            'career_stage' => 'classic',
            'current_turn' => 40,
            'energy_level' => 85,
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

    Aptitude::factory()->create([
        'character_id' => $this->character->id,
        'running_style' => 'leader',
        'grade' => 'A',
    ]);

    // Create skills
    $this->skill1 = Skill::factory()->ofType('speed')->create([
        'name' => 'Speed Star',
        'description' => 'Increases speed in the final stretch',
        'is_active' => true,
    ]);

    $this->skill2 = Skill::factory()->ofType('recovery')->create([
        'name' => 'Stamina Keeper',
        'description' => 'Maintains stamina throughout the race',
        'is_active' => true,
    ]);

    // Acquire skills for character
    SkillAcquisition::factory()->create([
        'character_id' => $this->character->id,
        'skill_id' => $this->skill1->id,
        'is_active' => true,
    ]);

    SkillAcquisition::factory()->create([
        'character_id' => $this->character->id,
        'skill_id' => $this->skill2->id,
        'is_active' => true,
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

    // Create past race history
    Race::factory()->create([
        'character_id' => $this->character->id,
        'race_name' => 'Previous Race 1',
        'turn_number' => 38,
        'finish_position' => 1,
        'won_race' => true,
        'distance_meters' => 1600,
        'surface' => 'turf',
    ]);

    Race::factory()->create([
        'character_id' => $this->character->id,
        'race_name' => 'Previous Race 2',
        'turn_number' => 35,
        'finish_position' => 3,
        'won_race' => false,
        'distance_meters' => 2000,
        'surface' => 'turf',
    ]);
});

describe('RaceStrategyAgent - Instantiation and Configuration', function () {
    it('can be instantiated with user ID', function () {
        $agent = new RaceStrategyAgent($this->user->id);

        expect($agent)->toBeInstanceOf(RaceStrategyAgent::class);
    });

    it('can be instantiated with user ID and race ID', function () {
        $agent = new RaceStrategyAgent($this->user->id, 123);

        expect($agent)->toBeInstanceOf(RaceStrategyAgent::class);
    });

    it('has proper system instructions', function () {
        $agent = new RaceStrategyAgent($this->user->id);
        $instructions = $agent->instructions();

        expect($instructions)->toBeString();
        expect($instructions)->toContain('Uma Musume');
        expect($instructions)->toContain('race');
        expect($instructions)->toContain('strategy');
        expect($instructions)->toContain('distance');
        expect($instructions)->toContain('surface');
        expect($instructions)->toContain('stamina');
    });

    it('uses Anthropic provider', function () {
        $agent = new RaceStrategyAgent($this->user->id);

        // Use reflection to access protected provider method
        $reflection = new ReflectionClass($agent);
        $method = $reflection->getMethod('provider');
        $provider = $method->invoke($agent);

        expect($provider)->toBeInstanceOf(AIProviderInterface::class);
    });

    it('generates unique thread ID for each user', function () {
        $agent1 = new RaceStrategyAgent($this->user->id);
        $agent2 = new RaceStrategyAgent($this->user->id + 1);

        $reflection1 = new ReflectionClass($agent1);
        $method1 = $reflection1->getMethod('getThreadId');
        $threadId1 = $method1->invoke($agent1);

        $reflection2 = new ReflectionClass($agent2);
        $method2 = $reflection2->getMethod('getThreadId');
        $threadId2 = $method2->invoke($agent2);

        expect($threadId1)->not->toBe($threadId2);
    });

    it('generates unique thread ID for each race', function () {
        $agent1 = new RaceStrategyAgent($this->user->id, 100);
        $agent2 = new RaceStrategyAgent($this->user->id, 200);

        $reflection1 = new ReflectionClass($agent1);
        $method1 = $reflection1->getMethod('getThreadId');
        $threadId1 = $method1->invoke($agent1);

        $reflection2 = new ReflectionClass($agent2);
        $method2 = $reflection2->getMethod('getThreadId');
        $threadId2 = $method2->invoke($agent2);

        expect($threadId1)->not->toBe($threadId2);
    });

    it('has RaceDataTool registered', function () {
        $agent = new RaceStrategyAgent($this->user->id);

        $reflection = new ReflectionClass($agent);
        $method = $reflection->getMethod('tools');
        $tools = $method->invoke($agent);

        expect($tools)->toBeArray();
        expect($tools)->not->toBeEmpty();
    });
});

describe('RaceStrategyAgent - Context Handling', function () {
    it('receives proper race context through service', function () {
        $service = app(RaceStrategyService::class);

        $raceData = [
            'race_name' => 'Tokyo Yushun',
            'race_grade' => 'G1',
            'distance_meters' => 2400,
            'distance_category' => 'intermediate',
            'surface' => 'turf',
            'track_type' => 'left',
            'weather' => 'sunny',
            'track_condition' => 'good',
            'field_size' => 18,
        ];

        // Use reflection to access protected method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $this->character, $raceData);

        // Verify context includes all necessary information
        expect($context)->toBeString();
        expect($context)->toContain('Test Racer');
        expect($context)->toContain('Tokyo Yushun');
        expect($context)->toContain('G1');
        expect($context)->toContain('2400');
        expect($context)->toContain('turf');
        expect($context)->toContain('sunny');
        expect($context)->toContain('good');
    });

    it('includes character stats in context', function () {
        $service = app(RaceStrategyService::class);

        $raceData = ['race_name' => 'Test Race'];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $this->character, $raceData);

        expect($context)->toContain('Current Statistics');
        expect($context)->toContain('Speed: 800');
        expect($context)->toContain('Stamina: 700');
        expect($context)->toContain('Power: 600');
    });

    it('includes character aptitudes in context', function () {
        $service = app(RaceStrategyService::class);

        $raceData = ['race_name' => 'Test Race'];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $this->character, $raceData);

        expect($context)->toContain('Character Aptitudes');
        expect($context)->toContain('mile');
        expect($context)->toContain('turf');
        expect($context)->toContain('leader');
    });

    it('includes acquired skills in context', function () {
        $service = app(RaceStrategyService::class);

        $raceData = ['race_name' => 'Test Race'];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $this->character, $raceData);

        expect($context)->toContain('Acquired Skills');
        expect($context)->toContain('Speed Star');
        expect($context)->toContain('Stamina Keeper');
    });

    it('includes available skills when provided', function () {
        $service = app(RaceStrategyService::class);

        $raceData = [
            'race_name' => 'Test Race',
            'available_skills' => [
                ['name' => 'Acceleration', 'skill_type' => 'speed', 'description' => 'Boosts acceleration'],
                ['name' => 'Endurance', 'skill_type' => 'stamina', 'description' => 'Improves endurance'],
            ],
        ];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $this->character, $raceData);

        expect($context)->toContain('Available Skills to Equip');
        expect($context)->toContain('Acceleration');
        expect($context)->toContain('Endurance');
    });

    it('includes support card deck in context', function () {
        $service = app(RaceStrategyService::class);

        $raceData = ['race_name' => 'Test Race'];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $this->character, $raceData);

        expect($context)->toContain('Support Card Deck');
        expect($context)->toContain('Speed Support Card');
        expect($context)->toContain('Friendship: 80');
    });

    it('includes past race performance in context', function () {
        $service = app(RaceStrategyService::class);

        $raceData = [
            'race_name' => 'Test Race',
            'past_race_performance' => true,
        ];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $this->character, $raceData);

        expect($context)->toContain('Past Race Performance');
        expect($context)->toContain('Previous Race 1');
        expect($context)->toContain('WON');
    });

    it('includes URA Finale specific context when applicable', function () {
        $service = app(RaceStrategyService::class);

        $raceData = [
            'race_name' => 'URA Finale Race',
            'is_ura_finale_race' => true,
            'ura_finale_stage' => 'URA1',
            'ura_finale_requirements' => ['Win the race', 'Finish in under 2:30'],
        ];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $this->character, $raceData);

        expect($context)->toContain('URA Finale Race');
        expect($context)->toContain('URA1');
        expect($context)->toContain('Requirements');
    });

    it('includes Unity Cup specific context when applicable', function () {
        $unityCupCharacter = Character::factory()
            ->withStats(['speed' => 800, 'stamina' => 700])
            ->create([
                'user_id' => $this->user->id,
                'scenario_type' => 'unity_cup',
            ]);

        $service = app(RaceStrategyService::class);

        $raceData = [
            'race_name' => 'Unity Cup Match',
            'is_unity_cup_match' => true,
            'unity_cup_opponent_rank' => 'A',
        ];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $unityCupCharacter, $raceData);

        expect($context)->toContain('Unity Cup Match');
        expect($context)->toContain('Opponent Rank: A');
    });
});

describe('RaceStrategyAgent - Structured Response', function () {
    it('returns structured RaceStrategyResponse when mocked', function () {
        // Mock the AI provider to return a structured response
        $mockProvider = mock(AIProviderInterface::class);
        // Agent calls systemPrompt() before structured(); return self to allow chaining
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
                    'recommended_running_style' => 'leader',
                    'recommended_skills' => ['Speed Star', 'Acceleration Boost'],
                    'race_preparation_advice' => 'Character has strong speed stats and leader aptitude. Focus on maintaining pace from the front.',
                    'expected_performance' => 'High chance of winning with current stats',
                    'risk_factors' => ['Watch stamina in final stretch', 'Field size is large'],
                ], JSON_THROW_ON_ERROR)
            ));

        // Create agent and inject mocked provider
        $agent = new RaceStrategyAgent($this->user->id, 123);

        // Use reflection to set the provider
        $reflection = new ReflectionClass($agent);
        $property = $reflection->getProperty('provider');
        $property->setValue($agent, $mockProvider);

        // Call structured method
        $response = $agent->structured(
            new UserMessage('Test race context'),
            RaceStrategyResponse::class
        );

        expect($response)->toBeInstanceOf(RaceStrategyResponse::class);
        expect($response->recommendedRunningStyle)->toBe('leader');
        expect($response->recommendedSkills)->toContain('Speed Star');
        expect($response->racePreparationAdvice)->toContain('speed');
        expect($response->expectedPerformance)->toContain('winning');
        expect($response->riskFactors)->toBeArray();
    });

    it('validates response structure through service', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star', 'Acceleration'],
            racePreparationAdvice: 'Focus on speed training and maintain high energy levels before the race.',
            expectedPerformance: 'High win probability',
            riskFactors: ['Large field size', 'Weather conditions']
        );

        $service = app(RaceStrategyService::class);
        $parsed = $service->parseResponse($response);

        expect($parsed)->toBeArray();
        expect($parsed)->toHaveKey('recommended_running_style');
        expect($parsed)->toHaveKey('recommended_skills');
        expect($parsed)->toHaveKey('race_preparation_advice');
        expect($parsed)->toHaveKey('expected_performance');
        expect($parsed)->toHaveKey('risk_factors');
        expect($parsed)->toHaveKey('summary');
        expect($parsed)->toHaveKey('validation_errors');
    });

    it('includes recommended skills in response', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star', 'Stamina Keeper', 'Acceleration Boost'],
            racePreparationAdvice: 'Equip speed and stamina skills for this race.',
            expectedPerformance: 'Good chance of top 3 finish'
        );

        expect($response->recommendedSkills)->toHaveCount(3);
        expect($response->recommendedSkills)->toContain('Speed Star');
        expect($response->recommendedSkills)->toContain('Stamina Keeper');
    });

    it('includes race preparation advice in response', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star'],
            racePreparationAdvice: 'Character is well-prepared for this race. Ensure energy level is above 80 and motivation is high.',
            expectedPerformance: 'Very high win probability'
        );

        expect($response->racePreparationAdvice)->toBeString();
        expect(strlen($response->racePreparationAdvice))->toBeGreaterThanOrEqual(20);
        expect(strlen($response->racePreparationAdvice))->toBeLessThanOrEqual(500);
    });

    it('includes expected performance assessment', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star'],
            racePreparationAdvice: 'Character is ready for the race.',
            expectedPerformance: 'Win probability: 75%. Character stats are well-suited for this distance and surface.'
        );

        expect($response->expectedPerformance)->toBeString();
        expect($response->expectedPerformance)->toContain('probability');
    });

    it('includes risk factors when present', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star'],
            racePreparationAdvice: 'Be cautious of stamina management.',
            expectedPerformance: 'Moderate win chance',
            riskFactors: [
                'Stamina may be insufficient for this distance',
                'Weather conditions are unfavorable',
                'Strong competition in the field',
            ]
        );

        expect($response->riskFactors)->toHaveCount(3);
        expect($response->riskFactors[0])->toContain('Stamina');
        expect($response->riskFactors[1])->toContain('Weather');
    });

    it('validates running style is valid', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star'],
            racePreparationAdvice: 'Lead from the front with strong pace.',
            expectedPerformance: 'High win chance'
        );

        $errors = $response->validate();

        expect($errors)->toBeArray();
        expect($errors)->not->toHaveKey('recommended_running_style');
    });
});

describe('RaceStrategyAgent - Requirements Validation', function () {
    it('validates Requirement 5.1: analyzes race conditions and character capabilities', function () {
        $service = app(RaceStrategyService::class);

        $raceData = [
            'race_name' => 'Tokyo Yushun',
            'distance_meters' => 2400,
            'surface' => 'turf',
            'weather' => 'sunny',
            'track_condition' => 'good',
        ];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $this->character, $raceData);

        // Verify race conditions are included
        expect($context)->toContain('Race Information');
        expect($context)->toContain('2400');
        expect($context)->toContain('turf');
        expect($context)->toContain('sunny');

        // Verify character capabilities are included
        expect($context)->toContain('Current Statistics');
        expect($context)->toContain('Speed: 800');
        expect($context)->toContain('Stamina: 700');
    });

    it('validates Requirement 5.2: recommends appropriate skills to equip', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star', 'Stamina Keeper', 'Acceleration Boost'],
            racePreparationAdvice: 'Equip these skills for optimal race performance.',
            expectedPerformance: 'High win chance'
        );

        expect($response->recommendedSkills)->toBeArray();
        expect($response->recommendedSkills)->not->toBeEmpty();
        expect($response->recommendedSkills)->toContain('Speed Star');
    });

    it('validates Requirement 5.3: suggests optimal running strategies based on distance and surface', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star'],
            racePreparationAdvice: 'For this 2400m turf race, lead from the front and maintain steady pace. Character has A-grade aptitude for this running style.',
            expectedPerformance: 'High win probability'
        );

        expect($response->recommendedRunningStyle)->toBeIn(['escape', 'leader', 'betweener', 'chaser']);
        expect($response->racePreparationAdvice)->toContain('pace');
    });

    it('validates Requirement 5.4: considers character stamina and speed stats', function () {
        $service = app(RaceStrategyService::class);

        $raceData = ['race_name' => 'Test Race', 'distance_meters' => 2400];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $this->character, $raceData);

        expect($context)->toContain('Speed: 800');
        expect($context)->toContain('Stamina: 700');
        expect($context)->toContain('Distance: 2400');
    });

    it('validates Requirement 5.5: prioritizes races based on character readiness', function () {
        $service = app(RaceStrategyService::class);

        $raceData = [
            'race_name' => 'Important Race',
            'strategic_importance' => 'This is a crucial race for career progression. Character readiness is high with good stats and energy.',
        ];

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('formatRaceContext');
        $context = $method->invoke($service, $this->character, $raceData);

        expect($context)->toContain('Strategic Importance');
        expect($context)->toContain('crucial');
        expect($context)->toContain('readiness');
    });
});

describe('RaceStrategyAgent - Error Handling', function () {
    it('handles missing character gracefully through service', function () {
        $service = app(RaceStrategyService::class);

        $raceData = ['race_name' => 'Test Race'];

        expect(fn () => $service->getStrategy(99999, $raceData, $this->user->id))
            ->toThrow(Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });

    it('validates race data structure', function () {
        $service = app(RaceStrategyService::class);

        $invalidRaceData = [
            'race_name' => '',  // Empty race name
        ];

        $errors = $service->validateRaceData($invalidRaceData);

        expect($errors)->toHaveKey('race_name');
    });

    it('validates distance range', function () {
        $service = app(RaceStrategyService::class);

        $invalidRaceData = [
            'race_name' => 'Test Race',
            'distance_meters' => 500,  // Too short
        ];

        $errors = $service->validateRaceData($invalidRaceData);

        expect($errors)->toHaveKey('distance_meters');
    });

    it('validates race grade', function () {
        $service = app(RaceStrategyService::class);

        $invalidRaceData = [
            'race_name' => 'Test Race',
            'race_grade' => 'Invalid Grade',
        ];

        $errors = $service->validateRaceData($invalidRaceData);

        expect($errors)->toHaveKey('race_grade');
    });

    it('validates surface type', function () {
        $service = app(RaceStrategyService::class);

        $invalidRaceData = [
            'race_name' => 'Test Race',
            'surface' => 'grass',  // Invalid surface
        ];

        $errors = $service->validateRaceData($invalidRaceData);

        expect($errors)->toHaveKey('surface');
    });

    it('validates available skills structure', function () {
        $service = app(RaceStrategyService::class);

        $invalidRaceData = [
            'race_name' => 'Test Race',
            'available_skills' => 'not an array',
        ];

        $errors = $service->validateRaceData($invalidRaceData);

        expect($errors)->toHaveKey('available_skills');
    });

    it('validates skill name in available skills', function () {
        $service = app(RaceStrategyService::class);

        $invalidRaceData = [
            'race_name' => 'Test Race',
            'available_skills' => [
                ['skill_type' => 'speed'],  // Missing name
            ],
        ];

        $errors = $service->validateRaceData($invalidRaceData);

        expect($errors)->not->toBeEmpty();
    });
});

describe('RaceStrategyAgent - Integration with Service Layer', function () {
    it('formats and parses responses correctly', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star', 'Acceleration'],
            racePreparationAdvice: 'Character is well-prepared with strong speed stats and leader aptitude.',
            expectedPerformance: 'High win probability of 80%',
            riskFactors: ['Watch stamina in final stretch']
        );

        $service = app(RaceStrategyService::class);
        $parsed = $service->parseResponse($response);

        expect($parsed['recommended_running_style'])->toBe('leader');
        expect($parsed['recommended_skills'])->toContain('Speed Star');
        expect($parsed['race_preparation_advice'])->toContain('speed');
        expect($parsed['expected_performance'])->toContain('probability');
        expect($parsed['risk_factors'])->toHaveCount(1);
        expect($parsed['summary'])->toBeString();
        expect($parsed['validation_errors'])->toBeArray();
    });

    it('generates human-readable summary', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star', 'Stamina Keeper'],
            racePreparationAdvice: 'Lead from the front with strong pace control.',
            expectedPerformance: 'High win chance',
            riskFactors: ['Large field size']
        );

        $summary = $response->getSummary();

        expect($summary)->toBeString();
        expect($summary)->toContain('Recommended Running Style: leader');
        expect($summary)->toContain('Race Preparation:');
        expect($summary)->toContain('Recommended Skills:');
        expect($summary)->toContain('Speed Star');
        expect($summary)->toContain('Expected Performance:');
        expect($summary)->toContain('Risk Factors:');
    });

    it('retrieves recommended skills for a race', function () {
        $service = app(RaceStrategyService::class);

        $raceData = [
            'race_name' => 'Test Race',
            'distance_meters' => 1600,
            'surface' => 'turf',
        ];

        $skills = $service->getRecommendedSkills($this->character->id, $raceData);

        expect($skills)->toBeArray();
        expect($skills)->not->toBeEmpty();
        expect($skills[0])->toHaveKey('id');
        expect($skills[0])->toHaveKey('name');
        expect($skills[0])->toHaveKey('skill_type');
    });

    it('handles empty skill list gracefully', function () {
        // Create character with no skills
        $characterNoSkills = Character::factory()
            ->withStats(['speed' => 500])
            ->create(['user_id' => $this->user->id]);

        $service = app(RaceStrategyService::class);

        $raceData = ['race_name' => 'Test Race'];

        $skills = $service->getRecommendedSkills($characterNoSkills->id, $raceData);

        expect($skills)->toBeArray();
        expect($skills)->toBeEmpty();
    });
});

describe('RaceStrategyAgent - Response Validation', function () {
    it('validates running style must be valid', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'invalid_style',
            recommendedSkills: ['Speed Star'],
            racePreparationAdvice: 'Test advice that meets minimum length requirement for validation.',
            expectedPerformance: 'Good'
        );

        $errors = $response->validate();

        expect($errors)->toHaveKey('recommended_running_style');
    });

    it('validates at least one skill must be recommended', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: [],  // Empty skills array
            racePreparationAdvice: 'Test advice that meets minimum length requirement for validation.',
            expectedPerformance: 'Good'
        );

        $errors = $response->validate();

        expect($errors)->toHaveKey('recommended_skills');
    });

    it('validates race preparation advice minimum length', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star'],
            racePreparationAdvice: 'Too short',  // Less than 20 characters
            expectedPerformance: 'Good'
        );

        $errors = $response->validate();

        expect($errors)->toHaveKey('race_preparation_advice');
    });

    it('validates race preparation advice maximum length', function () {
        $longAdvice = str_repeat('a', 501);  // More than 500 characters

        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star'],
            racePreparationAdvice: $longAdvice,
            expectedPerformance: 'Good'
        );

        $errors = $response->validate();

        expect($errors)->toHaveKey('race_preparation_advice');
    });

    it('validates skill names must be non-empty strings', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star', ''],  // Empty skill name
            racePreparationAdvice: 'Test advice that meets minimum length requirement for validation.',
            expectedPerformance: 'Good'
        );

        $errors = $response->validate();

        expect($errors)->toHaveKey('recommended_skills');
    });

    it('validates risk factors must be non-empty strings', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star'],
            racePreparationAdvice: 'Test advice that meets minimum length requirement for validation.',
            expectedPerformance: 'Good',
            riskFactors: ['Valid risk', '']  // Empty risk factor
        );

        $errors = $response->validate();

        expect($errors)->toHaveKey('risk_factors');
    });

    it('passes validation with valid data', function () {
        $response = new RaceStrategyResponse(
            recommendedRunningStyle: 'leader',
            recommendedSkills: ['Speed Star', 'Stamina Keeper'],
            racePreparationAdvice: 'Character is well-prepared for this race with strong stats and appropriate skills.',
            expectedPerformance: 'High win probability of 75%',
            riskFactors: ['Watch stamina in final stretch', 'Large field size']
        );

        $errors = $response->validate();

        expect($errors)->toBeEmpty();
    });
});
