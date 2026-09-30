<?php

namespace Automas\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePosDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name'              => 'required|string|max:255',
            'discount_type'     => 'required|in:percentage,fixed',
            'discount_value'    => 'required|numeric|min:0.01|max:100',
            'min_quantity'      => 'nullable|integer|min:1',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'product_ids'       => 'nullable|array',
            'product_ids.*'     => 'exists:product_service_items,id',
            'category_id'       => 'nullable|exists:product_service_categories,id',
        ];

        return $rules;
    }
}
