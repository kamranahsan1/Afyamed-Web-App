<?php

namespace App\Services\Firebase;

use Kreait\Firebase\Factory;
use RuntimeException;

final class FirebaseFactory
{
    private ?Factory $factory = null;

    public function __construct(private readonly FirebaseAuthService $authService) {}

    public function factory(): Factory
    {
        if ($this->factory instanceof Factory) {
            return $this->factory;
        }

        if (! $this->authService->configured()) {
            throw new RuntimeException('Firebase credentials are not configured.');
        }

        $factory = (new Factory)->withServiceAccount($this->authService->credentialsPath());

        if ($projectId = $this->authService->projectId()) {
            $factory = $factory->withProjectId($projectId);
        }

        // Hostinger / shared hosts often lack ext-grpc. REST transport works over HTTPS.
        $factory = $factory->withFirestoreClientConfig([
            'transport' => 'rest',
        ]);

        return $this->factory = $factory;
    }
}
