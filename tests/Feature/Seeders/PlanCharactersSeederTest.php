<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\GameCharacter;
use App\Models\User;
use Database\Seeders\PlanCharactersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

/**
 * @return array{admin: User, placeholder: Character}
 */
function charSeederFixtures(): array
{
    $admin = User::factory()->create(['email' => 'admin@umamusume.local']);

    collect([
        ['name_en' => 'Biwa Hayahide', 'image_path' => 'images/trainee_images/biwa_hayahide.png'],
        ['name_en' => 'Vodka', 'image_path' => 'images/trainee_images/vodka.png'],
        ['name_en' => 'Daiwa Scarlet', 'image_path' => 'images/trainee_images/daiwa_scarlet.png'],
        ['name_en' => 'Tokai Teio', 'image_path' => 'images/trainee_images/tokai_teio.png'],
        ['name_en' => 'Haru Urara', 'image_path' => 'images/trainee_images/haru_urara.png'],
        ['name_en' => 'El Condor Pasa', 'image_path' => 'images/trainee_images/el_condor_pasa.png'],
    ])->each(fn (array $attributes) => GameCharacter::factory()->create($attributes));

    // Placeholder character owned by a different user so the seeder guard is not triggered.
    $otherUser = User::factory()->create();
    $placeholder = Character::factory()->create(['user_id' => $otherUser->id]);
    Career::factory()->count(8)->create([
        'user_id' => $admin->id,
        'character_id' => $placeholder->id,
    ]);

    return ['admin' => $admin, 'placeholder' => $placeholder];
}

describe('PlanCharactersSeeder', function () {
    it('creates 8 new ucp_characters rows owned by admin', function () {
        ['admin' => $admin, 'placeholder' => $placeholder] = charSeederFixtures();

        $before = Character::where('user_id', $admin->id)->count();

        $this->artisan('db:seed', ['--class' => PlanCharactersSeeder::class]);

        $after = Character::where('user_id', $admin->id)->count();

        expect($after - $before)->toBe(8);
    });

    it('re-links all 8 careers to new characters owned by admin', function () {
        ['admin' => $admin] = charSeederFixtures();

        $this->artisan('db:seed', ['--class' => PlanCharactersSeeder::class]);

        for ($careerId = 1; $careerId <= 8; $careerId++) {
            $career = Career::find($careerId);
            $character = Character::find($career->character_id);

            expect($character)->not->toBeNull()
                ->and($character->user_id)->toBe($admin->id);
        }
    });

    it('each new character carries the correct stats for its career', function () {
        charSeederFixtures();

        $this->artisan('db:seed', ['--class' => PlanCharactersSeeder::class]);

        // Spot-check career 2 (Vodka Star) and career 8 (El Condor Pasa)
        $vodkaCareer = Career::find(2);
        $vodkaChar = Character::find($vodkaCareer->character_id);

        expect($vodkaChar->name)->toBe('Vodka (Star)')
            ->and($vodkaChar->current_stats['speed'])->toBe(767)
            ->and($vodkaChar->status)->toBe('completed');

        $elCareer = Career::find(8);
        $elChar = Character::find($elCareer->character_id);

        expect($elChar->name)->toBe('El Condor Pasa')
            ->and($elChar->current_stats['speed'])->toBe(194)
            ->and($elChar->career_stage)->toBe('junior');
    });

    it('is idempotent — skips if career 1 already has an admin-owned character', function () {
        ['admin' => $admin] = charSeederFixtures();

        $this->artisan('db:seed', ['--class' => PlanCharactersSeeder::class]);
        $countAfterFirst = Character::where('user_id', $admin->id)->count();

        $this->artisan('db:seed', ['--class' => PlanCharactersSeeder::class]);
        $countAfterSecond = Character::where('user_id', $admin->id)->count();

        expect($countAfterSecond)->toBe($countAfterFirst);
    });

    it('makes all 8 new characters visible via the training predictions controller query', function () {
        ['admin' => $admin] = charSeederFixtures();

        $this->artisan('db:seed', ['--class' => PlanCharactersSeeder::class]);

        // Simulate the controller query that populates the dropdown
        $visibleCharacters = Character::where('user_id', $admin->id)
            ->orderBy('name')
            ->get();

        // 1 placeholder + 8 new = 9 total; all 8 plan names should be present
        $names = $visibleCharacters->pluck('name')->all();

        foreach (['Biwa Hayahide', 'Vodka (Star)', 'Vodka (Platinum)', 'Daiwa Scarlet',
            'Tokai Teio', 'Haru Urara (JBC)', 'Haru Urara (URA)', 'El Condor Pasa'] as $expected) {
            expect($names)->toContain($expected);
        }
    });

    it('stores linked game character ids and avatar urls for seeded plan variants', function () {
        charSeederFixtures();

        $this->artisan('db:seed', ['--class' => PlanCharactersSeeder::class]);

        $vodkaStar = Character::query()->where('name', 'Vodka (Star)')->firstOrFail();
        $haruUraraJbc = Character::query()->where('name', 'Haru Urara (JBC)')->firstOrFail();

        expect($vodkaStar->game_character_id)->toBe(
            GameCharacter::query()->where('name_en', 'Vodka')->value('id')
        )->and($vodkaStar->getRawOriginal('avatar_url'))->toBe('/images/trainee_images/vodka.png');

        expect($haruUraraJbc->game_character_id)->toBe(
            GameCharacter::query()->where('name_en', 'Haru Urara')->value('id')
        )->and($haruUraraJbc->getRawOriginal('avatar_url'))->toBe('/images/trainee_images/haru_urara.png');
    });

    it('retroactively links variant names to the base game character catalog', function () {
        $user = User::factory()->create();
        $vodka = GameCharacter::factory()->create([
            'name_en' => 'Vodka',
            'image_path' => 'images/trainee_images/vodka.png',
        ]);

        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'Vodka (Platinum)',
            'avatar_url' => null,
            'game_character_id' => null,
        ]);

        $this->artisan('characters:link-game-data')
            ->assertSuccessful();

        $character->refresh();

        expect($character->game_character_id)->toBe($vodka->id)
            ->and($character->getRawOriginal('avatar_url'))->toBe('/images/trainee_images/vodka.png');
    });

    it('retroactively links variant names using local image files when the catalog image path is missing', function () {
        $user = User::factory()->create();
        $vodka = GameCharacter::factory()->create([
            'name_en' => 'Vodka',
            'image_path' => null,
        ]);

        $imageDirectory = public_path('images/trainee_images');
        File::ensureDirectoryExists($imageDirectory);
        $imageFile = $imageDirectory.DIRECTORY_SEPARATOR.'__vodka_umamusume_link_fixture.png';
        File::put($imageFile, 'fixture');

        $character = Character::factory()->create([
            'user_id' => $user->id,
            'name' => 'Vodka (Star)',
            'avatar_url' => null,
            'game_character_id' => null,
        ]);

        try {
            $this->artisan('characters:link-game-data')
                ->assertSuccessful();

            $character->refresh();

            expect($character->game_character_id)->toBe($vodka->id)
                ->and($character->getRawOriginal('avatar_url'))->toBe('/images/trainee_images/__vodka_umamusume_link_fixture.png');
        } finally {
            File::delete($imageFile);
        }
    });
});
