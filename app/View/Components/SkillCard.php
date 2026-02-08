<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SkillCard extends Component
{
    /** @var array<string, mixed> */
    public array $skill;

    public int $hintLevel;

    public bool $acquired;

    public bool $showCost;

    public bool $showDiscount;

    public string $size;

    /**
     * Hint discount rates (verified from game mechanics)
     * Source: docs/research/game-mechanics-research-report.md
     */
    private const HINT_DISCOUNTS = [
        0 => 0.00,  // No discount
        1 => 0.10,  // 10%
        2 => 0.20,  // 20%
        3 => 0.30,  // 30%
        4 => 0.35,  // 35%
        5 => 0.40,  // 40% (maximum)
    ];

    /**
     * Create a new component instance.
     *
     * @param  array<string, mixed>  $skill
     */
    public function __construct(
        array $skill,
        int $hintLevel = 0,
        bool $acquired = false,
        bool $showCost = true,
        bool $showDiscount = true,
        string $size = 'md'
    ) {
        $this->skill = $skill;
        $this->hintLevel = min(5, max(0, $hintLevel));
        $this->acquired = $acquired;
        $this->showCost = $showCost;
        $this->showDiscount = $showDiscount;
        $this->size = $size;
    }

    /**
     * Get skill name
     */
    public function getName(): string
    {
        $name = $this->skill['name'] ?? 'Unknown Skill';

        return \is_string($name) ? $name : 'Unknown Skill';
    }

    /**
     * Get skill description
     */
    public function getDescription(): string
    {
        $description = $this->skill['description'] ?? '';

        return \is_string($description) ? $description : '';
    }

    /**
     * Get base SP cost
     */
    public function getBaseCost(): int
    {
        $cost = $this->skill['base_sp_cost'] ?? 0;

        return \is_int($cost) ? $cost : (\is_numeric($cost) ? (int) $cost : 0);
    }

    /**
     * Calculate final cost with hint discount
     */
    public function getFinalCost(): int
    {
        $baseCost = $this->getBaseCost();
        $discount = self::HINT_DISCOUNTS[$this->hintLevel] ?? 0;

        return (int) round($baseCost * (1 - $discount));
    }

    /**
     * Get discount amount
     */
    public function getDiscountAmount(): int
    {
        return $this->getBaseCost() - $this->getFinalCost();
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentage(): int
    {
        return (int) (self::HINT_DISCOUNTS[$this->hintLevel] * 100);
    }

    /**
     * Get skill rarity
     */
    public function getRarity(): string
    {
        $rarity = $this->skill['rarity'] ?? 'normal';

        return \is_string($rarity) ? $rarity : 'normal';
    }

    /**
     * Get skill type
     */
    public function getType(): string
    {
        $type = $this->skill['type'] ?? 'general';

        return \is_string($type) ? $type : 'general';
    }

    /**
     * Get rarity color
     */
    public function getRarityColor(): string
    {
        return match (strtolower($this->getRarity())) {
            'rare', 'gold' => 'warning',
            'unique' => 'secondary',
            default => 'neutral',
        };
    }

    /**
     * Get size classes
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'p-3',
            'md' => 'p-4',
            'lg' => 'p-6',
            default => 'p-4',
        };
    }

    /**
     * Get view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.skill-card');
    }
}
