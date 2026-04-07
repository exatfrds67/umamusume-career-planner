<?php

declare(strict_types=1);

use App\Models\CareerPlan;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('renders the ai career plan visualizer on the character show page', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Special Week',
        'scenario_type' => 'ura_finale',
        'current_turn' => 14,
    ]);

    $response = $this->actingAs($user)
        ->get(route('characters.show', $character));

    $response->assertOk()
        ->assertSee('AI Career Plan')
        ->assertSee('Generate plan')
        ->assertSee('Generate a game-aware turn timeline using the current character state, skills, and races.');

    $response->assertSee('id="career-plan-visualizer"', false);
    $response->assertSee('trainingTimeline({', false);
});

it('preloads the latest generated career plan into the character show page', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Tokai Teio',
    ]);

    $plan = CareerPlan::factory()->locked()->create([
        'user_id' => $user->id,
        'character_id' => $character->id,
        'goal' => 'Peak for the final Arima Kinen push',
        'current_turn' => 8,
        'plan' => [
            'plan_id' => 'visualizer-plan',
            'character_id' => $character->id,
            'created_at' => now()->toIso8601String(),
            'goal' => 'Peak for the final Arima Kinen push',
            'status' => 'completed',
            'total_turns' => 72,
            'timeline' => [
                [
                    'turn' => 8,
                    'action' => [
                        'type' => 'training',
                        'facility' => 'speed',
                        'reasoning' => 'Push speed before the next race block.',
                    ],
                    'state_after' => [
                        'stats' => [
                            'speed' => 410,
                            'stamina' => 320,
                            'power' => 300,
                            'guts' => 250,
                            'wit' => 280,
                        ],
                        'energy' => 58,
                        'mood' => 'good',
                        'sp' => 145,
                        'skills' => [],
                    ],
                ],
            ],
            'summary' => [
                'final_predicted_stats' => [
                    'speed' => 1020,
                    'stamina' => 820,
                    'power' => 790,
                    'guts' => 510,
                    'wit' => 700,
                ],
                'total_sp_earned' => 1240,
                'races_won' => 7,
                'confidence' => 0.84,
            ],
        ],
    ]);

    $response = $this->actingAs($user)
        ->get(route('characters.show', $character));

    $response->assertOk()
        ->assertSee($plan->id, false)
        ->assertSee('Peak for the final Arima Kinen push', false)
        ->assertSee('trainingTimeline({', false);
});

it('renders the character show page when the career plans table is unavailable', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Vodka',
    ]);

    Schema::dropIfExists('career_plans');

    $response = $this->actingAs($user)
        ->get(route('characters.show', $character));

    $response->assertOk()
        ->assertSee('AI Career Plan')
        ->assertViewHas('latestCareerPlan', fn ($latestCareerPlan) => $latestCareerPlan === null);
});
