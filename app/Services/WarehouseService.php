<?php

namespace App\Services;

use App\Models\Warehouse;
use Automas\ProductService\Models\ProductServiceItem;
use Automas\ProductService\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    /**
     * Get active warehouses for quotation and invoice dropdowns
     */
    public function getActiveWarehouses()
    {
        return Warehouse::where('is_active', true)
            ->select('id', 'name', 'address')
            ->where('created_by', creatorId())
            ->get();
    }

    /**
     * Get products and stocks available for the specified warehouse
     */
    public function getWarehouseProducts(?int $warehouseId = null)
    {
        $query = ProductServiceItem::query()
            ->with('unitRelation:id,unit_name')
            ->select([
                'id',
                'name',
                'sku',
                'description',
                'long_description',
                'sale_price',
                'purchase_price',
                'tax_ids',
                'unit',
                'type',
            ])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('created_by', creatorId())
                    ->orWhere('creator_id', creatorId());
            });

        if ($warehouseId) {
            $query->where(function ($q) use ($warehouseId) {
                $q->where('type', 'service')
                    ->orWhereHas('warehouseStocks', function ($sub) use ($warehouseId) {
                        $sub->where('warehouse_id', $warehouseId);
                    });
            })->with([
                        'warehouseStocks' => fn($q) => $q->where('warehouse_id', $warehouseId),
                    ]);
        }

        return $query->get()->map(function ($product) {
            $stockQuantity = $product->relationLoaded('warehouseStocks') && $product->warehouseStocks->isNotEmpty()
                ? $product->warehouseStocks->first()->quantity
                : 0;

            $unitName = $product->unitRelation?->unit_name
                ?? (is_numeric($product->unit) ? '' : ($product->unit ?? ''));

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'description' => $product->description,
                'long_description' => $product->long_description,
                'sale_price' => $product->sale_price,
                'purchase_price' => $product->purchase_price,
                'unit' => $product->unit,
                'unit_name' => $unitName,
                'type' => $product->type,
                'stock_quantity' => $stockQuantity,
                'taxes' => $product->taxes->map(fn($tax) => [
                    'id' => $tax->id,
                    'tax_name' => $tax->tax_name,
                    'rate' => $tax->rate,
                ]),
            ];
        });
    }

    public function transferWarehouse(int $fromWarehouseId, int $toWarehouseId)
    {
        if ($fromWarehouseId === $toWarehouseId) {
            return;
        }

        DB::transaction(function () use ($fromWarehouseId, $toWarehouseId) {
            $sourceStocks = WarehouseStock::where('warehouse_id', $fromWarehouseId)->get();

            foreach ($sourceStocks as $sourceStock) {
                if ($sourceStock->quantity <= 0) {
                    $sourceStock->delete();
                    continue;
                }

                $destinationStock = WarehouseStock::firstOrNew([
                    'product_id' => $sourceStock->product_id,
                    'warehouse_id' => $toWarehouseId,
                ]);

                $destinationStock->quantity = (float) ($destinationStock->quantity ?? 0) + (float) $sourceStock->quantity;
                $destinationStock->save();

                $sourceStock->delete();
            }
        });
    }
}
