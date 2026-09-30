<?php

namespace Automas\Hrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'announcement_category_id' => 'required|exists:announcement_categories,id',
            'departments' => 'required|array',
            'departments.*' => 'exists:departments,id',
            'description' => 'required',
            'priority' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ];
    }
}
