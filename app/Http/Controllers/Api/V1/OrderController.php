<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddItemRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(): JsonResponse
    {
        $orders = $this->orderService->listOrders();

        return response()->json([
            'message' => 'Orders fetched successfully.',
            'data' => $orders,
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => $order,
        ], 201);
    }

    public function addItem(AddItemRequest $request, int $orderId): JsonResponse
    {
        $payload = $request->validated();
        $order = $this->orderService->addItem(
            $orderId,
            (int) $payload['product_id'],
            (int) $payload['quantity']
        );

        return response()->json([
            'message' => 'Item added to order successfully.',
            'data' => $order,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderDetails($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            return response()->json([
                'message' => 'Pedido nao encontrado.',
            ], 404);
        }

        return response()->json([
            'message' => 'Order details fetched successfully.',
            'data' => $order,
        ]);
    }
}
