<?php

namespace Automas\Inventory\Listeners;

use App\Events\PostPurchaseInvoice;
use Automas\Inventory\Models\InventoryCostLayer;
use Automas\Inventory\Models\InventoryItem;

class PostPurchaseInvoiceLis
{
    public function handle(PostPurchaseInvoice $event)
    {
        if (Module_is_active('Inventory')) {
            $purchaseInvoice = $event->purchaseInvoice;
            foreach ($purchaseInvoice->items as $item) {
                $inventoryItem = InventoryItem::where('product_id', $item->product_id)
                    ->where('created_by', $purchaseInvoice->created_by)
                    ->first();

                if ($inventoryItem && $inventoryItem->is_tracked == 1) {
                    InventoryCostLayer::create([
                        'item_id' => $inventoryItem->id,
                        'warehouse_id' => $purchaseInvoice->warehouse_id,
                        'purchase_date' => $purchaseInvoice->invoice_date,
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_price,
                        'remaining_quantity' => $item->quantity,
                        'reference_type' => 'purchase',
                        'reference_id' => $purchaseInvoice->id,
                        'creator_id' => $purchaseInvoice->creator_id,
                        'created_by' => $purchaseInvoice->created_by,
                    ]);
                }
            }
        }
    }
}
