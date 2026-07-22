<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $identity = $request->attributes->get('firebase_user', []);

        return ApiResponse::success([
            'firebase_uid' => $request->attributes->get('firebase_uid'),
            'email' => $identity['email'] ?? null,
            'phone' => $identity['phone'] ?? null,
            'note' => 'Profile and role documents live in Firestore; Laravel only verified the ID token.',
        ]);
    }
}
