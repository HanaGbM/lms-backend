<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $response = $next($request);

        if ($response->isSuccessful()) {
            return self::sendSuccess($request, $response);
        }

        return self::sendError($response);
    }

    private static function sendSuccess($request, $response)
    {
        return response()->json([
            'success' => true,
            'message' =>  $response->statusText(),
            'status' => $response->status() ?? 200,
            'data' => $response->original ? $response->original : [],
        ], $response->status() ?? 200);
    }

    private static function sendError($response)
    {
        $original = is_array($response->original ?? null) ? $response->original : [];
        $message = $original['message'] ?? $response->statusText();
        $errors = $original['errors'] ?? [];

        return response()->json([
            'success' => false,
            'message' => $message,
            'status' => $response->status() ?? 500,
            'data' => [
                'message' => $message,
                'errors' => $errors,
            ],
        ], $response->status() ?? 500);
    }
}
