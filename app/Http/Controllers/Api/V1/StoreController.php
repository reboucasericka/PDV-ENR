<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    public function __construct(private readonly StoreService $storeService) {}

    public function index(): JsonResponse
    {
        $stores = $this->storeService->listStores();

        return response()->json([
            'message' => 'Stores list',
            'data' => $stores,
        ]);
    }

    public function active(): JsonResponse
    {
        $stores = $this->storeService->listActiveStores();

        return response()->json([
            'message' => 'Active stores list',
            'data' => $stores,
        ]);
    }
}
