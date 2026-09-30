<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankTransferPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_counter_input' => 'required|integer|min:0',
            'storage_counter_input' => 'required|integer|min:0',
            'userprice_input' => 'required|numeric|min:0',
            'storage_price_input' => 'required|numeric|min:0',
            'user_module_price_input' => 'required|numeric|min:0',
            'time_period' => 'required|string|in:Month,Year',
            'payment_receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'plan_id' => 'required|exists:plans,id',
            'coupon_code' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'user_counter_input.required' => __('User count is required.'),
            'storage_counter_input.required' => __('Storage count is required.'),
            'userprice_input.required' => __('User price is required.'),
            'storage_price_input.required' => __('Storage price is required.'),
            'user_module_price_input.required' => __('Module price is required.'),
            'time_period.required' => __('Time period is required.'),
            'payment_receipt.required' => __('Payment receipt is required.'),
        ];
    }
}