<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AptitudeDisplay extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string  $type  Aptitude type (turf, dirt, short, mile, medium, long, escape, leading, tracking, chasing)
     * @param  string  $grade  Grade (S, A, B, C, D, E, F, G)
     * @param  int  $bonus  Bonus percentage (0-100)
     * @param  bool  $showIcon  Whether to show type icon
     * @param  bool  $showLabel  Whether to show type label
     * @param  string  $layout  Layout direction (horizontal, vertical)
     */
    public function __construct(
        public string $type,
        public string $grade = 'C',
        public int $bonus = 0,
        public bool $showIcon = true,
        public bool $showLabel = true,
        public string $layout = 'horizontal',
    ) {
        $this->type = strtolower($this->type);
        $this->grade = strtoupper($this->grade);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.aptitude-display');
    }

    /**
     * Get grade color class.
     */
    public function gradeColor(): string
    {
        return match ($this->grade) {
            'S' => 'text-yellow-500 dark:text-yellow-400',
            'A' => 'text-orange-500 dark:text-orange-400',
            'B' => 'text-blue-500 dark:text-blue-400',
            'C' => 'text-green-500 dark:text-green-400',
            'D' => 'text-gray-500 dark:text-gray-400',
            'E', 'F', 'G' => 'text-red-500 dark:text-red-400',
            default => 'text-gray-500 dark:text-gray-400',
        };
    }

    /**
     * Get type label.
     */
    public function typeLabel(): string
    {
        return match ($this->type) {
            'turf' => 'Turf',
            'dirt' => 'Dirt',
            'short' => 'Short',
            'mile' => 'Mile',
            'medium' => 'Medium',
            'long' => 'Long',
            'escape' => 'Escape',
            'leading' => 'Leading',
            'tracking' => 'Tracking',
            'chasing' => 'Chasing',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get layout classes.
     */
    public function layoutClasses(): string
    {
        return $this->layout === 'vertical'
            ? 'flex-col items-center'
            : 'flex-row items-center space-x-2';
    }
}
