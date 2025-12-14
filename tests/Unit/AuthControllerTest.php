<?php

namespace Tests\Unit;

use App\Constants\Messages;
use App\Http\Requests\CreateTravelOrderRequest;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_create_travel_order_request_validates_return_date_after_departure(): void
    {
        $request = new CreateTravelOrderRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('return_date', $rules);
        $this->assertContains('after_or_equal:departure_date', $rules['return_date']);
    }

    public function test_create_travel_order_request_has_correct_validation_rules(): void
    {
        $request = new CreateTravelOrderRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('destination', $rules);
        $this->assertArrayHasKey('departure_date', $rules);
        $this->assertArrayHasKey('return_date', $rules);
        
        $this->assertContains('required', $rules['destination']);
        $this->assertContains('string', $rules['destination']);
        
        $this->assertContains('required', $rules['departure_date']);
        $this->assertContains('date', $rules['departure_date']);
        
        $this->assertContains('required', $rules['return_date']);
        $this->assertContains('date', $rules['return_date']);
        $this->assertContains('after_or_equal:departure_date', $rules['return_date']);
    }
}
