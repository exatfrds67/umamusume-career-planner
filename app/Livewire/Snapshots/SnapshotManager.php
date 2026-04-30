<?php

declare(strict_types=1);

namespace App\Livewire\Snapshots;

use App\Models\Career;
use App\Models\Character;
use App\Models\RunSnapshot;
use App\Services\SnapshotService;
use Livewire\Component;

/**
 * SnapshotManager Livewire Component
 *
 * Manages run snapshots for a character's career — create, browse, restore, delete.
 */
class SnapshotManager extends Component
{
    public int $characterId;

    public ?int $careerId = null;

    public string $newDescription = '';

    public bool $showCreateModal = false;

    public bool $showRestoreConfirm = false;

    public bool $showDeleteConfirm = false;

    public ?int $selectedSnapshotId = null;

    public string $statusMessage = '';

    public string $statusType = ''; // 'success' | 'error'

    public bool $showCompareModal = false;

    public ?int $compareSnapshotAId = null;

    public ?int $compareSnapshotBId = null;

    /** @var array<string, mixed>|null */
    public ?array $compareResult = null;

    public function mount(int $characterId): void
    {
        $this->characterId = $characterId;

        // Load the most recent active career for this character
        $career = Career::where('character_id', $characterId)
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->first();

        $this->careerId = $career?->id;
    }

    /**
     * @return \Illuminate\Support\Collection<int, RunSnapshot>
     */
    private function loadSnapshots(): \Illuminate\Support\Collection
    {
        if ($this->careerId === null) {
            return collect();
        }

        $career = Career::where('id', $this->careerId)
            ->where('user_id', auth()->id())
            ->first();

        if ($career === null) {
            return collect();
        }

        return app(SnapshotService::class)->getSnapshotsForCareer($career);
    }

    public function openCreateModal(): void
    {
        $this->newDescription = '';
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->newDescription = '';
    }

    public function createSnapshot(): void
    {
        if ($this->careerId === null) {
            $this->setStatus('No active career found for this character.', 'error');

            return;
        }

        $career = Career::where('id', $this->careerId)
            ->where('user_id', auth()->id())
            ->first();

        if ($career === null) {
            $this->setStatus('Career not found.', 'error');

            return;
        }

        $description = trim($this->newDescription) ?: null;

        app(SnapshotService::class)->createSnapshot($career, 'manual', $description);

        $this->showCreateModal = false;
        $this->newDescription = '';
        $this->setStatus('Snapshot created successfully.', 'success');
    }

    public function confirmRestore(int $snapshotId): void
    {
        $this->selectedSnapshotId = $snapshotId;
        $this->showRestoreConfirm = true;
    }

    public function cancelRestore(): void
    {
        $this->showRestoreConfirm = false;
        $this->selectedSnapshotId = null;
    }

    public function restoreSnapshot(): void
    {
        if ($this->selectedSnapshotId === null || $this->careerId === null) {
            $this->setStatus('Invalid restore request.', 'error');

            return;
        }

        $career = Career::where('id', $this->careerId)
            ->where('user_id', auth()->id())
            ->first();

        if ($career === null) {
            $this->setStatus('Career not found.', 'error');

            return;
        }

        $snapshot = $career->runSnapshots()->find($this->selectedSnapshotId);

        if ($snapshot === null) {
            $this->setStatus('Snapshot not found.', 'error');

            return;
        }

        $result = app(SnapshotService::class)->restoreSnapshot($snapshot);

        $this->showRestoreConfirm = false;
        $this->selectedSnapshotId = null;

        $this->setStatus($result['message'], $result['success'] ? 'success' : 'error');
    }

    public function confirmDelete(int $snapshotId): void
    {
        $this->selectedSnapshotId = $snapshotId;
        $this->showDeleteConfirm = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->selectedSnapshotId = null;
    }

    public function deleteSnapshot(): void
    {
        if ($this->selectedSnapshotId === null || $this->careerId === null) {
            $this->setStatus('Invalid delete request.', 'error');

            return;
        }

        $career = Career::where('id', $this->careerId)
            ->where('user_id', auth()->id())
            ->first();

        if ($career === null) {
            $this->setStatus('Career not found.', 'error');

            return;
        }

        $snapshot = $career->runSnapshots()->find($this->selectedSnapshotId);

        if ($snapshot === null) {
            $this->setStatus('Snapshot not found.', 'error');

            return;
        }

        $snapshot->delete();

        $this->showDeleteConfirm = false;
        $this->selectedSnapshotId = null;
        $this->setStatus('Snapshot deleted.', 'success');
    }

    public function cleanupOldSnapshots(): void
    {
        if ($this->careerId === null) {
            return;
        }

        $career = Career::where('id', $this->careerId)
            ->where('user_id', auth()->id())
            ->first();

        if ($career === null) {
            return;
        }

        $deleted = app(SnapshotService::class)->cleanupOldSnapshots($career, 20);
        $this->setStatus("{$deleted} old snapshot(s) removed.", 'success');
    }

    public function openCompareModal(): void
    {
        $this->compareSnapshotAId = null;
        $this->compareSnapshotBId = null;
        $this->compareResult = null;
        $this->showCompareModal = true;
    }

    public function closeCompareModal(): void
    {
        $this->showCompareModal = false;
        $this->compareResult = null;
    }

    public function runCompare(): void
    {
        if ($this->compareSnapshotAId === null || $this->compareSnapshotBId === null) {
            $this->setStatus('Please select two snapshots to compare.', 'error');

            return;
        }

        if ($this->compareSnapshotAId === $this->compareSnapshotBId) {
            $this->setStatus('Please select two different snapshots.', 'error');

            return;
        }

        if ($this->careerId === null) {
            return;
        }

        $career = Career::where('id', $this->careerId)
            ->where('user_id', auth()->id())
            ->first();

        if ($career === null) {
            return;
        }

        $snapshotA = $career->runSnapshots()->find($this->compareSnapshotAId);
        $snapshotB = $career->runSnapshots()->find($this->compareSnapshotBId);

        if ($snapshotA === null || $snapshotB === null) {
            $this->setStatus('One or both snapshots not found.', 'error');

            return;
        }

        $this->compareResult = app(SnapshotService::class)->compareSnapshots($snapshotA, $snapshotB);
    }

    private function setStatus(string $message, string $type): void
    {
        $this->statusMessage = $message;
        $this->statusType = $type;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.snapshots.snapshot-manager', [
            'snapshots' => $this->loadSnapshots(),
            'character' => Character::find($this->characterId),
        ]);
    }
}
