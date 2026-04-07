<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\TrainingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a fallback active career when training executes without an existing career', function (): void {
    $user = User::factory()->create();

    $character = Character::factory()->create([
        'user_id' => $user->id,
        'scenario_type' => 'ura_finale',
        'career_stage' => 'junior',
        'current_turn' => 1,
        'energy_level' => 100,
        'status' => 'active',
        'available_sp' => 0,
        'current_stats' => [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ],
    ]);

    expect($character->careers()->count())->toBe(0);

    $service = app(TrainingService::class);
    $result = $service->executeTraining($character, 'speed', [
        'speed' => 20,
        'power' => 5,
        'sp' => 3,
    ]);

    expect($result['success'])->toBeTrue();

    $character->refresh();
    $career = $character->currentCareer;

    expect($career)->not->toBeNull();
    expect($career->user_id)->toBe($user->id)
        ->and($career->character_id)->toBe($character->id)
        ->and($career->status)->toBe('active')
        ->and($career->scenario_type)->toBe('ura_finale');

    $session = TrainingSession::query()->latest('id')->first();

    expect($session)->not->toBeNull();
    expect($session->career_id)->toBe($career->id)
        ->and($session->character_id)->toBe($character->id);
});
