<?php

declare(strict_types=1);

use App\Models\CareerPlan;
use App\Models\Character;
use App\Models\User;
use App\Notifications\TurnReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('links active reminder emails to the character page visualizer section', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);
    $plan = CareerPlan::factory()->locked()->create([
        'user_id' => $user->id,
        'character_id' => $character->id,
        'current_turn' => 12,
    ]);

    $notification = new TurnReminder(
        $plan,
        [
            'action' => [
                'type' => 'training',
                'reasoning' => 'Push speed before the next race block.',
            ],
        ],
        ['email' => true],
        false,
    );

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionText)->toBe('View Plan')
        ->and($mailMessage->actionUrl)->toEndWith("/characters/{$character->id}#career-plan-visualizer");
});
