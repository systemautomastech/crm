<?php

namespace Automas\Lead\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Automas\Lead\Models\LeadStage;
use Automas\Lead\Models\Pipeline;

class StoreLeadImportSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create-leads') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    public function rules(): array
    {
        return [
            'pipeline_id' => [
                'required',
                'integer',
                'exists:pipelines,id',
            ],

            'stage_id' => [
                'required',
                'integer',
                'exists:lead_stages,id',
            ],

            'duplicate_by' => [
                'required',
                Rule::in([
                    'phone',
                    'email',
                    'phone_or_email',
                    'none',
                ]),
            ],

            'duplicate_strategy' => [
                'required',
                Rule::in([
                    'skip',
                    'update',
                    'create',
                ]),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $pipelineId = (int) $this->input('pipeline_id');
                $stageId = (int) $this->input('stage_id');

                $pipelineExists = Pipeline::query()
                    ->whereKey($pipelineId)
                    ->where('created_by', creatorId())
                    ->exists();

                if (!$pipelineExists) {
                    $validator->errors()->add(
                        'pipeline_id',
                        __('The selected pipeline does not belong to your company.')
                    );

                    return;
                }

                $stageExists = LeadStage::query()
                    ->whereKey($stageId)
                    ->where('pipeline_id', $pipelineId)
                    ->exists();

                if (!$stageExists) {
                    $validator->errors()->add(
                        'stage_id',
                        __('The selected stage does not belong to the selected pipeline.')
                    );
                }

                if (
                    $this->input('duplicate_by') === 'email'
                    && $this->input('duplicate_strategy') === 'update'
                ) {
                    // Email is optional in your lead table.
                    // This is allowed, but rows without email cannot match.
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'pipeline_id.required' => __('Please select a pipeline.'),
            'stage_id.required' => __('Please select a stage.'),
            'duplicate_by.required' => __('Select how duplicate leads should be detected.'),
            'duplicate_strategy.required' => __('Select what should happen when a duplicate is found.'),
        ];
    }
}