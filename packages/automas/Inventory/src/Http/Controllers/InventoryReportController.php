<?php

namespace Automas\Inventory\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Automas\Inventory\Models\InventoryItem;
use Automas\Inventory\Models\InventoryCogsHistory;
use Automas\Inventory\Models\InventoryCostLayer;
use Automas\ProductService\Models\WarehouseStock;
use Carbon\Carbon;

class InventoryReportController extends Controller
{
    public function stockValuationReport(Request $request)
    {
        if(Auth::user()->can('view-inventory-reports')){
            $warehouseId = $request->warehouse_id;
            
            // Get stock valuation data
            $stockValuation = $this->getStockValuation($warehouseId);
            $valuationByWarehouse = $this->getValuationByWarehouse();
            $valuationByMethod = $this->getValuationByMethod($warehouseId);
            $lowStockItems = $this->getLowStockItems($warehouseId);
            
            // Get warehouses for dropdown
            $warehouses = Warehouse::where('created_by', creatorId())
                ->select('id', 'name')
                ->get();

            return Inertia::render('Inventory/Reports/StockValuationReport', [
                'stockValuation' => $stockValuation,
                'valuationByWarehouse' => $valuationByWarehouse,
                'valuationByMethod' => $valuationByMethod,
                'lowStockItems' => $lowStockItems,
                'warehouses' => $warehouses,
            ]);
        }else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function cogsReport(Request $request)
    {
        if(Auth::user()->can('view-inventory-reports')){
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            $warehouseId = $request->warehouse_id;
            
            // Get COGS data
            $cogsSummary = $this->getCogsSummary($fromDate, $toDate, $warehouseId);
            $cogsMonthly = $this->getCogsMonthly();
            $cogsTopItems = $this->getCogsTopItems($fromDate, $toDate, $warehouseId);
            $cogsDetails = $this->getCogsDetails($fromDate, $toDate, $warehouseId);
            
            // Get warehouses for dropdown
            $warehouses = Warehouse::where('created_by', creatorId())
                ->select('id', 'name')
                ->get();

            return Inertia::render('Inventory/Reports/CogsReport', [
                'cogsSummary' => $cogsSummary,
                'cogsMonthly' => $cogsMonthly,
                'cogsTopItems' => $cogsTopItems,
                'cogsDetails' => $cogsDetails,
                'warehouses' => $warehouses,
            ]);
        }else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function stockMovementReport(Request $request)
    {
        if(Auth::user()->can('view-inventory-reports')){
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            $warehouseId = $request->warehouse_id;
            
            // Get stock movement data
            $movementSummary = $this->getMovementSummary($fromDate, $toDate, $warehouseId);
            $movementByType = $this->getMovementByType($fromDate, $toDate, $warehouseId);
            $movementMonthly = $this->getMovementMonthly($warehouseId);
            $recentMovements = $this->getRecentMovements($fromDate, $toDate, $warehouseId);
            
            // Get warehouses for dropdown
            $warehouses = Warehouse::where('created_by', creatorId())
                ->select('id', 'name')
                ->get();

            return Inertia::render('Inventory/Reports/StockMovementReport', [
                'movementSummary' => $movementSummary,
                'movementByType' => $movementByType,
                'movementMonthly' => $movementMonthly,
                'recentMovements' => $recentMovements,
                'warehouses' => $warehouses,
            ]);
        }else{
            return back()->with('error', __('Permission denied'));
        }
    }

    private function getStockValuation($warehouseId = null)
    {
        $query = InventoryItem::where('inventory_items.created_by', creatorId())
            ->where('is_tracked', true)
            ->join('product_service_items', 'inventory_items.product_id', '=', 'product_service_items.id')
            ->select('inventory_items.*', 'product_service_items.purchase_price', 'product_service_items.sale_price');

        $items = $query->get();
        $totalValue = 0;
        $totalItems = 0;
        $totalQuantity = 0;
        
        foreach ($items as $item) {
            $stockQuery = WarehouseStock::where('product_id', $item->product_id);
            if ($warehouseId) {
                $stockQuery->where('warehouse_id', $warehouseId);
            }
            $quantity = $stockQuery->sum('quantity');
            
            if ($quantity <= 0) continue;
            
            // Get average cost from cost layers, fallback to purchase price
            $avgCost = $this->getAverageCost($item->id, $warehouseId);
            if ($avgCost == 0) {
                $avgCost = $item->purchase_price ?? 0;
            }
            
            $value = $quantity * $avgCost;
            
            $totalValue += $value;
            $totalQuantity += $quantity;
            $totalItems++;
        }

        return [
            'totalValue' => round($totalValue, 2),
            'totalItems' => $totalItems,
            'totalQuantity' => $totalQuantity,
        ];
    }

    private function getValuationByWarehouse()
    {
        $warehouses = Warehouse::where('created_by', creatorId())->get();
        $data = [];

        foreach ($warehouses as $warehouse) {
            $value = 0;
            $items = InventoryItem::where('inventory_items.created_by', creatorId())
                ->where('is_tracked', true)
                ->join('product_service_items', 'inventory_items.product_id', '=', 'product_service_items.id')
                ->select('inventory_items.*', 'product_service_items.purchase_price')
                ->get();
            
            foreach ($items as $item) {
                $quantity = WarehouseStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $warehouse->id)
                    ->sum('quantity');
                    
                if ($quantity <= 0) continue;
                    
                $avgCost = $this->getAverageCost($item->id, $warehouse->id);
                if ($avgCost == 0) {
                    $avgCost = $item->purchase_price ?? 0;
                }
                $value += $quantity * $avgCost;
            }

            if ($value > 0) {
                $data[] = [
                    'name' => $warehouse->name,
                    'value' => round($value, 2),
                ];
            }
        }

        return $data;
    }

    private function getValuationByMethod($warehouseId = null)
    {
        $methods = ['fifo', 'lifo', 'weighted_average'];
        $data = [];

        foreach ($methods as $method) {
            $query = InventoryItem::where('inventory_items.created_by', creatorId())
                ->where('is_tracked', true)
                ->where('valuation_method', $method)
                ->join('product_service_items', 'inventory_items.product_id', '=', 'product_service_items.id')
                ->select('inventory_items.*', 'product_service_items.purchase_price');
            
            $items = $query->get();
            $value = 0;

            foreach ($items as $item) {
                $stockQuery = WarehouseStock::where('product_id', $item->product_id);
                if ($warehouseId) {
                    $stockQuery->where('warehouse_id', $warehouseId);
                }
                $quantity = $stockQuery->sum('quantity');
                
                if ($quantity <= 0) continue;
                
                $avgCost = $this->getAverageCost($item->id, $warehouseId);
                if ($avgCost == 0) {
                    $avgCost = $item->purchase_price ?? 0;
                }
                $value += $quantity * $avgCost;
            }

            if ($value > 0) {
                $data[] = [
                    'name' => strtoupper(str_replace('_', ' ', $method)),
                    'value' => round($value, 2),
                ];
            }
        }

        return $data;
    }

    private function getLowStockItems($warehouseId = null)
    {
        $items = InventoryItem::where('inventory_items.created_by', creatorId())
            ->where('is_tracked', true)
            ->join('product_service_items', 'inventory_items.product_id', '=', 'product_service_items.id')
            ->select('inventory_items.*', 'product_service_items.name')
            ->get();

        $lowStock = [];

        foreach ($items as $item) {
            $stockQuery = WarehouseStock::where('product_id', $item->product_id);
            if ($warehouseId) {
                $stockQuery->where('warehouse_id', $warehouseId);
            }
            $quantity = $stockQuery->sum('quantity');

            if ($quantity <= $item->reorder_level) {
                $lowStock[] = [
                    'name' => $item->name,
                    'current_stock' => $quantity,
                    'reorder_level' => $item->reorder_level,
                    'status' => $quantity == 0 ? 'Out of Stock' : 'Low Stock',
                ];
            }
        }

        return $lowStock;
    }

    private function getCogsSummary($fromDate = null, $toDate = null, $warehouseId = null)
    {
        $query = InventoryCogsHistory::where('created_by', creatorId());

        if ($fromDate && $toDate) {
            $query->whereDate('sale_date', '>=', $fromDate)
                  ->whereDate('sale_date', '<=', $toDate);
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $totalCogs = $query->sum('total_cogs');
        $totalQuantity = $query->sum('quantity_sold');
        $avgUnitCost = $totalQuantity > 0 ? $totalCogs / $totalQuantity : 0;

        return [
            'totalCogs' => round($totalCogs, 2),
            'totalQuantity' => $totalQuantity,
            'avgUnitCost' => round($avgUnitCost, 2),
        ];
    }

    private function getCogsMonthly()
    {
        $data = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::create(date('Y'), $month)->format('M Y');
            $cogs = InventoryCogsHistory::where('created_by', creatorId())
                ->whereMonth('sale_date', $month)
                ->whereYear('sale_date', date('Y'))
                ->sum('total_cogs');
            
            $data[] = [
                'month' => $month,
                'name' => $monthName,
                'cogs' => round($cogs, 2),
            ];
        }

        return $data;
    }

    private function getCogsTopItems($fromDate = null, $toDate = null, $warehouseId = null)
    {
        $query = InventoryCogsHistory::where('inventory_cogs_history.created_by', creatorId())
            ->join('inventory_items', 'inventory_cogs_history.item_id', '=', 'inventory_items.id')
            ->join('product_service_items', 'inventory_items.product_id', '=', 'product_service_items.id')
            ->select('product_service_items.name', DB::raw('SUM(inventory_cogs_history.total_cogs) as total_cogs'))
            ->groupBy('product_service_items.id', 'product_service_items.name');

        if ($fromDate && $toDate) {
            $query->whereDate('inventory_cogs_history.sale_date', '>=', $fromDate)
                  ->whereDate('inventory_cogs_history.sale_date', '<=', $toDate);
        }

        if ($warehouseId) {
            $query->where('inventory_cogs_history.warehouse_id', $warehouseId);
        }

        return $query->orderBy('total_cogs', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'cogs' => round($item->total_cogs, 2),
                ];
            });
    }

    private function getCogsDetails($fromDate = null, $toDate = null, $warehouseId = null)
    {
        $query = InventoryCogsHistory::where('inventory_cogs_history.created_by', creatorId())
            ->join('inventory_items', 'inventory_cogs_history.item_id', '=', 'inventory_items.id')
            ->join('product_service_items', 'inventory_items.product_id', '=', 'product_service_items.id')
            ->select(
                'inventory_cogs_history.sale_date',
                'product_service_items.name',
                'inventory_cogs_history.quantity_sold',
                'inventory_cogs_history.unit_cost',
                'inventory_cogs_history.total_cogs',
                'inventory_cogs_history.reference_type'
            );

        if ($fromDate && $toDate) {
            $query->whereDate('inventory_cogs_history.sale_date', '>=', $fromDate)
                  ->whereDate('inventory_cogs_history.sale_date', '<=', $toDate);
        }

        if ($warehouseId) {
            $query->where('inventory_cogs_history.warehouse_id', $warehouseId);
        }

        return $query->orderBy('inventory_cogs_history.sale_date', 'desc')
            ->limit(50)
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->sale_date,
                    'product' => $item->name,
                    'quantity' => $item->quantity_sold,
                    'unit_cost' => round($item->unit_cost, 2),
                    'total_cogs' => round($item->total_cogs, 2),
                    'type' => $item->reference_type,
                ];
            });
    }

    private function getMovementSummary($fromDate = null, $toDate = null, $warehouseId = null)
    {
        $query = InventoryCostLayer::where('created_by', creatorId());

        if ($fromDate && $toDate) {
            $query->whereDate('purchase_date', '>=', $fromDate)
                  ->whereDate('purchase_date', '<=', $toDate);
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $totalIn = $query->sum('quantity');
        
        $cogsQuery = InventoryCogsHistory::where('created_by', creatorId());
        if ($fromDate && $toDate) {
            $cogsQuery->whereDate('sale_date', '>=', $fromDate)
                      ->whereDate('sale_date', '<=', $toDate);
        }
        if ($warehouseId) {
            $cogsQuery->where('warehouse_id', $warehouseId);
        }
        $totalOut = $cogsQuery->sum('quantity_sold');

        return [
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'netMovement' => $totalIn - $totalOut,
        ];
    }

    private function getMovementByType($fromDate = null, $toDate = null, $warehouseId = null)
    {
        $query = InventoryCostLayer::where('created_by', creatorId())
            ->select('reference_type', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('reference_type');

        if ($fromDate && $toDate) {
            $query->whereDate('purchase_date', '>=', $fromDate)
                  ->whereDate('purchase_date', '<=', $toDate);
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get()->map(function($item) {
            return [
                'name' => ucfirst($item->reference_type),
                'value' => $item->total_quantity,
            ];
        });
    }

    private function getMovementMonthly($warehouseId = null)
    {
        $data = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::create(date('Y'), $month)->format('M Y');
            
            $inQuery = InventoryCostLayer::where('created_by', creatorId())
                ->whereMonth('purchase_date', $month)
                ->whereYear('purchase_date', date('Y'));
            if ($warehouseId) {
                $inQuery->where('warehouse_id', $warehouseId);
            }
            $quantityIn = $inQuery->sum('quantity');
            
            $outQuery = InventoryCogsHistory::where('created_by', creatorId())
                ->whereMonth('sale_date', $month)
                ->whereYear('sale_date', date('Y'));
            if ($warehouseId) {
                $outQuery->where('warehouse_id', $warehouseId);
            }
            $quantityOut = $outQuery->sum('quantity_sold');
            
            $data[] = [
                'month' => $month,
                'name' => $monthName,
                'in' => $quantityIn,
                'out' => $quantityOut,
            ];
        }

        return $data;
    }

    private function getRecentMovements($fromDate = null, $toDate = null, $warehouseId = null)
    {
        $movements = [];

        // Get recent purchases
        $purchaseQuery = InventoryCostLayer::where('inventory_cost_layers.created_by', creatorId())
            ->join('inventory_items', 'inventory_cost_layers.item_id', '=', 'inventory_items.id')
            ->join('product_service_items', 'inventory_items.product_id', '=', 'product_service_items.id')
            ->select(
                'inventory_cost_layers.purchase_date as date',
                'product_service_items.name',
                'inventory_cost_layers.quantity',
                DB::raw("'IN' as type"),
                'inventory_cost_layers.reference_type'
            );

        if ($fromDate && $toDate) {
            $purchaseQuery->whereDate('inventory_cost_layers.purchase_date', '>=', $fromDate)
                          ->whereDate('inventory_cost_layers.purchase_date', '<=', $toDate);
        }

        if ($warehouseId) {
            $purchaseQuery->where('inventory_cost_layers.warehouse_id', $warehouseId);
        }

        $purchases = $purchaseQuery->orderBy('inventory_cost_layers.purchase_date', 'desc')
            ->limit(25)
            ->get();

        // Get recent sales
        $salesQuery = InventoryCogsHistory::where('inventory_cogs_history.created_by', creatorId())
            ->join('inventory_items', 'inventory_cogs_history.item_id', '=', 'inventory_items.id')
            ->join('product_service_items', 'inventory_items.product_id', '=', 'product_service_items.id')
            ->select(
                'inventory_cogs_history.sale_date as date',
                'product_service_items.name',
                'inventory_cogs_history.quantity_sold as quantity',
                DB::raw("'OUT' as type"),
                'inventory_cogs_history.reference_type'
            );

        if ($fromDate && $toDate) {
            $salesQuery->whereDate('inventory_cogs_history.sale_date', '>=', $fromDate)
                       ->whereDate('inventory_cogs_history.sale_date', '<=', $toDate);
        }

        if ($warehouseId) {
            $salesQuery->where('inventory_cogs_history.warehouse_id', $warehouseId);
        }

        $sales = $salesQuery->orderBy('inventory_cogs_history.sale_date', 'desc')
            ->limit(25)
            ->get();

        // Combine and sort
        $movements = $purchases->concat($sales)
            ->sortByDesc('date')
            ->take(50)
            ->values()
            ->map(function($item) {
                return [
                    'date' => $item->date,
                    'product' => $item->name,
                    'quantity' => $item->quantity,
                    'type' => $item->type,
                    'reference' => ucfirst($item->reference_type),
                ];
            });

        return $movements;
    }

    private function getAverageCost($itemId, $warehouseId = null)
    {
        $query = InventoryCostLayer::where('item_id', $itemId)
            ->where('remaining_quantity', '>', 0);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $layers = $query->get();
        
        if ($layers->isEmpty()) {
            return 0;
        }

        $totalCost = 0;
        $totalQuantity = 0;

        foreach ($layers as $layer) {
            $totalCost += $layer->remaining_quantity * $layer->unit_cost;
            $totalQuantity += $layer->remaining_quantity;
        }

        return $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;
    }
}
