<?php

namespace Automas\Quotation\Http\Requests\SalesQuotationContent;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesQuotationContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quotation_id' => 'sometimes|required|exists:sales_quotations,id',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'background_image' => 'nullable|string',
            'sort_order' => 'sometimes|integer|min:1',
            'creator_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'quotation_id.required' => __('The quotation ID is required.'),
            'quotation_id.exists' => __('Selected quotation does not exist.'),
            'title.required' => __('The content title is required.'),
            'title.string' => __('The content title must be a valid string.'),
            'title.max' => __('The content title may not be greater than 255 characters.'),
            'content.string' => __('Content must be a valid text string.'),
            'sort_order.integer' => __('Sort order must be a valid integer.'),
            'sort_order.min' => __('Sort order must be at least 1.'),
        ];
    }
}
