<?php

namespace Automas\Quotation\Http\Requests\SalesQuotation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quotation_number' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'revision_number' => 'nullable|integer|min:1',
            'parent_quotation_id' => 'nullable|exists:sales_quotations,id',
            'quotation_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:quotation_date',
            'customer_mode' => 'nullable|in:existing,new',
            'customer_type' => 'nullable|in:existing,new',
            'customer_id' => 'required_without_all:customer_name,customer_email|nullable|integer|exists:users,id',
            'customer_name' => 'required_without:customer_id|nullable|string|max:255',
            'customer_email' => 'required_without:customer_id|nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_address' => 'nullable|string',
            'warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'is_tax_enabled' => 'nullable',
            'is_prepaid' => 'nullable',
            'otc_discount_type' => 'nullable|in:percentage,fixed',
            'otc_discount_value' => 'nullable|numeric|min:0',
            'mrc_discount_type' => 'nullable|in:percentage,fixed',
            'mrc_discount_value' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,sent,accepted,rejected',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer',
            'items.*.product_id' => 'nullable|integer|min:1',
            'items.*.section' => 'nullable|string|max:100',
            'items.*.item_type' => 'nullable|string|max:50',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_type' => 'nullable|string|in:percentage,fixed',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.total_amount' => 'nullable|numeric|min:0',
            'items.*.taxes' => 'nullable|array',
            'items.*.taxes.*.tax_name' => 'required_with:items.*.taxes|string|max:255',
            'items.*.taxes.*.tax_rate' => 'required_with:items.*.taxes|numeric|min:0',
            'contents' => 'nullable|array',
            'contents.*.id' => 'nullable|integer',
            'contents.*.title' => 'required_with:contents|string|max:255',
            'contents.*.content' => 'nullable|string',
            'contents.*.background_image' => 'nullable|string',
            'contents.*.sort_order' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'quotation_date.required' => __('The quotation date is required.'),
            'quotation_date.date' => __('The quotation date must be a valid date.'),
            'due_date.required' => __('The due date is required.'),
            'due_date.date' => __('The due date must be a valid date.'),
            'due_date.after_or_equal' => __('The due date must be on or after the quotation date.'),
            'customer_id.required_unless' => __('Please select an existing customer.'),
            'customer_id.exists' => __('Selected customer does not exist.'),
            'customer_name.required_if' => __('Customer name is required for new customer.'),
            'customer_email.required_if' => __('Customer email is required for new customer.'),
            'customer_email.email' => __('Please enter a valid email address.'),
            'customer_phone.required_if' => __('Customer phone is required for new customer.'),
            'customer_address.required_if' => __('Customer address is required for new customer.'),
            'warehouse_id.exists' => __('Selected warehouse does not exist.'),
            'items.required' => __('At least one item is required.'),
            'items.array' => __('Items must be a valid list.'),
            'items.min' => __('At least one item is required.'),
            'items.*.quantity.required' => __('Quantity is required for each item.'),
            'items.*.quantity.numeric' => __('Quantity must be a valid number.'),
            'items.*.quantity.min' => __('Quantity must be greater than 0.'),
            'items.*.unit_price.required' => __('Unit price is required for each item.'),
            'items.*.unit_price.numeric' => __('Unit price must be a valid number.'),
            'items.*.unit_price.min' => __('Unit price must be 0 or greater.'),
            'items.*.discount_type.in' => __('Discount type must be percentage or fixed.'),
            'items.*.discount_percentage.max' => __('Discount percentage cannot exceed 100%.'),
            'items.*.discount_percentage.min' => __('Discount percentage must be 0 or greater.'),
            'items.*.discount_amount.min' => __('Discount amount must be 0 or greater.'),
            'items.*.taxes.*.tax_name.required_with' => __('Tax name is required when tax is provided.'),
            'items.*.taxes.*.tax_rate.required_with' => __('Tax rate is required when tax is provided.'),
            'contents.*.title.required_with' => __('Page title is required for each content section.'),
        ];
    }
}
