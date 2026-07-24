<?php

namespace App\Http\Middleware;

use App\Services\Firebase\FirebaseAuthService;
use App\Services\Firebase\FirestoreService;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class VerifyFirebaseToken
{
    public function __construct(
        private readonly FirebaseAuthService $firebase,
        private readonly FirestoreService $firestore,
    ) {}

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

        $uid = $identity['uid'];
        $request->attributes->set('firebase_uid', $uid);
        $request->attributes->set('firebase_user', $identity);

        $appUser = null;
        if ($this->firestore->configured()) {
            try {
                $appUser = $this->firestore->appUserSummary($uid);
            } catch (Throwable) {
                $appUser = null;
            }
        }

        $request->attributes->set('app_user', $appUser);

        if (is_array($appUser) && ($appUser['status'] ?? 'active') === 'suspended') {
            return ApiResponse::error('Account suspended.', 403);
        }

        return $next($request);
    }
}
