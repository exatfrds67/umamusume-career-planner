<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('Toggle Component', function () {
    it('renders with required props', function () {
        $html = Blade::render('<x-form.toggle name="notifications" label="Enable notifications" />');

        expect($html)
            ->toContain('name="notifications"')
            ->toContain('Enable notifications')
            ->toContain('role="switch"')
            ->toContain('form-group');
    });

    it('renders checked state', function () {
        $html = Blade::render('<x-form.toggle name="notifications" label="Notifications" :checked="true" />');

        expect($html)
            ->toContain('enabled: true');
    });

    it('renders unchecked state', function () {
        $html = Blade::render('<x-form.toggle name="notifications" label="Notifications" :checked="false" />');

        expect($html)
            ->toContain('enabled: false');
    });

    it('renders hint text', function () {
        $html = Blade::render('<x-form.toggle name="notifications" label="Notifications" hint="Receive email updates" />');

        expect($html)
            ->toContain('Receive email updates');
    });

    it('renders error state', function () {
        $html = Blade::render('<x-form.toggle name="notifications" label="Notifications" error="Required field" />');

        expect($html)
            ->toContain('Required field')
            ->toContain('aria-invalid="true"')
            ->toContain('role="alert"')
            ->toContain('ring-2 ring-error-500');
    });

    it('renders disabled state', function () {
        $html = Blade::render('<x-form.toggle name="notifications" label="Notifications" :disabled="true" />');

        expect($html)
            ->toContain('disabled')
            ->toContain('aria-disabled="true"')
            ->toContain('opacity-50');
    });

    it('renders small size', function () {
        $html = Blade::render('<x-form.toggle name="notifications" label="Notifications" size="sm" />');

        expect($html)
            ->toContain('w-8 h-4')
            ->toContain('h-3 w-3');
    });

    it('renders medium size (default)', function () {
        $html = Blade::render('<x-form.toggle name="notifications" label="Notifications" />');

        expect($html)
            ->toContain('w-11 h-6')
            ->toContain('h-5 w-5');
    });

    it('renders large size', function () {
        $html = Blade::render('<x-form.toggle name="notifications" label="Notifications" size="lg" />');

        expect($html)
            ->toContain('w-14 h-7')
            ->toContain('h-6 w-6');
    });

    it('includes hidden input for form submission', function () {
        $html = Blade::render('<x-form.toggle name="notifications" label="Notifications" />');

        expect($html)
            ->toContain('type="hidden"')
            ->toContain(':value="enabled ? \'1\' : \'0\'"');
    });

    it('has screen reader text', function () {
        $html = Blade::render('<x-form.toggle name="notifications" label="Enable alerts" />');

        expect($html)
            ->toContain('sr-only')
            ->toContain('Enable alerts');
    });
});
