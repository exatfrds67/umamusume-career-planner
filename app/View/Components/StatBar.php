<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatBar extends Component
{
    public string $stat;

    public int $current;

    public int $max;

    public ?int $target;

    public ?int $factorBonus;

    public bool $showIcon;

    public bool $showPercentage;

    public bool $showSoftCap;

    public bool $showLabel;

    public string $size;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $stat,
        int $current,
        int $max = 2000,
        ?int $target = null,
        ?int $factorBonus = null,
        bool $showIcon = true,
        bool $showPercentage = false,
        bool $showSoftCap = true,
        bool $showLabel = true,
        string $size = 'md'
    ) {
        $this->stat = strtolower($stat);
        $this->current = $current;
        $this->max = $max;
        $this->target = $target;
        $this->factorBonus = $factorBonus;
        $this->showIcon = $showIcon;
        $this->showPercentage = $showPercentage;
        $this->showSoftCap = $showSoftCap;
        $this->showLabel = $showLabel;
        $this->size = $size;
    }

    /**
     * Get the stat color class
     */
    public function getStatColor(): string
    {
        return match ($this->stat) {
            'speed' => 'stat-speed',
            'stamina' => 'stat-stamina',
            'power' => 'stat-power',
            'guts' => 'stat-guts',
            'wit', 'wisdom' => 'stat-wit',
            default => 'primary',
        };
    }

    /**
     * Get the stat label
     */
    public function getStatLabel(): string
    {
        return match ($this->stat) {
            'speed' => 'Speed',
            'stamina' => 'Stamina',
            'power' => 'Power',
            'guts' => 'Guts',
            'wit', 'wisdom' => 'Wit',
            default => ucfirst($this->stat),
        };
    }

    /**
     * Calculate percentage
     */
    public function getPercentage(): float
    {
        return min(100, ($this->current / $this->max) * 100);
    }

    /**
     * Calculate effective stat value (accounting for soft cap at 1200)
     */
    public function getEffectiveValue(): int
    {
        if ($this->current <= 1200) {
            return $this->current;
        }

        // Above 1200: stats count at 50% effectiveness
        $baseValue = 1200;
        $overCapValue = $this->current - 1200;
        $effectiveOverCap = (int) ($overCapValue * 0.5);

        return $baseValue + $effectiveOverCap;
    }

    /**
     * Check if stat is above soft cap
     */
    public function isAboveSoftCap(): bool
    {
        return $this->current > 1200;
    }

    /**
     * Get soft cap percentage (for visual indicator)
     */
    public function getSoftCapPercentage(): float
    {
        return (1200 / $this->max) * 100;
    }

    /**
     * Get target percentage
     */
    public function getTargetPercentage(): ?float
    {
        if ($this->target === null) {
            return null;
        }

        return min(100, ($this->target / $this->max) * 100);
    }

    /**
     * Get view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.stat-bar');
    }
}
