<?php

namespace Automas\SmartReports\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $route = $this->route()?->getName();
        $dateOptional = in_array($route, [
            'smart-reports.product-stock.generate',
            'smart-reports.project-progress.generate',
            'smart-reports.deal-pipeline.generate',
        ]);

        return [
            'employee_ids'     => 'nullable|array',
            'employee_ids.*'   => 'integer',
            'department_ids'   => 'nullable|array',
            'department_ids.*' => 'integer',
            'status'           => 'nullable|array',
            'payroll_id'       => 'nullable|integer',
            'leave_type_ids'   => 'nullable|array',
            'leave_type_ids.*' => 'integer',
            'customer_ids'     => 'nullable|array',
            'customer_ids.*'   => 'integer',
            'vendor_ids'       => 'nullable|array',
            'vendor_ids.*'     => 'integer',
            'warehouse_ids'    => 'nullable|array',
            'warehouse_ids.*'  => 'integer',
            'export_type'      => 'required|in:pdf,excel,view',
            'project_ids'      => 'nullable|array',
            'project_ids.*'    => 'integer',
            'pipeline_ids'     => 'nullable|array',
            'pipeline_ids.*'   => 'integer',
            'stage_ids'        => 'nullable|array',
            'stage_ids.*'      => 'integer',
            'user_ids'         => 'nullable|array',
            'user_ids.*'       => 'integer',
            'source_ids'       => 'nullable|array',
            'source_ids.*'     => 'integer',
            'label_ids'        => 'nullable|array',
            'label_ids.*'      => 'integer',
            'date_from'        => 'nullable|date',
            'date_to'          => 'nullable|date|after_or_equal:date_from',
            'client_ids'       => 'nullable|array',
            'client_ids.*'     => 'integer',
        ];
    }
}
