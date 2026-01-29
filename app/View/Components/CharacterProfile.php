<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CharacterProfile extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string  $image  Character portrait image URL
     * @param  string  $name  Character name
     * @param  string  $title  Character title/role
     * @param  string  $layout  Layout direction (row, column)
     * @param  bool  $imageLeft  Image position on left (true) or right (false)
     * @param  bool  $showBorder  Whether to show decorative border
     * @param  string  $imageSize  Size of image (small, medium, large)
     */
    public function __construct(
        public string $image = '',
        public string $name = '',
        public string $title = '',
        public string $layout = 'row',
        public bool $imageLeft = true,
        public bool $showBorder = true,
        public string $imageSize = 'medium',
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.character-profile');
    }

    /**
     * Get layout classes based on layout direction.
     */
    public function layoutClasses(): string
    {
        $baseClasses = 'flex gap-6 md:gap-8';
        $direction = $this->layout === 'column' ? 'flex-col' : 'flex-row';

        if ($this->layout === 'row' && ! $this->imageLeft) {
            return "$baseClasses $direction-reverse";
        }

        return "$baseClasses $direction";
    }

    /**
     * Get image section width classes.
     */
    public function imageSectionClasses(): string
    {
        return match ($this->layout) {
            'column' => 'w-full',
            default => 'w-full md:w-2/5 lg:w-1/3 flex-shrink-0',
        };
    }

    /**
     * Get content section width classes.
     */
    public function contentSectionClasses(): string
    {
        return match ($this->layout) {
            'column' => 'w-full',
            default => 'flex-1 min-w-0',
        };
    }

    /**
     * Get image size classes.
     */
    public function imageSizeClasses(): string
    {
        return match ($this->imageSize) {
            'small' => 'h-64',
            'large' => 'h-full min-h-96',
            default => 'h-80',
        };
    }
}
