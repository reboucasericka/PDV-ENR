<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashController extends Controller
{
    public function open(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'initial_balance' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $balance = isset($validated['initial_balance'])
            ? (float) $validated['initial_balance']
            : 0.0;

        session([
            'cash_open' => true,
            'cash_initial_balance' => $balance,
            'cash_opened_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'message' => 'Caixa aberto.',
            'data' => $this->cashPayload(),
        ]);
    }

    public function current(): JsonResponse
    {
        return response()->json([
            'message' => 'Estado do caixa.',
            'data' => $this->cashPayload(),
        ]);
    }

    /**
     * @return array{status: string, initial_balance: float|null, opened_at: string|null}
     */
    private function cashPayload(): array
    {
        $open = (bool) session('cash_open', false);

        return [
            'status' => $open ? 'open' : 'closed',
            'initial_balance' => $open ? (float) session('cash_initial_balance', 0) : null,
            'opened_at' => $open ? session('cash_opened_at') : null,
        ];
    }
}
