<?php

namespace App\Http\Controllers;

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesInvoiceItemTax;
use App\Models\User;
use App\Models\Warehouse;
use App\Http\Requests\StoreSalesInvoiceRequest;
use App\Http\Requests\UpdateSalesInvoiceRequest;
use Automas\ProductService\Models\ProductServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Events\CreateSalesInvoice;
use App\Events\UpdateSalesInvoice;
use App\Events\DestroySalesInvoice;
use App\Events\PostSalesInvoice;
use App\Events\EditSalesInvoice;
use App\Models\SalesInvoiceSetup;
use App\Models\EmailTemplate;
use App\Services\CustomerService;
use App\Services\WarehouseService;
use Spatie\LaravelPdf\Facades\Pdf;

class SalesInvoiceController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected WarehouseService $warehouseService
    ) {}

    private function checkInvoiceAccess(SalesInvoice $salesInvoice)
    {
        if (Auth::user()->can('manage-any-sales-invoices')) {
            return true;
        } elseif (Auth::user()->can('manage-own-sales-invoices') || Auth::user()->can('manage-sales-orders') || Auth::user()->can('view-sales-orders')) {
            if ($salesInvoice->creator_id == Auth::id() || $salesInvoice->customer_id == Auth::id()) {
                return true;
            }
            if ($salesInvoice->creator_id != Auth::id() && Auth::user()->type == 'client' && $salesInvoice->status == 'draft') {
                return false;
            }
            if (class_exists(\Automas\SalesOrder\Models\SalesOrder::class)) {
                $salesOrder = \Automas\SalesOrder\Models\SalesOrder::where('invoice_id', $salesInvoice->id)->first();
                if ($salesOrder) {
                    if ($salesOrder->acquired_by == Auth::id()) {
                        return true;
                    }
                    if ($salesOrder->assignedUsers()->where('users.id', Auth::id())->exists()) {
                        return true;
                    }
                    if ($salesOrder->creator_id == Auth::id()) {
                        return true;
                    }
                }
            }
            return false;
        }
        return false;
    }

    public function index(Request $request)
    {
        if (Auth::user()->can('manage-sales-invoices')) {
            $baseQuery = SalesInvoice::with(['customer', 'items'])
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-sales-invoices')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-sales-invoices')) {
                        $q->where(function ($sq) {
                            $sq->where('creator_id', Auth::id())
                                ->orWhere('customer_id', Auth::id());
                            if (class_exists(\Automas\SalesOrder\Models\SalesOrder::class)) {
                                $sq->orWhereHas('salesOrder', function ($soq) {
                                    $soq->where('acquired_by', Auth::id())
                                        ->orWhere('creator_id', Auth::id())
                                        ->orWhereHas('assignedUsers', fn($auq) => $auq->where('users.id', Auth::id()));
                                });
                            }
                        });
                        if (Auth::user()->type == 'client') {
                            $q->where('status', '!=', 'draft');
                        }
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                });

            // Filters that apply to both the list and the status/value breakdown
            if ($request->customer_id) {
                $baseQuery->where('customer_id', $request->customer_id);
            }
            if ($request->warehouse_id) {
                $baseQuery->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->search) {
                $baseQuery->where(function ($q) use ($request) {
                    $q->where('invoice_number', 'like', '%' . $request->search . '%')
                        ->orWhereHas('customer', function ($cq) use ($request) {
                            $cq->where('name', 'like', '%' . $request->search . '%');
                        });
                });
            }
            if ($request->date_range) {
                $dates = explode(' - ', $request->date_range);
                if (count($dates) === 2) {
                    $baseQuery->whereBetween('invoice_date', [$dates[0], $dates[1]]);
                }
            }

            // Breakdown by status (ignores the status filter itself so the cards stay usable as quick filters)
            $statusBreakdown = (clone $baseQuery)
                ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            $today = now()->startOfDay();
            $dueSoonEnd = $today->copy()->addDays(7);
            $overdue30Start = $today->copy()->subDays(30);

            $stats = [
                'total_count' => (clone $baseQuery)->count(),
                'total_value' => (clone $baseQuery)->sum('total_amount'),
                'outstanding_count' => (clone $baseQuery)->whereIn('status', ['posted', 'partial'])->count(),
                'outstanding_value' => (clone $baseQuery)->whereIn('status', ['posted', 'partial'])->sum('balance_amount'),
                'collected_value' => (clone $baseQuery)->sum('paid_amount'),
                'overdue_count' => (clone $baseQuery)->where('due_date', '<', $today)->whereIn('status', ['posted', 'partial'])->where('balance_amount', '>', 0)->count(),
                'overdue_value' => (clone $baseQuery)->where('due_date', '<', $today)->whereIn('status', ['posted', 'partial'])->where('balance_amount', '>', 0)->sum('balance_amount'),
            ];
            foreach (['draft', 'posted', 'partial', 'paid'] as $status) {
                $stats["{$status}_count"] = (int) ($statusBreakdown[$status]->count ?? 0);
                $stats["{$status}_value"] = $statusBreakdown[$status]->total ?? 0;
            }

            // AR-aging buckets: still used to let ?status= deep-link into a collection-urgency slice
            // of the list (e.g. status=overdue_1_30), even though the dashboard no longer shows them.
            $agingFilters = [
                'current' => fn($q) => $q->where('due_date', '>', $dueSoonEnd),
                'due_soon' => fn($q) => $q->where('due_date', '>=', $today)->where('due_date', '<=', $dueSoonEnd),
                'overdue_1_30' => fn($q) => $q->where('due_date', '<', $today)->where('due_date', '>=', $overdue30Start),
                'overdue_30_plus' => fn($q) => $q->where('due_date', '<', $overdue30Start),
            ];

            $query = clone $baseQuery;
            if ($request->status) {
                if ($request->status === 'overdue') {
                    $query->where('due_date', '<', $today)
                        ->whereIn('status', ['posted', 'partial'])
                        ->where('balance_amount', '>', 0);
                } elseif ($request->status === 'outstanding') {
                    $query->whereIn('status', ['posted', 'partial']);
                } elseif (isset($agingFilters[$request->status])) {
                    $query->whereIn('status', ['posted', 'partial']);
                    $agingFilters[$request->status]($query);
                } else {
                    $query->where('status', $request->status);
                }
            }

            // Apply sorting
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');

            // Validate sort field to prevent SQL injection
            $allowedSortFields = ['invoice_number', 'invoice_date', 'due_date', 'subtotal', 'tax_amount', 'total_amount', 'balance_amount', 'status', 'created_at'];
            if (!in_array($sortField, $allowedSortFields) || empty($sortField)) {
                $sortField = 'created_at';
            }

            $query->orderBy($sortField, $sortDirection);

            $perPage = $request->get('per_page', 10);
            $invoices = $query->paginate($perPage);

            // Encrypt invoice id for public url
            $invoices->getCollection()->transform(function ($invoice) {
                return $invoice->setAttribute(
                    'public_url',
                    route('sales-invoice.client.view', [
                        'token' => Crypt::encryptString((string) $invoice->id),
                    ])
                );
            });

            $customers = $this->customerService->getCustomers();
            $warehouses = Warehouse::where('is_active', true)->select('id', 'name')->where('created_by', creatorId())->get();

            // Outstanding balance grouped by customer - a salesperson collects by relationship, not by
            // invoice, so this surfaces who owes the most and how stale their oldest unpaid invoice is.
            // Restricted to managers (manage-any-sales-invoices) since it exposes every customer's
            // standing at once, not just the invoices a single creator/customer owns.
            $customerSummaries = collect();
            if (Auth::user()->can('manage-any-sales-invoices')) {
                $customerSummaries = (clone $baseQuery)
                    ->selectRaw('customer_id')
                    ->selectRaw('COUNT(*) as invoice_count')
                    ->selectRaw("SUM(CASE WHEN status IN ('posted', 'partial') THEN balance_amount ELSE 0 END) as outstanding")
                    ->selectRaw(
                        "MAX(CASE WHEN status IN ('posted', 'partial') AND due_date < ? AND balance_amount > 0 THEN DATEDIFF(?, due_date) END) as oldest_overdue_days",
                        [$today->toDateString(), $today->toDateString()]
                    )
                    ->groupBy('customer_id')
                    ->havingRaw("SUM(CASE WHEN status IN ('posted', 'partial') THEN balance_amount ELSE 0 END) > 0")
                    ->orderByDesc('oldest_overdue_days')
                    ->orderByDesc('outstanding')
                    ->limit(20)
                    ->get();

                $customerModels = User::whereIn('id', $customerSummaries->pluck('customer_id'))
                    ->select('id', 'name', 'email', 'avatar')
                    ->get()
                    ->keyBy('id');

                $customerSummaries = $customerSummaries->map(function ($row) use ($customerModels) {
                    $customer = $customerModels->get($row->customer_id);
                    return [
                        'customer' => $customer ? [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'email' => $customer->email,
                            'avatar' => $customer->avatar,
                        ] : null,
                        'invoice_count' => (int) $row->invoice_count,
                        'outstanding' => $row->outstanding,
                        'oldest_overdue_days' => $row->oldest_overdue_days !== null ? (int) $row->oldest_overdue_days : null,
                    ];
                })->values();
            }

            return Inertia::render('Sales/Index', [
                'invoices' => $invoices,
                'customers' => $customers,
                'warehouses' => $warehouses,
                'stats' => $stats,
                'customerSummaries' => $customerSummaries,
                'filters' => $request->only(['customer_id', 'warehouse_id', 'status', 'search', 'date_range'])
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function create()
    {
        if (Auth::user()->can('create-sales-invoices')) {
            $customers = $this->customerService->getCustomers();
            $products = $this->warehouseService->getWarehouseProducts();
            $warehouses = $this->warehouseService->getActiveWarehouses();
            $setupSettings = SalesInvoiceSetup::getSettings(creatorId());

            return Inertia::render('Sales/Create', [
                'customers' => $customers,
                'products' => $products,
                'warehouses' => $warehouses,
                'default_payment_terms' => $setupSettings['sales_invoice_default_payment_terms'] ?? '',
                'invoice_settings' => $setupSettings,
                'invoice_number' => SalesInvoice::generateInvoiceNumber(),
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreSalesInvoiceRequest $request)
    {
        if (Auth::user()->can('create-sales-invoices')) {
            $totals = $this->calculateTotals($request->items);

            $invoice = DB::transaction(function () use ($request, $totals) {
                $invoice = new SalesInvoice();
                if (!empty($request->invoice_number)) {
                    $invoice->invoice_number = $request->invoice_number;
                }
                $invoice->invoice_date = $request->invoice_date;
                $invoice->due_date = $request->due_date;
                $invoice->customer_id = $request->customer_id;
                $invoice->warehouse_id = $request->warehouse_id;
                $invoice->type = $request->type ?? 'product';
                $invoice->payment_terms = $request->payment_terms;
                $invoice->notes = $request->notes;
                $invoice->subtotal = $totals['subtotal'];
                $invoice->tax_amount = $totals['tax_amount'];
                $invoice->discount_amount = $totals['discount_amount'];
                $invoice->total_amount = $totals['total_amount'];
                $invoice->balance_amount = $totals['total_amount'];
                $invoice->creator_id = Auth::id();
                $invoice->created_by = creatorId();
                $invoice->save();

                // Create invoice items
                $this->createInvoiceItems($invoice->id, $request->items);

                return $invoice;
            });

            try {

                CreateSalesInvoice::dispatch($request, $invoice);
                // Send sales invoice mail
                if (company_setting('Sales Invoice') == 'on') {
                    $customerEmail = $invoice->customer?->email ?? $invoice->customer_email;
                    $customerName = $invoice->customer?->name ?? $invoice->customer_name;
                    $emailData = [
                        'invoice_number' => $invoice->invoice_number ?? null,
                        'sales_customer_name' => $customerName ?? null,
                        'warehouse_name' => $invoice->warehouse->name ?? null,
                        'total_amount' => $totals['total_amount'] ?? null,
                        'discount_amount' => $totals['discount_amount'] ?? null,
                    ];
                    if ($customerEmail) {
                        EmailTemplate::sendEmailTemplate('Sales Invoice', [$customerEmail], $emailData, $invoice->created_by);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Sales invoice mail failed: ' . $e->getMessage());
            }

            return redirect()->route('sales-invoices.index')->with('success', __('The sales invoice created successfully.'));
        } else {
            return redirect()->route('sales-invoices.index')->with('error', __('Permission denied'));
        }
    }

    public function show(SalesInvoice $salesInvoice)
    {
        if (Auth::user()->can('view-sales-invoices')) {
            if (!$this->checkInvoiceAccess($salesInvoice)) {
                return back()->with('error', __('Permission denied'));
            }

            $salesInvoice->load([
                'customer',
                'customerDetails',
                'items.product.unitRelation',
                'items.taxes',
                'warehouse',
                'paymentAllocations.payment.bankAccount',
                'paymentAllocations'
            ]);
            // dd($salesInvoice);
            return Inertia::render('Sales/View', [
                'invoice' => $salesInvoice,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function edit(SalesInvoice $salesInvoice)
    {
        if (Auth::user()->can('edit-sales-invoices') && $salesInvoice->created_by == creatorId()) {
            if ($salesInvoice->status != 'draft') {
                return redirect()->route('sales-invoices.index')->with('error', __('Cannot edit posted invoice.'));
            }

            $salesInvoice->load(['items.taxes', 'items.product.unitRelation']);

            EditSalesInvoice::dispatch($salesInvoice);

            $customers = $this->customerService->getCustomers();
            $products = $this->warehouseService->getWarehouseProducts($salesInvoice->warehouse_id);
            $warehouses = $this->warehouseService->getActiveWarehouses();

            return Inertia::render('Sales/Edit', [
                'invoice' => $salesInvoice,
                'customers' => $customers,
                'products' => $products,
                'warehouses' => $warehouses,
            ]);
        } else {
            return redirect()->route('sales-invoices.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateSalesInvoiceRequest $request, SalesInvoice $salesInvoice)
    {
        if (Auth::user()->can('edit-sales-invoices') && $salesInvoice->created_by == creatorId()) {
            if ($salesInvoice->status != 'draft') {
                return redirect()->route('sales-invoices.index')->with('error', __('Cannot update posted invoice.'));
            }
            $totals = $this->calculateTotals($request->items);

            $salesInvoice->invoice_date = $request->invoice_date;
            $salesInvoice->due_date = $request->due_date;
            $salesInvoice->customer_id = $request->customer_id;
            $salesInvoice->warehouse_id = $request->warehouse_id;
            $salesInvoice->payment_terms = $request->payment_terms;
            $salesInvoice->notes = $request->notes;
            $salesInvoice->subtotal = $totals['subtotal'];
            $salesInvoice->tax_amount = $totals['tax_amount'];
            $salesInvoice->discount_amount = $totals['discount_amount'];
            $salesInvoice->total_amount = $totals['total_amount'];
            $salesInvoice->balance_amount = $totals['total_amount'];
            $salesInvoice->save();

            // Delete existing items and recreate
            $salesInvoice->items()->delete();
            $this->createInvoiceItems($salesInvoice->id, $request->items);

            // Dispatch event for packages to handle their fields
            UpdateSalesInvoice::dispatch($request, $salesInvoice);

            return redirect()->route('sales-invoices.index')->with('success', __('The sales invoice details are updated successfully.'));
        } else {
            return redirect()->route('sales-invoices.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(SalesInvoice $salesInvoice)
    {
        if (Auth::user()->can('delete-sales-invoices')) {
            if ($salesInvoice->status === 'posted') {
                return back()->withErrors(['error' => __('Cannot delete posted invoice.')]);
            }

            // Dispatch event before deletion
            DestroySalesInvoice::dispatch($salesInvoice);

            $salesInvoice->delete();

            return redirect()->route('sales-invoices.index')->with('success', __('The sales invoice has been deleted.'));
        } else {
            return redirect()->route('sales-invoices.index')->with('error', __('Permission denied'));
        }
    }

    public function clientInvoice($token)
    {
        try {
            $invoiceId = Crypt::decryptString($token);
        } catch (\Exception $e) {
            $invoiceId = $token;
        }

        $salesInvoice = SalesInvoice::with([
            'customer',
            'customerDetails',
            'items.product.unitRelation',
            'items.taxes',
            'warehouse',
            'paymentAllocations.payment.bankAccount',
        ])->find($invoiceId);

        if (!$salesInvoice) {
            abort(404, __('Invoice not found'));
        }

        $creatorId = $salesInvoice->created_by ?? creatorId();
        $salesInvoiceSetting = SalesInvoiceSetup::getSettings($creatorId);

        return view('sales.public_invoice', [
            'invoice' => $salesInvoice,
            'salesInvoiceSetting' => $salesInvoiceSetting,
        ]);
    }

    private function calculateTotals($items)
    {
        $subtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;

        foreach ($items as $item) {
            $lineTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $discType = $item['discount_type'] ?? 'percentage';
            if ($discType === 'fixed') {
                $discountAmount = min(max((float) ($item['discount_amount'] ?? 0), 0), $lineTotal);
            } else {
                $discountAmount = ($lineTotal * (float) ($item['discount_percentage'] ?? 0)) / 100;
            }
            $afterDiscount = max(0, $lineTotal - $discountAmount);
            $taxAmount = ($afterDiscount * (float) ($item['tax_percentage'] ?? 0)) / 100;

            $subtotal += $lineTotal;
            $totalDiscount += $discountAmount;
            $totalTax += $taxAmount;
        }

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $totalTax,
            'discount_amount' => $totalDiscount,
            'total_amount' => max(0, $subtotal + $totalTax - $totalDiscount)
        ];
    }

    private function createInvoiceItems($invoiceId, $items)
    {
        foreach ($items as $itemData) {
            $item = new SalesInvoiceItem();
            $item->invoice_id = $invoiceId;
            $item->product_id = $itemData['product_id'];
            $item->description = $itemData['description'] ?? null;
            $item->product_type = $itemData['product_type'] ?? 'product';
            $item->quantity = $itemData['quantity'];
            $item->unit_price = $itemData['unit_price'];
            $item->discount_type = $itemData['discount_type'] ?? 'percentage';
            $lineTotal = (float) $item->quantity * (float) $item->unit_price;
            if ($item->discount_type === 'fixed') {
                $discAmt = min(max((float) ($itemData['discount_amount'] ?? 0), 0), $lineTotal);
                $item->discount_amount = $discAmt;
                $item->discount_percentage = $lineTotal > 0 ? round(($discAmt / $lineTotal) * 100, 4) : 0;
            } else {
                $item->discount_percentage = $itemData['discount_percentage'] ?? 0;
                $item->discount_amount = ($lineTotal * (float) $item->discount_percentage) / 100;
            }
            $item->tax_percentage = $itemData['tax_percentage'] ?? 0;
            $item->save();

            // Store individual taxes
            if (isset($itemData['taxes']) && is_array($itemData['taxes'])) {
                foreach ($itemData['taxes'] as $tax) {
                    $salesInvoiceItemTax = new SalesInvoiceItemTax();
                    $salesInvoiceItemTax->item_id = $item->id;
                    $salesInvoiceItemTax->tax_name = $tax['tax_name'];
                    $salesInvoiceItemTax->tax_rate = $tax['tax_rate'] ?? $tax['rate'] ?? 0;
                    $salesInvoiceItemTax->save();
                }
            }
        }
    }

    public function post(SalesInvoice $salesInvoice)
    {
        if (Auth::user()->can('post-sales-invoices')) {
            if ($salesInvoice->status !== 'draft') {
                return back()->withErrors(['error' => __('Only draft invoices can be posted.')]);
            }

            try {
                PostSalesInvoice::dispatch($salesInvoice);
            } catch (\Throwable $th) {
                return back()->with('error', $th->getMessage());
            }

            $salesInvoice->update(['status' => 'posted']);

            return back()->with('success', __('The sales invoice has been posted successfully.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function getWarehouseProducts(Request $request)
    {
        if (Auth::user()->can('create-sales-invoices') || Auth::user()->can('edit-sales-invoices')) {
            $warehouseId = $request->warehouse_id ? (int) $request->warehouse_id : null;
            $products = $this->warehouseService->getWarehouseProducts($warehouseId);
            return response()->json($products);
        } else {
            return response()->json([], 403);
        }
    }

    public function getServices(Request $request)
    {
        if (Auth::user()->can('create-sales-invoices') || Auth::user()->can('edit-sales-invoices')) {
            $services = ProductServiceItem::select('id', 'name', 'sku', 'sale_price', 'tax_ids', 'unit', 'type')
                ->where('is_active', true)
                ->where('type', 'service')
                ->where('created_by', creatorId())
                ->get()
                ->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'sku' => $service->sku,
                        'sale_price' => $service->sale_price,
                        'unit' => $service->unit,
                        'type' => $service->type,
                        'taxes' => $service->taxes->map(function ($tax) {
                            return [
                                'id' => $tax->id,
                                'tax_name' => $tax->tax_name,
                                'rate' => $tax->rate
                            ];
                        })
                    ];
                });
            return response()->json($services);
        } else {
            return response()->json([], 403);
        }
    }

    public function print(SalesInvoice $salesInvoice)
    {
        if (Auth::user()->can('print-sales-invoices')) {
            if (!$this->checkInvoiceAccess($salesInvoice)) {
                return back()->with('error', __('Permission denied'));
            }

            $salesInvoice->load([
                'customer',
                'customerDetails',
                'items.product.unitRelation',
                'items.taxes',
                'warehouse',
                'paymentAllocations.payment.bankAccount',
            ]);

            $billTo = [
                'name' => $salesInvoice->customerDetails->billing_address->name ?? $salesInvoice->customerDetails->company_name ?? $salesInvoice->customer_name ?? '',
                'email' => $salesInvoice->customerDetails->contact_person_email ?? $salesInvoice->customerDetails->customer_email ?? '',
                'phone' => $salesInvoice->customerDetails->contact_person_mobile ?? $salesInvoice->customerDetails->customer_phone ?? '',
                'address_line_1' => $salesInvoice->customerDetails->billing_address['address_line_1'] ?? '',
                'address_line_2' => $salesInvoice->customerDetails->billing_address['address_line_2'] ?? '',
                'city' => $salesInvoice->customerDetails->billing_address['city'] ?? '',
                'state' => $salesInvoice->customerDetails->billing_address['state'] ?? '',
                'country' => $salesInvoice->customerDetails->billing_address['country'] ?? '',
                'zip' => $salesInvoice->customerDetails->billing_address['zip_code'] ?? '',
            ];

            $same_as_billing = $salesInvoice->customerDetails->same_as_billing;
            $shipTo = [];

            if (!$same_as_billing) {
                $shipTo = [
                    'name' => $salesInvoice->customerDetails->shipping_address->name ?? $billTo['name'] ?? '',
                    'email' => $salesInvoice->customerDetails->shipping_address->email ?? $billTo['email'] ?? '',
                    'phone' => $salesInvoice->customerDetails->shipping_address->phone ?? $billTo['phone'] ?? '',
                    'address_line_1' => $salesInvoice->customerDetails->shipping_address['address_line_1'] ?? $billTo['address_line_1'] ?? '',
                    'address_line_2' => $salesInvoice->customerDetails->shipping_address['address_line_2'] ?? $billTo['address_line_2'] ?? '',
                    'city' => $salesInvoice->customerDetails->shipping_address['city'] ?? $billTo['city'] ?? '',
                    'state' => $salesInvoice->customerDetails->shipping_address['state'] ?? $billTo['state'] ?? '',
                    'country' => $salesInvoice->customerDetails->shipping_address['country'] ?? $billTo['country'] ?? '',
                    'zip' => $salesInvoice->customerDetails->shipping_address['zip_code'] ?? $billTo['zip'] ?? '',
                ];
            }

            $creatorId = $salesInvoice->created_by ?? creatorId();
            $salesInvoiceSetting = SalesInvoiceSetup::getSettings($creatorId);

            return view('sales.print', [
                'invoice' => $salesInvoice,
                'billTo' => $billTo,
                'shipTo' => $shipTo,
                'same_as_billing' => $same_as_billing,
                'salesInvoiceSetting' => $salesInvoiceSetting,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function downloadPdf(SalesInvoice $salesInvoice)
    {
        if (Auth::user()->can('print-sales-invoices')) {
            if (!$this->checkInvoiceAccess($salesInvoice)) {
                return back()->with('error', __('Permission denied'));
            }

            $salesInvoice->load([
                'customer',
                'customerDetails',
                'items.product.unitRelation',
                'items.taxes',
                'warehouse',
                'paymentAllocations.payment.bankAccount',
            ]);

            $creatorId = $salesInvoice->created_by ?? creatorId();
            $salesInvoiceSetting = SalesInvoiceSetup::getSettings($creatorId);
            $filename = "Invoice_{$salesInvoice->invoice_number}.pdf";

            return Pdf::view('sales.print', [
                'invoice' => $salesInvoice,
                'salesInvoiceSetting' => $salesInvoiceSetting,
                'isServerPdf' => true,
            ])
                ->format('a4')
                ->margins(0, 0, 0, 0)
                ->download($filename);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function setup()
    {
        if (!Auth::user()->can('manage-sales-invoice-setup')) {
            return redirect()->route('dashboard')->with('error', __('Permission denied'));
        }

        $settings = SalesInvoiceSetup::getSettings(creatorId());
        return Inertia::render('Sales/SystemSetup/Index', [
            'settings' => $settings,
        ]);
    }

    public function updateSetup(Request $request)
    {
        if (!Auth::user()->can('manage-sales-invoice-setup')) {
            return redirect()->back()->with('error', __('Permission denied'));
        }

        $settings = $request->input('settings', $request->except(['_token', '_method']));
        SalesInvoiceSetup::setSettings($settings, creatorId());
        return redirect()->back()->with('success', __('Sales Invoice setup updated successfully.'));
    }
}
