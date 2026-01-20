<?php

namespace Tests\Feature;

use App\Enums\TravelOrderStatus;
use App\Models\TravelOrder;
use App\Models\User;
use App\Notifications\TravelOrderStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TravelOrderNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_admin_can_update_status_and_notification_is_sent(): void
    {
        $user = User::factory()->create();
        $admin = $this->createAdminUserWithToken();

        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => TravelOrderStatus::APPROVED->value],
            $this->getAuthHeaders($admin['token'])
        );

        $response->assertStatus(200);

        Notification::assertSentOnDemand(
            TravelOrderStatusChanged::class,
            function ($notification, $channels, $notifiable) use ($travelOrder, $user) {
                return $notification->getTravelOrder()->id === $travelOrder->id
                    && $notification->getNewStatus() === TravelOrderStatus::APPROVED
                    && in_array('mail', $channels)
                    && $notifiable->routes['mail'] === $user->email
                ;
            }
        );
    }

    public function test_notification_is_sent_when_status_changes_to_approved(): void
    {
        $user = User::factory()->create();
        $admin = $this->createAdminUserWithToken();

        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $user->id,
        ]);


        $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => TravelOrderStatus::APPROVED->value],
            $this->getAuthHeaders($admin['token'])
        );

        Notification::assertSentOnDemand(
            TravelOrderStatusChanged::class,
            function ($notification, $channels, $notifiable) use ($user) {
                return in_array('mail', $channels)
                    && $notifiable->routes['mail'] === $user->email;
            }
        );
    }

    public function test_notification_is_sent_when_status_changes_to_canceled(): void
    {
        $user = User::factory()->create();
        $admin = $this->createAdminUserWithToken();

        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => TravelOrderStatus::CANCELED->value],
            $this->getAuthHeaders($admin['token'])
        );

        Notification::assertSentOnDemand(
            TravelOrderStatusChanged::class,
            function ($notification, $channels, $notifiable) use ($user) {
                return in_array('mail', $channels)
                    && $notifiable->routes['mail'] === $user->email;
            }
        );
    }

    public function test_notification_contains_correct_travel_order_information(): void
    {
        $user = $this->createAuthenticatedUser(['name' => 'João Silva', 'email' => 'joao@test.com'])['user'];
        $admin = $this->createAdminUserWithToken();

        $travelOrder = TravelOrder::factory()->create(['user_id' => $user->id]);

        $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => TravelOrderStatus::APPROVED->value],
            $this->getAuthHeaders($admin['token'])
        );

        Notification::assertSentOnDemand(
            TravelOrderStatusChanged::class,
            function ($notification, $channels, $notifiable) use ($travelOrder, $user) {
                $mailMessage = $notification->toMail($user);
                $viewData = $mailMessage->viewData;

                return $viewData['travelOrder']->destination === $travelOrder->destination
                    && $viewData['travelOrder']->id === $travelOrder->id
                    && $viewData['status'] === TravelOrderStatus::APPROVED->value
                    && $viewData['travelOrder']->userName === $user->name
                    && in_array('mail', $channels)
                    && $notifiable->routes['mail'] === $user->email
                ;
            }
        );
    }

    public function test_notification_email_has_correct_subject(): void
    {
        $user = $this->createAuthenticatedUser()['user'];
        $admin = $this->createAdminUserWithToken();

        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'destination' => 'Tokyo, Japão',
        ]);

        $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => TravelOrderStatus::APPROVED->value],
            $this->getAuthHeaders($admin['token'])
        );

        Notification::assertSentOnDemand(
            TravelOrderStatusChanged::class,
            function ($notification) use ($user, $travelOrder) {
                $mailMessage = $notification->toMail($user);

                return str_contains($mailMessage->subject, $travelOrder->destination)
                    && str_contains($mailMessage->subject, 'Status do Pedido de Viagem Atualizado')
                ;
            }
        );
    }

    public function test_notification_is_queued_not_sent_immediately(): void
    {
        $user = $this->createAuthenticatedUser()['user'];
        $admin = $this->createAdminUserWithToken();

        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->patchJson(
            "/api/travel-orders/{$travelOrder->id}/status",
            ['status' => TravelOrderStatus::APPROVED->value],
            $this->getAuthHeaders($admin['token'])
        );

        Notification::assertSentOnDemand(
            TravelOrderStatusChanged::class,
            function ($notification) {
                return $notification instanceof ShouldQueue;
            }
        );
    }
}
