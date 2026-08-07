<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
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
            'company_name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'trade_name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'tax_number' => ['sometimes', 'nullable', 'string', 'max:50'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:150'],
            'website' => ['sometimes', 'nullable', 'string', 'max:200'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'currency' => ['sometimes', 'nullable', 'string', 'max:10'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:100'],
            'logo' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
            'receipt_footer' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'printer_name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'vat' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'is_open' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_open')) {
            $this->merge([
                'is_open' => filter_var($this->input('is_open'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }

        if ($this->has('remove_logo')) {
            $this->merge([
                'remove_logo' => filter_var($this->input('remove_logo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
