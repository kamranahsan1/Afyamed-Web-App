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
        $candidates = [
            config('firebase.credentials'),
            storage_path('app'.DIRECTORY_SEPARATOR.'firebase'.DIRECTORY_SEPARATOR.'service-account.json'),
            base_path('storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'firebase'.DIRECTORY_SEPARATOR.'service-account.json'),
        ];

        foreach ($candidates as $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }

            $normalized = $this->normalizePath($path);
            if (is_file($normalized) && is_readable($normalized)) {
                return $normalized;
            }
        }

        return null;
    }

    public function configured(): bool
    {
        $path = $this->credentialsPath();
        if ($path === null) {
            return false;
        }

        $json = json_decode((string) file_get_contents($path), true);

        return is_array($json)
            && ! empty($json['project_id'])
            && ! empty($json['client_email'])
            && ! empty($json['private_key']);
    }

    public function projectId(): ?string
    {
        $fromEnv = config('firebase.project_id');
        if (is_string($fromEnv) && $fromEnv !== '') {
            return $fromEnv;
        }

        $path = $this->credentialsPath();
        if ($path === null) {
            return null;
        }

        $json = json_decode((string) file_get_contents($path), true);

        return is_array($json) ? ($json['project_id'] ?? null) : null;
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

        if ($projectId = $this->projectId()) {
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

    private function normalizePath(string $path): string
    {
        if (! str_starts_with($path, DIRECTORY_SEPARATOR)
            && ! preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
            $path = base_path($path);
        }

        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
