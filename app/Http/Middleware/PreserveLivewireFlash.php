<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Preserves session flash messages across Livewire AJAX requests.
 *
 * When a Livewire component fires an update request (e.g., closeDropdown),
 * the session's StartSession middleware moves flash keys from `_flash.new`
 * to `_flash.old` and then clears them — consuming the flash before the
 * redirected GET request has a chance to render it.
 *
 * This middleware detects Livewire AJAX requests and calls reflash() to
 * re-queue any existing flash messages so they survive to the next request.
 */
class PreserveLivewireFlash
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->hasHeader('X-Livewire')) {
            session()->reflash();
        }

        return $response;
    }
}
