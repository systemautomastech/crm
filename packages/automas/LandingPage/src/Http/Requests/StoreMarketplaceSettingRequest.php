<?php

namespace Automas\LandingPage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarketplaceSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module'          => 'required|string',
            'title'           => 'nullable|string|max:255',
            'config_sections' => 'nullable|array',
        ];
    }
}
