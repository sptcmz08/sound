<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return [
            'code' => ['required', 'string', 'max:100', Rule::unique('products')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', Rule::in(['PART', 'WIP', 'FG'])],
            'unit_id' => ['required', 'exists:units,id'],
            'minimum_stock' => ['required', 'decimal:0,4', 'gte:0'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'components' => ['nullable', 'array'],
            'components.*.product_id' => ['required_with:components', 'exists:products,id', 'distinct'],
            'components.*.quantity' => ['required_with:components', 'decimal:0,4', 'gt:0'],
        ];
    }
}
