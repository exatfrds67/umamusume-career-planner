<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\GameCharacter;
use App\Models\User;
use Database\Seeders\EnhancedRealUmaMusumeCharactersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/** @return array<string, mixed> */
function fakeUmapyoiCharacterPayload(string $nameEn, string $nameJp): array
{
    return [
        'id' => 1001,
        'name_en' => $nameEn,
        'name_jp' => $nameJp,
        'thumb_img' => 'https://example.test/thumb.png',
        'sns_icon' => 'https://example.test/icon.png',
    ];
}

it('links newly seeded external characters to the canonical game character catalog', function (): void {
    GameCharacter::factory()->create([
        'name_en' => 'Special Week',
        'name_jp' => 'スペシャルウィーク',
    ]);

    Http::fake([
        'https://api.umapyoi.net/api/v1/character/info' => Http::response([
            fakeUmapyoiCharacterPayload('Special Week', 'スペシャルウィーク'),
        ], 200),
    ]);

    $this->seed(EnhancedRealUmaMusumeCharactersSeeder::class);

    $character = Character::query()->where('name', '=', 'Special Week', 'and')->firstOrFail();
    $gameCharacter = GameCharacter::query()->where('name_en', '=', 'Special Week', 'and')->firstOrFail();

    expect($character->game_character_id)->toBe($gameCharacter->id)
        ->and($character->scenario_type)->toBe('ura_finale');
});

it('retroactively links existing external characters when the canonical game character exists', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $gameCharacter = GameCharacter::factory()->create([
        'name_en' => 'Special Week',
        'name_jp' => 'スペシャルウィーク',
    ]);

    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Special Week',
        'game_character_id' => null,
    ]);

    Http::fake([
        'https://api.umapyoi.net/api/v1/character/info' => Http::response([
            fakeUmapyoiCharacterPayload('Special Week', 'スペシャルウィーク'),
        ], 200),
    ]);

    $this->seed(EnhancedRealUmaMusumeCharactersSeeder::class);

    $character->refresh();

    expect($character->game_character_id)->toBe($gameCharacter->id);
});
