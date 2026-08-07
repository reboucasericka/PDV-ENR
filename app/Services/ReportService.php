<?php

namespace App\Services;

use App\Enums\CashStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\CashRegister;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    public function __construct(private readonly CashService $cashService) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function summary(array $filters = []): array
    {
        $orders = $this->filteredOrdersQuery($filters)->get();
        $salesTotal = round((float) $orders->sum(fn (Order $order) => (float) $order->total), 2);
        $ordersCount = $orders->count();
        $averageTicket = $ordersCount > 0 ? round($salesTotal / $ordersCount, 2) : 0.0;

        $topProduct = collect($this->products($filters))->first();
        $topCategory = collect($this->categories($filters))->first();
        $topPayment = collect($this->payments($filters))
            ->sortByDesc('total')
            ->first();

        return [
            'sales_total' => $salesTotal,
            'orders_count' => $ordersCount,
            'average_ticket' => $averageTicket,
            'top_product' => $topProduct ? [
                'name' => $topProduct['name'],
                'quantity' => $topProduct['quantity'],
                'revenue' => $topProduct['revenue'],
            ] : null,
            'top_category' => $topCategory ? [
                'name' => $topCategory['name'],
                'quantity' => $topCategory['quantity'],
                'revenue' => $topCategory['revenue'],
            ] : null,
            'top_payment' => $topPayment && ($topPayment['total'] ?? 0) > 0 ? [
                'method' => $topPayment['method'],
                'label' => $topPayment['label'],
                'total' => $topPayment['total'],
                'percent' => $topPayment['percent'],
            ] : null,
            'filters' => $this->filterOptions(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function sales(array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min((int) ($filters['per_page'] ?? 15), 50));

        $query = $this->filteredOrdersQuery($filters)
            ->with(['cashRegister.store:id,name', 'cashRegister.user:id,name'])
            ->latest('created_at');

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $builder) use ($search) {
                if (ctype_digit($search)) {
                    $builder->orWhere('id', (int) $search);
                }

                $builder
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhereHas('cashRegister.store', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('cashRegister.user', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (Order $order) => $this->formatSaleRow($order))
        );

        return $paginator;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array{product_id: int|null, name: string, quantity: int, revenue: float, average_price: float}>
     */
    public function products(array $filters = []): array
    {
        $items = $this->filteredOrderItems($filters);

        return $items
            ->groupBy('product_id')
            ->map(function (Collection $group) {
                $first = $group->first();
                $quantity = (int) $group->sum('quantity');
                $revenue = round((float) $group->sum(fn (OrderItem $item) => (float) $item->line_total), 2);

                return [
                    'product_id' => $first?->product_id,
                    'name' => $first?->product?->name ?? 'Produto removido',
                    'quantity' => $quantity,
                    'revenue' => $revenue,
                    'average_price' => $quantity > 0 ? round($revenue / $quantity, 2) : 0.0,
                ];
            })
            ->sortByDesc('quantity')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array{category_id: int|null, name: string, quantity: int, revenue: float}>
     */
    public function categories(array $filters = []): array
    {
        $items = $this->filteredOrderItems($filters);

        return $items
            ->groupBy(fn (OrderItem $item) => $item->product?->category_id ?? 0)
            ->map(function (Collection $group) {
                $first = $group->first();
                $category = $first?->product?->category;

                return [
                    'category_id' => $category?->id,
                    'name' => $category?->name ?? 'Sem categoria',
                    'quantity' => (int) $group->sum('quantity'),
                    'revenue' => round((float) $group->sum(fn (OrderItem $item) => (float) $item->line_total), 2),
                ];
            })
            ->sortByDesc('quantity')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array{method: string, label: string, total: float, count: int, percent: float}>
     */
    public function payments(array $filters = []): array
    {
        $orders = $this->filteredOrdersQuery($filters)->get(['payment_method', 'total']);
        $grandTotal = (float) $orders->sum(fn (Order $order) => (float) $order->total);

        $rows = [];
        foreach (PaymentMethod::posMethods() as $method) {
            $rows[$method->value] = [
                'method' => $method->value,
                'label' => $method->label(),
                'total' => 0.0,
                'count' => 0,
                'percent' => 0.0,
            ];
        }

        foreach ($orders as $order) {
            $method = $order->payment_method instanceof PaymentMethod
                ? $order->payment_method
                : (PaymentMethod::tryFrom((string) $order->payment_method) ?? PaymentMethod::CASH);

            $key = $method->value;
            $rows[$key]['total'] += (float) $order->total;
            $rows[$key]['count']++;
        }

        return array_values(array_map(function (array $row) use ($grandTotal) {
            $row['total'] = round($row['total'], 2);
            $row['percent'] = $grandTotal > 0
                ? round(($row['total'] / $grandTotal) * 100, 2)
                : 0.0;

            return $row;
        }, $rows));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function cashRegisters(array $filters = []): LengthAwarePaginator
    {
        $perPage = max(1, min((int) ($filters['per_page'] ?? 15), 50));

        $query = CashRegister::query()
            ->with(['store:id,name', 'user:id,name'])
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

        $paginator = $query->paginate($perPage)->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()->map(function (CashRegister $cash) {
                $salesTotal = $this->cashService->calculateSales($cash);
                $expectedBalance = $this->cashService->calculateExpectedBalance($cash);

                return [
                    'id' => $cash->id,
                    'store' => $cash->store ? [
                        'id' => $cash->store->id,
                        'name' => $cash->store->name,
                    ] : null,
                    'operator' => $cash->user ? [
                        'id' => $cash->user->id,
                        'name' => $cash->user->name,
                    ] : null,
                    'opening_balance' => (float) $cash->opening_balance,
                    'sales_total' => round($salesTotal, 2),
                    'expected_balance' => round($expectedBalance, 2),
                    'closing_balance' => $cash->closing_balance !== null
                        ? (float) $cash->closing_balance
                        : null,
                    'opened_at' => optional($cash->opened_at)?->toIso8601String(),
                    'closed_at' => optional($cash->closed_at)?->toIso8601String(),
                    'status' => $cash->status->value,
                ];
            })
        );

        return $paginator;
    }

    /**
     * @return array{stores: list<array{id: int, name: string}>, operators: list<array{id: int, name: string}>, payment_methods: list<array{value: string, label: string}>}
     */
    public function filterOptions(): array
    {
        return [
            'stores' => Store::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Store $store) => [
                    'id' => $store->id,
                    'name' => $store->name,
                ])
                ->values()
                ->all(),
            'operators' => User::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                ])
                ->values()
                ->all(),
            'payment_methods' => array_map(
                fn (PaymentMethod $method) => [
                    'value' => $method->value,
                    'label' => $method->label(),
                ],
                PaymentMethod::posMethods()
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filteredOrdersQuery(array $filters): Builder
    {
        $query = Order::query()->where('status', OrderStatus::PAID);

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['payment_method'])) {
            $method = PaymentMethod::tryFrom((string) $filters['payment_method']);
            if ($method) {
                $query->where('payment_method', $method);
            }
        }

        if (! empty($filters['store_id']) || ! empty($filters['user_id'])) {
            $query->whereHas('cashRegister', function (Builder $builder) use ($filters) {
                if (! empty($filters['store_id'])) {
                    $builder->where('store_id', (int) $filters['store_id']);
                }
                if (! empty($filters['user_id'])) {
                    $builder->where('user_id', (int) $filters['user_id']);
                }
            });
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, OrderItem>
     */
    private function filteredOrderItems(array $filters): Collection
    {
        return OrderItem::query()
            ->with(['product:id,name,category_id', 'product.category:id,name'])
            ->whereHas('order', function (Builder $builder) use ($filters) {
                $builder->where('status', OrderStatus::PAID);

                if (! empty($filters['date_from'])) {
                    $builder->whereDate('created_at', '>=', $filters['date_from']);
                }

                if (! empty($filters['date_to'])) {
                    $builder->whereDate('created_at', '<=', $filters['date_to']);
                }

                if (! empty($filters['payment_method'])) {
                    $method = PaymentMethod::tryFrom((string) $filters['payment_method']);
                    if ($method) {
                        $builder->where('payment_method', $method);
                    }
                }

                if (! empty($filters['store_id']) || ! empty($filters['user_id'])) {
                    $builder->whereHas('cashRegister', function (Builder $cashQuery) use ($filters) {
                        if (! empty($filters['store_id'])) {
                            $cashQuery->where('store_id', (int) $filters['store_id']);
                        }
                        if (! empty($filters['user_id'])) {
                            $cashQuery->where('user_id', (int) $filters['user_id']);
                        }
                    });
                }
            })
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatSaleRow(Order $order): array
    {
        $method = $order->payment_method instanceof PaymentMethod
            ? $order->payment_method
            : PaymentMethod::CASH;

        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'created_at' => optional($order->created_at)?->toIso8601String(),
            'operator' => $order->cashRegister?->user ? [
                'id' => $order->cashRegister->user->id,
                'name' => $order->cashRegister->user->name,
            ] : null,
            'store' => $order->cashRegister?->store ? [
                'id' => $order->cashRegister->store->id,
                'name' => $order->cashRegister->store->name,
            ] : null,
            'payment_method' => $method->value,
            'payment_label' => $method->label(),
            'total' => (float) $order->total,
            'status' => $order->status instanceof OrderStatus
                ? $order->status->value
                : (string) $order->status,
        ];
    }
}
