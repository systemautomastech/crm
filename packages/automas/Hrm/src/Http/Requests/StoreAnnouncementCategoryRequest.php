<?php

namespace Automas\Hrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'announcement_category' => 'required|string|max:255'
        ];
    }
}