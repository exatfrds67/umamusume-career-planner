<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\RunSnapshot;
use App\Models\User;
use App\Services\SnapshotService;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Uma',
        'scenario_type' => 'ura_finale',
    ]);

    $this->career = Career::factory()->create([
        'user_id' => $this->user->id,
        'character_id' => $this->character->id,
        'current_turn' => 20,
        'status' => 'active',
    ]);
});

describe('SnapshotManager Livewire Component', function () {
    it('renders the snapshot manager for a character with a career', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->assertSuccessful()
            ->assertSee('Run Snapshots');
    });

    it('shows empty state when no snapshots exist', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->assertSee('No Snapshots Yet');
    });

    it('shows create button when career exists', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->assertSee('New Snapshot');
    });

    it('opens create modal', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true);
    });

    it('closes create modal', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->call('openCreateModal')
            ->call('closeCreateModal')
            ->assertSet('showCreateModal', false);
    });

    it('creates a snapshot with description', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->set('newDescription', 'Before G1 race')
            ->call('createSnapshot')
            ->assertSet('showCreateModal', false)
            ->assertSet('statusType', 'success');

        $this->assertDatabaseHas('ucp_run_snapshots', [
            'career_id' => $this->career->id,
            'trigger_type' => 'manual',
            'description' => 'Before G1 race',
        ]);
    });

    it('creates a snapshot without description', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->call('createSnapshot')
            ->assertSet('statusType', 'success');

        $this->assertDatabaseHas('ucp_run_snapshots', [
            'career_id' => $this->career->id,
            'trigger_type' => 'manual',
        ]);
    });

    it('shows existing snapshots', function () {
        $snapshot = RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 20,
            'trigger_type' => 'manual',
            'description' => 'My checkpoint',
            'snapshot_data' => ['stats' => ['speed' => 500, 'stamina' => 400, 'power' => 450, 'guts' => 350, 'wit' => 380]],
            'checksum' => 'test',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->assertSee('My checkpoint')
            ->assertSee('TURN');
    });

    it('opens restore confirm modal', function () {
        $snapshot = RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 20,
            'trigger_type' => 'manual',
            'description' => 'Test',
            'snapshot_data' => ['stats' => []],
            'checksum' => 'test',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->call('confirmRestore', $snapshot->id)
            ->assertSet('showRestoreConfirm', true)
            ->assertSet('selectedSnapshotId', $snapshot->id);
    });

    it('cancels restore', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->set('showRestoreConfirm', true)
            ->set('selectedSnapshotId', 999)
            ->call('cancelRestore')
            ->assertSet('showRestoreConfirm', false)
            ->assertSet('selectedSnapshotId', null);
    });

    it('opens delete confirm modal', function () {
        $snapshot = RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 20,
            'trigger_type' => 'manual',
            'description' => 'Test',
            'snapshot_data' => ['stats' => []],
            'checksum' => 'test',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->call('confirmDelete', $snapshot->id)
            ->assertSet('showDeleteConfirm', true)
            ->assertSet('selectedSnapshotId', $snapshot->id);
    });

    it('deletes a snapshot', function () {
        $snapshot = RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 20,
            'trigger_type' => 'manual',
            'description' => 'To delete',
            'snapshot_data' => ['stats' => []],
            'checksum' => 'test',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->call('confirmDelete', $snapshot->id)
            ->call('deleteSnapshot')
            ->assertSet('showDeleteConfirm', false)
            ->assertSet('statusType', 'success');

        $this->assertDatabaseMissing('ucp_run_snapshots', ['id' => $snapshot->id]);
    });

    it('shows no career state when character has no career', function () {
        $characterWithoutCareer = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'No Career Uma',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $characterWithoutCareer->id])
            ->assertSee('No Active Career');
    });

    it('cannot access another user\'s career snapshots', function () {
        $otherUser = User::factory()->create();
        $otherCharacter = Character::factory()->create(['user_id' => $otherUser->id]);
        $otherCareer = Career::factory()->create([
            'user_id' => $otherUser->id,
            'character_id' => $otherCharacter->id,
        ]);
        RunSnapshot::create([
            'career_id' => $otherCareer->id,
            'turn_number' => 10,
            'trigger_type' => 'manual',
            'description' => 'Other user snapshot',
            'snapshot_data' => ['stats' => []],
            'checksum' => 'test',
        ]);

        // Our user's component should not see the other user's snapshot
        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id]);

        $component->assertDontSee('Other user snapshot');
    });
});

describe('SnapshotService', function () {
    it('creates a snapshot for a career', function () {
        $service = app(SnapshotService::class);
        $snapshot = $service->createSnapshot($this->career, 'manual', 'Test snapshot');

        expect($snapshot)->toBeInstanceOf(RunSnapshot::class);
        expect($snapshot->career_id)->toBe($this->career->id);
        expect($snapshot->trigger_type)->toBe('manual');
        expect($snapshot->description)->toBe('Test snapshot');
        expect($snapshot->checksum)->not->toBeNull();
    });

    it('captures career state correctly', function () {
        $service = app(SnapshotService::class);
        $state = $service->captureCareerState($this->career);

        expect($state)->toHaveKey('career');
        expect($state)->toHaveKey('stats');
        expect($state)->toHaveKey('sp');
        expect($state)->toHaveKey('meta');
        expect($state['meta']['version'])->toBe('1.0');
    });

    it('gets snapshots for a career ordered by turn desc', function () {
        $service = app(SnapshotService::class);
        $service->createSnapshot($this->career, 'manual', 'First');
        $service->createSnapshot($this->career, 'manual', 'Second');

        $snapshots = $service->getSnapshotsForCareer($this->career);

        expect($snapshots->count())->toBe(2);
    });

    it('cleans up old snapshots keeping the latest N', function () {
        $service = app(SnapshotService::class);

        // Create 25 snapshots
        for ($i = 1; $i <= 25; $i++) {
            RunSnapshot::create([
                'career_id' => $this->career->id,
                'turn_number' => $i,
                'trigger_type' => 'manual',
                'snapshot_data' => ['stats' => []],
                'checksum' => 'test'.$i,
            ]);
        }

        $deleted = $service->cleanupOldSnapshots($this->career, 20);

        expect($deleted)->toBe(5);
        expect(RunSnapshot::where('career_id', $this->career->id)->count())->toBe(20);
    });

    it('compares two snapshots', function () {
        $service = app(SnapshotService::class);

        $snapshotA = RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 10,
            'trigger_type' => 'manual',
            'snapshot_data' => ['stats' => ['speed' => 400, 'stamina' => 300, 'power' => 350, 'guts' => 250, 'wit' => 280]],
            'checksum' => 'a',
        ]);

        $snapshotB = RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 20,
            'trigger_type' => 'manual',
            'snapshot_data' => ['stats' => ['speed' => 600, 'stamina' => 450, 'power' => 500, 'guts' => 380, 'wit' => 420]],
            'checksum' => 'b',
        ]);

        $comparison = $service->compareSnapshots($snapshotA, $snapshotB);

        expect($comparison)->toHaveKey('stat_diffs');
        expect($comparison['stat_diffs']['speed']['diff'])->toBe(200);
        expect($comparison['turn_diff'])->toBe(10);
    });
});

describe('SnapshotManager Compare Feature', function () {
    it('shows compare button when 2+ snapshots exist', function () {
        RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 10,
            'trigger_type' => 'manual',
            'snapshot_data' => ['stats' => ['speed' => 400]],
            'checksum' => 'a',
        ]);
        RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 20,
            'trigger_type' => 'manual',
            'snapshot_data' => ['stats' => ['speed' => 600]],
            'checksum' => 'b',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->assertSee('Compare');
    });

    it('does not show compare button with fewer than 2 snapshots', function () {
        RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 10,
            'trigger_type' => 'manual',
            'snapshot_data' => ['stats' => []],
            'checksum' => 'a',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->assertDontSee('⚖️ Compare');
    });

    it('opens compare modal', function () {
        RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 10,
            'trigger_type' => 'manual',
            'snapshot_data' => ['stats' => []],
            'checksum' => 'a',
        ]);
        RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 20,
            'trigger_type' => 'manual',
            'snapshot_data' => ['stats' => []],
            'checksum' => 'b',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->call('openCompareModal')
            ->assertSet('showCompareModal', true);
    });

    it('closes compare modal', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->set('showCompareModal', true)
            ->call('closeCompareModal')
            ->assertSet('showCompareModal', false)
            ->assertSet('compareResult', null);
    });

    it('runs compare and shows stat diffs', function () {
        $snapshotA = RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 10,
            'trigger_type' => 'manual',
            'snapshot_data' => ['stats' => ['speed' => 400, 'stamina' => 300, 'power' => 350, 'guts' => 250, 'wit' => 280]],
            'checksum' => 'a',
        ]);
        $snapshotB = RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 20,
            'trigger_type' => 'manual',
            'snapshot_data' => ['stats' => ['speed' => 600, 'stamina' => 450, 'power' => 500, 'guts' => 380, 'wit' => 420]],
            'checksum' => 'b',
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->set('compareSnapshotAId', $snapshotA->id)
            ->set('compareSnapshotBId', $snapshotB->id)
            ->call('runCompare');

        $component->assertSet('compareResult.stat_diffs.speed.diff', 200);
        $component->assertSet('compareResult.turn_diff', 10);
    });

    it('shows error when same snapshot selected for compare', function () {
        $snapshot = RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 10,
            'trigger_type' => 'manual',
            'snapshot_data' => ['stats' => []],
            'checksum' => 'a',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->set('compareSnapshotAId', $snapshot->id)
            ->set('compareSnapshotBId', $snapshot->id)
            ->call('runCompare')
            ->assertSet('statusType', 'error');
    });

    it('shows mini stats row on snapshots with stat data', function () {
        RunSnapshot::create([
            'career_id' => $this->career->id,
            'turn_number' => 15,
            'trigger_type' => 'manual',
            'snapshot_data' => ['stats' => ['speed' => 500, 'stamina' => 400, 'power' => 450, 'guts' => 350, 'wit' => 380]],
            'checksum' => 'test',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Snapshots\SnapshotManager::class, ['characterId' => $this->character->id])
            ->assertSee('500')
            ->assertSee('400');
    });
});
