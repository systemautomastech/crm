<?php

namespace Automas\Quotation\Http\Requests\SalesQuotationItem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesQuotationItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quotation_id' => 'sometimes|required|exists:sales_quotations,id',
            'product_id' => 'nullable|exists:product_service_items,id',
            'section' => 'nullable|string|max:100',
            'item_type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'taxes' => 'nullable|array',
            'taxes.*.tax_name' => 'required_with:taxes|string|max:255',
            'taxes.*.tax_rate' => 'required_with:taxes|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'quotation_id.required' => __('The quotation ID is required.'),
            'quotation_id.exists' => __('Selected quotation does not exist.'),
            'product_id.exists' => __('Selected product does not exist.'),
            'quantity.required' => __('The quantity field is required.'),
            'quantity.numeric' => __('The quantity must be a valid number.'),
            'quantity.min' => __('Quantity must be greater than 0.'),
            'unit_price.required' => __('The unit price field is required.'),
            'unit_price.numeric' => __('The unit price must be a valid number.'),
            'unit_price.min' => __('Unit price must be 0 or greater.'),
            'discount_percentage.numeric' => __('Discount percentage must be a valid number.'),
            'discount_percentage.max' => __('Discount percentage cannot exceed 100%.'),
            'tax_percentage.numeric' => __('Tax percentage must be a valid number.'),
            'tax_percentage.max' => __('Tax percentage cannot exceed 100%.'),
            'taxes.*.tax_name.required_with' => __('Tax name is required when tax is provided.'),
            'taxes.*.tax_rate.required_with' => __('Tax rate is required when tax is provided.'),
        ];
    }
}
