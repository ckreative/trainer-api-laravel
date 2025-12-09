<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateAppKey
{
    /**
     * Handle an incoming request.
     *
     * Validates the X-App-Key header against the configured booking app key.
     * This middleware protects public endpoints from unauthorized access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appKey = $request->header('X-App-Key');
        $configuredKey = config('services.booking_app.key');

        if (empty($configuredKey)) {
            return response()->json([
                'error' => 'App key not configured',
                'message' => 'Server configuration error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($appKey !== $configuredKey) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing app key',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
