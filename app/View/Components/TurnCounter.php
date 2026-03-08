<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * TurnCounter Component
 *
 * Displays current turn with career stage (Junior/Classic/Senior).
 * Shows progress through the 78-turn career.
 */
class TurnCounter extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  int  $current  Current turn (1-78)
     * @param  int  $total  Total turns (default 78)
     * @param  bool  $showStage  Whether to show career stage
     * @param  bool  $showProgress  Whether to show progress bar
     * @param  string  $size  Size variant (sm, md, lg)
     */
    public function __construct(
        public int $current = 1,
        public int $total = 78,
        public bool $showStage = true,
        public bool $showProgress = true,
        public string $size = 'md'
    ) {
        // Clamp current between 1 and total
        $this->current = max(1, min($this->total, $current));
    }

    /**
     * Get career stage based on turn number.
     */
    public function getStage(): string
    {
        if ($this->current <= 24) {
            return 'Junior';
        }

        if ($this->current <= 48) {
            return 'Classic';
        }

        return 'Senior';
    }

    /**
     * Alias for getStage() - returns career stage.
     */
    public function stage(): string
    {
        return $this->getStage();
    }

    /**
     * Get stage color classes.
     */
    public function getStageColorClasses(): string
    {
        return match ($this->getStage()) {
            'Junior' => 'text-green-600 dark:text-green-400',
            'Classic' => 'text-blue-600 dark:text-blue-400',
            'Senior' => 'text-purple-600 dark:text-purple-400',
            default => 'text-neutral-600 dark:text-neutral-400',
        };
    }

    /**
     * Get progress percentage.
     */
    public function getProgressPercentage(): float
    {
        return ($this->current / $this->total) * 100;
    }

    /**
     * Alias for getProgressPercentage() - returns progress as integer.
     */
    public function progressPercentage(): int
    {
        return (int) round($this->getProgressPercentage());
    }

    /**
     * Get size classes.
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'text-xs',
            'md' => 'text-sm',
            'lg' => 'text-base',
            default => 'text-sm',
        };
    }

    /**
     * Alias for getSizeClasses().
     */
    public function sizeClasses(): string
    {
        return $this->getSizeClasses();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.turn-counter');
    }
}
