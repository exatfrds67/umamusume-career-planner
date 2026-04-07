<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class TrainingSuggestionPanel extends Component
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public array $suggestions = [];

    public ?int $selectedIndex = null;

    public function selectSuggestion(int $index): void
    {
        if ($this->selectedIndex === $index) {
            $this->selectedIndex = null;

            return;
        }

        if (! isset($this->suggestions[$index])) {
            return;
        }

        $this->selectedIndex = $index;
    }

    public function clearSelection(): void
    {
        $this->selectedIndex = null;
    }

    public function render(): View
    {
        return view('livewire.dashboard.training-suggestion-panel');
    }
}
