<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * BondMeter Component
 *
 * Displays bond/friendship level progress with threshold indicators.
 * Shows rainbow gradient at 80%+ (skill unlock threshold).
 */
class BondMeter extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  int  $value  Current bond level (0-100)
     * @param  int  $threshold  Skill unlock threshold (default 80)
     * @param  bool  $showLabel  Whether to show the label
     * @param  bool  $showPercentage  Whether to show percentage value
     * @param  string  $size  Size variant (sm, md, lg)
     */
    public function __construct(
        public int $value = 0,
        public int $threshold = 80,
        public bool $showLabel = true,
        public bool $showPercentage = true,
        public string $size = 'md'
    ) {
        // Clamp value between 0 and 100
        $this->value = max(0, min(100, $value));
    }

    /**
     * Check if bond level has reached the threshold.
     */
    public function isThresholdReached(): bool
    {
        return $this->value >= $this->threshold;
    }

    /**
     * Get the progress bar color classes.
     */
    public function getProgressColorClasses(): string
    {
        if ($this->isThresholdReached()) {
            return 'bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500';
        }

        return 'bg-blue-500';
    }

    /**
     * Get the text color classes.
     */
    public function getTextColorClasses(): string
    {
        if ($this->isThresholdReached()) {
            return 'text-pink-600 dark:text-pink-400 font-bold';
        }

        return 'text-gray-700 dark:text-gray-300';
    }

    /**
     * Get size classes for the meter.
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'h-2',
            'md' => 'h-3',
            'lg' => 'h-4',
            default => 'h-3',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.bond-meter');
    }
}
