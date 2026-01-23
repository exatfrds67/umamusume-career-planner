<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\DataOperationHistoryService;

/**
 * Data Management UI Tests
 *
 * Tests for Task 5.3.5: Build user-friendly import/export UI
 * Requirements: Requirement 23.5
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->historyService = app(DataOperationHistoryService::class);
});

describe('Data Management Hub View', function () {
    it('displays the data management hub page for authenticated users', function () {
        $response = $this->actingAs($this->user)
            ->get(route('data-management.index'));

        $response->assertSuccessful();
        $response->assertViewIs('data-management.index');
        $response->assertSee('Data Management Hub');
    });

    it('redirects unauthenticated users', function () {
        $response = $this->get(route('data-management.index'));

        // The application redirects unauthenticated users (either to login or home)
        $response->assertRedirect();
    });

    it('displays all tab navigation options', function () {
        $response = $this->actingAs($this->user)
            ->get(route('data-management.index'));

        $response->assertSee('Overview');
        $response->assertSee('Import');
        $response->assertSee('Export');
        $response->assertSee('Migration');
        $response->assertSee('Backup');
        $response->assertSee('History');
    });

    it('passes import types to the view', function () {
        $response = $this->actingAs($this->user)
            ->get(route('data-management.index'));

        $response->assertViewHas('importTypes');
    });

    it('passes export types to the view', function () {
        $response = $this->actingAs($this->user)
            ->get(route('data-management.index'));

        $response->assertViewHas('exportTypes');
    });

    it('passes export formats to the view', function () {
        $response = $this->actingAs($this->user)
            ->get(route('data-management.index'));

        $response->assertViewHas('exportFormats');
    });

    it('passes legacy formats to the view', function () {
        $response = $this->actingAs($this->user)
            ->get(route('data-management.index'));

        $response->assertViewHas('legacyFormats');
    });
});

describe('Data Management Dashboard API', function () {
    it('returns dashboard data for authenticated users', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/dashboard');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'statistics',
                'recent_operations',
                'ongoing_operations',
                'quick_stats',
            ],
        ]);
    });

    it('returns 401 for unauthenticated requests', function () {
        $response = $this->getJson('/api/data-management/dashboard');

        $response->assertUnauthorized();
    });

    it('includes quick stats in dashboard response', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/dashboard');

        $response->assertJsonStructure([
            'data' => [
                'quick_stats' => [
                    'total_characters',
                    'total_careers',
                    'total_backups',
                    'last_backup',
                ],
            ],
        ]);
    });

    it('includes statistics in dashboard response', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/dashboard');

        $response->assertJsonStructure([
            'data' => [
                'statistics' => [
                    'total_operations',
                    'successful_operations',
                    'failed_operations',
                    'success_rate',
                ],
            ],
        ]);
    });
});

describe('Operation History API', function () {
    it('returns operation history for authenticated users', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/history');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data',
            'pagination' => [
                'current_page',
                'per_page',
                'total',
                'total_pages',
                'has_more',
            ],
        ]);
    });

    it('supports pagination parameters', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/history?page=1&per_page=10');

        $response->assertSuccessful();
        $response->assertJsonPath('pagination.per_page', 10);
    });

    it('supports operation type filter', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/history?operation_type=import');

        $response->assertSuccessful();
    });

    it('supports status filter', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/history?status=completed');

        $response->assertSuccessful();
    });

    it('supports date range filters', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/history?date_from=2024-01-01&date_to=2024-12-31');

        $response->assertSuccessful();
    });
});

describe('Operation Status API', function () {
    it('returns ongoing operations status', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/status');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'ongoing_operations',
                'count',
            ],
        ]);
    });

    it('returns specific operation status when operation_id provided', function () {
        // Log an operation first
        $operationId = $this->historyService->logOperationStart(
            $this->user->id,
            'import',
            ['test' => true]
        );

        $response = $this->actingAs($this->user)
            ->getJson("/api/data-management/status?operation_id={$operationId}");

        $response->assertSuccessful();
        $response->assertJsonPath('data.operation_id', $operationId);
    });
});

describe('Statistics API', function () {
    it('returns statistics for authenticated users', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/statistics');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_operations',
                'successful_operations',
                'failed_operations',
                'success_rate',
                'by_type',
                'records_processed',
                'period',
            ],
        ]);
    });

    it('supports period parameter', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/statistics?period=week');

        $response->assertSuccessful();
        $response->assertJsonPath('data.period', 'week');
    });

    it('defaults to month period', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/data-management/statistics');

        $response->assertSuccessful();
        $response->assertJsonPath('data.period', 'month');
    });
});

describe('DataOperationHistoryService', function () {
    it('logs operation start correctly', function () {
        $operationId = $this->historyService->logOperationStart(
            $this->user->id,
            'import',
            ['type' => 'character']
        );

        expect($operationId)->toBeString();
        expect(strlen($operationId))->toBe(36); // UUID format
    });

    it('logs operation completion correctly', function () {
        $operationId = $this->historyService->logOperationStart(
            $this->user->id,
            'export',
            ['format' => 'json']
        );

        $this->historyService->logOperationComplete(
            $operationId,
            $this->user->id,
            'export',
            true,
            ['exported' => 10]
        );

        $history = $this->historyService->getHistory($this->user->id);
        expect($history['data'])->not->toBeEmpty();
    });

    it('calculates statistics correctly', function () {
        // Log some operations
        $operationId1 = $this->historyService->logOperationStart($this->user->id, 'import', []);
        $this->historyService->logOperationComplete($operationId1, $this->user->id, 'import', true, ['imported' => 5]);

        $operationId2 = $this->historyService->logOperationStart($this->user->id, 'export', []);
        $this->historyService->logOperationComplete($operationId2, $this->user->id, 'export', true, ['exported' => 10]);

        $stats = $this->historyService->getStatistics($this->user->id, 'month');

        expect($stats['total_operations'])->toBeGreaterThanOrEqual(2);
        expect($stats['successful_operations'])->toBeGreaterThanOrEqual(2);
    });

    it('returns recent operations', function () {
        $operationId = $this->historyService->logOperationStart($this->user->id, 'backup', []);
        $this->historyService->logOperationComplete($operationId, $this->user->id, 'backup', true, []);

        $recent = $this->historyService->getRecentOperations($this->user->id, 5);

        expect($recent)->toBeArray();
    });

    it('tracks ongoing operations', function () {
        // Start an operation but don't complete it
        $operationId = $this->historyService->logOperationStart($this->user->id, 'migration', []);

        $ongoing = $this->historyService->getOngoingOperations($this->user->id);

        expect($ongoing)->toBeArray();
    });
});

describe('Blade Components', function () {
    it('renders progress tracker component', function () {
        $view = $this->blade(
            '<x-data-management.progress-tracker 
                operation-id="test-123" 
                operation-type="import" 
                status="in_progress" 
                :progress="50" 
                message="Processing..." />'
        );

        $view->assertSee('import');
        $view->assertSee('Processing...');
    });

    it('renders error display component', function () {
        $view = $this->blade(
            '<x-data-management.error-display 
                :errors="[\'Error 1\', \'Error 2\']" 
                title="Import Errors" />'
        );

        $view->assertSee('Import Errors');
        $view->assertSee('Error 1');
        $view->assertSee('Error 2');
    });

    it('renders operation history component with empty state', function () {
        $view = $this->blade(
            '<x-data-management.operation-history 
                :operations="[]" 
                empty-message="No operations yet" />'
        );

        $view->assertSee('No operations yet');
    });
});
