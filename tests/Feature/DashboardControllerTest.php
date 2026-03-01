<?php

use App\Models\Career;
use App\Models\Character;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('dashboard loads successfully with no characters', function () {
    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewIs('dashboard');
    $response->assertViewHas('hasCharacters', false);
});

test('dashboard loads successfully with character', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewIs('dashboard');
    $response->assertViewHas('hasCharacters', true);
    $response->assertViewHas('selectedCharacter');
});

test('dashboard handles character with string race_schedule', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'race_schedule' => json_encode([
            ['name' => 'Test Race', 'turn' => 10, 'grade' => 'G1'],
        ]),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard handles character with invalid race_schedule', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'race_schedule' => 'invalid json string',
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard handles character with null race_schedule', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'race_schedule' => null,
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard handles character with string current_stats', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => json_encode([
            'speed' => 500,
            'stamina' => 400,
            'power' => 400,
            'guts' => 300,
            'wit' => 300,
        ]),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard handles character with string goals', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'goals' => json_encode([
            'target_grade' => 'A+',
            'target_skills' => 12,
        ]),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
});

test('metrics skill count falls back to career metadata when no skill acquisition records', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'available_sp' => 50,
    ]);

    Career::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'career_metadata' => [
            'skills' => [
                ['name' => 'Speed Boost', 'acquired' => true, 'sp_cost' => 120],
                ['name' => 'Power Rush', 'acquired' => true, 'sp_cost' => 90],
                ['name' => 'Stamina Save', 'acquired' => false, 'sp_cost' => 150],
                ['name' => 'Final Sprint', 'acquired' => false, 'sp_cost' => 200],
            ],
        ],
    ]);

    $response = $this->get(route('dashboard', ['character' => $character->id]));

    $response->assertSuccessful();
    $metrics = $response->viewData('metrics');
    expect($metrics['skillsAcquired'])->toBe(2)
        ->and($metrics['targetSkills'])->toBe(4);
});

test('metrics uses character available_sp as sp left', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'available_sp' => 174,
    ]);

    $response = $this->get(route('dashboard', ['character' => $character->id]));

    $response->assertSuccessful();
    $metrics = $response->viewData('metrics');
    expect($metrics['skillPoints'])->toBe(174);
});

test('metrics shows zero skills acquired when no career metadata and no skill acquisitions', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'available_sp' => 0,
    ]);

    $response = $this->get(route('dashboard', ['character' => $character->id]));

    $response->assertSuccessful();
    $metrics = $response->viewData('metrics');
    expect($metrics['skillsAcquired'])->toBe(0)
        ->and($metrics['skillPoints'])->toBe(0);
});

test('upcoming races are derived from career_metadata when race_schedule is empty', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'race_schedule' => null,
    ]);

    Career::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'status' => 'planning',
        'career_metadata' => [
            'race_name' => 'Tenno Sho (Spring)',
            'race_day' => false,
            'turn_before_race' => 8,
        ],
    ]);

    $response = $this->get(route('dashboard', ['character' => $character->id]));

    $response->assertSuccessful();
    $races = $response->viewData('races');
    expect($races)->toHaveCount(1)
        ->and($races[0]['name'])->toBe('Tenno Sho (Spring)')
        ->and($races[0]['grade'])->toBe('G1')
        ->and($races[0]['turnsAway'])->toBe(8);
});

test('upcoming races shows race day event when race_day is true', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'race_schedule' => null,
    ]);

    Career::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'status' => 'planning',
        'career_metadata' => [
            'race_name' => 'JBC SPRINT',
            'race_day' => true,
            'strategy' => 'LATE',
        ],
    ]);

    $response = $this->get(route('dashboard', ['character' => $character->id]));

    $response->assertSuccessful();
    $races = $response->viewData('races');
    expect($races)->toHaveCount(1)
        ->and($races[0]['name'])->toBe('JBC SPRINT')
        ->and($races[0]['turnsAway'])->toBe(0);
});

test('upcoming races are empty for completed careers with no schedule', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'race_schedule' => null,
    ]);

    Career::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'status' => 'completed',
        'career_metadata' => [
            'race_name' => 'URA Finale Finals',
            'race_day' => false,
        ],
    ]);

    $response = $this->get(route('dashboard', ['character' => $character->id]));

    $response->assertSuccessful();
    $races = $response->viewData('races');
    expect($races)->toBeEmpty();
});

test('stat progression data is returned with labels and data points', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_turn' => 30,
        'current_stats' => ['speed' => 400, 'stamina' => 300, 'power' => 350, 'guts' => 280, 'wit' => 270],
    ]);

    $response = $this->get(route('dashboard', ['character' => $character->id]));

    $response->assertSuccessful();
    expect($response->viewData('statProgression'))->not->toBeNull()
        ->and($response->viewData('progressionLabels'))->not->toBeNull()
        ->and($response->viewData('statProgression'))->toBeArray()
        ->and($response->viewData('progressionLabels'))->toHaveCount(6);
});

test('race grades are derived from class rank in career_metadata', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
    ]);

    Career::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'career_metadata' => ['class_rank' => 'platinum'],
    ]);

    $response = $this->get(route('dashboard', ['character' => $character->id]));

    $response->assertSuccessful();
    $grades = $response->viewData('raceGrades');
    expect($grades)->not->toBeEmpty();
    $gradeLabels = array_column($grades, 'grade');
    expect($gradeLabels)->toContain('G1');
});

test('recent activity includes skill and milestone events from career_metadata', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
    ]);

    Career::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $character->id,
        'career_metadata' => [
            'original_plan_title' => 'Test Plan',
            'race_day' => false,
            'skills' => [
                ['name' => 'Speed Boost', 'acquired' => true, 'sp_cost' => null],
                ['name' => 'Final Sprint', 'acquired' => false, 'sp_cost' => 180],
            ],
        ],
    ]);

    $response = $this->get(route('dashboard', ['character' => $character->id]));

    $response->assertSuccessful();
    $activity = $response->viewData('recentActivity');
    expect($activity)->not->toBeEmpty();
    $types = array_column($activity, 'type');
    expect($types)->toContain('skill')
        ->and($types)->toContain('milestone');
});
