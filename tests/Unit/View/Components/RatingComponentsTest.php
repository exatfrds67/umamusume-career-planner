<?php

use App\View\Components\PotentialBadge;
use App\View\Components\StarRating;

describe('StarRating Component', function () {
    it('clamps stars to valid range', function () {
        $component = new StarRating(stars: 10, maxStars: 5);
        expect($component->stars)->toBe(5);

        $component = new StarRating(stars: -1, maxStars: 5);
        expect($component->stars)->toBe(0);
    });

    it('returns correct size classes', function () {
        expect((new StarRating(size: 'sm'))->starSize())->toBe('w-3 h-3')
            ->and((new StarRating(size: 'md'))->starSize())->toBe('w-4 h-4')
            ->and((new StarRating(size: 'lg'))->starSize())->toBe('w-6 h-6');
    });

    it('renders with correct number of stars', function () {
        $view = $this->blade('<x-star-rating :stars="3" :max-stars="5" />');

        $view->assertSee('text-yellow-400 fill-current', false)
            ->assertDontSee('★'); // Should use SVG, not unicode
    });

    it('shows count when enabled', function () {
        $view = $this->blade('<x-star-rating :stars="4" :max-stars="5" :show-count="true" />');

        $view->assertSee('4/5');
    });
});

describe('PotentialBadge Component', function () {
    it('clamps level to valid range 1-9', function () {
        $component = new PotentialBadge(level: 15);
        expect($component->level)->toBe(9);

        $component = new PotentialBadge(level: 0);
        expect($component->level)->toBe(1);
    });

    it('returns correct color for level ranges', function () {
        expect((new PotentialBadge(level: 9))->badgeColor())
            ->toContain('purple-500')
            ->and((new PotentialBadge(level: 5))->badgeColor())
            ->toContain('blue-500')
            ->and((new PotentialBadge(level: 2))->badgeColor())
            ->toContain('gray-500');
    });

    it('returns correct size classes', function () {
        expect((new PotentialBadge(size: 'sm'))->sizeClasses())
            ->toContain('text-xs')
            ->and((new PotentialBadge(size: 'md'))->sizeClasses())
            ->toContain('text-sm')
            ->and((new PotentialBadge(size: 'lg'))->sizeClasses())
            ->toContain('text-base');
    });

    it('renders with level number', function () {
        $view = $this->blade('<x-potential-badge :level="7" />');

        $view->assertSee('Lv')
            ->assertSee('7');
    });

    it('hides label when showLabel is false', function () {
        $view = $this->blade('<x-potential-badge :level="5" :show-label="false" />');

        $view->assertDontSee('Lv')
            ->assertSee('5');
    });
});
