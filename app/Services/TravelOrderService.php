<?php

namespace App\Services;

use App\Constants\Messages;
use App\Contracts\TravelOrderRepositoryInterface;
use App\Enums\TravelOrderStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\InvalidTravelDatesException;
use App\Exceptions\NotFoundException;
use App\Models\TravelOrder;
use App\Notifications\TravelOrderStatusChanged;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TravelOrderService
{
    private TravelOrderRepositoryInterface $repository;

    public function __construct(TravelOrderRepositoryInterface $repository) 
    {
        $this->repository = $repository;
    }

    public function create(array $data): TravelOrder
    {
        $departureDate = Carbon::parse($data['departure_date']);
        $returnDate = Carbon::parse($data['return_date']);

        if ($departureDate->greaterThan($returnDate)) {
            throw new InvalidTravelDatesException(Messages::INVALID_TRAVEL_DATES);
        }

        return $this->repository->create($data);
    }

    public function listByUser(string $userId, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->findByUserId($userId, $filters);
    }

    public function findById(string $id, string $userId): TravelOrder
    {
        if (!$this->repository->belongsToUser($id, $userId)) {
            throw new NotFoundException(Messages::TRAVEL_ORDER_NOT_FOUND);
        }

        return $this->repository->findById($id);
    }

    public function updateStatus(string $id, string $newStatus): TravelOrder
    {
        $travelOrder = $this->repository->findById($id);
        $currentStatus = TravelOrderStatus::tryFrom($travelOrder->status);
        $newStatusEnum = TravelOrderStatus::tryFrom($newStatus);

        if (!$currentStatus || !$newStatusEnum) {
            throw new InvalidStatusTransitionException(Messages::INVALID_STATUS_UPDATE);
        }

        if (!$currentStatus->canUpdateTo($newStatusEnum)) {
            throw new InvalidStatusTransitionException(Messages::INVALID_STATUS_UPDATE);
        }

        $updateData = ['status' => $newStatus];
        
        if ($newStatusEnum === TravelOrderStatus::CANCELED) {
            $updateData['cancelled_at'] = Carbon::now();
        }

        $updatedTravelOrder = $this->repository->update($travelOrder, $updateData);

        $this->notifyUser($updatedTravelOrder);

        return $updatedTravelOrder;
    }

    public function notifyUser(TravelOrder $travelOrder): void
    {
        $travelOrder->user->notify(new TravelOrderStatusChanged($travelOrder));
    }
}
