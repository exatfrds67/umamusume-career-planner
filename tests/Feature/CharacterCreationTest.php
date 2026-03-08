<?php

use App\Models\Character;
use App\Models\GameCharacter;
use App\Models\User;
use App\Services\ExternalAPI\UmapyoiApiClient;
use Illuminate\Support\Facades\File;

test('character creation page can be accessed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('characters.create'));

    $response->assertSuccessful();
    $response->assertViewIs('characters.create');
});

test('character creation page exposes normalized trainee avatar urls', function () {
    $user = User::factory()->create();
    $gameCharacter = GameCharacter::factory()->create([
        'name_en' => 'Special Week',
        'image_path' => 'images/trainee_images/special-week.png',
    ]);

    $response = $this->actingAs($user)->get(route('characters.create'));

    $response->assertSuccessful();
    $response->assertViewHas('trainees', function (array $trainees) use ($gameCharacter): bool {
        $trainee = collect($trainees)->firstWhere('id', $gameCharacter->id);

        return is_array($trainee)
            && ($trainee['avatar_url'] ?? null) === '/images/trainee_images/special-week.png'
            && ($trainee['image'] ?? null) === '/images/trainee_images/special-week.png'
            && ($trainee['image_path'] ?? null) === 'images/trainee_images/special-week.png';
    });
});

test('character creation page derives trainee avatar urls from local image files when image_path is missing', function () {
    $user = User::factory()->create();
    $gameCharacter = GameCharacter::factory()->create([
        'name_en' => 'Vodka',
        'image_path' => null,
    ]);

    $imageDirectory = public_path('images/trainee_images');
    File::ensureDirectoryExists($imageDirectory);
    $imageFile = $imageDirectory.DIRECTORY_SEPARATOR.'__vodka_umamusume_test_fixture.png';
    File::put($imageFile, 'fixture');

    try {
        $response = $this->actingAs($user)->get(route('characters.create'));

        $response->assertSuccessful();
        $response->assertViewHas('trainees', function (array $trainees) use ($gameCharacter): bool {
            $trainee = collect($trainees)->firstWhere('id', $gameCharacter->id);

            return is_array($trainee)
                && ($trainee['avatar_url'] ?? null) === '/images/trainee_images/__vodka_umamusume_test_fixture.png'
                && ($trainee['image'] ?? null) === '/images/trainee_images/__vodka_umamusume_test_fixture.png';
        });
    } finally {
        File::delete($imageFile);
    }
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

    $character = Character::where('name', '=', 'Test Character', 'and')->first(['*']);
    expect($character)->not->toBeNull();
    expect($character->getStat('speed'))->toBe(500);
    expect($character->getStat('stamina'))->toBe(400);
    expect($character->aptitudes)->toHaveCount(10); // 4 distance + 2 surface + 4 style
});

test('character can be created with template avatar url', function () {
    $user = User::factory()->create();
    $gameCharacter = GameCharacter::factory()->create([
        'name_en' => 'Silence Suzuka',
        'image_path' => '/images/trainee_images/silence-suzuka.png',
    ]);

    $response = $this->actingAs($user)->post(route('characters.store'), [
        'trainee_id' => $gameCharacter->id,
        'name' => 'Silence Suzuka',
        'avatar_url' => '/images/trainee_images/silence-suzuka.png',
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
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('ucp_characters', [
        'name' => 'Silence Suzuka',
        'avatar_url' => '/images/trainee_images/silence-suzuka.png',
        'game_character_id' => $gameCharacter->id,
    ]);
});

test('character prefill endpoint exposes normalized avatar url fields', function () {
    $user = User::factory()->create();
    $client = \Mockery::mock(UmapyoiApiClient::class);
    $client->shouldReceive('getCharacter')
        ->once()
        ->with('123')
        ->andReturn([
            'success' => true,
            'source' => 'umapyoi.net',
            'data' => [
                'id' => 123,
                'name_en' => 'Special Week',
                'name_jp' => 'スペシャルウィーク',
                'thumb_img' => 'https://example.com/special-week.png',
                'category_label_en' => 'Runner',
                'color_main' => '#3B82F6',
                'base_stats' => [
                    'speed' => 100,
                    'stamina' => 90,
                    'power' => 95,
                    'guts' => 80,
                    'wisdom' => 85,
                ],
                'aptitudes' => [
                    'turf_short' => 'A',
                    'turf_mile' => 'A',
                    'turf_medium' => 'B',
                    'turf_long' => 'C',
                    'dirt_short' => 'G',
                    'dirt_mile' => 'G',
                    'dirt_medium' => 'G',
                    'dirt_long' => 'G',
                    'runner' => 'A',
                    'leader' => 'B',
                    'betweener' => 'C',
                    'chaser' => 'D',
                ],
            ],
        ]);
    $this->app->instance(UmapyoiApiClient::class, $client);

    $response = $this->actingAs($user)->getJson('/api/characters/prefill/123');

    $response->assertSuccessful();
    $response->assertJsonPath('data.avatar_url', 'https://example.com/special-week.png');
    $response->assertJsonPath('data.image', 'https://example.com/special-week.png');
    $response->assertJsonPath('data.image_url', 'https://example.com/special-week.png');
});

test('character name is required', function () {
    $user = User::factory()->create();

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

    $response = $this->actingAs($user)->post(route('characters.store'), $userData);

    $response->assertSessionHasErrors('name');
});

test('scenario type must be valid', function () {
    $user = User::factory()->create();

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

    $response = $this->actingAs($user)->post(route('characters.store'), $userData);

    $response->assertSessionHasErrors('scenario_type');
});

test('stats must be within valid range', function () {
    $user = User::factory()->create();

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

    $response = $this->actingAs($user)->post(route('characters.store'), $userData);

    $response->assertSessionHasErrors('stats.speed');
});

test('aptitude grades must be valid', function () {
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

    $response = $this->actingAs($user)->post(route('characters.store'), $userData);

    $response->assertSessionHasErrors('aptitudes.distance.sprint');
});

test('all required aptitudes must be provided', function () {
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

    $response = $this->actingAs($user)->post(route('characters.store'), $userData);

    $response->assertSessionHasErrors(['aptitudes.distance.medium', 'aptitudes.distance.long']);
});

test('character can be created with title', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('characters.store'), [
        'name' => 'Special Week',
        'title' => 'Special Dreamer',
        'scenario_type' => 'ura_finale',
        'stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 350,
        ],
        'aptitudes' => [
            'distance' => ['sprint' => 'A', 'mile' => 'B', 'medium' => 'C', 'long' => 'D'],
            'surface' => ['turf' => 'A', 'dirt' => 'B'],
            'style' => ['front_runner' => 'A', 'pace_chaser' => 'B', 'late_surger' => 'C', 'end_closer' => 'D'],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('ucp_characters', [
        'name' => 'Special Week',
        'title' => 'Special Dreamer',
    ]);
});

test('character can be created with image transform fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('characters.store'), [
        'name' => 'Vodka',
        'avatar_url' => '/images/trainee_images/vodka.jpg',
        'image_x' => 10.5,
        'image_y' => -5.2,
        'image_zoom' => 1.5,
        'image_rotation' => 90,
        'image_flip_h' => '1',
        'scenario_type' => 'ura_finale',
        'stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 350,
        ],
        'aptitudes' => [
            'distance' => ['sprint' => 'A', 'mile' => 'B', 'medium' => 'C', 'long' => 'D'],
            'surface' => ['turf' => 'A', 'dirt' => 'B'],
            'style' => ['front_runner' => 'A', 'pace_chaser' => 'B', 'late_surger' => 'C', 'end_closer' => 'D'],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $character = Character::where('name', '=', 'Vodka', 'and')->first(['*']);
    expect($character)->not->toBeNull();
    expect($character->image_x)->toBe(10.5);
    expect($character->image_y)->toBe(-5.2);
    expect($character->image_zoom)->toBe(1.5);
    expect($character->image_rotation)->toBe(90);
    expect($character->image_flip_h)->toBeTrue();
});
