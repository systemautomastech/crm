<?php

namespace Automas\Inventory\Listeners;

use Illuminate\Support\Facades\Auth;
use Automas\Inventory\Models\InventoryItem;
use Automas\ProductService\Events\UpdateProductServiceItem;

class UpdateProductServiceItemLis
{
    public function handle(UpdateProductServiceItem $event)
    {
        if (Module_is_active('Inventory')) {
            $request = $event->request;
            $item = $event->item;
            if ($request->reorder_level || $request->maximum_level) {
                $inventoryItem = InventoryItem::where('product_id', $item->id)
                    ->where('created_by', creatorId())
                    ->first();
                if ($inventoryItem) {
                    $inventoryItem->reorder_level        = $request->reorder_level ?? $inventoryItem->reorder_level;
                    $inventoryItem->maximum_level        = $request->maximum_level ?? $inventoryItem->maximum_level;
                    $inventoryItem->valuation_method     = $request->valuation_method ?? $inventoryItem->valuation_method;
                    $inventoryItem->is_tracked           = $request->boolean('is_tracked', $inventoryItem->is_tracked);
                    $inventoryItem->save();
                } else {
                    $newInventoryItem                    = new InventoryItem();
                    $newInventoryItem->product_id        = $item->id;
                    $newInventoryItem->reorder_level     = $request->reorder_level;
                    $newInventoryItem->maximum_level     = $request->maximum_level;
                    $newInventoryItem->valuation_method  = $request->valuation_method;
                    $newInventoryItem->is_tracked        = $request->boolean('is_tracked', false);
                    $newInventoryItem->creator_id        = Auth::id();
                    $newInventoryItem->created_by        = creatorId();
                    $newInventoryItem->save();
                }
            }
        }
    }
}
