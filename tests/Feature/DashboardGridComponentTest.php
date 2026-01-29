<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('DashboardGrid Component', function () {
    it('renders with default props', function () {
        $html = Blade::render('<x-dashboard-grid>Content here</x-dashboard-grid>');

        expect($html)
            ->toContain('grid')
            ->toContain('grid-cols-1')
            ->toContain('lg:grid-cols-3')
            ->toContain('gap-4')
            ->toContain('Content here')
            ->toContain('role="region"');
    });

    it('renders with custom columns', function () {
        $html = Blade::render('<x-dashboard-grid :columns="4">Content</x-dashboard-grid>');

        expect($html)
            ->toContain('xl:grid-cols-4')
            ->toContain('lg:grid-cols-3');
    });

    it('renders with 2 columns', function () {
        $html = Blade::render('<x-dashboard-grid :columns="2">Content</x-dashboard-grid>');

        expect($html)
            ->toContain('sm:grid-cols-2')
            ->not->toContain('lg:grid-cols-3');
    });

    it('renders with 1 column', function () {
        $html = Blade::render('<x-dashboard-grid :columns="1">Content</x-dashboard-grid>');

        expect($html)
            ->toContain('grid-cols-1')
            ->not->toContain('sm:grid-cols-2');
    });

    it('renders with small gap', function () {
        $html = Blade::render('<x-dashboard-grid gap="sm">Content</x-dashboard-grid>');

        expect($html)
            ->toContain('gap-2')
            ->toContain('sm:gap-3');
    });

    it('renders with large gap', function () {
        $html = Blade::render('<x-dashboard-grid gap="lg">Content</x-dashboard-grid>');

        expect($html)
            ->toContain('gap-6')
            ->toContain('sm:gap-8');
    });

    it('merges additional attributes', function () {
        $html = Blade::render('<x-dashboard-grid id="my-grid" data-test="value">Content</x-dashboard-grid>');

        expect($html)
            ->toContain('id="my-grid"')
            ->toContain('data-test="value"');
    });
});
