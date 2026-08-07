<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CashService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CashController extends Controller
{
    public function __construct(private readonly CashService $cashService) {}

    public function open(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $cash = $this->cashService->openCash(
                (int) $request->user()->id,
                (int) $validated['store_id'],
                (float) $validated['opening_balance']
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Caixa aberto.',
            'data' => $this->cashService->formatCurrentPayload($cash),
        ], 201);
    }

    public function current(Request $request): JsonResponse
    {
        $cash = $this->cashService->currentCash((int) $request->user()->id);

        return response()->json([
            'message' => 'Estado do caixa.',
            'data' => $this->cashService->formatCurrentPayload($cash) ?? [
                'status' => 'CLOSED',
                'id' => null,
                'store' => null,
                'operator' => null,
                'opening_balance' => null,
                'closing_balance' => null,
                'opened_at' => null,
                'closed_at' => null,
                'sales_total' => 0,
                'orders_count' => 0,
                'payment_totals' => [],
                'expected_balance' => null,
            ],
        ]);
    }

    public function close(Request $request): JsonResponse
    {
        try {
            $cash = $this->cashService->closeCash((int) $request->user()->id);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Caixa fechado.',
            'data' => $this->cashService->formatCurrentPayload($cash),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'string', 'in:OPEN,CLOSED,open,closed'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $paginator = $this->cashService->listHistory($filters);

        return response()->json([
            'message' => 'Historico de caixas.',
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'filters' => $this->cashService->historyFilterOptions(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $cash = $this->cashService->findCash($id);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        }

        return response()->json([
            'message' => 'Detalhe do caixa.',
            'data' => $this->cashService->formatHistoryDetail($cash),
        ]);
    }
}
