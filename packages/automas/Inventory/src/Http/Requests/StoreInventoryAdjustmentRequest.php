<?php

namespace Automas\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'adjustment_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_type' => 'required|in:increase,decrease,recount',
            'reason' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.current_quantity' => 'required|numeric|min:0',
            'items.*.adjusted_quantity' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }    
}
