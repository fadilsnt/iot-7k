<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIotApiKey
{
    /**
     * Ensure IoT requests carry the configured API key.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('services.iot.api_key');

        // Allow requests to pass when no key is configured.
        if ($configuredKey === '') {
            return $next($request);
        }

        $providedKey = (string) ($request->header('X-IOT-KEY') ?? $request->query('api_key'));

        if ($providedKey === '' || !hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'message' => 'Invalid or missing IoT API key.',
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
