<?php

namespace Automas\Lead\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLeadImportMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create-leads') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'column_mapping' => is_array($this->column_mapping)
                ? $this->column_mapping
                : [],

            'assignment_ranges' => is_array($this->assignment_ranges)
                ? $this->assignment_ranges
                : [],
        ]);
    }

    public function rules(): array
    {
        return [
            /*
             * CSV column index => CRM field.
             *
             * Example:
             * {
             *   "0": "name",
             *   "1": "phone",
             *   "2": "subject",
             *   "3": "__ignore__"
             * }
             */
            'column_mapping' => [
                'required',
                'array',
                'min:1',
            ],

            'column_mapping.*' => [
                'required',
                'string',
                Rule::in([
                    '__ignore__',
                    'name',
                    'subject',
                    'phone',
                    'email',
                    'notes',
                    'date',
                ]),
            ],

            'assignment_ranges' => [
                'nullable',
                'array',
                'max:100',
            ],

            'assignment_ranges.*.from_row' => [
                'required',
                'integer',
                'min:1',
            ],

            'assignment_ranges.*.to_row' => [
                'required',
                'integer',
                'min:1',
            ],

            'assignment_ranges.*.user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateRequiredMappings($validator);
                $this->validateUniqueMappings($validator);
                $this->validateAssignmentRanges($validator);
            },
        ];
    }

    private function validateRequiredMappings(
        Validator $validator
    ): void {
        $mapping = array_values(
            $this->input('column_mapping', [])
        );

        foreach (['name', 'subject', 'phone'] as $requiredField) {
            if (!in_array($requiredField, $mapping, true)) {
                $validator->errors()->add(
                    'column_mapping',
                    __(
                        'The :field field must be mapped.',
                        ['field' => ucfirst($requiredField)]
                    )
                );
            }
        }
    }

    private function validateUniqueMappings(
        Validator $validator
    ): void {
        $mappedFields = array_values(
            array_filter(
                $this->input('column_mapping', []),
                fn ($field) => $field !== '__ignore__'
            )
        );

        if (count($mappedFields) !== count(array_unique($mappedFields))) {
            $validator->errors()->add(
                'column_mapping',
                __('A CRM field cannot be mapped more than once.')
            );
        }
    }

    private function validateAssignmentRanges(
        Validator $validator
    ): void {
        $ranges = collect(
            $this->input('assignment_ranges', [])
        )
            ->map(fn (array $range, int $index): array => [
                'index' => $index,
                'from_row' => (int) ($range['from_row'] ?? 0),
                'to_row' => (int) ($range['to_row'] ?? 0),
                'user_id' => (int) ($range['user_id'] ?? 0),
            ])
            ->sortBy('from_row')
            ->values();

        foreach ($ranges as $range) {
            if ($range['to_row'] < $range['from_row']) {
                $validator->errors()->add(
                    "assignment_ranges.{$range['index']}.to_row",
                    __('The ending row must be greater than or equal to the starting row.')
                );
            }
        }

        for ($index = 1; $index < $ranges->count(); $index++) {
            $previous = $ranges[$index - 1];
            $current = $ranges[$index];

            if ($current['from_row'] <= $previous['to_row']) {
                $validator->errors()->add(
                    "assignment_ranges.{$current['index']}.from_row",
                    __('User assignment ranges cannot overlap.')
                );
            }
        }
    }

    public function messages(): array
    {
        return [
            'column_mapping.required' => __(
                'Please map the CSV columns before continuing.'
            ),

            'assignment_ranges.*.from_row.required' => __(
                'Enter the first row for this assignment.'
            ),

            'assignment_ranges.*.to_row.required' => __(
                'Enter the last row for this assignment.'
            ),

            'assignment_ranges.*.user_id.required' => __(
                'Select a user for this row range.'
            ),
        ];
    }
}