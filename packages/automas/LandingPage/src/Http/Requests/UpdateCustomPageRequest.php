<?php

namespace Automas\LandingPage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customPage = $this->route('customPage');

        return [
            'title'            => 'required|string|max:255',
            'slug'             => 'required|string|unique:custom_pages,slug,' . $customPage->id,
            'content'          => 'required|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_active'        => 'boolean',
        ];
    }
}
