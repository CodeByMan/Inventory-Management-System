<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockRequest;
use App\Http\Resources\StockMovementResource;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    public function stockIn(StockRequest $request): JsonResponse
    {
        $data = $request->validated();
        $movement = $this->stockService->stockIn(
            $data['product_id'],
            $data['quantity'],
            $request->user()->id,
            $data['note'] ?? null
        );

        return (new StockMovementResource($movement))->response()->setStatusCode(201);
    }

    public function stockOut(StockRequest $request): JsonResponse
    {
        $data = $request->validated();
        $movement = $this->stockService->stockOut(
            $data['product_id'],
            $data['quantity'],
            $request->user()->id,
            $data['note'] ?? null
        );

        return (new StockMovementResource($movement))->response()->setStatusCode(201);
    }

    public function reverse(Request $request, StockMovement $stockMovement): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);
        $movement = $this->stockService->reverse(
            $stockMovement->id,
            $request->user()->id,
            $data['reason']
        );

        return (new StockMovementResource($movement))->response()->setStatusCode(201);
    }
}
