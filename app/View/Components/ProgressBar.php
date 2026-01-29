<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProgressBar extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  int|float  $current  Current value
     * @param  int|float  $max  Maximum value
     * @param  string  $color  Color variant (primary, success, warning, danger, or stat name)
     * @param  string  $size  Size variant (sm, md, lg)
     * @param  bool  $showLabel  Whether to show percentage label
     * @param  bool  $showValues  Whether to show current/max values
     * @param  bool  $animated  Whether to animate the progress
     */
    public function __construct(
        public int|float $current = 0,
        public int|float $max = 100,
        public string $color = 'primary',
        public string $size = 'md',
        public bool $showLabel = false,
        public bool $showValues = false,
        public bool $animated = false,
    ) {
        $this->current = max(0, min($this->current, $this->max));
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.progress-bar');
    }

    /**
     * Calculate progress percentage.
     */
    public function percentage(): float
    {
        if ($this->max <= 0) {
            return 0;
        }

        return round(($this->current / $this->max) * 100, 1);
    }

    /**
     * Get progress bar color classes.
     */
    public function colorClasses(): string
    {
        return match ($this->color) {
            'speed' => 'bg-gradient-to-r from-rose-400 to-rose-500',
            'stamina' => 'bg-gradient-to-r from-green-400 to-green-500',
            'power' => 'bg-gradient-to-r from-orange-400 to-orange-500',
            'guts' => 'bg-gradient-to-r from-amber-400 to-amber-500',
            'wit', 'wisdom' => 'bg-gradient-to-r from-sky-400 to-sky-500',
            'success' => 'bg-gradient-to-r from-green-500 to-green-600',
            'warning' => 'bg-gradient-to-r from-yellow-500 to-yellow-600',
            'danger' => 'bg-gradient-to-r from-red-500 to-red-600',
            default => 'bg-gradient-to-r from-blue-500 to-blue-600',
        };
    }

    /**
     * Get size classes.
     */
    public function sizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'h-2',
            'lg' => 'h-6',
            default => 'h-4',
        };
    }
}
