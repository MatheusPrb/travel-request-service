<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTravelOrderRequest;
use App\Services\TravelOrderService;

class TravelOrderController extends Controller
{
    private  $service;

    public function __construct(TravelOrderService $service)
    {
        $this->service = $service;
    }

    public function store(CreateTravelOrderRequest $request)
    {   
        try {
            $order = $this->service->create($request->validated());

            return response()->json($order, 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
