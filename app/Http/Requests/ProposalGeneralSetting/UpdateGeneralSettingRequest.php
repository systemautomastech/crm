<?php

namespace App\Http\Requests\ProposalGeneralSetting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $settingId = $this->route('proposal_setting') ?? $this->id;

        return [
            'proposal_prefix' => 'sometimes|required|string|max:20',
            'proposal_starting_number' => 'sometimes|required|integer|min:1',
            'default_validity_days' => 'sometimes|required|integer|min:1|max:365',
            'logo_image' => 'nullable|string',
            'background_image' => 'nullable|string',
            'template_color' => 'nullable|string|max:20',
            'default_terms' => 'nullable|string',
            'creator_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('proposal_settings', 'creator_id')->ignore($settingId),
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'proposal_prefix.required' => __('The proposal number prefix is required.'),
            'proposal_prefix.string' => __('The proposal number prefix must be a valid string.'),
            'proposal_prefix.max' => __('The proposal number prefix may not be greater than 20 characters.'),

            'proposal_starting_number.required' => __('The proposal starting number is required.'),
            'proposal_starting_number.integer' => __('The starting number must be a valid integer.'),
            'proposal_starting_number.min' => __('The starting number must be at least 1.'),

            'default_validity_days.required' => __('The default validity period is required.'),
            'default_validity_days.integer' => __('The default validity period must be a valid number.'),
            'default_validity_days.min' => __('The default validity period must be at least 1 day.'),
            'default_validity_days.max' => __('The default validity period may not exceed 365 days.'),

            'logo_image.image' => __('The logo must be an image file.'),
            'logo_image.mimes' => __('The logo must be a file of type: jpeg, png, jpg, gif, svg, webp.'),
            'logo_image.max' => __('The logo image may not be greater than 2MB.'),

            'background_image.image' => __('The background must be an image file.'),
            'background_image.mimes' => __('The background image must be a file of type: jpeg, png, jpg, gif, svg, webp.'),
            'background_image.max' => __('The background image may not be greater than 2MB.'),

            'default_terms.string' => __('The default terms must be a valid text string.'),

            'creator_id.exists' => __('The selected creator is invalid.'),
            'creator_id.unique' => __('Proposal settings already exist for this creator.'),
        ];
    }
}
