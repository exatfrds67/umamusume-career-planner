<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StarRating extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  int  $stars  Number of filled stars (1-5)
     * @param  int  $maxStars  Maximum number of stars to display
     * @param  string  $size  Size variant (sm, md, lg)
     * @param  bool  $showCount  Whether to show numeric count
     */
    public function __construct(
        public int $stars = 3,
        public int $maxStars = 5,
        public string $size = 'md',
        public bool $showCount = false,
    ) {
        $this->stars = max(0, min($this->stars, $this->maxStars));
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.star-rating');
    }

    /**
     * Get star size class based on size variant.
     */
    public function starSize(): string
    {
        return match ($this->size) {
            'sm' => 'w-3 h-3',
            'lg' => 'w-6 h-6',
            default => 'w-4 h-4',
        };
    }
}
