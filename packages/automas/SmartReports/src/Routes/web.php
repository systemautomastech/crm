<?php

use Illuminate\Support\Facades\Route;
use Automas\SmartReports\Http\Controllers\SmartReportController;
use Automas\SmartReports\Http\Controllers\SmartReportGenerateController;

Route::middleware(['web', 'auth', 'verified', 'PlanModuleCheck:SmartReports'])->group(function () {
    
    // Page Routes (Inertia)
    Route::get('/smart-reports', [SmartReportController::class, 'index'])->name('smart-reports.index');
    Route::get('/smart-reports/attendance-report', [SmartReportController::class, 'attendanceReport'])->name('smart-reports.attendance.report');
    Route::get('/smart-reports/payroll-report', [SmartReportController::class, 'payrollReport'])->name('smart-reports.payroll.report');
    Route::get('/smart-reports/leave-report', [SmartReportController::class, 'leaveReport'])->name('smart-reports.leave.report');
    Route::get('/smart-reports/sales-invoice-report', [SmartReportController::class, 'salesInvoiceReport'])->name('smart-reports.sales-invoice.report');
    Route::get('/smart-reports/purchase-invoice-report', [SmartReportController::class, 'purchaseInvoiceReport'])->name('smart-reports.purchase-invoice.report');
    Route::get('/smart-reports/deal-pipeline-report', [SmartReportController::class, 'dealPipelineReport'])->name('smart-reports.deal-pipeline.report');
    Route::get('/smart-reports/lead-report', [SmartReportController::class, 'leadReport'])->name('smart-reports.lead.report');
    Route::get('/smart-reports/product-stock-report', [SmartReportController::class, 'productStockReport'])->name('smart-reports.product-stock.report');
    Route::get('/smart-reports/project-progress-report', [SmartReportController::class, 'projectProgressReport'])->name('smart-reports.project-progress.report');
    // API Routes (JSON)
    Route::prefix('smart-reports/attendance')->name('smart-reports.attendance.')->group(function () {
        Route::get('/filter-options', [SmartReportGenerateController::class, 'getFilterOptions'])->name('filter-options');
        Route::post('/generate', [SmartReportGenerateController::class, 'generateReport'])->name('generate');
    });

    Route::prefix('smart-reports/payroll')->name('smart-reports.payroll.')->group(function () {
        Route::get('/filter-options', [SmartReportGenerateController::class, 'getPayrollFilterOptions'])->name('filter-options');
        Route::post('/generate', [SmartReportGenerateController::class, 'generatePayrollReport'])->name('generate');
    });

    Route::prefix('smart-reports/leave')->name('smart-reports.leave.')->group(function () {
        Route::get('/filter-options', [SmartReportGenerateController::class, 'getLeaveFilterOptions'])->name('filter-options');
        Route::post('/generate', [SmartReportGenerateController::class, 'generateLeaveReport'])->name('generate');
    });

    Route::prefix('smart-reports/sales-invoice')->name('smart-reports.sales-invoice.')->group(function () {
        Route::get('/filter-options', [SmartReportGenerateController::class, 'getSalesInvoiceFilterOptions'])->name('filter-options');
        Route::post('/generate', [SmartReportGenerateController::class, 'generateSalesInvoiceReport'])->name('generate');
    });

    Route::prefix('smart-reports/purchase-invoice')->name('smart-reports.purchase-invoice.')->group(function () {
        Route::get('/filter-options', [SmartReportGenerateController::class, 'getPurchaseInvoiceFilterOptions'])->name('filter-options');
        Route::post('/generate', [SmartReportGenerateController::class, 'generatePurchaseInvoiceReport'])->name('generate');
    });

    Route::prefix('smart-reports/deal-pipeline')->name('smart-reports.deal-pipeline.')->group(function () {
        Route::get('/filter-options', [SmartReportGenerateController::class, 'getDealPipelineFilterOptions'])->name('filter-options');
        Route::post('/generate', [SmartReportGenerateController::class, 'generateDealPipelineReport'])->name('generate');
    });

    Route::prefix('smart-reports/lead')->name('smart-reports.lead.')->group(function () {
        Route::get('/filter-options', [SmartReportGenerateController::class, 'getLeadFilterOptions'])->name('filter-options');
        Route::post('/generate', [SmartReportGenerateController::class, 'generateLeadReport'])->name('generate');
    });

    Route::prefix('smart-reports/product-stock')->name('smart-reports.product-stock.')->group(function () {
        Route::get('/filter-options', [SmartReportGenerateController::class, 'getProductStockFilterOptions'])->name('filter-options');
        Route::post('/generate', [SmartReportGenerateController::class, 'generateProductStockReport'])->name('generate');
    });

    Route::prefix('smart-reports/project-progress')->name('smart-reports.project-progress.')->group(function () {
        Route::get('/filter-options', [SmartReportGenerateController::class, 'getProjectProgressFilterOptions'])->name('filter-options');
        Route::post('/generate', [SmartReportGenerateController::class, 'generateProjectProgressReport'])->name('generate');
    });
});