<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('SkeletonCard Component', function () {
    it('renders with default props', function () {
        $html = Blade::render('<x-skeleton-card />');

        expect($html)
            ->toContain('animate-pulse')
            ->toContain('aria-hidden="true"')
            ->toContain('role="presentation"')
            ->toContain('rounded-lg');
    });

    it('renders default 3 text lines', function () {
        $html = Blade::render('<x-skeleton-card />');

        // Should have title + 3 lines
        expect(substr_count($html, 'h-3'))->toBe(3);
    });

    it('renders custom number of lines', function () {
        $html = Blade::render('<x-skeleton-card :lines="5" />');

        expect(substr_count($html, 'h-3'))->toBe(5);
    });

    it('renders image placeholder when showImage is true', function () {
        $html = Blade::render('<x-skeleton-card :show-image="true" />');

        expect($html)
            ->toContain('h-40 w-full');
    });

    it('does not render image placeholder by default', function () {
        $html = Blade::render('<x-skeleton-card />');

        expect($html)
            ->not->toContain('h-40 w-full');
    });

    it('renders avatar placeholder when showAvatar is true', function () {
        $html = Blade::render('<x-skeleton-card :show-avatar="true" />');

        expect($html)
            ->toContain('h-10 w-10 rounded-full');
    });

    it('does not render avatar placeholder by default', function () {
        $html = Blade::render('<x-skeleton-card />');

        expect($html)
            ->not->toContain('h-10 w-10 rounded-full');
    });

    it('renders action buttons when showActions is true', function () {
        $html = Blade::render('<x-skeleton-card :show-actions="true" />');

        expect($html)
            ->toContain('h-9 w-20');
    });

    it('does not render action buttons by default', function () {
        $html = Blade::render('<x-skeleton-card />');

        expect($html)
            ->not->toContain('h-9 w-20');
    });

    it('renders all elements together', function () {
        $html = Blade::render('<x-skeleton-card :lines="2" :show-image="true" :show-avatar="true" :show-actions="true" />');

        expect($html)
            ->toContain('h-40 w-full') // image
            ->toContain('h-10 w-10 rounded-full') // avatar
            ->toContain('h-9 w-20'); // actions

        // Avatar placeholder adds extra h-3 and h-4 elements for name/subtitle
        // So we check that content lines exist via varying widths
        expect($html)
            ->toContain('w-full')
            ->toContain('w-5/6');
    });

    it('has varying line widths', function () {
        $html = Blade::render('<x-skeleton-card :lines="5" />');

        expect($html)
            ->toContain('w-full')
            ->toContain('w-5/6')
            ->toContain('w-4/5')
            ->toContain('w-3/4')
            ->toContain('w-2/3');
    });

    it('merges additional attributes', function () {
        $html = Blade::render('<x-skeleton-card id="loading-card" data-test="value" />');

        expect($html)
            ->toContain('id="loading-card"')
            ->toContain('data-test="value"');
    });

    it('supports dark mode styling', function () {
        $html = Blade::render('<x-skeleton-card />');

        expect($html)
            ->toContain('dark:bg-gray-700')
            ->toContain('dark:border-gray-700');
    });
});
