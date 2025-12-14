<?php

namespace Tests\Feature;

use App\Constants\Messages;
use App\Models\TravelOrder;
use App\Models\User;
use Tests\TestCase;

class TravelOrderTest extends TestCase
{
    public function test_authenticated_user_can_create_travel_order(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        $data = [
            'destination' => 'Paris, França',
            'departure_date' => '2024-06-01',
            'return_date' => '2024-06-15',
        ];

        $response = $this->postJson(
            '/api/travel-orders',
            $data,
            $this->getAuthHeaders($authenticated['token'])
        );

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'destination',
                'departure_date',
                'return_date',
                'status',
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('travel_orders', [
            'user_id' => $authenticated['user']->id,
            'destination' => 'Paris, França',
        ]);
    }

    public function test_unauthenticated_user_cannot_create_travel_order(): void
    {
        $data = [
            'destination' => 'Paris, França',
            'departure_date' => '2024-06-01',
            'return_date' => '2024-06-15',
        ];

        $response = $this->postJson('/api/travel-orders', $data);

        $response->assertStatus(401);
    }

    public function test_create_returns_validation_error_for_missing_destination(): void
    {
        $data = [
            'departure_date' => '2024-06-01',
            'return_date' => '2024-06-15',
        ];

        $response = $this->postJson(
            '/api/travel-orders',
            $data,
            $this->getAuthenticatedHeaders()
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['destination'])
        ;
    }

    public function test_create_returns_validation_error_for_invalid_departure_date(): void
    {
        $data = [
            'destination' => 'Paris, França',
            'departure_date' => 'invalid-date',
            'return_date' => '2024-06-15',
        ];

        $response = $this->postJson(
            '/api/travel-orders',
            $data,
            $this->getAuthenticatedHeaders()
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['departure_date'])
        ;
    }

    public function test_create_returns_validation_error_for_invalid_return_date(): void
    {
        $data = [
            'destination' => 'Paris, França',
            'departure_date' => '2024-06-01',
            'return_date' => 'invalid-date',
        ];

        $response = $this->postJson(
            '/api/travel-orders',
            $data,
            $this->getAuthenticatedHeaders()
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['return_date'])
        ;
    }

    public function test_create_returns_validation_error_when_return_before_departure(): void
    {
        $data = [
            'destination' => 'Paris, França',
            'departure_date' => '2024-06-15',
            'return_date' => '2024-06-01',
        ];

        $response = $this->postJson(
            '/api/travel-orders',
            $data,
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(422);
        
        $responseData = $response->json();
        $this->assertTrue(
            isset($responseData['errors']['return_date']) || 
            isset($responseData['error'])
        );
    }

    public function test_create_validates_dates_with_different_formats_in_request(): void
    {
        $data = [
            'destination' => 'Paris, França',
            'departure_date' => '2024-06-01',
            'return_date' => '2024-06-15',
        ];

        $response = $this->postJson(
            '/api/travel-orders',
            $data,
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(201);
    }

    public function test_create_automatically_sets_user_id_from_token(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        $data = [
            'destination' => 'Paris, França',
            'departure_date' => '2024-06-01',
            'return_date' => '2024-06-15',
        ];

        $response = $this->postJson(
            '/api/travel-orders',
            $data,
            $this->getAuthHeaders($authenticated['token'])
        );

        $response->assertStatus(201);

        $this->assertDatabaseHas('travel_orders', [
            'id' => $response->json('id'),
            'user_id' => $authenticated['user']->id,
        ]);
    }

    public function test_create_returns_201_with_travel_order_resource(): void
    {
        $data = [
            'destination' => 'Paris, França',
            'departure_date' => '2024-06-01',
            'return_date' => '2024-06-15',
        ];

        $response = $this->postJson(
            '/api/travel-orders',
            $data,
            $this->getAuthenticatedHeaders()
        );

        $response
            ->assertStatus(201)
            ->assertJson([
                'destination' => 'Paris, França',
                'departure_date' => '2024-06-01',
                'return_date' => '2024-06-15',
                'status' => 'solicitado',
            ])
        ;
    }

    public function test_authenticated_user_can_list_own_travel_orders(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        TravelOrder::factory()->count(3)->create(['user_id' => $authenticated['user']->id]);

        $response = $this->getJson('/api/travel-orders', $this->getAuthHeaders($authenticated['token']));

        $response
            ->assertStatus(200)
            ->assertJsonCount(3)
        ;
    }

    public function test_user_only_sees_own_travel_orders(): void
    {
        $user1 = $this->createAuthenticatedUser();
        $user2 = $this->createAuthenticatedUser();

        TravelOrder::factory()->count(3)->create(['user_id' => $user1['user']->id]);
        TravelOrder::factory()->count(2)->create(['user_id' => $user2['user']->id]);

        $token = $this->actingAsWithJwt($user1['user']);
        $response = $this->getJson('/api/travel-orders', $this->getAuthHeaders($token));

        $response
            ->assertStatus(200)
            ->assertJsonCount(3)
        ;

        $response->assertJsonMissing([
            'user_id' => $user2['user']->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_list_travel_orders(): void
    {
        $response = $this->getJson('/api/travel-orders');

        $response->assertStatus(401);
    }

    public function test_list_can_filter_by_status(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        TravelOrder::factory()->count(2)->approved()->create(['user_id' => $authenticated['user']->id]);
        TravelOrder::factory()->count(3)->requested()->create(['user_id' => $authenticated['user']->id]);

        $response = $this->getJson('/api/travel-orders?status=aprovado', $this->getAuthHeaders($authenticated['token']));

        $response
            ->assertStatus(200)
            ->assertJsonCount(2)
        ;

        $response->assertJsonFragment(['status' => 'aprovado']);
        $response->assertJsonMissing([
            'status' => 'solicitado',
        ]);
    }

    public function test_list_can_filter_by_destination(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        TravelOrder::factory()->create([
            'user_id' => $authenticated['user']->id,
            'destination' => 'Paris, França',
        ]);
        TravelOrder::factory()->create([
            'user_id' => $authenticated['user']->id,
            'destination' => 'Londres, Inglaterra',
        ]);

        $response = $this->getJson(
            '/api/travel-orders?destination=Paris',
            $this->getAuthHeaders($authenticated['token'])
        );

        $response
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['destination' => 'Paris, França'])
        ;
    }

    public function test_list_can_filter_by_date_range(): void
    {
        $authenticated = $this->createAuthenticatedUser();

        $order1 = TravelOrder::factory()->create([
            'user_id' => $authenticated['user']->id,
            'created_at' => '2024-01-15 10:00:00',
        ]);
        $order2 = TravelOrder::factory()->create([
            'user_id' => $authenticated['user']->id,
            'created_at' => '2024-02-15 10:00:00',
        ]);
        $order3 = TravelOrder::factory()->create([
            'user_id' => $authenticated['user']->id,
            'created_at' => '2024-03-15 10:00:00',
        ]);

        $response = $this->getJson(
            '/api/travel-orders?start_date=2024-02-01&end_date=2024-02-28',
            $this->getAuthHeaders($authenticated['token'])
        );

        $response
            ->assertStatus(200)
            ->assertJsonCount(1)
        ;
    }

    public function test_list_returns_empty_collection_when_no_orders(): void
    {
        $response = $this->getJson('/api/travel-orders', $this->getAuthenticatedHeaders());

        $response
            ->assertStatus(200)
            ->assertJsonCount(0)
        ;
    }

    public function test_authenticated_user_can_view_own_travel_order(): void
    {
        $authenticated = $this->createAuthenticatedUser();
        $travelOrder = TravelOrder::factory()->create(['user_id' => $authenticated['user']->id]);

        $response = $this->getJson(
            "/api/travel-orders/{$travelOrder->id}",
            $this->getAuthHeaders($authenticated['token'])
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $travelOrder->id,
                'destination' => $travelOrder->destination,
            ])
        ;
    }

    public function test_user_cannot_view_other_user_travel_order(): void
    {
        $user1 = $this->createAuthenticatedUser();
        $user2 = $this->createAuthenticatedUser();

        $travelOrder = TravelOrder::factory()->create(['user_id' => $user2['user']->id]);

        $response = $this->getJson(
            "/api/travel-orders/{$travelOrder->id}",
            $this->getAuthHeaders($user1['token'])
        );

        $response
            ->assertStatus(404)
            ->assertJson([
                'error' => 'Pedido de viagem não encontrado ou você não tem permissão para acessá-lo',
            ])
        ;
    }

    public function test_unauthenticated_user_cannot_view_travel_order(): void
    {
        $travelOrder = TravelOrder::factory()->create();

        $response = $this->getJson("/api/travel-orders/{$travelOrder->id}");

        $response->assertStatus(401);
    }

    public function test_show_returns_404_for_nonexistent_order(): void
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson("/api/travel-orders/{$nonExistentId}", $this->getAuthenticatedHeaders());

        $response->assertStatus(404);
    }

    public function test_show_returns_travel_order_resource(): void
    {
        $authenticated = $this->createAuthenticatedUser();
        $travelOrder = TravelOrder::factory()->create(['user_id' => $authenticated['user']->id]);

        $response = $this->getJson(
            "/api/travel-orders/{$travelOrder->id}",
            $this->getAuthHeaders($authenticated['token'])
        );

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'destination',
                'departure_date',
                'return_date',
                'status',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_admin_can_update_travel_order_status(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin->fresh());

        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status' => 'solicitado',
        ]);

        $response = $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => 'aprovado'],
            $this->getAuthHeaders($token)
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $travelOrder->id,
                'status' => 'aprovado',
            ])
        ;

        $this->assertDatabaseHas('travel_orders', [
            'id' => $travelOrder->id,
            'status' => 'aprovado',
        ]);
    }

    public function test_non_admin_cannot_update_travel_order_status(): void
    {
        $user = User::factory()->create();
        $token = $this->actingAsWithJwt($user);

        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status' => 'solicitado',
        ]);

        $response = $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => 'aprovado'],
            $this->getAuthHeaders($token)
        );

        $response
            ->assertStatus(403)
            ->assertJson(['error' => Messages::UNAUTHORIZED_ACCESS])
        ;
    }

    public function test_unauthenticated_user_cannot_update_travel_order_status(): void
    {
        $travelOrder = TravelOrder::factory()->create();

        $response = $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => 'aprovado']
        );

        $response->assertStatus(401);
    }

    public function test_cannot_cancel_approved_order(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin->fresh());

        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->approved()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => 'cancelado'],
            $this->getAuthHeaders($token)
        );

        $response
            ->assertStatus(422)
            ->assertJson([
                'error' => Messages::CANNOT_CANCEL_APPROVED_ORDER,
            ])
        ;

        $this->assertDatabaseHas('travel_orders', [
            'id' => $travelOrder->id,
            'status' => 'aprovado',
        ]);
    }

    public function test_can_cancel_requested_order(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin->fresh());

        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->requested()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => 'cancelado'],
            $this->getAuthHeaders($token)
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $travelOrder->id,
                'status' => 'cancelado',
            ])
        ;

        $this->assertDatabaseHas('travel_orders', [
            'id' => $travelOrder->id,
            'status' => 'cancelado',
        ]);
    }

    public function test_update_status_returns_validation_error_for_invalid_status(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin->fresh());

        $travelOrder = TravelOrder::factory()->create();

        $response = $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => 'invalid_status'],
            $this->getAuthHeaders($token)
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status'])
        ;
    }

    public function test_update_status_returns_validation_error_for_missing_status(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin->fresh());

        $travelOrder = TravelOrder::factory()->create();

        $response = $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            [],
            $this->getAuthHeaders($token)
        );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status'])
        ;
    }

    public function test_update_status_returns_404_for_nonexistent_order(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin->fresh());

        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $response = $this->patchJson(
            "/api/travel-orders/{$nonExistentId}/status",
            ['status' => 'aprovado'],
            $this->getAuthHeaders($token)
        );

        $response->assertStatus(404);
    }
}
