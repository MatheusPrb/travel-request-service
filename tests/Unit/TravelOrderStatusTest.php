<?php

namespace Tests\Unit;

use App\Enums\TravelOrderStatus;
use Tests\TestCase;

class TravelOrderStatusTest extends TestCase
{
    public function test_enum_has_correct_values(): void
    {
        $this->assertEquals('solicitado', TravelOrderStatus::REQUESTED->value);
        $this->assertEquals('aprovado', TravelOrderStatus::APPROVED->value);
        $this->assertEquals('cancelado', TravelOrderStatus::CANCELED->value);
    }

    public function test_requested_can_transition_to_approved(): void
    {
        $this->assertTrue(
            TravelOrderStatus::REQUESTED->canTransitionTo(TravelOrderStatus::APPROVED)
        );
    }

    public function test_requested_can_transition_to_canceled(): void
    {
        $this->assertTrue(
            TravelOrderStatus::REQUESTED->canTransitionTo(TravelOrderStatus::CANCELED)
        );
    }

    public function test_requested_cannot_transition_to_same_status(): void
    {
        $this->assertFalse(
            TravelOrderStatus::REQUESTED->canTransitionTo(TravelOrderStatus::REQUESTED)
        );
    }

    public function test_approved_cannot_transition_to_any_status(): void
    {
        $this->assertFalse(
            TravelOrderStatus::APPROVED->canTransitionTo(TravelOrderStatus::REQUESTED)
        );
        $this->assertFalse(
            TravelOrderStatus::APPROVED->canTransitionTo(TravelOrderStatus::APPROVED)
        );
        $this->assertFalse(
            TravelOrderStatus::APPROVED->canTransitionTo(TravelOrderStatus::CANCELED)
        );
    }

    public function test_canceled_cannot_transition_to_any_status(): void
    {
        $this->assertFalse(
            TravelOrderStatus::CANCELED->canTransitionTo(TravelOrderStatus::REQUESTED)
        );
        $this->assertFalse(
            TravelOrderStatus::CANCELED->canTransitionTo(TravelOrderStatus::APPROVED)
        );
        $this->assertFalse(
            TravelOrderStatus::CANCELED->canTransitionTo(TravelOrderStatus::CANCELED)
        );
    }

    public function test_values_returns_all_status_values(): void
    {
        $values = TravelOrderStatus::values();
        
        $this->assertIsArray($values);
        $this->assertCount(3, $values);
        $this->assertContains('solicitado', $values);
        $this->assertContains('aprovado', $values);
        $this->assertContains('cancelado', $values);
    }

    public function test_to_array_returns_all_status_values(): void
    {
        $array = TravelOrderStatus::toArray();
        
        $this->assertIsArray($array);
        $this->assertCount(3, $array);
        $this->assertContains('solicitado', $array);
        $this->assertContains('aprovado', $array);
        $this->assertContains('cancelado', $array);
    }
}
