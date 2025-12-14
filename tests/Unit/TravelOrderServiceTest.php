<?php

namespace Tests\Unit;

use App\Contracts\TravelOrderRepositoryInterface;
use App\Exceptions\InvalidTravelDatesException;
use App\Models\TravelOrder;
use App\Services\TravelOrderService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TravelOrderServiceTest extends TestCase
{
    private TravelOrderService $service;
    private MockInterface&TravelOrderRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TravelOrderRepositoryInterface::class);
        $this->service = new TravelOrderService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_travel_order_successfully(): void
    {
        $user = $this->createAuthenticatedUser();
        $data = [
            'user_id' => $user['user']->id,
            'destination' => 'Paris, França',
            'departure_date' => '2024-06-01',
            'return_date' => '2024-06-15',
        ];

        $travelOrder = TravelOrder::factory()->make($data);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($travelOrder)
        ;

        $result = $this->service->create($data);

        $this->assertInstanceOf(TravelOrder::class, $result);
        $this->assertEquals($data['destination'], $result->destination);
    }

    public function test_create_throws_exception_when_return_date_before_departure(): void
    {
        $user = $this->createAuthenticatedUser();
        $data = [
            'user_id' => $user['user']->id,
            'destination' => 'Paris, França',
            'departure_date' => '2024-06-15',
            'return_date' => '2024-06-01',
        ];

        $this->expectException(InvalidTravelDatesException::class);
        $this->expectExceptionMessage('Data de volta não pode ser antes da ida.');

        $this->service->create($data);
    }

    public function test_list_by_user_returns_only_user_orders(): void
    {
        $user = $this->createAuthenticatedUser();
        $orders = TravelOrder::factory()->count(3)->make();

        $this->repository
            ->shouldReceive('findByUserId')
            ->once()
            ->with($user['user']->id, [])
            ->andReturn(new Collection($orders))
        ;

        $result = $this->service->listByUser($user['user']->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(3, $result);
    }

    public function test_list_by_user_with_filters(): void
    {
        $user = $this->createAuthenticatedUser();
        $filters = ['status' => 'aprovado', 'destination' => 'Paris'];
        $orders = TravelOrder::factory()->count(2)->make();

        $this->repository
            ->shouldReceive('findByUserId')
            ->once()
            ->with($user['user']->id, $filters)
            ->andReturn(new Collection($orders))
        ;

        $result = $this->service->listByUser($user['user']->id, $filters);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    public function test_find_by_id_successfully(): void
    {
        $user = $this->createAuthenticatedUser();
        $travelOrderId = '123e4567-e89b-12d3-a456-426614174000';
        $travelOrder = TravelOrder::factory()->make([
            'id' => $travelOrderId,
            'user_id' => $user['user']->id
        ]);

        $this->repository
            ->shouldReceive('belongsToUser')
            ->once()
            ->with($travelOrderId, $user['user']->id)
            ->andReturn(true)
        ;

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with($travelOrderId)
            ->andReturn($travelOrder)
        ;

        $result = $this->service->findById($travelOrderId, $user['user']->id);

        $this->assertInstanceOf(TravelOrder::class, $result);
        $this->assertEquals($travelOrderId, $result->id);
    }

    public function test_find_by_id_throws_exception_when_not_belongs_to_user(): void
    {
        $user = $this->createAuthenticatedUser();
        $otherUser = $this->createAuthenticatedUser();
        $travelOrderId = '123e4567-e89b-12d3-a456-426614174001';

        TravelOrder::factory()->create([
            'id' => $travelOrderId,
            'user_id' => $otherUser['user']->id
        ]);

        $this->repository
            ->shouldReceive('belongsToUser')
            ->once()
            ->with($travelOrderId, $user['user']->id)
            ->andReturn(false)
        ;

        $this->expectException(HttpResponseException::class);

        $this->service->findById($travelOrderId, $user['user']->id);
    }

    public function test_find_by_id_throws_exception_when_not_found(): void
    {
        $user = $this->createAuthenticatedUser();
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->repository
            ->shouldReceive('belongsToUser')
            ->once()
            ->with($nonExistentId, $user['user']->id)
            ->andReturn(false)
        ;

        $this->expectException(HttpResponseException::class);

        $this->service->findById($nonExistentId, $user['user']->id);
    }
}
