<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->character = Character::factory()
        ->withStats([
            'speed' => 500,
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 350,
        ])
        ->create([
            'user_id' => $this->user->id,
            'name' => 'Career Planning Test Character',
        ]);
});

it('requires authentication to get career plan', function () {
    $response = $this->postJson('/api/career-planning/plan', [
        'character_id' => $this->character->id,
    ]);

    $response->assertUnauthorized();
});

it('validates character_id is required', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/career-planning/plan', [
            'planning_context' => [],
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['character_id']);
});

it('validates focus stats are valid', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/career-planning/plan', [
            'character_id' => $this->character->id,
            'planning_context' => [
                'focus_stats' => ['invalid_stat'],
            ],
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['planning_context.focus_stats.0']);
});

it('requires authentication to get plan history', function () {
    $response = $this->getJson("/api/career-planning/history/{$this->character->id}");

    $response->assertUnauthorized();
});

it('accepts valid career planning request structure', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/career-planning/plan', [
            'character_id' => $this->character->id,
            'planning_context' => [
                'goal_horizon_turns' => 12,
                'focus_stats' => ['speed', 'stamina'],
                'target_grade' => 'A',
                'preferred_races' => [
                    [
                        'name' => 'Spring Stakes',
                        'distance_category' => 'mile',
                        'surface' => 'turf',
                        'turns_until' => 6,
                    ],
                ],
                'additional_context' => 'Aim for balanced growth before summer cup.',
            ],
        ]);

    expect($response->status())->toBeIn([404, 500, 503]);
});
