<?php

namespace Tests\Unit;

use App\Constants\Messages;
use App\Exceptions\NotFoundException;
use Tests\TestCase;

class NotFoundExceptionTest extends TestCase
{
    public function test_not_found_exception_can_be_instantiated_with_message(): void
    {
        $message = 'Recurso não encontrado';
        $exception = new NotFoundException($message);

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function test_not_found_exception_uses_error_messages_constant(): void
    {
        $message = Messages::TRAVEL_ORDER_NOT_FOUND;
        $exception = new NotFoundException($message);

        $this->assertEquals($message, $exception->getMessage());
    }
}