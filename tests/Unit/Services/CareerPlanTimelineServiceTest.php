<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\Race;
use App\Models\Skill;
use App\Services\CareerPlanTimelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(CareerPlanTimelineService::class);
});

it('uses training predictions to target the largest stat gap', function () {
    $character = Character::factory()->create([
        'current_stats' => [
            'speed' => 320,
            'stamina' => 140,
            'power' => 280,
            'guts' => 240,
            'wit' => 260,
        ],
        'available_sp' => 80,
        'energy_level' => 78,
        'goals' => [
            'target_stats' => [
                'stamina' => 500,
                'speed' => 420,
            ],
        ],
    ]);

    $plan = $this->service->build($character, 'Win the finale');
    $firstAction = $plan['timeline'][0]['action'];

    expect($firstAction['type'])->toBe('training')
        ->and($firstAction['facility'])->toBe('stamina')
        ->and($firstAction['expected_gains']['stamina'])->toBeGreaterThan(0)
        ->and($firstAction['expected_sp_gain'])->toBeGreaterThanOrEqual(3)
        ->and($firstAction['reasoning'])->toContain('goal');
});

it('uses real skill recommendations instead of placeholder skill ids', function () {
    $skill = Skill::factory()->create([
        'name' => 'Corner Adept',
        'base_sp_cost' => 120,
        'meta_tier' => 'S',
    ]);

    $character = Character::factory()->create([
        'current_stats' => [
            'speed' => 260,
            'stamina' => 250,
            'power' => 240,
            'guts' => 220,
            'wit' => 230,
        ],
        'available_sp' => 150,
        'energy_level' => 70,
        'goals' => [],
    ]);

    $plan = $this->service->build($character, 'Stabilize the mid-game');
    $skillStep = collect($plan['timeline'])->firstWhere('action.type', 'skill');

    expect($skillStep)->not->toBeNull()
        ->and($skillStep['action']['skill_id'])->toBe($skill->id)
        ->and($skillStep['action']['skill_name'])->toBe('Corner Adept')
        ->and($skillStep['action']['discounted_cost'])->toBe(120);
});

it('uses recorded races when the character already has a planned race turn', function () {
    $character = Character::factory()->create([
        'current_stats' => [
            'speed' => 500,
            'stamina' => 420,
            'power' => 380,
            'guts' => 240,
            'wit' => 300,
        ],
        'available_sp' => 60,
        'energy_level' => 84,
    ]);

    $race = Race::factory()->create([
        'character_id' => $character->id,
        'turn_number' => 1,
        'surface' => 'turf',
        'track_condition' => 'good',
        'distance_category' => 'mile',
        'sp_reward' => 35,
        'speed_at_race' => 450,
        'stamina_at_race' => 360,
        'power_at_race' => 320,
        'guts_at_race' => 220,
        'wit_at_race' => 260,
    ]);

    $plan = $this->service->build($character, 'Hit the opening race on curve');
    $firstAction = $plan['timeline'][0]['action'];

    expect($firstAction['type'])->toBe('race')
        ->and($firstAction['race_id'])->toBe($race->id)
        ->and($firstAction['sp_reward'])->toBe(35)
        ->and($firstAction['expected_result'])->toContain('win_probability');
});
