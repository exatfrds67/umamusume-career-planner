<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * EnergyGauge Component
 *
 * Displays character energy level with trend indicator.
 * Shows green/orange/red states based on energy level.
 */
class EnergyGauge extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  int  $value  Current energy (0-100)
     * @param  string  $trend  Trend direction (up, down, flat)
     * @param  bool  $showIcon  Whether to show energy icon
     * @param  bool  $showTrend  Whether to show trend arrow
     * @param  string  $size  Size variant (sm, md, lg)
     */
    public function __construct(
        public int $value = 100,
        public string $trend = 'flat',
        public bool $showIcon = true,
        public bool $showTrend = true,
        public string $size = 'md'
    ) {
        // Clamp value between 0 and 100
        $this->value = max(0, min(100, $value));
    }

    /**
     * Get energy status based on value.
     */
    public function getStatus(): string
    {
        if ($this->value >= 70) {
            return 'high';
        }

        if ($this->value >= 40) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get energy state (critical/low/normal).
     */
    public function energyState(): string
    {
        if ($this->value < 30) {
            return 'critical';
        }

        if ($this->value < 60) {
            return 'low';
        }

        return 'normal';
    }

    /**
     * Get color classes based on energy level.
     */
    public function getColorClasses(): string
    {
        return match ($this->getStatus()) {
            'high' => 'text-green-600 dark:text-green-400',
            'medium' => 'text-amber-600 dark:text-amber-400',
            'low' => 'text-red-600 dark:text-red-400',
            default => 'text-gray-600 dark:text-gray-400',
        };
    }

    /**
     * Get bar color classes based on energy state.
     */
    public function colorClasses(): string
    {
        return match ($this->energyState()) {
            'critical' => 'bg-red-500 dark:bg-red-600',
            'low' => 'bg-orange-500 dark:bg-orange-600',
            'normal' => 'bg-green-500 dark:bg-green-600',
            default => 'bg-gray-500 dark:bg-gray-600',
        };
    }

    /**
     * Get progress bar color classes.
     */
    public function getProgressColorClasses(): string
    {
        return match ($this->getStatus()) {
            'high' => 'bg-gradient-to-r from-green-400 to-green-500',
            'medium' => 'bg-gradient-to-r from-amber-400 to-amber-500',
            'low' => 'bg-gradient-to-r from-red-400 to-red-500',
            default => 'bg-gray-500',
        };
    }

    /**
     * Get trend arrow icon.
     */
    public function getTrendArrow(): string
    {
        return match ($this->trend) {
            'up' => '↑',
            'down' => '↓',
            default => '→',
        };
    }

    /**
     * Alias for getTrendArrow().
     */
    public function trendIcon(): string
    {
        return $this->getTrendArrow();
    }

    /**
     * Get percentage value.
     */
    public function percentage(): int
    {
        return $this->value;
    }

    /**
     * Get trend color classes.
     */
    public function getTrendColorClasses(): string
    {
        return match ($this->trend) {
            'up' => 'text-green-600 dark:text-green-400',
            'down' => 'text-red-600 dark:text-red-400',
            default => 'text-gray-600 dark:text-gray-400',
        };
    }

    /**
     * Get size classes.
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'h-1 text-sm',
            'md' => 'h-2 text-base',
            'lg' => 'h-3 text-lg',
            default => 'h-2 text-base',
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
        return view('components.energy-gauge');
    }
}
