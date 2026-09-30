<?php

namespace Automas\Taskly\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:payment_date',
            'project_id' => 'required|exists:projects,id',
            'customer_id' => 'required|exists:users,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_terms' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.milestone_id' => 'required|exists:project_milestones,id',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ];
    }
}
