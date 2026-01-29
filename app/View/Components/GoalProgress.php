<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GoalProgress extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string  $goal  Goal type (G1, G2, G3, OP)
     * @param  int  $current  Current goal progress (wins achieved)
     * @param  int  $target  Target goal (total wins needed)
     * @param  string  $size  Component size (sm, md, lg)
     * @param  bool  $showLabel  Whether to show goal label
     */
    public function __construct(
        public string $goal = 'G1',
        public int $current = 0,
        public int $target = 5,
        public string $size = 'md',
        public bool $showLabel = true,
    ) {
        $this->goal = strtoupper($goal);
        // Clamp current between 0 and target
        $this->current = max(0, min($target, $current));
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.goal-progress');
    }

    /**
     * Get goal color classes.
     */
    public function colorClasses(): string
    {
        return match ($this->goal) {
            'G1' => 'text-yellow-600 dark:text-yellow-400',
            'G2' => 'text-blue-600 dark:text-blue-400',
            'G3' => 'text-purple-600 dark:text-purple-400',
            'OP' => 'text-green-600 dark:text-green-400',
            default => 'text-gray-600 dark:text-gray-400',
        };
    }

    /**
     * Get goal background color.
     */
    public function bgColor(): string
    {
        return match ($this->goal) {
            'G1' => 'bg-yellow-100 dark:bg-yellow-900/30',
            'G2' => 'bg-blue-100 dark:bg-blue-900/30',
            'G3' => 'bg-purple-100 dark:bg-purple-900/30',
            'OP' => 'bg-green-100 dark:bg-green-900/30',
            default => 'bg-gray-100 dark:bg-gray-900/30',
        };
    }

    /**
     * Get progress percentage.
     */
    public function percentage(): int
    {
        if ($this->target === 0) {
            return 0;
        }

        return (int) round(($this->current / $this->target) * 100);
    }

    /**
     * Check if goal is achieved.
     */
    public function isComplete(): bool
    {
        return $this->current >= $this->target;
    }

    /**
     * Get size classes.
     */
    public function sizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'text-xs',
            'lg' => 'text-base',
            default => 'text-sm',
        };
    }
}
