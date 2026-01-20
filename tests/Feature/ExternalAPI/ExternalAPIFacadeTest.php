<?php

declare(strict_types=1);

use App\Services\ExternalAPI\Context7Service;
use App\Services\ExternalAPI\ExternalAPIFacade;
use App\Services\ExternalAPI\UmamusumeDBApiClient;
use App\Services\ExternalAPI\UmapyoiApiClient;

beforeEach(function () {
    /** @var UmapyoiApiClient&Mockery\MockInterface $umapyoiClient */
    $umapyoiClient = Mockery::mock(UmapyoiApiClient::class);
    /** @var UmamusumeDBApiClient&Mockery\MockInterface $umamusumeDBClient */
    $umamusumeDBClient = Mockery::mock(UmamusumeDBApiClient::class);
    /** @var Context7Service&Mockery\MockInterface $contextService */
    $contextService = Mockery::mock(Context7Service::class);

    $this->umapyoiClient = $umapyoiClient;
    $this->umamusumeDBClient = $umamusumeDBClient;
    $this->contextService = $contextService;

    $this->facade = new ExternalAPIFacade(
        $umapyoiClient,
        $umamusumeDBClient,
        $contextService
    );
});

afterEach(function () {
    Mockery::close();
});

describe('ExternalAPIFacade', function () {
    it('fetches characters through facade', function () {
        $this->contextService->shouldReceive('storeApiCallContext')->once();
        $this->umapyoiClient->shouldReceive('getCharacters')
            ->withArgs([false])
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [['id' => 1, 'name' => 'Test']],
                'source' => 'api',
            ]);

        $result = $this->facade->getCharacters();

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveCount(1);
    });

    it('fetches a specific character through facade', function () {
        $this->contextService->shouldReceive('storeApiCallContext')->once();
        $this->umapyoiClient->shouldReceive('getCharacter')
            ->withArgs(['1', false])
            ->once()
            ->andReturn([
                'success' => true,
                'data' => ['id' => 1, 'name' => 'Test'],
                'source' => 'api',
            ]);

        $result = $this->facade->getCharacter('1');

        expect($result['success'])->toBeTrue()
            ->and($result['data']['name'])->toBe('Test');
    });

    it('fetches support cards through facade', function () {
        $this->contextService->shouldReceive('storeApiCallContext')->once();
        $this->umapyoiClient->shouldReceive('getSupportCards')
            ->withArgs([false])
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [['id' => 1, 'name' => 'Card']],
                'source' => 'api',
            ]);

        $result = $this->facade->getSupportCards();

        expect($result['success'])->toBeTrue();
    });

    it('fetches training calculation through facade', function () {
        $params = ['training_type' => 'speed'];

        $this->contextService->shouldReceive('storeApiCallContext')->once();
        $this->umamusumeDBClient->shouldReceive('getTrainingCalculation')
            ->withArgs([$params, false])
            ->once()
            ->andReturn([
                'success' => true,
                'data' => ['speed_gain' => 10],
                'source' => 'api',
            ]);

        $result = $this->facade->getTrainingCalculation($params);

        expect($result['success'])->toBeTrue()
            ->and($result['data'])->toHaveKey('speed_gain');
    });

    it('fetches meta tier rankings through facade', function () {
        $this->contextService->shouldReceive('storeApiCallContext')->once();
        $this->umamusumeDBClient->shouldReceive('getMetaTierRankings')
            ->withArgs([false])
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [['tier' => 'SS']],
                'source' => 'api',
            ]);

        $result = $this->facade->getMetaTierRankings();

        expect($result['success'])->toBeTrue();
    });

    it('provides health status for all APIs', function () {
        $this->umapyoiClient->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->umapyoiClient->shouldReceive('getCacheStatus')->once()->andReturn([
            'characters' => true,
            'support_cards' => false,
        ]);

        $this->umamusumeDBClient->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->umamusumeDBClient->shouldReceive('getCacheStatus')->once()->andReturn([
            'meta_tier_rankings' => true,
        ]);

        $this->contextService->shouldReceive('isAvailable')->twice()->andReturn(true);

        $health = $this->facade->getHealthStatus();

        expect($health)->toHaveKeys(['umapyoi', 'umamusumedb', 'context7'])
            ->and($health['umapyoi']['available'])->toBeTrue()
            ->and($health['umamusumedb']['available'])->toBeTrue();
    });

    it('clears all caches', function () {
        $this->umapyoiClient->shouldReceive('clearCache')->once();
        $this->umamusumeDBClient->shouldReceive('clearCache')->once();
        $this->contextService->shouldReceive('clearAllContexts')->once();

        $this->facade->clearAllCaches();

        // Should not throw exception
        expect(true)->toBeTrue();
    });

    it('provides comprehensive statistics', function () {
        $this->umapyoiClient->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->umapyoiClient->shouldReceive('getCacheStatus')->twice()->andReturn([]);

        $this->umamusumeDBClient->shouldReceive('isAvailable')->once()->andReturn(true);
        $this->umamusumeDBClient->shouldReceive('getCacheStatus')->twice()->andReturn([]);

        $this->contextService->shouldReceive('isAvailable')->twice()->andReturn(true);
        $this->contextService->shouldReceive('getContextSummary')->once()->andReturn([
            'total_contexts' => 100,
        ]);

        $stats = $this->facade->getStatistics();

        expect($stats)->toHaveKeys(['health', 'context_summary', 'cache_status']);
    });

    it('syncs all data', function () {
        $this->umapyoiClient->shouldReceive('getCharacters')
            ->with(true)
            ->once()
            ->andReturn(['success' => true, 'data' => [], 'source' => 'api']);

        $this->umapyoiClient->shouldReceive('getSupportCards')
            ->with(true)
            ->once()
            ->andReturn(['success' => true, 'data' => [], 'source' => 'api']);

        $this->umamusumeDBClient->shouldReceive('getMetaTierRankings')
            ->with(true)
            ->once()
            ->andReturn(['success' => true, 'data' => [], 'source' => 'api']);

        $this->contextService->shouldReceive('storeApiCallContext')->times(3);

        $results = $this->facade->syncAllData();

        expect($results)->toHaveKeys(['characters', 'support_cards', 'meta_data']);
    });
});
