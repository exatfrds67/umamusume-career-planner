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
     * @param  array<string, mixed>  $options  Backup options
     * @return array{success: bool, backup_id: string, file_path: string, file_size: int, checksum: string, errors: array<int, string>}
     */
    public function createBackup(int $userId, array $options = []): array
    {
        $backupId = Str::uuid()->toString();
        $compress = (bool) ($options['compress'] ?? true);
        $encrypt = (bool) ($options['encrypt'] ?? false);
        $encryptionKey = isset($options['encryption_key']) && \is_string($options['encryption_key']) ? $options['encryption_key'] : null;
        $backupType = isset($options['type']) && \is_string($options['type']) ? $options['type'] : self::TYPE_FULL;
        /** @var array<int, string> $includeTypes */
        $includeTypes = isset($options['include_types']) && \is_array($options['include_types']) ? array_values($options['include_types']) : ['character', 'career', 'skill', 'support_card'];
        $description = isset($options['description']) && \is_string($options['description']) ? $options['description'] : 'Manual backup';

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
            if ($fileContent === false || $fileContent === null) {
                throw new \RuntimeException('Failed to read backup file contents');
            }
            \assert(\is_string($fileContent));
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
     *
     * @param  array<int, string>  $includeTypes
     * @return array{success: bool, data: array<string, mixed>, counts: array<string, int>, errors: array<int, string>}
     */
    private function generateBackupData(int $userId, string $backupType, array $includeTypes): array
    {
        $data = [];
        $counts = [];
        $errors = [];

        try {
            foreach ($includeTypes as $type) {
                $exportResult = \call_user_func([$this->exportService, 'generateExport'], $type, $userId, []);

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
     *
     * @return array{success: bool, file_path?: string, error?: string}
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
            if ($zipContent === false) {
                throw new \RuntimeException('Failed to read zip file');
            }
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
            $encrypted = openssl_encrypt($content, 'AES-256-CBC', $customKey, 0, substr(hash('sha256', $customKey), 0, 16));
            if ($encrypted === false) {
                throw new \RuntimeException('Encryption failed');
            }

            return $encrypted;
        }

        return Crypt::encryptString($content);
    }

    /**
     * Decrypt content
     */
    private function decryptContent(string $content, ?string $customKey = null): string
    {
        if ($customKey) {
            $decrypted = openssl_decrypt($content, 'AES-256-CBC', $customKey, 0, substr(hash('sha256', $customKey), 0, 16));
            if ($decrypted === false) {
                throw new \RuntimeException('Decryption failed');
            }

            return $decrypted;
        }

        return Crypt::decryptString($content);
    }

    /**
     * Restore from backup with data integrity verification
     *
     * @param  string  $backupId  Backup ID
     * @param  int  $userId  User ID
     * @param  array<string, mixed>  $options  Restore options
     * @return array{success: bool, restored_counts: array<string, int>, errors: array<int, string>, warnings: array<int, string>, dry_run?: bool}
     */
    public function restoreFromBackup(string $backupId, int $userId, array $options = []): array
    {
        $decryptionKey = isset($options['decryption_key']) && \is_string($options['decryption_key']) ? $options['decryption_key'] : null;
        $overwriteExisting = (bool) ($options['overwrite_existing'] ?? false);
        $restoreTypes = isset($options['restore_types']) && \is_array($options['restore_types']) ? $options['restore_types'] : null; // null = restore all
        $dryRun = (bool) ($options['dry_run'] ?? false);

        Log::info('[BackupService] Starting backup restoration', [
            'backup_id' => $backupId,
            'user_id' => $userId,
            'dry_run' => $dryRun,
        ]);

        try {
            // Get backup record
            $backupRecord = $this->getBackupRecord($backupId);

            if (! \is_array($backupRecord)) {
                return [
                    'success' => false,
                    'restored_counts' => [],
                    'errors' => ['Backup not found'],
                    'warnings' => [],
                ];
            }

            // Verify ownership
            if (($backupRecord['user_id'] ?? null) !== $userId) {
                return [
                    'success' => false,
                    'restored_counts' => [],
                    'errors' => ['Unauthorized: Backup belongs to another user'],
                    'warnings' => [],
                ];
            }

            // Read and decompress backup file
            $filePath = \is_string($backupRecord['file_path'] ?? null) ? $backupRecord['file_path'] : '';
            $encrypted = (bool) ($backupRecord['encrypted'] ?? false);
            $checksum = \is_string($backupRecord['checksum'] ?? null) ? $backupRecord['checksum'] : '';

            $backupData = $this->readBackupFile($filePath, $encrypted, $decryptionKey);

            if (! $backupData['success']) {
                return [
                    'success' => false,
                    'restored_counts' => [],
                    'errors' => $backupData['errors'],
                    'warnings' => [],
                ];
            }

            // Verify data integrity
            $data = $backupData['data'];
            if (! \is_array($data)) {
                return [
                    'success' => false,
                    'restored_counts' => [],
                    'errors' => ['Invalid backup data structure'],
                    'warnings' => [],
                ];
            }

            /** @var array<string, mixed> $data */
            $integrityCheck = $this->verifyDataIntegrity($data, $checksum, $filePath);

            if (! $integrityCheck['valid']) {
                return [
                    'success' => false,
                    'restored_counts' => [],
                    'errors' => ['Data integrity verification failed: '.$integrityCheck['error']],
                    'warnings' => [],
                ];
            }

            // Validate backup version compatibility
            $metadata = $data['metadata'] ?? [];
            $version = \is_array($metadata) && isset($metadata['version']) && \is_string($metadata['version']) ? $metadata['version'] : '0.0';
            $versionCheck = $this->checkVersionCompatibility($version);

            if (! $versionCheck['compatible']) {
                return [
                    'success' => false,
                    'restored_counts' => [],
                    'errors' => ['Incompatible backup version: '.$versionCheck['error']],
                    'warnings' => [],
                ];
            }

            if ($dryRun) {
                return $this->simulateRestore($data, $restoreTypes);
            }

            // Perform actual restore
            return $this->executeRestore($data, $userId, $restoreTypes, $overwriteExisting);
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
     *
     * @return array{success: bool, data: mixed, errors: array<int, string>}
     */
    private function readBackupFile(string $filePath, bool $encrypted, ?string $decryptionKey = null): array
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
            if ($fileContent === false || $fileContent === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'errors' => ['Failed to read backup file'],
                ];
            }
            \assert(\is_string($fileContent));

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
     *
     * @param  array<string, mixed>  $data
     * @return array{valid: bool, error: string|null}
     */
    private function verifyDataIntegrity(array $data, string $storedChecksum, string $filePath): array
    {
        try {
            $fileContent = Storage::disk(self::BACKUP_DISK)->get($filePath);
            if ($fileContent === false || $fileContent === null) {
                return [
                    'valid' => false,
                    'error' => 'Failed to read backup file for integrity check',
                ];
            }
            \assert(\is_string($fileContent));
            $calculatedChecksum = hash('sha256', $fileContent);

            if ($calculatedChecksum !== $storedChecksum) {
                return [
                    'valid' => false,
                    'error' => 'Checksum mismatch - file may be corrupted',
                ];
            }

            // Validate data structure
            if (! isset($data['metadata'], $data['data'])) {
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
     *
     * @return array{compatible: bool, error: string|null}
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
     *
     * @param  array<string, mixed>  $backupData
     * @param  array<mixed, mixed>|null  $restoreTypes
     * @return array{success: bool, restored_counts: array<string, int>, errors: array<int, string>, warnings: array<int, string>, dry_run: bool}
     */
    private function simulateRestore(array $backupData, ?array $restoreTypes): array
    {
        /** @var array<string, int> $counts */
        $counts = [];
        /** @var array<int, string> $warnings */
        $warnings = [];

        $data = $backupData['data'] ?? [];
        if (! \is_array($data)) {
            $data = [];
        }
        /** @var array<int, string> $typesToRestore */
        $typesToRestore = $restoreTypes ?? array_keys($data);

        foreach ($typesToRestore as $type) {
            if (! \is_string($type)) {
                continue;
            }
            if (isset($data[$type]) && \is_array($data[$type])) {
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
     *
     * @param  array<string, mixed>  $backupData
     * @param  array<mixed, mixed>|null  $restoreTypes
     * @return array{success: bool, restored_counts: array<string, int>, errors: array<int, string>, warnings: array<int, string>}
     */
    private function executeRestore(array $backupData, int $userId, ?array $restoreTypes, bool $overwriteExisting): array
    {
        /** @var array<string, int> $restoredCounts */
        $restoredCounts = [];
        /** @var array<int, string> $errors */
        $errors = [];
        /** @var array<int, string> $warnings */
        $warnings = [];

        $data = $backupData['data'] ?? [];
        if (! \is_array($data)) {
            $data = [];
        }
        /** @var array<int, string> $typesToRestore */
        $typesToRestore = $restoreTypes ?? array_keys($data);

        DB::beginTransaction();

        try {
            foreach ($typesToRestore as $type) {
                if (! \is_string($type)) {
                    continue;
                }
                if (! isset($data[$type]) || ! \is_array($data[$type])) {
                    $warnings[] = "Type '{$type}' not found in backup";

                    continue;
                }

                /** @var array<int, array<string, mixed>> $typeData */
                $typeData = array_values($data[$type]);

                $result = match ($type) {
                    'characters' => $this->restoreCharacters($typeData, $userId, $overwriteExisting),
                    'careers' => $this->restoreCareers($typeData, $userId, $overwriteExisting),
                    'skills' => $this->restoreSkills($typeData, $overwriteExisting),
                    'support_cards' => $this->restoreSupportCards($typeData, $overwriteExisting),
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
     *
     * @param  array<int, array<string, mixed>>  $characters
     * @return array{restored: int, errors: array<int, string>, warnings: array<int, string>}
     */
    private function restoreCharacters(array $characters, int $userId, bool $overwrite): array
    {
        $restored = 0;
        $errors = [];
        $warnings = [];

        foreach ($characters as $charData) {
            try {
                $charName = \is_string($charData['name'] ?? null) ? $charData['name'] : 'Unknown';

                /** @var Character|null $existingChar */
                $existingChar = Character::where('user_id', $userId)
                    ->where('name', $charName)
                    ->first();

                if ($existingChar && ! $overwrite) {
                    $warnings[] = "Character '{$charName}' already exists, skipped";

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
                        'name' => $charName,
                        'scenario_type' => $charData['scenario_type'] ?? 'ura_finale',
                        'current_stats' => $charData['current_stats'] ?? [],
                        'energy_level' => $charData['energy_level'] ?? 100,
                        'mood_status' => $charData['mood_status'] ?? 'normal',
                    ]);
                }

                $restored++;
            } catch (\Exception $e) {
                $charName = \is_string($charData['name'] ?? null) ? $charData['name'] : 'Unknown';
                $errors[] = "Failed to restore character '{$charName}': ".$e->getMessage();
            }
        }

        return ['restored' => $restored, 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Restore careers from backup
     *
     * @param  array<int, array<string, mixed>>  $careers
     * @return array{restored: int, errors: array<int, string>, warnings: array<int, string>}
     */
    private function restoreCareers(array $careers, int $userId, bool $overwrite): array
    {
        $restored = 0;
        $errors = [];
        $warnings = [];

        foreach ($careers as $careerData) {
            try {
                $careerName = \is_string($careerData['career_name'] ?? null) ? $careerData['career_name'] : 'Unknown';

                /** @var Career|null $existingCareer */
                $existingCareer = Career::where('user_id', $userId)
                    ->where('career_name', $careerName)
                    ->first();

                if ($existingCareer && ! $overwrite) {
                    $warnings[] = "Career '{$careerName}' already exists, skipped";

                    continue;
                }

                $careerAttributes = [
                    'user_id' => $userId,
                    'career_name' => $careerName,
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
                $careerName = \is_string($careerData['career_name'] ?? null) ? $careerData['career_name'] : 'Unknown';
                $errors[] = "Failed to restore career '{$careerName}': ".$e->getMessage();
            }
        }

        return ['restored' => $restored, 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Restore skills from backup (reference data)
     *
     * @param  array<int, array<string, mixed>>  $skills
     * @return array{restored: int, errors: array<int, string>, warnings: array<int, string>}
     */
    private function restoreSkills(array $skills, bool $overwrite): array
    {
        $restored = 0;
        $errors = [];
        $warnings = [];

        foreach ($skills as $skillData) {
            try {
                $skillName = \is_string($skillData['name'] ?? null) ? $skillData['name'] : 'Unknown';

                /** @var Skill|null $existingSkill */
                $existingSkill = Skill::where('name', $skillName)->first();

                if ($existingSkill && ! $overwrite) {
                    $warnings[] = "Skill '{$skillName}' already exists, skipped";

                    continue;
                }

                $skillAttributes = [
                    'name' => $skillName,
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
                $skillName = \is_string($skillData['name'] ?? null) ? $skillData['name'] : 'Unknown';
                $errors[] = "Failed to restore skill '{$skillName}': ".$e->getMessage();
            }
        }

        return ['restored' => $restored, 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Restore support cards from backup (reference data)
     *
     * @param  array<int, array<string, mixed>>  $cards
     * @return array{restored: int, errors: array<int, string>, warnings: array<int, string>}
     */
    private function restoreSupportCards(array $cards, bool $overwrite): array
    {
        $restored = 0;
        $errors = [];
        $warnings = [];

        foreach ($cards as $cardData) {
            try {
                $cardName = \is_string($cardData['name'] ?? null) ? $cardData['name'] : 'Unknown';

                /** @var SupportCard|null $existingCard */
                $existingCard = SupportCard::where('name', $cardName)->first();

                if ($existingCard && ! $overwrite) {
                    $warnings[] = "Support card '{$cardName}' already exists, skipped";

                    continue;
                }

                $cardAttributes = [
                    'name' => $cardName,
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
                $cardName = \is_string($cardData['name'] ?? null) ? $cardData['name'] : 'Unknown';
                $errors[] = "Failed to restore support card '{$cardName}': ".$e->getMessage();
            }
        }

        return ['restored' => $restored, 'errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Schedule automated backup
     *
     * @param  int  $userId  User ID
     * @param  array<string, mixed>  $scheduleConfig  Schedule configuration
     * @return array{success: bool, schedule_id: string, errors: array<int, string>}
     */
    public function scheduleBackup(int $userId, array $scheduleConfig = []): array
    {
        $scheduleId = Str::uuid()->toString();
        $frequency = \is_string($scheduleConfig['frequency'] ?? null) ? $scheduleConfig['frequency'] : self::SCHEDULE_DAILY;
        $time = \is_string($scheduleConfig['time'] ?? null) ? $scheduleConfig['time'] : '02:00';
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
            /** @var array<string, array<string, mixed>> $userSchedules */
            $userSchedules = Cache::get(self::CACHE_PREFIX."user_schedules_{$userId}", []);
            if (! \is_array($userSchedules)) {
                $userSchedules = [];
            }
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
     *
     * @return array{success: bool, schedules: array<int, array<string, mixed>>}
     */
    public function getBackupSchedule(int $userId): array
    {
        /** @var array<string, array<string, mixed>> $schedules */
        $schedules = Cache::get(self::CACHE_PREFIX."user_schedules_{$userId}", []);
        if (! \is_array($schedules)) {
            $schedules = [];
        }

        return [
            'success' => true,
            'schedules' => array_values($schedules),
        ];
    }

    /**
     * Update backup schedule
     *
     * @param  array<string, mixed>  $updates
     * @return array{success: bool, schedule?: array<string, mixed>, errors: array<int, string>}
     */
    public function updateBackupSchedule(string $scheduleId, int $userId, array $updates): array
    {
        /** @var array<string, mixed>|null $schedule */
        $schedule = Cache::get(self::CACHE_PREFIX."schedule_{$scheduleId}");

        if (! \is_array($schedule) || ($schedule['user_id'] ?? null) !== $userId) {
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
        $frequency = \is_string($schedule['frequency'] ?? null) ? $schedule['frequency'] : self::SCHEDULE_DAILY;
        $time = \is_string($schedule['time'] ?? null) ? $schedule['time'] : '02:00';
        $schedule['next_run'] = $this->calculateNextRun($frequency, $time);

        Cache::put(self::CACHE_PREFIX."schedule_{$scheduleId}", $schedule, now()->addDays(365));

        // Update user's schedule list
        /** @var array<string, array<string, mixed>> $userSchedules */
        $userSchedules = Cache::get(self::CACHE_PREFIX."user_schedules_{$userId}", []);
        if (! \is_array($userSchedules)) {
            $userSchedules = [];
        }
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
     *
     * @return array{success: bool, errors: array<int, string>}
     */
    public function deleteBackupSchedule(string $scheduleId, int $userId): array
    {
        /** @var array<string, mixed>|null $schedule */
        $schedule = Cache::get(self::CACHE_PREFIX."schedule_{$scheduleId}");

        if (! \is_array($schedule) || ($schedule['user_id'] ?? null) !== $userId) {
            return [
                'success' => false,
                'errors' => ['Schedule not found or unauthorized'],
            ];
        }

        Cache::forget(self::CACHE_PREFIX."schedule_{$scheduleId}");

        /** @var array<string, array<string, mixed>> $userSchedules */
        $userSchedules = Cache::get(self::CACHE_PREFIX."user_schedules_{$userId}", []);
        if (! \is_array($userSchedules)) {
            $userSchedules = [];
        }
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
     * @param  array<string, mixed>  $filters  Optional filters
     * @return array{success: bool, backups: array<int, array<string, mixed>>, total: int, page?: int, per_page?: int, errors?: array<int, string>}
     */
    public function listBackups(int $userId, array $filters = []): array
    {
        try {
            /** @var array<string, array<string, mixed>> $backups */
            $backups = Cache::get(self::CACHE_PREFIX."user_backups_{$userId}", []);
            if (! \is_array($backups)) {
                $backups = [];
            }

            // Apply filters
            if (! empty($filters['type']) && \is_string($filters['type'])) {
                $filterType = $filters['type'];
                $backups = array_filter($backups, fn ($b) => ($b['type'] ?? null) === $filterType);
            }

            if (! empty($filters['status']) && \is_string($filters['status'])) {
                $filterStatus = $filters['status'];
                $backups = array_filter($backups, fn ($b) => ($b['status'] ?? null) === $filterStatus);
            }

            if (! empty($filters['date_from']) && \is_string($filters['date_from'])) {
                $filterDateFrom = $filters['date_from'];
                $backups = array_filter($backups, fn ($b) => ($b['created_at'] ?? '') >= $filterDateFrom);
            }

            if (! empty($filters['date_to']) && \is_string($filters['date_to'])) {
                $filterDateTo = $filters['date_to'];
                $backups = array_filter($backups, fn ($b) => ($b['created_at'] ?? '') <= $filterDateTo);
            }

            // Sort by created_at descending
            usort($backups, function ($a, $b) {
                $aTime = \is_string($a['created_at'] ?? null) ? $a['created_at'] : '';
                $bTime = \is_string($b['created_at'] ?? null) ? $b['created_at'] : '';

                return strcmp($bTime, $aTime);
            });

            // Apply pagination
            $page = \is_int($filters['page'] ?? null) ? $filters['page'] : 1;
            $perPage = \is_int($filters['per_page'] ?? null) ? $filters['per_page'] : 20;
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
     *
     * @return array<string, mixed>|null
     */
    public function getBackupDetails(string $backupId, int $userId): ?array
    {
        $backup = $this->getBackupRecord($backupId);

        if (! \is_array($backup) || ($backup['user_id'] ?? null) !== $userId) {
            return null;
        }

        return $backup;
    }

    /**
     * Delete a backup
     *
     * @return array{success: bool, errors: array<int, string>}
     */
    public function deleteBackup(string $backupId, int $userId): array
    {
        try {
            $backup = $this->getBackupRecord($backupId);

            if (! \is_array($backup)) {
                return [
                    'success' => false,
                    'errors' => ['Backup not found'],
                ];
            }

            if (($backup['user_id'] ?? null) !== $userId) {
                return [
                    'success' => false,
                    'errors' => ['Unauthorized: Backup belongs to another user'],
                ];
            }

            // Delete file
            $filePath = \is_string($backup['file_path'] ?? null) ? $backup['file_path'] : '';
            if ($filePath && Storage::disk(self::BACKUP_DISK)->exists($filePath)) {
                Storage::disk(self::BACKUP_DISK)->delete($filePath);
            }

            // Remove from cache
            Cache::forget(self::CACHE_PREFIX."backup_{$backupId}");

            /** @var array<string, array<string, mixed>> $userBackups */
            $userBackups = Cache::get(self::CACHE_PREFIX."user_backups_{$userId}", []);
            if (! \is_array($userBackups)) {
                $userBackups = [];
            }
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
     *
     * @param  array<string, mixed>  $metadata
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
        /** @var array<string, array<string, mixed>> $userBackups */
        $userBackups = Cache::get(self::CACHE_PREFIX."user_backups_{$userId}", []);
        if (! \is_array($userBackups)) {
            $userBackups = [];
        }
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
     *
     * @return array<string, mixed>|null
     */
    private function getBackupRecord(string $backupId): ?array
    {
        /** @var array<string, mixed>|null $record */
        $record = Cache::get(self::CACHE_PREFIX."backup_{$backupId}");

        return \is_array($record) ? $record : null;
    }

    /**
     * Update backup status
     */
    private function updateBackupStatus(string $backupId, string $status): void
    {
        /** @var array<string, mixed> $record */
        $record = Cache::get(self::CACHE_PREFIX."backup_{$backupId}", []);
        if (! \is_array($record)) {
            $record = [];
        }
        $record['status'] = $status;
        $record['updated_at'] = now()->toIso8601String();
        Cache::put(self::CACHE_PREFIX."backup_{$backupId}", $record, now()->addDays(self::MAX_RETENTION_DAYS));
    }

    /**
     * Clean up old backups based on retention policy
     *
     * @return array{success: bool, deleted: int, errors: array<int, string>}
     */
    public function cleanupOldBackups(int $userId, int $retentionDays = self::MAX_RETENTION_DAYS): array
    {
        $deleted = 0;
        $errors = [];

        try {
            /** @var array<string, array<string, mixed>> $userBackups */
            $userBackups = Cache::get(self::CACHE_PREFIX."user_backups_{$userId}", []);
            if (! \is_array($userBackups)) {
                $userBackups = [];
            }
            $cutoffDate = now()->subDays($retentionDays)->toIso8601String();

            foreach ($userBackups as $backupId => $backup) {
                if (! \is_array($backup)) {
                    continue;
                }
                if (($backup['created_at'] ?? '') < $cutoffDate) {
                    $result = $this->deleteBackup((is_string($backupId) ? (string) $backupId : ''), $userId);
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
     *
     * @return array{total_backups: int, total_size: int, total_size_formatted: string, by_type: array<string, int>, by_status: array<string, int>, schedules: int}
     */
    public function getBackupStatistics(int $userId): array
    {
        /** @var array<string, array<string, mixed>> $userBackups */
        $userBackups = Cache::get(self::CACHE_PREFIX."user_backups_{$userId}", []);
        if (! \is_array($userBackups)) {
            $userBackups = [];
        }

        $totalSize = 0;
        /** @var array<string, int> $byType */
        $byType = [];
        /** @var array<string, int> $byStatus */
        $byStatus = [];

        foreach ($userBackups as $backup) {
            if (! \is_array($backup)) {
                continue;
            }
            $fileSize = $backup['file_size'] ?? 0;
            $totalSize += \is_int($fileSize) ? $fileSize : 0;

            $type = $backup['type'] ?? 'unknown';
            $typeStr = \is_string($type) ? $type : 'unknown';

            $status = $backup['status'] ?? 'unknown';
            $statusStr = \is_string($status) ? $status : 'unknown';

            $byType[$typeStr] = ($byType[$typeStr] ?? 0) + 1;
            $byStatus[$statusStr] = ($byStatus[$statusStr] ?? 0) + 1;
        }

        /** @var array<string, array<string, mixed>> $userSchedules */
        $userSchedules = Cache::get(self::CACHE_PREFIX."user_schedules_{$userId}", []);
        if (! \is_array($userSchedules)) {
            $userSchedules = [];
        }

        return [
            'total_backups' => count($userBackups),
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'by_type' => $byType,
            'by_status' => $byStatus,
            'schedules' => count($userSchedules),
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
