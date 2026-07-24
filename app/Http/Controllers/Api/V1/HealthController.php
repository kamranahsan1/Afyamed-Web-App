<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $dbOk = false;
        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Throwable) {
            $dbOk = false;
        }

        return ApiResponse::success([
            'service' => 'afyamed-api',
            'stack' => [
                'app_data' => 'firebase_firestore',
                'app_auth' => 'firebase_auth',
                'admin_db' => 'mysql',
                'private_files' => 'hostinger_storage_app_private',
            ],
            'checks' => [
                'mysql' => $dbOk,
                'medical_disk' => Storage::disk('medical')->exists('.gitkeep')
                    || is_dir(storage_path('app/private/prescriptions')),
                'firebase_credentials' => app(\App\Services\Firebase\FirebaseAuthService::class)->configured(),
                'firestore' => app(\App\Services\Firebase\FirestoreService::class)->configured(),
            ],
        ]);
    }
}
