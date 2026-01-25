<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\OCRExtraction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Cleanup OCR Files Command
 *
 * Removes old OCR extraction files and temporary processed images.
 * Runs automatically via scheduler to maintain storage hygiene.
 *
 * Requirements: Task 5.1.2, Requirement 23.2
 */
class CleanupOCRFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:cleanup
                            {--days=7 : Number of days to keep files}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old OCR extraction files and temporary images';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Starting OCR file cleanup (files older than {$days} days)...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be deleted');
        }

        $deletedCount = 0;
        $errorCount = 0;
        $totalSize = 0;

        // Find old extractions
        $cutoffDate = now()->subDays($days);
        $oldExtractions = OCRExtraction::where('created_at', '<', $cutoffDate)
            ->whereIn('status', ['processed', 'failed'])
            ->get();

        $this->info("Found {$oldExtractions->count()} old extraction records");

        foreach ($oldExtractions as $extraction) {
            try {
                // Skip if image_path is empty
                if (empty($extraction->image_path)) {
                    if (! $dryRun) {
                        $extraction->delete();
                    }

                    continue;
                }

                // Get file size before deletion
                if (Storage::disk('local')->exists($extraction->image_path)) {
                    $fileSize = Storage::disk('local')->size($extraction->image_path);
                    $totalSize += $fileSize;

                    if (! $dryRun) {
                        // Delete the file
                        Storage::disk('local')->delete($extraction->image_path);

                        // Delete processed file if it exists
                        $processedPath = $this->getProcessedPath($extraction->image_path);
                        if (Storage::disk('local')->exists($processedPath)) {
                            Storage::disk('local')->delete($processedPath);
                        }

                        // Delete the extraction record
                        $extraction->delete();
                    }

                    $deletedCount++;
                } else {
                    // File doesn't exist, just delete the record
                    if (! $dryRun) {
                        $extraction->delete();
                    }
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('[CleanupOCRFiles] Failed to delete extraction', [
                    'extraction_id' => $extraction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Clean up orphaned files
        $orphanedCount = $this->cleanupOrphanedFiles($dryRun);

        // Display results
        $this->newLine();
        $this->info('Cleanup Summary:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Extraction records processed', $oldExtractions->count()],
                ['Files deleted', $deletedCount],
                ['Orphaned files cleaned', $orphanedCount],
                ['Errors encountered', $errorCount],
                ['Space freed', $this->formatBytes($totalSize)],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY RUN - No actual deletions were performed');
        }

        Log::info('[CleanupOCRFiles] Cleanup completed', [
            'deleted_count' => $deletedCount,
            'orphaned_count' => $orphanedCount,
            'error_count' => $errorCount,
            'space_freed' => $totalSize,
            'dry_run' => $dryRun,
        ]);

        return $errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Clean up orphaned files that don't have database records
     */
    protected function cleanupOrphanedFiles(bool $dryRun): int
    {
        $orphanedCount = 0;

        try {
            $files = Storage::disk('local')->files('ocr-uploads');

            foreach ($files as $file) {
                // Check if file has a corresponding database record
                $exists = OCRExtraction::where('image_path', $file)->exists();

                if (! $exists) {
                    if (! $dryRun) {
                        Storage::disk('local')->delete($file);
                    }
                    $orphanedCount++;
                }
            }
        } catch (\Exception $e) {
            Log::error('[CleanupOCRFiles] Failed to clean orphaned files', [
                'error' => $e->getMessage(),
            ]);
        }

        return $orphanedCount;
    }

    /**
     * Get the processed file path from original path
     */
    protected function getProcessedPath(string $originalPath): string
    {
        $directory = pathinfo($originalPath, PATHINFO_DIRNAME);
        $filename = pathinfo($originalPath, PATHINFO_FILENAME);
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);

        $suffix = $extension !== '' ? '.'.$extension : '';

        return rtrim($directory, '/').'/'.$filename.'_processed'.$suffix;
    }

    /**
     * Format bytes to human-readable size
     */
    protected function formatBytes(int $bytes): string
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
