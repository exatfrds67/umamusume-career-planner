<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\Character;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class DashboardIndex extends Component
{
    public ?int $selectedCharacterId = null;

    public function mount(): void
    {
        $activeCharacterId = Session::get('current_character_id');

        if (is_numeric($activeCharacterId)) {
            $this->selectedCharacterId = (int) $activeCharacterId;

            return;
        }

        $this->selectedCharacterId = $this->characters()->first()?->id;
    }

    public function selectCharacter(string|int $characterId): void
    {
        $characterId = (int) $characterId;

        if (! $this->characters()->contains('id', $characterId)) {
            return;
        }

        $this->selectedCharacterId = $characterId;
        Session::put('current_character_id', $characterId);
    }

    public function getCharactersProperty(): Collection
    {
        return $this->characters();
    }

    public function getSelectedCharacterProperty(): ?Character
    {
        return $this->characters()->firstWhere('id', $this->selectedCharacterId) ?? $this->characters()->first();
    }

    public function getStatsProperty(): array
    {
        return $this->normalizedStats($this->selectedCharacter?->current_stats ?? []);
    }

    public function getGoalsProperty(): array
    {
        $goals = $this->selectedCharacter?->goals;

        if (! is_array($goals) || $goals === []) {
            return [];
        }

        return collect($goals)
            ->map(function (mixed $goal, int|string $key): array {
                if (! is_array($goal)) {
                    return [];
                }

                $current = (int) ($goal['current'] ?? 0);
                $target = max(1, (int) ($goal['target'] ?? 1));
                $status = (string) ($goal['status'] ?? 'on_track');

                return [
                    'id' => is_int($key) ? $key : (int) $key,
                    'label' => (string) ($goal['label'] ?? 'Goal'),
                    'current' => $current,
                    'target' => $target,
                    'status' => $status,
                    'progress' => min(100, (int) round(($current / $target) * 100)),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getTrainingSuggestionsProperty(): array
    {
        $ordered = collect($this->stats)->sort()->keys()->values();

        return $ordered
            ->take(3)
            ->map(function (string $stat) use ($ordered): array {
                $value = (int) ($this->stats[$stat] ?? 0);
                $label = ucfirst($stat);

                return [
                    'label' => $label,
                    'action' => 'Train '.$label,
                    'stat' => $stat,
                    'value' => $value,
                    'risk' => $value < 300 ? 'high' : ($value < 500 ? 'medium' : 'low'),
                    'gains' => '+'.max(18, 60 - intdiv($value, 20)).' '.$label,
                    'recommended' => $stat === $ordered->first(),
                ];
            })
            ->values()
            ->all();
    }

    public function getUpcomingRacesProperty(): array
    {
        $schedule = $this->selectedCharacter?->race_schedule;

        if (! is_array($schedule) || $schedule === []) {
            return [];
        }

        return collect($schedule)
            ->map(function (mixed $race, int|string $key): array {
                if (! is_array($race)) {
                    return [];
                }

                $readiness = (int) ($race['readiness'] ?? 0);
                $turn = (int) ($race['turn'] ?? 0);
                $currentTurn = (int) ($this->selectedCharacter?->current_turn ?? 0);
                $turnsAway = max(0, $turn - $currentTurn);

                return [
                    'id' => is_int($key) ? $key : (int) $key,
                    'name' => (string) ($race['name'] ?? 'Upcoming Race'),
                    'grade' => (string) ($race['grade'] ?? 'G3'),
                    'distance' => (string) ($race['distance'] ?? 'Unknown distance'),
                    'surface' => (string) ($race['surface'] ?? 'Turf'),
                    'weather' => (string) ($race['weather'] ?? 'Sunny'),
                    'turn' => $turn,
                    'turnsAway' => $turnsAway,
                    'readiness' => $readiness,
                    'winProb' => (int) ($race['winProb'] ?? max(10, $readiness - 30)),
                    'required' => (bool) ($race['required'] ?? false),
                    'isGoalRace' => (bool) ($race['required'] ?? false),
                ];
            })
            ->filter()
            ->take(3)
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.dashboard.index', [
            'characters' => $this->characters,
            'selectedCharacter' => $this->selectedCharacter,
            'stats' => $this->stats,
            'goals' => $this->goals,
            'trainingSuggestions' => $this->trainingSuggestions,
            'upcomingRaces' => $this->upcomingRaces,
        ]);
    }

    private function characters(): Collection
    {
        if (! Auth::check()) {
            return Character::query()
                ->select(['id', 'name', 'avatar_url', 'game_character_id', 'current_turn', 'current_stats', 'goals', 'race_schedule', 'scenario_type', 'status', 'mood_status', 'energy_level', 'available_sp', 'is_seeded', 'updated_at'])
                ->where('is_seeded', true)
                ->orderByDesc('updated_at')
                ->get();
        }

        return Character::query()
            ->select(['id', 'name', 'avatar_url', 'game_character_id', 'current_turn', 'current_stats', 'goals', 'race_schedule', 'scenario_type', 'status', 'mood_status', 'energy_level', 'available_sp', 'is_pinned', 'is_seeded', 'user_id', 'updated_at'])
            ->where(function ($query): void {
                $query->where('user_id', Auth::id())
                    ->orWhere('is_seeded', true);
            })
            ->with(['currentCareer', 'gameCharacter'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * @param  array<string, mixed>|array<int, mixed>  $stats
     * @return array{speed:int, stamina:int, power:int, guts:int, wit:int}
     */
    private function normalizedStats(array $stats): array
    {
        return [
            'speed' => (int) ($stats['speed'] ?? 0),
            'stamina' => (int) ($stats['stamina'] ?? 0),
            'power' => (int) ($stats['power'] ?? 0),
            'guts' => (int) ($stats['guts'] ?? 0),
            'wit' => (int) ($stats['wit'] ?? ($stats['wisdom'] ?? 0)),
        ];
    }
}
