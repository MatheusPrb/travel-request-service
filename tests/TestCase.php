<?php

namespace Tests;

use App\DTO\TravelOrderDTO;
use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;
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
        
        Auth::guard('api')->setUser($user);
        
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

    protected function createAdminUserWithToken(): array
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin->fresh());

        return [
            'user' => $admin,
            'token' => $token,
        ];
    }

    public function makeDTO(TravelOrder $order): TravelOrderDTO
    {
        return new TravelOrderDTO(
            id: $order->id,
            status: $order->status,
            userId: $order->user_id,
            destination: $order->destination,
            departureDate: (string) $order->departure_date,
            returnDate: (string) $order->return_date,
            userEmail: $order->user->email ?? null,
            userName: $order->user->name ?? null,
            createdAt: (string) $order->created_at,
            updatedAt: (string) $order->updated_at,
        );
    }
}
