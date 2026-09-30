<?php

namespace Automas\Pos\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Automas\Pos\Models\Pos;
use Automas\Pos\Models\PosReturn;
use Automas\Pos\Models\PosBillingCounter;
use Automas\ProductService\Models\ProductServiceItem;
use App\Models\User;
use Carbon\Carbon;
use Automas\Pos\Models\PosPayment;
use Automas\Pos\Models\PosItem;

class DashboardController extends Controller
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
        if (Auth::user()->can('manage-pos-dashboard')) {
            $user = Auth::user();
            $userType = $user->type;

            // Route to appropriate dashboard based on user type
            switch ($userType) {
                case 'company':
                    return $this->companyDashboard($request);
                case 'client':
                default:
                    return $this->clientDashboard($request);
            }
        }
    }

    private function companyDashboard(Request $request)
    {
        $creatorId = creatorId();
        [$start, $end] = $this->getDateRange($request);

        $totalSales = Pos::where('created_by', $creatorId)
            ->whereBetween('created_at', [$start, $end])
            ->count();
        if ($totalSales == 0 && !$request->has('period')) {
            $totalSales = Pos::where('created_by', $creatorId)->count();
        }

        $totalRevenue = PosPayment::where('created_by', $creatorId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('discount_amount');
        if ($totalRevenue == 0 && !$request->has('period')) {
            $totalRevenue = PosPayment::where('created_by', $creatorId)->sum('discount_amount');
        }

        $avgTransaction = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Product Stats
        $totalProducts = ProductServiceItem::where('created_by', $creatorId)
            ->where('type', '!=', 'service')
            ->count();

        $returnSubquery = DB::table('pos_return_items')
            ->join('pos_returns', 'pos_return_items.return_id', '=', 'pos_returns.id')
            ->where('pos_returns.created_by', $creatorId)
            ->whereIn('pos_returns.status', ['approved', 'completed'])
            ->select('pos_return_items.product_id')
            ->selectRaw('SUM(pos_return_items.return_quantity) as total_return_quantity')
            ->selectRaw('SUM(pos_return_items.total_amount) as total_return_amount')
            ->groupBy('pos_return_items.product_id');

        // Top Products
        $topProducts = DB::table('pos_items')
            ->join('pos', 'pos_items.pos_id', '=', 'pos.id')
            ->join('product_service_items', 'pos_items.product_id', '=', 'product_service_items.id')
            ->leftJoinSub($returnSubquery, 'returns', function ($join) {
                $join->on('pos_items.product_id', '=', 'returns.product_id');
            })
            ->where('pos.created_by', $creatorId)
            ->select(
                'product_service_items.name',
                DB::raw('GREATEST(SUM(pos_items.quantity) - IFNULL(returns.total_return_quantity, 0), 0) as total_quantity'),
                DB::raw('GREATEST(SUM(pos_items.total_amount) - IFNULL(returns.total_return_amount, 0), 0) as total_revenue')
            )
            ->groupBy('pos_items.product_id', 'product_service_items.name', 'returns.total_return_quantity', 'returns.total_return_amount')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        // Recent Sales
        $recentSales = Pos::with(['customer:id,name', 'warehouse:id,name'])
            ->where('created_by', $creatorId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                $payment = PosPayment::where('pos_id', $sale->id)->first();
                $sale->total = $payment ? $payment->discount_amount : 0;
                return $sale;
            });

        // Customer Stats
        $totalCustomers = User::whereHas('roles', function ($query) {
            $query->where('name', 'client');
        })->where('created_by', $creatorId)->count();

        $walkInSales = Pos::where('created_by', $creatorId)
            ->whereNull('customer_id')
            ->count();

        // Return Stats
        $totalReturns = PosReturn::where('created_by', $creatorId)->count();
        $returnsAmount = PosReturn::where('created_by', $creatorId)
            ->whereIn('status', ['approved', 'completed'])
            ->sum('total_amount');

        // Out of stock product list
        $outOfStockProductsList = ProductServiceItem::where('product_service_items.created_by', $creatorId)
            ->where('type', '!=', 'service')
            ->where(function ($query) {
                $query->whereDoesntHave('warehouseStocks')
                    ->orWhereHas('warehouseStocks', function ($q) {
                        // No need for extra condition here - we filter by sum below
                    });
            })
            ->select('product_service_items.id', 'product_service_items.name as product_name', 'product_service_items.sku', 'product_service_items.image')
            ->selectSub(
                DB::table('warehouse_stocks')
                    ->selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('warehouse_stocks.product_id', 'product_service_items.id'),
                'stock'
            )
            ->having('stock', '<=', 0)
            ->limit(10)
            ->get();

        // Counter wise sales
        $counterWiseSales = PosBillingCounter::where('created_by', $creatorId)
            ->get()
            ->map(function ($counter) {
                $todaySalesCount = Pos::where('billing_counter_id', $counter->id)
                    ->whereDate('created_at', Carbon::today())
                    ->count();

                $todayRevenue = PosPayment::whereHas('pos', function ($q) use ($counter) {
                    $q->where('billing_counter_id', $counter->id);
                })
                    ->whereDate('created_at', Carbon::today())
                    ->sum('discount_amount');

                return [
                    'id' => $counter->id,
                    'name' => $counter->name,
                    'status' => $counter->status,
                    'today_sales' => $todaySalesCount,
                    'today_revenue' => $todayRevenue
                ];
            });

        // Sales Trend Data (Last 10 Days)
        $last10DaysSales = [];
        for ($i = 9; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailySales = PosPayment::where('created_by', $creatorId)
                ->whereDate('created_at', $date)
                ->sum('discount_amount');

            $last10DaysSales[] = [
                'date' => $date->format('M d'),
                'sales' => (float)$dailySales
            ];
        }

        return Inertia::render('Pos/Dashboard/Index', [
            'stats' => [
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'avg_transaction' => $avgTransaction,
                'total_products' => $totalProducts,
                'total_customers' => $totalCustomers,
                'walk_in_sales' => $walkInSales,
                'total_returns' => $totalReturns,
                'returns_amount' => $returnsAmount,
            ],
            'topProducts' => $topProducts,
            'recentSales' => $recentSales,
            'recentReturns' => [],
            'last10DaysSales' => $last10DaysSales,
            'outOfStockProductsList' => $outOfStockProductsList,
            'counterWiseSales' => $counterWiseSales,
        ]);
    }

    private function clientDashboard(Request $request)
    {
        $user = Auth::user();
        [$start, $end] = $this->getDateRange($request);

        $totalSales = Pos::where('customer_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();
        if ($totalSales == 0 && !$request->has('period')) {
            $totalSales = Pos::where('customer_id', $user->id)->count();
        }

        $totalRevenue = PosPayment::whereHas('pos', function ($query) use ($user) {
            $query->where('customer_id', $user->id);
        })
            ->whereBetween('created_at', [$start, $end])
            ->sum('discount_amount');

        if ($totalRevenue == 0 && !$request->has('period')) {
            $totalRevenue = PosPayment::whereHas('pos', function ($query) use ($user) {
                $query->where('customer_id', $user->id);
            })->sum('discount_amount');
        }

        return Inertia::render('Pos/Dashboard/Index', [
            'stats' => [
                'total_sales' => $totalSales,
                'total_revenue' => $totalRevenue,
                'avg_transaction' => $totalSales > 0 ? $totalRevenue / $totalSales : 0,
                'total_products' => 0,
                'total_customers' => 1,
                'walk_in_sales' => 0,
                'total_returns' => 0,
                'returns_amount' => 0,
            ],
            'topProducts' => [],
            'recentSales' => [],
            'recentReturns' => [],
            'last10DaysSales' => [],
            'outOfStockProductsList' => [],
            'counterWiseSales' => [],
        ]);
    }
}
