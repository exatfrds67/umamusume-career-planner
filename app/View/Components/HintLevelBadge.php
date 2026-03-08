<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * HintLevelBadge Component
 *
 * Displays skill hint level (0-5) with discount percentage.
 * Verified discount rates: 10%/20%/30%/35%/40% (max at level 5).
 */
class HintLevelBadge extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  int  $level  Hint level (0-5)
     * @param  bool  $showDiscount  Whether to show discount percentage
     * @param  bool  $showIcon  Whether to show hint icon
     * @param  string  $size  Size variant (sm, md, lg)
     */
    public function __construct(
        public int $level = 0,
        public bool $showDiscount = true,
        public bool $showIcon = true,
        public string $size = 'md'
    ) {
        // Clamp level between 0 and 5
        $this->level = max(0, min(5, $level));
    }

    /**
     * Get discount percentage for the hint level.
     * VERIFIED from game-mechanics-research-report.md
     */
    public function getDiscountPercentage(): int
    {
        return match ($this->level) {
            1 => 10,
            2 => 20,
            3 => 30,
            4 => 35,
            5 => 40, // Maximum discount
            default => 0,
        };
    }

    /**
     * Get color classes based on hint level.
     */
    public function getColorClasses(): string
    {
        if ($this->level === 0) {
            return 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 border-neutral-300 dark:border-neutral-600';
        }

        if ($this->level >= 5) {
            return 'bg-gradient-to-r from-purple-500 to-pink-500 text-white border-purple-600 dark:border-pink-600';
        }

        if ($this->level >= 3) {
            return 'bg-gradient-to-r from-blue-500 to-purple-500 text-white border-blue-600 dark:border-purple-600';
        }

        return 'bg-gradient-to-r from-green-500 to-blue-500 text-white border-green-600 dark:border-blue-600';
    }

    /**
     * Get size classes.
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'text-xs px-2 py-0.5',
            'md' => 'text-sm px-2.5 py-1',
            'lg' => 'text-base px-3 py-1.5',
            default => 'text-sm px-2.5 py-1',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.hint-level-badge');
    }
}
