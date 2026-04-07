<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Database\Seeders\CharacterTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates globally visible seeded characters for all authenticated users', function (): void {
    $viewer = User::factory()->create();
    $admin = User::factory()->create([
        'is_admin' => true,
        'email' => 'admin@umamusume.local',
    ]);

    $this->seed(CharacterTestSeeder::class);

    $allCharacters = Character::all();

    expect(
        $allCharacters
            ->filter(fn (Character $character): bool => $character->is_seeded)
            ->count()
    )->toBe(38);

    $expectedVisibleNames = [
        'Silence Suzuka',
        'Tokai Teio',
        'Gold Ship',
        'Kitasan Black',
        'Special Week',
        'Mejiro McQueen',
    ];

    foreach ($expectedVisibleNames as $name) {
        expect(
            $allCharacters->contains(
                fn (Character $character): bool => $character->name === $name
                    && $character->is_seeded
            )
        )->toBeTrue();
    }

    $viewerResponse = $this->actingAs($viewer)->get(route('characters.index'));
    $viewerResponse
        ->assertOk()
        ->assertSee('Silence Suzuka')
        ->assertSee('Tokai Teio')
        ->assertSee('Kitasan Black');

    $adminResponse = $this->actingAs($admin)->get(route('characters.index'));
    $adminResponse
        ->assertOk()
        ->assertSee('Silence Suzuka')
        ->assertSee('Gold Ship')
        ->assertSee('Special Week');
});

it('removes legacy per-admin copies seeded by older versions', function (): void {
    $admin = User::factory()->create([
        'is_admin' => true,
        'email' => 'admin@umamusume.local',
    ]);

    foreach (['Silence Suzuka', 'Tokai Teio', 'Gold Ship'] as $name) {
        Character::factory()->create([
            'user_id' => $admin->id,
            'name' => $name,
            'is_seeded' => false,
        ]);
    }

    $legacyNames = ['Silence Suzuka', 'Tokai Teio', 'Gold Ship'];

    expect(
        Character::all()
            ->filter(fn (Character $character): bool => $character->user_id === $admin->id
                && ! $character->is_seeded
                && in_array($character->name, $legacyNames, true)
            )
            ->count()
    )->toBe(3);

    $this->seed(CharacterTestSeeder::class);

    expect(
        Character::all()
            ->filter(fn (Character $character): bool => $character->user_id === $admin->id
                && ! $character->is_seeded
                && in_array($character->name, $legacyNames, true)
            )
            ->count()
    )->toBe(0);
});

it('shows seeded characters even with an out-of-range page query', function (): void {
    $viewer = User::factory()->create();

    $this->seed(CharacterTestSeeder::class);

    $response = $this->actingAs($viewer)->get(route('characters.index', ['page' => 999]));

    $response
        ->assertOk()
        ->assertSee('Silence Suzuka')
        ->assertSee('Tokai Teio')
        ->assertSee('Gold Ship');
});
