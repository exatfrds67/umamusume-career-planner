<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MemoriesGrid extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  array<int, array{id: string, title: string, icon?: string, url?: string, isLocked?: bool, isNew?: bool}>  $items  Grid items
     * @param  string  $title  Grid section title
     * @param  int  $columns  Number of columns (3 for 3x2 grid)
     * @param  bool  $showLockIcons  Whether to show lock/unlock indicators
     */
    public function __construct(
        public array $items = [],
        public string $title = 'Memories',
        public int $columns = 3,
        public bool $showLockIcons = true,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.memories-grid');
    }

    /**
     * Get grid column classes.
     */
    public function gridClasses(): string
    {
        return match ($this->columns) {
            2 => 'grid-cols-2',
            4 => 'grid-cols-2 md:grid-cols-4',
            default => 'grid-cols-3',
        };
    }

    /**
     * Get lock status for item.
     */
    public function isLocked(array $item): bool
    {
        return isset($item['isLocked']) && $item['isLocked'];
    }

    /**
     * Get new flag for item.
     */
    public function isNew(array $item): bool
    {
        return isset($item['isNew']) && $item['isNew'];
    }

    /**
     * Get item URL or empty string.
     */
    public function itemUrl(array $item): string
    {
        return $item['url'] ?? '';
    }
}
