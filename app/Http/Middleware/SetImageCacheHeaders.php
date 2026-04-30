<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SetImageCacheHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Set aggressive caching for trainee images (immutable, 1 year)
        // Images are immutable: filename includes version/ID, never changes
        if (Str::startsWith($request->path(), 'images/trainee_images')) {
            $response->header('Cache-Control', 'public, max-age=31536000, immutable');
            // Browser caches locally; server responds 304 Not Modified on revalidation
        }

        return $response;
    }
}
