<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CharacterPortrait extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string|null  $image  Image path or URL (null uses default placeholder)
     * @param  string  $alt  Alt text for accessibility
     * @param  string  $size  Size variant (xs, sm, md, lg, xl, 2xl)
     * @param  bool  $rounded  Whether to use rounded corners
     * @param  string|null  $badge  Optional badge content (stars, level, etc.)
     * @param  string  $badgePosition  Badge position (top-right, top-left, bottom-right, bottom-left)
     * @param  bool  $showBorder  Whether to show game-style border
     * @param  string  $borderColor  Border color class
     */
    public function __construct(
        public ?string $image = null,
        public string $alt = '',
        public string $size = 'md',
        public bool $rounded = true,
        public ?string $badge = null,
        public string $badgePosition = 'top-right',
        public bool $showBorder = true,
        public string $borderColor = 'border-neutral-300 dark:border-neutral-600',
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.character-portrait');
    }

    /**
     * Get size classes for the portrait container.
     */
    public function sizeClasses(): string
    {
        return match ($this->size) {
            'xs' => 'w-12 h-12',
            'sm' => 'w-16 h-16',
            'md' => 'w-24 h-24',
            'lg' => 'w-32 h-32',
            'xl' => 'w-40 h-40',
            '2xl' => 'w-48 h-48',
            default => 'w-24 h-24',
        };
    }

    /**
     * Get badge position classes.
     */
    public function badgePositionClasses(): string
    {
        return match ($this->badgePosition) {
            'top-left' => 'top-0 left-0',
            'top-right' => 'top-0 right-0',
            'bottom-left' => 'bottom-0 left-0',
            'bottom-right' => 'bottom-0 right-0',
            default => 'top-0 right-0',
        };
    }

    /**
     * Get border classes.
     */
    public function borderClasses(): string
    {
        if (! $this->showBorder) {
            return '';
        }

        return "border-2 {$this->borderColor}";
    }

    /**
     * Get rounded classes.
     */
    public function roundedClasses(): string
    {
        return $this->rounded ? 'rounded-lg overflow-hidden' : '';
    }
}
