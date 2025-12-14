<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    /**
     * Authenticate a user and return the JWT token.
     *
     * @param User|null $user
     * @return string
     */
    protected function actingAsWithJwt(?User $user = null): string
    {
        $user = $user ?? User::factory()->create();
        return JWTAuth::fromUser($user);
    }

    /**
     * Create an authenticated user and return user with token.
     *
     * @param array $attributes
     * @return array{user: User, token: string}
     */
    protected function createAuthenticatedUser(array $attributes = []): array
    {
        $user = User::factory()->create($attributes);
        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Get authorization header with JWT token.
     *
     * @param string $token
     * @return array
     */
    protected function getAuthHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Get authorization headers for an authenticated user.
     *
     * @param array $attributes
     * @return array
     */
    protected function getAuthenticatedHeaders(array $attributes = []): array
    {
        $authenticated = $this->createAuthenticatedUser($attributes);

        return $this->getAuthHeaders($authenticated['token']);
    }
}
