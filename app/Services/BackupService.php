<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Career;
use App\Models\Character;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Backup Service
 *
 * Handles automated backup creation with scheduling, manual backup functionality
 * with compression, restore functionality with data integrity verification,
 * and backup encryption with secure storage options.
 *
 * Requirements: 23.4
 */
class BackupService
{
    /**
     * Backup storage disk
     */
    private const BACKUP_DISK = 'local';

    /**
     * Backup directory
     */
    private const BACKUP_DIR = 'backups';

    /**
     * Backup status constants
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /**
     * Backup types
     */
    public const TYPE_FULL = 'full';

    public const TYPE_INCREMENTAL = 'incremental';

    public const TYPE_SELECTIVE = 'selective';

    /**
     * Schedule frequency options
     */
    public const SCHEDULE_DAILY = 'daily';

    public const SCHEDULE_WEEKLY = 'weekly';

    public const SCHEDULE_MONTHLY = 'monthly';

    public const SCHEDULE_CUSTOM = 'custom';

    /**
     * Backup version for compatibility
     */
    private const BACKUP_VERSION = '1.0';

    /**
     * Maximum backup retention (days)
     */
    private const MAX_RETENTION_DAYS = 90;

    /**
     * Cache prefix for backup operations
     */
    private const CACHE_PREFIX = 'backup_';

    public function __construct(
        private readonly DataExportService $exportService
    ) {}

    /**
     * Create a manual backup with optional compression and encryption
     *
     * @param  int  $userId  User ID
     * @param  array  $options  Backup options
     * @return array{success: bool, backup_id: string, file_path: string, file_size: int, checksum: string, errors: array}
     */
    public function createBackup(int $userId, array $options = []): array
    {
        $backupId = Str::uuid()->toString();
        $compress = $options['compress'] ?? true;
        $encrypt = $options['encrypt'] ?? false;
        $encryptionKey = $options['encryption_key'] ?? null;
        $backupType = $options['type'] ?? self::TYPE_FULL;
        $includeTypes = $options['include_types'] ?? ['character', 'career', 'skill', 'support_card'];
        $description = $options['description'] ?? 'Manual backup';

        Log::info('[BackupService] Starting backup creation', [
            'backup_id' => $backupId,
            'user_id' => $userId,
            'type' => $backupType,
            'compress' => $compress,
            'encrypt' => $encrypt,
        ]);

        try {
            // Update backup status
            $this->updateBackupStatus($backupId, self::STATUS_IN_PROGRESS);

            // Generate backup data
            $backupData = $this->generateBackupData($userId, $backupType, $includeTypes);

            if (! $backupData['success']) {
                return [
                    'success' => false,
                    'backup_id' => $backupId,
                    'file_path' => '',
                    'file_size' => 0,
                    'checksum' => '',
                    'errors' => $backupData['errors'],
                ];
            }

            // Create backup metadata
            $metadata = [
                'backup_id' => $backupId,
                'version' => self::BACKUP_VERSION,
                'type' => $backupType,
                'user_id' => $userId,
                'created_at' => now()->toIso8601String(),
                'description' => $description,
                'include_types' => $includeTypes,
                'compressed' => $compress,
                'encrypted' => $encrypt,
                'record_counts' => $backupData['counts'],
            ];

            // Combine metadata and data
            $fullBackup = [
                'metadata' => $metadata,
                'data' => $backupData['data'],
            ];

            // Convert to JSON
            $jsonContent = json_encode($fullBackup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($jsonContent === false) {
                throw new \RuntimeException('Failed to encode backup content as JSON');
            }

            // Encrypt if requested
            if ($encrypt) {
                $jsonContent = $this->encryptContent($jsonContent, $encryptionKey);
            }

            // Save backup file
            $timestamp = now()->format('Y-m-d_His');
            $extension = $compress ? 'zip' : 'json';
            $fileName = "backup_{$backupType}_{$timestamp}_{$backupId}.{$extension}";
            $filePath = self::BACKUP_DIR."/{$userId}/{$fileName}";

            // Ensure directory exists
            Storage::disk(self::BACKUP_DISK)->makeDirectory(self::BACKUP_DIR."/{$userId}");

            if ($compress) {
                $result = $this->compressAndSave($jsonContent, $filePath, $encrypt);
            } else {
                Storage::disk(self::BACKUP_DISK)->put($filePath, $jsonContent);
                $result = ['success' => true, 'file_path' => $filePath];
            }

            if (! $result['success']) {
                $this->updateBackupStatus($backupId, self::STATUS_FAILED);

                return [
                    'success' => false,
                    'backup_id' => $backupId,
                    'file_path' => '',
                    'file_size' => 0,
                    'checksum' => '',
                    'errors' => ['Failed to save backup file'],
                ];
            }

            // Calculate checksum
            $fileContent = Storage::disk(self::BACKUP_DISK)->get($filePath);
            if ($fileContent === false) {
                throw new \RuntimeException('Failed to read backup file contents');
            }
            $checksum = hash('sha256', $fileContent);
            $fileSize = strlen($fileContent);

            // Store backup record
            $this->storeBackupRecord($backupId, $userId, $metadata, $filePath, $fileSize, $checksum);

            // Update status
            $this->updateBackupStatus($backupId, self::STATUS_COMPLETED);

            Log::info('[BackupService] Backup created successfully', [
                'backup_id' => $backupId,
                'file_path' => $filePath,
                'file_size' => $fileSize,
            ]);

            return [
                'success' => true,
                'backup_id' => $backupId,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'checksum' => $checksum,
                'errors' => [],
            ];
        } catch (\Exception $e) {
            $this->updateBackupStatus($backupId, self::STATUS_FAILED);

            Log::error('[BackupService] Backup creation failed', [
                'backup_id' => $backupId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'backup_id' => $backupId,
                'file_path' => '',
                'file_size' => 0,
                'checksum' => '',
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Generate backup data based on type and included types
     */
    private function generateBackupData(int $userId, string $backupType, array $includeTypes): array
    {
        $data = [];
        $counts = [];
        $errors = [];

        try {
            foreach ($includeTypes as $type) {
                $exportResult = $this->exportService->generateExport($type, $userId, []);

                if ($exportResult['success']) {
                    $data[$type] = $exportResult['data'];
                    $counts[$type] = $exportResult['count'];
                } else {
                    $errors[] = "Failed to export {$type}: ".implode(', ', $exportResult['errors']);
                }
            }

            return [
                'success' => empty($errors),
                'data' => $data,
                'counts' => $counts,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'counts' => [],
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Compress content and save to file
     */
    private function compressAndSave(string $content, string $filePath, bool $encrypted): array
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'backup_');
        $zipPath = $tempFile.'.zip';

        try {
            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return ['success' => false, 'error' => 'Failed to create zip archive'];
            }

            $dataFileName = $encrypted ? 'backup_data.enc' : 'backup_data.json';
            $zip->addFromString($dataFileName, $content);
            $zip->close();

            // Move to storage
            $zipContent = file_get_contents($zipPath);
            Storage::disk(self::BACKUP_DISK)->put($filePath, $zipContent);

            // Cleanup temp files
            @unlink($tempFile);
            @unlink($zipPath);

            return ['success' => true, 'file_path' => $filePath];
        } catch (\Exception $e) {
            @unlink($tempFile);
            @unlink($zipPath);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Encrypt content using Laravel's encryption
     */
    private function encryptContent(string $content, ?string $customKey = null): string
    {
        if ($customKey) {
            // Use custom key for encryption
            return openssl_encrypt($content, 'AES-256-CBC', $customKey, 0, substr(hash('sha256', $customKey), 0, 16));
        }

        return Crypt::encryptString($content);
    }

    /**
     * Decrypt content
     */
    private function decryptContent(string $content, ?string $customKey = null): string
    {
        if ($customKey) {
            return openssl_decrypt($content, 'AES-256-CBC', $customKey, 0, substr(hash('sha256', $customKey), 0, 16));
        }

        return Crypt::decryptString($content);
    }

    /**
     * Restore from backup with data integrity verification
     *
     * @param  string  $backupId  Backup ID
     * @param  int  $userId  User ID
     * @param  array  $options  Restore options
     * @return array{success: bool, restored_counts: array, errors: array, warnings: array, dry_run?: bool}
     */
    public function restoreFromBackup(string $backupId, int $userId, array $options = []): array
    {
        $decryptionKey = $options['decryption_key'] ?? null;
        $overwriteExisting = $options['overwrite_existing'] ?? false;
        $restoreTypes = $options['restore_types'] ?? null; // null = restore all
        $dryRun = $options['dry_run'] ?? false;

        Log::info('[BackupService] Starting backup restoration', [
            'backup_id' => $backupId,
            'user_id' => $userId,
            'dry_run' => $dryRun,
        ]);

        try {
            // Get backup record
            $backupRecord = $this->getBackupRecord($backupId);

            if (! $backupRecord) {
                return [
                    'success' => false,
                    'restored_counts' => [],
                    'errors' => ['Backup not found'],
                    'warnings' => [],
                ];
            }

            // Verify ownership
            if ($backupRecord['user_id'] !== $userId) {
                return [
                    'success' => false,
                    'restored_counts' => [],
                    'errors' => ['Unauthorized: Backup belongs to another user'],
                    'warnings' => [],
                ];
            }

            // Read and decompress backup file
            $backupData = $this->readBackupFile($backupRecord['file_path'], $backupRecord['encrypted'], $decryptionKey);

            if (! $backupData['success']) {
                return [
                    'success' => false,
                    'restored_counts' => [],
                    'errors' => $backupData['errors'],
                    'warnings' => [],
                ];
            }

            // Verify data integrity
            $integrityCheck = $this->verifyDataIntegrity($backupData['data'], $backupRecord['checksum'], $backupRecord['file_path']);

            if (! $integrityCheck['valid']) {
                return [
                    'success' => false,
                    'restored_counts' => [],
                    'errors' => ['Data integrity verification failed: '.$integrityCheck['error']],
                    'warnings' => [],
                ];
            }

            // Validate backup version compatibility
            $versionCheck = $this->checkVersionCompatibility($backupData['data']['metadata']['version'] ?? '0.0');

            if (! $versionCheck['compatible']) {
                return [
                    'success' => false,
                    'restored_counts' => [],
                    'errors' => ['Incompatible backup version: '.$versionCheck['error']],
                    'warnings' => [],
                ];
            }

            if ($dryRun) {
                return $this->simulateRestore($backupData['data'], $restoreTypes);
            }

            // Perform actual restore
            return $this->executeRestore($backupData['data'], $userId, $restoreTypes, $overwriteExisting);
        } catch (\Exception $e) {
            Log::error('[BackupService] Restore failed', [
                'backup_id' => $backupId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'restored_counts' => [],
                'errors' => [$e->getMessage()],
                'warnings' => [],
            ];
        }
    }

    /**
     * Read and decompress backup file
     */
    private function readBackupFile(string $filePath, bool $encrypted, ?string $decryptionKey): array
    {
        try {
            if (! Storage::disk(self::BACKUP_DISK)->exists($filePath)) {
                return [
                    'success' => false,
                    'data' => null,
                    'errors' => ['Backup file not found'],
                ];
            }

            $fileContent = Storage::disk(self::BACKUP_DISK)->get($filePath);

            // Check if it's a zip file
            if (str_ends_with($filePath, '.zip')) {
                $fileContent = $this->decompressContent($fileContent, $encrypted);
            }

            // Decrypt if needed
            if ($encrypted) {
                $fileContent = $this->decryptContent($fileContent, $decryptionKey);
            }

            $data = json_decode($fileContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'data' => null,
                    'errors' => ['Invalid backup data format: '.json_last_error_msg()],
                ];
            }

            return [
                'success' => true,
                'data' => $data,
                'errors' => [],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Decompress zip content
     */
    private function decompressContent(string $zipContent, bool $encrypted): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'backup_restore_');
        file_put_contents($tempFile, $zipContent);

        $zip = new ZipArchive;
        if ($zip->open($tempFile) !== true) {
            @unlink($tempFile);
            throw new \RuntimeException('Failed to open backup archive');
        }

        $dataFileName = $encrypted ? 'backup_data.enc' : 'backup_data.json';
        $content = $zip->getFromName($dataFileName);
        $zip->close();
        @unlink($tempFile);

        if ($content === false) {
            throw new \RuntimeException('Backup data file not found in archive');
        }

        return $content;
    }

    /**
     * Verify data integrity using checksum
     */
    private function verifyDataIntegrity(array $data, string $storedChecksum, string $filePath): array
    {
        try {
            $fileContent = Storage::disk(self::BACKUP_DISK)->get($filePath);
            $calculatedChecksum = hash('sha256', $fileContent);

            if ($calculatedChecksum !== $storedChecksum) {
                return [
                    'valid' => false,
                    'error' => 'Checksum mismatch - file may be corrupted',
                ];
            }

            // Validate data structure
            if (! isset($data['metadata']) || ! isset($data['data'])) {
                return [
                    'valid' => false,
                    'error' => 'Invalid backup structure',
                ];
            }

            return ['valid' => true, 'error' => null];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check backup version compatibility
     */
    private function checkVersionCompatibility(string $backupVersion): array
    {
        $currentVersion = self::BACKUP_VERSION;
        $backupParts = explode('.', $backupVersion);
        $currentParts = explode('.', $currentVersion);

        // Major version must match
        if (($backupParts[0] ?? '0') !== ($currentParts[0] ?? '0')) {
            return [
                'compatible' => false,
                'error' => "Major version mismatch: backup v{$backupVersion}, current v{$currentVersion}",
            ];
        }

        return ['compatible' => true, 'error' => null];
    }

    /**
     * Simulate restore operation (dry run)
     */
    /**
     * @return array{success: bool, restored_counts: array, errors: array, warnings: array, dry_run: bool}
     */
    private function simulateRestore(array $backupData, ?array $restoreTypes): array
    {
        $counts = [];
        $warnings = [];

        $data = $backupData['data'] ?? [];
        $typesToRestore = $restoreTypes ?? array_keys($data);

        foreach ($typesToRestore as $type) {
            if (isset($data[$type])) {
                $counts[$type] = count($data[$type]);
            } else {
                $warnings[] = "Type '{$type}' not found in backup";
            }
        }

        return [
            'success' => true,
            'restored_counts' => $counts,
            'errors' => [],
            'warnings' => $warnings,
            'dry_run' => true,
        ];
    }

    /**
     * Execute the actual restore operation
     */
    /**
     * @return array{success: bool, restored_counts: array, errors: array, warnings: array}
     */
    private function executeRestore(array $backupData, int $userId, ?array $restoreTypes, bool $overwriteExisting): array
    {
        $restoredCounts = [];
        $errors = [];
        $warnings = [];

        $data = $backupData['data'] ?? [];
        $typesToRestore = $restoreTypes ?? array_keys($data);

        DB::beginTransaction();

        try {
            foreach ($typesToRestore as $type) {
                if (! isset($data[$type])) {
                    $warnings[] = "Type '{$type}' not found in backup";

                    continue;
                }

                $result = match ($type) {
                    'characters' => $this->restoreCharacters($data[$type], $userId, $overwriteExisting),
                    'careers' => $this->restoreCareers($data[$type], $userId, $overwriteExisting),
                    'skills' => $this->restoreSkills($data[$type], $overwriteExisting),
                    'support_cards' => $this->restoreSupportCards($data[$type], $overwriteExisting),
                    default => ['restored' => 0, 'errors' => ["Unknown type: {$type}"]],
                };

                $restoredCounts[$type] = $result['restored'];
                if (! empty($result['errors'])) {
                    $errors = array_merge($errors, $result['errors']);
                }
                if (! empty($result['warnings'])) {
                    $warnings = array_merge($warnings, $result['warnings']);
                }
            }

            if (empty($errors)) {
                DB::commit();
                Log::info('[BackupService] Restore completed successfully', [
                    'user_id' => $userId,
                    'restored_counts' => $restoredCounts,
                ]);
            } else {
                DB::rollBack();
                Log::warning('[BackupService] Restore completed with errors', [
                    'user_id' => $userId,
                    'errors' => $errors,
                ]);
            }

            return [
                'success' => empty($errors),
                'restored_counts' => $restoredCounts,
                'errors' => $errors,
                'warnings' => $warnings,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[BackupService] Restore transaction failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'restored_counts' => [],
                'errors' => [$e->getMessage()],
                'warnings' => [],
            ];
        }
    }

    /**
     * Restore characters from backup
     */
    private function restoreCharacters(array $characters, int $userId, bool $overwrite): array
    {
        $restored = 0;
        $errors = [];
        $warnings = [];

        foreach ($characters as $charData) {
            try {
                /** @var Character|null $existingChar */
                $existingChar = Character::where('user_id', $userId)
                    ->where('name', $charData['name'])
                    ->first();

                if ($existingChar && ! $overwrite) {
                    $warnings[] = "Character '{$charData['name']}' already exists, skipped";

                    continue;
                }

                if ($existingChar && $overwrite) {
                    $existingChar->update([
                        'scenario_type' => $charData['scenario_type'] ?? 'ura_finale',
                        'current_stats' => $charData['current_stats'] ?? [],
                        'energy_level' => $charData['energy_level'] ?? 100,
                        'mood_status' => $charData['mood_status'] ?? 'normal',
                    ]);
                } else {
                    Character::create([
                        'user_id' => $userId,
                        'name' => $charData['name'],
                        'scenario_type' => $charData['scenario_type'] ?? 'ura_finale',
                        'current_stats' => $charData['current_stats'] ?? [],
                        'energy_level' => $charData['energy_level'] ?? 100,
                        'mood_status' => $charData['mood_status'] ?? 'normal',
                    ]);
                }

                $restored++;
            } catch (\Exception $e) {
                $errors[] = "Failed to restore character '{$charData['name']}': ".$e->getMessage();
            }
        }

        return ['restored' => $restored, 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Restore careers from backup
     */
    private function restoreCareers(array $careers, int $userId, bool $overwrite): array
    {
        $restored = 0;
        $errors = [];
        $warnings = [];

        foreach ($careers as $careerData) {
            try {
                /** @var Career|null $existingCareer */
                $existingCareer = Career::where('user_id', $userId)
                    ->where('career_name', $careerData['career_name'])
                    ->first();

                if ($existingCareer && ! $overwrite) {
                    $warnings[] = "Career '{$careerData['career_name']}' already exists, skipped";

                    continue;
                }

                $careerAttributes = [
                    'user_id' => $userId,
                    'career_name' => $careerData['career_name'],
                    'scenario_type' => $careerData['scenario_type'] ?? 'ura_finale',
                    'status' => $careerData['status'] ?? 'active',
                    'current_turn' => $careerData['current_turn'] ?? 1,
                ];

                if ($existingCareer && $overwrite) {
                    $existingCareer->update($careerAttributes);
                } else {
                    Career::create($careerAttributes);
                }

                $restored++;
            } catch (\Exception $e) {
                $errors[] = "Failed to restore career '{$careerData['career_name']}': ".$e->getMessage();
            }
        }

        return ['restored' => $restored, 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Restore skills from backup (reference data)
     */
    private function restoreSkills(array $skills, bool $overwrite): array
    {
        $restored = 0;
        $errors = [];
        $warnings = [];

        foreach ($skills as $skillData) {
            try {
                /** @var Skill|null $existingSkill */
                $existingSkill = Skill::where('name', $skillData['name'])->first();

                if ($existingSkill && ! $overwrite) {
                    $warnings[] = "Skill '{$skillData['name']}' already exists, skipped";

                    continue;
                }

                $skillAttributes = [
                    'name' => $skillData['name'],
                    'skill_type' => $skillData['skill_type'] ?? 'passive',
                    'rarity' => $skillData['rarity'] ?? 'normal',
                    'base_sp_cost' => $skillData['base_sp_cost'] ?? 120,
                    'description' => $skillData['description'] ?? '',
                ];

                if ($existingSkill && $overwrite) {
                    $existingSkill->update($skillAttributes);
                } else {
                    Skill::create($skillAttributes);
                }

                $restored++;
            } catch (\Exception $e) {
                $errors[] = "Failed to restore skill '{$skillData['name']}': ".$e->getMessage();
            }
        }

        return ['restored' => $restored, 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Restore support cards from backup (reference data)
     */
    private function restoreSupportCards(array $cards, bool $overwrite): array
    {
        $restored = 0;
        $errors = [];
        $warnings = [];

        foreach ($cards as $cardData) {
            try {
                /** @var SupportCard|null $existingCard */
                $existingCard = SupportCard::where('name', $cardData['name'])->first();

                if ($existingCard && ! $overwrite) {
                    $warnings[] = "Support card '{$cardData['name']}' already exists, skipped";

                    continue;
                }

                $cardAttributes = [
                    'name' => $cardData['name'],
                    'rarity' => $cardData['rarity'] ?? 'SSR',
                    'card_type' => $cardData['specialization'] ?? 'speed',
                ];

                if ($existingCard && $overwrite) {
                    $existingCard->update($cardAttributes);
                } else {
                    SupportCard::create($cardAttributes);
                }

                $restored++;
            } catch (\Exception $e) {
                $errors[] = "Failed to restore support card '{$cardData['name']}': ".$e->getMessage();
            }
        }

        return ['restored' => $restored, 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Schedule automated backup
     *
     * @param  int  $userId  User ID
     * @param  array  $scheduleConfig  Schedule configuration
     * @return array{success: bool, schedule_id: string, errors: array}
     */
    public function scheduleBackup(int $userId, array $scheduleConfig): array
    {
        $scheduleId = Str::uuid()->toString();
        $frequency = $scheduleConfig['frequency'] ?? self::SCHEDULE_DAILY;
        $time = $scheduleConfig['time'] ?? '02:00';
        $backupType = $scheduleConfig['type'] ?? self::TYPE_FULL;
        $compress = $scheduleConfig['compress'] ?? true;
        $encrypt = $scheduleConfig['encrypt'] ?? false;
        $retentionDays = min($scheduleConfig['retention_days'] ?? 30, self::MAX_RETENTION_DAYS);
        $includeTypes = $scheduleConfig['include_types'] ?? ['character', 'career', 'skill', 'support_card'];

        try {
            $schedule = [
                'schedule_id' => $scheduleId,
                'user_id' => $userId,
                'frequency' => $frequency,
                'time' => $time,
                'backup_type' => $backupType,
                'compress' => $compress,
                'encrypt' => $encrypt,
                'retention_days' => $retentionDays,
                'include_types' => $includeTypes,
                'enabled' => true,
                'last_run' => null,
                'next_run' => $this->calculateNextRun($frequency, $time),
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ];

            // Store schedule in database
            DB::table('ucp_system_logs')->insert([
                'log_category' => 'backup_schedule',
                'log_level' => 'info',
                'log_source' => 'web',
                'event_type' => 'schedule_created',
                'message' => "Backup schedule created: {$scheduleId}",
                'context_data' => json_encode($schedule),
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Cache the schedule for quick access
            Cache::put(self::CACHE_PREFIX."schedule_{$scheduleId}", $schedule, now()->addDays(365));

            // Add to user's schedule list
            $userSchedules = Cache::get(self::CACHE_PREFIX."user_schedules_{$userId}", []);
            $userSchedules[$scheduleId] = $schedule;
            Cache::put(self::CACHE_PREFIX."user_schedules_{$userId}", $userSchedules, now()->addDays(365));

            Log::info('[BackupService] Backup schedule created', [
                'schedule_id' => $scheduleId,
                'user_id' => $userId,
                'frequency' => $frequency,
            ]);

            return [
                'success' => true,
                'schedule_id' => $scheduleId,
                'schedule' => $schedule,
                'errors' => [],
            ];
        } catch (\Exception $e) {
            Log::error('[BackupService] Failed to create backup schedule', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'schedule_id' => '',
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Calculate next run time based on frequency
     */
    private function calculateNextRun(string $frequency, string $time): string
    {
        $timeParts = explode(':', $time);
        $hour = (int) ($timeParts[0] ?? 2);
        $minute = (int) ($timeParts[1] ?? 0);

        $nextRun = now()->setTime($hour, $minute, 0);

        if ($nextRun->isPast()) {
            $nextRun = match ($frequency) {
                self::SCHEDULE_DAILY => $nextRun->addDay(),
                self::SCHEDULE_WEEKLY => $nextRun->addWeek(),
                self::SCHEDULE_MONTHLY => $nextRun->addMonth(),
                default => $nextRun->addDay(),
            };
        }

        return $nextRun->toIso8601String();
    }

    /**
     * Get backup schedule for user
     */
    public function getBackupSchedule(int $userId): array
    {
        $schedules = Cache::get(self::CACHE_PREFIX."user_schedules_{$userId}", []);

        return [
            'success' => true,
            'schedules' => array_values($schedules),
        ];
    }

    /**
     * Update backup schedule
     */
    public function updateBackupSchedule(string $scheduleId, int $userId, array $updates): array
    {
        $schedule = Cache::get(self::CACHE_PREFIX."schedule_{$scheduleId}");

        if (! $schedule || $schedule['user_id'] !== $userId) {
            return [
                'success' => false,
                'errors' => ['Schedule not found or unauthorized'],
            ];
        }

        $allowedUpdates = ['frequency', 'time', 'backup_type', 'compress', 'encrypt', 'retention_days', 'include_types', 'enabled'];

        foreach ($allowedUpdates as $field) {
            if (isset($updates[$field])) {
                $schedule[$field] = $updates[$field];
            }
        }

        $schedule['updated_at'] = now()->toIso8601String();
        $schedule['next_run'] = $this->calculateNextRun($schedule['frequency'], $schedule['time']);

        Cache::put(self::CACHE_PREFIX."schedule_{$scheduleId}", $schedule, now()->addDays(365));

        // Update user's schedule list
        $userSchedules = Cache::get(self::CACHE_PREFIX."user_schedules_{$userId}", []);
        $userSchedules[$scheduleId] = $schedule;
        Cache::put(self::CACHE_PREFIX."user_schedules_{$userId}", $userSchedules, now()->addDays(365));

        return [
            'success' => true,
            'schedule' => $schedule,
            'errors' => [],
        ];
    }

    /**
     * Delete backup schedule
     */
    public function deleteBackupSchedule(string $scheduleId, int $userId): array
    {
        $schedule = Cache::get(self::CACHE_PREFIX."schedule_{$scheduleId}");

        if (! $schedule || $schedule['user_id'] !== $userId) {
            return [
                'success' => false,
                'errors' => ['Schedule not found or unauthorized'],
            ];
        }

        Cache::forget(self::CACHE_PREFIX."schedule_{$scheduleId}");

        $userSchedules = Cache::get(self::CACHE_PREFIX."user_schedules_{$userId}", []);
        unset($userSchedules[$scheduleId]);
        Cache::put(self::CACHE_PREFIX."user_schedules_{$userId}", $userSchedules, now()->addDays(365));

        return [
            'success' => true,
            'errors' => [],
        ];
    }

    /**
     * List all backups for a user
     *
     * @param  int  $userId  User ID
     * @param  array  $filters  Optional filters
     * @return array{success: bool, backups: array, total: int}
     */
    public function listBackups(int $userId, array $filters = []): array
    {
        try {
            $backups = Cache::get(self::CACHE_PREFIX."user_backups_{$userId}", []);

            // Apply filters
            if (! empty($filters['type'])) {
                $backups = array_filter($backups, fn ($b) => $b['type'] === $filters['type']);
            }

            if (! empty($filters['status'])) {
                $backups = array_filter($backups, fn ($b) => $b['status'] === $filters['status']);
            }

            if (! empty($filters['date_from'])) {
                $backups = array_filter($backups, fn ($b) => $b['created_at'] >= $filters['date_from']);
            }

            if (! empty($filters['date_to'])) {
                $backups = array_filter($backups, fn ($b) => $b['created_at'] <= $filters['date_to']);
            }

            // Sort by created_at descending
            usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

            // Apply pagination
            $page = $filters['page'] ?? 1;
            $perPage = $filters['per_page'] ?? 20;
            $offset = ($page - 1) * $perPage;
            $total = count($backups);
            $backups = array_slice($backups, $offset, $perPage);

            return [
                'success' => true,
                'backups' => array_values($backups),
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
            ];
        } catch (\Exception $e) {
            Log::error('[BackupService] Failed to list backups', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'backups' => [],
                'total' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Get backup details
     */
    public function getBackupDetails(string $backupId, int $userId): ?array
    {
        $backup = $this->getBackupRecord($backupId);

        if (! $backup || $backup['user_id'] !== $userId) {
            return null;
        }

        return $backup;
    }

    /**
     * Delete a backup
     */
    public function deleteBackup(string $backupId, int $userId): array
    {
        try {
            $backup = $this->getBackupRecord($backupId);

            if (! $backup) {
                return [
                    'success' => false,
                    'errors' => ['Backup not found'],
                ];
            }

            if ($backup['user_id'] !== $userId) {
                return [
                    'success' => false,
                    'errors' => ['Unauthorized: Backup belongs to another user'],
                ];
            }

            // Delete file
            if (Storage::disk(self::BACKUP_DISK)->exists($backup['file_path'])) {
                Storage::disk(self::BACKUP_DISK)->delete($backup['file_path']);
            }

            // Remove from cache
            Cache::forget(self::CACHE_PREFIX."backup_{$backupId}");

            $userBackups = Cache::get(self::CACHE_PREFIX."user_backups_{$userId}", []);
            unset($userBackups[$backupId]);
            Cache::put(self::CACHE_PREFIX."user_backups_{$userId}", $userBackups, now()->addDays(365));

            Log::info('[BackupService] Backup deleted', [
                'backup_id' => $backupId,
                'user_id' => $userId,
            ]);

            return [
                'success' => true,
                'errors' => [],
            ];
        } catch (\Exception $e) {
            Log::error('[BackupService] Failed to delete backup', [
                'backup_id' => $backupId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Store backup record
     */
    private function storeBackupRecord(string $backupId, int $userId, array $metadata, string $filePath, int $fileSize, string $checksum): void
    {
        $record = [
            'backup_id' => $backupId,
            'user_id' => $userId,
            'type' => $metadata['type'],
            'description' => $metadata['description'],
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'checksum' => $checksum,
            'compressed' => $metadata['compressed'],
            'encrypted' => $metadata['encrypted'],
            'record_counts' => $metadata['record_counts'],
            'status' => self::STATUS_COMPLETED,
            'created_at' => $metadata['created_at'],
        ];

        // Store in cache
        Cache::put(self::CACHE_PREFIX."backup_{$backupId}", $record, now()->addDays(self::MAX_RETENTION_DAYS));

        // Add to user's backup list
        $userBackups = Cache::get(self::CACHE_PREFIX."user_backups_{$userId}", []);
        $userBackups[$backupId] = $record;
        Cache::put(self::CACHE_PREFIX."user_backups_{$userId}", $userBackups, now()->addDays(self::MAX_RETENTION_DAYS));

        // Log to database
        DB::table('ucp_system_logs')->insert([
            'log_category' => 'backup',
            'log_level' => 'info',
            'log_source' => 'web',
            'event_type' => 'backup_created',
            'message' => "Backup created: {$backupId}",
            'context_data' => json_encode($record),
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Get backup record
     */
    private function getBackupRecord(string $backupId): ?array
    {
        return Cache::get(self::CACHE_PREFIX."backup_{$backupId}");
    }

    /**
     * Update backup status
     */
    private function updateBackupStatus(string $backupId, string $status): void
    {
        $record = Cache::get(self::CACHE_PREFIX."backup_{$backupId}", []);
        $record['status'] = $status;
        $record['updated_at'] = now()->toIso8601String();
        Cache::put(self::CACHE_PREFIX."backup_{$backupId}", $record, now()->addDays(self::MAX_RETENTION_DAYS));
    }

    /**
     * Clean up old backups based on retention policy
     */
    public function cleanupOldBackups(int $userId, int $retentionDays = 30): array
    {
        $deleted = 0;
        $errors = [];

        try {
            $userBackups = Cache::get(self::CACHE_PREFIX."user_backups_{$userId}", []);
            $cutoffDate = now()->subDays($retentionDays)->toIso8601String();

            foreach ($userBackups as $backupId => $backup) {
                if ($backup['created_at'] < $cutoffDate) {
                    $result = $this->deleteBackup($backupId, $userId);
                    if ($result['success']) {
                        $deleted++;
                    } else {
                        $errors = array_merge($errors, $result['errors']);
                    }
                }
            }

            return [
                'success' => true,
                'deleted' => $deleted,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'deleted' => $deleted,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Get backup statistics for user
     */
    public function getBackupStatistics(int $userId): array
    {
        $userBackups = Cache::get(self::CACHE_PREFIX."user_backups_{$userId}", []);

        $totalSize = 0;
        $byType = [];
        $byStatus = [];

        foreach ($userBackups as $backup) {
            $totalSize += $backup['file_size'] ?? 0;
            $type = $backup['type'] ?? 'unknown';
            $status = $backup['status'] ?? 'unknown';

            $byType[$type] = ($byType[$type] ?? 0) + 1;
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
        }

        return [
            'total_backups' => count($userBackups),
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'by_type' => $byType,
            'by_status' => $byStatus,
            'schedules' => count(Cache::get(self::CACHE_PREFIX."user_schedules_{$userId}", [])),
        ];
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
