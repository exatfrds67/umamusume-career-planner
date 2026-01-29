<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('SelectDropdown Component', function () {
    it('renders with required props', function () {
        $html = Blade::render('
            <x-form.select-dropdown
                name="grade"
                :options="[\'S\' => \'S Rank\', \'A\' => \'A Rank\']"
            />
        ');

        expect($html)
            ->toContain('name="grade"')
            ->toContain('S Rank')
            ->toContain('A Rank')
            ->toContain('form-group');
    });

    it('renders with label', function () {
        $html = Blade::render('
            <x-form.select-dropdown
                name="grade"
                label="Select Grade"
                :options="[\'S\' => \'S Rank\']"
            />
        ');

        expect($html)
            ->toContain('Select Grade')
            ->toContain('<label');
    });

    it('renders placeholder option', function () {
        $html = Blade::render('
            <x-form.select-dropdown
                name="grade"
                placeholder="Choose a grade"
                :options="[\'S\' => \'S Rank\']"
            />
        ');

        expect($html)
            ->toContain('Choose a grade')
            ->toContain('disabled');
    });

    it('renders with selected value', function () {
        $html = Blade::render('
            <x-form.select-dropdown
                name="grade"
                value="A"
                :options="[\'S\' => \'S Rank\', \'A\' => \'A Rank\']"
            />
        ');

        expect($html)
            ->toMatch('/value="A"[^>]*selected/');
    });

    it('renders required indicator', function () {
        $html = Blade::render('
            <x-form.select-dropdown
                name="grade"
                label="Grade"
                :required="true"
                :options="[\'S\' => \'S\']"
            />
        ');

        expect($html)
            ->toContain('aria-required="true"')
            ->toContain('required')
            ->toContain('(required)');
    });

    it('renders error state', function () {
        $html = Blade::render('
            <x-form.select-dropdown
                name="grade"
                error="Please select a grade"
                :options="[\'S\' => \'S\']"
            />
        ');

        expect($html)
            ->toContain('Please select a grade')
            ->toContain('aria-invalid="true"')
            ->toContain('role="alert"')
            ->toContain('border-error-500');
    });

    it('renders hint text', function () {
        $html = Blade::render('
            <x-form.select-dropdown
                name="grade"
                hint="Higher grades are better"
                :options="[\'S\' => \'S\']"
            />
        ');

        expect($html)
            ->toContain('Higher grades are better');
    });

    it('renders disabled state', function () {
        $html = Blade::render('
            <x-form.select-dropdown
                name="grade"
                :disabled="true"
                :options="[\'S\' => \'S\']"
            />
        ');

        expect($html)
            ->toContain('disabled')
            ->toContain('aria-disabled="true"');
    });

    it('renders dropdown icon', function () {
        $html = Blade::render('
            <x-form.select-dropdown
                name="grade"
                :options="[\'S\' => \'S\']"
            />
        ');

        expect($html)
            ->toContain('<svg')
            ->toContain('pointer-events-none');
    });

    it('normalizes array options format', function () {
        $html = Blade::render('
            <x-form.select-dropdown
                name="stat"
                :options="[[\'value\' => \'speed\', \'label\' => \'Speed\'], [\'value\' => \'stamina\', \'label\' => \'Stamina\']]"
            />
        ');

        expect($html)
            ->toContain('value="speed"')
            ->toContain('Speed')
            ->toContain('value="stamina"')
            ->toContain('Stamina');
    });
});
