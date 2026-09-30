<?php

namespace Automas\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePosBillingCounterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $counterId = $this->route('pos_billing_counter')->id ?? null;

        // Note: Bank account validation added to fix counter number issue
        // This ensures proper counter configuration and prevents duplicate counter number generation
        return [
            'name'            => 'required|string|max:255',
            'code'            => 'required|string|max:100|unique:pos_billing_counters,code,' . $counterId,
            'bank_account_id' => 'required|exists:bank_accounts,id', // Required for counter number fix
            'status'          => 'required|boolean',
            'description'     => 'nullable|string|max:1000',
        ];
    }
}
