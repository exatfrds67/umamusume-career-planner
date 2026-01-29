<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ConditionBadge extends Component
{
    public string $condition;

    public ?string $trend;

    public ?int $turnsActive;

    public string $size;

    public bool $showTrend;

    public bool $showDuration;

    /**
     * Valid conditions
     * Source: docs/design/README.md
     */
    private const VALID_CONDITIONS = ['GREAT', 'GOOD', 'NORMAL', 'BAD'];

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $condition = 'NORMAL',
        ?string $trend = null,
        ?int $turnsActive = null,
        string $size = 'md',
        bool $showTrend = true,
        bool $showDuration = false
    ) {
        $this->condition = strtoupper($condition);
        $this->trend = $trend;
        $this->turnsActive = $turnsActive;
        $this->size = $size;
        $this->showTrend = $showTrend;
        $this->showDuration = $showDuration;

        // Validate condition
        if (! in_array($this->condition, self::VALID_CONDITIONS)) {
            $this->condition = 'NORMAL';
        }
    }

    /**
     * Get the condition color class
     */
    public function getConditionColor(): string
    {
        return match ($this->condition) {
            'GREAT' => 'condition-great',
            'GOOD' => 'condition-good',
            'NORMAL' => 'condition-normal',
            'BAD' => 'condition-bad',
            default => 'condition-normal',
        };
    }

    /**
     * Get color classes for badge styling.
     */
    public function colorClasses(): string
    {
        return match ($this->condition) {
            'GREAT' => 'bg-pink-500 text-white dark:bg-pink-600',
            'GOOD' => 'bg-blue-500 text-white dark:bg-blue-600',
            'NORMAL' => 'bg-orange-500 text-white dark:bg-orange-600',
            'BAD' => 'bg-red-500 text-white dark:bg-red-600',
            default => 'bg-gray-500 text-white dark:bg-gray-600',
        };
    }

    /**
     * Get the trend icon
     */
    public function getTrendIcon(): ?string
    {
        if (! $this->showTrend || ! $this->trend) {
            return null;
        }

        return match ($this->trend) {
            'up' => '↑',
            'down' => '↓',
            'stable', 'flat' => '→',
            default => null,
        };
    }

    /**
     * Alias for getTrendIcon().
     */
    public function trendIcon(): string
    {
        if (! $this->trend) {
            return '→';
        }

        return match ($this->trend) {
            'up' => '↑',
            'down' => '↓',
            default => '→',
        };
    }

    /**
     * Get the condition description
     */
    public function getConditionDescription(): string
    {
        return match ($this->condition) {
            'GREAT' => 'Great Condition (+20% training effectiveness)',
            'GOOD' => 'Good Condition (+10% training effectiveness)',
            'NORMAL' => 'Normal Condition (baseline)',
            'BAD' => 'Bad Condition (-10% training effectiveness)',
            default => 'Unknown',
        };
    }

    /**
     * Get the size classes
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'px-2 py-0.5 text-xs',
            'md' => 'px-3 py-1 text-sm',
            'lg' => 'px-4 py-1.5 text-base',
            default => 'px-3 py-1 text-sm',
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
     * Get condition label.
     */
    public function label(): string
    {
        return match ($this->condition) {
            'GREAT' => 'Great',
            'GOOD' => 'Good',
            'NORMAL' => 'Normal',
            'BAD' => 'Bad',
            default => 'Unknown',
        };
    }

    /**
     * Get view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.condition-badge');
    }
}
