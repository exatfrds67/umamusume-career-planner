<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Helpers\ImageOptimizationHelper;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Optimized Image Component
 *
 * Renders images with modern format support, lazy loading, and responsive srcset.
 */
class OptimizedImage extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $src,
        public string $alt,
        public string $type = 'default',
        public ?string $placeholder = null,
        public ?string $class = null,
        public ?string $width = null,
        public ?string $height = null,
        public bool $lazy = true,
        public bool $responsive = false,
        /** @var array<int> */
        public array $widths = [],
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|string
    {
        return view('components.optimized-image');
    }

    /**
     * Get the image HTML based on type
     */
    public function getImageHtml(): string
    {
        $attributes = $this->buildAttributes();

        return match ($this->type) {
            'picture' => ImageOptimizationHelper::picture($this->src, $this->alt, $attributes),
            'responsive' => ImageOptimizationHelper::responsiveImage($this->src, $this->alt, $this->widths, $attributes),
            'lazy' => ImageOptimizationHelper::lazyImage($this->src, $this->alt, $this->placeholder, $attributes),
            'blur-up' => $this->placeholder
                ? ImageOptimizationHelper::blurUp($this->src, $this->placeholder, $this->alt, $attributes)
                : ImageOptimizationHelper::lazyImage($this->src, $this->alt, null, $attributes),
            default => $this->getDefaultImage($attributes),
        };
    }

    /**
     * Build attributes array
     *
     * @return array<string, mixed>
     */
    private function buildAttributes(): array
    {
        $attributes = [];

        if ($this->class) {
            $attributes['class'] = $this->class;
        }

        if ($this->width) {
            $attributes['width'] = $this->width;
        }

        if ($this->height) {
            $attributes['height'] = $this->height;
        }

        return $attributes;
    }

    /**
     * Get default image HTML
     *
     * @param  array<string, mixed>  $attributes
     */
    private function getDefaultImage(array $attributes): string
    {
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= sprintf(' %s="%s"', $key, htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
        }

        $loadingAttr = $this->lazy ? ' loading="lazy" decoding="async"' : '';

        return sprintf(
            '<img src="%s" alt="%s"%s%s>',
            $this->src,
            htmlspecialchars($this->alt, ENT_QUOTES, 'UTF-8'),
            $loadingAttr,
            $attrString
        );
    }
}
