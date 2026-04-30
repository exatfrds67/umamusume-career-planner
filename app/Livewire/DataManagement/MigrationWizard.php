<?php

declare(strict_types=1);

namespace App\Livewire\DataManagement;

use App\Models\Career;
use App\Models\Character;
use App\Services\DataMigrationService;
use Livewire\Component;

/**
 * MigrationWizard Livewire Component
 *
 * 4-step wizard for converting local browser data to account-backed storage.
 * Steps: Validate → Preview → Migrate → Done
 */
class MigrationWizard extends Component
{
    /** @var 'idle'|'validate'|'preview'|'migrate'|'done' */
    public string $step = 'idle';

    public bool $keepLocalCopy = true;

    public int $migratedCount = 0;

    public int $totalCount = 0;

    /** @var array<int, array{name: string, status: string}> */
    public array $previewItems = [];

    /** @var array<int, array{name: string, status: string}> */
    public array $migratedItems = [];

    /** @var array<string, mixed> */
    public array $validationResult = [];

    public string $errorMessage = '';

    public function startMigration(): void
    {
        $this->step = 'validate';
        $this->errorMessage = '';

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Count local characters (those without a completed migration)
        $characterCount = Character::where('user_id', $user->id)->count();
        $careerCount = Career::where('user_id', $user->id)->count();

        $this->validationResult = [
            'characters' => $characterCount,
            'careers' => $careerCount,
            'builds' => 0, // Support card builds
            'total' => $characterCount + $careerCount,
        ];
    }

    public function goToPreview(): void
    {
        $this->step = 'preview';

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $characters = Character::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $this->previewItems = $characters->map(fn (Character $c) => [
            'name' => $c->name,
            'status' => 'ready',
        ])->values()->toArray();

        $this->totalCount = \count($this->previewItems);
    }

    public function runMigration(): void
    {
        $this->step = 'migrate';
        $this->migratedCount = 0;
        $this->migratedItems = [];

        // Simulate migration — in a real implementation this would call DataMigrationService
        // For now we mark all preview items as migrated
        foreach ($this->previewItems as $item) {
            $this->migratedItems[] = [
                'name' => $item['name'],
                'status' => 'migrated',
            ];
            $this->migratedCount++;
        }

        $this->step = 'done';
    }

    public function cancelWizard(): void
    {
        $this->step = 'idle';
        $this->migratedCount = 0;
        $this->totalCount = 0;
        $this->previewItems = [];
        $this->migratedItems = [];
        $this->validationResult = [];
        $this->errorMessage = '';
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.data-management.migration-wizard');
    }
}
