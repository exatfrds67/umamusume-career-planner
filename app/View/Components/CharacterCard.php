<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CharacterCard extends Component
{
    public array $character;

    public bool $showStats;

    public bool $showAptitudes;

    public string $size;

    public bool $selectable;

    /**
     * Create a new component instance.
     */
    public function __construct(
        array $character,
        bool $showStats = true,
        bool $showAptitudes = false,
        string $size = 'md',
        bool $selectable = false
    ) {
        $this->character = $character;
        $this->showStats = $showStats;
        $this->showAptitudes = $showAptitudes;
        $this->size = $size;
        $this->selectable = $selectable;
    }

    /**
     * Get character name
     */
    public function getName(): string
    {
        return $this->character['name'] ?? 'Unknown';
    }

    /**
     * Get character avatar URL
     */
    public function getAvatarUrl(): ?string
    {
        return $this->character['avatar_url'] ?? $this->character['avatar_path'] ?? null;
    }

    /**
     * Get character stats
     */
    public function getStats(): array
    {
        return [
            'speed' => $this->character['speed'] ?? 0,
            'stamina' => $this->character['stamina'] ?? 0,
            'power' => $this->character['power'] ?? 0,
            'guts' => $this->character['guts'] ?? 0,
            'wit' => $this->character['wit'] ?? $this->character['wisdom'] ?? 0,
        ];
    }

    /**
     * Get total stats
     */
    public function getTotalStats(): int
    {
        return array_sum($this->getStats());
    }

    /**
     * Get character grade based on total stats
     */
    public function getGrade(): string
    {
        $total = $this->getTotalStats();

        return match (true) {
            $total >= 4750 => 'S',
            $total >= 4250 => 'A',
            $total >= 3750 => 'B',
            $total >= 3250 => 'C',
            $total >= 2750 => 'D',
            $total >= 2250 => 'E',
            $total >= 1750 => 'F',
            default => 'G',
        };
    }

    /**
     * Get size classes
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'w-48',
            'md' => 'w-64',
            'lg' => 'w-80',
            default => 'w-64',
        };
    }

    /**
     * Get view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.character-card');
    }
}
