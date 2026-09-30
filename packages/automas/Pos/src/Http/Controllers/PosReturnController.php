<?php

namespace Automas\Pos\Http\Controllers;

use Automas\Pos\Models\PosReturn;
use Automas\Pos\Models\PosReturnItem;
use Automas\Pos\Models\PosReturnItemTax;
use Automas\Pos\Models\Pos;
use Automas\Pos\Models\PosItem;
use App\Models\User;
use App\Models\Warehouse;
use Automas\ProductService\Models\ProductServiceTax;
use Automas\Pos\Events\ApprovePosReturn;
use Automas\Pos\Events\CompletePosReturn;
use Automas\Pos\Events\CreatePosReturn;
use Automas\Pos\Events\DestroyPosReturn;
use Illuminate\Http\Request;
use Automas\Pos\Http\Requests\StorePosReturnRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\EmailTemplate;

class PosReturnController extends \Illuminate\Routing\Controller
{
    private function getItemReturnDiscount($originalItem, float $returnQty): float
    {
        // Use item-level discount (new system)
        if (!is_null($originalItem->item_discount_amount) && $originalItem->item_discount_amount > 0) {
            return ($originalItem->item_discount_amount / $originalItem->quantity) * $returnQty;
        }

        return 0.0;
    }

    private function checkReturnAccess(PosReturn $posReturn)
    {
        if(Auth::user()->can('manage-any-pos-returns')) {
            return true;
        } elseif(Auth::user()->can('manage-own-pos-returns')) {
            if($posReturn->creator_id != Auth::id() && $posReturn->customer_id != Auth::id()) {
                return false;
            }
            if($posReturn->creator_id != Auth::id() && Auth::user()->type == 'client' && $posReturn->status == 'draft') {
                return false;
            }
            return true;
        }
        return false;
    }

    public function index(Request $request)
    {
        if(Auth::user()->can('manage-pos-returns')){
            $query = PosReturn::with(['customer', 'originalPos', 'items.product', 'warehouse'])
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-pos-returns')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-pos-returns')) {
                        $q->where('creator_id', Auth::id())->orWhere('customer_id',Auth::id());
                        if(Auth::user()->type == 'client') {
                            $q->where('status','!=', 'draft');
                        }
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                });

            if ($request->customer_id) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->warehouse_id) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->status) {
                $query->where('status', $request->status);
            }
            if ($request->search) {
                $query->where('return_number', 'like', '%' . $request->search . '%');
            }
            if ($request->date_range) {
                $dates = explode(' - ', $request->date_range);
                if (count($dates) === 2) {
                    $query->whereBetween('return_date', [$dates[0], $dates[1]]);
                }
            }

            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $allowedSortFields = ['return_number', 'return_date', 'total_amount', 'status', 'created_at'];
            if (!in_array($sortField, $allowedSortFields) || empty($sortField)) {
                $sortField = 'created_at';
            }

            $query->orderBy($sortField, $sortDirection);
            $perPage = $request->get('per_page', 10);
            $returns = $query->paginate($perPage);

            $customers = User::where('type', 'client')->select('id', 'name', 'email')->where('created_by', creatorId())->get();
            $warehouses = Warehouse::where('is_active', true)->select('id', 'name')->where('created_by', creatorId())->get();

            return Inertia::render('Pos/PosReturn/Index', [
                'returns' => $returns,
                'customers' => $customers,
                'warehouses' => $warehouses,
                'filters' => $request->only(['customer_id', 'warehouse_id', 'status', 'search', 'date_range'])
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function create()
    {
        if(Auth::user()->can('create-pos-returns')){
            $posSales = Pos::with(['customer', 'warehouse', 'items.product', 'posReturns.items'])
                ->where('created_by', creatorId())
                ->when(Auth::user()->type == 'client', function($q) {
                    $q->where('customer_id', Auth::id());
                })
                ->get();

            foreach ($posSales as $pos) {
                foreach ($pos->items as $item) {
                    $totalReturned = $pos->posReturns
                        ->where('status', '!=', 'cancelled')
                        ->flatMap->items
                        ->where('original_pos_item_id', $item->id)
                        ->sum('return_quantity');

                    $item->available_quantity = $item->quantity - $totalReturned;
                    
                    // Load tax details for each item
                    if ($item->tax_ids && is_array($item->tax_ids)) {
                        $item->taxes = ProductServiceTax::whereIn('id', $item->tax_ids)
                            ->where('created_by', creatorId())
                            ->get(['id', 'tax_name', 'rate'])
                            ->map(function($tax) {
                                return [
                                    'id' => $tax->id,
                                    'name' => $tax->tax_name,
                                    'rate' => $tax->rate
                                ];
                            })
                            ->toArray();
                    } else {
                        $item->taxes = [];
                    }
                }
            }

            $warehouses = Warehouse::where('created_by', creatorId())->where('is_active', true)->get();

            return Inertia::render('Pos/PosReturn/Create', [
                'posSales' => $posSales,
                'warehouses' => $warehouses
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StorePosReturnRequest $request)
    {
        if(Auth::user()->can('create-pos-returns')){
            $totals = $this->calculateReturnTotals($request->items, $request->original_pos_id);
            
            $return = new PosReturn();
            $return->return_date = $request->return_date;
            $return->customer_id = $request->customer_id;
            $return->warehouse_id = $request->warehouse_id ?? null;
            $return->original_pos_id = $request->original_pos_id;
            $return->reason = $request->reason;
            $return->notes = $request->notes;
            $return->subtotal = $totals['subtotal'];
            $return->tax_amount = $totals['tax_amount'];
            $return->discount_amount = $totals['discount_amount'];
            $return->total_amount = $totals['total_amount'];
            $return->status = 'draft';
            $return->creator_id = Auth::id();
            $return->created_by = creatorId();
            $return->save();

            $this->createReturnItems($return->id, $request->items, $request->original_pos_id);
            
            try {
                CreatePosReturn::dispatch($request, $return);
                
                if(company_setting('POS Return') == 'on' && $return->customer && $return->customer->email) {
                    $emailData = [
                        'return_number' => $return->return_number ?? null,
                        'return_date' => $request->return_date ?? null,
                        'customer_name' => $return->customer->name ?? null,
                        'warehouse_name' => $return->warehouse->name ?? null,
                        'reason' => $request->reason ?? null,
                        'total_amount' => $totals['total_amount'] ?? null,
                    ];
                    $message = EmailTemplate::sendEmailTemplate('POS Return', [$return->customer->email], $emailData);
                    if($message['is_success'] == false && !empty($message['error'])) {
                        return back()
                            ->with('success', __('The POS return has been created successfully.'))
                            ->with('error', $message['error']);
                    }
                }
                
            } catch (\Throwable $th) {
                return back()->with('error', $th->getMessage());
            }
            
            return redirect()->route('pos.returns.index')->with('success', __('The POS return has been created successfully.'));
        } else {
            return redirect()->route('pos.returns.index')->with('error', __('Permission denied'));
        }
    }

    public function show(PosReturn $posReturn)
    {
        if(Auth::user()->can('view-pos-returns') && $posReturn->created_by == creatorId()){
            if(!$this->checkReturnAccess($posReturn)) {
                return redirect()->route('pos.returns.index')->with('error', __('Permission denied'));
            }

            $posReturn->load(['customer', 'warehouse', 'customerDetails', 'originalPos', 'items.product']);

            return Inertia::render('Pos/PosReturn/View', [
                'return' => $posReturn
            ]);
        } else {
            return redirect()->route('pos.returns.index')->with('error', __('Permission denied'));
        }
    }

    public function approve(PosReturn $posReturn)
    {
        if(Auth::user()->can('approve-pos-returns')){
            if ($posReturn->status !== 'draft') {
                return redirect()->back()->with('error', __('Only draft returns can be approved.'));
            }

            try {
                ApprovePosReturn::dispatch($posReturn);
            } catch (\Throwable $th) {
                return back()->with('error', $th->getMessage());
            }

            $posReturn->update(['status' => 'approved']);

            return redirect()->route('pos.returns.index')->with('success', __('The POS return has been approved successfully.'));
        } else {
            return redirect()->route('pos.returns.index')->with('error', __('Permission denied'));
        }
    }

    public function complete(PosReturn $posReturn)
    {
        if(Auth::user()->can('complete-pos-returns')){
            if ($posReturn->status !== 'approved') {
                return redirect()->back()->with('error', __('Only approved returns can be completed.'));
            }

            CompletePosReturn::dispatch($posReturn);
            $posReturn->update(['status' => 'completed']);

            return redirect()->route('pos.returns.index')->with('success', __('The POS return has been completed successfully.'));
        } else {
            return redirect()->route('pos.returns.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(PosReturn $posReturn)
    {
        if(Auth::user()->can('delete-pos-returns') && $posReturn->created_by == creatorId()){
            if ($posReturn->status !== 'draft') {
                return back()->with('error', __('Only draft returns can be deleted.'));
            }
            
            DestroyPosReturn::dispatch($posReturn);
            $posReturn->update(['status' => 'cancelled']);

            return back()->with('success', __('The POS return has been cancelled.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    private function calculateReturnTotals($items, $originalPosId)
    {
        $originalPos = Pos::with(['items'])->find($originalPosId);
        $subtotal = 0;
        $totalTax = 0;
        $totalDiscount = 0;

        foreach ($items as $item) {
            $originalItem = $originalPos->items->where('id', $item['original_pos_item_id'])->first();
            $lineTotal = $item['return_quantity'] * $item['unit_price'];
            
            $discountAmount = $originalItem
                ? $this->getItemReturnDiscount($originalItem, $item['return_quantity'])
                : 0.0;
            
            $afterDiscount = $lineTotal - $discountAmount;
            
            $taxAmount = 0;
            if ($originalItem && $originalItem->tax_ids && is_array($originalItem->tax_ids)) {
                $taxes = ProductServiceTax::whereIn('id', $originalItem->tax_ids)->get();
                foreach ($taxes as $tax) {
                    $taxAmount += $afterDiscount * ($tax->rate / 100);
                }
            }

            $subtotal += $lineTotal;
            $totalDiscount += $discountAmount;
            $totalTax += $taxAmount;
        }

        $totalAmount = $subtotal - $totalDiscount + $totalTax;

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $totalTax,
            'discount_amount' => $totalDiscount,
            'total_amount' => $totalAmount
        ];
    }

    private function createReturnItems($returnId, $items, $originalPosId)
    {
        $originalPos = Pos::with(['items.product'])->find($originalPosId);

        foreach ($items as $itemData) {
            $originalItem = $originalPos->items->where('id', $itemData['original_pos_item_id'])->first();
            $lineTotal = $itemData['return_quantity'] * $itemData['unit_price'];
            
            $discountAmount = $originalItem
                ? $this->getItemReturnDiscount($originalItem, $itemData['return_quantity'])
                : 0.0;
            
            $afterDiscount = $lineTotal - $discountAmount;
            
            $taxAmount = 0;
            if ($originalItem && $originalItem->tax_ids && is_array($originalItem->tax_ids)) {
                $taxes = ProductServiceTax::whereIn('id', $originalItem->tax_ids)->get();
                foreach ($taxes as $tax) {
                    $taxAmount += $afterDiscount * ($tax->rate / 100);
                }
            }
            $totalAmount = $afterDiscount + $taxAmount;

            $item = new PosReturnItem();
            $item->return_id = $returnId;
            $item->product_id = $itemData['product_id'];
            $item->original_pos_item_id = $itemData['original_pos_item_id'];
            $item->original_quantity = $originalItem ? $originalItem->quantity : 0;
            $item->return_quantity = $itemData['return_quantity'];
            $item->unit_price = $itemData['unit_price'];
            $item->discount_amount = $discountAmount;
            $item->tax_amount = $taxAmount;
            $item->total_amount = $totalAmount;
            $item->reason = $itemData['reason'] ?? null;
            $item->return_discount_amount = $discountAmount;
            $item->original_item_discount = $originalItem->item_discount_amount ?? 0;
            $item->save();

            // Store individual taxes from original POS item
            if ($originalItem && $originalItem->tax_ids && is_array($originalItem->tax_ids)) {
                $taxes = ProductServiceTax::whereIn('id', $originalItem->tax_ids)->get();
                foreach ($taxes as $tax) {
                    $returnItemTax = new PosReturnItemTax();
                    $returnItemTax->item_id = $item->id;
                    $returnItemTax->tax_name = $tax->tax_name;
                    $returnItemTax->tax_rate = $tax->rate;
                    $returnItemTax->save();
                }
            }
        }
    }
}
