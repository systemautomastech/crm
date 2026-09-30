<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:20',
            'phone' => 'nullable|string|regex:/^\+?\d{10,16}$/',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ];
    }
}