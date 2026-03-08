<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\BackupService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Backup and Restore System Tests
 *
 * Tests for Task 5.3.4: Create backup and restore system
 * Requirements: 23.4
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Clear any existing backup cache
    Cache::flush();

    // Setup fake storage
    Storage::fake('local');
});

describe('BackupService', function () {
    it('creates a full backup successfully', function () {
        $backupService = app(BackupService::class);

        $result = $backupService->createBackup($this->user->id, [
            'type' => BackupService::TYPE_FULL,
            'compress' => false,
            'encrypt' => false,
            'description' => 'Test backup',
        ]);

        expect($result['success'])->toBeTrue();
        expect($result['backup_id'])->not->toBeEmpty();
        expect($result['checksum'])->not->toBeEmpty();
    });

    it('creates a compressed backup', function () {
        $backupService = app(BackupService::class);

        $result = $backupService->createBackup($this->user->id, [
            'type' => BackupService::TYPE_FULL,
            'compress' => true,
            'encrypt' => false,
        ]);

        expect($result['success'])->toBeTrue();
        expect($result['file_path'])->toContain('.zip');
    });

    it('creates an encrypted backup', function () {
        $backupService = app(BackupService::class);

        $result = $backupService->createBackup($this->user->id, [
            'type' => BackupService::TYPE_FULL,
            'compress' => false,
            'encrypt' => true,
            'encryption_key' => 'test-encryption-key-12345',
        ]);

        expect($result['success'])->toBeTrue();
    });

    it('lists backups for a user', function () {
        $backupService = app(BackupService::class);

        // Create a backup first
        $backupService->createBackup($this->user->id, [
            'type' => BackupService::TYPE_FULL,
            'compress' => false,
        ]);

        $result = $backupService->listBackups($this->user->id);

        expect($result['success'])->toBeTrue();
        expect($result['backups'])->toBeArray();
    });

    it('gets backup details', function () {
        $backupService = app(BackupService::class);

        $createResult = $backupService->createBackup($this->user->id, [
            'type' => BackupService::TYPE_FULL,
            'compress' => false,
        ]);

        $details = $backupService->getBackupDetails($createResult['backup_id'], $this->user->id);

        expect($details)->not->toBeNull();
        expect($details['backup_id'])->toBe($createResult['backup_id']);
    });

    it('deletes a backup', function () {
        $backupService = app(BackupService::class);

        $createResult = $backupService->createBackup($this->user->id, [
            'type' => BackupService::TYPE_FULL,
            'compress' => false,
        ]);

        $deleteResult = $backupService->deleteBackup($createResult['backup_id'], $this->user->id);

        expect($deleteResult['success'])->toBeTrue();

        // Verify backup is gone
        $details = $backupService->getBackupDetails($createResult['backup_id'], $this->user->id);
        expect($details)->toBeNull();
    });

    it('prevents unauthorized backup access', function () {
        $backupService = app(BackupService::class);
        $otherUser = User::factory()->create();

        $createResult = $backupService->createBackup($this->user->id, [
            'type' => BackupService::TYPE_FULL,
            'compress' => false,
        ]);

        // Try to access with different user
        $details = $backupService->getBackupDetails($createResult['backup_id'], $otherUser->id);

        expect($details)->toBeNull();
    });
});

describe('Backup Scheduling', function () {
    it('creates a backup schedule', function () {
        $backupService = app(BackupService::class);

        $result = $backupService->scheduleBackup($this->user->id, [
            'frequency' => BackupService::SCHEDULE_DAILY,
            'time' => '02:00',
            'type' => BackupService::TYPE_FULL,
            'retention_days' => 30,
        ]);

        expect($result['success'])->toBeTrue();
        expect($result['schedule_id'])->not->toBeEmpty();
        expect($result['schedule']['frequency'])->toBe('daily');
    });

    it('lists backup schedules', function () {
        $backupService = app(BackupService::class);

        $backupService->scheduleBackup($this->user->id, [
            'frequency' => BackupService::SCHEDULE_DAILY,
            'time' => '02:00',
        ]);

        $result = $backupService->getBackupSchedule($this->user->id);

        expect($result['success'])->toBeTrue();
        expect($result['schedules'])->toHaveCount(1);
    });

    it('updates a backup schedule', function () {
        $backupService = app(BackupService::class);

        $createResult = $backupService->scheduleBackup($this->user->id, [
            'frequency' => BackupService::SCHEDULE_DAILY,
            'time' => '02:00',
        ]);

        $updateResult = $backupService->updateBackupSchedule(
            $createResult['schedule_id'],
            $this->user->id,
            ['frequency' => BackupService::SCHEDULE_WEEKLY]
        );

        expect($updateResult['success'])->toBeTrue();
        expect($updateResult['schedule']['frequency'])->toBe('weekly');
    });

    it('deletes a backup schedule', function () {
        $backupService = app(BackupService::class);

        $createResult = $backupService->scheduleBackup($this->user->id, [
            'frequency' => BackupService::SCHEDULE_DAILY,
            'time' => '02:00',
        ]);

        $deleteResult = $backupService->deleteBackupSchedule($createResult['schedule_id'], $this->user->id);

        expect($deleteResult['success'])->toBeTrue();

        // Verify schedule is gone
        $schedules = $backupService->getBackupSchedule($this->user->id);
        expect($schedules['schedules'])->toHaveCount(0);
    });
});

describe('Backup Restore', function () {
    it('restores from backup with dry run', function () {
        $backupService = app(BackupService::class);

        $createResult = $backupService->createBackup($this->user->id, [
            'type' => BackupService::TYPE_FULL,
            'compress' => false,
        ]);

        $restoreResult = $backupService->restoreFromBackup(
            $createResult['backup_id'],
            $this->user->id,
            ['dry_run' => true]
        );

        expect($restoreResult['success'])->toBeTrue();
        expect($restoreResult['dry_run'])->toBeTrue();
    });

    it('prevents restore of non-existent backup', function () {
        $backupService = app(BackupService::class);

        $result = $backupService->restoreFromBackup(
            'non-existent-backup-id',
            $this->user->id,
            []
        );

        expect($result['success'])->toBeFalse();
        expect($result['errors'])->toContain('Backup not found');
    });

    it('prevents unauthorized restore', function () {
        $backupService = app(BackupService::class);
        $otherUser = User::factory()->create();

        $createResult = $backupService->createBackup($this->user->id, [
            'type' => BackupService::TYPE_FULL,
            'compress' => false,
        ]);

        $restoreResult = $backupService->restoreFromBackup(
            $createResult['backup_id'],
            $otherUser->id,
            []
        );

        expect($restoreResult['success'])->toBeFalse();
        expect($restoreResult['errors'][0])->toContain('Unauthorized');
    });
});

describe('Backup Statistics', function () {
    it('returns backup statistics', function () {
        $backupService = app(BackupService::class);

        // Create a backup
        $backupService->createBackup($this->user->id, [
            'type' => BackupService::TYPE_FULL,
            'compress' => false,
        ]);

        $stats = $backupService->getBackupStatistics($this->user->id);

        expect($stats['total_backups'])->toBe(1);
        expect($stats)->toHaveKey('total_size');
        expect($stats)->toHaveKey('total_size_formatted');
        expect($stats)->toHaveKey('by_type');
        expect($stats)->toHaveKey('by_status');
    });
});

describe('Backup API Endpoints', function () {
    it('creates backup via API', function () {
        $response = $this->postJson('/api/backup/create', [
            'type' => 'full',
            'compress' => true,
            'description' => 'API test backup',
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => ['backup_id', 'file_path', 'file_size', 'checksum'],
        ]);
    });

    it('lists backups via API', function () {
        // Create a backup first
        $this->postJson('/api/backup/create', ['type' => 'full']);

        $response = $this->getJson('/api/backup/list');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => ['backups', 'total'],
        ]);
    });

    it('gets backup details via API', function () {
        $createResponse = $this->postJson('/api/backup/create', ['type' => 'full']);
        $backupId = $createResponse->json('data.backup_id');

        $response = $this->getJson("/api/backup/{$backupId}");

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => ['backup_id', 'type', 'status'],
        ]);
    });

    it('deletes backup via API', function () {
        $createResponse = $this->postJson('/api/backup/create', ['type' => 'full']);
        $backupId = $createResponse->json('data.backup_id');

        $response = $this->deleteJson("/api/backup/{$backupId}");

        $response->assertSuccessful();
        $response->assertJson(['success' => true]);
    });

    it('creates schedule via API', function () {
        $response = $this->postJson('/api/backup/schedule', [
            'frequency' => 'daily',
            'time' => '02:00',
            'type' => 'full',
            'retention_days' => 30,
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => ['schedule_id', 'schedule'],
        ]);
    });

    it('gets schedules via API', function () {
        $this->postJson('/api/backup/schedule', [
            'frequency' => 'daily',
            'time' => '02:00',
        ]);

        $response = $this->getJson('/api/backup/schedule');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => ['schedules'],
        ]);
    });

    it('gets statistics via API', function () {
        $response = $this->getJson('/api/backup/statistics');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => ['total_backups', 'total_size', 'total_size_formatted'],
        ]);
    });
});

describe('Backup Validation', function () {
    it('validates backup create request', function () {
        $response = $this->postJson('/api/backup/create', [
            'type' => 'invalid_type',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type']);
    });

    it('requires encryption key when encrypt is true', function () {
        $response = $this->postJson('/api/backup/create', [
            'type' => 'full',
            'encrypt' => true,
            // Missing encryption_key
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['encryption_key']);
    });

    it('validates schedule frequency', function () {
        $response = $this->postJson('/api/backup/schedule', [
            'frequency' => 'invalid_frequency',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['frequency']);
    });

    it('validates schedule time format', function () {
        $response = $this->postJson('/api/backup/schedule', [
            'frequency' => 'daily',
            'time' => 'invalid_time',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['time']);
    });

    it('validates retention days range', function () {
        $response = $this->postJson('/api/backup/schedule', [
            'frequency' => 'daily',
            'retention_days' => 100, // Max is 90
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['retention_days']);
    });
});

describe('Backup Cleanup', function () {
    it('cleans up old backups', function () {
        $backupService = app(BackupService::class);

        // Create a backup
        $backupService->createBackup($this->user->id, [
            'type' => BackupService::TYPE_FULL,
            'compress' => false,
        ]);

        // Cleanup with 0 days retention (should delete all)
        $result = $backupService->cleanupOldBackups($this->user->id, 0);

        expect($result['success'])->toBeTrue();
    });

    it('cleans up via API', function () {
        // Create a backup first
        $this->postJson('/api/backup/create', ['type' => 'full']);

        $response = $this->postJson('/api/backup/cleanup', [
            'retention_days' => 30,
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => ['deleted'],
        ]);
    });
});

describe('Backup Management Page', function () {
    it('renders actionable backup management sections', function () {
        $response = $this->get(route('backup.index'));

        $response->assertSuccessful();
        $response->assertSee('id="backup-page"', false);
        $response->assertSee('id="create"', false);
        $response->assertSee('id="restore"', false);
        $response->assertSee('Create Backup');
        $response->assertSee('Schedule Backup');
        $response->assertSee('Cleanup Old Backups');
    });
});
