<?php

declare(strict_types=1);

use App\Jobs\GenerateCareerPlan;
use App\Jobs\SendTurnNotification;
use App\Models\CareerPlan;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Timeline Test Character',
        'available_sp' => 120,
        'current_turn' => 1,
        'energy_level' => 72,
    ]);
});

it('queues an asynchronous career plan request and reserves a plan record', function () {
    Queue::fake();

    $response = $this->actingAs($this->user)
        ->postJson('/api/career-planning/plan/jobs', [
            'character_id' => $this->character->id,
            'goal' => 'Win the final race',
            'options' => [
                'depth' => 'full',
            ],
        ]);

    $response->assertStatus(202)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['job_id', 'plan_id', 'status_url']);

    expect(CareerPlan::query()->count())->toBe(1);

    $planId = (string) $response->json('plan_id');

    $this->assertDatabaseHas('career_plans', [
        'id' => $planId,
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'is_locked' => false,
    ]);

    Queue::assertPushed(GenerateCareerPlan::class, function (GenerateCareerPlan $job) use ($planId): bool {
        return $job->planId === $planId && $job->characterId === $this->character->id;
    });
});

it('returns accepted when a reserved plan is still pending', function () {
    $plan = CareerPlan::factory()->queued()->create([
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'plan' => [
            'plan_id' => 'pending-plan',
            'character_id' => $this->character->id,
            'created_at' => now()->toIso8601String(),
            'goal' => 'Win the final race',
            'status' => 'queued',
            'job_id' => 'job-123',
            'total_turns' => 0,
            'timeline' => [],
            'summary' => [],
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/career-planning/plan/{$plan->id}");

    $response->assertStatus(202)
        ->assertJsonPath('success', true)
        ->assertJsonPath('status', 'queued');

    expect($response->headers->get('Retry-After'))->toBe('10');
});

it('locks a completed plan and queues a turn notification', function () {
    Queue::fake();

    $plan = CareerPlan::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'is_locked' => false,
        'plan' => [
            'plan_id' => 'completed-plan',
            'character_id' => $this->character->id,
            'created_at' => now()->toIso8601String(),
            'goal' => 'Win the final race',
            'status' => 'completed',
            'total_turns' => 5,
            'timeline' => [
                [
                    'turn' => 1,
                    'action' => [
                        'type' => 'training',
                        'facility' => 'speed',
                        'reasoning' => 'Train speed first.',
                    ],
                    'state_after' => [],
                ],
            ],
            'summary' => [],
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/career-planning/plan/{$plan->id}/lock", [
            'start_turn' => 1,
            'notification_preferences' => [
                'email' => true,
                'push' => false,
            ],
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('career_plans', [
        'id' => $plan->id,
        'is_locked' => true,
        'current_turn' => 1,
    ]);

    Queue::assertPushed(SendTurnNotification::class, fn (SendTurnNotification $job): bool => $job->planId === $plan->id);
});

it('returns the next action for a locked plan', function () {
    $plan = CareerPlan::factory()->locked()->create([
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'current_turn' => 2,
        'plan' => [
            'plan_id' => 'next-plan',
            'character_id' => $this->character->id,
            'created_at' => now()->toIso8601String(),
            'goal' => 'Win the final race',
            'status' => 'completed',
            'total_turns' => 4,
            'timeline' => [
                ['turn' => 1, 'action' => ['type' => 'training'], 'state_after' => []],
                ['turn' => 2, 'action' => ['type' => 'rest', 'reasoning' => 'Recover now.'], 'state_after' => []],
            ],
            'summary' => [],
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/career-planning/plan/{$plan->id}/next");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('turn', 2)
        ->assertJsonPath('action.type', 'rest');
});

it('advances a locked plan turn and queues the next notification', function () {
    Queue::fake();

    $plan = CareerPlan::factory()->locked()->create([
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'current_turn' => 1,
        'plan' => [
            'plan_id' => 'advance-plan',
            'character_id' => $this->character->id,
            'created_at' => now()->toIso8601String(),
            'goal' => 'Win the final race',
            'status' => 'completed',
            'total_turns' => 3,
            'timeline' => [
                ['turn' => 1, 'action' => ['type' => 'training'], 'state_after' => []],
                ['turn' => 2, 'action' => ['type' => 'race'], 'state_after' => []],
            ],
            'summary' => [],
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/career-planning/plan/{$plan->id}/advance");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('new_turn', 2);

    $plan->refresh();
    expect($plan->current_turn)->toBe(2);

    Queue::assertPushed(SendTurnNotification::class, fn (SendTurnNotification $job): bool => $job->planId === $plan->id);
});
