<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'opening_stock' => ['sometimes', 'integer', 'min:0', 'max:100000000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'minimum_quantity' => ['required', 'integer', 'min:0', 'max:100000000'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'category' => ['nullable', 'string', 'max:120'],
        ];
    }
}
