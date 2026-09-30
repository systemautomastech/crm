<?php

namespace Automas\AIBusinessAdvisor\Services;

use Illuminate\Support\Facades\DB;

class DataAggregationService
{
    /**
     * Collect financial metrics from the Account module.
     */
    public function getFinancialMetrics(int $userId): array
    {
        $currentMonth = now()->month;
        $currentYear  = now()->year;
        $lastMonth    = now()->subMonth()->month;
        $lastYear     = now()->subMonth()->year;

        // Revenue this month vs last month (from journal_entries)
        $revenueThisMonth = DB::table('journal_entries')
            ->where('created_by', $userId)
            ->where('status', 'posted')
            ->whereIn('reference_type', [
                'revenue', 'sales_invoice', 'service_invoice', 'pos_sale', 'customer_payment',
                'mobile_service_payment', 'fleet_booking_payment', 'beauty_booking_payment',
                'hotel_booking_payment', 'parking_booking_payment', 'vehicle_booking_payment',
                'laundry_payment', 'medical_order_payment', 'event_booking_payment',
                'fee_receive', 'membership_plan_payment', 'project_payment'
            ])
            ->whereMonth('journal_date', $currentMonth)
            ->whereYear('journal_date', $currentYear)
            ->sum('total_credit') ?? 0;

        $revenueLastMonth = DB::table('journal_entries')
            ->where('created_by', $userId)
            ->where('status', 'posted')
            ->whereIn('reference_type', [
                'revenue', 'sales_invoice', 'service_invoice', 'pos_sale', 'customer_payment',
                'mobile_service_payment', 'fleet_booking_payment', 'beauty_booking_payment',
                'hotel_booking_payment', 'parking_booking_payment', 'vehicle_booking_payment',
                'laundry_payment', 'medical_order_payment', 'event_booking_payment',
                'fee_receive', 'membership_plan_payment', 'project_payment'
            ])
            ->whereMonth('journal_date', $lastMonth)
            ->whereYear('journal_date', $lastYear)
            ->sum('total_credit') ?? 0;

        // Total expenses this month
        $expensesThisMonth = DB::table('journal_entries')
            ->where('created_by', $userId)
            ->where('status', 'posted')
            ->whereIn('reference_type', [
                'expense', 'purchase_invoice', 'vendor_payment', 'payroll', 'commission_payment',
                'dairy_cattle_expense_tracking', 'catering_expense_tracking',
                'fleet_expense', 'case_expense', 'laundry_expense'
            ])
            ->whereMonth('journal_date', $currentMonth)
            ->whereYear('journal_date', $currentYear)
            ->sum('total_debit') ?? 0;

        // Overdue sales invoices
        $overdueInvoices = DB::table('sales_invoices')
            ->where('created_by', $userId)
            ->where(function ($query) {
                $query->where('status', 'overdue')
                    ->orWhere(function ($q) {
                        $q->where('status', '!=', 'paid')
                            ->where('due_date', '<', now()->toDateString());
                    });
            })
            ->select(DB::raw('COUNT(*) as count'), DB::raw('SUM(balance_amount) as total'))
            ->first();

        // Cash balance (bank accounts)
        $cashBalance = DB::table('bank_accounts')
            ->where('created_by', $userId)
            ->where('is_active', 1)
            ->sum('current_balance') ?? 0;

        // Accounts receivable
        $accountsReceivable = DB::table('sales_invoices')
            ->where('created_by', $userId)
            ->whereIn('status', ['posted', 'partial'])
            ->sum('balance_amount') ?? 0;

        // Accounts payable
        $accountsPayable = DB::table('purchase_invoices')
            ->where('created_by', $userId)
            ->whereIn('status', ['posted', 'partial'])
            ->sum('balance_amount') ?? 0;

        $profit = $revenueThisMonth - $expensesThisMonth;
        $profitMargin = $revenueThisMonth > 0
            ? round(($profit / $revenueThisMonth) * 100, 2)
            : 0;

        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 2)
            : 0;

        // Active customers this month
        $activeCustomers = DB::table('sales_invoices')
            ->where('created_by', $userId)
            ->whereMonth('invoice_date', $currentMonth)
            ->whereYear('invoice_date', $currentYear)
            ->distinct('customer_id')
            ->count();

        // Invoice aging (past due)
        $invoiceAging = DB::table('sales_invoices')
            ->where('created_by', $userId)
            ->whereIn('status', ['posted', 'partial'])
            ->select(
                DB::raw('COUNT(CASE WHEN due_date >= CURDATE() THEN 1 END) as due_future'),
                DB::raw('COUNT(CASE WHEN due_date < CURDATE() AND due_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as due_30_days'),
                DB::raw('COUNT(CASE WHEN due_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND due_date >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) THEN 1 END) as due_60_days'),
                DB::raw('COUNT(CASE WHEN due_date < DATE_SUB(CURDATE(), INTERVAL 60 DAY) THEN 1 END) as due_90_plus')
            )
            ->first();

        // Return rate (credit notes vs sales)
        $totalSalesInvoices = DB::table('sales_invoices')
            ->where('created_by', $userId)
            ->whereMonth('invoice_date', $currentMonth)
            ->whereYear('invoice_date', $currentYear)
            ->count();

        $totalCreditNotes = DB::table('credit_notes')
            ->where('created_by', $userId)
            ->whereMonth('credit_note_date', $currentMonth)
            ->whereYear('credit_note_date', $currentYear)
            ->count();

        $returnRate = $totalSalesInvoices > 0
            ? round(($totalCreditNotes / $totalSalesInvoices) * 100, 2)
            : 0;

        return [
            'revenue_this_month'      => (float) $revenueThisMonth,
            'revenue_last_month'      => (float) $revenueLastMonth,
            'revenue_growth_percent'  => (float) $revenueGrowth,
            'expenses_this_month'     => (float) $expensesThisMonth,
            'profit'                  => (float) $profit,
            'profit_margin_percent'   => (float) $profitMargin,
            'cash_balance'            => (float) $cashBalance,
            'accounts_receivable'     => (float) $accountsReceivable,
            'accounts_payable'        => (float) $accountsPayable,
            'overdue_invoices_count'  => (int) ($overdueInvoices->count ?? 0),
            'overdue_invoices_amount' => (float) ($overdueInvoices->total ?? 0),
            'active_customers_month'  => (int) $activeCustomers,
            'invoices_30_days_past'   => (int) ($invoiceAging->due_30_days ?? 0),
            'invoices_60_days_past'   => (int) ($invoiceAging->due_60_days ?? 0),
            'invoices_90_plus_past'   => (int) ($invoiceAging->due_90_plus ?? 0),
            'return_rate_percent'     => (float) $returnRate,
        ];
    }

    /**
     * Collect HRM metrics.
     */
    public function getHrmMetrics(int $userId): array
    {
        $today = now()->toDateString();
        $currentMonth = now()->month;
        $currentYear  = now()->year;

        // Total active employees
        $totalEmployees = DB::table('employees')
            ->where('created_by', $userId)
            ->whereNotNull('user_id')
            ->count();

        // New hires this month
        $newHires = DB::table('employees')
            ->where('created_by', $userId)
            ->whereMonth('date_of_joining', $currentMonth)
            ->whereYear('date_of_joining', $currentYear)
            ->count();

        // Attendance rate this month
        $attendanceData = DB::table('attendances')
            ->where('created_by', $userId)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw("COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count")
            )
            ->first();

        $attendanceRate = ($attendanceData->total > 0)
            ? round(($attendanceData->present_count / $attendanceData->total) * 100, 2)
            : 0;

        // Pending leave requests
        $pendingLeaves = DB::table('leave_applications')
            ->where('created_by', $userId)
            ->where('status', 'pending')
            ->count();

        // Total payroll this month (from payrolls table)
        $payrollThisMonth = DB::table('payrolls')
            ->where('created_by', $userId)
            ->whereMonth('pay_date', $currentMonth)
            ->whereYear('pay_date', $currentYear)
            ->where('status', 'completed')
            ->sum('total_net_pay') ?? 0;

        // Top performer (most tasks completed this month)
        $topPerformer = DB::table('project_tasks as pt')
            ->join('task_stages as ts', 'ts.id', '=', 'pt.stage_id')
            ->join('users as u', 'u.id', '=', DB::raw(
                'JSON_UNQUOTE(JSON_EXTRACT(pt.assigned_to, "$[0]"))'
            ))
            ->where('pt.created_by', $userId)
            ->where('ts.complete', 1)
            ->whereMonth('pt.updated_at', $currentMonth)
            ->groupBy('u.id', 'u.name')
            ->select('u.name', DB::raw('COUNT(pt.id) as tasks_count'))
            ->orderByDesc('tasks_count')
            ->first();

        return [
            'total_employees'         => (int) $totalEmployees,
            'new_hires_this_month'    => (int) $newHires,
            'attendance_rate_percent' => (float) $attendanceRate,
            'pending_leave_requests'  => (int) $pendingLeaves,
            'payroll_this_month'      => (float) $payrollThisMonth,
            'top_performer_name'      => $topPerformer->name ?? null,
            'top_performer_tasks'     => (int) ($topPerformer->tasks_count ?? 0),
        ];
    }

    /**
     * Collect Sales / CRM metrics from the Lead module.
     */
    public function getSalesMetrics(int $userId): array
    {
        $currentMonth = now()->month;
        $currentYear  = now()->year;
        $thirtyDaysAgo = now()->subDays(30)->toDateString();

        // Active leads
        $activeLeads = DB::table('leads')
            ->where('created_by', $userId)
            ->where('is_active', 1)
            ->where('is_converted', 0)
            ->count();

        // Inactive leads (no activity 30+ days)
        $inactiveLeads = DB::table('leads')
            ->where('created_by', $userId)
            ->where('is_active', 1)
            ->where('is_converted', 0)
            ->where('updated_at', '<', $thirtyDaysAgo)
            ->count();

        // Total pipeline value (open deals, status = '0')
        $pipelineValue = DB::table('deals')
            ->where('created_by', $userId)
            ->where('status', '0')
            ->where('is_active', 1)
            ->sum('price') ?? 0;

        // Deals won this month (status = '1')
        $dealsWonThisMonth = DB::table('deals')
            ->where('created_by', $userId)
            ->where('status', '1')
            ->whereMonth('updated_at', $currentMonth)
            ->whereYear('updated_at', $currentYear)
            ->count();

        // Deals won value this month
        $dealsWonValueThisMonth = DB::table('deals')
            ->where('created_by', $userId)
            ->where('status', '1')
            ->whereMonth('updated_at', $currentMonth)
            ->whereYear('updated_at', $currentYear)
            ->sum('price') ?? 0;

        // Total deals (for conversion rate)
        $totalDeals = DB::table('deals')
            ->where('created_by', $userId)
            ->whereIn('status', ['1', '2']) // won + lost
            ->whereMonth('updated_at', $currentMonth)
            ->count();

        $conversionRate = $totalDeals > 0
            ? round(($dealsWonThisMonth / $totalDeals) * 100, 2)
            : 0;

        // Sales invoices this month
        $salesInvoicesThisMonth = DB::table('sales_invoices')
            ->where('created_by', $userId)
            ->whereIn('status', ['posted', 'partial', 'paid'])
            ->whereMonth('invoice_date', $currentMonth)
            ->whereYear('invoice_date', $currentYear)
            ->select(
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total')
            )
            ->first();

        return [
            'active_leads'               => (int) $activeLeads,
            'inactive_leads_30_days'     => (int) $inactiveLeads,
            'pipeline_value'             => (float) $pipelineValue,
            'deals_won_this_month'       => (int) $dealsWonThisMonth,
            'deals_won_value_this_month' => (float) $dealsWonValueThisMonth,
            'conversion_rate_percent'    => (float) $conversionRate,
            'sales_invoices_count'       => (int) ($salesInvoicesThisMonth->count ?? 0),
            'sales_invoices_total'       => (float) ($salesInvoicesThisMonth->total ?? 0),
        ];
    }

    /**
     * Collect Project metrics from the Taskly module.
     */
    public function getProjectMetrics(int $userId): array
    {
        $today = now()->toDateString();
        $currentMonth = now()->month;

        // Project counts by status
        $projectStats = DB::table('projects')
            ->where('created_by', $userId)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Delayed projects (end_date passed, not finished)
        $delayedProjects = DB::table('projects')
            ->where('created_by', $userId)
            ->where('status', '!=', 'Finished')
            ->where('end_date', '<', $today)
            ->count();

        // Budget utilization (milestone costs vs project budgets)
        $budgetData = DB::table('projects as p')
            ->leftJoin('project_milestones as pm', 'pm.project_id', '=', 'p.id')
            ->where('p.created_by', $userId)
            ->where('p.status', 'Ongoing')
            ->select(
                DB::raw('SUM(p.budget) as total_budget'),
                DB::raw('SUM(pm.cost) as total_milestone_cost')
            )
            ->first();

        $budgetUtilization = ($budgetData->total_budget > 0)
            ? round(($budgetData->total_milestone_cost / $budgetData->total_budget) * 100, 2)
            : 0;

        // Task completion rate this month
        $taskStats = DB::table('project_tasks as pt')
            ->join('task_stages as ts', 'ts.id', '=', 'pt.stage_id')
            ->where('pt.created_by', $userId)
            ->whereMonth('pt.updated_at', $currentMonth)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN ts.complete = 1 THEN 1 ELSE 0 END) as completed')
            )
            ->first();

        $taskCompletionRate = ($taskStats->total > 0)
            ? round(($taskStats->completed / $taskStats->total) * 100, 2)
            : 0;

        // Projects finishing in next 7 days (at risk)
        $projectsDueSoon = DB::table('projects')
            ->where('created_by', $userId)
            ->where('status', 'Ongoing')
            ->whereBetween('end_date', [$today, now()->addDays(7)->toDateString()])
            ->count();

        return [
            'active_projects'                => (int) ($projectStats['Ongoing'] ?? 0),
            'on_hold_projects'               => (int) ($projectStats['Onhold'] ?? 0),
            'finished_projects'              => (int) ($projectStats['Finished'] ?? 0),
            'delayed_projects'               => (int) $delayedProjects,
            'projects_due_in_7_days'         => (int) $projectsDueSoon,
            'budget_utilization_percent'     => (float) $budgetUtilization,
            'task_completion_rate_percent'   => (float) $taskCompletionRate,
        ];
    }

    /**
     * Collect POS metrics.
     */
    public function getPosMetrics(int $userId): array
    {
        $today = now()->toDateString();
        $currentMonth = now()->month;
        $currentYear  = now()->year;
        $lastMonth    = now()->subMonth()->month;
        $lastYear     = now()->subMonth()->year;

        // Today's POS revenue
        $todayRevenue = DB::table('pos as p')
            ->join('pos_payments as pp', 'pp.pos_id', '=', 'p.id')
            ->where('p.created_by', $userId)
            ->where('p.pos_date', $today)
            ->where('p.status', 'completed')
            ->sum('pp.amount') ?? 0;

        // This month's POS revenue
        $monthRevenue = DB::table('pos as p')
            ->join('pos_payments as pp', 'pp.pos_id', '=', 'p.id')
            ->where('p.created_by', $userId)
            ->where('p.status', 'completed')
            ->whereMonth('p.pos_date', $currentMonth)
            ->whereYear('p.pos_date', $currentYear)
            ->sum('pp.amount') ?? 0;

        // Last month's POS revenue (for comparison)
        $lastMonthRevenue = DB::table('pos as p')
            ->join('pos_payments as pp', 'pp.pos_id', '=', 'p.id')
            ->where('p.created_by', $userId)
            ->where('p.status', 'completed')
            ->whereMonth('p.pos_date', $lastMonth)
            ->whereYear('p.pos_date', $lastYear)
            ->sum('pp.amount') ?? 0;

        $posGrowth = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2)
            : 0;

        // Top selling product this month
        $topProduct = DB::table('pos_items as pi')
            ->join('pos as p', 'p.id', '=', 'pi.pos_id')
            ->join('product_service_items as psi', 'psi.id', '=', 'pi.product_id')
            ->where('p.created_by', $userId)
            ->where('p.status', 'completed')
            ->whereMonth('p.pos_date', $currentMonth)
            ->whereYear('p.pos_date', $currentYear)
            ->groupBy('psi.id', 'psi.name')
            ->select('psi.name', DB::raw('SUM(pi.quantity) as units_sold'), DB::raw('SUM(pi.total_amount) as revenue'))
            ->orderByDesc('units_sold')
            ->first();

        // Total transactions this month
        $totalTransactions = DB::table('pos')
            ->where('created_by', $userId)
            ->where('status', 'completed')
            ->whereMonth('pos_date', $currentMonth)
            ->whereYear('pos_date', $currentYear)
            ->count();

        return [
            'today_pos_revenue'        => (float) $todayRevenue,
            'month_pos_revenue'        => (float) $monthRevenue,
            'pos_growth_percent'       => (float) $posGrowth,
            'total_transactions_month' => (int) $totalTransactions,
            'top_product_name'         => $topProduct->name ?? null,
            'top_product_units'        => (int) ($topProduct->units_sold ?? 0),
            'top_product_revenue'      => (float) ($topProduct->revenue ?? 0),
        ];
    }

    /**
     * Collect Inventory / Stock metrics from ProductService module.
     */
    public function getInventoryMetrics(int $userId): array
    {
        // Total active products
        $totalProducts = DB::table('product_service_items')
            ->where('created_by', $userId)
            ->where('is_active', 1)
            ->count();

        // Out of stock (total warehouse stock = 0)
        $outOfStock = DB::table('product_service_items as psi')
            ->leftJoin('warehouse_stocks as ws', 'ws.product_id', '=', 'psi.id')
            ->where('psi.created_by', $userId)
            ->where('psi.is_active', 1)
            ->select('psi.id')
            ->groupBy('psi.id')
            ->havingRaw('COALESCE(SUM(ws.quantity), 0) = 0')
            ->count();

        // Low stock (quantity < 10)
        $lowStock = DB::table('product_service_items as psi')
            ->join('warehouse_stocks as ws', 'ws.product_id', '=', 'psi.id')
            ->where('psi.created_by', $userId)
            ->where('psi.is_active', 1)
            ->select('psi.id')
            ->groupBy('psi.id')
            ->havingRaw('SUM(ws.quantity) > 0 AND SUM(ws.quantity) < 10')
            ->count();

        // Total stock value
        $stockValue = DB::table('product_service_items as psi')
            ->join('warehouse_stocks as ws', 'ws.product_id', '=', 'psi.id')
            ->where('psi.created_by', $userId)
            ->where('psi.is_active', 1)
            ->select(DB::raw('SUM(ws.quantity * psi.sale_price) as total_value'))
            ->value('total_value') ?? 0;

        // Active warehouses
        $totalWarehouses = DB::table('warehouses')
            ->where('created_by', $userId)
            ->where('is_active', 1)
            ->count();

        return [
            'total_products'     => (int) $totalProducts,
            'out_of_stock_count' => (int) $outOfStock,
            'low_stock_count'    => (int) $lowStock,
            'total_stock_value'  => (float) $stockValue,
            'total_warehouses'   => (int) $totalWarehouses,
        ];
    }

    /**
     * Master aggregator — returns all metrics for a user.
     */
    public function getAllMetrics(int $userId): array
    {
        return [
            'generated_at' => now()->toDateTimeString(),
            'financial'    => $this->getFinancialMetrics($userId),
            'hrm'          => $this->getHrmMetrics($userId),
            'sales'        => $this->getSalesMetrics($userId),
            'projects'     => $this->getProjectMetrics($userId),
            'pos'          => $this->getPosMetrics($userId),
            'inventory'    => $this->getInventoryMetrics($userId),
        ];
    }
}
