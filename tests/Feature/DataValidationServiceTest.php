<?php

declare(strict_types=1);

use App\Services\ExternalAPI\DataValidationService;
use App\Services\MCP\MCPClientService;

beforeEach(function () {
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    $this->mcpClient = $mcpClient;
    $this->service = new DataValidationService($mcpClient);
});

afterEach(function () {
    Mockery::close();
});

test('validates data successfully with no errors', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = [
        ['id' => 1, 'name' => 'Silence Suzuka'],
        ['id' => 2, 'name' => 'Special Week'],
    ];

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert
    expect($result)->toBeArray()
        ->and($result['score'])->toBeGreaterThan(0)
        ->and($result)->toHaveKeys(['valid', 'errors', 'warnings', 'score', 'details']);
});

test('detects missing required fields', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = [
        ['id' => 1], // Missing 'name'
        ['name' => 'Special Week'], // Missing 'id'
    ];

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert
    expect($result)->toBeArray()
        ->and($result['valid'])->toBeFalse()
        ->and($result['errors'])->not->toBeEmpty();
});

test('detects incorrect field types', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = [
        ['id' => 'not_an_integer', 'name' => 'Test'], // Wrong type for id
    ];

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert
    expect($result)->toBeArray()
        ->and($result['valid'])->toBeFalse()
        ->and($result['errors'])->not->toBeEmpty();
});

test('detects duplicate entries', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = [
        ['id' => 1, 'name' => 'Test1'],
        ['id' => 1, 'name' => 'Test2'], // Duplicate ID
    ];

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert
    expect($result)->toBeArray()
        ->and($result['valid'])->toBeFalse()
        ->and($result['errors'])->not->toBeEmpty();
});

test('validates enum fields correctly', function () {
    // Arrange
    $dataType = 'umapyoi_support_cards';
    $data = [
        ['id' => 1, 'name' => 'Test', 'rarity' => 'SSR'],
        ['id' => 2, 'name' => 'Test2', 'rarity' => 'INVALID'], // Invalid rarity
    ];

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert
    expect($result)->toBeArray()
        ->and($result['valid'])->toBeFalse()
        ->and($result['errors'])->not->toBeEmpty();
});

test('calculates completeness score correctly', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = [
        ['id' => 1, 'name' => 'Complete'],
        ['id' => 2, 'name' => null], // Incomplete
    ];

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert
    expect($result)->toBeArray()
        ->and($result['details']['quality_checks']['metrics']['completeness'])->toBeLessThan(100.0);
});

test('calculates consistency score correctly', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = [
        ['id' => 1, 'name' => 'Test1'],
        ['id' => '2', 'name' => 'Test2'], // Inconsistent type for id
    ];

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert
    expect($result)->toBeArray()
        ->and($result['details']['quality_checks']['metrics']['consistency'])->toBeLessThan(100.0);
});

test('handles empty data gracefully', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = [];

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert - empty data is valid but may have warnings
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['valid', 'errors', 'warnings', 'score', 'details']);
});

test('handles non-array data gracefully', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = 'not_an_array';

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert
    expect($result)->toBeArray()
        ->and($result['valid'])->toBeFalse();
});

test('returns validation history', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = [['id' => 1, 'name' => 'Test']];

    // Act
    $this->service->validateData($dataType, $data);
    $history = $this->service->getValidationHistory();

    // Assert
    expect($history)->toBeArray()
        ->and($history)->not->toBeEmpty();
});

test('returns validation statistics', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = [['id' => 1, 'name' => 'Test']];

    // Act
    $this->service->validateData($dataType, $data);
    $stats = $this->service->getValidationStatistics();

    // Assert
    expect($stats)->toBeArray()
        ->and($stats)->toHaveKeys(['total_validations', 'successful', 'failed', 'avg_score']);
});

test('validates data with warnings but no errors', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = [
        ['id' => 1, 'name' => 'Test'],
    ];

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert
    expect($result)->toBeArray()
        ->and($result['score'])->toBeGreaterThan(0);
});

test('calculates validation score based on errors and warnings', function () {
    // Arrange
    $dataType = 'umapyoi_characters';
    $data = [
        ['id' => 1], // Missing name - error
    ];

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert
    expect($result)->toBeArray()
        ->and($result['score'])->toBeLessThan(100.0);
});

test('handles unknown data type gracefully', function () {
    // Arrange
    $dataType = 'unknown_type';
    $data = [['id' => 1, 'name' => 'Test']];

    // Act
    $result = $this->service->validateData($dataType, $data);

    // Assert
    expect($result)->toBeArray()
        ->and($result['valid'])->toBeTrue()
        ->and($result['warnings'])->toContain('No validation rules defined');
});
