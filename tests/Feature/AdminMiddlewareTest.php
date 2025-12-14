<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    public function test_admin_can_access_protected_route(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin->fresh());

        $response = $this->postJson(
            '/api/users/promote-to-admin',
            ['user_id' => User::factory()->create()->id],
            $this->getAuthHeaders($token)
        );

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_non_admin_cannot_access_protected_route(): void
    {
        $user = User::factory()->create();
        $token = $this->actingAsWithJwt($user);

        $response = $this->postJson(
            '/api/users/promote-to-admin',
            ['user_id' => User::factory()->create()->id],
            $this->getAuthHeaders($token)
        );

        $response
            ->assertStatus(403)
            ->assertJson(['error' => 'Acesso negado. Apenas administradores podem realizar esta ação.'])
        ;
    }

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $response = $this->postJson('/api/users/promote-to-admin', [
            'user_id' => User::factory()->create()->id,
        ]);

        $response->assertStatus(401);
    }
}
