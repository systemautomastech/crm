<?php

namespace Automas\Pos\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Pos\Models\PosSale;
use Automas\Pos\Models\PosItem;
use Automas\ProductService\Models\ProductServiceItem;
use App\Models\Warehouse;
use Carbon\Carbon;
use Automas\Pos\Models\Pos;
use Automas\Pos\Models\PosReturn;
use Automas\Pos\Models\PosReturnItem;

class PosReportController extends Controller
{
    public function sales(Request $request)
    {
        if(Auth::user()->can('manage-pos-reports')){
            $creatorId = creatorId();
            
            // Sales data for the report
            $salesData = Pos::where('created_by', $creatorId)
                ->with(['customer:id,name', 'warehouse:id,name', 'items'])
                ->latest()
                ->paginate(20);

            // Calculate total from pos_sale_items
            $salesWithTotals = $salesData->getCollection()->map(function($sale) {
                $sale->total = $sale->items->sum('total_amount');
                return $sale;
            });
            $salesData->setCollection($salesWithTotals);

            // Daily sales for last 7 days
            $dailySales = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $sales = PosItem::where('created_by', $creatorId)
                    ->whereDate('created_at', $date)
                    ->sum('total_amount');
                $count = Pos::where('created_by', $creatorId)
                    ->whereDate('created_at', $date)
                    ->count();
                $dailySales[] = [
                    'date' => $date->format('M d'),
                    'sales' => $sales,
                    'count' => $count
                ];
            }

            // Monthly sales for last 6 months
            $monthlySales = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $sales = PosItem::where('created_by', $creatorId)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_amount');
                $count = Pos::where('created_by', $creatorId)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
                $monthlySales[] = [
                    'month' => $date->format('M Y'),
                    'sales' => $sales,
                    'count' => $count
                ];
            }

            // Warehouse sales
            $warehouseSales = PosItem::where('created_by', $creatorId)
                ->with('sale.warehouse')
                ->get()
                ->groupBy('sale.warehouse_id')
                ->map(function($items, $warehouseId) {
                    $warehouse = $items->first()->sale->warehouse;
                    return [
                        'name' => $warehouse->name ?? 'Unknown',
                        'sales' => $items->sum('total_amount'),
                        'count' => $items->pluck('pos_id')->unique()->count()
                    ];
                })
                ->values();

            // Discount data - Top 10 products with discounts
            $discountData = PosItem::where('created_by', $creatorId)
                ->where('item_discount_amount', '>', 0)
                ->with('product')
                ->get()
                ->groupBy('product_id')
                ->map(function($items, $productId) {
                    $product = $items->first()->product;
                    return [
                        'product_name' => $product->name ?? 'Unknown',
                        'sku' => $product->sku ?? 'N/A',
                        'total_discount_given' => $items->sum('item_discount_amount'),
                        'total_revenue' => $items->sum('total_amount'),
                    ];
                })
                ->sortByDesc('total_discount_given')
                ->take(10)
                ->values();

            // Monthly discount trends
            $monthlyDiscounts = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $discountAmount = PosItem::where('created_by', $creatorId)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('item_discount_amount');
                $monthlyDiscounts[] = [
                    'month' => $date->format('M Y'),
                    'discount_amount' => $discountAmount,
                ];
            }

            // Return data - Top 10 products with returns
            $returnData = PosReturnItem::whereHas('posReturn', function($query) use ($creatorId) {
                    $query->where('created_by', $creatorId);
                })
                ->with('product')
                ->get()
                ->groupBy('product_id')
                ->map(function($items, $productId) {
                    $product = $items->first()->product;
                    $totalReturns = $items->sum('return_quantity');
                    $totalReturnAmount = $items->sum('total_amount');
                    
                    return [
                        'product_name' => $product->name ?? 'Unknown',
                        'sku' => $product->sku ?? 'N/A',
                        'total_returns' => $totalReturns,
                        'total_return_amount' => $totalReturnAmount,
                    ];
                })
                ->sortByDesc('total_return_amount')
                ->take(10)
                ->values();

            // Monthly return trends
            $monthlyReturns = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $returnAmount = PosReturn::where('created_by', $creatorId)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_amount');
                $returnCount = PosReturn::where('created_by', $creatorId)
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
                $monthlyReturns[] = [
                    'month' => $date->format('M Y'),
                    'return_amount' => $returnAmount,
                    'return_count' => $returnCount,
                ];
            }

            // Return statistics
            $totalReturns = PosReturn::where('created_by', $creatorId)->count();
            $totalReturnAmount = PosReturn::where('created_by', $creatorId)->sum('total_amount');

            $returnStats = [
                'total_returns' => $totalReturns,
                'total_return_amount' => $totalReturnAmount,
            ];

            return Inertia::render('Pos/Reports/Sales', [
                'salesData' => $salesData,
                'dailySales' => $dailySales,
                'monthlySales' => $monthlySales,
                'warehouseSales' => $warehouseSales,
                'discountData' => $discountData,
                'monthlyDiscounts' => $monthlyDiscounts,
                'returnData' => $returnData,
                'monthlyReturns' => $monthlyReturns,
                'returnStats' => $returnStats,
            ]);
        }else{
            return redirect()->route('pos.index')->with('error', __('Permission denied'));
        }
    }

    public function products(Request $request)
    {
        if(Auth::user()->can('manage-pos-reports')){
            $creatorId = creatorId();

            // Get approved/completed returns grouped by product
            $returnData = PosReturnItem::whereHas('posReturn', function($query) use ($creatorId) {
                    $query->where('created_by', $creatorId)
                          ->whereIn('status', ['approved', 'completed']);
                })
                ->selectRaw('product_id, SUM(return_quantity) as total_return_quantity, SUM(total_amount) as total_return_amount')
                ->groupBy('product_id')
                ->get()
                ->keyBy('product_id');

            // Product performance data
            $productData = PosItem::where('created_by', $creatorId)
                ->with('product')
                ->get()
                ->groupBy('product_id')
                ->map(function($items, $productId) use ($returnData) {
                    $product = $items->first()->product;
                    if (!$product) {
                        return null;
                    }
                    $returns = $returnData->get($productId);
                    $returnQuantity = $returns ? (float) $returns->total_return_quantity : 0;
                    $returnAmount = $returns ? (float) $returns->total_return_amount : 0;

                    return [
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'total_quantity' => max(0, $items->sum('quantity') - $returnQuantity),
                        'total_revenue' => max(0, $items->sum('total_amount') - $returnAmount),
                        'total_orders' => $items->pluck('pos_id')->unique()->count()
                    ];
                })
                ->filter()
                ->sortByDesc('total_revenue')
                ->values();

            return Inertia::render('Pos/Reports/Products', [
                'productData' => $productData,
            ]);
        }else{
            return redirect()->route('pos.index')->with('error', __('Permission denied'));
        }
    }

    public function customers(Request $request)
    {
        if(Auth::user()->can('manage-pos-reports')){
            $creatorId = creatorId();
            
            // Customer analysis data
            $customerData = PosItem::where('created_by', $creatorId)
                ->with('sale.customer')
                ->get()
                ->groupBy('sale.customer_id')
                ->map(function($items, $customerId) {
                    $customer = $items->first()->sale->customer;
                    $totalSpent = $items->sum('total_amount');
                    $orderCount = $items->pluck('pos_id')->unique()->count();
                    return [
                        'customer_id' => $customerId,
                        'customer' => ['name' => $customer->name ?? 'Walk-in'],
                        'total_orders' => $orderCount,
                        'total_spent' => $totalSpent,
                        'avg_order_value' => $orderCount > 0 ? $totalSpent / $orderCount : 0,
                        'last_order_date' => $items->max('created_at')
                    ];
                })
                ->sortByDesc('total_spent')
                ->take(20)
                ->values();

            return Inertia::render('Pos/Reports/Customers', [
                'customerData' => $customerData,
            ]);
        }else{
            return redirect()->route('pos.index')->with('error', __('Permission denied'));
        }
    }


}