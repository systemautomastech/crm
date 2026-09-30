<?php

namespace Automas\Hrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkingDaysRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
        ];
    }

    public function messages(): array
    {
        return [
            'working_days.required' => __('At least one working day must be selected.'),
            'working_days.array' => __('Working days must be an array.'),
            'working_days.min' => __('At least one working day must be selected.'),
            'working_days.*.in' => __('Invalid day selected.'),
        ];
    }
}