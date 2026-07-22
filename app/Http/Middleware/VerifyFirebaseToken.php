<?php

namespace App\Http\Middleware;

use App\Services\Firebase\FirebaseAuthService;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class VerifyFirebaseToken
{
    public function __construct(private readonly FirebaseAuthService $firebase) {}

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->bearerToken();
        if ($header === null || $header === '') {
            return ApiResponse::error('Missing Firebase ID token.', 401);
        }

        try {
            $identity = $this->firebase->verifyIdToken($header);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 401);
        }

        $request->attributes->set('firebase_uid', $identity['uid']);
        $request->attributes->set('firebase_user', $identity);

        return $next($request);
    }
}
