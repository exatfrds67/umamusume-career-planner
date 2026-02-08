<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SupportCard extends Component
{
    /** @var array<string, mixed> */
    public array $card;

    public int $level;

    public int $limitBreak;

    public ?int $bondLevel;

    public string $size;

    public bool $showEffects;

    /**
     * Valid card types
     */
    private const CARD_TYPES = ['Speed', 'Stamina', 'Power', 'Guts', 'Wit', 'Friend'];

    /**
     * Create a new component instance.
     *
     * @param  array<string, mixed>  $card
     */
    public function __construct(
        array $card,
        int $level = 1,
        int $limitBreak = 0,
        ?int $bondLevel = null,
        string $size = 'md',
        bool $showEffects = true
    ) {
        $this->card = $card;
        $this->level = min(50, max(1, $level));
        $this->limitBreak = min(4, max(0, $limitBreak));
        $this->bondLevel = $bondLevel !== null ? min(100, max(0, $bondLevel)) : null;
        $this->size = $size;
        $this->showEffects = $showEffects;
    }

    /**
     * Get card name
     */
    public function getName(): string
    {
        $name = $this->card['name'] ?? 'Unknown Card';

        return \is_string($name) ? $name : 'Unknown Card';
    }

    /**
     * Get card type
     */
    public function getType(): string
    {
        $type = $this->card['type'] ?? 'Friend';
        $typeStr = \is_string($type) ? $type : 'Friend';

        return \in_array($typeStr, self::CARD_TYPES, true) ? $typeStr : 'Friend';
    }

    /**
     * Get card rarity (SSR, SR, R)
     */
    public function getRarity(): string
    {
        $rarity = $this->card['rarity'] ?? 'R';

        return \is_string($rarity) ? $rarity : 'R';
    }

    /**
     * Get card image URL
     */
    public function getImageUrl(): ?string
    {
        $url = $this->card['image_url'] ?? null;

        if ($url === null) {
            return null;
        }

        return \is_string($url) ? $url : null;
    }

    /**
     * Get type color class
     */
    public function getTypeColor(): string
    {
        return match ($this->getType()) {
            'Speed' => 'stat-speed',
            'Stamina' => 'stat-stamina',
            'Power' => 'stat-power',
            'Guts' => 'stat-guts',
            'Wit' => 'stat-wit',
            'Friend' => 'secondary',
            default => 'neutral',
        };
    }

    /**
     * Get rarity stars
     */
    public function getRarityStars(): int
    {
        return match ($this->getRarity()) {
            'SSR' => 3,
            'SR' => 2,
            'R' => 1,
            default => 1,
        };
    }

    /**
     * Get card effects
     *
     * @return array<int, mixed>
     */
    public function getEffects(): array
    {
        $effects = $this->card['effects'] ?? [];

        return is_array($effects) ? $effects : [];
    }

    /**
     * Check if bond is at rainbow level (80%+)
     */
    public function isRainbowBond(): bool
    {
        return $this->bondLevel !== null && $this->bondLevel >= 80;
    }

    /**
     * Get size classes
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'w-32',
            'md' => 'w-48',
            'lg' => 'w-64',
            default => 'w-48',
        };
    }

    /**
     * Get view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.support-card');
    }
}
