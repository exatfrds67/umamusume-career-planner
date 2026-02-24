<?php

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

it('syncs a create operation', function () {
    $operations = [
        [
            'type' => 'create',
            'endpoint' => '/api/v1/careers',
            'method' => 'POST',
            'data' => [
                'character_id' => $this->character->id,
                'career_name' => 'Test Career',
                'scenario_type' => 'ura_finale',
                'status' => 'active',
                'current_turn' => 1,
                'current_phase' => 'junior',
            ],
            'entity_id' => null,
            'timestamp' => now()->subMinute()->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['processed'])->toBe(1);
    expect($results['succeeded'])->toBe(1);
    expect($results['failed'])->toBe(0);
    expect($results['conflicts'])->toBeEmpty();
});

it('syncs an update operation', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'current_turn' => 5,
    ]);

    $operations = [
        [
            'type' => 'update',
            'endpoint' => "/api/v1/careers/{$career->id}",
            'method' => 'PUT',
            'data' => ['current_turn' => 10],
            'entity_id' => (string) $career->id,
            'timestamp' => now()->addMinute()->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['succeeded'])->toBe(1);

    $career->refresh();
    expect($career->current_turn)->toBe(10);
});

it('syncs a delete operation', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
    ]);

    $operations = [
        [
            'type' => 'delete',
            'endpoint' => "/api/v1/careers/{$career->id}",
            'method' => 'DELETE',
            'data' => [],
            'entity_id' => (string) $career->id,
            'timestamp' => now()->addMinute()->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['succeeded'])->toBe(1);
    expect(Career::find($career->id))->toBeNull();
});

it('detects a conflict when server data is newer', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'current_turn' => 5,
        'updated_at' => now(),
    ]);

    $operation = [
        'type' => 'update',
        'entity_id' => (string) $career->id,
        'timestamp' => now()->subHour()->getTimestampMs(),
        'data' => ['current_turn' => 8],
    ];

    $conflict = $this->service->detectConflict($this->user, $operation);

    expect($conflict)->not->toBeNull();
    expect($conflict['entity_id'])->toBe((string) $career->id);
    expect($conflict['type'])->toBe('update');
});

it('does not detect conflict when offline data is newer', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'updated_at' => now()->subHour(),
    ]);

    $operation = [
        'type' => 'update',
        'entity_id' => (string) $career->id,
        'timestamp' => now()->getTimestampMs(),
        'data' => ['current_turn' => 8],
    ];

    $conflict = $this->service->detectConflict($this->user, $operation);

    expect($conflict)->toBeNull();
});

it('does not detect conflict for create operations', function () {
    $operation = [
        'type' => 'create',
        'entity_id' => null,
        'timestamp' => now()->getTimestampMs(),
        'data' => ['character_id' => $this->character->id],
    ];

    $conflict = $this->service->detectConflict($this->user, $operation);

    expect($conflict)->toBeNull();
});

it('resolves conflict with server_wins strategy', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'current_turn' => 10,
    ]);

    $conflict = [
        'entity_id' => (string) $career->id,
        'offline_data' => ['current_turn' => 5],
    ];

    $result = $this->service->resolveConflict($this->user, $conflict, 'server_wins');

    expect($result['resolved'])->toBeTrue();
    expect($result['strategy'])->toBe('server_wins');

    $career->refresh();
    expect($career->current_turn)->toBe(10);
});

it('resolves conflict with client_wins strategy', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'current_turn' => 10,
    ]);

    $conflict = [
        'entity_id' => (string) $career->id,
        'offline_data' => ['current_turn' => 15],
    ];

    $result = $this->service->resolveConflict($this->user, $conflict, 'client_wins');

    expect($result['resolved'])->toBeTrue();
    expect($result['strategy'])->toBe('client_wins');

    $career->refresh();
    expect($career->current_turn)->toBe(15);
});

it('resolves conflict with merge strategy', function () {
    $career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'current_turn' => 10,
    ]);

    $conflict = [
        'entity_id' => (string) $career->id,
        'offline_data' => ['current_turn' => 15, 'current_phase' => null],
    ];

    $result = $this->service->resolveConflict($this->user, $conflict, 'merge');

    expect($result['resolved'])->toBeTrue();

    $career->refresh();
    expect($career->current_turn)->toBe(15);
});

it('handles batch with mixed results', function () {
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
                'career_name' => 'Batch Test Career',
                'scenario_type' => 'ura_finale',
                'status' => 'active',
                'current_turn' => 1,
                'current_phase' => 'junior',
            ],
            'entity_id' => null,
            'timestamp' => now()->getTimestampMs(),
        ],
        [
            'type' => 'update',
            'endpoint' => '/api/v1/careers/99999',
            'method' => 'PUT',
            'data' => ['current_turn' => 10],
            'entity_id' => '99999',
            'timestamp' => now()->addMinute()->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['processed'])->toBe(2);
    expect($results['succeeded'])->toBe(1);
    expect($results['failed'])->toBe(1);
});

it('fails on unknown operation type', function () {
    $operations = [
        [
            'type' => 'unknown_op',
            'endpoint' => '/api/test',
            'method' => 'POST',
            'data' => [],
            'entity_id' => null,
            'timestamp' => now()->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['failed'])->toBe(1);
    expect($results['errors'])->toHaveCount(1);
});

it('prevents syncing operations for another users career', function () {
    $otherUser = User::factory()->create();
    $otherChar = Character::factory()->create(['user_id' => $otherUser->id]);
    $career = Career::factory()->create([
        'character_id' => $otherChar->id,
        'user_id' => $otherUser->id,
        'status' => 'active',
    ]);

    $operations = [
        [
            'type' => 'update',
            'endpoint' => "/api/v1/careers/{$career->id}",
            'method' => 'PUT',
            'data' => ['current_turn' => 10],
            'entity_id' => (string) $career->id,
            'timestamp' => now()->addMinute()->getTimestampMs(),
        ],
    ];

    $results = $this->service->syncBatch($this->user, $operations);

    expect($results['failed'])->toBe(1);
});
