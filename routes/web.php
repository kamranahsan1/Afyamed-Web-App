<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/refresh', function (Request $request) {

    $secret = env('DEPLOY_WEBHOOK_SECRET');

    if (!$secret || $request->header('X-Deploy-Secret') !== $secret) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }

    sleep(5);

    try {
        Artisan::call('app:deploy');

        return response()->json([
            'success' => true,
            'message' => 'Deployment completed successfully.',
            'output' => Artisan::output(),
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'success' => false,
            'message' => 'Deployment failed.',
            'error' => $e->getMessage(),
        ], 500);
    }
});
