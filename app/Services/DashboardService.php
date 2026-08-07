<?php

namespace App\Services;

use App\Enums\CashStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(private readonly CashService $cashService) {}

    /**
     * Agrega todos os indicadores do dashboard administrativo.
     *
     * @return array<string, mixed>
     */
    public function getDashboard(?int $userId = null): array
    {
        $today = Carbon::today();
        $paidToday = $this->paidOrdersToday($today);

        $salesCount = $paidToday->count();
        $revenueToday = round((float) $paidToday->sum(fn (Order $order) => (float) $order->total), 2);
        $averageTicket = $salesCount > 0 ? round($revenueToday / $salesCount, 2) : 0.0;

        $cashStatus = $userId
            ? ($this->cashService->hasOpenCash($userId) ? CashStatus::OPEN->value : CashStatus::CLOSED->value)
            : CashStatus::CLOSED->value;

        return [
            'cards' => [
                'sales_count_today' => $salesCount,
                'revenue_today' => $revenueToday,
                'average_ticket' => $averageTicket,
                'cash_status' => $cashStatus,
            ],
            'charts' => [
                'sales_by_hour' => $this->salesByHour($paidToday),
                'payment_methods' => $this->paymentMethodTotals($paidToday),
                'top_products' => $this->topProducts($today, 10),
                'top_categories' => $this->topCategories($today, 10),
            ],
            'lists' => [
                'recent_orders' => $this->recentOrders(8),
                'recent_closed_cash' => $this->recentClosedCash(8),
            ],
            'indicators' => [
                'products_active' => Product::query()->where('is_active', true)->count(),
                'products_inactive' => Product::query()->where('is_active', false)->count(),
                'categories' => Category::query()->count(),
                'orders_today' => $salesCount,
                'revenue_today' => $revenueToday,
            ],
        ];
    }

    /**
     * @return Collection<int, Order>
     */
    private function paidOrdersToday(Carbon $today): Collection
    {
        return Order::query()
            ->where('status', OrderStatus::PAID)
            ->whereDate('created_at', $today)
            ->get(['id', 'total', 'payment_method', 'created_at', 'reference', 'cash_register_id']);
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return list<array{hour: int, label: string, total: float, count: int}>
     */
    private function salesByHour(Collection $orders): array
    {
        $buckets = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $buckets[$hour] = [
                'hour' => $hour,
                'label' => sprintf('%02d:00', $hour),
                'total' => 0.0,
                'count' => 0,
            ];
        }

        foreach ($orders as $order) {
            $hour = (int) $order->created_at->format('G');
            $buckets[$hour]['total'] += (float) $order->total;
            $buckets[$hour]['count']++;
        }

        return array_values(array_map(function (array $bucket) {
            $bucket['total'] = round($bucket['total'], 2);

            return $bucket;
        }, $buckets));
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return list<array{method: string, label: string, total: float, count: int}>
     */
    private function paymentMethodTotals(Collection $orders): array
    {
        $totals = [];
        foreach (PaymentMethod::posMethods() as $method) {
            $totals[$method->value] = [
                'method' => $method->value,
                'label' => $method->label(),
                'total' => 0.0,
                'count' => 0,
            ];
        }

        foreach ($orders as $order) {
            $method = $order->payment_method instanceof PaymentMethod
                ? $order->payment_method
                : (PaymentMethod::tryFrom((string) $order->payment_method) ?? PaymentMethod::CASH);

            $key = $method->value;
            $totals[$key]['total'] += (float) $order->total;
            $totals[$key]['count']++;
        }

        return array_values(array_map(function (array $row) {
            $row['total'] = round($row['total'], 2);

            return $row;
        }, $totals));
    }

    /**
     * @return list<array{product_id: int|null, name: string, quantity: int, total: float}>
     */
    private function topProducts(Carbon $today, int $limit = 10): array
    {
        $items = OrderItem::query()
            ->with('product:id,name')
            ->whereHas('order', function ($query) use ($today) {
                $query
                    ->where('status', OrderStatus::PAID)
                    ->whereDate('created_at', $today);
            })
            ->get(['product_id', 'quantity', 'line_total']);

        return $items
            ->groupBy('product_id')
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'product_id' => $first?->product_id,
                    'name' => $first?->product?->name ?? 'Produto removido',
                    'quantity' => (int) $group->sum('quantity'),
                    'total' => round((float) $group->sum(fn (OrderItem $item) => (float) $item->line_total), 2),
                ];
            })
            ->sortByDesc('quantity')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return list<array{category_id: int|null, name: string, quantity: int, total: float}>
     */
    private function topCategories(Carbon $today, int $limit = 10): array
    {
        $items = OrderItem::query()
            ->with('product.category:id,name')
            ->whereHas('order', function ($query) use ($today) {
                $query
                    ->where('status', OrderStatus::PAID)
                    ->whereDate('created_at', $today);
            })
            ->get();

        return $items
            ->groupBy(fn (OrderItem $item) => $item->product?->category_id ?? 0)
            ->map(function (Collection $group) {
                $first = $group->first();
                $category = $first?->product?->category;

                return [
                    'category_id' => $category?->id,
                    'name' => $category?->name ?? 'Sem categoria',
                    'quantity' => (int) $group->sum('quantity'),
                    'total' => round((float) $group->sum(fn (OrderItem $item) => (float) $item->line_total), 2),
                ];
            })
            ->sortByDesc('quantity')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentOrders(int $limit = 8): array
    {
        return Order::query()
            ->with(['cashRegister.store:id,name'])
            ->where('status', OrderStatus::PAID)
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function (Order $order) {
                $method = $order->payment_method instanceof PaymentMethod
                    ? $order->payment_method
                    : PaymentMethod::CASH;

                return [
                    'id' => $order->id,
                    'reference' => $order->reference,
                    'total' => (float) $order->total,
                    'payment_method' => $method->value,
                    'payment_label' => $method->label(),
                    'store_name' => $order->cashRegister?->store?->name,
                    'created_at' => optional($order->created_at)?->toIso8601String(),
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentClosedCash(int $limit = 8): array
    {
        return CashRegister::query()
            ->with(['store:id,name', 'user:id,name'])
            ->where('status', CashStatus::CLOSED)
            ->latest('closed_at')
            ->limit($limit)
            ->get()
            ->map(function (CashRegister $cash) {
                return [
                    'id' => $cash->id,
                    'store_name' => $cash->store?->name,
                    'operator_name' => $cash->user?->name,
                    'opening_balance' => (float) $cash->opening_balance,
                    'closing_balance' => $cash->closing_balance !== null
                        ? (float) $cash->closing_balance
                        : null,
                    'opened_at' => optional($cash->opened_at)?->toIso8601String(),
                    'closed_at' => optional($cash->closed_at)?->toIso8601String(),
                    'status' => $cash->status->value,
                ];
            })
            ->all();
    }
}
