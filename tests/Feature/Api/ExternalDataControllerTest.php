<?php

use App\Models\User;
use App\Services\CacheManagementService;
use App\Services\ExternalAPI\ResponseTransformer;
use App\Services\ExternalAPI\ResponseValidator;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    Cache::flush();

    // Mock MCP service
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    $mcpClient->shouldReceive('isServerEnabled')->andReturn(false);
    $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);
    $this->app->instance(MCPClientService::class, $mcpClient);

    // Mock cache manager
    /** @var CacheManagementService&Mockery\MockInterface $cacheManager */
    $cacheManager = Mockery::mock(CacheManagementService::class);
    $cacheManager->shouldReceive('remember')->andReturnUsing(function ($key, $callback, $ttl) {
        return $callback();
    });
    $cacheManager->shouldReceive('recordApiResponseTime')->andReturn(null);
    $this->app->instance(CacheManagementService::class, $cacheManager);

    // Mock validator
    /** @var ResponseValidator&Mockery\MockInterface $validator */
    $validator = Mockery::mock(ResponseValidator::class);
    $validator->shouldReceive('validate')->andReturnUsing(function ($type, $data) {
        // Handle wrapper keys like the real validator
        $wrapperKeys = [
            'umapyoi_characters' => 'characters',
            'umapyoi_character' => 'character',
            'umapyoi_support_cards' => 'support_cards',
            'umapyoi_support_card' => 'support_card',
            'umapyoi_skills' => 'skills',
            'umapyoi_skill' => 'skill',
            'umapyoi_news' => 'news',
        ];

        $wrapperKey = $wrapperKeys[$type] ?? null;

        // If the data has a wrapper key, extract it
        if ($wrapperKey && isset($data[$wrapperKey])) {
            $extractedData = $data[$wrapperKey];
        } else {
            // Otherwise check if data is the raw array (from HTTP response body)
            $extractedData = is_array($data) ? $data : [];
        }

        return [
            'valid' => true,
            'errors' => [],
            'data' => $extractedData,
        ];
    });
    $this->app->instance(ResponseValidator::class, $validator);

    // Mock transformer
    /** @var ResponseTransformer&Mockery\MockInterface $transformer */
    $transformer = Mockery::mock(ResponseTransformer::class);
    $transformer->shouldReceive('transform')->andReturnUsing(function ($type, $data) {
        return $data;
    });
    $this->app->instance(ResponseTransformer::class, $transformer);
});

it('fetches characters from umapyoi API', function () {
    // Mock the external API response with proper structure matching schema
    Http::fake([
        'api.umapyoi.net/*' => Http::response([
            ['id' => 1, 'name_en' => 'Special Week', 'name_jp' => 'スペシャルウィーク', 'category_label' => 'ウマ娘'],
        ], 200),
    ]);

    $response = actingAs($this->user)->getJson('/api/external/characters');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'success',
        'data',
        'source',
    ]);
});

it('fetches support cards from umapyoi API', function () {
    // Mock the external API response with proper structure matching schema
    Http::fake([
        'api.umapyoi.net/*' => Http::response([
            ['id' => 1, 'chara_id' => 100, 'title_en' => 'Special Week [SSR]', 'gametora' => 'speed'],
        ], 200),
    ]);

    $response = actingAs($this->user)->getJson('/api/external/support-cards');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'success',
        'data',
        'source',
    ]);
});

it('fetches skills from local database', function () {
    // Ensure we have at least some skills in the database
    if (\App\Models\Skill::count() === 0) {
        \App\Models\Skill::factory()->count(5)->create();
    }

    $response = actingAs($this->user)->getJson('/api/external/skills');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'success',
        'data',
        'source',
        'note',
    ]);
    $response->assertJson([
        'success' => true,
        'source' => 'local database',
    ]);
    expect($response->json('data'))->toBeArray();
});

it('fetches news from umapyoi API', function () {
    // Mock the external API response with proper structure matching schema
    Http::fake([
        'api.umapyoi.net/*' => Http::response([
            [
                'id' => 1,
                'title' => 'Test News',
                'title_english' => 'Test News',
                'message' => 'Test news content',
                'post_at' => 1704067200, // Unix timestamp
            ],
        ], 200),
    ]);

    $response = actingAs($this->user)->getJson('/api/external/news?limit=10');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'success',
        'data',
        'source',
    ]);
});

it('checks API status', function () {
    $response = actingAs($this->user)->getJson('/api/external/status');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'success',
        'data' => [
            'umapyoi' => [
                'available',
                'cache_status',
            ],
        ],
    ]);
});

it('clears API cache', function () {
    $response = actingAs($this->user)->postJson('/api/external/clear-cache');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'success',
        'message',
    ]);
});

it('requires authentication for external data endpoints', function () {
    $response = $this->getJson('/api/external/characters');
    $response->assertUnauthorized();

    $response = $this->getJson('/api/external/support-cards');
    $response->assertUnauthorized();

    $response = $this->getJson('/api/external/skills');
    $response->assertUnauthorized();

    $response = $this->getJson('/api/external/news');
    $response->assertUnauthorized();

    $response = $this->getJson('/api/external/status');
    $response->assertUnauthorized();

    $response = $this->postJson('/api/external/clear-cache');
    $response->assertUnauthorized();
});

it('handles API errors gracefully', function () {
    // Mock failed API response
    Http::fake([
        'api.umapyoi.net/*' => Http::response(null, 500),
    ]);

    $response = actingAs($this->user)->getJson('/api/external/characters');

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
    ]);
    $response->assertJsonStructure(['message']);
});
