<?php

namespace App\Services;

use App\Repositories\TravelOrderRepository;

class TravelOrderService
{
    private TravelOrderRepository $repository;

    public function __construct(TravelOrderRepository $repository) 
    {
        $this->repository = $repository;
    }

    public function create(array $data)
    {
        if ($data['departure_date'] > $data['return_date']) {
            throw new \Exception('Data de volta não pode ser antes da ida.');
        }

        return $this->repository->create($data);
    }
}
