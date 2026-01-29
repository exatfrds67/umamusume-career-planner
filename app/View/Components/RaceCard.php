<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * RaceCard Component
 *
 * Displays race information with readiness score and win probability.
 * Shows race grade (G1/G2/G3/OP), distance, track type, and entry status.
 */
class RaceCard extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  array  $race  Race data
     * @param  int|null  $readiness  Readiness score (0-100)
     * @param  float|null  $winProb  Win probability (0-100)
     * @param  bool  $isEntered  Whether character is entered
     * @param  bool  $clickable  Whether the card is clickable
     * @param  string  $size  Size variant (sm, md, lg)
     */
    public function __construct(
        public array $race,
        public ?int $readiness = null,
        public ?float $winProb = null,
        public bool $isEntered = false,
        public bool $clickable = true,
        public string $size = 'md'
    ) {}

    /**
     * Get race grade badge color classes.
     */
    public function getGradeColorClasses(): string
    {
        $grade = $this->race['grade'] ?? 'OP';

        return match ($grade) {
            'G1' => 'bg-gradient-to-r from-yellow-400 to-amber-500 text-white',
            'G2' => 'bg-gradient-to-r from-gray-300 to-gray-400 text-gray-900',
            'G3' => 'bg-gradient-to-r from-amber-600 to-amber-700 text-white',
            default => 'bg-gray-500 text-white',
        };
    }

    /**
     * Get readiness status color classes.
     */
    public function getReadinessColorClasses(): string
    {
        if ($this->readiness === null) {
            return 'text-gray-500 dark:text-gray-400';
        }

        if ($this->readiness >= 80) {
            return 'text-green-600 dark:text-green-400';
        }

        if ($this->readiness >= 60) {
            return 'text-amber-600 dark:text-amber-400';
        }

        return 'text-red-600 dark:text-red-400';
    }

    /**
     * Get readiness status text.
     */
    public function getReadinessStatus(): string
    {
        if ($this->readiness === null) {
            return 'Unknown';
        }

        if ($this->readiness >= 80) {
            return 'Ready';
        }

        if ($this->readiness >= 60) {
            return 'Caution';
        }

        return 'Not Ready';
    }

    /**
     * Get track type icon.
     */
    public function getTrackIcon(): string
    {
        $track = $this->race['track'] ?? 'turf';

        return match ($track) {
            'turf' => '🌱',
            'dirt' => '🏜️',
            default => '🏁',
        };
    }

    /**
     * Get size classes.
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'p-3',
            'md' => 'p-4',
            'lg' => 'p-5',
            default => 'p-4',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.race-card');
    }
}
