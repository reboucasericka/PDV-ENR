<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddItemRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

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
        $order = $this->orderService->createOrder($request->validated());

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
        $order = $this->orderService->getOrderDetails($id);

        return response()->json([
            'message' => 'Order details fetched successfully.',
            'data' => $order,
        ]);
    }
}
