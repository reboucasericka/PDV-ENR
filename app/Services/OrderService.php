<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    public function createOrder(array $data): Order
    {
        $items = $data['items'] ?? [];

        if (empty($items)) {
            throw new InvalidArgumentException('Order must have at least one item.');
        }

        return DB::transaction(function () use ($data, $items) {
            $order = Order::create([
                'reference' => $data['reference'] ?? $this->generateReference(),
                'status' => $data['status'] ?? OrderStatus::OPEN->value,
                'total' => 0,
            ]);

            foreach ($items as $item) {
                $this->attachItem(
                    $order,
                    (int) $item['product_id'],
                    (int) $item['quantity']
                );
            }

            $this->calculateTotal($order);

            return $this->getOrderDetails((int) $order->id);
        });
    }

    public function addItem(int $orderId, int $productId, int $quantity): Order
    {
        return DB::transaction(function () use ($orderId, $productId, $quantity) {
            $order = Order::query()->findOrFail($orderId);

            $this->attachItem($order, $productId, $quantity);
            $this->calculateTotal($order);

            return $this->getOrderDetails((int) $order->id);
        });
    }

    public function calculateTotal(Order $order): Order
    {
        $total = $order->items()->sum('line_total');
        $order->update(['total' => $total]);

        return $order->fresh();
    }

    public function getOrderDetails(int $id): Order
    {
        return Order::query()
            ->with(['items.product'])
            ->findOrFail($id);
    }

    public function listOrders(): Collection
    {
        return Order::query()
            ->withCount('items')
            ->orderByDesc('id')
            ->get();
    }

    private function attachItem(Order $order, int $productId, int $quantity): void
    {
        $product = Product::query()->findOrFail($productId);
        $this->validateStock($product, $quantity);

        $unitPrice = (string) $product->price;
        $lineTotal = function_exists('bcmul')
            ? bcmul($unitPrice, (string) $quantity, 2)
            : number_format((float) $unitPrice * $quantity, 2, '.', '');

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ]);

        $product->decrement('stock', $quantity);
    }

    private function validateStock(Product $product, int $quantity): void
    {
        if ($product->stock < $quantity) {
            throw new InvalidArgumentException('Insufficient stock for selected product.');
        }
    }

    private function generateReference(): string
    {
        return 'DJ-'.strtoupper(Str::random(8));
    }
}
