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

        $travelOrderDto = $this->makeDTO($travelOrder);
        $updatedDto = $this->makeDTO($updatedTravelOrder);

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with($travelOrder->id)
            ->andReturn($travelOrderDto)
        ;

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with($travelOrderDto, ['status' => TravelOrderStatus::APPROVED->value])
            ->andReturn($updatedDto)
        ;

        $this->service->updateStatus($travelOrder->id, TravelOrderStatus::APPROVED->value);

        Notification::assertSentOnDemand(
            TravelOrderStatusChanged::class,
            function ($notification, $channels, $notifiable) use ($updatedDto, $user) {
                return $notification->getTravelOrder()->id === $updatedDto->id
                    && in_array('mail', $channels)
                    && $notifiable->routes['mail'] === $user->email;
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

        $dto = $this->makeDTO($travelOrder);

        $this->service->notifyUser($dto);

        Notification::assertSentOnDemand(
            TravelOrderStatusChanged::class,
            function ($notification, $channels, $notifiable) use ($user) {
                return in_array('mail', $channels)
                    && $notifiable->routes['mail'] === $user->email;
            }
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

        $travelOrderDto = $this->makeDTO($travelOrder);
        $updatedDto = $this->makeDTO($updatedTravelOrder);

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->andReturn($travelOrderDto)
        ;

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->andReturn($updatedDto)
        ;

        $this->service->updateStatus($travelOrder->id, TravelOrderStatus::CANCELED->value);

        Notification::assertSentOnDemand(
            TravelOrderStatusChanged::class,
            function ($notification) use ($updatedDto) {
                return $notification->getTravelOrder()->status === $updatedDto->status;
            }
        );
    }
}
