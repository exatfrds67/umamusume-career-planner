<?php

use Illuminate\Support\Facades\Blade;

describe('Button Component', function () {
    it('renders primary button with default size', function () {
        $html = Blade::render('<x-button variant="primary">Click Me</x-button>');

        expect($html)
            ->toContain('btn')
            ->toContain('btn-primary')
            ->toContain('btn-md')
            ->toContain('Click Me')
            ->toContain('type="button"');
    });

    it('renders secondary button', function () {
        $html = Blade::render('<x-button variant="secondary">Secondary</x-button>');

        expect($html)
            ->toContain('btn-secondary')
            ->toContain('Secondary');
    });

    it('renders outline button', function () {
        $html = Blade::render('<x-button variant="outline">Outline</x-button>');

        expect($html)
            ->toContain('btn-outline')
            ->toContain('Outline');
    });

    it('renders small button', function () {
        $html = Blade::render('<x-button size="sm">Small</x-button>');

        expect($html)
            ->toContain('btn-sm')
            ->toContain('Small');
    });

    it('renders medium button', function () {
        $html = Blade::render('<x-button size="md">Medium</x-button>');

        expect($html)
            ->toContain('btn-md')
            ->toContain('Medium');
    });

    it('renders large button', function () {
        $html = Blade::render('<x-button size="lg">Large</x-button>');

        expect($html)
            ->toContain('btn-lg')
            ->toContain('Large');
    });

    it('renders disabled button', function () {
        $html = Blade::render('<x-button disabled>Disabled</x-button>');

        expect($html)
            ->toContain('disabled')
            ->toContain('aria-disabled="true"')
            ->toContain('Disabled');
    });

    it('renders loading button with spinner', function () {
        $html = Blade::render('<x-button loading>Loading</x-button>');

        expect($html)
            ->toContain('aria-busy="true"')
            ->toContain('animate-spin')
            ->toContain('Loading...')
            ->toContain('Loading');
    });

    it('renders button as link when href is provided', function () {
        $html = Blade::render('<x-button href="/dashboard">Go to Dashboard</x-button>');

        expect($html)
            ->toContain('href="/dashboard"')
            ->toContain('role="button"')
            ->toContain('Go to Dashboard')
            ->not->toContain('<button');
    });

    it('renders button with aria-label', function () {
        $html = Blade::render('<x-button aria-label="Save changes">Save</x-button>');

        expect($html)
            ->toContain('aria-label="Save changes"')
            ->toContain('Save');
    });

    it('renders submit button when type is submit', function () {
        $html = Blade::render('<x-button type="submit">Submit</x-button>');

        expect($html)
            ->toContain('type="submit"')
            ->toContain('Submit');
    });

    it('applies custom classes', function () {
        $html = Blade::render('<x-button class="custom-class">Custom</x-button>');

        expect($html)
            ->toContain('custom-class')
            ->toContain('btn')
            ->toContain('Custom');
    });

    it('renders loading button as disabled', function () {
        $html = Blade::render('<x-button loading>Processing</x-button>');

        expect($html)
            ->toContain('disabled')
            ->toContain('aria-disabled="true"')
            ->toContain('aria-busy="true"');
    });

    it('does not render link when disabled', function () {
        $html = Blade::render('<x-button href="/dashboard" disabled>Disabled Link</x-button>');

        expect($html)
            ->not->toContain('href="/dashboard"')
            ->toContain('<button')
            ->toContain('disabled');
    });

    it('does not render link when loading', function () {
        $html = Blade::render('<x-button href="/dashboard" loading>Loading Link</x-button>');

        expect($html)
            ->not->toContain('href="/dashboard"')
            ->toContain('<button')
            ->toContain('disabled');
    });

    it('passes through additional attributes', function () {
        $html = Blade::render('<x-button id="my-button" data-test="value">Button</x-button>');

        expect($html)
            ->toContain('id="my-button"')
            ->toContain('data-test="value"')
            ->toContain('Button');
    });

    it('renders with all size variants', function () {
        $sizes = ['sm', 'md', 'lg'];

        foreach ($sizes as $size) {
            $html = Blade::render("<x-button size=\"{$size}\">Button</x-button>");
            expect($html)->toContain("btn-{$size}");
        }
    });

    it('renders with all variant types', function () {
        $variants = ['primary', 'secondary', 'outline'];

        foreach ($variants as $variant) {
            $html = Blade::render("<x-button variant=\"{$variant}\">Button</x-button>");
            expect($html)->toContain("btn-{$variant}");
        }
    });
});
