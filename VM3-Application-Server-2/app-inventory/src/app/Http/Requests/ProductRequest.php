<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for both creating and updating a product.
 */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        // On update the product keeps its own SKU, so the unique rule has to
        // ignore the current row.
        $productId = $this->route('product') ? $this->route('product')->id : null;

        return [
            'sku' => [
                'required',
                'string',
                'max:32',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'name' => ['required', 'string', 'max:160'],
            'quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'reorder_level' => ['required', 'integer', 'min:0', 'max:1000000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'location' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sku.required' => 'The SKU is required.',
            'sku.unique' => 'Another product already uses this SKU.',
            'name.required' => 'The product name is required.',
            'quantity.required' => 'Enter the quantity in stock.',
            'reorder_level.required' => 'Enter the reorder level.',
            'unit_price.required' => 'Enter the unit price.',
        ];
    }

    /**
     * Uppercase SKUs keep the catalogue consistent.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('sku')) {
            $this->merge(['sku' => strtoupper(trim((string) $this->input('sku')))]);
        }
    }
}
