<?php

namespace App\Services\Firebase;

use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;
use RuntimeException;

final class FirebaseAuthService
{
    private ?FirebaseAuth $auth = null;

    public function credentialsPath(): ?string
    {
        $candidates = array_filter([
            config('firebase.credentials'),
            storage_path('app/firebase/service-account.json'),
        ], fn ($path): bool => is_string($path) && $path !== '');

        foreach ($candidates as $path) {
            if (! str_starts_with($path, DIRECTORY_SEPARATOR)
                && ! preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
                $path = base_path($path);
            }

            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    public function configured(): bool
    {
        return $this->credentialsPath() !== null;
    }

    public function auth(): FirebaseAuth
    {
        if ($this->auth instanceof FirebaseAuth) {
            return $this->auth;
        }

        if (! $this->configured()) {
            throw new RuntimeException('Firebase credentials are not configured.');
        }

        $factory = (new Factory)->withServiceAccount($this->credentialsPath());

        if ($projectId = config('firebase.project_id')) {
            $factory = $factory->withProjectId($projectId);
        }

        $this->auth = $factory->createAuth();

        return $this->auth;
    }

    /**
     * @return array{uid: string, email: ?string, phone: ?string, claims: array<string, mixed>}
     */
    public function verifyIdToken(string $idToken): array
    {
        if (! $this->configured()) {
            if (config('firebase.auth_bypass') && app()->environment(['local', 'testing'])) {
                return [
                    'uid' => 'local-bypass-uid',
                    'email' => 'bypass@afyamed.local',
                    'phone' => null,
                    'claims' => ['bypass' => true],
                ];
            }

            throw new RuntimeException('Firebase credentials are not configured.');
        }

        try {
            $verified = $this->auth()->verifyIdToken($idToken);
        } catch (FailedToVerifyToken $e) {
            throw new RuntimeException('Invalid Firebase ID token.', 0, $e);
        }

        $claims = $verified->claims();

        return [
            'uid' => (string) $claims->get('sub'),
            'email' => $claims->get('email'),
            'phone' => $claims->get('phone_number'),
            'claims' => $claims->all(),
        ];
    }
}
