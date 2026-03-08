<?php

use App\Models\GameCharacter;
use App\Models\GameRace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── GameCharacter Model ───────────────────────────────────────────────────────

it('can create a game character', function (): void {
    $character = GameCharacter::factory()->create([
        'slug' => 'test-horse',
        'name_en' => 'Test Horse',
        'primary_distance' => 'medium',
        'preferred_style' => 'leader',
    ]);

    expect($character->slug)->toBe('test-horse')
        ->and($character->name_en)->toBe('Test Horse')
        ->and($character->primary_distance)->toBe('medium');
});

it('has a targetRaces relationship', function (): void {
    $character = GameCharacter::factory()->create();
    $race = GameRace::factory()->create();

    $character->targetRaces()->attach($race->id, [
        'race_type' => 'goal',
        'priority' => 1,
        'notes' => 'The dream race',
    ]);

    expect($character->targetRaces()->count())->toBe(1)
        ->and($character->targetRaces->first()->id)->toBe($race->id)
        ->and($character->targetRaces->first()->pivot->race_type)->toBe('goal');
});

it('goalRaces returns only goal type races', function (): void {
    $character = GameCharacter::factory()->create();
    $goalRace = GameRace::factory()->create(['slug' => 'goal-race-a']);
    $storyRace = GameRace::factory()->create(['slug' => 'story-race-a']);

    $character->targetRaces()->attach($goalRace->id, ['race_type' => 'goal', 'priority' => 1, 'notes' => null]);
    $character->targetRaces()->attach($storyRace->id, ['race_type' => 'story', 'priority' => 2, 'notes' => null]);

    expect($character->goalRaces()->count())->toBe(1)
        ->and($character->goalRaces->first()->id)->toBe($goalRace->id);
});

it('requiredRaces returns required and goal type races', function (): void {
    $character = GameCharacter::factory()->create();
    $goalRace = GameRace::factory()->create(['slug' => 'goal-race-b']);
    $requiredRace = GameRace::factory()->create(['slug' => 'required-race-b']);
    $storyRace = GameRace::factory()->create(['slug' => 'story-race-b']);
    $recommendedRace = GameRace::factory()->create(['slug' => 'recommended-race-b']);

    $character->targetRaces()->attach($goalRace->id, ['race_type' => 'goal', 'priority' => 1, 'notes' => null]);
    $character->targetRaces()->attach($requiredRace->id, ['race_type' => 'required', 'priority' => 2, 'notes' => null]);
    $character->targetRaces()->attach($storyRace->id, ['race_type' => 'story', 'priority' => 3, 'notes' => null]);
    $character->targetRaces()->attach($recommendedRace->id, ['race_type' => 'recommended', 'priority' => 4, 'notes' => null]);

    expect($character->requiredRaces()->count())->toBe(2);
});

it('returns goal race policy and citation metadata from notes', function (): void {
    $character = GameCharacter::factory()->create([
        'notes' => [
            'goal_race_policy' => [
                'starts_with' => 'debut-race',
            ],
            'goal_race_policy_sources' => [
                [
                    'label' => 'Official JP URA Finals scenario page',
                    'url' => 'https://umamusume.jp/contents/game/scenario/ura/',
                ],
            ],
            'goal_race_sources' => [
                [
                    'label' => 'Official event page',
                    'url' => 'https://example.test/official',
                ],
            ],
        ],
    ]);

    expect($character->goalRacePolicy()['starts_with'])->toBe('debut-race')
        ->and($character->goalRacePolicy()['ends_with'])->toBe('arima-kinen')
        ->and($character->goalRacePolicy()['mode_extensions'])->not->toHaveKey('unity_cup')
        ->and($character->goalRacePolicySources())->toHaveCount(1)
        ->and($character->goalRacePolicySources()[0]['label'])->toBe('Official JP URA Finals scenario page')
        ->and($character->goalRaceSources())->toHaveCount(1)
        ->and($character->goalRaceSources()[0]['label'])->toBe('Official event page');
});

it('GameRace has a gameCharacters reverse relationship', function (): void {
    $character = GameCharacter::factory()->create();
    $race = GameRace::factory()->create(['slug' => 'reverse-test-race']);

    $character->targetRaces()->attach($race->id, ['race_type' => 'goal', 'priority' => 1, 'notes' => null]);

    expect($race->gameCharacters()->count())->toBe(1)
        ->and($race->gameCharacters->first()->id)->toBe($character->id);
});

it('enforces unique character-race combinations in the pivot', function (): void {
    $character = GameCharacter::factory()->create();
    $race = GameRace::factory()->create(['slug' => 'unique-test-race']);

    $character->targetRaces()->attach($race->id, ['race_type' => 'goal', 'priority' => 1, 'notes' => null]);

    expect(fn () => $character->targetRaces()->attach($race->id, ['race_type' => 'story', 'priority' => 2, 'notes' => null]))
        ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

// ── Seeder Smoke Tests ────────────────────────────────────────────────────────

it('has seeded game characters in the database', function (): void {
    $this->seed(\Database\Seeders\GameRaceSeeder::class);
    $this->seed(\Database\Seeders\GameCharacterSeeder::class);

    expect(GameCharacter::count('*'))->toBeGreaterThanOrEqual(60);
});

it('Special Week has Japan Derby as a goal race after seeding', function (): void {
    $this->seed(\Database\Seeders\GameRaceSeeder::class);
    $this->seed(\Database\Seeders\GameCharacterSeeder::class);

    $specialWeek = GameCharacter::query()->where('slug', '=', 'special-week', 'and')->first(['*']);

    expect($specialWeek)->not->toBeNull()
        ->and($specialWeek->goalRaces()->count())->toBeGreaterThanOrEqual(1);

    $japanDerby = $specialWeek->goalRaces()->where('slug', '=', 'japan-derby', 'and')->first(['*']);
    expect($japanDerby)->not->toBeNull();
});

it('all seeded characters have at least one target race', function (): void {
    $this->seed(\Database\Seeders\GameRaceSeeder::class);
    $this->seed(\Database\Seeders\GameCharacterSeeder::class);

    $withoutRaces = GameCharacter::query()
        ->whereDoesntHave('targetRaces')
        ->pluck('slug');

    expect($withoutRaces->isEmpty())->toBeTrue();
});

it('all seeded characters start with debut and end with arima kinen goal', function (): void {
    $this->seed(\Database\Seeders\GameRaceSeeder::class);
    $this->seed(\Database\Seeders\GameCharacterSeeder::class);

    $characters = GameCharacter::query()->with('targetRaces')->get();

    expect($characters->isNotEmpty())->toBeTrue();

    $failures = $characters->filter(function (GameCharacter $character): bool {
        $targets = $character->targetRaces->sortBy(fn ($race) => $race->pivot->priority)->values();
        $first = $targets->first();
        $last = $targets->last();

        return $first?->slug !== 'debut-race'
            || $first->pivot->race_type !== 'required'
            || $last?->slug !== 'arima-kinen'
            || $last->pivot->race_type !== 'goal';
    });

    expect($failures->isEmpty())->toBeTrue($failures->pluck('slug')->join(', '));
});

it('seeded characters expose goal race policy metadata for future source citations', function (): void {
    $this->seed(\Database\Seeders\GameRaceSeeder::class);
    $this->seed(\Database\Seeders\GameCharacterSeeder::class);

    $specialWeek = GameCharacter::query()->where('slug', '=', 'special-week', 'and')->firstOrFail();
    $notes = $specialWeek->notes ?? [];

    expect($specialWeek->goalRacePolicy()['starts_with'])->toBe('debut-race')
        ->and($specialWeek->goalRacePolicy()['ends_with'])->toBe('arima-kinen')
        ->and($specialWeek->goalRacePolicy()['mode_extensions'])->not->toHaveKey('unity_cup')
        ->and(data_get($notes, 'goal_race_research.status'))->toBe('pending-character-goal-verification')
        ->and(data_get($notes, 'goal_race_research.verification_scope.ura_finale_extension'))->toBe('official-jp-portal')
        ->and(data_get($notes, 'goal_race_research.verification_scope.unity_cup_extension'))->toBe('unverified')
        ->and($specialWeek->goalRacePolicySources())->toHaveCount(3)
        ->and($specialWeek->goalRaceSources())->toBeArray();
});
