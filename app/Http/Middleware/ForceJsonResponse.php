<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For POST, PUT, PATCH requests, enforce JSON content type
        // Allow multipart/form-data requests (file uploads) to pass through
        $isMultipart = str_contains($request->header('Content-Type', ''), 'multipart/form-data');

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH']) && ! $request->isJson() && ! $isMultipart) {
            return response()->json([
                'message' => 'Content-Type must be application/json',
            ], 415);
        }

        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
