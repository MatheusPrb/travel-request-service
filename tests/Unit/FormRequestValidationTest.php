<?php

namespace Tests\Unit;

use App\Http\Requests\BaseFormRequest;
use App\Http\Requests\CreateTravelOrderRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\PromoteUserToAdminRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateTravelOrderStatusRequest;
use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    public function test_all_form_requests_extend_base_form_request(): void
    {
        $formRequests = [
            CreateTravelOrderRequest::class,
            UpdateTravelOrderStatusRequest::class,
            RegisterRequest::class,
            LoginRequest::class,
            PromoteUserToAdminRequest::class,
        ];

        foreach ($formRequests as $formRequestClass) {
            $reflection = new \ReflectionClass($formRequestClass);
            $this->assertTrue(
                $reflection->isSubclassOf(BaseFormRequest::class),
                "{$formRequestClass} deve herdar de BaseFormRequest"
            );
        }
    }

    public function test_create_travel_order_request_has_correct_rules(): void
    {
        $request = new CreateTravelOrderRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('destination', $rules);
        $this->assertArrayHasKey('departure_date', $rules);
        $this->assertArrayHasKey('return_date', $rules);
    }

    public function test_update_travel_order_status_request_has_correct_rules(): void
    {
        $request = new UpdateTravelOrderStatusRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('status', $rules);
        $this->assertContains('in:solicitado,aprovado,cancelado', $rules['status']);
    }

    public function test_register_request_has_correct_rules(): void
    {
        $request = new RegisterRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    public function test_login_request_has_correct_rules(): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    public function test_promote_user_to_admin_request_has_correct_rules(): void
    {
        $request = new PromoteUserToAdminRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('user_id', $rules);
    }

    public function test_form_requests_do_not_have_duplicate_failed_validation(): void
    {
        $formRequests = [
            CreateTravelOrderRequest::class,
            UpdateTravelOrderStatusRequest::class,
            RegisterRequest::class,
            LoginRequest::class,
            PromoteUserToAdminRequest::class,
        ];

        foreach ($formRequests as $formRequestClass) {
            $reflection = new \ReflectionClass($formRequestClass);
            
            $method = $reflection->getMethod('failedValidation');
            $declaringClass = $method->getDeclaringClass();
            
            $this->assertEquals(
                BaseFormRequest::class,
                $declaringClass->getName(),
                "{$formRequestClass} não deve ter seu próprio método failedValidation"
            );
        }
    }
}
