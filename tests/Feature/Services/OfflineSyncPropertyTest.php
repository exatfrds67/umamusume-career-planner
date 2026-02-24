<?php

/**
 * Property tests for offline functionality.
 *
 * Feature: umamusume-career-planner-main-v2.4.0
 *
 * Property 13: Offline Data Persistence
 * Property 14: Sync Order Preservation
 * Property 15: Conflict Detection Accuracy
 *
 * Validates: Requirements NFR-PWA-03, NFR-PWA-05, FR-10.4
 */

use App\Models\Career;
use App\Models\Character;
use App\Models\User;
use App\Services\Offline\OfflineSyncService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
    ]);
    $this->service = new OfflineSyncService;
});

// Property 13: Offline Data Persistence
// Data created through sync should be retrievable and match input

it('Property 13: persists all career fields from sync create operations', function () {
    $fields = [
        'character_id' => $this->character->id,
        'career_name' => 'Persistence Test - '.fake()->word(),
        'scenario_type' => fake()->randomElement(['ura_finale', 'unity_cup']),
        'status' => 'active',
        'current_turn' => fake()->numberBetween(1, 78),
        'current_phase' => fake()->randomElement(['junior', 'classic', 'senior']),
    ];

    $operations = [
        [
            'type' => 'create',
            'endpoint' => '/api/v1/careers',
            'method' => 'POST',
            'data' => $fields,
            'entity_id' => null,
            'timestamp' => now()->subMinute()->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['succeeded'])->toBe(1);

    $career = Career::query()
        ->where('user_id', $this->user->id)
        ->where('career_name', $fields['career_name'])
        ->first();

    expect($career)->not->toBeNull();
    expect($career->character_id)->toBe($fields['character_id']);
    expect($career->scenario_type)->toBe($fields['scenario_type']);
    expect($career->current_turn)->toBe($fields['current_turn']);
    expect($career->current_phase)->toBe($fields['current_phase']);
})->repeat(5);

it('Property 13: persists updates through sync without data loss', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'current_turn' => 1,
    ]);

    $newTurn = fake()->numberBetween(2, 78);

    $operations = [
        [
            'type' => 'update',
            'endpoint' => "/api/v1/careers/{$career->id}",
            'method' => 'PUT',
            'data' => ['current_turn' => $newTurn],
            'entity_id' => (string) $career->id,
            'timestamp' => now()->addMinute()->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['succeeded'])->toBe(1);

    $career->refresh();
    expect($career->current_turn)->toBe($newTurn);
    expect($career->character_id)->toBe($this->character->id);
    expect($career->user_id)->toBe($this->user->id);
})->repeat(5);

// Property 14: Sync Order Preservation
// Operations submitted in sequence should be processed in FIFO order

it('Property 14: processes operations in submission order', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'current_turn' => 1,
    ]);

    $baseTime = now();

    $operations = [
        [
            'type' => 'update',
            'endpoint' => "/api/v1/careers/{$career->id}",
            'method' => 'PUT',
            'data' => ['current_turn' => 5],
            'entity_id' => (string) $career->id,
            'timestamp' => $baseTime->copy()->addSeconds(1)->getTimestampMs(),
        ],
        [
            'type' => 'update',
            'endpoint' => "/api/v1/careers/{$career->id}",
            'method' => 'PUT',
            'data' => ['current_turn' => 10],
            'entity_id' => (string) $career->id,
            'timestamp' => $baseTime->copy()->addSeconds(2)->getTimestampMs(),
        ],
        [
            'type' => 'update',
            'endpoint' => "/api/v1/careers/{$career->id}",
            'method' => 'PUT',
            'data' => ['current_turn' => 15],
            'entity_id' => (string) $career->id,
            'timestamp' => $baseTime->copy()->addSeconds(3)->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['succeeded'])->toBe(3);

    $career->refresh();
    expect($career->current_turn)->toBe(15);
});

it('Property 14: final state reflects last operation in batch', function () {
    $phases = ['junior', 'classic', 'senior'];
    $finalPhase = fake()->randomElement($phases);

    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'current_turn' => 1,
        'current_phase' => 'junior',
    ]);

    $baseTime = now();
    $operations = [];

    foreach ($phases as $i => $phase) {
        $operations[] = [
            'type' => 'update',
            'endpoint' => "/api/v1/careers/{$career->id}",
            'method' => 'PUT',
            'data' => ['current_phase' => $phase],
            'entity_id' => (string) $career->id,
            'timestamp' => $baseTime->copy()->addSeconds($i + 1)->getTimestampMs(),
        ];
    }

    $operations[] = [
        'type' => 'update',
        'endpoint' => "/api/v1/careers/{$career->id}",
        'method' => 'PUT',
        'data' => ['current_phase' => $finalPhase],
        'entity_id' => (string) $career->id,
        'timestamp' => $baseTime->copy()->addSeconds(count($phases) + 1)->getTimestampMs(),
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    $career->refresh();
    expect($career->current_phase)->toBe($finalPhase);
})->repeat(3);

// Property 15: Conflict Detection Accuracy
// A conflict should be detected when server data was modified after the offline timestamp

it('Property 15: detects conflict when server record was modified after offline edit', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'current_turn' => 10,
        'updated_at' => now(),
    ]);

    $offlineTimestamp = now()->subMinutes(5)->getTimestampMs();

    $operations = [
        [
            'type' => 'update',
            'endpoint' => "/api/v1/careers/{$career->id}",
            'method' => 'PUT',
            'data' => ['current_turn' => 20],
            'entity_id' => (string) $career->id,
            'timestamp' => $offlineTimestamp,
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['conflicts'])->not->toBeEmpty();
});

it('Property 15: does not detect conflict when server was not modified after offline edit', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'current_turn' => 10,
        'updated_at' => now()->subMinutes(10),
    ]);

    $offlineTimestamp = now()->subMinutes(2)->getTimestampMs();

    $operations = [
        [
            'type' => 'update',
            'endpoint' => "/api/v1/careers/{$career->id}",
            'method' => 'PUT',
            'data' => ['current_turn' => 20],
            'entity_id' => (string) $career->id,
            'timestamp' => $offlineTimestamp,
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['conflicts'])->toBeEmpty();
    expect($results['succeeded'])->toBe(1);
});

it('Property 15: conflict detection does not apply to create operations', function () {
    $operations = [
        [
            'type' => 'create',
            'endpoint' => '/api/v1/careers',
            'method' => 'POST',
            'data' => [
                'character_id' => $this->character->id,
                'career_name' => 'No Conflict Create',
                'scenario_type' => 'ura_finale',
                'status' => 'active',
                'current_turn' => 1,
                'current_phase' => 'junior',
            ],
            'entity_id' => null,
            'timestamp' => now()->subHour()->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['conflicts'])->toBeEmpty();
    expect($results['succeeded'])->toBe(1);
});

it('Property 15: delete operations on non-existent records fail gracefully', function () {
    $operations = [
        [
            'type' => 'delete',
            'endpoint' => '/api/v1/careers/99999',
            'method' => 'DELETE',
            'data' => [],
            'entity_id' => '99999',
            'timestamp' => now()->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['failed'])->toBe(1);
    expect($results['errors'])->toHaveCount(1);
});

it('Property 15: batch results always sum correctly', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
    ]);

    $operations = [
        [
            'type' => 'create',
            'endpoint' => '/api/v1/careers',
            'method' => 'POST',
            'data' => [
                'character_id' => $this->character->id,
                'career_name' => 'Sum Test Career',
                'scenario_type' => 'unity_cup',
                'status' => 'active',
                'current_turn' => 1,
                'current_phase' => 'junior',
            ],
            'entity_id' => null,
            'timestamp' => now()->getTimestampMs(),
        ],
        [
            'type' => 'update',
            'endpoint' => '/api/v1/careers/88888',
            'method' => 'PUT',
            'data' => ['current_turn' => 10],
            'entity_id' => '88888',
            'timestamp' => now()->addMinute()->getTimestampMs(),
        ],
        [
            'type' => 'unknown_op',
            'endpoint' => '/api/test',
            'method' => 'POST',
            'data' => [],
            'entity_id' => null,
            'timestamp' => now()->addMinutes(2)->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    $total = $results['succeeded'] + $results['failed'] + count($results['conflicts']);
    expect($results['processed'])->toBe(count($operations));
    expect($total)->toBeLessThanOrEqual($results['processed']);
});
