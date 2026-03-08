<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MemoryGuardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No bindings required
    }

    public function boot(): void
    {
        // Set a global PHP memory limit from configuration to prevent OOM
        // Determine appropriate memory limit
        $defaultLimitConfig = config('api-performance.memory.limit', '2048M');
        $testingLimitConfig = config('api-performance.memory.testing_limit', '2048M');
        $defaultLimit = is_string($defaultLimitConfig) ? $defaultLimitConfig : '2048M';
        $testingLimit = is_string($testingLimitConfig) ? $testingLimitConfig : '2048M';

        $limitToApply = $defaultLimit;

        // For CLI testing context, apply higher testing limit to avoid OOM
        if (app()->runningInConsole() && app()->environment('testing')) {
            $limitToApply = $testingLimit;
        }

        $currentLimit = ini_get('memory_limit');

        // Only raise the limit when needed. Do not clobber a higher CLI value.
        if (self::shouldApplyMemoryLimit(is_string($currentLimit) ? $currentLimit : null, $limitToApply)) {
            @ini_set('memory_limit', $limitToApply);
        }
    }

    public static function shouldApplyMemoryLimit(?string $currentLimit, string $targetLimit): bool
    {
        $targetBytes = self::memoryLimitToBytes($targetLimit);

        if ($targetBytes === null) {
            return false;
        }

        $currentBytes = self::memoryLimitToBytes($currentLimit);

        if ($currentBytes === null) {
            return false;
        }

        return $currentBytes < $targetBytes;
    }

    public static function memoryLimitToBytes(?string $limit): ?int
    {
        if (! is_string($limit)) {
            return null;
        }

        $normalizedLimit = trim($limit);

        if ($normalizedLimit === '' || $normalizedLimit === '-1') {
            return null;
        }

        if (! preg_match('/^(\d+)([KMG]?)$/i', $normalizedLimit, $matches)) {
            return null;
        }

        $value = (int) $matches[1];
        $suffix = strtoupper($matches[2]);

        return match ($suffix) {
            'G' => $value * 1024 * 1024 * 1024,
            'M' => $value * 1024 * 1024,
            'K' => $value * 1024,
            default => $value,
        };
    }
}
