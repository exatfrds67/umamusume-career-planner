<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('WizardLayout Component', function () {
    it('renders with required props', function () {
        $html = Blade::render('
            <x-wizard-layout :steps="[\'Step 1\', \'Step 2\', \'Step 3\']" :current-step="0">
                Form content here
            </x-wizard-layout>
        ');

        expect($html)
            ->toContain('wizard-layout')
            ->toContain('Form content here')
            ->toContain('Step 1 of 3')
            ->toContain('Step 1')
            ->toContain('Step 2')
            ->toContain('Step 3')
            ->toContain('role="region"')
            ->toContain('role="progressbar"');
    });

    it('renders progress percentage correctly', function () {
        $html = Blade::render('
            <x-wizard-layout :steps="[\'A\', \'B\', \'C\', \'D\']" :current-step="1">
                Content
            </x-wizard-layout>
        ');

        expect($html)
            ->toContain('Step 2 of 4')
            ->toContain('50%');
    });

    it('renders with title prop', function () {
        $html = Blade::render('
            <x-wizard-layout :steps="[\'Info\']" :current-step="0" title="Create Character">
                Content
            </x-wizard-layout>
        ');

        expect($html)
            ->toContain('Create Character');
    });

    it('renders header slot', function () {
        $html = Blade::render('
            <x-wizard-layout :steps="[\'Info\']" :current-step="0">
                <x-slot:header>Custom Header Title</x-slot:header>
                Content
            </x-wizard-layout>
        ');

        expect($html)
            ->toContain('Custom Header Title');
    });

    it('renders footer slot', function () {
        $html = Blade::render('
            <x-wizard-layout :steps="[\'Info\']" :current-step="0">
                Content
                <x-slot:footer>
                    <button>Next</button>
                </x-slot:footer>
            </x-wizard-layout>
        ');

        expect($html)
            ->toContain('wizard-footer')
            ->toContain('<button>Next</button>');
    });

    it('marks completed steps with checkmark', function () {
        $html = Blade::render('
            <x-wizard-layout :steps="[\'Done\', \'Current\', \'Future\']" :current-step="1">
                Content
            </x-wizard-layout>
        ');

        expect($html)
            ->toContain('Completed:')
            ->toContain('aria-current="step"');
    });

    it('shows current step indicator', function () {
        $html = Blade::render('
            <x-wizard-layout :steps="[\'First\', \'Second\']" :current-step="1">
                Content
            </x-wizard-layout>
        ');

        expect($html)
            ->toContain('aria-current="step"');
    });

    it('renders with aria attributes for accessibility', function () {
        $html = Blade::render('
            <x-wizard-layout :steps="[\'A\', \'B\']" :current-step="0">
                Content
            </x-wizard-layout>
        ');

        expect($html)
            ->toContain('aria-valuenow="1"')
            ->toContain('aria-valuemin="1"')
            ->toContain('aria-valuemax="2"')
            ->toContain('aria-label="Wizard steps"');
    });
});
