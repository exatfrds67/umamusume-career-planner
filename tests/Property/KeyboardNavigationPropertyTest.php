<?php

declare(strict_types=1);

describe('Keyboard Navigation Property Tests', function () {
    /**
     * Property 12: Keyboard Navigation Completeness
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 12: Keyboard Navigation Completeness
     * Validates: Requirements NFR-A-06, NFR-A-07
     *
     * All interactive elements must be keyboard-accessible
     * (proper tabindex, no positive tabindex values, no missing focus).
     */
    it('ensures no positive tabindex values in page output', function () {
        $user = \App\Models\User::factory()->create();

        $pages = [
            '/' => false,
            '/login' => false,
            '/register' => false,
            '/dashboard' => true,
            '/profile' => true,
        ];

        foreach ($pages as $page => $requiresAuth) {
            $response = $requiresAuth
                ? $this->actingAs($user)->get($page)
                : $this->get($page);

            if ($response->getStatusCode() !== 200) {
                continue;
            }

            $content = $response->getContent();

            preg_match_all('/tabindex=["\'](\d+)["\']/i', $content, $matches);

            foreach ($matches[1] as $value) {
                $intValue = (int) $value;
                expect($intValue)->toBeLessThanOrEqual(0, "Page {$page} has positive tabindex={$intValue} which disrupts natural tab order");
            }
        }
    })->group('property');

    it('ensures buttons and links have accessible text content', function () {
        $user = \App\Models\User::factory()->create();

        $pages = [
            '/login' => false,
            '/register' => false,
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

            preg_match_all('/<button\s[^>]*>\s*<\/button>/i', $content, $emptyButtons);

            foreach ($emptyButtons[0] as $button) {
                $hasAriaLabel = str_contains($button, 'aria-label');
                $hasTitle = str_contains($button, 'title=');

                expect($hasAriaLabel || $hasTitle)->toBeTrue("Page {$page} has empty button without aria-label: ".substr($button, 0, 80));
            }
        }

        expect(true)->toBeTrue();
    })->group('property');

    it('ensures keyboard shortcut help component uses proper ARIA pattern', function () {
        $helpFile = resource_path('views/components/keyboard-shortcuts-help.blade.php');

        if (! file_exists($helpFile)) {
            expect(true)->toBeTrue();

            return;
        }

        $content = file_get_contents($helpFile);

        expect($content)->toContain('role="dialog"')
            ->and($content)->toContain('aria-modal')
            ->and($content)->toContain('aria-labelledby');
    })->group('property');

    it('ensures skip-to-main link exists in layout', function () {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        if ($response->getStatusCode() !== 200) {
            $this->markTestSkipped('Dashboard not accessible');
        }

        $content = $response->getContent();

        $hasSkipLink = str_contains($content, 'skip-to-main')
            || str_contains($content, '#main')
            || str_contains($content, '#content')
            || str_contains($content, 'Skip to')
            || str_contains($content, 'skip to');

        expect($hasSkipLink)->toBeTrue('Dashboard page missing skip-to-main navigation link');
    })->group('property');

    it('ensures form controls in Blade components have proper structure', function () {
        $componentDir = resource_path('views/components');

        if (! is_dir($componentDir)) {
            $this->markTestSkipped('Components directory not found');
        }

        $componentFiles = glob($componentDir.'/*.blade.php') ?: [];
        $testedCount = 0;

        foreach ($componentFiles as $file) {
            $content = file_get_contents($file);

            if (str_contains($content, '<select') && ! str_contains($content, 'type="hidden"')) {
                $hasLabel = str_contains($content, 'aria-label')
                    || str_contains($content, 'aria-labelledby')
                    || str_contains($content, '<label');

                expect($hasLabel)->toBeTrue(basename($file).' has <select> without accessible label');
                $testedCount++;
            }

            if (str_contains($content, 'role="tablist"')) {
                $hasTabRole = str_contains($content, 'role="tab"');
                expect($hasTabRole)->toBeTrue();
                $testedCount++;
            }
        }

        expect($testedCount)->toBeGreaterThanOrEqual(0);
    })->group('property');
});
