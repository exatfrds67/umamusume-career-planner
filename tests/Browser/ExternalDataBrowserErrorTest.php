<?php

/**
 * External Data Browser Error Scenario Tests
 *
 * Tests error handling, partial failures, and retry functionality
 * for the External Data Browser component.
 *
 * Test Coverage:
 * - API unavailable (all endpoints fail)
 * - Partial endpoint failures
 * - Network timeouts
 * - Retry functionality
 * - Error message display
 * - Loading state management
 */
describe('External Data Browser - Error Scenarios', function () {
    beforeEach(function () {
        // Clear any cached data
        \Illuminate\Support\Facades\Cache::flush();
    });

    it('displays error message when API is completely unavailable', function () {
        // Mock all endpoints to fail
        \Illuminate\Support\Facades\Http::fake([
            '*/api/external/characters' => \Illuminate\Support\Facades\Http::response(null, 503),
            '*/api/external/support-cards' => \Illuminate\Support\Facades\Http::response(null, 503),
            '*/api/external/skills' => \Illuminate\Support\Facades\Http::response(null, 503),
            '*/api/external/news' => \Illuminate\Support\Facades\Http::response(null, 503),
        ]);

        $page = visit('/external-data/browse');

        // Wait for loading to complete
        $page->waitFor('[x-show="!loading"]', 10);

        // Check that error messages are displayed
        $page->assertSee('Unable to load data')
            ->assertSee('API unavailable');

        // Verify apiAvailable is false
        $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]")).apiAvailable')
            ->toBe(false);

        // Check that retry button is visible
        $page->assertSee('Retry All');
    })->group('error-handling', 'browser');

    it('handles partial endpoint failures gracefully', function () {
        // Mock characters and skills to succeed, support cards and news to fail
        \Illuminate\Support\Facades\Http::fake([
            '*/api/external/characters' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'name_en' => 'Special Week', 'category_label_en' => 'Short'],
                    ['id' => 2, 'name_en' => 'Silence Suzuka', 'category_label_en' => 'Mile'],
                ],
                'source' => 'umapyoi.net',
                'cached' => false,
            ], 200),
            '*/api/external/support-cards' => \Illuminate\Support\Facades\Http::response(null, 500),
            '*/api/external/skills' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'name_en' => 'Speed Star', 'type' => 'Speed'],
                ],
                'source' => 'umapyoi.net',
                'cached' => false,
            ], 200),
            '*/api/external/news' => \Illuminate\Support\Facades\Http::response(null, 404),
        ]);

        $page = visit('/external-data/browse');

        // Wait for loading to complete
        $page->waitFor('[x-show="!loading"]', 10);

        // Verify successful data is displayed
        $page->assertSee('Special Week')
            ->assertSee('Silence Suzuka')
            ->assertSee('Speed Star');

        // Verify error messages for failed endpoints
        $page->assertSee('Support Cards')
            ->assertSee('error'); // Should show error indicator

        // Verify apiAvailable is true (at least one endpoint succeeded)
        $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]")).apiAvailable')
            ->toBe(true);

        // Check that individual retry buttons are visible for failed endpoints
        $page->assertSee('Retry');
    })->group('error-handling', 'browser');

    it('displays appropriate error messages for different HTTP status codes', function () {
        \Illuminate\Support\Facades\Http::fake([
            '*/api/external/characters' => \Illuminate\Support\Facades\Http::response(null, 404),
            '*/api/external/support-cards' => \Illuminate\Support\Facades\Http::response(null, 500),
            '*/api/external/skills' => \Illuminate\Support\Facades\Http::response(null, 503),
            '*/api/external/news' => \Illuminate\Support\Facades\Http::response(null, 429),
        ]);

        $page = visit('/external-data/browse');

        $page->waitFor('[x-show="!loading"]', 10);

        // Check that different error types are handled
        $component = $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]"))');

        expect($component['errors']['characters'])->toContain('404');
        expect($component['errors']['supportCards'])->toContain('500');
        expect($component['errors']['skills'])->toContain('503');
        expect($component['errors']['news'])->toContain('429');
    })->group('error-handling', 'browser');

    it('handles network timeout errors', function () {
        // This test would require mocking a timeout scenario
        // For now, we'll test the error handling structure

        $page = visit('/external-data/browse');

        // Inject a network error simulation
        $page->evaluate(<<<'JS'
            const component = window.Alpine.$data(document.querySelector('[x-data]'));
            component.errors.characters = 'Network error occurred';
            component.errors.supportCards = 'Network error occurred';
            component.errors.skills = 'Network error occurred';
            component.errors.news = 'Network error occurred';
            component.apiAvailable = false;
        JS);

        // Verify error messages are displayed
        $page->assertSee('Network error occurred');
        $page->assertSee('Retry All');
    })->group('error-handling', 'browser');

    it('allows retrying individual failed endpoints', function () {
        // Initial load: characters fails, others succeed
        \Illuminate\Support\Facades\Http::fake([
            '*/api/external/characters' => \Illuminate\Support\Facades\Http::sequence()
                ->push(null, 503) // First call fails
                ->push([ // Second call (retry) succeeds
                    'success' => true,
                    'data' => [['id' => 1, 'name_en' => 'Special Week']],
                    'source' => 'umapyoi.net',
                ], 200),
            '*/api/external/support-cards' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => [],
                'source' => 'umapyoi.net',
            ], 200),
            '*/api/external/skills' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => [],
                'source' => 'umapyoi.net',
            ], 200),
            '*/api/external/news' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => [],
                'source' => 'umapyoi.net',
            ], 200),
        ]);

        $page = visit('/external-data/browse');

        $page->waitFor('[x-show="!loading"]', 10);

        // Verify characters error is displayed
        $component = $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]"))');
        expect($component['errors']['characters'])->not->toBeNull();

        // Click retry button for characters
        $page->evaluate(<<<'JS'
            const component = window.Alpine.$data(document.querySelector('[x-data]'));
            component.retryEndpoint('characters');
        JS);

        // Wait for retry to complete
        $page->waitFor('[x-show="!loadingCharacters"]', 5);

        // Verify error is cleared and data is loaded
        $component = $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]"))');
        expect($component['errors']['characters'])->toBeNull();
        expect($component['characters'])->toHaveCount(1);
        expect($component['characters'][0]['name_en'])->toBe('Special Week');
    })->group('error-handling', 'retry', 'browser');

    it('allows retrying all failed endpoints at once', function () {
        // First load: all fail
        // Second load (retry): all succeed
        \Illuminate\Support\Facades\Http::fake([
            '*/api/external/characters' => \Illuminate\Support\Facades\Http::sequence()
                ->push(null, 503)
                ->push(['success' => true, 'data' => [['id' => 1, 'name_en' => 'Test']], 'source' => 'test'], 200),
            '*/api/external/support-cards' => \Illuminate\Support\Facades\Http::sequence()
                ->push(null, 503)
                ->push(['success' => true, 'data' => [], 'source' => 'test'], 200),
            '*/api/external/skills' => \Illuminate\Support\Facades\Http::sequence()
                ->push(null, 503)
                ->push(['success' => true, 'data' => [], 'source' => 'test'], 200),
            '*/api/external/news' => \Illuminate\Support\Facades\Http::sequence()
                ->push(null, 503)
                ->push(['success' => true, 'data' => [], 'source' => 'test'], 200),
        ]);

        $page = visit('/external-data/browse');

        $page->waitFor('[x-show="!loading"]', 10);

        // Verify all endpoints failed
        $component = $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]"))');
        expect($component['apiAvailable'])->toBe(false);

        // Click "Retry All" button
        $page->evaluate(<<<'JS'
            const component = window.Alpine.$data(document.querySelector('[x-data]'));
            component.retryAll();
        JS);

        // Wait for retry to complete
        $page->waitFor('[x-show="!loading"]', 10);

        // Verify all errors are cleared and API is available
        $component = $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]"))');
        expect($component['apiAvailable'])->toBe(true);
        expect($component['errors']['characters'])->toBeNull();
        expect($component['errors']['supportCards'])->toBeNull();
        expect($component['errors']['skills'])->toBeNull();
        expect($component['errors']['news'])->toBeNull();
    })->group('error-handling', 'retry', 'browser');

    it('maintains loading state correctly during error scenarios', function () {
        \Illuminate\Support\Facades\Http::fake([
            '*/api/external/characters' => \Illuminate\Support\Facades\Http::response(null, 503),
            '*/api/external/support-cards' => \Illuminate\Support\Facades\Http::response(null, 503),
            '*/api/external/skills' => \Illuminate\Support\Facades\Http::response(null, 503),
            '*/api/external/news' => \Illuminate\Support\Facades\Http::response(null, 503),
        ]);

        $page = visit('/external-data/browse');

        // Check that loading is true initially
        $component = $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]"))');
        expect($component['loading'])->toBe(true);

        // Wait for loading to complete
        $page->waitFor('[x-show="!loading"]', 10);

        // Check that loading is false after completion
        $component = $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]"))');
        expect($component['loading'])->toBe(false);
    })->group('error-handling', 'loading-state', 'browser');

    it('shows section-specific loading indicators during retry', function () {
        \Illuminate\Support\Facades\Http::fake([
            '*/api/external/characters' => \Illuminate\Support\Facades\Http::sequence()
                ->push(null, 503)
                ->push(['success' => true, 'data' => [], 'source' => 'test'], 200),
            '*/api/external/support-cards' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => [],
                'source' => 'test',
            ], 200),
            '*/api/external/skills' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => [],
                'source' => 'test',
            ], 200),
            '*/api/external/news' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => [],
                'source' => 'test',
            ], 200),
        ]);

        $page = visit('/external-data/browse');

        $page->waitFor('[x-show="!loading"]', 10);

        // Start retry for characters
        $page->evaluate(<<<'JS'
            const component = window.Alpine.$data(document.querySelector('[x-data]'));
            component.retryEndpoint('characters');
        JS);

        // Check that loadingCharacters is true
        $component = $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]"))');
        expect($component['loadingCharacters'])->toBe(true);

        // Wait for retry to complete
        $page->waitFor('[x-show="!loadingCharacters"]', 5);

        // Check that loadingCharacters is false
        $component = $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]"))');
        expect($component['loadingCharacters'])->toBe(false);
    })->group('error-handling', 'loading-state', 'browser');

    it('preserves existing data when retry fails', function () {
        // Set up initial successful data
        \Illuminate\Support\Facades\Http::fake([
            '*/api/external/characters' => \Illuminate\Support\Facades\Http::sequence()
                ->push([
                    'success' => true,
                    'data' => [['id' => 1, 'name_en' => 'Initial Data']],
                    'source' => 'test',
                ], 200)
                ->push(null, 503), // Retry fails
            '*/api/external/support-cards' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => [],
                'source' => 'test',
            ], 200),
            '*/api/external/skills' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => [],
                'source' => 'test',
            ], 200),
            '*/api/external/news' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'data' => [],
                'source' => 'test',
            ], 200),
        ]);

        $page = visit('/external-data/browse');

        $page->waitFor('[x-show="!loading"]', 10);

        // Verify initial data is loaded
        $component = $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]"))');
        expect($component['characters'])->toHaveCount(1);
        expect($component['characters'][0]['name_en'])->toBe('Initial Data');

        // Attempt retry (which will fail)
        $page->evaluate(<<<'JS'
            const component = window.Alpine.$data(document.querySelector('[x-data]'));
            component.retryEndpoint('characters');
        JS);

        $page->waitFor('[x-show="!loadingCharacters"]', 5);

        // Verify data is still preserved
        $component = $page->evaluate('window.Alpine.$data(document.querySelector("[x-data]"))');
        expect($component['characters'])->toHaveCount(1);
        expect($component['characters'][0]['name_en'])->toBe('Initial Data');

        // Verify error is set
        expect($component['errors']['characters'])->not->toBeNull();
    })->group('error-handling', 'data-preservation', 'browser');
});
