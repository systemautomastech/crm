<?php

namespace Automas\LandingPage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLandingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name'    => 'nullable|string|max:255',
            'contact_email'   => 'nullable|email|max:255',
            'contact_phone'   => 'nullable|string|max:255',
            'contact_address' => 'nullable|string',
            'config_sections' => 'nullable|array',
        ];
    }
}
