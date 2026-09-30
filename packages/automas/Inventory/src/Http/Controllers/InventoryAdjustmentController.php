<?php

namespace Automas\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Automas\Inventory\Models\InventoryAdjustment;
use App\Models\Warehouse;
use Automas\Inventory\Models\InventoryAdjustmentItem;
use Automas\Inventory\Models\InventoryItem;
use Automas\ProductService\Models\WarehouseStock;
use Automas\Inventory\Http\Requests\StoreInventoryAdjustmentRequest;
use Automas\Inventory\Events\CreateInventoryAdjustment;
use Automas\Inventory\Events\DestroyInventoryAdjustment;
use Automas\Inventory\Events\ApproveInventoryAdjustment;
use Automas\Inventory\Events\PostInventoryAdjustment;

class InventoryAdjustmentController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-inventory-adjustments')){
            $adjustments = InventoryAdjustment::query()
                ->with(['warehouse', 'approver'])
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-inventory-adjustments')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-inventory-adjustments')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('adjustment_number'), fn($q) => $q->where('adjustment_number', 'like', '%' . request('adjustment_number') . '%'))
                ->when(request('adjustment_type'), fn($q) => $q->where('adjustment_type', request('adjustment_type')))
                ->when(request('status'), fn($q) => $q->where('status', request('status')))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('Inventory/Adjustments/Index', [
                'adjustments' => $adjustments,
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function create()
    {
        if(Auth::user()->can('create-inventory-adjustments')){
            $warehouses = Warehouse::where('is_active', true)
                ->where('created_by', creatorId())
                ->select('id', 'name')
                ->get();

            return Inertia::render('Inventory/Adjustments/Create', [
                'warehouses' => $warehouses,
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function getQuantity()
    {
        if(Auth::user()->can('create-inventory-adjustments')){
            $productId = request('product_id');
            $warehouseId = request('warehouse_id');
            $stock = WarehouseStock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            return response()->json([
                'quantity' => $stock ? $stock->quantity : 0
            ]);
        }
        else{
            return response()->json(['error' => __('Permission denied')], 403);
        }
    }

    public function getWarehouseItems()
    {
        if(Auth::user()->can('create-inventory-adjustments')){
            $warehouseId = request('warehouse_id');
            
            $inventoryItems = InventoryItem::with(['product'])
                ->where('created_by', creatorId())
                ->where('is_tracked', true)
                ->whereHas('product.warehouseStocks', function($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                })
                ->get();

            return response()->json([
                'inventoryItems' => $inventoryItems
            ]);
        }
        else{
            return response()->json(['error' => __('Permission denied')], 403);
        }
    }

    public function store(StoreInventoryAdjustmentRequest $request)
    {
        if(Auth::user()->can('create-inventory-adjustments')){
            $validated = $request->validated();
            $adjustment                  = new InventoryAdjustment();
            $adjustment->adjustment_date = $validated['adjustment_date'];
            $adjustment->warehouse_id    = $validated['warehouse_id'];
            $adjustment->adjustment_type = $validated['adjustment_type'];
            $adjustment->reason          = $validated['reason'];
            $adjustment->status          = 'draft';
            $adjustment->creator_id      = Auth::id();
            $adjustment->created_by      = creatorId();
            $adjustment->save();
            foreach($validated['items'] as $itemData) {
                InventoryAdjustmentItem::create([
                    'adjustment_id'       => $adjustment->id,
                    'item_id'             => $itemData['item_id'],
                    'current_quantity'    => $itemData['current_quantity'],
                    'adjusted_quantity'   => $itemData['adjusted_quantity'],
                    'difference_quantity' => $itemData['adjusted_quantity'] - $itemData['current_quantity'],
                    'notes'               => $itemData['notes'] ?? '',
                    'creator_id'          => Auth::id(),
                    'created_by'          => creatorId(),
                ]);
            }

            CreateInventoryAdjustment::dispatch($request, $adjustment);

            return redirect()->route('inventory.adjustments.index')->with('success', __('The inventory adjustment has been created successfully.'));
        }
        else{
            return redirect()->route('inventory.adjustments.index')->with('error', __('Permission denied'));
        }
    }

    public function show(InventoryAdjustment $adjustment)
    {
        if(Auth::user()->can('view-inventory-adjustments') && $adjustment->created_by == creatorId()){
            $adjustment->load(['warehouse', 'approver', 'items.inventoryItem.product']);

            return response()->json([
                'adjustment' => $adjustment
            ]);
        }
        else{
            return response()->json(['error' => __('Permission denied')], 403);
        }
    }
   
    public function destroy(InventoryAdjustment $adjustment)
    {
        if(Auth::user()->can('delete-inventory-adjustments') && $adjustment->created_by == creatorId()){
            if ($adjustment->status === 'posted') {
                return back()->with('error', __('Cannot delete posted adjustment.'));
            }

            DestroyInventoryAdjustment::dispatch($adjustment);

            $adjustment->delete();

            return back()->with('success', __('The inventory adjustment has been deleted.'));
        }
        else{
            return redirect()->route('inventory.adjustments.index')->with('error', __('Permission denied'));
        }
    }

    public function approve(InventoryAdjustment $adjustment)
    {
        if(Auth::user()->can('approve-inventory-adjustments')){
            if ($adjustment->status !== 'draft') {
                return back()->with('error', __('Only draft adjustments can be approved.'));
            }

            $adjustment->status      = 'approved';
            $adjustment->approved_by = Auth::id();
            $adjustment->approved_at = now();
            $adjustment->save();

            ApproveInventoryAdjustment::dispatch($adjustment);

            return back()->with('success', __('The inventory adjustment has been approved successfully.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function post(InventoryAdjustment $adjustment)
    {
        if(Auth::user()->can('approve-inventory-adjustments')){
            if ($adjustment->status !== 'approved') {
                return back()->with('error', __('Only approved adjustments can be posted.'));
            }
            foreach ($adjustment->items as $adjustmentItem) {
                $inventoryItem = $adjustmentItem->inventoryItem;
                $productId     = $inventoryItem->product_id;
                $warehouseId   = $adjustment->warehouse_id;
                $differenceQty = $adjustmentItem->difference_quantity;
                $stock = WarehouseStock::where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->first();                    
                if ($differenceQty > 0) {
                    if ($stock) {
                        $stock->increment('quantity', $differenceQty);
                    } else {
                        WarehouseStock::create([
                            'warehouse_id' => $warehouseId,
                            'product_id'   => $productId,
                            'quantity'     => $differenceQty
                        ]);
                    }

                } elseif ($differenceQty < 0) {
                    if ($stock) {
                        $stock->decrement('quantity', abs($differenceQty));
                    }
                }
            }

            $adjustment->status = 'posted';
            $adjustment->save();

            PostInventoryAdjustment::dispatch($adjustment);

            return back()->with('success', __('The inventory adjustment has been posted successfully.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

}
