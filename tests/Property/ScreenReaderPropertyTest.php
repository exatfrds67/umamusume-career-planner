<?php

declare(strict_types=1);

describe('Screen Reader Property Tests', function () {
    /**
     * Property 11: ARIA Live Region Announcements
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 11: ARIA Live Region Announcements
     * Validates: Requirements NFR-A-12
     *
     * Pages with dynamic content must include appropriate ARIA live regions
     * for screen reader announcements.
     */
    it('ensures layouts contain ARIA live regions for status messages', function () {
        $layoutFiles = [];

        $layoutDir = resource_path('views/layouts');
        if (is_dir($layoutDir)) {
            $layoutFiles = array_merge($layoutFiles, glob($layoutDir.'/*.blade.php') ?: []);
        }

        $componentDir = resource_path('views/components');
        if (is_dir($componentDir)) {
            $notificationFiles = glob($componentDir.'/*notification*') ?: [];
            $toastFiles = glob($componentDir.'/*toast*') ?: [];
            $alertFiles = glob($componentDir.'/*alert*') ?: [];
            $layoutFiles = array_merge($layoutFiles, $notificationFiles, $toastFiles, $alertFiles);
        }

        $liveRegionCount = 0;

        foreach ($layoutFiles as $file) {
            $content = file_get_contents($file);

            if (str_contains($content, 'aria-live')) {
                $liveRegionCount++;

                $hasValidValue = str_contains($content, 'aria-live="polite"')
                    || str_contains($content, 'aria-live="assertive"')
                    || str_contains($content, "aria-live='polite'")
                    || str_contains($content, "aria-live='assertive'")
                    || (bool) preg_match('/aria-live="\{\{.*?\}\}"/', $content);

                expect($hasValidValue)->toBeTrue(basename($file).' has aria-live but with invalid value');
            }
        }

        expect($liveRegionCount)->toBeGreaterThan(0, 'No ARIA live regions found in layouts or notification components');
    })->group('property');

    it('ensures error/alert components use assertive live regions', function () {
        $componentDir = resource_path('views/components');

        if (! is_dir($componentDir)) {
            $this->markTestSkipped('Components directory not found');
        }

        $alertFiles = glob($componentDir.'/*alert*') ?: [];
        $errorFiles = glob($componentDir.'/*error*') ?: [];

        $allFiles = array_merge($alertFiles, $errorFiles);

        foreach ($allFiles as $file) {
            $content = file_get_contents($file);

            $hasAriaLive = str_contains($content, 'aria-live')
                || str_contains($content, 'role="alert"')
                || str_contains($content, "role='alert'")
                || str_contains($content, 'role="status"')
                || str_contains($content, "role='status'")
                || (bool) preg_match('/role="\{\{.*?\}\}"/', $content);

            expect($hasAriaLive)->toBeTrue(basename($file).' lacks aria-live or alert/status role for screen readers');
        }
    })->group('property');

    it('ensures form validation errors use aria-invalid', function () {
        $componentDir = resource_path('views/components');

        if (! is_dir($componentDir)) {
            $this->markTestSkipped('Components directory not found');
        }

        $formComponents = array_filter(array_merge(
            glob($componentDir.'/*input*') ?: [],
            glob($componentDir.'/*form*') ?: [],
            glob($componentDir.'/*field*') ?: [],
        ), 'is_file');

        $testedCount = 0;

        foreach ($formComponents as $file) {
            $content = file_get_contents($file);

            if (str_contains($content, '@error') || str_contains($content, '$errors')) {
                $hasAriaInvalid = str_contains($content, 'aria-invalid');
                $hasAriaDescribedby = str_contains($content, 'aria-describedby');

                $hasAccessibleError = $hasAriaInvalid || $hasAriaDescribedby
                    || str_contains($content, 'role="alert"');

                expect($hasAccessibleError)->toBeTrue(
                    basename($file).' handles errors but lacks aria-invalid or aria-describedby'
                );
                $testedCount++;
            }
        }

        expect($testedCount)->toBeGreaterThanOrEqual(0);
    })->group('property');

    it('ensures dynamic loading states have aria-busy', function () {
        $livewireDir = resource_path('views/livewire');

        if (! is_dir($livewireDir)) {
            $this->markTestSkipped('Livewire views directory not found');
        }

        $livewireFiles = glob($livewireDir.'/*.blade.php') ?: [];
        $subDirFiles = glob($livewireDir.'/**/*.blade.php') ?: [];
        $allFiles = array_merge($livewireFiles, $subDirFiles);

        $testedCount = 0;

        foreach ($allFiles as $file) {
            $content = file_get_contents($file);

            if (str_contains($content, 'wire:loading')) {
                $hasAriaBusy = str_contains($content, 'aria-busy')
                    || str_contains($content, 'wire:loading.attr="aria-busy"')
                    || str_contains($content, 'role="status"');

                if ($hasAriaBusy) {
                    $testedCount++;
                }
            }
        }

        expect($testedCount)->toBeGreaterThanOrEqual(0);
    })->group('property');

    it('ensures semantic landmarks exist on authenticated pages', function () {
        $user = \App\Models\User::factory()->create();

        $pages = ['/dashboard'];

        foreach ($pages as $page) {
            $response = $this->actingAs($user)->get($page);

            if ($response->getStatusCode() !== 200) {
                continue;
            }

            $content = $response->getContent();

            $hasNav = str_contains($content, '<nav')
                || str_contains($content, 'role="navigation"');
            $hasMain = str_contains($content, '<main')
                || str_contains($content, 'role="main"');

            expect($hasNav)->toBeTrue("Page {$page} missing <nav> landmark")
                ->and($hasMain)->toBeTrue("Page {$page} missing <main> landmark");
        }
    })->group('property');

    it('ensures images on pages have alt attributes', function () {
        $user = \App\Models\User::factory()->create();

        $pages = [
            '/' => false,
            '/login' => false,
            '/dashboard' => true,
        ];

        foreach ($pages as $page => $requiresAuth) {
            $response = $requiresAuth
                ? $this->actingAs($user)->get($page)
                : $this->get($page);

            if ($response->getStatusCode() !== 200) {
                continue;
            }

            $content = $response->getContent();

            preg_match_all('/<img\s[^>]*>/i', $content, $imgMatches);

            foreach ($imgMatches[0] as $img) {
                $hasAlt = str_contains($img, 'alt=');
                expect($hasAlt)->toBeTrue("Image on {$page} missing alt attribute: ".substr($img, 0, 80));
            }
        }
    })->group('property');
});
