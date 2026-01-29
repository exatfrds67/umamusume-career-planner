<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RaceDayBadge extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  int  $daysUntil  Days until race (0 = race day)
     * @param  bool  $isRaceDay  Whether today is race day
     * @param  string  $size  Badge size (sm, md, lg)
     * @param  bool  $showCountdown  Whether to show day count
     */
    public function __construct(
        public int $daysUntil = 7,
        public bool $isRaceDay = false,
        public string $size = 'md',
        public bool $showCountdown = true,
    ) {
        // Clamp days to 0+
        $this->daysUntil = max(0, $daysUntil);
        // If daysUntil is 0, assume it's race day
        if ($this->daysUntil === 0) {
            $this->isRaceDay = true;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.race-day-badge');
    }

    /**
     * Get badge color classes.
     */
    public function colorClasses(): string
    {
        if ($this->isRaceDay) {
            return 'bg-red-600 dark:bg-red-700 text-white';
        }

        return match (true) {
            $this->daysUntil === 1 => 'bg-red-500 dark:bg-red-600 text-white',
            $this->daysUntil <= 3 => 'bg-orange-500 dark:bg-orange-600 text-white',
            $this->daysUntil <= 7 => 'bg-amber-500 dark:bg-amber-600 text-white',
            default => 'bg-blue-500 dark:bg-blue-600 text-white',
        };
    }

    /**
     * Get size classes.
     */
    public function sizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'px-2 py-0.5 text-xs',
            'lg' => 'px-4 py-1.5 text-base',
            default => 'px-3 py-1 text-sm',
        };
    }

    /**
     * Get badge label text.
     */
    public function label(): string
    {
        if ($this->isRaceDay) {
            return 'RACE DAY';
        }

        if ($this->daysUntil === 1) {
            return 'Tomorrow';
        }

        return "In {$this->daysUntil} days";
    }
}
