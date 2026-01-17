<?php

use App\Models\Character;
use App\Models\User;

test('character creation page can be accessed', function () {
    $response = $this->get(route('characters.create'));

    $response->assertSuccessful();
    $response->assertViewIs('characters.create');
});

test('character can be created with valid data', function () {
    // Create a user first to satisfy foreign key constraint
    $user = User::factory()->create();

    $userData = [
        'name' => 'Test Character',
        'scenario_type' => 'ura_finale',
        'stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 350,
        ],
        'aptitudes' => [
            'distance' => [
                'sprint' => 'A',
                'mile' => 'B',
                'medium' => 'C',
                'long' => 'D',
            ],
            'surface' => [
                'turf' => 'A',
                'dirt' => 'B',
            ],
            'style' => [
                'front_runner' => 'A',
                'pace_chaser' => 'B',
                'late_surger' => 'C',
                'end_closer' => 'D',
            ],
        ],
    ];

    $response = $this->actingAs($user)->post(route('characters.store'), $userData);

    $response->assertSessionHasNoErrors();
    $response->assertSessionMissing('error');
    $response->assertRedirect();

    $this->assertDatabaseHas('ucp_characters', [
        'name' => 'Test Character',
        'scenario_type' => 'ura_finale',
    ]);

    $character = Character::where('name', 'Test Character')->first();
    expect($character)->not->toBeNull();
    expect($character->getStat('speed'))->toBe(500);
    expect($character->getStat('stamina'))->toBe(400);
    expect($character->aptitudes)->toHaveCount(10); // 4 distance + 2 surface + 4 style
});

test('character name is required', function () {
    $userData = [
        'name' => '',
        'scenario_type' => 'ura_finale',
        'stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 350,
        ],
        'aptitudes' => [
            'distance' => [
                'sprint' => 'A',
                'mile' => 'B',
                'medium' => 'C',
                'long' => 'D',
            ],
            'surface' => [
                'turf' => 'A',
                'dirt' => 'B',
            ],
            'style' => [
                'front_runner' => 'A',
                'pace_chaser' => 'B',
                'late_surger' => 'C',
                'end_closer' => 'D',
            ],
        ],
    ];

    $response = $this->post(route('characters.store'), $userData);

    $response->assertSessionHasErrors('name');
});

test('scenario type must be valid', function () {
    $userData = [
        'name' => 'Test Character',
        'scenario_type' => 'invalid_scenario',
        'stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 350,
        ],
        'aptitudes' => [
            'distance' => [
                'sprint' => 'A',
                'mile' => 'B',
                'medium' => 'C',
                'long' => 'D',
            ],
            'surface' => [
                'turf' => 'A',
                'dirt' => 'B',
            ],
            'style' => [
                'front_runner' => 'A',
                'pace_chaser' => 'B',
                'late_surger' => 'C',
                'end_closer' => 'D',
            ],
        ],
    ];

    $response = $this->post(route('characters.store'), $userData);

    $response->assertSessionHasErrors('scenario_type');
});

test('stats must be within valid range', function () {
    $userData = [
        'name' => 'Test Character',
        'scenario_type' => 'ura_finale',
        'stats' => [
            'speed' => 1500, // Invalid: exceeds 1200
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 350,
        ],
        'aptitudes' => [
            'distance' => [
                'sprint' => 'A',
                'mile' => 'B',
                'medium' => 'C',
                'long' => 'D',
            ],
            'surface' => [
                'turf' => 'A',
                'dirt' => 'B',
            ],
            'style' => [
                'front_runner' => 'A',
                'pace_chaser' => 'B',
                'late_surger' => 'C',
                'end_closer' => 'D',
            ],
        ],
    ];

    $response = $this->post(route('characters.store'), $userData);

    $response->assertSessionHasErrors('stats.speed');
});

test('aptitude grades must be valid', function () {
    $userData = [
        'name' => 'Test Character',
        'scenario_type' => 'ura_finale',
        'stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 350,
        ],
        'aptitudes' => [
            'distance' => [
                'sprint' => 'Z', // Invalid grade
                'mile' => 'B',
                'medium' => 'C',
                'long' => 'D',
            ],
            'surface' => [
                'turf' => 'A',
                'dirt' => 'B',
            ],
            'style' => [
                'front_runner' => 'A',
                'pace_chaser' => 'B',
                'late_surger' => 'C',
                'end_closer' => 'D',
            ],
        ],
    ];

    $response = $this->post(route('characters.store'), $userData);

    $response->assertSessionHasErrors('aptitudes.distance.sprint');
});

test('all required aptitudes must be provided', function () {
    $userData = [
        'name' => 'Test Character',
        'scenario_type' => 'ura_finale',
        'stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 350,
        ],
        'aptitudes' => [
            'distance' => [
                'sprint' => 'A',
                'mile' => 'B',
                // Missing medium and long
            ],
            'surface' => [
                'turf' => 'A',
                'dirt' => 'B',
            ],
            'style' => [
                'front_runner' => 'A',
                'pace_chaser' => 'B',
                'late_surger' => 'C',
                'end_closer' => 'D',
            ],
        ],
    ];

    $response = $this->post(route('characters.store'), $userData);

    $response->assertSessionHasErrors(['aptitudes.distance.medium', 'aptitudes.distance.long']);
});
