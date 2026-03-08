<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TypeIcon extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string  $type  Stat type (speed, stamina, power, guts, wit, friend)
     * @param  string  $size  Icon size (sm, md, lg, xl)
     * @param  bool  $showLabel  Whether to show type label
     * @param  bool  $filled  Whether to use filled icon style
     */
    public function __construct(
        public string $type = 'speed',
        public string $size = 'md',
        public bool $showLabel = false,
        public bool $filled = true,
    ) {
        $this->type = strtolower($this->type);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.type-icon');
    }

    /**
     * Get the color classes for the type.
     */
    public function colorClasses(): string
    {
        return match ($this->type) {
            'speed' => 'text-blue-500 dark:text-blue-400',
            'stamina' => 'text-green-500 dark:text-green-400',
            'power' => 'text-orange-500 dark:text-orange-400',
            'guts' => 'text-amber-500 dark:text-amber-400',
            'wit', 'wisdom' => 'text-sky-500 dark:text-sky-400',
            'friend' => 'text-pink-500 dark:text-pink-400',
            default => 'text-neutral-500 dark:text-neutral-400',
        };
    }

    /**
     * Get size classes.
     */
    public function sizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'w-4 h-4',
            'lg' => 'w-8 h-8',
            'xl' => 'w-10 h-10',
            default => 'w-6 h-6',
        };
    }

    /**
     * Get the SVG icon symbol for the type.
     */
    public function iconSymbol(): string
    {
        return match ($this->type) {
            'speed' => '⚡',
            'stamina' => '💚',
            'power' => '💪',
            'guts' => '🔥',
            'wit' => '🧠',
            'wisdom' => '📚',
            'friend' => '👥',
            default => '⭐',
        };
    }

    /**
     * Get label text.
     */
    public function label(): string
    {
        return match ($this->type) {
            'wit', 'wisdom' => 'Wisdom',
            'friend' => 'Friend',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get background color for badge style.
     */
    public function bgColor(): string
    {
        return match ($this->type) {
            'speed' => 'bg-blue-100 dark:bg-blue-900/30',
            'stamina' => 'bg-green-100 dark:bg-green-900/30',
            'power' => 'bg-orange-100 dark:bg-orange-900/30',
            'guts' => 'bg-amber-100 dark:bg-amber-900/30',
            'wit', 'wisdom' => 'bg-sky-100 dark:bg-sky-900/30',
            'friend' => 'bg-pink-100 dark:bg-pink-900/30',
            default => 'bg-neutral-100 dark:bg-neutral-900/30',
        };
    }
}
