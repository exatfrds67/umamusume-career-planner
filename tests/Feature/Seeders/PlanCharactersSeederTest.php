<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\User;
use Database\Seeders\PlanCharactersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array{admin: User, placeholder: Character}
 */
function charSeederFixtures(): array
{
    $admin = User::factory()->create(['email' => 'admin@umamusume.local']);
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
});
