<?php

namespace App\Repositories;

use App\Models\TravelOrder;

class TravelOrderRepository
{
    public function create(array $data)
    {
        return TravelOrder::create($data);
    }

    public function findById(string $id)
    {
        return TravelOrder::findOrFail($id);
    }
}
