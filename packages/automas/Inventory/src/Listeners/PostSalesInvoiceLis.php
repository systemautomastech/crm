<?php

namespace Automas\Inventory\Listeners;

use App\Events\PostSalesInvoice;
use Automas\Inventory\Events\CreateInventoryCogsHistory;
use Automas\Inventory\Models\InventoryCogsHistory;
use Automas\Inventory\Models\InventoryItem;

class PostSalesInvoiceLis
{
    public function handle(PostSalesInvoice $event)
    {
        if (Module_is_active('Inventory')) {
            $salesInvoice = $event->salesInvoice;
            foreach ($salesInvoice->items as $item) {
                $inventoryItem = InventoryItem::where('product_id', $item->product_id)
                    ->where('created_by', $salesInvoice->created_by)
                    ->first();
                if ($inventoryItem && $inventoryItem->is_tracked == 1) {
                    $totalCogs = $item->quantity * $item->unit_price;
                    InventoryCogsHistory::create([
                        'item_id' => $inventoryItem->id,
                        'warehouse_id' => $salesInvoice->warehouse_id,
                        'sale_date' => $salesInvoice->invoice_date,
                        'quantity_sold' => $item->quantity,
                        'unit_cost' => $item->unit_price,
                        'total_cogs' => $totalCogs,
                        'reference_type' => 'sales',
                        'reference_id' => $salesInvoice->id,
                        'creator_id' => $salesInvoice->creator_id,
                        'created_by' => $salesInvoice->created_by,
                    ]);
                    try {
                        CreateInventoryCogsHistory::dispatch($inventoryItem, $salesInvoice);
                    } catch (\Throwable $th) {
                        return back()->with('error', $th->getMessage());
                    }  
                }
            }
        }
    }
}