<?php

namespace App\Contracts;

use App\Models\TravelOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TravelOrderRepositoryInterface
{
    public function create(array $data): TravelOrder;
    public function findById(string $id): TravelOrder;
    public function findByUserId(string $userId, array $filters = []): LengthAwarePaginator;
    public function belongsToUser(string $id, string $userId): bool;
    public function update(TravelOrder $travelOrder, array $data): TravelOrder;
}
