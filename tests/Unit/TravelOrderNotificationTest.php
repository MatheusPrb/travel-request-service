<?php

namespace Tests\Unit;

use App\Enums\TravelOrderStatus;
use App\Models\TravelOrder;
use App\Models\User;
use App\Notifications\TravelOrderStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TravelOrderNotificationTest extends TestCase
{
    public function test_notification_is_queued(): void
    {
        $notification = new TravelOrderStatusChanged(
            TravelOrder::factory()->make()
        );

        $this->assertInstanceOf(ShouldQueue::class, $notification);
    }

    public function test_notification_has_correct_tries(): void
    {
        $notification = new TravelOrderStatusChanged(
            TravelOrder::factory()->make()
        );

        $this->assertEquals(3, $notification->tries);
    }

    public function test_notification_uses_mail_channel(): void
    {
        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->create(['user_id' => $user->id]);

        $notification = new TravelOrderStatusChanged($travelOrder);
        $channels = $notification->via($user);

        $this->assertEquals(['mail'], $channels);
    }

    public function test_notification_subject_for_approved_status(): void
    {
        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->approved()->create([
            'user_id' => $user->id,
            'destination' => 'Paris, França',
        ]);

        $notification = new TravelOrderStatusChanged($travelOrder);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Paris, França', $mailMessage->subject);
        $this->assertStringContainsString('Status do Pedido de Viagem Atualizado', $mailMessage->subject);
    }

    public function test_notification_subject_for_canceled_status(): void
    {
        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->cancelled()->create([
            'user_id' => $user->id,
            'destination' => 'Tokyo, Japão',
        ]);

        $notification = new TravelOrderStatusChanged($travelOrder);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Tokyo, Japão', $mailMessage->subject);
    }

    public function test_notification_contains_travel_order_data(): void
    {
        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->approved()->create([
            'user_id' => $user->id,
            'destination' => 'Nova York, EUA',
            'departure_date' => '2024-06-01',
            'return_date' => '2024-06-15',
        ]);

        $notification = new TravelOrderStatusChanged($travelOrder);
        $mailMessage = $notification->toMail($user);

        $viewData = $mailMessage->viewData;
        
        $this->assertEquals($travelOrder->id, $viewData['travelOrder']->id);
        $this->assertEquals('Nova York, EUA', $viewData['travelOrder']->destination);
        $this->assertEquals(TravelOrderStatus::APPROVED->value, $viewData['status']);
        $this->assertEquals($user->id, $viewData['user']->id);
    }

    public function test_notification_message_for_approved_status(): void
    {
        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->approved()->create([
            'user_id' => $user->id,
        ]);

        $notification = new TravelOrderStatusChanged($travelOrder);
        $mailMessage = $notification->toMail($user);

        $viewData = $mailMessage->viewData;
        
        $this->assertEquals('Seu pedido de viagem foi aprovado!', $viewData['statusMessage']);
        $this->assertEquals('#10b981', $viewData['statusColor']);
    }

    public function test_notification_message_for_canceled_status(): void
    {
        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->cancelled()->create([
            'user_id' => $user->id,
        ]);

        $notification = new TravelOrderStatusChanged($travelOrder);
        $mailMessage = $notification->toMail($user);

        $viewData = $mailMessage->viewData;
        
        $this->assertEquals('Seu pedido de viagem foi cancelado.', $viewData['statusMessage']);
        $this->assertEquals('#ef4444', $viewData['statusColor']);
    }

    public function test_notification_uses_travel_order_status_from_instance(): void
    {
        $user = User::factory()->create();

        $travelOrder = TravelOrder::factory()->approved()->create([
            'user_id' => $user->id,
        ]);

        $travelOrder->status = TravelOrderStatus::CANCELED->value;
        
        $notification = new TravelOrderStatusChanged($travelOrder);
        $mailMessage = $notification->toMail($user);

        $viewData = $mailMessage->viewData;
        
        $this->assertEquals(TravelOrderStatus::CANCELED->value, $viewData['status']);
        $this->assertEquals('Seu pedido de viagem foi cancelado.', $viewData['statusMessage']);
    }
}
