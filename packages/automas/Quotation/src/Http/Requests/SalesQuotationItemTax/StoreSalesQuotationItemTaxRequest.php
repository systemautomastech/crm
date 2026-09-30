<?php

namespace Automas\Quotation\Http\Requests\SalesQuotationItemTax;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesQuotationItemTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:sales_quotation_items,id',
            'tax_name' => 'required|string|max:255',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required' => __('The quotation item ID is required.'),
            'item_id.exists' => __('Selected quotation item does not exist.'),
            'tax_name.required' => __('The tax name is required.'),
            'tax_name.string' => __('The tax name must be a valid string.'),
            'tax_name.max' => __('The tax name may not be greater than 255 characters.'),
            'tax_rate.required' => __('The tax rate is required.'),
            'tax_rate.numeric' => __('The tax rate must be a valid number.'),
            'tax_rate.min' => __('The tax rate must be 0 or greater.'),
            'tax_rate.max' => __('The tax rate cannot exceed 100%.'),
        ];
    }
}
