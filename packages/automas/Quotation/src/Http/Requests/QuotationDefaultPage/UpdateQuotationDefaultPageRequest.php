<?php

namespace Automas\Quotation\Http\Requests\QuotationDefaultPage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuotationDefaultPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('created_by') && auth()->check()) {
            $this->merge([
                'created_by' => creatorId(),
            ]);
        }
    }

    public function rules(): array
    {
        $pageId = $this->route('defaultPage')?->id ?? ($this->route('default_page')?->id ?? $this->route('default_page'));

        return [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'page_type' => 'sometimes|string',
            'background_image' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('quotation_default_pages', 'sort_order')
                    ->where(fn($query) => $query->where('created_by', creatorId()))
                    ->ignore($pageId),
            ],
            'created_by' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('The page title field is required.'),
            'title.string' => __('The page title must be a valid string.'),
            'title.max' => __('The page title may not be greater than 255 characters.'),
            'title.unique' => __('A page with this title already exists.'),
            'content.string' => __('The page content must be a valid string.'),
            'is_active.boolean' => __('The active status must be active or inactive.'),
            'sort_order.integer' => __('The sort order must be an integer.'),
            'sort_order.min' => __('The sort order must be at least 1.'),
            'sort_order.unique' => __('Duplicate sort order, please use a different order.'),
            'creator_id.exists' => __('The selected creator is invalid.'),
        ];
    }
}
