<?php

namespace Automas\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:product_service_items,id',
            'reorder_level' => 'required|numeric|min:0',
            'maximum_level' => 'required|numeric|min:0',
            'inventory_account_id' => 'required|exists:chart_of_accounts,id',
            'cogs_account_id' => 'required|exists:chart_of_accounts,id',
            'sales_account_id' => 'required|exists:chart_of_accounts,id',
            'valuation_method' => 'required|in:fifo,lifo,weighted_average',
            'is_tracked' => 'boolean',
        ];
    }
}
