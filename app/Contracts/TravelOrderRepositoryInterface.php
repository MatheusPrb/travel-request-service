<?php

namespace App\Contracts;

use App\Models\TravelOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TravelOrderRepositoryInterface
{
    public function create(array $data): TravelOrder;
    public function findById(string $id): TravelOrder;
    public function findByUserId(string $userId, array $filters = []): Collection|LengthAwarePaginator;
    public function belongsToUser(string $id, string $userId): bool;
}
