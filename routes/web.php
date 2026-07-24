<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/refresh', function (Request $request) {
    // This route is intended to be used as a GitHub webhook for automatic deployment.

    $secret = config('services.github.webhook_secret');

    if (!$secret) {
        return response()->json([
            'success' => false,
            'message' => 'Webhook secret is not configured.',
        ], 500);
    }

    // Verify request is genuinely from GitHub
    $signature = $request->header('X-Hub-Signature-256');

    $expectedSignature = 'sha256=' . hash_hmac(
        'sha256',
        $request->getContent(),
        $secret
    );

    if (
        !$signature ||
        !hash_equals($expectedSignature, $signature)
    ) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid GitHub signature.',
        ], 401);
    }

    // Only process GitHub push events
    if ($request->header('X-GitHub-Event') !== 'push') {
        return response()->json([
            'success' => true,
            'message' => 'Event ignored.',
        ]);
    }

    // Only run deployment for main branch
    if ($request->input('ref') !== 'refs/heads/main') {
        return response()->json([
            'success' => true,
            'message' => 'Branch ignored.',
        ]);
    }

    try {
        // Delay execution to ensure that the repository is fully updated before deployment
        sleep(3);

        set_time_limit(300);

        Artisan::call('app:deploy');

        return response()->json([
            'success' => true,
            'message' => 'Deployment completed.',
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
