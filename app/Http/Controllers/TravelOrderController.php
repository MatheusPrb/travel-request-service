<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderStatusRequest;
use App\Http\Resources\TravelOrderResource;
use App\Services\TravelOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelOrderController extends Controller
{
    private TravelOrderService $service;

    public function __construct(TravelOrderService $service)
    {
        $this->service = $service;
    }

    public function store(CreateTravelOrderRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), [
            'user_id' => auth('api')->id()
        ]);

        $order = $this->service->create($data);

        return response()->json(new TravelOrderResource($order), 201);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->service->listByUser(auth('api')->id(), $request->all());

        return response()->json(TravelOrderResource::collection($orders));
    }

    public function show(string $id): JsonResponse
    {
        $order = $this->service->findById($id, auth('api')->id());

        return response()->json(new TravelOrderResource($order));
    }

    public function updateStatus(string $id, UpdateTravelOrderStatusRequest $request): JsonResponse
    {
        $order = $this->service->updateStatus($id, $request->validated()['status']);

        return response()->json(new TravelOrderResource($order));
    }
}
