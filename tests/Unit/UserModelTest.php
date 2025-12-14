<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_user_is_not_admin_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_user_can_be_made_admin(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->fresh()->isAdmin());

        $result = $user->makeAdmin();

        $this->assertTrue($result);
        $this->assertTrue($user->fresh()->isAdmin());
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_admin' => true,
        ]);
    }

    public function test_user_admin_status_can_be_removed(): void
    {
        $user = User::factory()->create();
        $user->makeAdmin();

        $this->assertTrue($user->fresh()->isAdmin());

        $result = $user->removeAdmin();

        $this->assertTrue($result);
        $this->assertFalse($user->fresh()->isAdmin());
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_admin' => false,
        ]);
    }

    public function test_is_admin_returns_false_when_is_admin_is_false(): void
    {
        $user = User::factory()->create();
        $user->update(['is_admin' => false]);

        $this->assertFalse($user->fresh()->isAdmin());
    }

    public function test_is_admin_returns_false_when_is_admin_is_zero(): void
    {
        $user = User::factory()->create(['is_admin' => 0]);

        $this->assertFalse($user->isAdmin());
    }

    public function test_is_admin_returns_true_when_is_admin_is_one(): void
    {
        $user = User::factory()->create(['is_admin' => 1]);

        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_field_is_not_fillable(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'is_admin' => true,
        ]);

        $this->assertFalse($user->isAdmin());
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_admin' => false,
        ]);
    }
}
