<?php

namespace Tests\Feature;

use App\Models\TravelOrder;
use App\Models\User;
use Tests\TestCase;

class FormRequestErrorFormatTest extends TestCase
{
    public function test_create_travel_order_request_returns_errors_in_correct_format(): void
    {
        $response = $this->postJson(
            '/api/travel-orders',
            [],
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(422);
        
        $data = $response->json();
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertArrayHasKey('destination', $data['errors']);
        $this->assertArrayHasKey('departure_date', $data['errors']);
        $this->assertArrayHasKey('return_date', $data['errors']);
    }

    public function test_update_travel_order_status_request_returns_errors_in_correct_format(): void
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

        $response->assertStatus(422);
        
        $data = $response->json();
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertArrayHasKey('status', $data['errors']);
    }

    public function test_register_request_returns_errors_in_correct_format(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422);
        
        $data = $response->json();
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertArrayHasKey('name', $data['errors']);
        $this->assertArrayHasKey('email', $data['errors']);
        $this->assertArrayHasKey('password', $data['errors']);
    }

    public function test_login_request_returns_errors_in_correct_format(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422);
        
        $data = $response->json();
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertArrayHasKey('email', $data['errors']);
        $this->assertArrayHasKey('password', $data['errors']);
    }

    public function test_promote_user_to_admin_request_returns_errors_in_correct_format(): void
    {
        $admin = User::factory()->create();
        $admin->makeAdmin();
        $token = $this->actingAsWithJwt($admin->fresh());

        $response = $this->postJson(
            '/api/users/promote-to-admin',
            [],
            $this->getAuthHeaders($token)
        );

        $response->assertStatus(422);
        
        $data = $response->json();
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertArrayHasKey('user_id', $data['errors']);
    }

    public function test_all_form_requests_return_422_status_code_on_validation_failure(): void
    {

        $response1 = $this->postJson(
            '/api/travel-orders',
            ['invalid' => 'data'],
            $this->getAuthenticatedHeaders()
        );
        $response1->assertStatus(422);

        $admin = $this->createAdminUserWithToken();
        $token = $admin['token'];
        $travelOrder = TravelOrder::factory()->create();
        
        $response2 = $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            [],
            $this->getAuthHeaders($token)
        );
        $response2->assertStatus(422);

        $response3 = $this->postJson('/api/register', ['invalid' => 'data']);
        $response3->assertStatus(422);

        $response4 = $this->postJson('/api/login', ['invalid' => 'data']);
        $response4->assertStatus(422);

        $response5 = $this->postJson(
            '/api/users/promote-to-admin',
            ['invalid' => 'data'],
            $this->getAuthHeaders($token)
        );
        $response5->assertStatus(422);
    }

    public function test_list_travel_orders_request_returns_errors_in_correct_format(): void
    {
        $response = $this->getJson(
            '/api/travel-orders?status=invalid&per_page=200',
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(422);
        
        $data = $response->json();
        $this->assertArrayHasKey('errors', $data);
        $this->assertIsArray($data['errors']);
        $this->assertArrayHasKey('status', $data['errors']);
        $this->assertArrayHasKey('per_page', $data['errors']);
    }

    public function test_list_travel_orders_request_validates_status(): void
    {
        $response = $this->getJson(
            '/api/travel-orders?status=invalido',
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    public function test_list_travel_orders_request_validates_destination_max_length(): void
    {
        $longDestination = str_repeat('a', 256);
        
        $response = $this->getJson(
            "/api/travel-orders?destination={$longDestination}",
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['destination']);
    }

    public function test_list_travel_orders_request_validates_date_format(): void
    {
        $response = $this->getJson(
            '/api/travel-orders?start_date=invalid-date',
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_date']);
    }

    public function test_list_travel_orders_request_validates_end_date_after_start_date(): void
    {
        $response = $this->getJson(
            '/api/travel-orders?start_date=2024-02-01&end_date=2024-01-01',
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_date']);
    }

    public function test_list_travel_orders_request_validates_per_page_min(): void
    {
        $response = $this->getJson(
            '/api/travel-orders?per_page=0',
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    public function test_list_travel_orders_request_validates_per_page_max(): void
    {
        $response = $this->getJson(
            '/api/travel-orders?per_page=101',
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    public function test_list_travel_orders_request_validates_page_min(): void
    {
        $response = $this->getJson(
            '/api/travel-orders?page=0',
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page']);
    }

    public function test_list_travel_orders_request_accepts_valid_parameters(): void
    {
        $response = $this->getJson(
            '/api/travel-orders?status=aprovado&destination=Paris&per_page=10&page=1',
            $this->getAuthenticatedHeaders()
        );

        $response->assertStatus(200);
    }
}
