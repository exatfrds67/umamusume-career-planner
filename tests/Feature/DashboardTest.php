<?php

use App\Models\Character;
use App\Models\User;

it('displays dashboard with empty state when no characters exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewIs('dashboard');
    $response->assertViewHas('hasCharacters', false);
    $response->assertSee('No Characters Yet');
    $response->assertSee('Create Your First Character');
});

it('displays dashboard with character data when characters exist', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Uma',
        'scenario_type' => 'ura_finale',
        'current_turn' => 24,
        'current_stats' => [
            'speed' => 800,
            'stamina' => 700,
            'power' => 650,
            'guts' => 600,
            'wit' => 550,
        ],
        'energy_level' => 75,
        'mood_status' => 'good',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewIs('dashboard');
    $response->assertViewHas('hasCharacters', true);
    $response->assertViewHas('selectedCharacter');
    $response->assertSee('Test Uma');
});

it('allows selecting a specific character via query parameter', function () {
    $user = User::factory()->create();

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'First Uma',
        'status' => 'active',
    ]);

    $character2 = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Second Uma',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard', ['character' => $character2->id]));

    $response->assertSuccessful();
    $response->assertViewHas('selectedCharacter', fn ($selected) => $selected->id === $character2->id);
});

it('displays correct metrics for selected character', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Metrics Test Uma',
        'current_turn' => 35,
        'current_stats' => [
            'speed' => 900,
            'stamina' => 850,
            'power' => 800,
            'guts' => 750,
            'wit' => 700,
        ],
        'goals' => [
            'target_grade' => 'S',
            'target_skills' => 15,
        ],
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('metrics', fn ($metrics) => $metrics['currentTurn'] === 35
        && $metrics['targetGrade'] === 'S'
        && $metrics['targetSkills'] === 15);
});

it('displays stats snapshot with correct values', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'current_stats' => [
            'speed' => 1000,
            'stamina' => 900,
            'power' => 800,
            'guts' => 700,
            'wit' => 600,
        ],
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('stats', fn ($stats) => $stats['speed'] === 1000
        && $stats['stamina'] === 900
        && $stats['power'] === 800
        && $stats['guts'] === 700
        && $stats['wit'] === 600);
});

it('displays mood and energy correctly', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'energy_level' => 65,
        'mood_status' => 'great',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('moodEnergy', fn ($moodEnergy) => $moodEnergy['mood'] === 'great'
        && $moodEnergy['energy'] === 65
        && $moodEnergy['maxEnergy'] === 100);
});

it('renders dashboard analytics from live character data instead of placeholder values', function () {
    $user = User::factory()->create();

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Analytics Test Uma',
        'current_turn' => 35,
        'current_stats' => [
            'speed' => 900,
            'stamina' => 850,
            'power' => 800,
            'guts' => 750,
            'wit' => 700,
        ],
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Turn 35');
    $response->assertDontSee('Turn 32');
    $response->assertDontSee('3,500 fans');
});

it('does not inject the debugbar into dashboard responses by default', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertDontSee('phpdebugbar', false);
});
