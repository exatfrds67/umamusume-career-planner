<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test the External Data Browser functionality
 *
 * This test ensures that the external data API endpoints work correctly
 * even when Redis is not available, which was causing issues in the browser.
 */
class ExternalDataBrowserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the external data characters endpoint works
     */
    public function test_external_characters_endpoint_works(): void
    {
        // Test the API endpoint directly
        $response = $this->getJson('/api/external/characters');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name_en',
                    'name_jp',
                    'name',
                    'category_label_en',
                    'thumb_img',
                    'color_main',
                    'color_sub',
                    'metadata' => [
                        'source',
                        'transformed_at',
                    ],
                ],
            ],
            'source',
            'cached',
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertGreaterThan(0, count($data['data']));
        $this->assertEquals('umapyoi.net', $data['source']);
    }

    /**
     * Test that the external data support cards endpoint works
     */
    public function test_external_support_cards_endpoint_works(): void
    {
        $response = $this->getJson('/api/external/support-cards');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'source',
            'cached',
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals('umapyoi.net', $data['source']);
    }

    /**
     * Test that the external data skills endpoint works
     */
    public function test_external_skills_endpoint_works(): void
    {
        $response = $this->getJson('/api/external/skills');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'source',
            'cached',
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals('local database', $data['source']);
    }

    /**
     * Test that the external data status endpoint works
     */
    public function test_external_status_endpoint_works(): void
    {
        $response = $this->getJson('/api/external/status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'umapyoi' => [
                    'available',
                    'cache_status',
                ],
            ],
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertIsBool($data['data']['umapyoi']['available']);
    }

    /**
     * Test that the external data browser page loads
     */
    public function test_external_data_browser_page_loads(): void
    {
        // Create a user and authenticate
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/external-data/browse');

        $response->assertStatus(200);
        $response->assertSee('External Data Browser');
        $response->assertSee('Browse and import data from umapyoi.net');
    }
}
