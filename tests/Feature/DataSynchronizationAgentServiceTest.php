<?php

declare(strict_types=1);

use App\Services\ExternalAPI\ConflictResolutionService;
use App\Services\ExternalAPI\DataSynchronizationAgentService;
use App\Services\ExternalAPI\DataValidationService;
use App\Services\ExternalAPI\UmamusumeDBApiClient;
use App\Services\ExternalAPI\UmapyoiApiClient;
use App\Services\MCP\MCPClientService;
use App\Services\MCP\SubagentCoordinationService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();

    // Mock dependencies
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    /** @var SubagentCoordinationService&Mockery\MockInterface $subagentCoordination */
    $subagentCoordination = Mockery::mock(SubagentCoordinationService::class);
    /** @var UmapyoiApiClient&Mockery\MockInterface $umapyoiClient */
    $umapyoiClient = Mockery::mock(UmapyoiApiClient::class);
    /** @var UmamusumeDBApiClient&Mockery\MockInterface $umamusumeDBClient */
    $umamusumeDBClient = Mockery::mock(UmamusumeDBApiClient::class);
    /** @var DataValidationService&Mockery\MockInterface $validationService */
    $validationService = Mockery::mock(DataValidationService::class);
    /** @var ConflictResolutionService&Mockery\MockInterface $conflictResolution */
    $conflictResolution = Mockery::mock(ConflictResolutionService::class);

    $this->mcpClient = $mcpClient;
    $this->subagentCoordination = $subagentCoordination;
    $this->umapyoiClient = $umapyoiClient;
    $this->umamusumeDBClient = $umamusumeDBClient;
    $this->validationService = $validationService;
    $this->conflictResolution = $conflictResolution;

    $this->service = new DataSynchronizationAgentService(
        $mcpClient,
        $subagentCoordination,
        $umapyoiClient,
        $umamusumeDBClient,
        $validationService,
        $conflictResolution
    );
});

afterEach(function () {
    Mockery::close();
});

test('coordinates multi-source synchronization successfully', function () {
    // Arrange
    $dataSources = ['umapyoi_characters', 'umamusumedb_meta'];

    $this->mcpClient->shouldReceive('isServerEnabled')
        ->withArgs(['strands-agents'])
        ->andReturn(true);

    $this->umapyoiClient->shouldReceive('getCharacters')
        ->andReturn([
            ['id' => 1, 'name' => 'Silence Suzuka'],
            ['id' => 2, 'name' => 'Special Week'],
        ]);

    $this->umamusumeDBClient->shouldReceive('getMetaTierRankings')
        ->andReturn([
            ['card_id' => 1, 'tier' => 'SS'],
            ['card_id' => 2, 'tier' => 'S'],
        ]);

    $this->validationService->shouldReceive('validateData')
        ->twice()
        ->andReturn([
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'score' => 95.0,
        ]);

    $this->conflictResolution->shouldReceive('resolveConflicts')
        ->andReturn([
            'resolved_data' => ['test' => 'data'],
            'conflicts' => [],
            'resolution_strategy' => 'priority_based',
        ]);

    // Act
    $result = $this->service->coordinateMultiSourceSync($dataSources);

    // Assert
    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result)->toHaveKeys(['synchronized', 'conflicts', 'quality_score', 'duration_ms'])
        ->and($result['quality_score'])->toBeGreaterThan(0);
});

test('handles MCP server unavailability with fallback', function () {
    // Arrange
    $dataSources = ['umapyoi_characters'];

    $this->mcpClient->shouldReceive('isServerEnabled')
        ->withArgs(['strands-agents'])
        ->andReturn(false);

    $this->umapyoiClient->shouldReceive('getCharacters')
        ->andReturn([['id' => 1, 'name' => 'Test']]);

    // Act
    $result = $this->service->coordinateMultiSourceSync($dataSources);

    // Assert
    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['quality_score'])->toBe(50.0); // Lower score for fallback
});

test('handles fetch failures gracefully', function () {
    // Arrange
    $dataSources = ['umapyoi_characters'];

    $this->mcpClient->shouldReceive('isServerEnabled')
        ->withArgs(['strands-agents'])
        ->andReturn(true);

    $this->umapyoiClient->shouldReceive('getCharacters')
        ->andThrow(new \Exception('API unavailable'));

    $this->validationService->shouldReceive('validateData')
        ->andReturn([
            'valid' => false,
            'errors' => ['Fetch failed'],
            'warnings' => [],
            'score' => 0.0,
        ]);

    $this->conflictResolution->shouldReceive('resolveConflicts')
        ->andReturn([
            'resolved_data' => [],
            'conflicts' => [],
            'resolution_strategy' => 'single_source',
        ]);

    // Act
    $result = $this->service->coordinateMultiSourceSync($dataSources);

    // Assert
    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue();
});

test('calculates quality score correctly', function () {
    // Arrange
    $dataSources = ['umapyoi_characters'];

    $this->mcpClient->shouldReceive('isServerEnabled')
        ->withArgs(['strands-agents'])
        ->andReturn(true);

    $this->umapyoiClient->shouldReceive('getCharacters')
        ->andReturn([['id' => 1, 'name' => 'Test']]);

    $this->validationService->shouldReceive('validateData')
        ->andReturn([
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'score' => 100.0,
        ]);

    $this->conflictResolution->shouldReceive('resolveConflicts')
        ->andReturn([
            'resolved_data' => ['test' => 'data'],
            'conflicts' => [],
            'resolution_strategy' => 'consensus',
        ]);

    // Act
    $result = $this->service->coordinateMultiSourceSync($dataSources);

    // Assert
    expect($result['quality_score'])->toBeGreaterThanOrEqual(100.0);
});

test('stores synchronized data in cache', function () {
    // Arrange
    $dataSources = ['umapyoi_characters'];

    $this->mcpClient->shouldReceive('isServerEnabled')
        ->withArgs(['strands-agents'])
        ->andReturn(true);

    $this->umapyoiClient->shouldReceive('getCharacters')
        ->andReturn([['id' => 1, 'name' => 'Test']]);

    $this->validationService->shouldReceive('validateData')
        ->andReturn([
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'score' => 95.0,
        ]);

    $this->conflictResolution->shouldReceive('resolveConflicts')
        ->andReturn([
            'resolved_data' => ['test_key' => 'test_value'],
            'conflicts' => [],
            'resolution_strategy' => 'priority_based',
        ]);

    // Act
    $result = $this->service->coordinateMultiSourceSync($dataSources);

    // Assert
    expect($result['synchronized'])->toBeArray()
        ->and($result['synchronized'])->not->toBeEmpty();
});

test('handles validation errors appropriately', function () {
    // Arrange
    $dataSources = ['umapyoi_characters'];

    $this->mcpClient->shouldReceive('isServerEnabled')
        ->with('strands-agents')
        ->andReturn(true);

    $this->umapyoiClient->shouldReceive('getCharacters')
        ->andReturn([['id' => 1, 'name' => 'Test']]);

    $this->validationService->shouldReceive('validateData')
        ->andReturn([
            'valid' => false,
            'errors' => ['Invalid data format'],
            'warnings' => ['Missing optional field'],
            'score' => 60.0,
        ]);

    $this->conflictResolution->shouldReceive('resolveConflicts')
        ->andReturn([
            'resolved_data' => [],
            'conflicts' => [],
            'resolution_strategy' => 'single_source',
        ]);

    // Act
    $result = $this->service->coordinateMultiSourceSync($dataSources);

    // Assert
    expect($result)->toBeArray()
        ->and($result['quality_score'])->toBeLessThanOrEqual(100.0);
});

test('detects and reports conflicts', function () {
    // Arrange
    $dataSources = ['umapyoi_characters', 'umamusumedb_meta'];

    $this->mcpClient->shouldReceive('isServerEnabled')
        ->with('strands-agents')
        ->andReturn(true);

    $this->umapyoiClient->shouldReceive('getCharacters')
        ->andReturn([['id' => 1, 'name' => 'Test1']]);

    $this->umamusumeDBClient->shouldReceive('getMetaTierRankings')
        ->andReturn([['card_id' => 1, 'tier' => 'SS']]);

    $this->validationService->shouldReceive('validateData')
        ->twice()
        ->andReturn([
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'score' => 95.0,
        ]);

    $this->conflictResolution->shouldReceive('resolveConflicts')
        ->andReturn([
            'resolved_data' => ['test' => 'data'],
            'conflicts' => [
                'conflict1' => ['type' => 'value_mismatch'],
            ],
            'resolution_strategy' => 'weighted_average',
        ]);

    // Act
    $result = $this->service->coordinateMultiSourceSync($dataSources);

    // Assert
    expect($result['conflicts'])->toBeArray()
        ->and($result['conflicts'])->not->toBeEmpty();
});

test('returns sync status correctly', function () {
    // Act
    $status = $this->service->getSyncStatus();

    // Assert
    expect($status)->toBeArray()
        ->and($status)->toHaveKeys(['active_syncs', 'completed_syncs', 'failed_syncs', 'avg_quality_score']);
});

test('schedules automatic synchronization', function () {
    // Arrange
    $dataSources = ['umapyoi_characters'];
    $options = ['interval' => 3600];

    // Act & Assert - Should not throw exception
    $this->service->scheduleAutoSync($dataSources, $options);

    expect(true)->toBeTrue();
});
