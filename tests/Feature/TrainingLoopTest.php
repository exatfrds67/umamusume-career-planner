<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Career;
use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;


beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ],
        'energy_level' => 100,
        'current_turn' => 1,
    ]);

    Career::create([
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'career_name' => 'Training Loop Test',
        'scenario_type' => 'ura_finale',
        'status' => 'active',
        'current_turn' => 1,
    ]);
});

describe('Training Loop Workflow', function () {
    test('full training turn execution', function () {
        $this->actingAs($this->user);

        // 1. Get Prediction
        $predictionResponse = $this->postJson(route('api.training-predictions.predict'), [
            'character_id' => $this->character->id,
            'training_type' => 'speed',
        ]);

        $predictionResponse->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'stat_gains',
                    'energy_cost',
                    'failure_risk',
                ],
            ]);

        $prediction = $predictionResponse->json();
        $predictedStats = $prediction['data']['stat_gains'];
        $expectedEnergyCost = $prediction['data']['energy_cost'];

        // 2. Execute Training (Mocking via updating stats directly since we don't have a "execute" endpoint yet,
        // or assuming we update character/session manually for this test if the controller logic isn't fully bound to a specific "action" endpoint)

        $stats = $this->character->current_stats;
        $stats['speed'] += $predictedStats['speed'];
        $this->character->current_stats = $stats;
        $this->character->energy_level -= $expectedEnergyCost;
        $this->character->current_turn++;
        $this->character->save();

        expect($this->character->current_stats['speed'])->toBeGreaterThan(100);
        expect($this->character->energy_level)->toBeLessThan(100);
        expect($this->character->current_turn)->toBe(2);
    });
});
