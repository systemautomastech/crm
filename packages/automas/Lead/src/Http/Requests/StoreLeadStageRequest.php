<?php

namespace Automas\Lead\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:100',
            'pipeline_id' => 'nullable|integer|exists:pipelines,id',
            'final_type' => 'nullable|string|in:none,accepted,rejected',
            'is_final_accepted' => 'nullable|boolean',
            'is_final_rejected' => 'nullable|boolean',
        ];
    }
}