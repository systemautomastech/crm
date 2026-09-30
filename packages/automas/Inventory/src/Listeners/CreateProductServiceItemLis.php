<?php

namespace Automas\Inventory\Listeners;

use Illuminate\Support\Facades\Auth;
use Automas\Inventory\Models\InventoryItem;
use Automas\ProductService\Events\CreateProductServiceItem;

class CreateProductServiceItemLis
{
    public function handle(CreateProductServiceItem $event)
    {
        if (Module_is_active('Inventory')) {
            $request = $event->request;
            $item = $event->item;
            if ($request->reorder_level || $request->maximum_level) {
                $exists = InventoryItem::where('product_id', $item->id)
                    ->where('created_by', creatorId())
                    ->exists();
                if ($exists) {
                    return redirect()->back()->with('error', __('This product already has an inventory item.'));
                }
                $inventoryItem                       = new InventoryItem();
                $inventoryItem->product_id           = $item->id;
                $inventoryItem->reorder_level        = $request->reorder_level;
                $inventoryItem->maximum_level        = $request->maximum_level;                
                $inventoryItem->valuation_method     = $request->valuation_method;
                $inventoryItem->is_tracked           = $request->is_tracked;
                $inventoryItem->creator_id           = Auth::id();
                $inventoryItem->created_by           = creatorId();
                $inventoryItem->save();
            }
        }
    }
}
