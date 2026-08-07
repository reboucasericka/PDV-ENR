<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['nullable', 'string', 'max:150'],
            'trade_name' => ['nullable', 'string', 'max:150'],
            'tax_number' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'website' => ['nullable', 'string', 'max:200'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'receipt_footer' => ['nullable', 'string', 'max:1000'],
            'printer_name' => ['nullable', 'string', 'max:150'],
            'vat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_open' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_open')) {
            $this->merge([
                'is_open' => filter_var($this->input('is_open'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
