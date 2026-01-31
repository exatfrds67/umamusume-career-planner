<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatRadarChart extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  array<string, int|string>  $stats  Associative array of stat values (speed, stamina, power, guts, wit)
     * @param  int|string  $max  Maximum value for stats (default 2000)
     * @param  string  $size  Chart size (sm, md, lg)
     * @param  bool  $showLabels  Whether to show stat labels
     * @param  bool  $showValues  Whether to show stat values
     * @param  bool  $animated  Whether to animate on load
     */
    public function __construct(
        public array $stats = [],
        public int|string $max = 1000,
        public string $size = 'md',
        public bool $showLabels = true,
        public bool $showValues = false,
        public bool $animated = true,
    ) {
        // Normalize all stat values to integers
        $normalizedStats = [];
        foreach ($this->stats as $key => $value) {
            $normalizedStats[strtolower($key)] = (int) $value;
        }

        $this->stats = [
            'speed' => $normalizedStats['speed'] ?? 0,
            'stamina' => $normalizedStats['stamina'] ?? 0,
            'power' => $normalizedStats['power'] ?? 0,
            'guts' => $normalizedStats['guts'] ?? 0,
            'wit' => $normalizedStats['wit'] ?? 0,
        ];

        $this->max = (int) $max;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.stat-radar-chart');
    }

    /**
     * Get stat value, clamped to max.
     */
    public function getStatValue(string $stat): int
    {
        return min($this->max, max(0, $this->stats[strtolower($stat)] ?? 0));
    }

    /**
     * Get all stat values normalized to 0-100 percentage.
     */
    public function getStatPercentages(): array
    {
        return [
            'speed' => round(($this->getStatValue('speed') / $this->max) * 100),
            'stamina' => round(($this->getStatValue('stamina') / $this->max) * 100),
            'power' => round(($this->getStatValue('power') / $this->max) * 100),
            'guts' => round(($this->getStatValue('guts') / $this->max) * 100),
            'wit' => round(($this->getStatValue('wit') / $this->max) * 100),
        ];
    }

    /**
     * Get color class for stat type.
     */
    public function getStatColor(string $stat): string
    {
        return match (strtolower($stat)) {
            'speed' => 'text-blue-500 dark:text-blue-400',
            'stamina' => 'text-green-500 dark:text-green-400',
            'power' => 'text-orange-500 dark:text-orange-400',
            'guts' => 'text-amber-500 dark:text-amber-400',
            'wit' => 'text-sky-500 dark:text-sky-400',
            default => 'text-gray-500 dark:text-gray-400',
        };
    }

    /**
     * Get fill color for SVG (without dark mode, as SVG handles it separately).
     */
    public function getSvgFillColor(string $stat): string
    {
        return match (strtolower($stat)) {
            'speed' => '#3b82f6',  // blue-500
            'stamina' => '#22c55e', // green-500
            'power' => '#f97316',   // orange-500
            'guts' => '#fbbf24',    // amber-500
            'wit' => '#0ea5e9',     // sky-500
            default => '#6b7280',   // gray-500
        };
    }

    /**
     * Get size classes for SVG container.
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'w-32 h-32',
            'lg' => 'w-96 h-96',
            default => 'w-64 h-64',
        };
    }

    /**
     * Get stat names in order for pentagon (5 points).
     */
    public function getStatNames(): array
    {
        return ['speed', 'stamina', 'power', 'guts', 'wit'];
    }

    /**
     * Calculate SVG points for pentagon.
     */
    public function calculatePoints(): array
    {
        $stats = $this->getStatNames();
        $percentages = $this->getStatPercentages();
        $points = [];

        // Pentagon center and radius
        $size = $this->size === 'sm' ? 64 : ($this->size === 'lg' ? 192 : 128);
        $centerX = $size / 2;
        $centerY = $size / 2;
        $maxRadius = $size / 2.2;

        foreach ($stats as $index => $stat) {
            $angle = (($index * 360) / 5) - 90; // Start from top
            $radians = deg2rad($angle);
            $radius = ($percentages[$stat] / 100) * $maxRadius;

            $x = $centerX + ($radius * cos($radians));
            $y = $centerY + ($radius * sin($radians));

            $points[] = "$x,$y";
        }

        return $points;
    }

    /**
     * Get pentagon grid points (background reference).
     */
    public function getGridPoints(int $level = 5): array
    {
        $stats = $this->getStatNames();
        $gridPoints = [];

        $size = $this->size === 'sm' ? 64 : ($this->size === 'lg' ? 192 : 128);
        $centerX = $size / 2;
        $centerY = $size / 2;
        $maxRadius = $size / 2.2;

        for ($gridLevel = 1; $gridLevel <= $level; $gridLevel++) {
            $levelPoints = [];
            $radius = ($gridLevel / $level) * $maxRadius;

            foreach ($stats as $index => $stat) {
                $angle = (($index * 360) / 5) - 90;
                $radians = deg2rad($angle);

                $x = $centerX + ($radius * cos($radians));
                $y = $centerY + ($radius * sin($radians));

                $levelPoints[] = "$x,$y";
            }

            $gridPoints[$gridLevel] = implode(' ', $levelPoints);
        }

        return $gridPoints;
    }
}
