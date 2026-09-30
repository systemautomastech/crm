<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $typeRule = auth()->user()->type === 'superadmin' ? 'nullable' : 'required|exists:roles,id';
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email,NULL,id,created_by,' . creatorId()
            ],
            'mobile_no' => 'nullable|string|regex:/^\+?\d{10,16}$/',
            'password' => 'required|confirmed|min:6',
            'type' => $typeRule,
            'is_enable_login' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => __('Role is required.'),
        ];
    }
}
