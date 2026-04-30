<?php

declare(strict_types=1);

namespace App\Livewire\Characters;

use App\Models\Character;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class CharacterIndex extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $scenario = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sortBy = 'updated_at';

    public function render(): View
    {
        return view('livewire.characters.index', [
            'characters' => $this->characters,
        ]);
    }

    public function getCharactersProperty(): Collection
    {
        $query = Character::query()
            ->select(['id', 'name', 'title', 'avatar_url', 'game_character_id', 'current_turn', 'current_stats', 'scenario_type', 'status', 'mood_status', 'is_pinned', 'is_seeded', 'user_id', 'updated_at', 'created_at'])
            ->where(function ($builder): void {
                $builder->where('user_id', Auth::id())
                    ->orWhere('is_seeded', true);
            })
            ->with(['gameCharacter', 'currentCareer']);

        if ($this->search !== '') {
            $query->where(function ($builder): void {
                $builder->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('title', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->scenario !== '') {
            $query->where('scenario_type', $this->scenario);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $query->orderByDesc('is_pinned');

        match ($this->sortBy) {
            'name' => $query->orderBy('name'),
            'created_at' => $query->orderByDesc('created_at'),
            default => $query->orderByDesc('updated_at'),
        };

        return $query->get();
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }
}
