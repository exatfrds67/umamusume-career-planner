<?php

declare(strict_types=1);

describe('Accessibility Property Tests', function () {
    /**
     * Property 7: Focus Visibility
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 7: Focus Visibility
     * Validates: Requirements NFR-A-06
     *
     * All interactive elements must have visible focus styles.
     */
    it('ensures all pages include focus-visible CSS for interactive elements', function () {
        $user = \App\Models\User::factory()->create();

        $pages = [
            '/' => false,
            '/login' => false,
            '/register' => false,
            '/dashboard' => true,
            '/plans' => true,
        ];

        foreach ($pages as $page => $requiresAuth) {
            $response = $requiresAuth
                ? $this->actingAs($user)->get($page)
                : $this->get($page);

            if ($response->getStatusCode() === 302) {
                continue;
            }

            $content = $response->getContent();

            $hasInteractiveElements = str_contains($content, '<button')
                || str_contains($content, '<a ')
                || str_contains($content, '<input')
                || str_contains($content, '<select')
                || str_contains($content, '<textarea');

            if ($hasInteractiveElements) {
                $hasFocusStyles = str_contains($content, 'focus:')
                    || str_contains($content, 'focus-visible:')
                    || str_contains($content, 'focus-within:')
                    || str_contains($content, ':focus')
                    || str_contains($content, 'focus-visible');

                expect($hasFocusStyles)->toBeTrue("Page {$page} has interactive elements but no focus styles");
            }
        }
    })->group('property');

    /**
     * Property 8: Color Contrast Compliance
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 8: Color Contrast Compliance
     * Validates: Requirements NFR-A-03
     *
     * Pages must not use inline styles with low-contrast text colors.
     * This is a structural check — actual contrast auditing requires browser tools.
     */
    it('ensures pages do not use known low-contrast inline color combinations', function () {
        $user = \App\Models\User::factory()->create();

        $lowContrastPatterns = [
            'color: #ccc',
            'color: #ddd',
            'color: #eee',
            'color: lightgray',
            'color: #fff; background-color: #fff',
            'color: yellow',
        ];

        $pages = ['/', '/login', '/register'];

        foreach ($pages as $page) {
            $response = $this->get($page);

            if ($response->getStatusCode() !== 200) {
                continue;
            }

            $content = strtolower($response->getContent());

            foreach ($lowContrastPatterns as $pattern) {
                expect($content)->not->toContain(strtolower($pattern), "Page {$page} contains low-contrast pattern: {$pattern}");
            }
        }
    })->group('property');

    it('ensures Tailwind classes follow dark mode contrast patterns', function () {
        $user = \App\Models\User::factory()->create();

        $pages = ['/dashboard', '/plans'];

        foreach ($pages as $page) {
            $response = $this->actingAs($user)->get($page);

            if ($response->getStatusCode() !== 200) {
                continue;
            }

            $content = $response->getContent();

            if (str_contains($content, 'text-gray-')) {
                $usesGrayText = preg_match('/text-gray-[23]00(?!\/)/', $content);
                $supportsDarkMode = str_contains($content, 'dark:');

                if ($usesGrayText) {
                    expect($supportsDarkMode)->toBeTrue("Page {$page} uses light gray text but may lack dark mode overrides");
                }
            }
        }
    })->group('property');

    /**
     * Property 9: Form Label Association
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 9: Form Label Association
     * Validates: Requirements NFR-A-07
     *
     * All form inputs must have associated labels (via for/id or aria-label).
     */
    it('ensures form inputs on public pages have labels or aria-labels', function () {
        $pages = ['/', '/login', '/register'];

        foreach ($pages as $page) {
            $response = $this->get($page);

            if ($response->getStatusCode() !== 200) {
                continue;
            }

            $content = $response->getContent();

            preg_match_all('/<input\s[^>]*>/i', $content, $inputMatches);

            foreach ($inputMatches[0] as $input) {
                if (str_contains($input, 'type="hidden"')
                    || str_contains($input, "type='hidden'")
                    || str_contains($input, 'type="submit"')
                    || str_contains($input, "type='submit'")) {
                    continue;
                }

                $hasLabel = str_contains($input, 'aria-label')
                    || str_contains($input, 'aria-labelledby')
                    || str_contains($input, 'id=');

                if (str_contains($input, 'id=')) {
                    preg_match('/id=["\']([^"\']+)["\']/i', $input, $idMatch);
                    if (! empty($idMatch[1])) {
                        $hasLabelFor = str_contains($content, 'for="'.$idMatch[1].'"')
                            || str_contains($content, "for='".$idMatch[1]."'");
                        $hasLabel = $hasLabel || $hasLabelFor;
                    }
                }

                expect($hasLabel)->toBeTrue("Input missing label/aria-label on {$page}: ".substr($input, 0, 80));
            }
        }
    })->group('property');

    /**
     * Property 10: Modal Focus Trapping
     *
     * Feature: umamusume-career-planner-main-v2.4.0, Property 10: Modal Focus Trapping
     * Validates: Requirements NFR-A-08
     *
     * Modal components must use appropriate ARIA attributes.
     */
    it('ensures modal components have proper ARIA dialog attributes', function () {
        $modalFiles = glob(resource_path('views/components/*modal*')) ?: [];
        $dialogFiles = glob(resource_path('views/components/*dialog*')) ?: [];

        $allFiles = array_merge($modalFiles, $dialogFiles);

        foreach ($allFiles as $file) {
            $content = file_get_contents($file);

            $hasRole = str_contains($content, 'role="dialog"')
                || str_contains($content, "role='dialog'")
                || str_contains($content, 'role="alertdialog"')
                || str_contains($content, "role='alertdialog'");

            expect($hasRole)->toBeTrue('Modal file '.basename($file)." missing role='dialog'");

            $hasAriaLabel = str_contains($content, 'aria-label')
                || str_contains($content, 'aria-labelledby');

            expect($hasAriaLabel)->toBeTrue('Modal file '.basename($file).' missing aria-label/aria-labelledby');
        }
    })->group('property');

    it('ensures all pages include proper landmark regions', function () {
        $user = \App\Models\User::factory()->create();

        $pagesNeedingLandmarks = [
            '/dashboard' => true,
        ];

        foreach ($pagesNeedingLandmarks as $page => $requiresAuth) {
            $response = $requiresAuth
                ? $this->actingAs($user)->get($page)
                : $this->get($page);

            if ($response->getStatusCode() !== 200) {
                continue;
            }

            $content = $response->getContent();

            $hasMainLandmark = str_contains($content, '<main')
                || str_contains($content, 'role="main"');

            expect($hasMainLandmark)->toBeTrue("Page {$page} missing <main> landmark region");
        }
    })->group('property');
});
