<?php

namespace App\Http\Requests\ProposalDefaultPage;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class StoreDefaultPageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('created_by') && auth()->check()) {
            $this->merge([
                'created_by' => creatorId(),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'page_type' => 'sometimes|string',
            'background_image' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('proposal_default_pages', 'sort_order')->where(fn ($query) => $query->where('created_by', creatorId())),
            ],
            'created_by' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => __('The page title field is required.'),
            'title.string' => __('The page title must be a valid string.'),
            'title.max' => __('The page title may not be greater than 255 characters.'),
            'content.string' => __('The page content must be a valid string.'),
            'is_active.boolean' => __('The active status must be active or inactive.'),
            'sort_order.integer' => __('The sort order must be an integer.'),
            'sort_order.min' => __('The sort order must be at least 1.'),
            'sort_order.unique' => __('Duplicate sort order, please use a different order.'),
        ];
    }
}
