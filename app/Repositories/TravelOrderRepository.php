<?php

namespace App\Repositories;

use App\Contracts\TravelOrderRepositoryInterface;
use App\Models\TravelOrder;
use App\DTO\TravelOrderDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TravelOrderRepository implements TravelOrderRepositoryInterface
{
    public function create(array $data): TravelOrderDTO
    {
        $order = TravelOrder::create($data);
        $order->loadMissing('user');

        return $this->toDTO($order);
    }

    public function findById(string $id): TravelOrderDTO
    {
        $order = TravelOrder::findOrFail($id);
        $order->loadMissing('user');

        return $this->toDTO($order);
    }

    public function findByUserId(string $userId, array $filters = []): LengthAwarePaginator
    {
        $query = TravelOrder::where('user_id', $userId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['destination'])) {
            $query->where('destination', 'like', '%' . $filters['destination'] . '%');
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['travel_start_date'])) {
            $query->whereDate('departure_date', '>=', $filters['travel_start_date']);
        }

        if (isset($filters['travel_end_date'])) {
            $query->whereDate('return_date', '<=', $filters['travel_end_date']);
        }

        $perPage = $filters['per_page'] ?? config('pagination.per_page');
        
        return $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn (TravelOrder $order) => $this->toDTO($order))
        ;
    }

    public function belongsToUser(string $id, string $userId): bool
    {
        return TravelOrder::where('id', $id)
            ->where('user_id', $userId)
            ->exists()
        ;
    }

    public function update(TravelOrderDTO $travelOrder, array $data): TravelOrderDTO
    {
        $order = TravelOrder::findOrFail($travelOrder->id);
        $order->update($data);

        $order->loadMissing('user');

        return $this->toDTO($order);
    }

    private function toDTO(TravelOrder $model): TravelOrderDTO
    {
        return new TravelOrderDTO(
            id: $model->id,
            status: $model->status,
            userId: $model->user_id,
            destination: $model->destination,
            departureDate: $model->departure_date,
            returnDate: $model->return_date,
            userEmail: $model->user->email,
            userName: $model->user->name,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
