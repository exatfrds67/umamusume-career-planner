<?php

use function Pest\Laravel\get;

/**
 * External Data Browser - Retry Functionality Feature Tests
 *
 * Tests for task 4.1.3: Test retry functionality
 * Validates Requirements 2.3 from design document
 *
 * These tests verify the page loads correctly and has the retry functionality in place.
 */
describe('External Data Browser - Retry Functionality', function () {
    beforeEach(function () {
        // Create and authenticate a user for all tests
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user);
    });

    it('loads the external data browse page successfully', function () {
        $response = get('/external-data/browse');

        $response->assertStatus(200);
        $response->assertSee('External Data Browser');
        $response->assertSee('externalDataBrowser()');
    })->group('external-data', 'retry');

    it('includes retry all button in the page', function () {
        $response = get('/external-data/browse');

        $response->assertStatus(200);
        $response->assertSee('retryAll()');
        $response->assertSee('Retry All Failed');
    })->group('external-data', 'retry');

    it('includes individual retry buttons for each endpoint', function () {
        $response = get('/external-data/browse');

        $response->assertStatus(200);
        // Check for retry buttons for each endpoint
        $response->assertSee('retryEndpoint(\'characters\')');
        $response->assertSee('retryEndpoint(\'supportCards\')');
        $response->assertSee('retryEndpoint(\'skills\')');
        $response->assertSee('retryEndpoint(\'news\')');
    })->group('external-data', 'retry');

    it('includes error banners for each endpoint', function () {
        $response = get('/external-data/browse');

        $response->assertStatus(200);
        // Check for error display sections
        $response->assertSee('errors.characters');
        $response->assertSee('errors.supportCards');
        $response->assertSee('errors.skills');
        $response->assertSee('errors.news');
    })->group('external-data', 'retry');

    it('includes loading states for each endpoint', function () {
        $response = get('/external-data/browse');

        $response->assertStatus(200);
        // Check for section-specific loading states
        $response->assertSee('loadingCharacters');
        $response->assertSee('loadingSupportCards');
        $response->assertSee('loadingSkills');
        $response->assertSee('loadingNews');
    })->group('external-data', 'retry');

    it('includes retry button disabled state logic', function () {
        $response = get('/external-data/browse');

        $response->assertStatus(200);
        // Check for disabled state binding (escaped in HTML)
        $response->assertSee(':disabled="loadingCharacters"', false);
        $response->assertSee(':disabled="loadingSupportCards"', false);
        $response->assertSee(':disabled="loadingSkills"', false);
        $response->assertSee(':disabled="loadingNews"', false);
    })->group('external-data', 'retry');

    it('includes retry button text that changes during loading', function () {
        $response = get('/external-data/browse');

        $response->assertStatus(200);
        // Check for dynamic button text (escaped in HTML)
        $response->assertSee('loadingCharacters ? \'Retrying...\' : \'Retry\'', false);
        $response->assertSee('loadingSupportCards ? \'Retrying...\' : \'Retry\'', false);
        $response->assertSee('loadingSkills ? \'Retrying...\' : \'Retry\'', false);
        $response->assertSee('loadingNews ? \'Retrying...\' : \'Retry\'', false);
    })->group('external-data', 'retry');

    it('includes loading spinner animation during retry', function () {
        $response = get('/external-data/browse');

        $response->assertStatus(200);
        // Check for spinner animation classes (escaped in HTML)
        $response->assertSee('animate-spin');
        $response->assertSee(':class="{ \'animate-spin\': loadingCharacters }"', false);
        $response->assertSee(':class="{ \'animate-spin\': loadingSupportCards }"', false);
        $response->assertSee(':class="{ \'animate-spin\': loadingSkills }"', false);
        $response->assertSee(':class="{ \'animate-spin\': loadingNews }"', false);
    })->group('external-data', 'retry');

    it('includes section loading indicators', function () {
        $response = get('/external-data/browse');

        $response->assertStatus(200);
        // Check for section-specific loading indicators (escaped in HTML)
        $response->assertSee('x-show="loadingCharacters && !loading"', false);
        $response->assertSee('x-show="loadingSupportCards && !loading"', false);
        $response->assertSee('x-show="loadingSkills && !loading"', false);
        $response->assertSee('x-show="loadingNews && !loading"', false);
    })->group('external-data', 'retry');

    it('has the javascript component file', function () {
        $jsFile = resource_path('js/pages/external-data/browse.js');

        expect(file_exists($jsFile))->toBeTrue();

        $content = file_get_contents($jsFile);

        // Verify retry methods exist
        expect($content)->toContain('async retryEndpoint(endpointName)');
        expect($content)->toContain('async retryAll()');
    })->group('external-data', 'retry');
});
