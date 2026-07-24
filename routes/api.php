<?php

use App\Http\Controllers\Api\V1\DoctorController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProfileFileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class);

    Route::get('/documents/{ulid}/stream', [ProfileFileController::class, 'stream'])
        ->name('api.v1.documents.stream');

    Route::get('/specialities', [DoctorController::class, 'specialities']);
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/{id}', [DoctorController::class, 'show']);
    Route::get('/doctors/{id}/slots', [DoctorController::class, 'slots']);

    Route::middleware('firebase.auth')->group(function (): void {
        Route::get('/me', MeController::class);

        Route::post('/profile/avatar', [ProfileFileController::class, 'uploadAvatar']);
        Route::post('/profile/verification-documents', [ProfileFileController::class, 'uploadVerificationDocument']);
        Route::get('/documents/{ulid}/url', [ProfileFileController::class, 'temporaryDocumentUrl']);

        Route::post('/devices/register', [NotificationController::class, 'registerDevice']);
        Route::delete('/devices/{id}', [NotificationController::class, 'unregisterDevice']);
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::patch('/notifications/{ulid}/read', [NotificationController::class, 'markRead']);
        Route::get('/notification-preferences', [NotificationController::class, 'preferences']);
        Route::put('/notification-preferences', [NotificationController::class, 'updatePreferences']);

        Route::get('/doctor/availability', [DoctorController::class, 'listAvailability']);
        Route::post('/doctor/availability', [DoctorController::class, 'storeAvailability']);
        Route::put('/doctor/availability/{id}', [DoctorController::class, 'updateAvailability']);
        Route::delete('/doctor/availability/{id}', [DoctorController::class, 'destroyAvailability']);
        Route::put('/doctor/profile-public', [DoctorController::class, 'updatePublicProfile']);
    });
});
