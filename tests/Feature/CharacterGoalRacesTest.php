<?php

use App\Models\Character;
use App\Models\GameCharacter;
use App\Models\GameRace;
use App\Models\User;

it('passes goal races data to character index view when character is linked', function () {
    $user = User::factory()->create();

    $gameCharacter = GameCharacter::factory()->create(['name_en' => 'Test Uma']);
    $goalRace = GameRace::factory()->create([
        'name_en' => 'Sprinters Stakes',
        'grade' => 'G1',
        'distance_meters' => 1200,
        'distance_category' => 'sprint',
    ]);

    $gameCharacter->targetRaces()->attach($goalRace->id, [
        'race_type' => 'goal',
        'priority' => 1,
        'notes' => 'Sprint champion',
    ]);

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Uma',
        'game_character_id' => $gameCharacter->id,
    ]);

    $response = $this->actingAs($user)->get(route('characters.index'));

    $response->assertOk();

    $characters = $response->viewData('characters');
    expect($characters)->not->toBeNull();

    $firstCharacter = $characters->items()[0] ?? null;
    expect($firstCharacter)->toBeArray();
    expect(data_get($firstCharacter, 'next_goal.name'))->toBe('Sprinters Stakes');
});

it('shows goal races section on character show page', function () {
    $user = User::factory()->create();

    $gameCharacter = GameCharacter::factory()->create(['name_en' => 'Curren Chan']);
    $goalRace1 = GameRace::factory()->create([
        'name_en' => 'Sprinters Stakes',
        'grade' => 'G1',
        'distance_meters' => 1200,
        'distance_category' => 'sprint',
        'venue' => 'Nakayama',
    ]);
    $goalRace2 = GameRace::factory()->create([
        'name_en' => 'Takamatsunomiya Kinen',
        'grade' => 'G1',
        'distance_meters' => 1200,
        'distance_category' => 'sprint',
        'venue' => 'Chukyo',
    ]);

    $gameCharacter->targetRaces()->attach($goalRace1->id, [
        'race_type' => 'goal',
        'priority' => 1,
        'notes' => 'Sprint G1',
    ]);
    $gameCharacter->targetRaces()->attach($goalRace2->id, [
        'race_type' => 'goal',
        'priority' => 2,
        'notes' => null,
    ]);

    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Curren Chan',
        'game_character_id' => $gameCharacter->id,
    ]);

    $response = $this->actingAs($user)->get(route('characters.show', $character));

    $response->assertOk();
    $response->assertSee('Goal Races');
    $response->assertSee('Sprinters Stakes');
    $response->assertSee('Takamatsunomiya Kinen');
    $response->assertSee('1200m');
    $response->assertSee('Nakayama');
    $response->assertSee('Sprint G1');
});

it('does not show goal races section when character has no game character linked', function () {
    $user = User::factory()->create();

    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Unlinked Character',
        'game_character_id' => null,
    ]);

    $response = $this->actingAs($user)->get(route('characters.show', $character));

    $response->assertOk();
    $response->assertDontSee('Goal Races');
});

it('deduplicates goal races on character index rendering', function () {
    $user = User::factory()->create();

    $gameCharacter = GameCharacter::factory()->create(['name_en' => 'Speed Star']);
    $goalRaceA = GameRace::factory()->create([
        'name_en' => 'Japanese Derby',
        'grade' => 'G1',
        'distance_meters' => 2400,
        'distance_category' => 'long',
    ]);
    $goalRaceB = GameRace::factory()->create([
        'name_en' => 'Japanese Derby',
        'grade' => 'G1',
        'distance_meters' => 2400,
        'distance_category' => 'long',
    ]);

    $gameCharacter->targetRaces()->attach($goalRaceA->id, [
        'race_type' => 'goal',
        'priority' => 1,
        'notes' => null,
    ]);
    $gameCharacter->targetRaces()->attach($goalRaceB->id, [
        'race_type' => 'goal',
        'priority' => 2,
        'notes' => null,
    ]);

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Speed Star',
        'game_character_id' => $gameCharacter->id,
    ]);

    $response = $this->actingAs($user)->get(route('characters.index'));

    $response->assertOk();

    $characters = $response->viewData('characters');
    expect($characters)->not->toBeNull();

    $firstCharacter = $characters->items()[0] ?? null;
    expect($firstCharacter)->toBeArray();
    expect(data_get($firstCharacter, 'goal_races'))->toHaveCount(1)
        ->and(data_get($firstCharacter, 'goal_races.0.name'))->toBe('Japanese Derby');
});

it('paginates character index with default 50 per page', function () {
    $user = User::factory()->create();

    Character::factory()->count(55)->create([
        'user_id' => $user->id,
        'is_seeded' => false,
    ]);

    $response = $this->actingAs($user)->get(route('characters.index'));

    $response->assertOk();
    $response->assertSee('Showing 50 of 55 characters');
    $response->assertSee('page=2', false);
});

it('groups duplicate variants under a single parent card', function () {
    $user = User::factory()->create();

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Haru Urara',
        'scenario_type' => 'ura_finale',
    ]);

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Haru Urara',
        'scenario_type' => 'unity_cup',
    ]);

    $response = $this->actingAs($user)->get(route('characters.index'));

    $response->assertOk();
    $response->assertSee('Showing 1 of 1 characters');
    $response->assertSee('Versions');
});

it('renders compare and gallery controls with compare scan cues on index', function () {
    $user = User::factory()->create();

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Compare Mode Runner',
        'scenario_type' => 'ura_finale',
    ]);

    $response = $this->actingAs($user)->get(route('characters.index'));

    $response->assertOk();
    $response->assertSee('data-testid="compare-view-toggle"', false);
    $response->assertSee('data-testid="gallery-view-toggle"', false);
    $response->assertSee('data-testid="compare-view-list"', false);
    $response->assertSee('Core Stats');
    $response->assertSee('Compare View is optimized for fast stat scan.');
});

it('includes a grouped parent when searching for a variant name', function () {
    $user = User::factory()->create();
    $gameCharacter = GameCharacter::factory()->create(['name_en' => 'Variant Parent']);

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Tokai Teio A',
        'game_character_id' => $gameCharacter->id,
        'status' => 'active',
    ]);

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Tokai Teio B',
        'game_character_id' => $gameCharacter->id,
        'status' => 'completed',
    ]);

    $response = $this->actingAs($user)->get(route('characters.index', ['search' => 'Tokai Teio A']));

    $response->assertOk();
    $response->assertSee('Showing 1 of 1 characters');
    $response->assertSee('Versions');
});

it('stores game_character_id when creating character with trainee_id', function () {
    $user = User::factory()->create();
    $gameCharacter = GameCharacter::factory()->create(['name_en' => 'Test Trainee']);

    $data = [
        'name' => 'My Trainee Character',
        'scenario_type' => 'ura_finale',
        'trainee_id' => $gameCharacter->id,
        'stats' => [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ],
        'aptitudes' => [
            'distance' => [
                'sprint' => 'A',
                'mile' => 'A',
                'medium' => 'A',
                'long' => 'A',
            ],
            'surface' => [
                'turf' => 'A',
                'dirt' => 'A',
            ],
            'style' => [
                'front_runner' => 'A',
                'pace_chaser' => 'A',
                'late_surger' => 'A',
                'end_closer' => 'A',
            ],
        ],
    ];

    $response = $this->actingAs($user)->post(route('characters.store'), $data);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('ucp_characters', [
        'name' => 'My Trainee Character',
        'game_character_id' => $gameCharacter->id,
    ]);
});

it('handles character with empty goal races gracefully on show page', function () {
    $user = User::factory()->create();

    $gameCharacter = GameCharacter::factory()->create(['name_en' => 'No Goals']);

    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'No Goals',
        'game_character_id' => $gameCharacter->id,
    ]);

    $response = $this->actingAs($user)->get(route('characters.show', $character));

    $response->assertOk();
    $response->assertDontSee('Goal Races');
});

it('shows both sprint G1 goals on index for a character with multiple goal races', function () {
    $user = User::factory()->create();

    $gameCharacter = GameCharacter::factory()->create(['name_en' => 'Sprint Champ']);
    $race1 = GameRace::factory()->create([
        'name_en' => 'Sprinters Stakes',
        'grade' => 'G1',
        'distance_meters' => 1200,
        'distance_category' => 'sprint',
    ]);
    $race2 = GameRace::factory()->create([
        'name_en' => 'Takamatsunomiya Kinen',
        'grade' => 'G1',
        'distance_meters' => 1200,
        'distance_category' => 'sprint',
    ]);

    $gameCharacter->targetRaces()->attach($race1->id, ['race_type' => 'goal', 'priority' => 1, 'notes' => null]);
    $gameCharacter->targetRaces()->attach($race2->id, ['race_type' => 'goal', 'priority' => 2, 'notes' => null]);

    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Sprint Champ',
        'game_character_id' => $gameCharacter->id,
    ]);

    $response = $this->actingAs($user)->get(route('characters.index'));

    $response->assertOk();

    $characters = $response->viewData('characters');
    expect($characters)->not->toBeNull();

    $firstCharacter = $characters->items()[0] ?? null;
    expect($firstCharacter)->toBeArray();
    expect(data_get($firstCharacter, 'next_goal.name'))->toBe('Sprinters Stakes')
        ->and(data_get($firstCharacter, 'remaining_goal_count'))->toBe(1);
});

it('seeded Curren Chan game character has two sprint G1 goals', function () {
    $this->seed(\Database\Seeders\GameRaceSeeder::class);
    $this->seed(\Database\Seeders\GameCharacterSeeder::class);

    $currenChan = GameCharacter::where('slug', '=', 'curren-chan', 'and')->first(['*']);

    expect($currenChan)->not->toBeNull();

    $goalRaces = $currenChan->goalRaces;

    expect(count($goalRaces))->toBe(3);

    $goalSlugs = $goalRaces->pluck('slug', null)->sort()->values()->toArray();

    expect($goalSlugs)->toContain('sprinters-stakes');
    expect($goalSlugs)->toContain('takamatsunomiya-kinen');
    expect($goalSlugs)->toContain('arima-kinen');
});

it('no seeded game character has zero goal races after comprehensive seeder update', function () {
    $this->seed(\Database\Seeders\GameRaceSeeder::class);
    $this->seed(\Database\Seeders\GameCharacterSeeder::class);

    $gameCharacters = GameCharacter::with('goalRaces')->get();

    expect($gameCharacters->count())->toBeGreaterThanOrEqual(61);

    $noGoals = $gameCharacters->filter(fn ($gc) => $gc->goalRaces->isEmpty());

    /** @var string $noGoalNames */
    $noGoalNames = $noGoals->pluck('name_en')->join(', ');
    expect($noGoals->count())->toBe(0, 'These game characters have no goal races: '.$noGoalNames);
});
