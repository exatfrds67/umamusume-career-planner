<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * SPCounter Component
 *
 * Displays skill points (SP) budget tracker with current/available display.
 * Shows warning states when SP is low or exceeded.
 */
class SPCounter extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  int  $current  Current SP spent
     * @param  int  $available  Total SP available
     * @param  bool  $showBar  Whether to show progress bar
     * @param  bool  $showIcon  Whether to show SP icon
     * @param  string  $size  Size variant (sm, md, lg)
     */
    public function __construct(
        public int $current = 0,
        public int $available = 0,
        public bool $showBar = true,
        public bool $showIcon = true,
        public string $size = 'md'
    ) {}

    /**
     * Get remaining SP.
     */
    public function getRemaining(): int
    {
        return $this->available - $this->current;
    }

    /**
     * Get usage percentage.
     */
    public function getPercentage(): float
    {
        if ($this->available === 0) {
            return 0;
        }

        return min(100, ($this->current / $this->available) * 100);
    }

    /**
     * Check if SP is exceeded.
     */
    public function isExceeded(): bool
    {
        return $this->current > $this->available;
    }

    /**
     * Check if SP is running low (>80% used).
     */
    public function isLow(): bool
    {
        return ! $this->isExceeded() && $this->getPercentage() > 80;
    }

    /**
     * Get status color classes.
     */
    public function getStatusColorClasses(): string
    {
        if ($this->isExceeded()) {
            return 'text-red-600 dark:text-red-400';
        }

        if ($this->isLow()) {
            return 'text-amber-600 dark:text-amber-400';
        }

        return 'text-green-600 dark:text-green-400';
    }

    /**
     * Get progress bar color classes.
     */
    public function getProgressColorClasses(): string
    {
        if ($this->isExceeded()) {
            return 'bg-red-500';
        }

        if ($this->isLow()) {
            return 'bg-amber-500';
        }

        return 'bg-green-500';
    }

    /**
     * Get size classes.
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'text-sm',
            'md' => 'text-base',
            'lg' => 'text-lg',
            default => 'text-base',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.sp-counter');
    }
}
