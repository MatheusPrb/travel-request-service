<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthenticationTest extends TestCase
{
    public function test_token_expiration_returns_401(): void
    {
        $expiredToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImlhdCI6MTYwOTQ1OTIwMCwiZXhwIjoxNjA5NDU5MjAwLCJzdWIiOiIxIn0.invalid_signature';

        $response = $this->getJson('/api/me', $this->getAuthHeaders($expiredToken));

        $response->assertStatus(401);
    }

    public function test_invalid_token_returns_401(): void
    {
        $invalidToken = 'invalid.token.here';

        $response = $this->getJson('/api/me', $this->getAuthHeaders($invalidToken));

        $response->assertStatus(401);
    }

    public function test_missing_token_returns_401(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_malformed_token_returns_401(): void
    {
        $malformedToken = 'not.a.valid.jwt.token.structure';

        $response = $this->getJson('/api/me', $this->getAuthHeaders($malformedToken));

        $response->assertStatus(401);
    }

    public function test_valid_token_allows_access(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        $response = $this->getJson('/api/me', $this->getAuthHeaders($authenticated['token']));

        $response
            ->assertStatus(200)
            ->assertJson(['id' => $authenticated['user']->id])
        ;
    }

    public function test_token_without_bearer_prefix_returns_401(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        $response = $this->getJson('/api/me', [
            'Authorization' => $authenticated['token'],
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401);
    }
}
