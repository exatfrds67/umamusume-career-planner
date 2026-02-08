<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LogReaderService
{
    /**
     * Get log entries from the Laravel log file.
     *
     * @return array<int, array{timestamp: string, level: string, message: string, context: string}>
     */
    public function getLogEntries(int $lines = 100, ?string $level = null): array
    {
        $logPath = storage_path('logs/laravel.log');

        if (! File::exists($logPath)) {
            return [];
        }

        // Read only the last portion of the file to avoid memory issues
        $fileSize = File::size($logPath);
        $maxBytes = 1024 * 1024; // 1MB max

        if ($fileSize > $maxBytes) {
            $handle = fopen($logPath, 'r');
            if ($handle === false) {
                return [];
            }
            fseek($handle, -$maxBytes, SEEK_END);
            $content = fread($handle, $maxBytes);
            fclose($handle);

            if ($content === false) {
                return [];
            }
        } else {
            $content = File::get($logPath);
        }

        $entries = $this->parseLogEntries($content);

        if ($level) {
            $entries = array_filter($entries, fn ($entry) => $entry['level'] === strtoupper($level));
        }

        return array_slice(array_reverse($entries), 0, $lines);
    }

    /**
     * Parse log file content into structured entries.
     *
     * @return array<int, array{timestamp: string, level: string, message: string, context: string}>
     */
    protected function parseLogEntries(string $content): array
    {
        $entries = [];
        $lines = explode("\n", $content);
        $currentEntry = null;

        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(\w+): (.+)$/', $line, $matches)) {
                if ($currentEntry) {
                    $entries[] = $currentEntry;
                }

                $currentEntry = [
                    'timestamp' => $matches[1],
                    'level' => strtoupper($matches[2]),
                    'message' => $matches[3],
                    'context' => '',
                ];
            } elseif ($currentEntry && trim($line)) {
                $currentEntry['context'] .= $line."\n";
            }
        }

        if ($currentEntry) {
            $entries[] = $currentEntry;
        }

        return $entries;
    }

    /**
     * Search log entries by query.
     *
     * @return array<int, array{timestamp: string, level: string, message: string, context: string}>
     */
    public function searchLogs(string $query, int $lines = 100): array
    {
        $entries = $this->getLogEntries($lines * 2);

        return array_filter($entries, function ($entry) use ($query) {
            return Str::contains($entry['message'], $query, true) ||
                Str::contains($entry['context'], $query, true);
        });
    }

    /**
     * Clear old log files.
     */
    public function clearOldLogs(int $daysToKeep = 7): int
    {
        $logPath = storage_path('logs');
        $files = File::files($logPath);
        $deleted = 0;

        foreach ($files as $file) {
            if ($file->getExtension() === 'log' && $file->getFilename() !== 'laravel.log') {
                $mtime = $file->getMTime();
                // Convert timestamp to Carbon instance for diffInDays
                $age = is_int($mtime) ? now()->diffInDays(\Carbon\Carbon::createFromTimestamp($mtime)) : 0;

                if ($age > $daysToKeep) {
                    File::delete($file->getPathname());
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Get log file size.
     */
    public function getLogFileSize(): string
    {
        $logPath = storage_path('logs/laravel.log');

        if (! File::exists($logPath)) {
            return '0 B';
        }

        $bytes = File::size($logPath);

        return $this->formatBytes($bytes);
    }

    /**
     * Format bytes to human-readable size.
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
