<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Firebase\FirestoreService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __construct(private readonly FirestoreService $firestore) {}

    public function __invoke(Request $request): JsonResponse
    {
        $identity = $request->attributes->get('firebase_user', []);
        $uid = (string) $request->attributes->get('firebase_uid');
        $appUser = $request->attributes->get('app_user');

        if (! is_array($appUser) && $this->firestore->configured()) {
            $appUser = $this->firestore->appUserSummary($uid);
        }

        return ApiResponse::success([
            'firebase_uid' => $uid,
            'email' => $appUser['email'] ?? ($identity['email'] ?? null),
            'phone' => $appUser['phone'] ?? ($identity['phone'] ?? null),
            'app_user' => $appUser,
            'role' => $appUser['role'] ?? null,
            'status' => $appUser['status'] ?? null,
            'ulid' => $appUser['ulid'] ?? null,
            'verification_status' => $appUser['verification_status'] ?? null,
            'name' => $appUser['name'] ?? null,
            'profile' => $appUser['profile'] ?? null,
        ]);
    }
}
