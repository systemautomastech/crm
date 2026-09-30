<?php

namespace Automas\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Inventory\Models\InventoryItem;
class InventoryItemController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-inventory-items')){
            $items = InventoryItem::query()
                ->with(['product'])
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-inventory-items')) {
                        $q->where('inventory_items.created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-inventory-items')) {
                        $q->where('inventory_items.creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('product_name'), function ($q) {
                    $q->whereHas('product', function ($query) {
                        $query->where('name', 'like', '%' . request('product_name') . '%')
                              ->orWhere('sku', 'like', '%' . request('product_name') . '%');
                    });
                })
                ->when(request('valuation_method'), fn($q) => $q->where('inventory_items.valuation_method', request('valuation_method')))
                ->when(request('is_tracked') !== null, fn($q) => $q->where('inventory_items.is_tracked', request('is_tracked')))
                ->when(request('sort'), function($q) {
                    $sort = request('sort');
                    $direction = request('direction', 'asc');
                    if ($sort === 'product.name') {
                        $q->join('product_service_items', 'inventory_items.product_id', '=', 'product_service_items.id')
                          ->orderBy('product_service_items.name', $direction)
                          ->select('inventory_items.*');
                    } else {
                        $q->orderBy($sort, $direction);
                    }
                }, fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('Inventory/Items/Index', [
                'items' => $items,
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    } 

    public function getByProduct($productId)
    {
        $inventoryItem = InventoryItem::where('product_id', $productId)->first();
        
        if (!$inventoryItem) {
            return response()->json(null);
        }
        
        return response()->json([
            'reorder_level' => $inventoryItem->reorder_level,
            'maximum_level' => $inventoryItem->maximum_level,
            'valuation_method' => $inventoryItem->valuation_method,
            'is_tracked' => $inventoryItem->is_tracked,
        ]);
    }
}
