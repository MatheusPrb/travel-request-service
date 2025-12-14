<?php

namespace App\Services;

use App\Repositories\TravelOrderRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

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

    public function listByUser(string $userId, array $filters = []): Collection
    {
        return $this->repository->findByUserId($userId, $filters);
    }

    public function findById(string $id, string $userId)
    {
        if (!$this->repository->belongsToUser($id, $userId)) {
            throw new HttpResponseException(
                response()->json([
                    'error' => 'Pedido de viagem não encontrado ou você não tem permissão para acessá-lo'
                ], Response::HTTP_NOT_FOUND)
            );
        }

        return $this->repository->findById($id);
    }
}
