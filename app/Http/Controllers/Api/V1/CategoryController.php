<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->list($request->only(['is_active', 'search']));

        return response()->json([
            'message' => 'Categories list',
            'data' => $categories,
        ]);
    }

    public function active(): JsonResponse
    {
        return response()->json([
            'message' => 'Active categories list',
            'data' => $this->categoryService->listActive(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'message' => 'Category details',
            'data' => $this->categoryService->find($id),
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return response()->json([
            'message' => 'Category created',
            'data' => $category,
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->categoryService->update($id, $request->validated());

        return response()->json([
            'message' => 'Category updated',
            'data' => $category,
        ]);
    }

    public function activate(int $id): JsonResponse
    {
        return response()->json([
            'message' => 'Category activated',
            'data' => $this->categoryService->setActive($id, true),
        ]);
    }

    public function deactivate(int $id): JsonResponse
    {
        return response()->json([
            'message' => 'Category deactivated',
            'data' => $this->categoryService->setActive($id, false),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->categoryService->delete($id);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Category deleted',
        ]);
    }
}
