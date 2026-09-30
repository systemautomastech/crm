<?php

namespace Automas\Account\Http\Controllers;

use App\Models\SalesInvoiceReturn;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Account\Models\Customer;
use Automas\Account\Models\Vendor;
use Automas\Account\Models\CustomerPayment;
use Automas\Account\Models\VendorPayment;
use Automas\Account\Models\Revenue;
use Automas\Account\Models\Expense;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->can('manage-account-dashboard')) {
            $user = Auth::user();
            $userType = $user->type;

            switch ($userType) {
                case 'company':
                    return $this->companyDashboard($request);
                case 'vendor':
                    return $this->vendorDashboard($request);
                case 'client':
                    return $this->clientDashboard($request);
                case 'staff':
                default:
                    return $this->staffDashboard($request);
            }
        }
        return back()->with('error', __('Permission denied'));
    }

    private function getDateRange(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $now = Carbon::now();

        switch ($period) {
            case 'prev_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $start = $now->copy()->subMonths(3)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'last_6_months':
                $start = $now->copy()->subMonths(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'custom':
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $start = Carbon::parse($request->get('start_date'))->startOfDay();
                    $end = Carbon::parse($request->get('end_date'))->endOfDay();
                } else {
                    $start = $now->copy()->startOfMonth();
                    $end = $now->copy()->endOfMonth();
                }
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
        }

        return [$start, $end];
    }

    private function companyDashboard(Request $request)
    {
        $creatorId = creatorId();
        [$start, $end] = $this->getDateRange($request);

        $totalClients = Customer::where('created_by', $creatorId)->count();
        $totalVendors = Vendor::where('created_by', $creatorId)->count();

        // Period-filtered Revenue & Expenses
        $totalRevenue = Revenue::where('created_by', $creatorId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
        if ($totalRevenue == 0 && !$request->has('period')) {
            $totalRevenue = Revenue::where('created_by', $creatorId)->sum('amount');
        }

        $totalExpense = Expense::where('created_by', $creatorId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
        if ($totalExpense == 0 && !$request->has('period')) {
            $totalExpense = Expense::where('created_by', $creatorId)->sum('amount');
        }

        // Period-filtered Customer & Vendor Payments
        $totalCustomerPayments = CustomerPayment::where('created_by', $creatorId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('payment_amount');
        if ($totalCustomerPayments == 0 && !$request->has('period')) {
            $totalCustomerPayments = CustomerPayment::where('created_by', $creatorId)->sum('payment_amount');
            if ($totalCustomerPayments == 0) {
                $totalCustomerPayments = CustomerPayment::whereHas('customer', function ($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })->sum('payment_amount');
            }
        }

        $totalVendorPayments = VendorPayment::where('created_by', $creatorId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('payment_amount');
        if ($totalVendorPayments == 0 && !$request->has('period')) {
            $totalVendorPayments = VendorPayment::where('created_by', $creatorId)->sum('payment_amount');
            if ($totalVendorPayments == 0) {
                $totalVendorPayments = VendorPayment::whereHas('vendor', function ($q) use ($creatorId) {
                    $q->where('created_by', $creatorId);
                })->sum('payment_amount');
            }
        }

        $netProfit = $totalRevenue - $totalExpense;

        $recentRevenues = Revenue::where('created_by', $creatorId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->revenue_number ?? ('REV-' . $item->id),
                    'description' => $item->description ?? 'Revenue transaction',
                    'amount' => $item->amount,
                    'date' => $item->created_at ? $item->created_at->format('M d, Y') : ''
                ];
            });

        $recentExpenses = Expense::where('created_by', $creatorId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->expense_number ?? ('EXP-' . $item->id),
                    'description' => $item->description ?? 'Expense transaction',
                    'amount' => $item->amount,
                    'date' => $item->created_at ? $item->created_at->format('M d, Y') : ''
                ];
            });

        $monthlyCustomerPayments = [];
        $monthlyVendorPayments = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M');

            $customerPayments = CustomerPayment::where('created_by', $creatorId)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('payment_amount');

            $vendorPayments = VendorPayment::where('created_by', $creatorId)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('payment_amount');

            $monthlyCustomerPayments[] = [
                'month' => $monthName,
                'customer_payments' => $customerPayments
            ];

            $monthlyVendorPayments[] = [
                'month' => $monthName,
                'vendor_payments' => $vendorPayments
            ];
        }

        return Inertia::render('Account/Dashboard/CompanyDashboard', [
            'stats' => [
                'total_clients' => $totalClients,
                'total_vendors' => $totalVendors,
                'total_revenue' => $totalRevenue,
                'total_expense' => $totalExpense,
                'total_customer_payment' => $totalCustomerPayments,
                'total_vendor_payment' => $totalVendorPayments,
                'net_profit' => $netProfit
            ],
            'monthlyCustomerPayments' => $monthlyCustomerPayments,
            'monthlyVendorPayments' => $monthlyVendorPayments,
            'recentRevenues' => $recentRevenues,
            'recentExpenses' => $recentExpenses
        ]);
    }

    private function vendorDashboard(Request $request)
    {
        $user = Auth::user();
        [$start, $end] = $this->getDateRange($request);

        $totalPayments = VendorPayment::where('vendor_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->sum('payment_amount');
        if ($totalPayments == 0 && !$request->has('period')) {
            $totalPayments = VendorPayment::where('vendor_id', $user->id)->sum('payment_amount');
        }

        $totalExpenses = Expense::where('created_by', $user->created_by)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
        if ($totalExpenses == 0 && !$request->has('period')) {
            $totalExpenses = Expense::where('created_by', $user->created_by)->sum('amount');
        }

        $paymentCount = VendorPayment::where('vendor_id', $user->id)->count();

        $monthlyPayments = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M');

            $monthPayments = VendorPayment::where('vendor_id', $user->id)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('payment_amount');

            $monthlyPayments[] = [
                'month' => $monthName,
                'payments' => $monthPayments
            ];
        }

        // Dynamic return purchase invoices
        $recentReturnInvoices = collect();
        if (class_exists('\\App\Models\\PurchaseReturn')) {
            $recentReturnInvoices = \App\Models\PurchaseReturn::where('vendor_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($return) {
                    return [
                        'id' => $return->id,
                        'invoice_number' => $return->return_number ?? 'PUR-RET-' . $return->id,
                        'amount' => $return->total_amount ?? 0,
                        'date' => $return->created_at ? $return->created_at->format('M d, Y') : '',
                        'status' => $return->status ?? 'Pending'
                    ];
                });
        }

        // Dynamic debit notes
        $recentDebitNotes = collect();
        if (class_exists('\\Automas\\Account\\Models\\DebitNote')) {
            $recentDebitNotes = \Automas\Account\Models\DebitNote::where('vendor_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'debit_note_number' => $note->debit_note_number ?? 'DN-' . $note->id,
                        'amount' => $note->total_amount ?? 0,
                        'date' => $note->created_at ? $note->created_at->format('M d, Y') : '',
                        'status' => $note->status ?? 'Pending'
                    ];
                });
        }

        return Inertia::render('Account/Dashboard/VendorDashboard', [
            'stats' => [
                'total_payments' => $totalPayments,
                'total_expenses' => $totalExpenses,
                'payment_count' => $paymentCount
            ],
            'monthlyPayments' => $monthlyPayments,
            'recentReturnInvoices' => $recentReturnInvoices,
            'recentDebitNotes' => $recentDebitNotes,
            'vendor' => ['name' => $user->name]
        ]);
    }

    private function clientDashboard(Request $request)
    {
        $user = Auth::user();
        [$start, $end] = $this->getDateRange($request);

        $totalPayments = CustomerPayment::where('customer_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->sum('payment_amount');
        if ($totalPayments == 0 && !$request->has('period')) {
            $totalPayments = CustomerPayment::where('customer_id', $user->id)->sum('payment_amount');
        }

        $totalRevenues = Revenue::where('created_by', $user->created_by)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
        if ($totalRevenues == 0 && !$request->has('period')) {
            $totalRevenues = Revenue::where('created_by', $user->created_by)->sum('amount');
        }

        $paymentCount = CustomerPayment::where('customer_id', $user->id)->count();

        $monthlyPayments = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M');

            $monthPayments = CustomerPayment::where('customer_id', $user->id)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('payment_amount');

            $monthlyPayments[] = [
                'month' => $monthName,
                'payments' => $monthPayments
            ];
        }

        // Dynamic return invoices from SalesReturns
        $recentReturnInvoices = collect();
        if (class_exists('\\App\Models\\SalesInvoiceReturn')) {
            $recentReturnInvoices = SalesInvoiceReturn::where('customer_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($return) {
                    return [
                        'id' => $return->id,
                        'invoice_number' => $return->return_number ?? 'RET-' . $return->id,
                        'amount' => $return->total_amount ?? 0,
                        'date' => $return->created_at ? $return->created_at->format('M d, Y') : '',
                        'status' => $return->status ?? 'Pending'
                    ];
                });
        }

        // Dynamic credit notes
        $recentCreditNotes = collect();
        if (class_exists('\\Automas\\Account\\Models\\CreditNote')) {
            $recentCreditNotes = \Automas\Account\Models\CreditNote::where('customer_id', $user->id)
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'credit_note_number' => $note->credit_note_number ?? 'CN-' . $note->id,
                        'amount' => $note->total_amount ?? 0,
                        'date' => $note->created_at ? $note->created_at->format('M d, Y') : '',
                        'status' => $note->status ?? 'Pending'
                    ];
                });
        }

        return Inertia::render('Account/Dashboard/ClientDashboard', [
            'stats' => [
                'total_payments' => $totalPayments,
                'total_revenues' => $totalRevenues,
                'payment_count' => $paymentCount
            ],
            'monthlyPayments' => $monthlyPayments,
            'recentReturnInvoices' => $recentReturnInvoices,
            'recentCreditNotes' => $recentCreditNotes,
            'customer' => ['name' => $user->name]
        ]);
    }

    private function staffDashboard(Request $request)
    {
        $user = Auth::user();
        $creatorId = $user->created_by;
        [$start, $end] = $this->getDateRange($request);

        $totalClients = Customer::where('created_by', $creatorId)->count();
        $totalVendors = Vendor::where('created_by', $creatorId)->count();

        $monthlyRevenue = Revenue::where('created_by', $creatorId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
        if ($monthlyRevenue == 0 && !$request->has('period')) {
            $monthlyRevenue = Revenue::where('created_by', $creatorId)->whereMonth('created_at', Carbon::now()->month)->sum('amount');
        }

        $monthlyExpense = Expense::where('created_by', $creatorId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
        if ($monthlyExpense == 0 && !$request->has('period')) {
            $monthlyExpense = Expense::where('created_by', $creatorId)->whereMonth('created_at', Carbon::now()->month)->sum('amount');
        }

        $recentActivities = collect()
            ->merge(Revenue::where('created_by', $creatorId)->latest()->limit(3)->get()->map(function ($item) {
                return ['type' => 'Revenue', 'title' => $item->revenue_number ?? ('REV-' . $item->id), 'amount' => $item->amount, 'date' => $item->created_at ? $item->created_at->format('M d, Y') : ''];
            }))
            ->merge(Expense::where('created_by', $creatorId)->latest()->limit(3)->get()->map(function ($item) {
                return ['type' => 'Expense', 'title' => $item->expense_number ?? ('EXP-' . $item->id), 'amount' => $item->amount, 'date' => $item->created_at ? $item->created_at->format('M d, Y') : ''];
            }))
            ->sortByDesc('date')
            ->take(6)
            ->values();

        return Inertia::render('Account/Dashboard/StaffDashboard', [
            'stats' => [
                'total_clients' => $totalClients,
                'total_vendors' => $totalVendors,
                'monthly_revenue' => $monthlyRevenue,
                'monthly_expense' => $monthlyExpense
            ],
            'recentActivities' => $recentActivities
        ]);
    }
}
