<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    public function test_user_can_register_successfully(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $data);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ])
        ;

        $this->assertDatabaseHas('users', ['email' => 'joao@example.com']);
    }

    public function test_register_returns_validation_error_for_invalid_email(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $data);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
        ;
    }

    public function test_register_returns_validation_error_for_duplicate_email(): void
    {
        User::factory()->create(['email' => 'joao@example.com']);

        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $data);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
        ;
    }

    public function test_register_returns_validation_error_for_weak_password(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => '12345',
            'password_confirmation' => '12345',
        ];

        $response = $this->postJson('/api/register', $data);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password'])
        ;
    }

    public function test_register_returns_validation_error_for_missing_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password'])
        ;
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'joao@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'joao@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['token'])
        ;
    }

    public function test_login_returns_error_for_invalid_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
        ;
    }

    public function test_login_returns_error_for_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'joao@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'joao@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(401)
            ->assertJson(['error' => 'Credenciais inválidas'])
        ;
    }

    public function test_login_returns_token_in_response(): void
    {
        $user = User::factory()->create([
            'email' => 'joao@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response->json());
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_returns_401_for_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(401)
            ->assertJson(['error' => 'Credenciais inválidas'])
        ;
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        $response = $this->getJson('/api/me', $this->getAuthHeaders($authenticated['token']));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ])
            ->assertJson([
                'id' => $authenticated['user']->id,
                'email' => $authenticated['user']->email,
            ])
        ;
    }

    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        $response = $this->postJson(
            '/api/logout',
            [],
            $this->getAuthHeaders($authenticated['token'])
        );

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Logout realizado com sucesso'])
        ;
    }

    public function test_logout_invalidates_token(): void
    {
        $authenticated = $this->createAuthenticatedUser();
        $token = $authenticated['token'];

        $logoutResponse = $this->postJson('/api/logout', [], $this->getAuthHeaders($token));
        $logoutResponse->assertStatus(200);

        $this->expectException(TokenBlacklistedException::class);
        JWTAuth::setToken($token)->authenticate();
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    public function test_user_can_refresh_token(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        $response = $this->postJson(
            '/api/refresh',
            [],
            $this->getAuthHeaders($authenticated['token'])
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['token'])
        ;
    }

    public function test_refresh_returns_new_token(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        $response = $this->postJson(
            '/api/refresh',
            [],
            $this->getAuthHeaders($authenticated['token'])
        );

        $newToken = $response->json('token');
        $this->assertNotEquals($authenticated['token'], $newToken);
        $this->assertNotEmpty($newToken);
    }

    public function test_unauthenticated_user_cannot_refresh(): void
    {
        $response = $this->postJson('/api/refresh');

        $response->assertStatus(401);
    }
}
