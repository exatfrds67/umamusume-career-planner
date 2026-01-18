<?php

/**
 * @property App\Services\MCP\MCPClientService&Mockery\MockInterface $mcpClient
 * @property App\Services\AI\Agents\TrainingOptimizationAgent $agent
 * @property App\Models\Character $character
 */

use App\Models\Character;
use App\Services\AI\Agents\TrainingOptimizationAgent;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    $this->mcpClient = Mockery::mock(MCPClientService::class);
    $this->agent = new TrainingOptimizationAgent($this->mcpClient);
    $this->character = Character::factory()->create([
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 450,
            'guts' => 300,
            'wit' => 350,
        ],
        'energy_level' => 80,
        'mood_status' => 'good',
    ]);
});

afterEach(function () {
    Mockery::close();
});

it('analyzes training options successfully', function () {
    Config::set('ai.agents.training_optimization.enabled', true);

    $this->mcpClient->shouldReceive('isStrandsAgentsAvailable')
        ->once()
        ->andReturn(true);

    $trainingOptions = [
        ['type' => 'speed', 'participants' => 2],
        ['type' => 'stamina', 'participants' => 3],
    ];

    $result = $this->agent->analyzeTrainingOptions(
        $this->character,
        $trainingOptions,
        ['target_speed' => 800]
    );

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['recommendations', 'analysis', 'confidence', 'reasoning', 'metadata'])
        ->and($result['metadata'])->toHaveKey('agent_id')
        ->and($result['metadata']['character_id'])->toBe($this->character->id);
});

it('returns default recommendations when MCP is unavailable', function () {
    Config::set('ai.agents.training_optimization.enabled', true);

    $this->mcpClient->shouldReceive('isStrandsAgentsAvailable')
        ->once()
        ->andReturn(false);

    $trainingOptions = [
        ['type' => 'speed', 'participants' => 2],
    ];

    $result = $this->agent->analyzeTrainingOptions(
        $this->character,
        $trainingOptions
    );

    expect($result)->toBeArray()
        ->and($result['metadata']['fallback'])->toBeTrue()
        ->and($result['confidence'])->toBe(0.5);
});

it('predicts stat gains for training option', function () {
    Config::set('ai.agents.training_optimization.enabled', true);

    $this->mcpClient->shouldReceive('isStrandsAgentsAvailable')
        ->once()
        ->andReturn(true);

    $trainingOption = ['type' => 'speed', 'participants' => 2];

    $result = $this->agent->predictStatGains($this->character, $trainingOption);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['stat_gains', 'energy_cost', 'failure_risk', 'confidence'])
        ->and($result['stat_gains'])->toBeArray();
});

it('optimizes training sequence for multiple turns', function () {
    Config::set('ai.agents.training_optimization.enabled', true);

    $this->mcpClient->shouldReceive('isStrandsAgentsAvailable')
        ->once()
        ->andReturn(true);

    $result = $this->agent->optimizeTrainingSequence(
        $this->character,
        5,
        ['target_speed' => 800]
    );

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['sequence', 'expected_outcomes', 'confidence', 'reasoning']);
});

it('caches recommendations', function () {
    Config::set('ai.agents.training_optimization.enabled', true);

    $this->mcpClient->shouldReceive('isStrandsAgentsAvailable')
        ->once()
        ->andReturn(true);

    $trainingOptions = [['type' => 'speed']];

    // First call - should process
    $result1 = $this->agent->analyzeTrainingOptions($this->character, $trainingOptions);

    // Second call - should use cache (MCP client not called again)
    $result2 = $this->agent->analyzeTrainingOptions($this->character, $trainingOptions);

    expect($result1)->toEqual($result2);
});

it('returns correct status', function () {
    Config::set('ai.agents.training_optimization.enabled', true);

    $this->mcpClient->shouldReceive('isStrandsAgentsAvailable')
        ->once()
        ->andReturn(true);

    $status = $this->agent->getStatus();

    expect($status)->toBeArray()
        ->and($status)->toHaveKeys(['enabled', 'available', 'agent_id', 'mcp_server'])
        ->and($status['enabled'])->toBeTrue()
        ->and($status['mcp_server'])->toBe('strands-agents');
});

it('checks availability correctly', function () {
    Config::set('ai.agents.training_optimization.enabled', true);

    $this->mcpClient->shouldReceive('isStrandsAgentsAvailable')
        ->once()
        ->andReturn(true);

    expect($this->agent->isAvailable())->toBeTrue();
});

it('handles disabled agent gracefully', function () {
    Config::set('ai.agents.training_optimization.enabled', false);

    $trainingOptions = [['type' => 'speed']];

    $result = $this->agent->analyzeTrainingOptions($this->character, $trainingOptions);

    expect($result)->toBeArray()
        ->and($result['metadata']['fallback'])->toBeTrue();
});
