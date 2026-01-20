<?php

namespace App\Contracts;

use App\DTO\TravelOrderDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TravelOrderRepositoryInterface
{
    public function create(array $data): TravelOrderDTO;
    public function findById(string $id): TravelOrderDTO;
    public function findByUserId(string $userId, array $filters = []): LengthAwarePaginator;
    public function belongsToUser(string $id, string $userId): bool;
    public function update(TravelOrderDTO $travelOrder, array $data): TravelOrderDTO;
}
