<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;
        $typeRule = auth()->user()->type === 'superadmin' ? 'nullable' : 'nullable|exists:roles,id';

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email,' . $userId . ',id,created_by,' . creatorId()
            ],
            'mobile_no' => 'nullable|string|regex:/^\+?\d{10,16}$/',
            'type' => $typeRule,
            'is_enable_login' => 'boolean',
        ];
    }
}