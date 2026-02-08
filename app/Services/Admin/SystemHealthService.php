<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SystemHealthService
{
    /**
     * Get system health status.
     *
     * @return array{database: array<string, mixed>, cache: array<string, mixed>, redis: array<string, mixed>, storage: array<string, mixed>, queue: array<string, mixed>}
     */
    public function getSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];
    }

    /**
     * Check database connection.
     *
     * @return array<string, mixed>
     */
    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();
            $database = DB::connection()->getDatabaseName();

            return [
                'status' => 'healthy',
                'driver' => $driver,
                'database' => $database,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache connection.
     *
     * @return array<string, mixed>
     */
    protected function checkCache(): array
    {
        try {
            $key = 'health_check_'.time();
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $value === 'test' ? 'healthy' : 'error',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check Redis connection.
     *
     * @return array<string, mixed>
     */
    protected function checkRedis(): array
    {
        try {
            if (config('database.redis.client') === null) {
                return [
                    'status' => 'not_configured',
                ];
            }

            Redis::ping();

            return [
                'status' => 'healthy',
                'host' => config('database.redis.default.host'),
                'port' => config('database.redis.default.port'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage disk space.
     *
     * @return array<string, mixed>
     */
    protected function checkStorage(): array
    {
        $storagePath = storage_path();
        $totalSpace = disk_total_space($storagePath);
        $freeSpace = disk_free_space($storagePath);

        if ($totalSpace === false || $freeSpace === false) {
            return [
                'status' => 'error',
                'message' => 'Unable to read disk space',
            ];
        }

        $usedSpace = $totalSpace - $freeSpace;
        $usedPercentage = ($usedSpace / $totalSpace) * 100;

        return [
            'status' => $usedPercentage > 90 ? 'warning' : 'healthy',
            'total' => $this->formatBytes((int) $totalSpace),
            'used' => $this->formatBytes((int) $usedSpace),
            'free' => $this->formatBytes((int) $freeSpace),
            'used_percentage' => round($usedPercentage, 2),
        ];
    }

    /**
     * Check queue status.
     *
     * @return array<string, mixed>
     */
    protected function checkQueue(): array
    {
        try {
            $driver = config('queue.default');
            $failedJobs = DB::table('failed_jobs')->count();

            return [
                'status' => $failedJobs > 100 ? 'warning' : 'healthy',
                'driver' => $driver,
                'failed_jobs' => $failedJobs,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get environment information.
     *
     * @return array<string, mixed>
     */
    public function getEnvironmentInfo(): array
    {
        return [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'app_url' => config('app.url'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timezone' => config('app.timezone'),
        ];
    }

    /**
     * Format bytes to human-readable size.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
