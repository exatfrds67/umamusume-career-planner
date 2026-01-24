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

        // Apply the limit early in the application lifecycle
        @ini_set('memory_limit', $limitToApply);
    }
}
