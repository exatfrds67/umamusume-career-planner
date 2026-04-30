<?php

declare(strict_types=1);

namespace App\Livewire\Races;

use App\Models\GameRace;
use App\Services\RaceExecutionService;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class RaceEntryModal extends Component
{
    public string $step = 'enter'; // enter | result

    #[Validate('required|integer|between:1,8')]
    public int $placement = 0;

    public ?int $raceId = null;

    public bool $showModal = false;

    public function mount(): void
    {
        // Component initialized
    }

    #[On('open-race-modal')]
    public function openModal(int $raceId): void
    {
        $this->raceId = $raceId;
        $this->showModal = true;
        $this->step = 'enter';
        $this->placement = 0;
    }

    public function selectPlacement(int $placement): void
    {
        $this->placement = $placement;
    }

    public function recordResult(RaceExecutionService $raceService): void
    {
        $this->validate();

        if (! $this->raceId || ! auth()->check()) {
            return;
        }

        try {
            /** @var \App\Models\User $user */
            $user = auth()->user();
            $character = $user->characters()
                ->orderBy('created_at', 'desc')
                ->first();

            $result = $raceService->recordResult(
                characterId: $character !== null ? $character->id : 0,
                raceId: $this->raceId,
                placement: $this->placement
            );

            if ($result) {
                $this->step = 'result';
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to record race result: '.$e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset();
    }

    public function render(): \Illuminate\View\View
    {
        $race = null;
        if ($this->raceId) {
            $race = GameRace::find($this->raceId);
        }

        $rewardPreview = null;
        if ($this->placement > 0) {
            $rewardPreview = $this->calculateRewards($this->placement, $race);
        }

        return view('livewire.races.race-entry-modal', [
            'race' => $race,
            'placement' => $this->placement,
            'rewardPreview' => $rewardPreview,
            'step' => $this->step,
        ]);
    }

    /**
     * @return array{fans: int, sp: int, statBonus: int, placement: int}
     */
    protected function calculateRewards(int $placement, ?GameRace $race = null): array
    {
        $grade = $race !== null ? $race->grade : 'G3';

        $gradeBase = match ($grade) {
            'G1' => ['fans' => 3200, 'sp' => 180],
            'G2' => ['fans' => 1800, 'sp' => 120],
            default => ['fans' => 900, 'sp' => 80],
        };

        $placementMultipliers = [1.0, 0.65, 0.4, 0.2, 0.1, 0.05, 0.02, 0.01];
        $multiplier = $placementMultipliers[$placement - 1] ?? 0.0;

        $fans = (int) round($gradeBase['fans'] * $multiplier);
        $sp = $placement <= 3 ? (int) round($gradeBase['sp'] * $multiplier) : 0;
        $statBonus = $placement === 1 ? 8 : ($placement <= 3 ? 4 : 0);

        return [
            'fans' => $fans,
            'sp' => $sp,
            'statBonus' => $statBonus,
            'placement' => $placement,
        ];
    }
}
