<?php

declare(strict_types=1);

namespace App\Livewire\Races;

use App\Models\Character;
use App\Services\RaceExecutionService;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Races')]
class RaceIndex extends Component
{
    public string $activeTab = 'upcoming';

    public ?int $selectedRaceId = null;

    public function mount(): void
    {
        // Component initialized
    }

    public function selectRace(int $raceId): void
    {
        $this->selectedRaceId = $raceId;
    }

    public function switchTab(string $tab): void
    {
        if (\in_array($tab, ['upcoming', 'history'], true)) {
            $this->activeTab = $tab;
            $this->selectedRaceId = null;
        }
    }

    public function render(RaceExecutionService $raceExecutionService): \Illuminate\View\View
    {
        $character = null;
        if (auth()->check()) {
            $character = Character::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->first();
        }

        $upcomingRaces = [];
        $historyRaces = collect();

        if ($character) {
            // Get upcoming races for the character (returns plain array)
            $upcomingRaces = $raceExecutionService->getUpcomingRaces($character);

            // Get race history for the character
            $historyRaces = $character->races()
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
        }

        $selectedRace = null;
        if ($this->selectedRaceId) {
            if ($this->activeTab === 'upcoming') {
                // $upcomingRaces is a plain array from getUpcomingRaces()
                foreach ($upcomingRaces as $race) {
                    if (($race['id'] ?? null) === $this->selectedRaceId) {
                        $selectedRace = $race;
                        break;
                    }
                }
            } else {
                $selectedRace = $historyRaces->firstWhere('id', $this->selectedRaceId);
            }
        }

        return view('livewire.races.race-index', [
            'character' => $character,
            'upcomingRaces' => $upcomingRaces,
            'historyRaces' => $historyRaces,
            'selectedRace' => $selectedRace,
            'activeTab' => $this->activeTab,
            'selectedRaceId' => $this->selectedRaceId,
        ]);
    }
}
