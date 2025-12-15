<?php

namespace Tests\Unit;

use App\Contracts\TravelOrderRepositoryInterface;
use App\Enums\TravelOrderStatus;
use App\Models\TravelOrder;
use App\Models\User;
use App\Notifications\TravelOrderStatusChanged;
use App\Services\TravelOrderService;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TravelOrderServiceNotificationTest extends TestCase
{
    private TravelOrderService $service;
    private MockInterface&TravelOrderRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        $this->repository = Mockery::mock(TravelOrderRepositoryInterface::class);
        $this->service = new TravelOrderService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_update_status_dispatches_notification(): void
    {
        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $user->id,
        ]);

        $updatedTravelOrder = TravelOrder::factory()->approved()->make([
            'id' => $travelOrder->id,
            'user_id' => $user->id,
        ]);
        $updatedTravelOrder->setRelation('user', $user);

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with($travelOrder->id)
            ->andReturn($travelOrder)
        ;

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with($travelOrder, ['status' => TravelOrderStatus::APPROVED->value])
            ->andReturn($updatedTravelOrder)
        ;

        $this->service->updateStatus($travelOrder->id, TravelOrderStatus::APPROVED->value);

        Notification::assertSentTo(
            $user,
            TravelOrderStatusChanged::class,
            function ($notification, $channels) use ($updatedTravelOrder) {
                return $notification->getTravelOrder()->id === $updatedTravelOrder->id
                    && in_array('mail', $channels);
            }
        );
    }

    public function test_notify_user_sends_notification_to_correct_user(): void
    {
        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->approved()->create([
            'user_id' => $user->id,
        ]);
        $travelOrder->setRelation('user', $user);

        $this->service->notifyUser($travelOrder);

        Notification::assertSentTo(
            $user,
            TravelOrderStatusChanged::class
        );
    }

    public function test_notification_contains_updated_status(): void
    {
        $user = User::factory()->create();
        $travelOrder = TravelOrder::factory()->create([
            'user_id' => $user->id,
        ]);

        $updatedTravelOrder = TravelOrder::factory()->cancelled()->make([
            'id' => $travelOrder->id,
            'user_id' => $user->id,
        ]);
        $updatedTravelOrder->setRelation('user', $user);

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->andReturn($travelOrder)
        ;

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->andReturn($updatedTravelOrder)
        ;

        $this->service->updateStatus($travelOrder->id, TravelOrderStatus::CANCELED->value);

        Notification::assertSentTo(
            $user,
            TravelOrderStatusChanged::class,
            function ($notification) use ($updatedTravelOrder) {
                return $notification->getTravelOrder()->status === $updatedTravelOrder->status;
            }
        );
    }
}
