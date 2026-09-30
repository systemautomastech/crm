<?php

namespace Automas\SalesOrder\Http\Controllers;

use App\Http\Controllers\Controller;
use Automas\SalesOrder\Models\SalesOrder;
use Automas\SalesOrder\Models\SalesOrderDelivery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalesOrderDashboardController extends Controller
{
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

    public function index(Request $request)
    {
        $user = Auth::user();

        abort_unless(
            $user->can('manage-sales-orders') ||
            $user->can('view-sales-orders') ||
            $user->can('manage-sales-order-dashboard') ||
            in_array($user->type, ['company', 'superadmin', 'admin']),
            403,
            'You do not have permission to view Sales Order Dashboard.'
        );

        $creatorId = creatorId();
        [$start, $end] = $this->getDateRange($request);

        // ── 1. Stat Summaries ──────────────────────────────────────────────
        $baseSoQuery = SalesOrder::accessible();

        // Total Sales Orders
        $totalSoCount = (clone $baseSoQuery)->count();
        $totalSoValue = (float) ((clone $baseSoQuery)->sum('total_amount') ?? 0);

        // Period Filtered Sales Orders
        $thisMonthSoCount = (clone $baseSoQuery)
            ->whereBetween('order_date', [$start, $end])
            ->count();
        $thisMonthSoValue = (float) ((clone $baseSoQuery)
            ->whereBetween('order_date', [$start, $end])
            ->sum('total_amount') ?? 0);

        if ($thisMonthSoCount == 0 && !$request->has('period')) {
            $thisMonthSoCount = $totalSoCount;
            $thisMonthSoValue = $totalSoValue;
        }

        // Pending Sales Orders
        $pendingSoCount = (clone $baseSoQuery)
            ->where('delivery_status', SalesOrder::DELIVERY_STATUS_PENDING)
            ->where('status', '!=', SalesOrder::STATUS_CANCELLED)
            ->count();
        $pendingSoValue = (float) ((clone $baseSoQuery)
            ->where('delivery_status', SalesOrder::DELIVERY_STATUS_PENDING)
            ->where('status', '!=', SalesOrder::STATUS_CANCELLED)
            ->sum('total_amount') ?? 0);

        // Partially Delivered Sales Orders
        $partialSoCount = (clone $baseSoQuery)
            ->where('delivery_status', SalesOrder::DELIVERY_STATUS_PARTIAL)
            ->count();
        $partialSoValue = (float) ((clone $baseSoQuery)
            ->where('delivery_status', SalesOrder::DELIVERY_STATUS_PARTIAL)
            ->sum('total_amount') ?? 0);

        // ── 2. Delivery Challan Chart Data (Last 15 Days Trend) ─────────────
        $challanChartData = [];
        for ($i = 14; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dateLabel = Carbon::now()->subDays($i)->format('M d');

            $totalDeliveries = SalesOrderDelivery::where('created_by', $creatorId)
                ->whereDate('delivery_date', $date)
                ->count();

            $completedDeliveries = SalesOrderDelivery::where('created_by', $creatorId)
                ->whereDate('delivery_date', $date)
                ->where('status', SalesOrderDelivery::STATUS_DELIVERED)
                ->count();

            $challanChartData[] = [
                'date' => $dateLabel,
                'full_date' => $date,
                'total' => $totalDeliveries,
                'delivered' => $completedDeliveries,
            ];
        }

        // ── 3. Last 5 Sales Orders ─────────────────────────────────────────
        $last5SO = SalesOrder::accessible()
            ->with(['customer:id,name', 'warehouse:id,name'])
            ->latest('order_date')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(function ($so) {
                return [
                    'id' => $so->id,
                    'order_number' => $so->order_number,
                    'customer_name' => $so->customer?->name ?? 'N/A',
                    'warehouse_name' => $so->warehouse?->name ?? 'N/A',
                    'order_date' => $so->order_date ? $so->order_date->format('M d, Y') : 'N/A',
                    'expected_delivery_date' => $so->expected_delivery_date ? $so->expected_delivery_date->format('M d, Y') : null,
                    'status' => $so->status,
                    'delivery_status' => $so->delivery_status,
                    'total_amount' => (float) $so->total_amount,
                ];
            });

        // ── 4. Last 5 Delivery Challans ──────────────────────────────────
        $last5DC = SalesOrderDelivery::where('created_by', $creatorId)
            ->with(['salesOrder:id,order_number,customer_id,warehouse_id', 'salesOrder.customer:id,name', 'salesOrder.warehouse:id,name'])
            ->latest('delivery_date')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(function ($dc) {
                return [
                    'id' => $dc->id,
                    'challan_number' => $dc->delivery_number,
                    'sales_order_number' => $dc->salesOrder?->order_number ?? 'N/A',
                    'customer_name' => $dc->salesOrder?->customer?->name ?? 'N/A',
                    'warehouse_name' => $dc->salesOrder?->warehouse?->name ?? 'N/A',
                    'delivery_date' => $dc->delivery_date ? $dc->delivery_date->format('M d, Y') : 'N/A',
                    'status' => $dc->status,
                    'delivered_by' => $dc->creator?->name ?? 'N/A',
                ];
            });

        // ── 5. Calendar Schedule Events ───────────────────────────────────
        $calendarEvents = [];
        $deliveries = SalesOrderDelivery::where('created_by', $creatorId)
            ->whereNotNull('delivery_date')
            ->with(['salesOrder.customer:id,name'])
            ->get();

        foreach ($deliveries as $del) {
            $calendarEvents[] = [
                'id' => 'dc_' . $del->id,
                'title' => 'Delivery: ' . $del->delivery_number,
                'startDate' => $del->delivery_date->format('Y-m-d'),
                'time' => '10:00 AM',
                'description' => 'Delivery Challan #' . $del->delivery_number . ' for ' . ($del->salesOrder?->customer?->name ?? 'Customer'),
                'type' => 'Delivery Challan',
                'color' => '#10b981',
            ];
        }

        $ordersWithExpected = SalesOrder::accessible()
            ->whereNotNull('expected_delivery_date')
            ->get();

        foreach ($ordersWithExpected as $ord) {
            $calendarEvents[] = [
                'id' => 'so_' . $ord->id,
                'title' => 'SO Expected: ' . $ord->order_number,
                'startDate' => $ord->expected_delivery_date->format('Y-m-d'),
                'time' => '02:00 PM',
                'description' => 'Expected Delivery for ' . $ord->order_number,
                'type' => 'Sales Order',
                'color' => '#3b82f6',
            ];
        }

        return Inertia::render('SalesOrder/Dashboard/Index', [
            'stats' => [
                'total_so' => ['count' => $totalSoCount, 'value' => $totalSoValue],
                'this_month' => ['count' => $thisMonthSoCount, 'value' => $thisMonthSoValue],
                'pending' => ['count' => $pendingSoCount, 'value' => $pendingSoValue],
                'partially_delivered' => ['count' => $partialSoCount, 'value' => $partialSoValue],
            ],
            'challanChartData' => $challanChartData,
            'last5SO' => $last5SO,
            'last5DC' => $last5DC,
            'calendarEvents' => $calendarEvents,
        ]);
    }
}
