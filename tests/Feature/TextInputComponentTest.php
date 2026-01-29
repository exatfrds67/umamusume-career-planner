<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('TextInput Component', function () {
    it('renders with required name prop', function () {
        $html = Blade::render('<x-form.text-input name="email" />');

        expect($html)
            ->toContain('name="email"')
            ->toContain('type="text"')
            ->toContain('form-group');
    });

    it('renders with label', function () {
        $html = Blade::render('<x-form.text-input name="email" label="Email Address" />');

        expect($html)
            ->toContain('Email Address')
            ->toContain('<label');
    });

    it('renders required indicator', function () {
        $html = Blade::render('<x-form.text-input name="email" label="Email" :required="true" />');

        expect($html)
            ->toContain('aria-required="true"')
            ->toContain('required')
            ->toContain('(required)');
    });

    it('renders with custom type', function () {
        $html = Blade::render('<x-form.text-input name="password" type="password" />');

        expect($html)
            ->toContain('type="password"');
    });

    it('renders email type', function () {
        $html = Blade::render('<x-form.text-input name="email" type="email" />');

        expect($html)
            ->toContain('type="email"');
    });

    it('renders with placeholder', function () {
        $html = Blade::render('<x-form.text-input name="email" placeholder="Enter your email" />');

        expect($html)
            ->toContain('placeholder="Enter your email"');
    });

    it('renders with value', function () {
        $html = Blade::render('<x-form.text-input name="email" value="test@example.com" />');

        expect($html)
            ->toContain('value="test@example.com"');
    });

    it('renders error state', function () {
        $html = Blade::render('<x-form.text-input name="email" error="Email is required" />');

        expect($html)
            ->toContain('Email is required')
            ->toContain('aria-invalid="true"')
            ->toContain('role="alert"')
            ->toContain('border-error-500');
    });

    it('renders hint text', function () {
        $html = Blade::render('<x-form.text-input name="email" hint="We will not share your email" />');

        expect($html)
            ->toContain('We will not share your email');
    });

    it('hides hint when error is shown', function () {
        $html = Blade::render('<x-form.text-input name="email" error="Error" hint="Hint" />');

        expect($html)
            ->toContain('Error')
            ->not->toContain('Hint');
    });

    it('renders disabled state', function () {
        $html = Blade::render('<x-form.text-input name="email" :disabled="true" />');

        expect($html)
            ->toContain('disabled')
            ->toContain('aria-disabled="true"')
            ->toContain('opacity-50');
    });

    it('renders readonly state', function () {
        $html = Blade::render('<x-form.text-input name="email" :readonly="true" />');

        expect($html)
            ->toContain('readonly');
    });

    it('connects label to input with for attribute', function () {
        $html = Blade::render('<x-form.text-input name="email" label="Email" />');

        expect($html)
            ->toMatch('/for="[^"]*email[^"]*"/');
    });

    it('connects error to input with aria-describedby', function () {
        $html = Blade::render('<x-form.text-input name="email" error="Required" />');

        expect($html)
            ->toContain('aria-describedby');
    });
});
