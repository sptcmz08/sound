<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canOperateStock() ?? false;
    }

    public function rules(): array
    {
        return ['document_date' => ['required', 'date'], 'warehouse_id' => ['required', 'exists:warehouses,id'], 'reference_no' => ['nullable', 'string', 'max:255'], 'contact_name' => ['nullable', 'string', 'max:255'], 'department_name' => ['nullable', 'string', 'max:255'], 'purpose' => ['nullable', 'string', 'max:255'], 'note' => ['nullable', 'string', 'max:2000'], 'idempotency_key' => ['required', 'uuid'], 'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'], 'items.*.quantity' => ['required', 'decimal:0,4', 'gt:0'], 'items.*.unit_cost' => ['nullable', 'decimal:0,4', 'gte:0'], 'items.*.unit_price' => ['nullable', 'decimal:0,4', 'gte:0'], 'items.*.note' => ['nullable', 'string', 'max:500']];
    }
}
