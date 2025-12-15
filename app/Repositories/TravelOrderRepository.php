<?php

namespace App\Repositories;

use App\Contracts\TravelOrderRepositoryInterface;
use App\Models\TravelOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TravelOrderRepository implements TravelOrderRepositoryInterface
{
    public function create(array $data): TravelOrder
    {
        $order = TravelOrder::create($data);
        $order->loadMissing('user');

        return $order;
    }

    public function findById(string $id): TravelOrder
    {
        $order = TravelOrder::findOrFail($id);
        $order->loadMissing('user');

        return $order;
    }

    public function findByUserId(string $userId, array $filters = []): Collection|LengthAwarePaginator
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

        $orders = $query->orderBy('created_at', 'desc')->get();
        $orders->loadMissing('user');

        return $orders;
    }

    public function belongsToUser(string $id, string $userId): bool
    {
        return TravelOrder::where('id', $id)
            ->where('user_id', $userId)
            ->exists()
        ;
    }

    public function update(TravelOrder $travelOrder, array $data): TravelOrder
    {
        $travelOrder->update($data);

        $travelOrder->loadMissing('user');

        return $travelOrder;
    }
}
