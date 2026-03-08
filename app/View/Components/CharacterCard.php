<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CharacterCard extends Component
{
    /** @var array<string, mixed> */
    public array $character;

    public bool $showStats;

    public bool $showAptitudes;

    public string $size;

    public bool $selectable;

    /**
     * Create a new component instance.
     *
     * @param  array<string, mixed>  $character
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
        $name = $this->character['name'] ?? 'Unknown';

        return \is_string($name) ? $name : 'Unknown';
    }

    /**
     * Get character avatar URL
     */
    public function getAvatarUrl(): ?string
    {
        $candidates = [
            $this->character['avatar_url'] ?? null,
            $this->character['image'] ?? null,
            $this->character['image_url'] ?? null,
            $this->character['thumb_img'] ?? null,
            $this->character['avatar_path'] ?? null,
            $this->character['image_path'] ?? null,
        ];

        foreach ($candidates as $url) {
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return null;
    }

    /**
     * Get character stats
     *
     * @return array<string, int>
     */
    public function getStats(): array
    {
        $speed = $this->character['speed'] ?? 0;
        $stamina = $this->character['stamina'] ?? 0;
        $power = $this->character['power'] ?? 0;
        $guts = $this->character['guts'] ?? 0;
        $wit = $this->character['wit'] ?? $this->character['wisdom'] ?? 0;

        return [
            'speed' => \is_int($speed) ? $speed : (\is_numeric($speed) ? (int) $speed : 0),
            'stamina' => \is_int($stamina) ? $stamina : (\is_numeric($stamina) ? (int) $stamina : 0),
            'power' => \is_int($power) ? $power : (\is_numeric($power) ? (int) $power : 0),
            'guts' => \is_int($guts) ? $guts : (\is_numeric($guts) ? (int) $guts : 0),
            'wit' => \is_int($wit) ? $wit : (\is_numeric($wit) ? (int) $wit : 0),
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
