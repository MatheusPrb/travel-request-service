<?php

namespace Tests\Unit;

use App\Constants\Messages;
use App\Exceptions\InvalidTravelDatesException;
use Tests\TestCase;

class InvalidTravelDatesExceptionTest extends TestCase
{
    public function test_invalid_travel_dates_exception_can_be_instantiated_with_message(): void
    {
        $message = 'Datas inválidas';
        $exception = new InvalidTravelDatesException($message);

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function test_invalid_travel_dates_exception_uses_error_messages_constant(): void
    {
        $message = Messages::INVALID_TRAVEL_DATES;
        $exception = new InvalidTravelDatesException($message);

        $this->assertEquals($message, $exception->getMessage());
    }
}
