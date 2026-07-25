<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('patch') ? 'sometimes' : 'required';

        return [
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'name' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'quantity' => ['prohibited'],
            'opening_stock' => ['prohibited'],
            'price' => [$required, 'numeric', 'min:0', 'max:9999999999.99'],
            'minimum_quantity' => [$required, 'integer', 'min:0', 'max:100000000'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'category' => ['nullable', 'string', 'max:120'],
        ];
    }
}
