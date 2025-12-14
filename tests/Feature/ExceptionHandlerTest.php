<?php

namespace Tests\Feature;

use App\Constants\Messages;
use App\Models\TravelOrder;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    public function test_not_found_exception_returns_404_with_correct_message(): void
    {
        $authenticated = $this->createAuthenticatedUser();
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson(
            "/api/travel-orders/{$nonExistentId}",
            $this->getAuthHeaders($authenticated['token'])
        );

        $response
            ->assertStatus(404)
            ->assertJson([
                'error' => Messages::TRAVEL_ORDER_NOT_FOUND
            ])
        ;
    }

    public function test_invalid_travel_dates_exception_returns_422_with_correct_message(): void
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

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    'return_date' => [
                        'The return date field must be a date after or equal to departure date.',
                    ],
                ],
            ])
        ;
    }

    public function test_user_cannot_access_other_user_travel_order_returns_404(): void
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
                'error' => Messages::TRAVEL_ORDER_NOT_FOUND
            ])
        ;
    }
}
