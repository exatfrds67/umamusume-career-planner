<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\User;
use App\Neuron\Agents\CareerPlanningAgent;
use App\Neuron\Agents\Tools\CharacterStatsTool;
use App\Neuron\Agents\Tools\RaceDataTool;
use NeuronAI\Providers\AIProviderInterface;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->character = Character::factory()
        ->withStats([
            'speed' => 450,
            'stamina' => 350,
            'power' => 300,
            'guts' => 250,
            'wit' => 280,
        ])
        ->create([
            'user_id' => $this->user->id,
            'name' => 'Career Planner Test',
            'scenario_type' => 'ura_finale',
            'career_stage' => 'classic',
            'current_turn' => 24,
        ]);

    $this->career = Career::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'status' => 'active',
        'current_turn' => 24,
        'current_phase' => 'classic',
    ]);
});

it('can be instantiated with user and character', function () {
    $agent = new CareerPlanningAgent($this->user->id, $this->character->id, $this->career->id);

    expect($agent)->toBeInstanceOf(CareerPlanningAgent::class);
});

it('has proper system instructions', function () {
    $agent = new CareerPlanningAgent($this->user->id);
    $instructions = $agent->instructions();

    expect($instructions)->toBeString();
    expect($instructions)->toContain('career');
    expect($instructions)->toContain('milestone');
});

it('uses Anthropic provider', function () {
    $agent = new CareerPlanningAgent($this->user->id);

    $reflection = new ReflectionClass($agent);
    $method = $reflection->getMethod('provider');
    $method->setAccessible(true);
    $provider = $method->invoke($agent);

    expect($provider)->toBeInstanceOf(AIProviderInterface::class);
});

it('generates unique thread IDs across characters and careers', function () {
    $agent1 = new CareerPlanningAgent($this->user->id, $this->character->id, $this->career->id);
    $agent2 = new CareerPlanningAgent($this->user->id, $this->character->id + 1, $this->career->id + 1);

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

it('registers core tools', function () {
    $agent = new CareerPlanningAgent($this->user->id);

    $reflection = new ReflectionClass($agent);
    $method = $reflection->getMethod('tools');
    $method->setAccessible(true);
    $tools = $method->invoke($agent);

    $toolClasses = array_map(function ($tool) {
        return is_object($tool) ? get_class($tool) : null;
    }, is_array($tools) ? $tools : []);

    expect($toolClasses)->toContain(CharacterStatsTool::class);
    expect($toolClasses)->toContain(RaceDataTool::class);
});
