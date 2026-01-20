<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\File;

/**
 * Image Optimization Helper
 *
 * Provides utilities for optimizing images with modern formats (AVIF, WebP),
 * responsive image generation, and lazy loading attributes.
 */
class ImageOptimizationHelper
{
    /**
     * Supported modern image formats in order of preference
     */
    public const MODERN_FORMATS = ['avif', 'webp'];

    /**
     * Default responsive image widths
     */
    public const DEFAULT_WIDTHS = [320, 640, 768, 1024, 1280, 1536];

    /**
     * Generate picture element HTML with modern format sources
     *
     * @param  string  $src  Original image source
     * @param  string  $alt  Alt text for accessibility
     * @param  array<string, mixed>  $attributes  Additional attributes
     * @return string HTML picture element
     */
    public static function picture(string $src, string $alt, array $attributes = []): string
    {
        $basePath = pathinfo($src, PATHINFO_DIRNAME);
        $filename = pathinfo($src, PATHINFO_FILENAME);
        $extension = pathinfo($src, PATHINFO_EXTENSION);

        $sources = [];

        // Add modern format sources
        foreach (self::MODERN_FORMATS as $format) {
            $modernSrc = "{$basePath}/{$filename}.{$format}";
            if (self::imageExists($modernSrc)) {
                $sources[] = sprintf(
                    '<source srcset="%s" type="image/%s">',
                    $modernSrc,
                    $format
                );
            }
        }

        // Build attributes string
        $attrString = self::buildAttributeString($attributes);

        // Build picture element
        $html = '<picture>';
        $html .= implode('', $sources);
        $html .= sprintf(
            '<img src="%s" alt="%s" loading="lazy" decoding="async"%s>',
            $src,
            htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'),
            $attrString
        );
        $html .= '</picture>';

        return $html;
    }

    /**
     * Generate responsive image with srcset
     *
     * @param  string  $src  Base image source
     * @param  string  $alt  Alt text for accessibility
     * @param  array<int>  $widths  Array of widths for srcset
     * @param  array<string, mixed>  $attributes  Additional attributes
     * @return string HTML img element with srcset
     */
    public static function responsiveImage(
        string $src,
        string $alt,
        array $widths = [],
        array $attributes = []
    ): string {
        $widths = $widths ?: self::DEFAULT_WIDTHS;
        $basePath = pathinfo($src, PATHINFO_DIRNAME);
        $filename = pathinfo($src, PATHINFO_FILENAME);
        $extension = pathinfo($src, PATHINFO_EXTENSION);

        $srcset = [];
        foreach ($widths as $width) {
            $responsiveSrc = "{$basePath}/{$filename}-{$width}w.{$extension}";
            if (self::imageExists($responsiveSrc)) {
                $srcset[] = "{$responsiveSrc} {$width}w";
            }
        }

        $srcsetAttr = ! empty($srcset) ? sprintf(' srcset="%s"', implode(', ', $srcset)) : '';
        $sizesAttr = ! empty($srcset) ? ' sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"' : '';

        $attrString = self::buildAttributeString($attributes);

        return sprintf(
            '<img src="%s" alt="%s" loading="lazy" decoding="async"%s%s%s>',
            $src,
            htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'),
            $srcsetAttr,
            $sizesAttr,
            $attrString
        );
    }

    /**
     * Generate lazy loading image HTML
     *
     * @param  string  $src  Image source
     * @param  string  $alt  Alt text for accessibility
     * @param  string|null  $placeholder  Low-quality placeholder image
     * @param  array<string, mixed>  $attributes  Additional attributes
     * @return string HTML for lazy loading image
     */
    public static function lazyImage(
        string $src,
        string $alt,
        ?string $placeholder = null,
        array $attributes = []
    ): string {
        $class = $attributes['class'] ?? '';
        $attributes['class'] = trim("lazy-image {$class}");
        $attributes['data-src'] = $src;

        $attrString = self::buildAttributeString($attributes);

        $initialSrc = $placeholder ?? self::generatePlaceholder();

        return sprintf(
            '<img src="%s" alt="%s" loading="lazy" decoding="async"%s>',
            $initialSrc,
            htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'),
            $attrString
        );
    }

    /**
     * Generate lazy loading background image div
     *
     * @param  string  $src  Background image source
     * @param  array<string, mixed>  $attributes  Additional attributes
     * @return string HTML for lazy loading background
     */
    public static function lazyBackground(string $src, array $attributes = []): string
    {
        $class = $attributes['class'] ?? '';
        $attributes['class'] = trim("lazy-background {$class}");
        $attributes['data-bg-src'] = $src;

        unset($attributes['class']);
        $attrString = self::buildAttributeString($attributes);

        return sprintf(
            '<div class="%s" data-bg-src="%s"%s></div>',
            trim("lazy-background {$class}"),
            $src,
            $attrString
        );
    }

    /**
     * Generate blur-up placeholder container
     *
     * @param  string  $src  Full image source
     * @param  string  $placeholder  Low-quality placeholder source
     * @param  string  $alt  Alt text for accessibility
     * @param  array<string, mixed>  $attributes  Additional attributes
     * @return string HTML for blur-up effect
     */
    public static function blurUp(
        string $src,
        string $placeholder,
        string $alt,
        array $attributes = []
    ): string {
        $attrString = self::buildAttributeString($attributes);

        return sprintf(
            '<div class="blur-up-container"%s>
                <img src="%s" alt="" class="blur-up-placeholder" aria-hidden="true">
                <img src="%s" alt="%s" class="blur-up-image lazy-image" data-src="%s" loading="lazy" decoding="async">
            </div>',
            $attrString,
            $placeholder,
            $placeholder,
            htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'),
            $src
        );
    }

    /**
     * Get the best available image format
     *
     * @param  string  $basePath  Base path without extension
     * @param  string  $fallbackExtension  Fallback extension
     * @return string Best available image path
     */
    public static function getBestFormat(string $basePath, string $fallbackExtension = 'png'): string
    {
        foreach (self::MODERN_FORMATS as $format) {
            $path = "{$basePath}.{$format}";
            if (self::imageExists($path)) {
                return $path;
            }
        }

        return "{$basePath}.{$fallbackExtension}";
    }

    /**
     * Generate preload link for critical images
     *
     * @param  string  $src  Image source
     * @param  string  $type  Image MIME type
     * @param  string|null  $media  Media query
     * @return string HTML link element for preloading
     */
    public static function preloadLink(string $src, string $type = 'image/png', ?string $media = null): string
    {
        $mediaAttr = $media ? sprintf(' media="%s"', $media) : '';

        return sprintf(
            '<link rel="preload" as="image" href="%s" type="%s"%s>',
            $src,
            $type,
            $mediaAttr
        );
    }

    /**
     * Generate preload links for responsive images
     *
     * @param  array<array{src: string, media: string, type?: string}>  $sources  Array of source configurations
     * @return string HTML link elements for preloading
     */
    public static function preloadResponsive(array $sources): string
    {
        $links = [];

        foreach ($sources as $source) {
            $type = $source['type'] ?? 'image/png';
            $links[] = self::preloadLink($source['src'], $type, $source['media'] ?? null);
        }

        return implode("\n", $links);
    }

    /**
     * Check if an image exists in public path
     *
     * @param  string  $path  Image path relative to public
     */
    public static function imageExists(string $path): bool
    {
        $fullPath = public_path(ltrim($path, '/'));

        return File::exists($fullPath);
    }

    /**
     * Generate a placeholder data URI
     *
     * @param  int  $width  Placeholder width
     * @param  int  $height  Placeholder height
     * @param  string  $color  Background color
     * @return string Data URI for placeholder
     */
    public static function generatePlaceholder(
        int $width = 1,
        int $height = 1,
        string $color = '#e5e5e5'
    ): string {
        // Generate a simple SVG placeholder
        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d"><rect fill="%s" width="100%%" height="100%%"/></svg>',
            $width,
            $height,
            $color
        );

        return 'data:image/svg+xml,'.rawurlencode($svg);
    }

    /**
     * Build HTML attribute string from array
     *
     * @param  array<string, mixed>  $attributes  Attributes array
     * @return string Attribute string
     */
    private static function buildAttributeString(array $attributes): string
    {
        $parts = [];

        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $parts[] = $key;
            } elseif ($value !== false && $value !== null) {
                $parts[] = sprintf('%s="%s"', $key, htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
            }
        }

        return ! empty($parts) ? ' '.implode(' ', $parts) : '';
    }

    /**
     * Get image dimensions from file
     *
     * @param  string  $path  Image path
     * @return array{width: int, height: int}|null
     */
    public static function getImageDimensions(string $path): ?array
    {
        $fullPath = public_path(ltrim($path, '/'));

        if (! File::exists($fullPath)) {
            return null;
        }

        $size = getimagesize($fullPath);

        if ($size === false) {
            return null;
        }

        return [
            'width' => $size[0],
            'height' => $size[1],
        ];
    }

    /**
     * Calculate aspect ratio padding for responsive containers
     *
     * @param  int  $width  Image width
     * @param  int  $height  Image height
     * @return float Padding percentage
     */
    public static function calculateAspectRatioPadding(int $width, int $height): float
    {
        if ($width === 0) {
            return 0;
        }

        return ($height / $width) * 100;
    }
}
