<?php

return [
    /*
    | Path to the Firebase service-account JSON downloaded from Firebase Console.
    | Keep this file outside git (e.g. storage/app/firebase/service-account.json).
    */
    'credentials' => env('FIREBASE_CREDENTIALS'),

    'project_id' => env('FIREBASE_PROJECT_ID'),

    /*
    | When false / credentials missing, token verification is skipped in local
    | only if FIREBASE_AUTH_BYPASS=true (never enable in production).
    */
    'auth_bypass' => (bool) env('FIREBASE_AUTH_BYPASS', false),
];
