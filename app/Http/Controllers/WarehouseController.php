<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Events\CreateWarehouse;
use App\Events\DestroyWarehouse;
use App\Events\UpdateWarehouse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-warehouses')){
            $baseQuery = Warehouse::query()
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-warehouses')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-warehouses')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('search'), function($q) {
                    $term = request('search');
                    $q->where(function($q2) use ($term) {
                        $q2->where('name', 'like', "%{$term}%")
                            ->orWhere('city', 'like', "%{$term}%")
                            ->orWhere('address', 'like', "%{$term}%");
                    });
                });

            $neverStockedCutoff = now()->subDays(14);

            $warehouses = (clone $baseQuery)
                ->withSum(['stocks as stock_quantity'], 'quantity')
                ->withCount(['stocks as product_count'])
                ->with(['stocks.product:id,purchase_price'])
                ->latest()
                ->get()
                ->map(function (Warehouse $warehouse) use ($neverStockedCutoff) {
                    $stockQty = (float) ($warehouse->stock_quantity ?? 0);
                    $warehouse->stock_quantity = $stockQty;
                    $warehouse->stock_value = $warehouse->stocks->sum(
                        fn($stock) => max(0, (float) $stock->quantity) * (float) ($stock->product->purchase_price ?? 0)
                    );
                    $warehouse->out_of_stock_count = $warehouse->stocks->where('quantity', '<=', 0)->count();
                    $warehouse->has_stranded_stock = !$warehouse->is_active && $stockQty > 0;
                    $warehouse->is_never_stocked = $warehouse->is_active
                        && $warehouse->created_at <= $neverStockedCutoff
                        && $stockQty <= 0;
                    unset($warehouse->stocks);
                    return $warehouse;
                });

            $activeCount = $warehouses->where('is_active', true)->count();

            return Inertia::render('warehouses/index', [
                'warehouses' => $warehouses->values(),
                'stats' => [
                    'total' => $warehouses->count(),
                    'active' => $activeCount,
                    'inactive' => $warehouses->count() - $activeCount,
                ],
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreWarehouseRequest $request)
    {
        if(Auth::user()->can('create-warehouses')){
            $validated = $request->validated();
            $validated['is_active'] = $request->boolean('is_active', true);

            $warehouse = new Warehouse();
            $warehouse->name = $validated['name'];
            $warehouse->address = $validated['address'];
            $warehouse->city = $validated['city'];
            $warehouse->zip_code = $validated['zip_code'];
            $warehouse->phone = $validated['phone'];
            $warehouse->email = $validated['email'];
            $warehouse->is_active = $validated['is_active'];
            $warehouse->creator_id = Auth::id();
            $warehouse->created_by = creatorId();
            $warehouse->save();

            // Dispatch event for packages to handle their fields
            CreateWarehouse::dispatch($request, $warehouse);

            return redirect()->route('warehouses.index')->with('success', __('The warehouse has been created successfully.'));
        }
        else{
            return redirect()->route('warehouses.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        if(Auth::user()->can('edit-warehouses')){
            $validated = $request->validated();
            $validated['is_active'] = $request->boolean('is_active', true);

            $warehouse->name = $validated['name'];
            $warehouse->address = $validated['address'];
            $warehouse->city = $validated['city'];
            $warehouse->zip_code = $validated['zip_code'];
            $warehouse->phone = $validated['phone'];
            $warehouse->email = $validated['email'];
            $warehouse->is_active = $validated['is_active'];
            $warehouse->save();

            // Dispatch event for packages to handle their fields
            UpdateWarehouse::dispatch($request, $warehouse);

            return back()->with('success', __('The warehouse details are updated successfully.'));
        }
        else{
            return redirect()->route('warehouses.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(Warehouse $warehouse)
    {
        if(Auth::user()->can('delete-warehouses')){
            DestroyWarehouse::dispatch($warehouse);

            $warehouse->delete();

            return back()->with('success', __('The warehouse has been deleted.'));
        }
        else{
            return redirect()->route('warehouses.index')->with('error', __('Permission denied'));
        }
    }
}
