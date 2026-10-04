<?php

namespace App\Http\Controllers;

use App\Events\AcceptSalesProposal;
use App\Events\CreateSalesProposal;
use App\Events\DestroySalesProposal;
use App\Events\RejectSalesProposal;
use App\Events\SentSalesProposal;
use App\Events\UpdateSalesProposal;
use App\Http\Requests\StoreSalesProposalRequest;
use App\Http\Requests\UpdateSalesProposalRequest;
use App\Models\ProposalSetting;
use App\Models\ProposalSubject;
use App\Models\SalesProposal;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\Warehouse;
use App\Services\CustomerService;
use App\Services\ProposalService;
use App\Services\WarehouseService;
use Automas\ProductService\Models\ProductServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;

class SalesProposalController extends Controller
{
    public function __construct(
        protected ProposalService $proposalService,
        protected CustomerService $customerService,
        protected WarehouseService $warehouseService
    ) {
    }

    /**
     * Display proposal list & board view.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->can('manage-sales-proposals')) {
            return back()->with('error', __('Permission denied'));
        }

        $query = $this->proposalService->getProposalsQuery($user);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('proposal_number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) === 2) {
                $query->whereBetween('proposal_date', [$dates[0], $dates[1]]);
            }
        }

        $stats = $this->proposalService->getAggregatedStats($query);

        $listQuery = clone $query;
        if ($request->filled('status')) {
            if ($request->status === 'expired') {
                $listQuery->where('due_date', '<', now())->whereNotIn('status', ['accepted', 'rejected']);
            } else {
                $listQuery->where('status', $request->status);
            }
        }

        $allowedSorts = ['proposal_number', 'reference', 'subject', 'proposal_date', 'due_date', 'subtotal', 'tax_amount', 'total_amount', 'status', 'created_at'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'created_at';
        $direction = $request->input('direction', 'desc');

        $proposals = $listQuery->orderBy($sort, $direction)->paginate($request->input('per_page', 10));
        $proposals->through(function ($item) {
            $item->public_print_url = route('sales-proposals.public-print', Crypt::encryptString($item->id));

            return $item;
        });

        $customers = $this->customerService->getCustomers();

        $boardData = null;
        if ($request->input('view', 'board') !== 'list') {
            $boardData = $this->proposalService->getBoardData($query);
            if (is_array($boardData)) {
                foreach ($boardData as $status => $statusItems) {
                    if (is_iterable($statusItems)) {
                        foreach ($statusItems as $item) {
                            $item->public_print_url = route('sales-proposals.public-print', Crypt::encryptString($item->id));
                        }
                    }
                }
            }
        }

        $users = User::where('created_by', creatorId())
            ->emp()
            ->select('id', 'name', 'email')
            ->get();
        $userGroups = UserGroup::where('created_by', creatorId())
            ->active()
            ->select('id', 'name')
            ->get();

        return Inertia::render('SalesProposals/Index', [
            'proposals' => $proposals,
            'customers' => $customers,
            'users' => $users,
            'userGroups' => $userGroups,
            'stats' => $stats,
            'boardData' => $boardData,
            'filters' => $request->only(['customer_id', 'status', 'search', 'date_range']),
        ]);
    }

    /**
     * Show create proposal form.
     */
    public function create()
    {
        if (!Auth::user()->can('create-sales-proposals')) {
            return back()->with('error', __('Permission denied'));
        }

        $customers = $this->customerService->getCustomers();
        $warehouses = $this->warehouseService->getActiveWarehouses();
        $defaultPages = $this->proposalService->getActiveDefaultPages(Auth::id());
        $proposalSetting = ProposalSetting::getSettings(creatorId());
        $subjects = ProposalSubject::where('created_by', creatorId())->orderBy('name')->get(['id', 'name']);

        return Inertia::render('SalesProposals/Create', [
            'customers' => $customers,
            'warehouses' => $warehouses,
            'defaultPages' => $defaultPages,
            'defaultTerms' => $proposalSetting['default_terms'] ?? null,
            'proposalSetting' => $proposalSetting,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Store newly created proposal.
     */
    public function store(StoreSalesProposalRequest $request)
    {
        if (!Auth::user()->can('create-sales-proposals')) {
            return redirect()->route('sales-proposals.index')->with('error', __('Permission denied'));
        }

        $proposal = $this->proposalService->createProposal($request);

        try {
            CreateSalesProposal::dispatch($request, $proposal);
        } catch (\Throwable $th) {
            // Silently catch event dispatcher exceptions
        }

        return redirect()->route('sales-proposals.index')->with('success', __('The sales proposal has been created successfully.'));
    }

    /**
     * Show single proposal view.
     */
    public function show(SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('view-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return redirect()->route('sales-proposals.index')->with('error', __('Permission denied'));
        }

        $salesProposal->load($this->proposalService->getProposalRelations());

        $customers = $this->customerService->getCustomers();
        $users = User::where('created_by', creatorId())
            ->emp()
            ->select('id', 'name', 'email')
            ->get();
        $userGroups = UserGroup::where('created_by', creatorId())
            ->active()
            ->select('id', 'name')
            ->get();

        return Inertia::render('SalesProposals/View', [
            'proposal' => $salesProposal,
            'customers' => $customers,
            'users' => $users,
            'userGroups' => $userGroups,
        ]);
    }

    /**
     * Show edit proposal form.
     */
    public function edit(SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('edit-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return redirect()->route('sales-proposals.index')->with('error', __('Permission denied'));
        }

        if ($salesProposal->converted_to_invoice) {
            return redirect()->route('sales-proposals.index')->with('error', __('Cannot update converted proposal.'));
        }

        if ($salesProposal->status === 'accepted' || $salesProposal->converted_to_invoice) {
            return redirect()->route('sales-proposals.index')->with('error', __('Cannot edit an accepted or converted proposal.'));
        }

        $salesProposal->load($this->proposalService->getProposalRelations());

        $customers = $this->customerService->getCustomers();
        $warehouses = $this->warehouseService->getActiveWarehouses();
        $proposalSetting = ProposalSetting::getSettings(creatorId());
        $defaultPages = $this->proposalService->getActiveDefaultPages(Auth::id());
        $products = $this->warehouseService->getWarehouseProducts($salesProposal->warehouse_id);
        $subjects = ProposalSubject::where('created_by', creatorId())->orderBy('name')->get(['id', 'name']);

        return Inertia::render('SalesProposals/Edit', [
            'proposal' => $salesProposal,
            'customers' => $customers,
            'warehouses' => $warehouses,
            'products' => $products,
            'defaultPages' => $defaultPages,
            'defaultTerms' => $proposalSetting['default_terms'] ?? null,
            'proposalSetting' => $proposalSetting,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Update specified proposal.
     */
    public function update(UpdateSalesProposalRequest $request, SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('edit-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return redirect()->route('sales-proposals.index')->with('error', __('Permission denied'));
        }

        if ($salesProposal->status === 'accepted' || $salesProposal->converted_to_invoice) {
            return redirect()->route('sales-proposals.index')->with('error', __('Cannot update an accepted or converted proposal.'));
        }

        $proposal = $this->proposalService->updateProposal($salesProposal, $request);

        try {
            UpdateSalesProposal::dispatch($request, $proposal);
        } catch (\Throwable $th) {
            // Silently catch event dispatcher exceptions
        }

        return redirect()->route('sales-proposals.index')->with('success', __('The sales proposal has been updated successfully.'));
    }

    /**
     * Remove specified proposal.
     */
    public function destroy(SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('delete-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return redirect()->route('sales-proposals.index')->with('error', __('Permission denied'));
        }

        if ($salesProposal->converted_to_invoice) {
            return back()->withErrors(['error' => __('Cannot delete converted proposal.')]);
        }

        DestroySalesProposal::dispatch($salesProposal);
        $salesProposal->delete();

        return redirect()->route('sales-proposals.index')->with('success', __('The sales proposal has been deleted.'));
    }

    /**
     * Duplicate existing proposal record.
     */
    public function duplicate(SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('create-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return back()->with('error', __('Permission denied'));
        }

        $newProposal = $this->proposalService->duplicateProposal($salesProposal);

        DuplicateSalesProposal::dispatch($newProposal);

        return redirect()->route('sales-proposals.edit', $newProposal->id)->with('success', __('Proposal duplicated successfully.'));
    }

    /**
     * Mark proposal as sent and notify customer.
     */
    public function sent(SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('sent-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return back()->with('error', __('Permission denied'));
        }

        if (!in_array($salesProposal->status, ['draft', 'sent'])) {
            return back()->with('error', __('Only draft or sent proposals can be sent.'));
        }

        SentSalesProposal::dispatch($salesProposal);

        $notification = $this->proposalService->notifyCustomerOnStatusChange($salesProposal, 'Proposal Sent');

        if (isset($notification) && $notification['is_success'] === false && !empty($notification['error'])) {
            $salesProposal->update(['status' => 'sent']);

            return back()
                ->with('success', __('Proposal sent successfully.'))
                ->with('error', $notification['error']);
        }

        $salesProposal->update(['status' => 'sent']);

        return back()->with('success', __('Proposal sent successfully.'));
    }

    /**
     * Mark proposal as accepted.
     */
    public function accept(SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('accept-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return back()->with('error', __('Permission denied'));
        }

        if ($salesProposal->status !== 'sent') {
            return back()->with('error', __('Only sent proposals can be accepted.'));
        }

        AcceptSalesProposal::dispatch($salesProposal);

        $notification = $this->proposalService->notifyCustomerOnStatusChange($salesProposal, 'Proposal Approved', 'Accepted');

        if (isset($notification) && $notification['is_success'] === false && !empty($notification['error'])) {
            $salesProposal->update(['status' => 'accepted']);

            return back()
                ->with('success', __('Proposal accepted successfully.'))
                ->with('error', $notification['error']);
        }

        $salesProposal->update(['status' => 'accepted']);

        return back()->with('success', __('Proposal accepted successfully.'));
    }

    /**
     * Mark proposal as rejected.
     */
    public function reject(SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('reject-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return back()->with('error', __('Permission denied'));
        }

        if ($salesProposal->status !== 'sent') {
            return back()->with('error', __('Only sent proposals can be rejected.'));
        }

        RejectSalesProposal::dispatch($salesProposal);

        $notification = $this->proposalService->notifyCustomerOnStatusChange($salesProposal, 'Proposal Approved', 'Rejected');

        if (isset($notification) && $notification['is_success'] === false && !empty($notification['error'])) {
            $salesProposal->update(['status' => 'rejected']);

            return back()
                ->with('success', __('Proposal rejected successfully.'))
                ->with('error', $notification['error']);
        }

        $salesProposal->update(['status' => 'rejected']);

        return back()->with('success', __('Proposal rejected successfully.'));
    }

    /**
     * Convert proposal to sales order.
     */
    public function convertToSalesOrder(Request $request, SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('convert-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return back()->with('error', __('Permission denied'));
        }

        if ($salesProposal->status !== 'accepted') {
            return back()->with('error', __('Only accepted proposals can be converted to sales order.'));
        }

        if ($salesProposal->converted_to_sales_order || $salesProposal->sales_order_id) {
            return back()->with('error', __('Proposal has already been converted to sales order.'));
        }

        try {
            $customItems = $request->has('items') ? $request->input('items') : null;
            $warehouseId = $request->input('warehouse_id') ? (int) $request->input('warehouse_id') : null;

            $salesOrder = $this->proposalService->convertProposalToSalesOrder($salesProposal, $customItems, $warehouseId, $request->all());

            if (\Route::has('salesorder.orders.show')) {
                return redirect()->route('salesorder.orders.show', $salesOrder->id)->with('success', __('Proposal successfully converted to sales order.'));
            }

            return back()->with('success', __('Proposal successfully converted to sales order.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Convert proposal to invoice (products only).
     */
    public function convertToInvoice(Request $request, SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('convert-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return back()->with('error', __('Permission denied'));
        }

        if ($salesProposal->status !== 'accepted') {
            return back()->with('error', __('Only accepted proposals can be converted to invoice.'));
        }

        if ($salesProposal->converted_to_invoice) {
            return back()->with('error', __('Proposal has already been converted to invoice.'));
        }

        try {
            $customItems = $request->has('items') ? $request->input('items') : null;
            $warehouseId = $request->input('warehouse_id') ? (int) $request->input('warehouse_id') : null;

            $invoice = $this->proposalService->convertProposalToInvoice($salesProposal, $customItems, $warehouseId);

            return redirect()->route('sales-invoices.show', $invoice->id)->with('success', __('Proposal successfully converted to invoice.'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get proposal conversion details (items with product relations and warehouse products).
     */
    public function convertDetails(SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('convert-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return response()->json(['error' => __('Permission denied')], 403);
        }

        $salesProposal->load(['customer', 'warehouse', 'items.product.unitRelation', 'items.taxes']);

        $warehouseId = $salesProposal->warehouse_id;
        $products = $this->warehouseService->getWarehouseProducts($warehouseId);

        return response()->json([
            'proposal' => $salesProposal,
            'products' => $products,
        ]);
    }

    /**
     * Print proposal view.
     */
    public function print(SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('print-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return back()->with('error', __('Permission denied'));
        }

        $salesProposal->load($this->proposalService->getProposalRelations());
        $authorId = $salesProposal->creator_id ?? Auth::id();
        $defaultPages = $this->proposalService->getActiveDefaultPages($authorId);
        $proposalSetting = ProposalSetting::getSettings(creatorId());

        return inertia('SalesProposals/Print', [
            'proposal' => $salesProposal,
            'defaultPages' => $defaultPages,
            'proposalSetting' => $proposalSetting,
        ]);
    }

    /**
     * Public print proposal view via encrypted token.
     */
    public function publicPrint(string $token)
    {
        $id = null;
        try {
            $id = Crypt::decryptString($token);
        } catch (\Throwable $e) {
            try {
                $id = Crypt::decryptString(urldecode($token));
            } catch (\Throwable $e2) {
                if (is_numeric($token)) {
                    $id = $token;
                }
            }
        }

        if (!$id) {
            abort(404, 'Invalid or expired link.');
        }

        $salesProposal = SalesProposal::findOrFail($id);

        $salesProposal->load($this->proposalService->getProposalRelations());
        $authorId = $salesProposal->creator_id ?? $salesProposal->created_by;
        $defaultPages = $this->proposalService->getActiveDefaultPages($authorId, $salesProposal->created_by);
        $proposalSetting = array_merge(
            ProposalSetting::getSettings($salesProposal->created_by) ?? [],
            array_filter(ProposalSetting::getSettings($salesProposal->creator_id) ?? [])
        );

        return inertia('SalesProposals/Print', [
            'proposal' => $salesProposal,
            'defaultPages' => $defaultPages,
            'proposalSetting' => $proposalSetting,
        ]);
    }

    /**
     * Download PDF version of proposal.
     */
    public function downloadPdf(SalesProposal $salesProposal)
    {
        if (!Auth::user()->can('print-sales-proposals') || !$this->proposalService->hasProposalAccess($salesProposal)) {
            return back()->with('error', __('Permission denied'));
        }

        $salesProposal->load($this->proposalService->getProposalRelations());
        $authorId = $salesProposal->creator_id ?? Auth::id();
        $defaultPages = $this->proposalService->getActiveDefaultPages($authorId);
        $proposalSetting = ProposalSetting::getSettings(creatorId());

        $fileName = "Proposal For_{$salesProposal->subject}_({$salesProposal->proposal_number}).pdf";

        return Pdf::view('sales-proposals.print', [
            'proposal' => $salesProposal,
            'defaultPages' => $defaultPages,
            'proposalSetting' => $proposalSetting,
            'isServerPdf' => true,
        ])
            ->format('a4')
            ->margins(0, 0, 0, 0)
            ->download($fileName);
    }

    /**
     * AJAX endpoint for warehouse products.
     */
    public function getWarehouseProducts(Request $request)
    {
        if (!Auth::user()->can('create-sales-proposals') && !Auth::user()->can('edit-sales-proposals') && !Auth::user()->can('convert-sales-proposals') && !Auth::user()->can('view-sales-proposals')) {
            return response()->json([], 403);
        }

        $warehouseId = $request->warehouse_id ? (int) $request->warehouse_id : null;
        $products = $this->warehouseService->getWarehouseProducts($warehouseId);

        return response()->json($products);
    }

    /**
     * AJAX endpoint for services.
     */
    public function getServices(Request $request)
    {
        if (!Auth::user()->can('create-sales-proposals') && !Auth::user()->can('edit-sales-proposals') && !Auth::user()->can('convert-sales-proposals') && !Auth::user()->can('view-sales-proposals')) {
            return response()->json([], 403);
        }

        $services = ProductServiceItem::with('unitRelation:id,unit_name')
            ->select('id', 'name', 'sku', 'description', 'long_description', 'sale_price', 'tax_ids', 'unit', 'type')
            ->where('is_active', true)
            ->where('type', 'service')
            ->where(function ($q) {
                $q->where('created_by', creatorId())
                    ->orWhere('creator_id', creatorId());
            })
            ->get()
            ->map(function ($service) {
                $unitName = $service->unitRelation?->unit_name ?? (is_numeric($service->unit) ? '' : ($service->unit ?? ''));

                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'long_description' => $service->long_description,
                    'sku' => $service->sku,
                    'sale_price' => $service->sale_price,
                    'unit' => $service->unit,
                    'unit_name' => $unitName,
                    'type' => $service->type,
                    'taxes' => $service->taxes->map(fn($tax) => [
                        'id' => $tax->id,
                        'tax_name' => $tax->tax_name,
                        'rate' => $tax->rate,
                    ]),
                ];
            });

        return response()->json($services);
    }
}
