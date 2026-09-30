<?php

namespace Automas\SmartReports\Http\Controllers;

use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Hrm\Models\LeaveApplication;
use Automas\Hrm\Models\PayrollEntry;
use Automas\Lead\Models\Deal;
use Automas\ProductService\Models\ProductServiceItem;
use Automas\SmartReports\Services\ReportGenerators\AttendanceSummaryGenerator;
use Automas\Taskly\Models\Project;

class SmartReportController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->can('manage-smart-reports')) {
            $cid = creatorId();

            $today          = now()->toDateString();
            $monthStart     = now()->startOfMonth()->toDateString();
            $monthEnd       = now()->endOfMonth()->toDateString();
            $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
            $lastMonthEnd   = now()->subMonthNoOverflow()->endOfMonth()->toDateString();

            // Attendance — current month 1st → today (uses same generator as AttendanceReport)
            $attendanceStats = [];
            if (class_exists('\Automas\Hrm\Models\Attendance')) {
                $generator = new AttendanceSummaryGenerator();
                $data      = $generator->getData([
                    'start_date' => $monthStart,
                    'end_date'   => $today,
                ]);
                $summary   = $generator->getSummary($data);
                $attendanceStats = [
                    ['label' => 'Employees',       'value' => $summary['total_employees'],     'color' => 'blue'],
                    ['label' => 'Attendance Rate', 'value' => $summary['attendance_rate'].'%', 'color' => 'green'],
                ];
            }

            // Payroll — last month
            $payrollStats = [];
            if (class_exists('\Automas\Hrm\Models\PayrollEntry')) {
                $base = PayrollEntry::where('payroll_entries.created_by', $cid)
                    ->join('payrolls', 'payroll_entries.payroll_id', '=', 'payrolls.id')
                    ->whereBetween('payrolls.pay_period_start', [$lastMonthStart, $lastMonthEnd]);
                $grossPay = (clone $base)->sum('payroll_entries.gross_pay');
                $netPay   = (clone $base)->sum('payroll_entries.net_pay');
                $payrollStats = [
                    ['label' => 'Gross Pay', 'value' => number_format($grossPay, 0), 'color' => 'green'],
                    ['label' => 'Net Pay',   'value' => number_format($netPay, 0),   'color' => 'blue'],
                ];
            }

            // Leave — current month
            $leaveStats = [];
            if (class_exists('\Automas\Hrm\Models\LeaveApplication')) {
                $rows = LeaveApplication::where('created_by', $cid)
                    ->whereBetween('start_date', [$monthStart, $monthEnd])
                    ->selectRaw('status, count(*) as cnt')
                    ->groupBy('status')
                    ->pluck('cnt', 'status');
                $leaveStats = [
                    ['label' => 'Total',    'value' => $rows->sum(),              'color' => 'blue'],
                    ['label' => 'Approved', 'value' => $rows->get('approved', 0), 'color' => 'green'],
                ];
            }

            // Sales Invoice — current month
            $salesStats = [];
            if (class_exists('\App\Models\SalesInvoice')) {
                $rows = SalesInvoice::whereRaw('created_by = ?', [$cid])
                    ->whereBetween('invoice_date', [$monthStart, $monthEnd])
                    ->selectRaw('status, count(*) as cnt')
                    ->groupBy('status')
                    ->pluck('cnt', 'status');
                $salesStats = [
                    ['label' => 'Total', 'value' => $rows->sum(),           'color' => 'blue'],
                    ['label' => 'Paid',  'value' => $rows->get('paid', 0),  'color' => 'green'],
                    ['label' => 'Draft', 'value' => $rows->get('draft', 0), 'color' => 'yellow'],
                ];
            }

            // Purchase Invoice — current month
            $purchaseStats = [];
            if (class_exists('\App\Models\PurchaseInvoice')) {
                $rows = PurchaseInvoice::whereRaw('created_by = ?', [$cid])
                    ->whereBetween('invoice_date', [$monthStart, $monthEnd])
                    ->selectRaw('status, count(*) as cnt')
                    ->groupBy('status')
                    ->pluck('cnt', 'status');
                $purchaseStats = [
                    ['label' => 'Total', 'value' => $rows->sum(),           'color' => 'blue'],
                    ['label' => 'Paid',  'value' => $rows->get('paid', 0),  'color' => 'green'],
                    ['label' => 'Draft', 'value' => $rows->get('draft', 0), 'color' => 'yellow'],
                ];
            }

            // Deal Pipeline — current month
            $dealStats = [];
            if (class_exists('\Automas\Lead\Models\Deal')) {
                $rows = Deal::where('created_by', $cid)
                    ->whereBetween('created_at', [$monthStart.' 00:00:00', $monthEnd.' 23:59:59'])
                    ->selectRaw('CAST(status AS CHAR) as status, count(*) as cnt')
                    ->groupBy('status')
                    ->pluck('cnt', 'status');
                $dealStats = [
                    ['label' => 'Total', 'value' => $rows->sum(),          'color' => 'blue'],
                    ['label' => 'Won',   'value' => $rows->get('Won', 0),  'color' => 'green'],
                    ['label' => 'Loss',  'value' => $rows->get('Loss', 0), 'color' => 'red'],
                ];
            }

            // Product Stock — this month (matches ProductStockReport auto-load)
            $stockStats = [];
            if (class_exists('\Automas\ProductService\Models\ProductServiceItem')) {
                $stockRows     = ProductServiceItem::where('product_service_items.created_by', $cid)
                    ->whereIn('product_service_items.type', ['product', 'part'])
                    ->leftJoin('warehouse_stocks', 'product_service_items.id', '=', 'warehouse_stocks.product_id')
                    ->selectRaw('product_service_items.id, COALESCE(SUM(warehouse_stocks.quantity), 0) as qty')
                    ->groupBy('product_service_items.id')
                    ->get();
                $totalProducts = $stockRows->count();
                $inStock       = $stockRows->filter(fn($r) => $r->qty > 0)->count();
                $stockStats = [
                    ['label' => 'Total Products', 'value' => $totalProducts,             'color' => 'blue'],
                    ['label' => 'In Stock',        'value' => $inStock,                  'color' => 'green'],
                    ['label' => 'Out of Stock',    'value' => $totalProducts - $inStock,  'color' => 'red'],
                ];
            }

            // Project Progress — no date filter (auto-load sends no date, shows all projects)
            $projectStats = [];
            if (class_exists('\Automas\Taskly\Models\Project')) {
                $projectRows   = Project::where('created_by', $cid)
                    ->selectRaw('count(*) as cnt, SUM(budget) as total_budget')
                    ->first();
                $projectStats = [
                    ['label' => 'Total Projects', 'value' => $projectRows->cnt,                          'color' => 'blue'],
                    ['label' => 'Total Budget',   'value' => number_format($projectRows->total_budget, 0), 'color' => 'green'],
                ];
            }

            $hrmActive = module_is_active('Hrm');
            $leadActive = module_is_active('Lead');
            $tasklyActive = module_is_active('Taskly');

            $reports = [
                ...($hrmActive ? [[
                    'type'        => 'attendance_summary',
                    'name'        => 'Attendance Summary',
                    'description' => 'Track employee attendance, late arrivals, and overtime',
                    'module'      => 'HRM',
                    'icon'        => 'calendar-check',
                    'stats'       => $attendanceStats,
                ]] : []),
                ...($hrmActive ? [[
                    'type'        => 'payroll_sheet',
                    'name'        => 'Payroll Sheet',
                    'description' => 'Employee salary breakdown with allowances and deductions',
                    'module'      => 'HRM',
                    'icon'        => 'currency-dollar',
                    'stats'       => $payrollStats,
                ]] : []),
                ...($hrmActive ? [[
                    'type'        => 'leave_report',
                    'name'        => 'Leave Report',
                    'description' => 'Track employee leave applications and leave balances',
                    'module'      => 'HRM',
                    'icon'        => 'calendar-days',
                    'stats'       => $leaveStats,
                ]] : []),
                [
                    'type'        => 'sales_invoice_report',
                    'name'        => 'Sales Invoice Report',
                    'description' => 'Track sales invoices with customer, amount and payment',
                    'module'      => 'Sales',
                    'icon'        => 'currency-dollar',
                    'stats'       => $salesStats,
                ],
                [
                    'type'        => 'purchase_invoice_report',
                    'name'        => 'Purchase Invoice Report',
                    'description' => 'Track purchase invoices with vendor, amount and payment status',
                    'module'      => 'Purchase',
                    'icon'        => 'shopping-cart',
                    'stats'       => $purchaseStats,
                ],
                ...($leadActive ? [[
                    'type'        => 'deal_pipeline_report',
                    'name'        => 'Deal Pipeline Report',
                    'description' => 'Sales pipeline analysis with deal stages, win/loss rates and deal values',
                    'module'      => 'CRM',
                    'icon'        => 'pipeline',
                    'stats'       => $dealStats,
                ]] : []),
                ...($leadActive ? [[
                    'type'        => 'lead_report',
                    'name'        => 'Lead Report',
                    'description' => 'Dynamic lead performance report with user, stage, source, label and date filters',
                    'module'      => 'CRM',
                    'icon'        => 'users',
                    'stats'       => [
                        ['label' => 'Total Leads', 'value' => 0, 'color' => 'blue'],
                    ],
                ]] : []),
                [
                    'type'        => 'product_stock_report',
                    'name'        => 'Product Stock Report',
                    'description' => 'Inventory overview with stock quantities, values and warehouse-wise breakdown',
                    'module'      => 'Inventory',
                    'icon'        => 'package',
                    'stats'       => $stockStats,
                ],
                ...($tasklyActive ? [[
                    'type'        => 'project_progress_report',
                    'name'        => 'Project Progress Report',
                    'description' => 'Project tracking with task completion, milestones and budget status',
                    'module'      => 'Project',
                    'icon'        => 'chart-bar',
                    'stats'       => $projectStats,
                ]] : []),
            ];

            return Inertia::render('SmartReports/Dashboard', [
                'reports' => $reports,
                'message' => __('Smart Reports - Generate pre-built reports with smart filters.'),
            ]);
        }
        return back()->with('error', __('Permission denied'));
    }

    public function attendanceReport(Request $request)
    {
        if (Auth::user()->can('manage-attendance-reports')) {
            return Inertia::render('SmartReports/AttendanceReport');
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function payrollReport(Request $request)
    {
        if (Auth::user()->can('manage-payroll-reports')) {
            return Inertia::render('SmartReports/PayrollReport');
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function leaveReport(Request $request)
    {
        if (Auth::user()->can('manage-leave-reports')) {
            return Inertia::render('SmartReports/LeaveReport');
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function salesInvoiceReport(Request $request)
    {
        if (Auth::user()->can('manage-sales-invoice-reports')) {
            return Inertia::render('SmartReports/SalesInvoiceReport');
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function purchaseInvoiceReport(Request $request)
    {
        if (Auth::user()->can('manage-purchase-invoice-reports')) {
            return Inertia::render('SmartReports/PurchaseInvoiceReport');
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function dealPipelineReport(Request $request)
    {
        if (Auth::user()->can('manage-deal-pipeline-reports')) {
            return Inertia::render('SmartReports/DealPipelineReport');
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function leadReport(Request $request)
    {
        if (Auth::user()->can('manage-deal-pipeline-reports')) {
            return Inertia::render('SmartReports/LeadReport');
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function productStockReport(Request $request)
    {
        if (Auth::user()->can('manage-product-stock-reports')) {
            return Inertia::render('SmartReports/ProductStockReport');
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function projectProgressReport(Request $request)
    {
        if (Auth::user()->can('manage-project-progress-reports')) {
            return Inertia::render('SmartReports/ProjectProgressReport');
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }
}
