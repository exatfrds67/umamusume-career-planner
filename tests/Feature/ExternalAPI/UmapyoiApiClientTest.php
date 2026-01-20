<?php

declare(strict_types=1);

use App\Services\ExternalAPI\UmapyoiApiClient;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    $mcpClient->shouldReceive('isServerEnabled')->withArgs(['fetch'])->andReturn(false);
    $this->mcpClient = $mcpClient;
    $this->client = new UmapyoiApiClient($mcpClient);
});

afterEach(function () {
    Mockery::close();
});

describe('UmapyoiApiClient', function () {
    it('fetches characters successfully', function () {
        Http::fake([
            'api.umapyoi.net/v1/characters' => Http::response([
                'characters' => [
                    ['id' => 1, 'name' => 'Silence Suzuka'],
                    ['id' => 2, 'name' => 'Special Week'],
                ],
            ], 200),
        ]);

        $result = $this->client->getCharacters();

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveCount(2)
            ->and($result['source'])->toBe('api');
    });

    it('caches character data', function () {
        Http::fake([
            'api.umapyoi.net/v1/characters' => Http::response([
                'characters' => [
                    ['id' => 1, 'name' => 'Silence Suzuka'],
                ],
            ], 200),
        ]);

        // First call - should hit API
        $result1 = $this->client->getCharacters();
        expect($result1['source'])->toBe('api');

        // Second call - should hit cache
        $result2 = $this->client->getCharacters();
        expect($result2['source'])->toBe('cache')
            ->and($result2['data'])->toEqual($result1['data']);
    });

    it('handles API errors gracefully', function () {
        Http::fake([
            'api.umapyoi.net/v1/characters' => Http::response([], 500),
        ]);

        $result = $this->client->getCharacters();

        expect($result['success'])->toBeFalse()
            ->and($result['source'])->toBe('error')
            ->and($result)->toHaveKey('error');
    });

    it('fetches a specific character by ID', function () {
        Http::fake([
            'api.umapyoi.net/v1/characters/1' => Http::response([
                'character' => ['id' => 1, 'name' => 'Silence Suzuka'],
            ], 200),
        ]);

        $result = $this->client->getCharacter('1');

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('name')
            ->and($result['data']['name'])->toBe('Silence Suzuka');
    });

    it('fetches support cards successfully', function () {
        Http::fake([
            'api.umapyoi.net/v1/support-cards' => Http::response([
                'support_cards' => [
                    ['id' => 1, 'name' => 'Kitasan Black'],
                    ['id' => 2, 'name' => 'Narita Brian'],
                ],
            ], 200),
        ]);

        $result = $this->client->getSupportCards();

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveCount(2);
    });

    it('fetches a specific support card by ID', function () {
        Http::fake([
            'api.umapyoi.net/v1/support-cards/1' => Http::response([
                'support_card' => ['id' => 1, 'name' => 'Kitasan Black'],
            ], 200),
        ]);

        $result = $this->client->getSupportCard('1');

        expect($result['success'])->toBeTrue()
            ->and($result['data']['name'])->toBe('Kitasan Black');
    });

    it('fetches news with limit', function () {
        Http::fake([
            'api.umapyoi.net/v1/news*' => Http::response([
                'news' => [
                    ['id' => 1, 'title' => 'New Event'],
                    ['id' => 2, 'title' => 'Maintenance'],
                ],
            ], 200),
        ]);

        $result = $this->client->getNews(10);

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveCount(2);
    });

    it('checks API availability', function () {
        Http::fake([
            'api.umapyoi.net/health' => Http::response([], 200),
        ]);

        $available = $this->client->isAvailable();

        expect($available)->toBeTrue();
    });

    it('returns false when API is unavailable', function () {
        Http::fake([
            'api.umapyoi.net/health' => Http::response([], 500),
        ]);

        $available = $this->client->isAvailable();

        expect($available)->toBeFalse();
    });

    it('clears cache successfully', function () {
        Http::fake([
            'api.umapyoi.net/v1/characters' => Http::response([
                'characters' => [['id' => 1, 'name' => 'Test']],
            ], 200),
        ]);

        // Populate cache
        $this->client->getCharacters();
        expect(Cache::has('umapyoi:characters'))->toBeTrue();

        // Clear cache
        $this->client->clearCache();
        expect(Cache::has('umapyoi:characters'))->toBeFalse();
    });

    it('provides cache status', function () {
        $status = $this->client->getCacheStatus();

        expect($status)->toHaveKeys(['characters', 'support_cards', 'news']);
    });

    it('forces refresh when requested', function () {
        Http::fake([
            'api.umapyoi.net/v1/characters' => Http::response([
                'characters' => [['id' => 1, 'name' => 'Test']],
            ], 200),
        ]);

        // First call
        $this->client->getCharacters();

        // Force refresh should bypass cache
        $result = $this->client->getCharacters(true);
        expect($result['source'])->toBe('api');
    });
});
