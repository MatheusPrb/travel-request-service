<?php

namespace Tests\Unit;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Tests\TestCase;

class BaseFormRequestTest extends TestCase
{
    public function test_base_form_request_extends_form_request(): void
    {
        $reflection = new \ReflectionClass(BaseFormRequest::class);
        
        $this->assertTrue($reflection->isAbstract());
        $this->assertTrue($reflection->isSubclassOf(FormRequest::class));
    }

    public function test_base_form_request_has_failed_validation_method(): void
    {
        $reflection = new \ReflectionClass(BaseFormRequest::class);
        
        $this->assertTrue($reflection->hasMethod('failedValidation'));
        
        $method = $reflection->getMethod('failedValidation');
        $this->assertTrue($method->isProtected());
    }

    public function test_failed_validation_throws_http_response_exception_with_422(): void
    {
        $request = new class extends BaseFormRequest {
            public function authorize(): bool
            {
                return true;
            }

            public function rules(): array
            {
                return [
                    'test_field' => ['required', 'string'],
                ];
            }
        };

        $validator = ValidatorFacade::make([], ['test_field' => 'required|string']);

        $this->expectException(HttpResponseException::class);

        try {
            $reflection = new \ReflectionClass($request);
            $method = $reflection->getMethod('failedValidation');
            if (method_exists($method, 'setAccessible')) {
                $method->setAccessible(true);
            }
            $method->invoke($request, $validator);
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            
            $this->assertEquals(422, $response->getStatusCode());
            $this->assertJson($response->getContent());
            
            $data = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('errors', $data);
            
            throw $e;
        }
    }

    public function test_failed_validation_returns_errors_in_correct_format(): void
    {
        $request = new class extends BaseFormRequest {
            public function authorize(): bool
            {
                return true;
            }

            public function rules(): array
            {
                return [
                    'name' => ['required', 'string'],
                    'email' => ['required', 'email'],
                ];
            }
        };

        $validator = ValidatorFacade::make(
            [],
            [
                'name' => 'required|string',
                'email' => 'required|email',
            ]
        );

        try {
            $reflection = new \ReflectionClass($request);
            $method = $reflection->getMethod('failedValidation');
            if (method_exists($method, 'setAccessible')) {
                $method->setAccessible(true);
            }
            $method->invoke($request, $validator);
            
            $this->fail('Expected HttpResponseException was not thrown');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $data = json_decode($response->getContent(), true);
            
            $this->assertIsArray($data['errors']);
            $this->assertArrayHasKey('name', $data['errors']);
            $this->assertArrayHasKey('email', $data['errors']);
        }
    }
}
