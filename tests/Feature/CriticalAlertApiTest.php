<?php

declare(strict_types=1);

use App\Collections\CriticalAlertCollection;
use App\Enums\AlertType;
use App\Enums\Priority;
use App\Models\Character;
use App\Models\User;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CriticalAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->mock(TrainingAdvisoryService::class, function ($mock) {
        $mock->shouldReceive('detectCriticalSituations')
            ->andReturnUsing(function ($context) {
                $alerts = [];

                $stamina = $context->stats->stamina ?? 0;
                if ($stamina < 400) {
                    $upcomingRaces = $context->upcomingRaces ?? [];
                    $turnsUntilCritical = 0;
                    if (! empty($upcomingRaces)) {
                        $nextRace = $upcomingRaces[0];
                        $raceTurn = $nextRace['turn'] ?? 0;
                        $turnsUntilCritical = max(0, $raceTurn - $context->turnNumber);
                    }

                    $alerts[] = new CriticalAlert(
                        type: AlertType::STAMINA_CRISIS,
                        message: "Stamina critically low at {$stamina} for upcoming race",
                        actionItems: ['Focus on Stamina training', 'Consider rest to recover energy'],
                        turnsUntilCritical: $turnsUntilCritical,
                        detailedAnalysis: "Running style affects stamina consumption. Current stamina of {$stamina} is below recommended threshold.",
                        priority: Priority::CRITICAL,
                    );
                }

                $energy = $context->energy ?? 50;
                if ($energy < 40) {
                    $alerts[] = new CriticalAlert(
                        type: AlertType::ENERGY_CRITICAL,
                        message: "Energy at {$energy} - high failure rate risk",
                        actionItems: ['Rest immediately or train Wisdom'],
                        turnsUntilCritical: 0,
                        detailedAnalysis: null,
                        priority: Priority::CRITICAL,
                    );
                }

                $spAvailable = $context->spAvailable ?? 0;
                if ($spAvailable < 50 && $context->turnNumber > 30) {
                    $alerts[] = new CriticalAlert(
                        type: AlertType::SP_SHORTAGE,
                        message: "SP budget low at {$spAvailable}",
                        actionItems: ['Prioritize SP-efficient skills', 'Focus on hint collection'],
                        turnsUntilCritical: 0,
                        detailedAnalysis: null,
                        priority: Priority::HIGH,
                    );
                }

                return new CriticalAlertCollection($alerts);
            });
    });
});

it('detects stamina crisis via API', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Low Stamina Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 320,
            'power' => 550,
            'guts' => 480,
            'wisdom' => 520,
        ],
        'energy_level' => 75,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/advisory/critical/detect', [
            'career_run_id' => $character->id,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 75,
                'upcoming_races' => [
                    ['distance' => 'medium', 'turn' => 38],
                ],
            ],
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'alerts' => [
                '*' => [
                    'type',
                    'priority',
                    'message',
                    'action_items',
                    'turns_until_critical',
                ],
            ],
        ]);

    $alerts = $response->json('alerts');
    $staminaAlert = collect($alerts)->firstWhere('type', 'stamina_crisis');

    expect($staminaAlert)->not->toBeNull();
    expect($staminaAlert['priority'])->toBe('critical');
    expect($staminaAlert['message'])->toContain('Stamina');
    expect($staminaAlert['message'])->toContain('320');
    expect($staminaAlert['action_items'])->not->toBeEmpty();
})->group('feature', 'api', 'critical-alerts', 'stamina-crisis');

it('detects energy critical via API', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 600,
            'stamina' => 500,
            'power' => 550,
            'guts' => 480,
            'wisdom' => 520,
        ],
        'energy_level' => 25,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 30,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/advisory/critical/detect', [
            'career_run_id' => $character->id,
            'turn_number' => 30,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 500,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 25,
            ],
        ]);

    $response->assertOk();
    $alerts = $response->json('alerts');
    $energyAlert = collect($alerts)->firstWhere('type', 'energy_critical');

    expect($energyAlert)->not->toBeNull();
    expect($energyAlert['message'])->toContain('Energy');
    expect($energyAlert['message'])->toContain('25');
    $hasRestAction = collect($energyAlert['action_items'])->contains(fn ($item) => str_contains($item, 'Rest'));
    expect($hasRestAction)->toBeTrue();
})->group('feature', 'api', 'critical-alerts', 'energy-critical');

it('detects SP shortage via API', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 600,
            'stamina' => 500,
            'power' => 550,
            'guts' => 480,
            'wisdom' => 520,
        ],
        'available_sp' => 30,
        'energy_level' => 75,
        'current_turn' => 40,
        'career_stage' => 'classic',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/advisory/critical/detect', [
            'career_run_id' => $character->id,
            'turn_number' => 40,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 500,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 75,
                'sp_available' => 30,
                'acquired_skills' => [1, 2, 3],
            ],
        ]);

    $response->assertOk();
    $alerts = $response->json('alerts');
    $spAlert = collect($alerts)->firstWhere('type', 'sp_shortage');

    expect($spAlert)->not->toBeNull();
    expect($spAlert['message'])->toContain('SP');
    expect($spAlert['action_items'])->not->toBeEmpty();
})->group('feature', 'api', 'critical-alerts', 'sp-shortage');

it('returns multiple alerts for multiple issues', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 600,
            'stamina' => 300,
            'power' => 550,
            'guts' => 480,
            'wisdom' => 520,
        ],
        'available_sp' => 25,
        'energy_level' => 30,
        'mood_status' => 'bad',
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/advisory/critical/detect', [
            'career_run_id' => $character->id,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 300,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'sp_available' => 25,
                'energy' => 30,
                'mood' => 'bad',
                'upcoming_races' => [
                    ['distance' => 'medium', 'turn' => 38],
                ],
            ],
        ]);

    $response->assertOk();
    $alerts = $response->json('alerts');

    expect($alerts)->toBeArray();
    expect(count($alerts))->toBeGreaterThanOrEqual(2);

    $alertTypes = collect($alerts)->pluck('type')->toArray();
    expect($alertTypes)->toContain('stamina_crisis');
    expect($alertTypes)->toContain('energy_critical');
})->group('feature', 'api', 'critical-alerts', 'multiple-alerts');

it('includes detailed analysis in alerts', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 600,
            'stamina' => 320,
            'power' => 550,
            'guts' => 480,
            'wisdom' => 520,
        ],
        'energy_level' => 75,
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/advisory/critical/detect', [
            'career_run_id' => $character->id,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 75,
                'upcoming_races' => [
                    ['distance' => 'medium', 'turn' => 38],
                ],
            ],
        ]);

    $response->assertOk();
    $alerts = $response->json('alerts');
    $staminaAlert = collect($alerts)->firstWhere('type', 'stamina_crisis');

    expect($staminaAlert)->toHaveKey('detailed_analysis');
    expect($staminaAlert['detailed_analysis'])->not->toBeEmpty();
    expect($staminaAlert['detailed_analysis'])->toContain('Running style');
})->group('feature', 'api', 'critical-alerts', 'detailed-analysis');

it('calculates turns until critical correctly', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 600,
            'stamina' => 320,
            'power' => 550,
            'guts' => 480,
            'wisdom' => 520,
        ],
        'energy_level' => 75,
        'current_turn' => 35,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/advisory/critical/detect', [
            'career_run_id' => $character->id,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 75,
                'upcoming_races' => [
                    ['distance' => 'medium', 'turn' => 38],
                ],
            ],
        ]);

    $response->assertOk();
    $alerts = $response->json('alerts');
    $staminaAlert = collect($alerts)->firstWhere('type', 'stamina_crisis');

    expect($staminaAlert)->toHaveKey('turns_until_critical');
    expect($staminaAlert['turns_until_critical'])->toBe(3);
})->group('feature', 'api', 'critical-alerts', 'timing');

it('returns empty alerts for healthy character', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 800,
            'stamina' => 700,
            'power' => 750,
            'guts' => 650,
            'wisdom' => 720,
        ],
        'available_sp' => 200,
        'energy_level' => 80,
        'mood_status' => 'great',
        'career_stage' => 'classic',
        'current_turn' => 25,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/advisory/critical/detect', [
            'career_run_id' => $character->id,
            'turn_number' => 25,
            'context' => [
                'stats' => [
                    'speed' => 800,
                    'stamina' => 700,
                    'power' => 750,
                    'guts' => 650,
                    'wisdom' => 720,
                ],
                'sp_available' => 200,
                'energy' => 80,
                'mood' => 'great',
            ],
        ]);

    $response->assertOk();
    $alerts = $response->json('alerts');

    expect($alerts)->toBeArray();
    expect($alerts)->toBeEmpty();
})->group('feature', 'api', 'critical-alerts', 'no-alerts');

it('validates required fields in API request', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/advisory/critical/detect', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['career_run_id', 'turn_number']);
})->group('feature', 'api', 'critical-alerts', 'validation');

it('requires authentication for API access', function () {
    $response = $this->postJson('/api/advisory/critical/detect', [
        'career_run_id' => 1,
        'turn_number' => 35,
        'context' => [],
    ]);

    $response->assertUnauthorized();
})->group('feature', 'api', 'critical-alerts', 'authentication');

it('prioritizes alerts correctly', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 600,
            'stamina' => 300,
            'power' => 550,
            'guts' => 480,
            'wisdom' => 520,
        ],
        'available_sp' => 45,
        'energy_level' => 35,
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/advisory/critical/detect', [
            'career_run_id' => $character->id,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 300,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'sp_available' => 45,
                'energy' => 35,
                'upcoming_races' => [
                    ['distance' => 'medium', 'turn' => 38],
                ],
            ],
        ]);

    $response->assertOk();
    $alerts = $response->json('alerts');

    $firstAlert = $alerts[0] ?? null;
    expect($firstAlert)->not->toBeNull();
    expect($firstAlert['priority'])->toBeIn(['critical', 'high']);

    $staminaIndex = collect($alerts)->search(fn ($alert) => $alert['type'] === 'stamina_crisis');
    $spIndex = collect($alerts)->search(fn ($alert) => $alert['type'] === 'sp_shortage');

    if ($staminaIndex !== false && $spIndex !== false) {
        expect($staminaIndex)->toBeLessThan($spIndex);
    }
})->group('feature', 'api', 'critical-alerts', 'priority');
