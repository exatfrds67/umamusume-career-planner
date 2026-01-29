<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * DeckSlot Component
 *
 * Displays a single slot in the 6-slot support deck grid.
 * Handles empty slots, filled slots, and click interactions.
 */
class DeckSlot extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  array|null  $card  Support card data (null for empty slot)
     * @param  int  $position  Slot position (1-6)
     * @param  bool  $clickable  Whether the slot is clickable
     * @param  string  $size  Slot size (sm, md, lg)
     */
    public function __construct(
        public ?array $card = null,
        public int $position = 1,
        public bool $clickable = true,
        public string $size = 'md'
    ) {}

    /**
     * Get the slot label based on position.
     */
    public function getSlotLabel(): string
    {
        return match ($this->position) {
            1, 2, 3 => "Main Slot {$this->position}",
            4, 5, 6 => 'Sub Slot '.($this->position - 3),
            default => "Slot {$this->position}",
        };
    }

    /**
     * Check if this is a main slot (1-3).
     */
    public function isMainSlot(): bool
    {
        return $this->position >= 1 && $this->position <= 3;
    }

    /**
     * Get size classes for the slot.
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'h-32 w-24',
            'md' => 'h-40 w-28',
            'lg' => 'h-48 w-36',
            default => 'h-40 w-28',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.deck-slot');
    }
}
