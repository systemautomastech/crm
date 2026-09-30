<?php

namespace Automas\Hrm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_name' => 'required|max:100',
            'branch_id' => 'required|exists:branches,id',
            'employee_prefix' => 'required|max:5'
        ];
    }
}