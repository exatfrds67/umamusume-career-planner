<?php

namespace Tests\Unit\View\Components;

use App\View\Components\TypeIcon;

describe('TypeIcon Component', function () {
    it('renders with default type (speed)', function () {
        $component = new TypeIcon;

        expect($component->type)->toBe('speed');
        expect($component->size)->toBe('md');
        expect($component->showLabel)->toBeFalse();
        expect($component->filled)->toBeTrue();
    });

    it('normalizes type to lowercase', function () {
        $component = new TypeIcon(type: 'STAMINA');

        expect($component->type)->toBe('stamina');
    });

    it('returns correct color classes for each stat type', function () {
        $types = [
            'speed' => 'text-blue-500 dark:text-blue-400',
            'stamina' => 'text-green-500 dark:text-green-400',
            'power' => 'text-orange-500 dark:text-orange-400',
            'guts' => 'text-amber-500 dark:text-amber-400',
            'wit' => 'text-sky-500 dark:text-sky-400',
            'wisdom' => 'text-sky-500 dark:text-sky-400',
            'friend' => 'text-pink-500 dark:text-pink-400',
        ];

        foreach ($types as $type => $expectedColor) {
            $component = new TypeIcon(type: $type);
            expect($component->colorClasses())->toBe($expectedColor);
        }
    });

    it('returns correct size classes', function () {
        $sizes = [
            'sm' => 'w-4 h-4',
            'md' => 'w-6 h-6',
            'lg' => 'w-8 h-8',
            'xl' => 'w-10 h-10',
        ];

        foreach ($sizes as $size => $expectedClasses) {
            $component = new TypeIcon(size: $size);
            expect($component->sizeClasses())->toBe($expectedClasses);
        }
    });

    it('returns correct icon symbols for each type', function () {
        $icons = [
            'speed' => '⚡',
            'stamina' => '💚',
            'power' => '💪',
            'guts' => '🔥',
            'wit' => '🧠',
            'wisdom' => '📚',
            'friend' => '👥',
        ];

        foreach ($icons as $type => $expectedIcon) {
            $component = new TypeIcon(type: $type);
            expect($component->iconSymbol())->toBe($expectedIcon);
        }
    });

    it('returns correct labels for each type', function () {
        $labels = [
            'speed' => 'Speed',
            'stamina' => 'Stamina',
            'power' => 'Power',
            'guts' => 'Guts',
            'wit' => 'Wisdom',
            'wisdom' => 'Wisdom',
            'friend' => 'Friend',
        ];

        foreach ($labels as $type => $expectedLabel) {
            $component = new TypeIcon(type: $type);
            expect($component->label())->toBe($expectedLabel);
        }
    });

    it('returns correct background colors for each type', function () {
        $bgColors = [
            'speed' => 'bg-blue-100 dark:bg-blue-900/30',
            'stamina' => 'bg-green-100 dark:bg-green-900/30',
            'power' => 'bg-orange-100 dark:bg-orange-900/30',
            'guts' => 'bg-amber-100 dark:bg-amber-900/30',
            'wit' => 'bg-sky-100 dark:bg-sky-900/30',
            'friend' => 'bg-pink-100 dark:bg-pink-900/30',
        ];

        foreach ($bgColors as $type => $expectedBgColor) {
            $component = new TypeIcon(type: $type);
            expect($component->bgColor())->toBe($expectedBgColor);
        }
    });

    it('renders component view correctly', function () {
        $component = new TypeIcon(type: 'speed', size: 'md', showLabel: true);
        $view = $component->render();

        expect($view)->not->toBeNull();
    });

    it('accepts custom constructor parameters', function () {
        $component = new TypeIcon(
            type: 'power',
            size: 'lg',
            showLabel: true,
            filled: false,
        );

        expect($component->type)->toBe('power');
        expect($component->size)->toBe('lg');
        expect($component->showLabel)->toBeTrue();
        expect($component->filled)->toBeFalse();
    });
});
