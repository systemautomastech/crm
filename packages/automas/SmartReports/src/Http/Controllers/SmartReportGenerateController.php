<?php

namespace Automas\SmartReports\Http\Controllers;

use Illuminate\Routing\Controller;
use Automas\SmartReports\Http\Requests\GenerateReportRequest;
use Automas\SmartReports\Services\ReportGenerators\AttendanceSummaryGenerator;
use Automas\SmartReports\Services\ReportGenerators\PayrollSheetGenerator;
use Automas\SmartReports\Services\ReportGenerators\LeaveReportGenerator;
use Automas\SmartReports\Services\ReportGenerators\SalesInvoiceReportGenerator;
use Automas\SmartReports\Services\ReportGenerators\PurchaseInvoiceReportGenerator;
use Automas\SmartReports\Services\ReportGenerators\DealPipelineReportGenerator;
use Automas\SmartReports\Services\ReportGenerators\LeadReportGenerator;
use Automas\SmartReports\Services\ReportGenerators\ProductStockReportGenerator;
use Automas\SmartReports\Services\ReportGenerators\ProjectProgressReportGenerator;

class SmartReportGenerateController extends Controller
{
    // ── Private service helpers ───────────────────────────────────────────────

    private function attendance()        { return new AttendanceSummaryGenerator(); }
    private function payroll()           { return new PayrollSheetGenerator(); }
    private function leave()             { return new LeaveReportGenerator(); }
    private function salesInvoice()      { return new SalesInvoiceReportGenerator(); }
    private function purchaseInvoice()   { return new PurchaseInvoiceReportGenerator(); }
    private function dealPipeline()      { return new DealPipelineReportGenerator(); }
    private function lead()              { return new LeadReportGenerator(); }
    private function productStock()      { return new ProductStockReportGenerator(); }
    private function projectProgress()   { return new ProjectProgressReportGenerator(); }

    // ── Shared response helpers ───────────────────────────────────────────────

    private function filterOptions($service)
    {
        try {
            return response()->json(['success' => true, 'data' => $service->getFilterOptions()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function generate($service, array $filters, GenerateReportRequest $request)
    {
        try {
            $result = $service->getReportData($filters);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Attendance ────────────────────────────────────────────────────────────

    public function getFilterOptions()
    {
        return $this->filterOptions($this->attendance());
    }

    public function generateReport(GenerateReportRequest $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'employee_ids', 'department_ids', 'branch_ids', 'status']);
        return $this->generate($this->attendance(), $filters, $request);
    }

    // ── Payroll ───────────────────────────────────────────────────────────────

    public function getPayrollFilterOptions()
    {
        return $this->filterOptions($this->payroll());
    }

    public function generatePayrollReport(GenerateReportRequest $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'employee_ids', 'department_ids', 'payroll_id']);
        return $this->generate($this->payroll(), $filters, $request);
    }

    // ── Leave ─────────────────────────────────────────────────────────────────

    public function getLeaveFilterOptions()
    {
        return $this->filterOptions($this->leave());
    }

    public function generateLeaveReport(GenerateReportRequest $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'employee_ids', 'department_ids', 'branch_ids', 'status', 'leave_type_ids']);
        return $this->generate($this->leave(), $filters, $request);
    }

    // ── Sales Invoice ─────────────────────────────────────────────────────────

    public function getSalesInvoiceFilterOptions()
    {
        return $this->filterOptions($this->salesInvoice());
    }

    public function generateSalesInvoiceReport(GenerateReportRequest $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'customer_ids', 'warehouse_ids', 'status']);
        return $this->generate($this->salesInvoice(), $filters, $request);
    }

    // ── Purchase Invoice ──────────────────────────────────────────────────────

    public function getPurchaseInvoiceFilterOptions()
    {
        return $this->filterOptions($this->purchaseInvoice());
    }

    public function generatePurchaseInvoiceReport(GenerateReportRequest $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'vendor_ids', 'warehouse_ids', 'status']);
        return $this->generate($this->purchaseInvoice(), $filters, $request);
    }

    // ── Deal Pipeline ─────────────────────────────────────────────────────────

    public function getDealPipelineFilterOptions()
    {
        return $this->filterOptions($this->dealPipeline());
    }

    public function generateDealPipelineReport(GenerateReportRequest $request)
    {
        $filters = $request->only(['pipeline_ids', 'stage_ids', 'status', 'client_ids']);
        return $this->generate($this->dealPipeline(), $filters, $request);
    }

    // ── Lead ─────────────────────────────────────────────────────────────────

    public function getLeadFilterOptions()
    {
        return $this->filterOptions($this->lead());
    }

    public function generateLeadReport(GenerateReportRequest $request)
    {
        $filters = $request->only(['pipeline_ids', 'stage_ids', 'user_ids', 'source_ids', 'label_ids', 'status', 'date_from', 'date_to']);
        return $this->generate($this->lead(), $filters, $request);
    }

    // ── Product Stock ─────────────────────────────────────────────────────────

    public function getProductStockFilterOptions()
    {
        return $this->filterOptions($this->productStock());
    }

    public function generateProductStockReport(GenerateReportRequest $request)
    {
        $filters = $request->only(['category_ids', 'warehouse_ids', 'type', 'stock_status']);
        return $this->generate($this->productStock(), $filters, $request);
    }

    // ── Project Progress ──────────────────────────────────────────────────────

    public function getProjectProgressFilterOptions()
    {
        return $this->filterOptions($this->projectProgress());
    }

    public function generateProjectProgressReport(GenerateReportRequest $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'status', 'project_ids']);
        return $this->generate($this->projectProgress(), $filters, $request);
    }
}
