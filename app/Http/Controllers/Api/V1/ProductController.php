<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all') || $request->has('is_active')) {
            $products = $this->productService->list(
                $request->only(['is_active', 'category_id', 'search', 'is_favorite'])
            );
        } else {
            $categoryId = $request->filled('category_id') ? (int) $request->query('category_id') : null;
            $search = $request->filled('search') ? (string) $request->query('search') : null;
            $products = $this->productService->listActiveForPos($categoryId, $search);
        }

        return response()->json([
            'message' => 'Products list',
            'data' => $products,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'message' => 'Product details',
            'data' => $this->productService->find($id),
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return response()->json([
            'message' => 'Product created',
            'data' => $product,
        ], 201);
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->update($id, $request->validated());

        return response()->json([
            'message' => 'Product updated',
            'data' => $product,
        ]);
    }

    public function activate(int $id): JsonResponse
    {
        return response()->json([
            'message' => 'Product activated',
            'data' => $this->productService->setActive($id, true),
        ]);
    }

    public function deactivate(int $id): JsonResponse
    {
        return response()->json([
            'message' => 'Product deactivated',
            'data' => $this->productService->setActive($id, false),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productService->delete($id);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Product deleted',
        ]);
    }
}
