<?php

namespace App\Services;

use App\Constants\Messages;
use App\Contracts\TravelOrderRepositoryInterface;
use App\Exceptions\InvalidTravelDatesException;
use App\Exceptions\NotFoundException;
use App\Models\TravelOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

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

    public function listByUser(string $userId, array $filters = []): Collection
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
}
