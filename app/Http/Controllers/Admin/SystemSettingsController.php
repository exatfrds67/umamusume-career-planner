<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SystemHealthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    public function __construct(
        protected SystemHealthService $healthService
    ) {}

    /**
     * Display system settings and health.
     */
    public function index(): View
    {
        $health = $this->healthService->getSystemHealth();
        $environment = $this->healthService->getEnvironmentInfo();

        return view('admin.system-settings.index', compact('health', 'environment'));
    }

    /**
     * Clear application cache.
     */
    public function clearCache(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'all');
        $typeString = is_string($type) ? $type : 'all';

        match ($typeString) {
            'config' => Artisan::call('config:clear'),
            'route' => Artisan::call('route:clear'),
            'view' => Artisan::call('view:clear'),
            'cache' => Cache::flush(),
            default => $this->clearAllCaches(),
        };

        return back()->with('success', ucfirst($typeString).' cache cleared successfully.');
    }

    /**
     * Clear all caches.
     */
    protected function clearAllCaches(): void
    {
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Cache::flush();
    }

    /**
     * Optimize application.
     */
    public function optimize(): RedirectResponse
    {
        Artisan::call('optimize');

        return back()->with('success', 'Application optimized successfully.');
    }

    /**
     * Clear optimization.
     */
    public function clearOptimization(): RedirectResponse
    {
        Artisan::call('optimize:clear');

        return back()->with('success', 'Optimization cleared successfully.');
    }
}
