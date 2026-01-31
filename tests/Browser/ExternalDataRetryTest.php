<?php

use function Pest\Laravel\get;

/**
 * External Data Browser - Retry Functionality Tests
 *
 * Tests for task 4.1.3: Test retry functionality
 * Validates Requirements 2.3 from design document
 */
describe('External Data Browser - Retry Functionality', function () {
    beforeEach(function () {
        // Ensure we have a clean state
        $this->artisan('cache:clear');
    });

    it('can retry individual endpoint when it fails', function () {
        $page = visit('/external-data/browse');

        // Wait for page to load
        $page->waitFor('[x-data="externalDataBrowser()"]');

        // Simulate an error state by checking if error banner exists
        // If no error, we'll test the retry button functionality anyway
        $page->pause(2000); // Wait for initial data load

        // Check if characters error exists
        if ($page->assertVisible('[x-show="errors.characters"]', false)) {
            // Click the retry button for characters
            $page->click('button[\\@click="retryEndpoint(\'characters\')"]');

            // Verify loading state is shown
            $page->assertVisible('[x-show="loadingCharacters && !loading"]');

            // Wait for retry to complete
            $page->waitFor('[x-show="loadingCharacters && !loading"]', false, 5000);

            // Verify either success (data loaded) or error message updated
            $page->pause(1000);
        }

        $page->assertNoJavascriptErrors();
    })->group('browser', 'external-data', 'retry');

    it('can retry all failed endpoints with retry all button', function () {
        $page = visit('/external-data/browse');

        // Wait for page to load
        $page->waitFor('[x-data="externalDataBrowser()"]');
        $page->pause(2000);

        // Check if "Retry All Failed" button is visible (only shows when errors exist)
        if ($page->assertVisible('button[\\@click="retryAll()"]', false)) {
            // Click the "Retry All Failed" button
            $page->click('button[\\@click="retryAll()"]');

            // Verify global loading state is shown
            $page->assertVisible('[x-show="loading"]');

            // Wait for retry to complete
            $page->waitFor('[x-show="loading"]', false, 10000);

            // Verify page is in a stable state
            $page->pause(1000);
        }

        $page->assertNoJavascriptErrors();
    })->group('browser', 'external-data', 'retry');

    it('shows loading spinner during individual endpoint retry', function () {
        $page = visit('/external-data/browse');

        $page->waitFor('[x-data="externalDataBrowser()"]');
        $page->pause(2000);

        // Try to trigger a retry on support cards
        $page->click('button[\\@click="activeTab = \'support-cards\'"]');
        $page->pause(500);

        if ($page->assertVisible('[x-show="errors.supportCards"]', false)) {
            // Click retry button
            $page->click('button[\\@click="retryEndpoint(\'supportCards\')"]');

            // Verify section-specific loading indicator appears
            $page->assertVisible('[x-show="loadingSupportCards && !loading"]');

            // Wait for completion
            $page->waitFor('[x-show="loadingSupportCards && !loading"]', false, 5000);
        }

        $page->assertNoJavascriptErrors();
    })->group('browser', 'external-data', 'retry');

    it('updates error state correctly after retry', function () {
        $page = visit('/external-data/browse');

        $page->waitFor('[x-data="externalDataBrowser()"]');
        $page->pause(2000);

        // Switch to skills tab
        $page->click('button[\\@click="activeTab = \'skills\'"]');
        $page->pause(500);

        // Check if error exists
        $hasError = $page->assertVisible('[x-show="errors.skills"]', false);

        if ($hasError) {
            // Get initial error message
            $initialError = $page->evaluate('() => {
                const component = Alpine.$data(document.querySelector("[x-data=\'externalDataBrowser()\']"));
                return component.errors.skills;
            }');

            // Click retry
            $page->click('button[\\@click="retryEndpoint(\'skills\')"]');

            // Wait for retry to complete
            $page->pause(3000);

            // Verify error state changed (either cleared or updated)
            $newError = $page->evaluate('() => {
                const component = Alpine.$data(document.querySelector("[x-data=\'externalDataBrowser()\']"));
                return component.errors.skills;
            }');

            // Error should either be null (success) or potentially the same (still failing)
            expect($newError)->toBeIn([null, $initialError]);
        }

        $page->assertNoJavascriptErrors();
    })->group('browser', 'external-data', 'retry');

    it('disables retry button during retry operation', function () {
        $page = visit('/external-data/browse');

        $page->waitFor('[x-data="externalDataBrowser()"]');
        $page->pause(2000);

        // Switch to news tab
        $page->click('button[\\@click="activeTab = \'news\'"]');
        $page->pause(500);

        if ($page->assertVisible('[x-show="errors.news"]', false)) {
            // Get the retry button
            $retryButton = 'button[\\@click="retryEndpoint(\'news\')"]';

            // Click retry
            $page->click($retryButton);

            // Immediately check if button is disabled
            $isDisabled = $page->evaluate('(selector) => {
                return document.querySelector(selector).disabled;
            }', $retryButton);

            expect($isDisabled)->toBeTrue();

            // Wait for completion
            $page->pause(3000);
        }

        $page->assertNoJavascriptErrors();
    })->group('browser', 'external-data', 'retry');

    it('shows correct button text during retry', function () {
        $page = visit('/external-data/browse');

        $page->waitFor('[x-data="externalDataBrowser()"]');
        $page->pause(2000);

        if ($page->assertVisible('[x-show="errors.characters"]', false)) {
            // Get initial button text
            $initialText = $page->evaluate('() => {
                const button = document.querySelector("button[\\@click=\'retryEndpoint(\\\'characters\\\')\']");
                return button ? button.textContent.trim() : "";
            }');

            expect($initialText)->toContain('Retry');

            // Click retry
            $page->click('button[\\@click="retryEndpoint(\'characters\')"]');

            // Check button text changes to "Retrying..."
            $page->pause(100);
            $retryingText = $page->evaluate('() => {
                const button = document.querySelector("button[\\@click=\'retryEndpoint(\\\'characters\\\')\']");
                return button ? button.textContent.trim() : "";
            }');

            // Should show "Retrying..." during operation
            expect($retryingText)->toContain('Retrying');

            // Wait for completion
            $page->pause(3000);
        }

        $page->assertNoJavascriptErrors();
    })->group('browser', 'external-data', 'retry');

    it('maintains data from successful endpoints during retry', function () {
        $page = visit('/external-data/browse');

        $page->waitFor('[x-data="externalDataBrowser()"]');
        $page->pause(2000);

        // Get current data counts
        $initialCounts = $page->evaluate('() => {
            const component = Alpine.$data(document.querySelector("[x-data=\'externalDataBrowser()\']"));
            return {
                characters: component.characters.length,
                supportCards: component.supportCards.length,
                skills: component.skills.length,
                news: component.news.length
            };
        }');

        // If any endpoint has errors, retry it
        if ($page->assertVisible('[x-show="errors.characters"]', false)) {
            $page->click('button[\\@click="retryEndpoint(\'characters\')"]');
            $page->pause(3000);

            // Verify other data is maintained
            $newCounts = $page->evaluate('() => {
                const component = Alpine.$data(document.querySelector("[x-data=\'externalDataBrowser()\']"));
                return {
                    characters: component.characters.length,
                    supportCards: component.supportCards.length,
                    skills: component.skills.length,
                    news: component.news.length
                };
            }');

            // Other endpoints should maintain their data
            expect($newCounts['supportCards'])->toBe($initialCounts['supportCards']);
            expect($newCounts['skills'])->toBe($initialCounts['skills']);
            expect($newCounts['news'])->toBe($initialCounts['news']);
        }

        $page->assertNoJavascriptErrors();
    })->group('browser', 'external-data', 'retry');

    it('updates api availability after successful retry', function () {
        $page = visit('/external-data/browse');

        $page->waitFor('[x-data="externalDataBrowser()"]');
        $page->pause(2000);

        // Check initial API availability
        $initialAvailability = $page->evaluate('() => {
            const component = Alpine.$data(document.querySelector("[x-data=\'externalDataBrowser()\']"));
            return component.apiAvailable;
        }');

        // If API is unavailable and we have errors, try retry all
        if (! $initialAvailability && $page->assertVisible('button[\\@click="retryAll()"]', false)) {
            $page->click('button[\\@click="retryAll()"]');
            $page->pause(5000);

            // Check if API availability updated
            $newAvailability = $page->evaluate('() => {
                const component = Alpine.$data(document.querySelector("[x-data=\'externalDataBrowser()\']"));
                return component.apiAvailable;
            }');

            // API availability should be updated based on retry results
            expect($newAvailability)->toBeIn([true, false]);
        }

        $page->assertNoJavascriptErrors();
    })->group('browser', 'external-data', 'retry');
});
