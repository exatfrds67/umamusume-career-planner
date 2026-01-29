<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('AlertBanner Component', function () {
    it('renders with default type (info)', function () {
        $html = Blade::render('<x-alert-banner>Alert message here</x-alert-banner>');

        expect($html)
            ->toContain('Alert message here')
            ->toContain('role="alert"')
            ->toContain('bg-info-50');
    });

    it('renders success type', function () {
        $html = Blade::render('<x-alert-banner type="success">Success message</x-alert-banner>');

        expect($html)
            ->toContain('Success message')
            ->toContain('bg-success-50')
            ->toContain('text-success');
    });

    it('renders error type', function () {
        $html = Blade::render('<x-alert-banner type="error">Error message</x-alert-banner>');

        expect($html)
            ->toContain('Error message')
            ->toContain('bg-error-50')
            ->toContain('text-error');
    });

    it('renders warning type', function () {
        $html = Blade::render('<x-alert-banner type="warning">Warning message</x-alert-banner>');

        expect($html)
            ->toContain('Warning message')
            ->toContain('bg-warning-50')
            ->toContain('text-warning');
    });

    it('renders with title', function () {
        $html = Blade::render('<x-alert-banner title="Important Notice">Details here</x-alert-banner>');

        expect($html)
            ->toContain('Important Notice')
            ->toContain('Details here')
            ->toContain('<h3');
    });

    it('renders dismiss button when dismissible', function () {
        $html = Blade::render('<x-alert-banner :dismissible="true">Message</x-alert-banner>');

        expect($html)
            ->toContain('aria-label="Dismiss alert"')
            ->toContain('@click="show = false"');
    });

    it('hides dismiss button when not dismissible', function () {
        $html = Blade::render('<x-alert-banner :dismissible="false">Message</x-alert-banner>');

        expect($html)
            ->not->toContain('aria-label="Dismiss alert"');
    });

    it('includes Alpine.js show/hide transitions', function () {
        $html = Blade::render('<x-alert-banner>Message</x-alert-banner>');

        expect($html)
            ->toContain('x-show="show"')
            ->toContain('x-transition:enter')
            ->toContain('x-transition:leave');
    });

    it('renders appropriate icon for each type', function () {
        $successHtml = Blade::render('<x-alert-banner type="success">Msg</x-alert-banner>');
        $errorHtml = Blade::render('<x-alert-banner type="error">Msg</x-alert-banner>');
        $warningHtml = Blade::render('<x-alert-banner type="warning">Msg</x-alert-banner>');
        $infoHtml = Blade::render('<x-alert-banner type="info">Msg</x-alert-banner>');

        expect($successHtml)->toContain('<svg');
        expect($errorHtml)->toContain('<svg');
        expect($warningHtml)->toContain('<svg');
        expect($infoHtml)->toContain('<svg');
    });

    it('merges additional attributes', function () {
        $html = Blade::render('<x-alert-banner id="my-alert" data-test="value">Message</x-alert-banner>');

        expect($html)
            ->toContain('id="my-alert"')
            ->toContain('data-test="value"');
    });
});
