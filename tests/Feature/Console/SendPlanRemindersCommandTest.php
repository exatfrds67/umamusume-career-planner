<?php

declare(strict_types=1);

use App\Jobs\SendTurnNotification;
use App\Models\CareerPlan;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('queues reminder jobs for locked plans that still have remaining turns', function () {
    Queue::fake();

    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $eligiblePlan = CareerPlan::factory()->locked()->create([
        'user_id' => $user->id,
        'character_id' => $character->id,
        'current_turn' => 2,
        'plan' => [
            'plan_id' => 'eligible-plan',
            'character_id' => $character->id,
            'created_at' => now()->toIso8601String(),
            'goal' => 'Win the final race',
            'status' => 'completed',
            'total_turns' => 5,
            'timeline' => [
                ['turn' => 1, 'action' => ['type' => 'training'], 'state_after' => []],
                ['turn' => 2, 'action' => ['type' => 'rest'], 'state_after' => []],
            ],
            'summary' => [],
        ],
    ]);

    CareerPlan::factory()->locked()->create([
        'user_id' => $user->id,
        'character_id' => $character->id,
        'current_turn' => 6,
        'plan' => [
            'plan_id' => 'finished-plan',
            'character_id' => $character->id,
            'created_at' => now()->toIso8601String(),
            'goal' => 'Already done',
            'status' => 'completed',
            'total_turns' => 5,
            'timeline' => [],
            'summary' => [],
        ],
    ]);

    $this->artisan('career-plans:send-reminders')
        ->expectsOutput('Queued reminder jobs for 1 career plan(s).')
        ->assertSuccessful();

    Queue::assertPushed(SendTurnNotification::class, fn (SendTurnNotification $job): bool => $job->planId === $eligiblePlan->id);
    Queue::assertPushed(SendTurnNotification::class, 1);
});

it('can target a specific plan id', function () {
    Queue::fake();

    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $firstPlan = CareerPlan::factory()->locked()->create([
        'user_id' => $user->id,
        'character_id' => $character->id,
        'current_turn' => 1,
        'plan' => [
            'plan_id' => 'first-plan',
            'character_id' => $character->id,
            'created_at' => now()->toIso8601String(),
            'goal' => 'Target one',
            'status' => 'completed',
            'total_turns' => 4,
            'timeline' => [],
            'summary' => [],
        ],
    ]);

    $secondPlan = CareerPlan::factory()->locked()->create([
        'user_id' => $user->id,
        'character_id' => $character->id,
        'current_turn' => 1,
        'plan' => [
            'plan_id' => 'second-plan',
            'character_id' => $character->id,
            'created_at' => now()->toIso8601String(),
            'goal' => 'Target two',
            'status' => 'completed',
            'total_turns' => 4,
            'timeline' => [],
            'summary' => [],
        ],
    ]);

    $this->artisan('career-plans:send-reminders', ['--plan' => $secondPlan->id])
        ->expectsOutput('Queued reminder jobs for 1 career plan(s).')
        ->assertSuccessful();

    Queue::assertPushed(SendTurnNotification::class, fn (SendTurnNotification $job): bool => $job->planId === $secondPlan->id);
    Queue::assertNotPushed(SendTurnNotification::class, fn (SendTurnNotification $job): bool => $job->planId === $firstPlan->id);
});
