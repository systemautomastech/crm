<?php

namespace Automas\SalesOrder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\SalesOrder\Models\SalesOrder;
use App\Models\User;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function orderReports(Request $request)
    {
        if (!Auth::user()->can('manage-sales-orders') && !Auth::user()->can('view-reports')) {
            return back()->with('error', __('Permission denied'));
        }

        // Summary Cards
        $orderSummary = $this->getOrderSummary();

        // Order Status Distribution (Pie Chart)
        $orderStatusDistribution = $this->getOrderStatusDistribution();

        // Delivery Status Distribution
        $deliveryStatusDistribution = $this->getDeliveryStatusDistribution();

        // Conversion Metrics
        $conversionMetrics = $this->getOrderConversionMetrics();

        // Staff Orders (Bar Chart with filters)
        $staffOrders = $this->getStaffOrders($request->from_date, $request->to_date, $request->sales_user);

        // Per Month Orders
        $perMonthOrders = $this->getPerMonthOrders();

        // Amount Reports
        $amountSummary = $this->getOrderAmountSummary();
        $monthlyAmounts = $this->getOrderMonthlyAmounts();

        // Get users for dropdown
        $users = User::emp()->where('created_by', '=', creatorId())
            ->select('id', 'name')
            ->get();

        return Inertia::render('SalesOrder/Reports/SalesOrderReports', [
            'orderSummary'               => $orderSummary,
            'orderStatusDistribution'    => $orderStatusDistribution,
            'deliveryStatusDistribution' => $deliveryStatusDistribution,
            'conversionMetrics'          => $conversionMetrics,
            'staffOrders'                => $staffOrders,
            'perMonthOrders'             => $perMonthOrders,
            'amountSummary'              => $amountSummary,
            'monthlyAmounts'             => $monthlyAmounts,
            'users'                      => $users,
            'statuses'                   => $this->getOrderStatuses(),
        ]);
    }

    private function getOrderSummary(): array
    {
        $totalOrders     = SalesOrder::where('created_by', creatorId())->count();
        $confirmedOrders = SalesOrder::where('created_by', creatorId())->where('status', 'confirmed')->count();
        $cancelledOrders = SalesOrder::where('created_by', creatorId())->where('status', 'cancelled')->count();
        $draftOrders     = SalesOrder::where('created_by', creatorId())->where('status', 'draft')->count();
        $deliveredOrders = SalesOrder::where('created_by', creatorId())->where('delivery_status', 'delivered')->count();
        $partialOrders   = SalesOrder::where('created_by', creatorId())->where('delivery_status', 'partial')->count();
        $pendingOrders   = SalesOrder::where('created_by', creatorId())->where('delivery_status', 'pending')->count();

        return [
            'total'     => $totalOrders,
            'confirmed' => $confirmedOrders,
            'cancelled' => $cancelledOrders,
            'draft'     => $draftOrders,
            'delivered' => $deliveredOrders,
            'partial'   => $partialOrders,
            'pending'   => $pendingOrders,
        ];
    }

    private function getOrderStatusDistribution(): array
    {
        $confirmed = SalesOrder::where('created_by', creatorId())->where('status', 'confirmed')->count();
        $cancelled = SalesOrder::where('created_by', creatorId())->where('status', 'cancelled')->count();
        $draft     = SalesOrder::where('created_by', creatorId())->where('status', 'draft')->count();

        return [
            ['name' => __('Confirmed'), 'value' => $confirmed],
            ['name' => __('Cancelled'), 'value' => $cancelled],
            ['name' => __('Draft'),     'value' => $draft],
        ];
    }

    private function getDeliveryStatusDistribution(): array
    {
        $pending   = SalesOrder::where('created_by', creatorId())->where('delivery_status', 'pending')->count();
        $partial   = SalesOrder::where('created_by', creatorId())->where('delivery_status', 'partial')->count();
        $delivered = SalesOrder::where('created_by', creatorId())->where('delivery_status', 'delivered')->count();

        return [
            ['name' => __('Pending'),   'value' => $pending],
            ['name' => __('Partial'),   'value' => $partial],
            ['name' => __('Delivered'), 'value' => $delivered],
        ];
    }

    private function getOrderConversionMetrics(): array
    {
        $totalOrders     = SalesOrder::where('created_by', creatorId())->count();
        $invoicedOrders  = SalesOrder::where('created_by', creatorId())->where('is_invoiced', true)->count();
        $conversionRate  = $totalOrders > 0 ? round(($invoicedOrders / $totalOrders) * 100, 2) : 0;

        return [
            'totalOrders'     => $totalOrders,
            'convertedOrders' => $invoicedOrders,
            'conversionRate'  => $conversionRate,
        ];
    }

    private function getStaffOrders($fromDate = null, $toDate = null, $salesUser = null): array
    {
        $users = User::emp()->where('created_by', '=', creatorId());

        if ($salesUser && $salesUser !== 'all') {
            $users->where('id', $salesUser);
        }

        $users = $users->get();
        $data  = [];

        foreach ($users as $user) {
            $query = SalesOrder::where('created_by', creatorId())
                ->where(function ($q) use ($user) {
                    $q->where('creator_id', $user->id)
                      ->orWhere('acquired_by', $user->id)
                      ->orWhereHas('assignedUsers', fn($sq) => $sq->where('users.id', $user->id));
                });

            if ($fromDate && $toDate) {
                $query->whereDate('order_date', '>=', $fromDate)
                      ->whereDate('order_date', '<=', $toDate);
            }

            $count = $query->count();

            $data[] = [
                'name'   => $user->name,
                'orders' => $count,
            ];
        }

        return $data;
    }

    private function getOrderStatuses(): array
    {
        return [
            ['value' => 'draft',     'label' => __('Draft')],
            ['value' => 'confirmed', 'label' => __('Confirmed')],
            ['value' => 'cancelled', 'label' => __('Cancelled')],
        ];
    }

    private function getPerMonthOrders(): array
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::create(date('Y'), $month)->format('M Y');
            $count = SalesOrder::where('created_by', creatorId())
                ->whereMonth('order_date', $month)
                ->whereYear('order_date', date('Y'))
                ->count();

            $data[] = [
                'month'  => $month,
                'name'   => $monthName,
                'orders' => $count,
            ];
        }

        return $data;
    }

    private function getOrderAmountSummary(): array
    {
        $orders      = SalesOrder::where('sales_orders.created_by', creatorId())->get();
        $totalAmount = $orders->sum(fn($order) => $order->getTotal());

        $confirmedOrders = $orders->where('status', 'confirmed');
        $confirmedAmount = $confirmedOrders->sum(fn($order) => $order->getTotal());

        $cancelledOrders = $orders->where('status', 'cancelled');
        $cancelledAmount = $cancelledOrders->sum(fn($order) => $order->getTotal());

        $draftOrders = $orders->where('status', 'draft');
        $draftAmount = $draftOrders->sum(fn($order) => $order->getTotal());

        return [
            'total'     => $totalAmount,
            'confirmed' => $confirmedAmount,
            'cancelled' => $cancelledAmount,
            'draft'     => $draftAmount,
        ];
    }

    private function getOrderMonthlyAmounts(): array
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::create(date('Y'), $month)->format('M Y');
            $orders    = SalesOrder::where('created_by', creatorId())
                ->whereMonth('order_date', $month)
                ->whereYear('order_date', date('Y'))
                ->get();
            $amount    = $orders->sum(fn($order) => $order->getTotal());

            $data[] = [
                'month'  => $month,
                'name'   => $monthName,
                'amount' => $amount,
            ];
        }

        return $data;
    }
}
