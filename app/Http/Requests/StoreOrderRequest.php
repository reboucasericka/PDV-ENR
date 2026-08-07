<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference' => ['nullable', 'string', 'max:60'],
            'status' => ['nullable', new Enum(OrderStatus::class)],
            'cash_register_id' => ['nullable', 'integer', 'exists:cash_registers,id'],
            'payment_method' => ['nullable', new Enum(PaymentMethod::class)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
