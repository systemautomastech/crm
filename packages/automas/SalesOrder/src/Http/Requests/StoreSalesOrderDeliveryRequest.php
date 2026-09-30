<?php

namespace Automas\SalesOrder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesOrderDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_date'               => 'required|date',
            'notes'                       => 'nullable|string',
            'items'                       => 'required|array|min:1',
            'items.*.sales_order_item_id' => 'required|integer|exists:sales_order_items,id',
            'items.*.quantity'            => 'required|numeric|min:0.0001',
            'items.*.notes'               => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_date.required'               => __('Delivery date is required.'),
            'items.required'                       => __('At least one item must be selected for delivery.'),
            'items.*.quantity.min'                 => __('Delivery quantity must be at least 1.'),
            'items.*.sales_order_item_id.required' => __('Sales order item ID is required.'),
        ];
    }
}
