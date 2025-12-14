<?php

namespace Tests\Unit;

use App\Constants\Messages;
use Tests\TestCase;

class MessagesTest extends TestCase
{
    public function test_error_messages_constants_are_defined(): void
    {
        $this->assertNotEmpty(Messages::INVALID_CREDENTIALS);
        $this->assertNotEmpty(Messages::INVALID_TOKEN);
        $this->assertNotEmpty(Messages::UNAUTHORIZED_ACCESS);
        $this->assertNotEmpty(Messages::TRAVEL_ORDER_NOT_FOUND);
        $this->assertNotEmpty(Messages::INVALID_TRAVEL_DATES);
        $this->assertNotEmpty(Messages::USER_ALREADY_ADMIN);
        $this->assertNotEmpty(Messages::USER_PROMOTED_TO_ADMIN);
        $this->assertNotEmpty(Messages::LOGOUT_SUCCESS);
    }

    public function test_error_messages_are_strings(): void
    {
        $this->assertIsString(Messages::INVALID_CREDENTIALS);
        $this->assertIsString(Messages::INVALID_TOKEN);
        $this->assertIsString(Messages::UNAUTHORIZED_ACCESS);
        $this->assertIsString(Messages::TRAVEL_ORDER_NOT_FOUND);
        $this->assertIsString(Messages::INVALID_TRAVEL_DATES);
        $this->assertIsString(Messages::USER_ALREADY_ADMIN);
        $this->assertIsString(Messages::USER_PROMOTED_TO_ADMIN);
        $this->assertIsString(Messages::LOGOUT_SUCCESS);
    }

    public function test_travel_order_not_found_message_is_correct(): void
    {
        $expectedMessage = 'Pedido de viagem não encontrado ou você não tem permissão para acessá-lo';
        $this->assertEquals($expectedMessage, Messages::TRAVEL_ORDER_NOT_FOUND);
    }

    public function test_invalid_travel_dates_message_is_correct(): void
    {
        $expectedMessage = 'Data de volta não pode ser antes da ida.';
        $this->assertEquals($expectedMessage, Messages::INVALID_TRAVEL_DATES);
    }
}
