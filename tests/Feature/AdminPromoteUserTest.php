<?php

namespace Tests\Feature;

use App\Constants\Messages;
use App\Models\User;
use Tests\TestCase;

class AdminPromoteUserTest extends TestCase
{
    public function test_admin_can_promote_user_to_admin(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin);

        $userToPromote = User::factory()->create();
        $this->assertFalse($userToPromote->isAdmin());

        $response = $this->postJson(
            '/api/users/promote-to-admin',
            ['user_id' => $userToPromote->id],
            $this->getAuthHeaders($token)
        );

        $response
            ->assertStatus(200)
            ->assertJson(['message' => Messages::USER_PROMOTED_TO_ADMIN])
        ;

        $this->assertTrue($userToPromote->fresh()->isAdmin());
        $this->assertDatabaseHas('users', [
            'id' => $userToPromote->id,
            'is_admin' => true,
        ]);
    }

    public function test_admin_cannot_promote_nonexistent_user(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin);

        $fakeUserId = '00000000-0000-0000-0000-000000000000';

        $response = $this->postJson(
            '/api/users/promote-to-admin',
            ['user_id' => $fakeUserId],
            $this->getAuthHeaders($token)
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);
    }

    public function test_admin_cannot_promote_user_with_invalid_uuid(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin);

        $response = $this->postJson(
            '/api/users/promote-to-admin',
            ['user_id' => 'invalid-uuid'],
            $this->getAuthHeaders($token)
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);
    }

    public function test_promote_user_returns_message_when_user_is_already_admin(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin);

        $alreadyAdmin = User::factory()->create();
        $alreadyAdmin->makeAdmin();

        $response = $this->postJson(
            '/api/users/promote-to-admin',
            ['user_id' => $alreadyAdmin->id],
            $this->getAuthHeaders($token)
        );

        $response
            ->assertStatus(200)
            ->assertJson(['message' => Messages::USER_ALREADY_ADMIN])
        ;

        $this->assertTrue($alreadyAdmin->fresh()->isAdmin());
    }

    public function test_promote_user_requires_user_id(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin);

        $response = $this->postJson(
            '/api/users/promote-to-admin',
            [],
            $this->getAuthHeaders($token)
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id']);
    }

    public function test_non_admin_cannot_promote_user(): void
    {
        $regularUser = User::factory()->create();
        $token = $this->actingAsWithJwt($regularUser);

        $userToPromote = User::factory()->create();

        $response = $this->postJson(
            '/api/users/promote-to-admin',
            ['user_id' => $userToPromote->id],
            $this->getAuthHeaders($token)
        );

        $response->assertStatus(403);
        $this->assertFalse($userToPromote->fresh()->isAdmin());
    }

    public function test_unauthenticated_user_cannot_promote_user(): void
    {
        $userToPromote = User::factory()->create();

        $response = $this->postJson('/api/users/promote-to-admin', [
            'user_id' => $userToPromote->id,
        ]);

        $response->assertStatus(401);
        $this->assertFalse($userToPromote->fresh()->isAdmin());
    }

    public function test_admin_cannot_promote_themselves(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin);

        $response = $this->postJson(
            '/api/users/promote-to-admin',
            ['user_id' => $admin->id],
            $this->getAuthHeaders($token)
        );

        $response
            ->assertStatus(200)
            ->assertJson(['message' => Messages::USER_ALREADY_ADMIN])
        ;
    }
}
