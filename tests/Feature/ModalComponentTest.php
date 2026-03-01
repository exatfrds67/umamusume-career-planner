<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('Modal Component', function () {
    it('renders with required name prop', function () {
        $html = Blade::render('<x-modal name="confirm">Modal content</x-modal>');

        expect($html)
            ->toContain('Modal content')
            ->toContain('role="dialog"')
            ->toContain('aria-modal="true"');
    });

    it('renders with title', function () {
        $html = Blade::render('<x-modal name="confirm" title="Confirm Action">Content</x-modal>');

        expect($html)
            ->toContain('Confirm Action')
            ->toContain('aria-labelledby="modal-title-confirm"')
            ->toContain('id="modal-title-confirm"');
    });

    it('renders close button when closeable', function () {
        $html = Blade::render('<x-modal name="confirm" :closeable="true">Content</x-modal>');

        expect($html)
            ->toContain('aria-label="Close modal"');
    });

    it('hides close button when not closeable', function () {
        $html = Blade::render('<x-modal name="confirm" :closeable="false">Content</x-modal>');

        expect($html)
            ->not->toContain('aria-label="Close modal"');
    });

    it('renders footer slot', function () {
        $html = Blade::render('
            <x-modal name="confirm">
                Content
                <x-slot:footer>
                    <button>Cancel</button>
                    <button>Confirm</button>
                </x-slot:footer>
            </x-modal>
        ');

        expect($html)
            ->toContain('<button>Cancel</button>')
            ->toContain('<button>Confirm</button>');
    });

    it('renders small size', function () {
        $html = Blade::render('<x-modal name="confirm" size="sm">Content</x-modal>');

        expect($html)
            ->toContain('max-w-md');
    });

    it('renders medium size (default)', function () {
        $html = Blade::render('<x-modal name="confirm">Content</x-modal>');

        expect($html)
            ->toContain('max-w-lg');
    });

    it('renders large size', function () {
        $html = Blade::render('<x-modal name="confirm" size="lg">Content</x-modal>');

        expect($html)
            ->toContain('max-w-2xl');
    });

    it('renders xl size', function () {
        $html = Blade::render('<x-modal name="confirm" size="xl">Content</x-modal>');

        expect($html)
            ->toContain('max-w-4xl');
    });

    it('renders full size', function () {
        $html = Blade::render('<x-modal name="confirm" size="full">Content</x-modal>');

        expect($html)
            ->toContain('max-w-full');
    });

    it('includes Alpine.js event listeners', function () {
        $html = Blade::render('<x-modal name="test-modal">Content</x-modal>');

        expect($html)
            ->toContain('@open-modal.window')
            ->toContain('@close-modal.window')
            ->toContain('@keydown.escape.window');
    });

    it('includes focus trap functionality', function () {
        $html = Blade::render('<x-modal name="confirm">Content</x-modal>');

        expect($html)
            ->toContain('trapFocus')
            ->toContain('focusables');
    });

    it('has backdrop overlay', function () {
        $html = Blade::render('<x-modal name="confirm">Content</x-modal>');

        expect($html)
            ->toContain('fixed inset-0 z-50')
            ->toContain('bg-gray-900/50')
            ->toContain('backdrop-blur-xs');
    });
});
