<?php

namespace App\Services;

use App\Enums\CashStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\CashRegister;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CashService
{
    public function openCash(int $userId, int $storeId, float $openingBalance): CashRegister
    {
        if ($openingBalance < 0) {
            throw new InvalidArgumentException('O saldo inicial nao pode ser negativo.');
        }

        $store = Store::query()->find($storeId);

        if (! $store) {
            throw new InvalidArgumentException('Loja nao encontrada.');
        }

        if (! $store->is_active) {
            throw new InvalidArgumentException('Loja inativa. Nao e possivel abrir o caixa.');
        }

        return DB::transaction(function () use ($userId, $storeId, $openingBalance) {
            $existing = CashRegister::query()
                ->with(['store', 'user'])
                ->where('user_id', $userId)
                ->where('status', CashStatus::OPEN)
                ->lockForUpdate()
                ->latest('opened_at')
                ->first();

            // Idempotente: reutiliza o caixa OPEN do operador (ex.: reentrada apos login).
            if ($existing) {
                return $existing;
            }

            return CashRegister::query()->create([
                'store_id' => $storeId,
                'user_id' => $userId,
                'opening_balance' => $openingBalance,
                'closing_balance' => null,
                'opened_at' => now(),
                'closed_at' => null,
                'status' => CashStatus::OPEN,
            ])->load(['store', 'user']);
        });
    }

    public function closeCash(int $userId): CashRegister
    {
        $cash = $this->currentCash($userId);

        if (! $cash) {
            throw new InvalidArgumentException('Nao ha caixa aberto para fechar.');
        }

        if ($cash->status !== CashStatus::OPEN) {
            throw new InvalidArgumentException('Este caixa ja esta fechado.');
        }

        $expectedBalance = $this->calculateExpectedBalance($cash);

        return DB::transaction(function () use ($cash, $expectedBalance) {
            $cash->update([
                'closing_balance' => $expectedBalance,
                'closed_at' => now(),
                'status' => CashStatus::CLOSED,
            ]);

            return $cash->fresh(['store', 'user']);
        });
    }

    public function currentCash(int $userId): ?CashRegister
    {
        return CashRegister::query()
            ->with(['store', 'user'])
            ->where('user_id', $userId)
            ->where('status', CashStatus::OPEN)
            ->latest('opened_at')
            ->first();
    }

    public function hasOpenCash(int $userId): bool
    {
        return CashRegister::query()
            ->where('user_id', $userId)
            ->where('status', CashStatus::OPEN)
            ->exists();
    }

    public function calculateSales(CashRegister $cashRegister): float
    {
        return (float) Order::query()
            ->where('cash_register_id', $cashRegister->id)
            ->where('status', OrderStatus::PAID)
            ->sum('total');
    }

    public function countOrders(CashRegister $cashRegister): int
    {
        return (int) Order::query()
            ->where('cash_register_id', $cashRegister->id)
            ->where('status', OrderStatus::PAID)
            ->count();
    }

    /**
     * @return list<array{method: string, label: string, total: float}>
     */
    public function calculatePaymentTotals(CashRegister $cashRegister): array
    {
        $rows = Order::query()
            ->selectRaw('payment_method, SUM(total) as total')
            ->where('cash_register_id', $cashRegister->id)
            ->where('status', OrderStatus::PAID)
            ->groupBy('payment_method')
            ->get();

        $totals = [];

        foreach ($rows as $row) {
            $rawMethod = $row->getAttributes()['payment_method'] ?? null;
            $method = $rawMethod instanceof PaymentMethod
                ? $rawMethod
                : (PaymentMethod::tryFrom((string) $rawMethod) ?? PaymentMethod::CASH);

            $totals[] = [
                'method' => $method->value,
                'label' => $method->label(),
                'total' => (float) $row->total,
            ];
        }

        if ($totals === []) {
            $totals[] = [
                'method' => PaymentMethod::CASH->value,
                'label' => PaymentMethod::CASH->label(),
                'total' => 0.0,
            ];
        }

        return $totals;
    }

    public function calculateExpectedBalance(CashRegister $cashRegister): float
    {
        $cashSales = (float) Order::query()
            ->where('cash_register_id', $cashRegister->id)
            ->where('status', OrderStatus::PAID)
            ->where('payment_method', PaymentMethod::CASH)
            ->sum('total');

        return round((float) $cashRegister->opening_balance + $cashSales, 2);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildClosingSummary(CashRegister $cashRegister): array
    {
        $salesTotal = $this->calculateSales($cashRegister);
        $expectedBalance = $this->calculateExpectedBalance($cashRegister);
        $closedAt = $cashRegister->status === CashStatus::CLOSED
            ? $cashRegister->closed_at
            : now();

        return [
            'opening_balance' => (float) $cashRegister->opening_balance,
            'orders_count' => $this->countOrders($cashRegister),
            'sales_total' => $salesTotal,
            'payment_totals' => $this->calculatePaymentTotals($cashRegister),
            'expected_balance' => $expectedBalance,
            'opened_at' => optional($cashRegister->opened_at)?->toIso8601String(),
            'closed_at' => optional($closedAt)?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function formatCurrentPayload(?CashRegister $cashRegister): ?array
    {
        if (! $cashRegister) {
            return null;
        }

        $summary = $this->buildClosingSummary($cashRegister);

        return [
            'id' => $cashRegister->id,
            'status' => $cashRegister->status->value,
            'store' => $cashRegister->store ? [
                'id' => $cashRegister->store->id,
                'name' => $cashRegister->store->name,
                'city' => $cashRegister->store->city,
            ] : null,
            'operator' => $cashRegister->user ? [
                'id' => $cashRegister->user->id,
                'name' => $cashRegister->user->name,
            ] : null,
            'opening_balance' => $summary['opening_balance'],
            'closing_balance' => $cashRegister->closing_balance !== null
                ? (float) $cashRegister->closing_balance
                : null,
            'opened_at' => $summary['opened_at'],
            'closed_at' => $cashRegister->closed_at?->toIso8601String(),
            'sales_total' => $summary['sales_total'],
            'orders_count' => $summary['orders_count'],
            'payment_totals' => $summary['payment_totals'],
            'expected_balance' => $summary['expected_balance'],
        ];
    }

    /**
     * Lista paginada do histórico de caixas (filtros + pesquisa).
     *
     * @param  array{
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     store_id?: int|null,
     *     user_id?: int|null,
     *     status?: string|null,
     *     search?: string|null,
     *     per_page?: int|null,
     *     page?: int|null
     * }  $filters
     */
    public function listHistory(array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min((int) ($filters['per_page'] ?? 15), 50));

        $paginator = $this->historyQuery($filters)
            ->paginate($perPage)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(
                fn (CashRegister $cash) => $this->formatHistoryItem($cash)
            )
        );

        return $paginator;
    }

    public function findCash(int $id): CashRegister
    {
        $cash = CashRegister::query()
            ->with(['store', 'user'])
            ->find($id);

        if (! $cash) {
            throw new InvalidArgumentException('Caixa nao encontrado.');
        }

        return $cash;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatHistoryItem(CashRegister $cashRegister): array
    {
        $ordersCount = isset($cashRegister->orders_count)
            ? (int) $cashRegister->orders_count
            : $this->countOrders($cashRegister);

        $salesTotal = isset($cashRegister->sales_total)
            ? (float) $cashRegister->sales_total
            : $this->calculateSales($cashRegister);

        return [
            'id' => $cashRegister->id,
            'store' => $cashRegister->store ? [
                'id' => $cashRegister->store->id,
                'name' => $cashRegister->store->name,
            ] : null,
            'operator' => $cashRegister->user ? [
                'id' => $cashRegister->user->id,
                'name' => $cashRegister->user->name,
            ] : null,
            'opened_at' => optional($cashRegister->opened_at)?->toIso8601String(),
            'closed_at' => optional($cashRegister->closed_at)?->toIso8601String(),
            'orders_count' => $ordersCount,
            'sales_total' => round($salesTotal, 2),
            'status' => $cashRegister->status->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatHistoryDetail(CashRegister $cashRegister): array
    {
        $summary = $this->buildClosingSummary($cashRegister);
        $duration = $this->calculateOpenDuration($cashRegister);

        $orders = Order::query()
            ->where('cash_register_id', $cashRegister->id)
            ->where('status', OrderStatus::PAID)
            ->orderBy('created_at')
            ->get()
            ->map(function (Order $order) {
                $method = $order->payment_method instanceof PaymentMethod
                    ? $order->payment_method
                    : PaymentMethod::CASH;

                return [
                    'id' => $order->id,
                    'reference' => $order->reference,
                    'created_at' => optional($order->created_at)?->toIso8601String(),
                    'payment_method' => $method->value,
                    'payment_label' => $method->label(),
                    'total' => (float) $order->total,
                ];
            })
            ->values()
            ->all();

        return [
            'id' => $cashRegister->id,
            'status' => $cashRegister->status->value,
            'store' => $cashRegister->store ? [
                'id' => $cashRegister->store->id,
                'name' => $cashRegister->store->name,
                'city' => $cashRegister->store->city,
            ] : null,
            'operator' => $cashRegister->user ? [
                'id' => $cashRegister->user->id,
                'name' => $cashRegister->user->name,
            ] : null,
            'opening_balance' => $summary['opening_balance'],
            'closing_balance' => $cashRegister->closing_balance !== null
                ? (float) $cashRegister->closing_balance
                : ($cashRegister->status === CashStatus::CLOSED
                    ? $summary['expected_balance']
                    : null),
            'opened_at' => $summary['opened_at'],
            'closed_at' => $cashRegister->closed_at?->toIso8601String(),
            'duration_seconds' => $duration['seconds'],
            'duration_label' => $duration['label'],
            'orders_count' => $summary['orders_count'],
            'sales_total' => $summary['sales_total'],
            'payment_totals' => $this->normalizePaymentTotals($summary['payment_totals']),
            'expected_balance' => $summary['expected_balance'],
            'orders' => $orders,
        ];
    }

    /**
     * Opções de filtro (lojas e operadores que já tiveram caixa).
     *
     * @return array{stores: list<array{id: int, name: string}>, operators: list<array{id: int, name: string}>}
     */
    public function historyFilterOptions(): array
    {
        $storeIds = CashRegister::query()
            ->distinct()
            ->pluck('store_id')
            ->filter()
            ->all();

        $userIds = CashRegister::query()
            ->distinct()
            ->pluck('user_id')
            ->filter()
            ->all();

        $stores = Store::query()
            ->whereIn('id', $storeIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Store $store) => [
                'id' => $store->id,
                'name' => $store->name,
            ])
            ->values()
            ->all();

        $operators = User::query()
            ->whereIn('id', $userIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
            ])
            ->values()
            ->all();

        return [
            'stores' => $stores,
            'operators' => $operators,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function historyQuery(array $filters): Builder
    {
        $query = CashRegister::query()
            ->with(['store', 'user'])
            ->withCount([
                'orders as orders_count' => fn (Builder $q) => $q->where('status', OrderStatus::PAID),
            ])
            ->withSum([
                'orders as sales_total' => fn (Builder $q) => $q->where('status', OrderStatus::PAID),
            ], 'total')
            ->latest('opened_at');

        if (! empty($filters['date_from'])) {
            $query->whereDate('opened_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('opened_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', (int) $filters['store_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['status'])) {
            $status = CashStatus::tryFrom(strtoupper((string) $filters['status']));
            if ($status) {
                $query->where('status', $status);
            }
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search) {
                if (ctype_digit($search)) {
                    $builder->orWhere('id', (int) $search);
                }

                $builder
                    ->orWhereHas('store', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    /**
     * @return array{seconds: int, label: string}
     */
    private function calculateOpenDuration(CashRegister $cashRegister): array
    {
        if (! $cashRegister->opened_at) {
            return ['seconds' => 0, 'label' => '—'];
        }

        $end = $cashRegister->closed_at ?? now();
        $seconds = max(0, $cashRegister->opened_at->diffInSeconds($end));

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours > 0) {
            $label = sprintf('%dh %02dm', $hours, $minutes);
        } else {
            $label = sprintf('%dm', $minutes);
        }

        return [
            'seconds' => $seconds,
            'label' => $label,
        ];
    }

    /**
     * Garante as 4 formas de pagamento no detalhe do histórico.
     *
     * @param  list<array{method: string, label: string, total: float}>  $totals
     * @return list<array{method: string, label: string, total: float}>
     */
    private function normalizePaymentTotals(array $totals): array
    {
        $byMethod = [];
        foreach ($totals as $row) {
            $byMethod[$row['method']] = (float) $row['total'];
        }

        $normalized = [];
        foreach (PaymentMethod::posMethods() as $method) {
            $normalized[] = [
                'method' => $method->value,
                'label' => $method->label(),
                'total' => round($byMethod[$method->value] ?? 0.0, 2),
            ];
        }

        return $normalized;
    }
}
