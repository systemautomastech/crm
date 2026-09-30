<?php

namespace Automas\LandingPage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|unique:custom_pages,slug',
            'content'          => 'required|string',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'is_active'        => 'boolean',
        ];
    }
}
