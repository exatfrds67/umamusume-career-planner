<?php

declare(strict_types=1);

use App\Services\ExternalAPI\UmamusumeDBApiClient;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    $mcpClient->shouldReceive('isServerEnabled')->withArgs(['fetch'])->andReturn(false);
    $this->mcpClient = $mcpClient;
    $this->client = new UmamusumeDBApiClient($mcpClient);
});

afterEach(function () {
    Mockery::close();
});

describe('UmamusumeDBApiClient', function () {
    it('fetches training calculation successfully', function () {
        Http::fake([
            'api.umamusumedb.com/v1/training/calculate' => Http::response([
                'calculation' => [
                    'speed_gain' => 10,
                    'stamina_gain' => 8,
                ],
            ], 200),
        ]);

        $result = $this->client->getTrainingCalculation([
            'training_type' => 'speed',
            'support_cards' => [1, 2, 3],
        ]);

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('speed_gain');
    });

    it('caches training calculations', function () {
        Http::fake([
            'api.umamusumedb.com/v1/training/calculate' => Http::response([
                'calculation' => ['speed_gain' => 10],
            ], 200),
        ]);

        $params = ['training_type' => 'speed'];

        // First call - should hit API
        $result1 = $this->client->getTrainingCalculation($params);
        expect($result1['source'])->toBe('api');

        // Second call - should hit cache
        $result2 = $this->client->getTrainingCalculation($params);
        expect($result2['source'])->toBe('cache');
    });

    it('fetches meta tier rankings successfully', function () {
        Http::fake([
            'api.umamusumedb.com/v1/meta/tier-rankings' => Http::response([
                'rankings' => [
                    ['card_id' => 1, 'tier' => 'SS'],
                    ['card_id' => 2, 'tier' => 'S'],
                ],
            ], 200),
        ]);

        $result = $this->client->getMetaTierRankings();

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveCount(2);
    });

    it('fetches community builds for a character', function () {
        Http::fake([
            'api.umamusumedb.com/v1/characters/1/builds' => Http::response([
                'builds' => [
                    ['id' => 1, 'name' => 'Speed Build'],
                    ['id' => 2, 'name' => 'Stamina Build'],
                ],
            ], 200),
        ]);

        $result = $this->client->getCommunityBuilds('1');

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveCount(2);
    });

    it('fetches skill effectiveness data', function () {
        Http::fake([
            'api.umamusumedb.com/v1/skills/effectiveness' => Http::response([
                'skills' => [
                    ['skill_id' => 1, 'effectiveness' => 0.85],
                    ['skill_id' => 2, 'effectiveness' => 0.92],
                ],
            ], 200),
        ]);

        $result = $this->client->getSkillEffectiveness();

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveCount(2);
    });

    it('fetches race strategy recommendations', function () {
        Http::fake([
            'api.umamusumedb.com/v1/race/strategy' => Http::response([
                'strategy' => [
                    'running_style' => 'late_surger',
                    'confidence' => 0.88,
                ],
            ], 200),
        ]);

        $result = $this->client->getRaceStrategy([
            'distance' => 'medium',
            'surface' => 'turf',
        ]);

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('running_style');
    });

    it('retries on failure with exponential backoff', function () {
        $attempts = 0;

        Http::fake(function () use (&$attempts) {
            $attempts++;
            if ($attempts < 3) {
                return Http::response([], 500);
            }

            return Http::response(['rankings' => []], 200);
        });

        $result = $this->client->getMetaTierRankings();

        expect($result['success'])->toBeTrue()
            ->and($attempts)->toBe(3);
    });

    it('handles API errors gracefully', function () {
        Http::fake([
            'api.umamusumedb.com/v1/meta/tier-rankings' => Http::response([], 500),
        ]);

        $result = $this->client->getMetaTierRankings();

        expect($result['success'])->toBeFalse()
            ->and($result['source'])->toBe('error')
            ->and($result)->toHaveKey('error');
    });

    it('checks API availability', function () {
        Http::fake([
            'api.umamusumedb.com/health' => Http::response([], 200),
        ]);

        $available = $this->client->isAvailable();

        expect($available)->toBeTrue();
    });

    it('clears cache successfully', function () {
        $this->client->clearCache();

        // Should not throw exception
        expect(true)->toBeTrue();
    });

    it('provides cache status', function () {
        $status = $this->client->getCacheStatus();

        expect($status)->toHaveKeys(['meta_tier_rankings', 'skill_effectiveness']);
    });
});
