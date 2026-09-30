<?php

namespace Automas\Quotation\Http\Requests\QuotationSetting;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quotation_prefix' => 'sometimes|required|string|max:20',
            'quotation_starting_number' => 'sometimes|required|integer|min:1',
            'default_validity_days' => 'sometimes|required|integer|min:1|max:365',
            'logo_image' => 'nullable|string',
            'background_image' => 'nullable|string',
            'template_color' => 'nullable|string|max:20',
            'default_terms' => 'nullable|string',
            'creator_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'quotation_prefix.required' => __('The quotation number prefix is required.'),
            'quotation_prefix.string' => __('The quotation number prefix must be a valid string.'),
            'quotation_prefix.max' => __('The quotation number prefix may not be greater than 20 characters.'),
            'quotation_starting_number.required' => __('The quotation starting number is required.'),
            'quotation_starting_number.integer' => __('The starting number must be a valid integer.'),
            'quotation_starting_number.min' => __('The starting number must be at least 1.'),
            'default_validity_days.required' => __('The default validity period is required.'),
            'default_validity_days.integer' => __('The default validity period must be a valid number.'),
            'default_validity_days.min' => __('The default validity period must be at least 1 day.'),
            'default_validity_days.max' => __('The default validity period may not exceed 365 days.'),
            'default_terms.string' => __('The default terms must be a valid text string.'),
            'creator_id.exists' => __('The selected creator is invalid.'),
        ];
    }
}
