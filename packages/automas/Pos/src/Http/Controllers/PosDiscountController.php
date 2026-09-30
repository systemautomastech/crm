<?php

namespace Automas\Pos\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Pos\Models\PosDiscount;
use Automas\Pos\Http\Requests\StorePosDiscountRequest;
use Automas\Pos\Http\Requests\UpdatePosDiscountRequest;
use Automas\Pos\Events\CreatePosDiscount;
use Automas\Pos\Events\UpdatePosDiscount;
use Automas\Pos\Events\DestroyPosDiscount;
use Automas\ProductService\Models\ProductServiceItem;
use Automas\ProductService\Models\ProductServiceCategory;

class PosDiscountController extends Controller
{
    public function index(Request $request)
    {
        if(Auth::user()->can('manage-pos-discounts')){
            $query = PosDiscount::with(['products:id,name', 'category:id,name'])
                ->where('created_by', creatorId());

            if ($request->search) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }
            if ($request->status) {
                $query->where('is_active', $request->status === 'active' ? 1 : 0);
            }
            if ($request->discount_type) {
                $query->where('discount_type', $request->discount_type);
            }
            if ($request->date_from && $request->date_to) {
                $query->whereBetween('start_date', [$request->date_from, $request->date_to]);
            }

            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $allowedSortFields = ['name', 'discount_value', 'created_at'];
            if (!in_array($sortField, $allowedSortFields) || empty($sortField)) {
                $sortField = 'created_at';
            }

            $query->orderBy($sortField, $sortDirection);
            $perPage = $request->get('per_page', 10);
            $discounts = $query->paginate($perPage);

            $products = ProductServiceItem::select('id', 'name')
                ->where('created_by', creatorId())
                ->orderBy('name')
                ->get();
            $categories = ProductServiceCategory::select('id', 'name')
                ->where('created_by', creatorId())
                ->orderBy('name')
                ->get();

            return Inertia::render('Pos/PosDiscount/Index', [
                'discounts' => $discounts,
                'products' => $products,
                'categories' => $categories,
                'filters' => $request->only(['search', 'status', 'discount_type', 'date_from', 'date_to'])
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function create()
    {
        if(Auth::user()->can('create-pos-discounts')){
            $products = ProductServiceItem::select('id', 'name', 'sku', 'sale_price', 'category_id')
                ->where('created_by', creatorId())
                ->orderBy('name')
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku ?? '',
                        'price' => $product->sale_price ?? 0,
                        'category_id' => $product->category_id,
                    ];
                });

            $categories = ProductServiceCategory::select('id', 'name')
                ->where('created_by', creatorId())
                ->orderBy('name')
                ->get();

            return Inertia::render('Pos/PosDiscount/Create', [
                'products' => $products,
                'categories' => $categories,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StorePosDiscountRequest $request)
    {
        if(Auth::user()->can('create-pos-discounts')){
            $discount = new PosDiscount();
            $discount->name = $request->name;
            $discount->discount_type = $request->discount_type;
            $discount->discount_value = $request->discount_value;
            $discount->min_quantity = $request->min_quantity ?? 1;
            $discount->start_date = $request->start_date ?: null;
            $discount->end_date = $request->end_date ?: null;
            $discount->is_active = true;
            $discount->category_id = $request->category_id ?? null;
            $discount->creator_id = Auth::id();
            $discount->created_by = creatorId();
            $discount->save();

            // Attach products
            if (!empty($request->product_ids)) {
                $discount->products()->attach($request->product_ids);
            }

            try {
                CreatePosDiscount::dispatch($request, $discount);
            } catch (\Throwable $th) {
                return back()->with('error', $th->getMessage());
            }

            return redirect()->route('pos.discounts.index')->with('success', __('The pos discount has been created successfully.'));
        } else {
            return redirect()->route('pos.discounts.index')->with('error', __('Permission denied'));
        }
    }

    public function edit(PosDiscount $posDiscount)
    {
        if(Auth::user()->can('edit-pos-discounts') && $posDiscount->created_by == creatorId()){
            $posDiscount->load(['products:id,name,sku,sale_price,category_id', 'category:id,name']);
            
            $products = ProductServiceItem::select('id', 'name', 'sku', 'sale_price', 'category_id')
                ->where('created_by', creatorId())
                ->orderBy('name')
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku ?? '',
                        'price' => $product->sale_price ?? 0,
                        'category_id' => $product->category_id,
                    ];
                });

            $categories = ProductServiceCategory::select('id', 'name')
                ->where('created_by', creatorId())
                ->orderBy('name')
                ->get();

            return Inertia::render('Pos/PosDiscount/Edit', [
                'discount' => $posDiscount,
                'products' => $products,
                'categories' => $categories,
            ]);
        } else {
            return redirect()->route('pos.discounts.index')->with('error', __('Permission denied'));
        }
    }

    public function show(PosDiscount $posDiscount)
    {
        if(Auth::user()->can('view-pos-discounts') && $posDiscount->created_by == creatorId()){
            $posDiscount->load(['products:id,name,sku', 'category:id,name']);

            return Inertia::render('Pos/PosDiscount/View', [
                'discount' => $posDiscount,
            ]);
        } else {
            return redirect()->route('pos.discounts.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdatePosDiscountRequest $request, PosDiscount $posDiscount)
    {
        if(Auth::user()->can('edit-pos-discounts') && $posDiscount->created_by == creatorId()){
            $posDiscount->name = $request->name;
            $posDiscount->discount_type = $request->discount_type;
            $posDiscount->discount_value = $request->discount_value;
            $posDiscount->min_quantity = $request->min_quantity ?? 1;
            $posDiscount->start_date = $request->start_date ?: null;
            $posDiscount->end_date = $request->end_date ?: null;
            $posDiscount->category_id = $request->category_id ?? null;
            $posDiscount->save();

            // Sync products
            if (isset($request->product_ids)) {
                $posDiscount->products()->sync($request->product_ids);
            }

            try {
                UpdatePosDiscount::dispatch($request, $posDiscount);
            } catch (\Throwable $th) {
                return back()->with('error', $th->getMessage());
            }

            return redirect()->route('pos.discounts.index')->with('success', __('The pos discount has been updated successfully.'));
        } else {
            return redirect()->route('pos.discounts.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(PosDiscount $posDiscount)
    {
        if(Auth::user()->can('delete-pos-discounts') && $posDiscount->created_by == creatorId()){
            DestroyPosDiscount::dispatch($posDiscount);
            $posDiscount->delete();

            return redirect()->route('pos.discounts.index')->with('success', __('The pos discount has been deleted.'));
        } else {
            return redirect()->route('pos.discounts.index')->with('error', __('Permission denied'));
        }
    }
}
