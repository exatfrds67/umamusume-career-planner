<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\StorageMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detect and set the current storage mode on each request.
 *
 * The storage mode is determined by:
 * 1. Session value (if previously set)
 * 2. Route prefix (/local/* = local mode)
 * 3. Authentication status (authenticated = account, guest = local)
 *
 * The resolved mode is shared with all views via View::share().
 */
class DetectStorageMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the route is explicitly a local-mode route
        if ($request->is('local/*') || $request->is('local')) {
            $mode = StorageMode::LOCAL;
        } else {
            $mode = StorageMode::fromRequest($request);
        }

        // Store in session for subsequent requests
        if ($request->hasSession()) {
            $request->session()->put('storage_mode', $mode->value);
        }

        // Share with all views
        view()->share('storageMode', $mode);

        return $next($request);
    }
}
