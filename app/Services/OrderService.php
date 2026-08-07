<?php

namespace App\Services;

use App\Enums\CashStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\CashRegister;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    public function createOrder(array $data): array
    {
        $items = $data['items'] ?? [];

        if (empty($items)) {
            throw new InvalidArgumentException('O pedido precisa de pelo menos um item.');
        }

        $paymentMethod = $this->resolvePaymentMethod($data['payment_method'] ?? null);
        $status = $this->resolveStatus($data['status'] ?? null);
        $cashRegisterId = isset($data['cash_register_id']) ? (int) $data['cash_register_id'] : null;

        if ($status === OrderStatus::PAID) {
            $this->assertOpenCashRegister($cashRegisterId);
        }

        return DB::transaction(function () use ($data, $items, $paymentMethod, $status, $cashRegisterId) {
            $order = Order::create([
                'cash_register_id' => $cashRegisterId,
                'reference' => $data['reference'] ?? $this->generateReference(),
                'status' => $status,
                'payment_method' => $paymentMethod,
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

    public function addItem(int $orderId, int $productId, int $quantity): array
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

    public function getOrderDetails(int $id): array
    {
        $order = Order::query()
            ->with(['items.product', 'cashRegister.store', 'cashRegister.user'])
            ->findOrFail($id);

        return $this->formatOrderDetail($order);
    }

    public function listOrders(): Collection
    {
        return Order::query()
            ->withCount('items')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Payload completo para recibo, comanda e detalhe do pedido.
     *
     * @return array<string, mixed>
     */
    public function formatOrderDetail(Order $order): array
    {
        $method = $order->payment_method instanceof PaymentMethod
            ? $order->payment_method
            : (PaymentMethod::tryFrom((string) $order->payment_method) ?? PaymentMethod::CASH);

        $status = $order->status instanceof OrderStatus
            ? $order->status
            : OrderStatus::tryFrom((string) $order->status);

        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'status' => $status?->value ?? (string) $order->status,
            'total' => (float) $order->total,
            'payment_method' => $method->value,
            'payment_label' => $method->label(),
            'created_at' => optional($order->created_at)?->toIso8601String(),
            'cash_register' => $order->cashRegister ? [
                'id' => $order->cashRegister->id,
                'status' => $order->cashRegister->status instanceof CashStatus
                    ? $order->cashRegister->status->value
                    : (string) $order->cashRegister->status,
            ] : null,
            'store' => $order->cashRegister?->store ? [
                'id' => $order->cashRegister->store->id,
                'name' => $order->cashRegister->store->name,
                'city' => $order->cashRegister->store->city,
            ] : null,
            'operator' => $order->cashRegister?->user ? [
                'id' => $order->cashRegister->user->id,
                'name' => $order->cashRegister->user->name,
            ] : null,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product?->name ?? 'Produto',
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                    'notes' => null,
                ];
            })->values()->all(),
        ];
    }

    private function resolvePaymentMethod(mixed $value): PaymentMethod
    {
        if ($value instanceof PaymentMethod) {
            return $value;
        }

        $method = PaymentMethod::tryFrom((string) ($value ?? PaymentMethod::CASH->value));

        if (! $method) {
            throw new InvalidArgumentException('Forma de pagamento invalida.');
        }

        return $method;
    }

    private function resolveStatus(mixed $value): OrderStatus
    {
        if ($value instanceof OrderStatus) {
            return $value;
        }

        if ($value === null || $value === '') {
            return OrderStatus::OPEN;
        }

        $status = OrderStatus::tryFrom((string) $value);

        if (! $status) {
            throw new InvalidArgumentException('Estado do pedido invalido.');
        }

        return $status;
    }

    private function assertOpenCashRegister(?int $cashRegisterId): void
    {
        if (! $cashRegisterId) {
            throw new InvalidArgumentException('Caixa obrigatorio para registar uma venda.');
        }

        $cash = CashRegister::query()->find($cashRegisterId);

        if (! $cash) {
            throw new InvalidArgumentException('Caixa nao encontrado.');
        }

        if ($cash->status !== CashStatus::OPEN) {
            throw new InvalidArgumentException('O caixa esta fechado. Nao e possivel registar a venda.');
        }
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
            throw new InvalidArgumentException('Stock insuficiente para o produto selecionado.');
        }
    }

    private function generateReference(): string
    {
        return 'DJ-'.strtoupper(Str::random(8));
    }
}
