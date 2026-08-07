<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function summary(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request);

        return response()->json([
            'message' => 'Relatorio resumo.',
            'data' => $this->reportService->summary($filters),
        ]);
    }

    public function sales(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request, withSearch: true, withPagination: true);
        $paginator = $this->reportService->sales($filters);

        return response()->json([
            'message' => 'Relatorio de vendas.',
            'data' => $paginator->items(),
            'meta' => $this->paginationMeta($paginator),
            'filters' => $this->reportService->filterOptions(),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request);

        return response()->json([
            'message' => 'Relatorio de produtos.',
            'data' => $this->reportService->products($filters),
            'filters' => $this->reportService->filterOptions(),
        ]);
    }

    public function categories(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request);

        return response()->json([
            'message' => 'Relatorio de categorias.',
            'data' => $this->reportService->categories($filters),
            'filters' => $this->reportService->filterOptions(),
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request);

        return response()->json([
            'message' => 'Relatorio de pagamentos.',
            'data' => $this->reportService->payments($filters),
            'filters' => $this->reportService->filterOptions(),
        ]);
    }

    public function cashRegisters(Request $request): JsonResponse
    {
        $filters = $this->validatedFilters($request, withPagination: true, withCashStatus: true);
        $paginator = $this->reportService->cashRegisters($filters);

        return response()->json([
            'message' => 'Relatorio de caixas.',
            'data' => $paginator->items(),
            'meta' => $this->paginationMeta($paginator),
            'filters' => $this->reportService->filterOptions(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedFilters(
        Request $request,
        bool $withSearch = false,
        bool $withPagination = false,
        bool $withCashStatus = false,
    ): array {
        $rules = [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'payment_method' => ['nullable', 'string', 'in:cash,card,mbway,multibanco'],
        ];

        if ($withSearch) {
            $rules['search'] = ['nullable', 'string', 'max:100'];
        }

        if ($withPagination) {
            $rules['page'] = ['nullable', 'integer', 'min:1'];
            $rules['per_page'] = ['nullable', 'integer', 'min:1', 'max:50'];
        }

        if ($withCashStatus) {
            $rules['status'] = ['nullable', 'string', 'in:OPEN,CLOSED,open,closed'];
        }

        return $request->validate($rules);
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     * @return array{current_page: int, last_page: int, per_page: int, total: int}
     */
    private function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
