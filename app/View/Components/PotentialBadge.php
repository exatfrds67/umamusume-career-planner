<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PotentialBadge extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  int  $level  Potential level (1-9)
     * @param  string  $size  Size variant (sm, md, lg)
     * @param  bool  $showLabel  Whether to show "Lv" label
     */
    public function __construct(
        public int $level = 1,
        public string $size = 'md',
        public bool $showLabel = true,
    ) {
        $this->level = max(1, min($this->level, 9));
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.potential-badge');
    }

    /**
     * Get badge color based on potential level.
     */
    public function badgeColor(): string
    {
        return match (true) {
            $this->level >= 7 => 'bg-gradient-to-r from-purple-500 to-pink-500 text-white',
            $this->level >= 4 => 'bg-gradient-to-r from-blue-500 to-cyan-500 text-white',
            default => 'bg-neutral-500 text-white',
        };
    }

    /**
     * Get badge size classes.
     */
    public function sizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'px-1.5 py-0.5 text-xs',
            'lg' => 'px-3 py-1.5 text-base',
            default => 'px-2 py-1 text-sm',
        };
    }
}
