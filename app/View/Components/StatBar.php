<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatBar extends Component
{
    /**
     * Semantic token map for stat-specific UI colors.
     *
     * @var array<string, array<int, string>>
     */
    private const STAT_TOKEN_MAP = [
        'speed' => [
            '200' => '--color-stat-speed-200',
            '300' => '--color-stat-speed-300',
            '400' => '--color-stat-speed-400',
            '500' => '--color-stat-speed-500',
            '600' => '--color-stat-speed-600',
            '700' => '--color-stat-speed-700',
            '800' => '--color-stat-speed-800',
        ],
        'stamina' => [
            '200' => '--color-stat-stamina-200',
            '300' => '--color-stat-stamina-300',
            '400' => '--color-stat-stamina-400',
            '500' => '--color-stat-stamina-500',
            '600' => '--color-stat-stamina-600',
            '700' => '--color-stat-stamina-700',
            '800' => '--color-stat-stamina-800',
        ],
        'power' => [
            '200' => '--color-stat-power-200',
            '300' => '--color-stat-power-300',
            '400' => '--color-stat-power-400',
            '500' => '--color-stat-power-500',
            '600' => '--color-stat-power-600',
            '700' => '--color-stat-power-700',
            '800' => '--color-stat-power-800',
        ],
        'guts' => [
            '200' => '--color-stat-guts-200',
            '300' => '--color-stat-guts-300',
            '400' => '--color-stat-guts-400',
            '500' => '--color-stat-guts-500',
            '600' => '--color-stat-guts-600',
            '700' => '--color-stat-guts-700',
            '800' => '--color-stat-guts-800',
        ],
        'wit' => [
            '200' => '--color-stat-wit-200',
            '300' => '--color-stat-wit-300',
            '400' => '--color-stat-wit-400',
            '500' => '--color-stat-wit-500',
            '600' => '--color-stat-wit-600',
            '700' => '--color-stat-wit-700',
            '800' => '--color-stat-wit-800',
        ],
        'wisdom' => [
            '200' => '--color-stat-wit-200',
            '300' => '--color-stat-wit-300',
            '400' => '--color-stat-wit-400',
            '500' => '--color-stat-wit-500',
            '600' => '--color-stat-wit-600',
            '700' => '--color-stat-wit-700',
            '800' => '--color-stat-wit-800',
        ],
    ];

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
        int|string $current,
        int|string $max = 2000,
        int|string|null $target = null,
        int|string|null $factorBonus = null,
        bool $showIcon = true,
        bool $showPercentage = false,
        bool $showSoftCap = true,
        bool $showLabel = true,
        string $size = 'md'
    ) {
        $this->stat = strtolower($stat);
        $this->current = (int) $current;
        $this->max = (int) $max;
        $this->target = $target !== null ? (int) $target : null;
        $this->factorBonus = $factorBonus !== null ? (int) $factorBonus : null;
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
     * Get CSS variable tokens for the current stat.
     *
     * @return array<int, string>
     */
    public function getStatTokens(): array
    {
        return self::STAT_TOKEN_MAP[$this->stat] ?? [
            '200' => '--color-primary-200',
            '300' => '--color-primary-300',
            '400' => '--color-primary-400',
            '500' => '--color-primary-500',
            '600' => '--color-primary-600',
            '700' => '--color-primary-700',
            '800' => '--color-primary-800',
        ];
    }

    /**
     * Get inline CSS custom properties for semantic stat rendering.
     */
    public function getStatStyle(): string
    {
        $tokens = $this->getStatTokens();

        return implode('; ', array_map(
            static fn (int $shade, string $token): string => "--stat-{$shade}: var({$token})",
            array_keys($tokens),
            $tokens,
        ));
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
