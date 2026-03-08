<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('Spinner Component', function () {
    it('renders with default props', function () {
        $html = Blade::render('<x-spinner />');

        expect($html)
            ->toContain('<svg')
            ->toContain('role="status"')
            ->toContain('aria-label="Loading"')
            ->toContain('animate-spin')
            ->toContain('h-6 w-6')
            ->toContain('text-primary-600');
    });

    it('renders xs size', function () {
        $html = Blade::render('<x-spinner size="xs" />');

        expect($html)
            ->toContain('h-3 w-3');
    });

    it('renders sm size', function () {
        $html = Blade::render('<x-spinner size="sm" />');

        expect($html)
            ->toContain('h-4 w-4');
    });

    it('renders md size (default)', function () {
        $html = Blade::render('<x-spinner size="md" />');

        expect($html)
            ->toContain('h-6 w-6');
    });

    it('renders lg size', function () {
        $html = Blade::render('<x-spinner size="lg" />');

        expect($html)
            ->toContain('h-8 w-8');
    });

    it('renders xl size', function () {
        $html = Blade::render('<x-spinner size="xl" />');

        expect($html)
            ->toContain('h-12 w-12');
    });

    it('renders primary color (default)', function () {
        $html = Blade::render('<x-spinner color="primary" />');

        expect($html)
            ->toContain('text-primary-600');
    });

    it('renders gray color', function () {
        $html = Blade::render('<x-spinner color="gray" />');

        expect($html)
            ->toContain('text-neutral-500')
            ->toContain('dark:text-neutral-400');
    });

    it('renders white color', function () {
        $html = Blade::render('<x-spinner color="white" />');

        expect($html)
            ->toContain('text-white');
    });

    it('renders custom aria-label', function () {
        $html = Blade::render('<x-spinner label="Saving changes" />');

        expect($html)
            ->toContain('aria-label="Saving changes"')
            ->toContain('Saving changes');
    });

    it('has reduced motion support', function () {
        $html = Blade::render('<x-spinner />');

        expect($html)
            ->toContain('motion-reduce:');
    });

    it('has screen reader text', function () {
        $html = Blade::render('<x-spinner label="Processing" />');

        expect($html)
            ->toContain('sr-only')
            ->toContain('Processing');
    });

    it('merges additional attributes', function () {
        $html = Blade::render('<x-spinner id="my-spinner" data-test="value" />');

        expect($html)
            ->toContain('id="my-spinner"')
            ->toContain('data-test="value"');
    });
});
