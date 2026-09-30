<?php

namespace Automas\SmartReports\Services\ReportGenerators;

use App\Models\Warehouse;
use Automas\ProductService\Models\ProductServiceCategory;
use Automas\ProductService\Models\ProductServiceItem;

class ProductStockReportGenerator
{
    public function getReportData(array $filters)
    {
        $data = $this->getData($filters);
        return [
            'data'    => $data,
            'summary' => $this->getSummary($data),
        ];
    }

    public function getData(array $filters)
    {
        $query = ProductServiceItem::query()
            ->leftJoin('product_service_categories', 'product_service_items.category_id', '=', 'product_service_categories.id')
            ->leftJoin('warehouse_stocks', 'product_service_items.id', '=', 'warehouse_stocks.product_id')
            ->leftJoin('warehouses', 'warehouse_stocks.warehouse_id', '=', 'warehouses.id')
            ->where('product_service_items.created_by', creatorId())
            ->select(
                'product_service_items.id',
                'product_service_items.name as product_name',
                'product_service_items.sku',
                'product_service_items.type',
                'product_service_items.sale_price',
                'product_service_items.purchase_price',
                'product_service_items.is_active',
                'product_service_categories.name as category_name',
                'warehouse_stocks.warehouse_id',
                'warehouses.name as warehouse_name',
                \DB::raw('COALESCE(SUM(warehouse_stocks.quantity), 0) as quantity'),
                \DB::raw('(SELECT GROUP_CONCAT(
                    CASE WHEN w2.name IS NOT NULL AND ws2.quantity > 0
                    THEN CONCAT(w2.name, "(", ws2.quantity, ")")
                    END
                    ORDER BY w2.name SEPARATOR ", "
                ) FROM warehouse_stocks ws2
                LEFT JOIN warehouses w2 ON ws2.warehouse_id = w2.id
                WHERE ws2.product_id = product_service_items.id
                ) as warehouse_breakdown')
            )
            ->groupBy(
                'product_service_items.id',
                'product_service_items.name',
                'product_service_items.sku',
                'product_service_items.type',
                'product_service_items.sale_price',
                'product_service_items.purchase_price',
                'product_service_items.is_active',
                'product_service_items.category_id',
                'product_service_categories.name',
                'warehouse_stocks.warehouse_id',
                'warehouses.name'
            );

        if (!empty($filters['category_ids'])) {
            $query->whereIn('product_service_items.category_id', $filters['category_ids']);
        }

        if (!empty($filters['warehouse_ids'])) {
            $query->whereIn('warehouse_stocks.warehouse_id', $filters['warehouse_ids']);
        }

        if (!empty($filters['type'])) {
            $query->whereIn('product_service_items.type', $filters['type']);
        } else {
            $query->whereIn('product_service_items.type', ['product', 'part']);
        }

        if (isset($filters['stock_status']) && $filters['stock_status'] === 'in_stock') {
            $query->havingRaw('COALESCE(SUM(warehouse_stocks.quantity), 0) > 0');
        } elseif (isset($filters['stock_status']) && $filters['stock_status'] === 'out_of_stock') {
            $query->havingRaw('COALESCE(SUM(warehouse_stocks.quantity), 0) <= 0');
        }

        return $query->orderBy('product_service_items.name')->get();
    }

    public function getSummary($data)
    {
        // Group by product id, sum qty across warehouses
        $byProduct = $data->groupBy('id')->map(fn($rows) => (object)[
            'quantity'       => $rows->sum(fn($r) => floatval($r->quantity ?? 0)),
            'purchase_price' => floatval($rows->first()->purchase_price ?? 0),
            'sale_price'     => floatval($rows->first()->sale_price ?? 0),
        ]);

        $totalStockValue = $byProduct->sum(fn($r) => $r->purchase_price * $r->quantity);

        return [
            'total_products'     => $byProduct->count(),
            'total_quantity'     => $byProduct->sum(fn($r) => $r->quantity),
            'in_stock_count'     => $byProduct->filter(fn($r) => $r->quantity > 0)->count(),
            'out_of_stock_count' => $byProduct->filter(fn($r) => $r->quantity <= 0)->count(),
            'total_stock_value'  => round($totalStockValue, 2),
            'average_sale_price' => $byProduct->count() > 0 ? round($byProduct->avg(fn($r) => $r->sale_price), 2) : 0,
        ];
    }

    public function getFilterOptions()
    {
        $creatorId = creatorId();

        $categories = ProductServiceCategory::where('created_by', $creatorId)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn($c) => ['value' => $c->id, 'label' => $c->name]);

        $warehouseIds = \Automas\ProductService\Models\WarehouseStock::join('product_service_items', 'warehouse_stocks.product_id', '=', 'product_service_items.id')
            ->where('product_service_items.created_by', $creatorId)
            ->pluck('warehouse_stocks.warehouse_id')->unique()->filter();

        $warehouses = Warehouse::whereIn('id', $warehouseIds)
            ->orderBy('name')->get(['id', 'name'])
            ->map(fn($w) => ['value' => $w->id, 'label' => $w->name]);

        return [
            'categories' => $categories,
            'warehouses' => $warehouses,
        ];
    }
}
