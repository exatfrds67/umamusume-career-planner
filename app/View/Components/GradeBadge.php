<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GradeBadge extends Component
{
    public string $grade;

    public string $size;

    public bool $showLabel;

    public ?string $label;

    /**
     * Valid grades (S is maximum, no SS)
     * Source: docs/research/game-mechanics-research-report.md
     */
    private const VALID_GRADES = ['S', 'A', 'B', 'C', 'D', 'E', 'F', 'G'];

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $grade,
        string $size = 'md',
        bool $showLabel = false,
        ?string $label = null
    ) {
        $this->grade = strtoupper($grade);
        $this->size = $size;
        $this->showLabel = $showLabel;
        $this->label = $label;

        // Validate grade
        if (! in_array($this->grade, self::VALID_GRADES)) {
            $this->grade = 'G'; // Default to lowest grade
        }
    }

    /**
     * Get the grade color class
     */
    public function getGradeColor(): string
    {
        return match ($this->grade) {
            'S' => 'grade-s',
            'A' => 'grade-a',
            'B' => 'grade-b',
            'C' => 'grade-c',
            'D' => 'grade-d',
            'E' => 'grade-e',
            'F' => 'grade-f',
            'G' => 'grade-g',
            default => 'grade-g',
        };
    }

    /**
     * Get the size classes
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'xs' => 'w-5 h-5 text-[10px]',
            'sm' => 'w-6 h-6 text-xs',
            'md' => 'w-8 h-8 text-sm',
            'lg' => 'w-10 h-10 text-base',
            'xl' => 'w-12 h-12 text-lg',
            default => 'w-8 h-8 text-sm',
        };
    }

    /**
     * Get the grade description
     */
    public function getGradeDescription(): string
    {
        return match ($this->grade) {
            'S' => 'Excellent (+5% bonus)',
            'A' => 'Good (baseline)',
            'B' => 'Average (-10% penalty)',
            'C' => 'Below Average (-20% penalty)',
            'D' => 'Poor (-30-40% penalty)',
            'E' => 'Very Poor (-50-60% penalty)',
            'F' => 'Terrible (-70-80% penalty)',
            'G' => 'Unusable (-90% penalty)',
            default => 'Unknown',
        };
    }

    /**
     * Get view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.grade-badge');
    }
}
