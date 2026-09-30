<?php

namespace Automas\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'billing_counter_id' => 'required|exists:pos_billing_counters,id',
            'pos_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:product_service_items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.item_discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.item_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'billing_counter_id.required' => __('Please select a billing counter before creating POS.'),
        ];
    }
}