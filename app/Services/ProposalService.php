<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\ProposalDefaultPage;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesInvoiceItemTax;
use App\Models\SalesProposal;
use App\Models\SalesProposalContent;
use App\Models\SalesProposalItem;
use App\Models\SalesProposalItemTax;
use App\Models\User;
use App\Models\UserGroup;
use Automas\ProductService\Models\ProductServiceItem;
use Automas\SalesOrder\Models\SalesOrder;
use Automas\SalesOrder\Models\SalesOrderItem;
use Automas\SalesOrder\Models\SalesOrderItemTax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ProposalService
{
    public function __construct(
        protected WarehouseService $warehouseService
    ) {
    }

    public function getProposalRelations(): array
    {
        $relations = ['customer', 'items.product.unitRelation', 'items.taxes', 'warehouse'];

        if (Schema::hasTable('sales_proposal_contents')) {
            $relations[] = 'contents';
        }

        return $relations;
    }

    public function hasProposalAccess(SalesProposal $proposal): bool
    {
        if ($proposal->created_by != creatorId()) {
            return false;
        }

        $user = Auth::user();

        if ($user->type === 'superadmin' || $user->type === 'company' || $user->can('manage-any-sales-proposals')) {
            return true;
        }

        if ($user->can('manage-own-sales-proposals')) {
            if ($proposal->creator_id != $user->id && $proposal->customer_id != $user->id) {
                return false;
            }

            if ($proposal->creator_id != $user->id && $user->type === 'client' && $proposal->status === 'draft') {
                return false;
            }

            return true;
        }

        return false;
    }

    public function getActiveDefaultPages(int $authorId, ?int $creatorId = null)
    {
        $creatorId = $creatorId ?? Auth::check() ? creatorId() : $authorId;
        return ProposalDefaultPage::where('created_by', $creatorId)
            ->where(function ($query) use ($authorId, $creatorId) {
                $query->where('creator_id', $authorId)
                    ->orWhere('creator_id', $creatorId);
            })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'title', 'content', 'page_type', 'background_image', 'sort_order', 'creator_id', 'created_by']);
    }


    public function notifyCustomerOnStatusChange(SalesProposal $proposal, string $templateName, ?string $statusLabel = null): ?array
    {
        $recipient = null;

        if ($templateName === 'Proposal Sent') {
            $recipient = $proposal->customer?->email ?: $proposal->customer_email;
        } elseif ($templateName === 'Proposal Approved') {
            $author = User::find($proposal->creator_id);
            $recipient = company_setting('company_email', $proposal->created_by) ?: $author?->email;
        }

        if (empty($recipient) || company_setting($templateName) !== 'on') {
            return null;
        }

        $data = [
            'proposal_number' => $proposal->proposal_number ?? null,
            'sales_customer_name' => $proposal->customer?->name ?: $proposal->customer_name ?: 'Customer',
            'total_amount' => $proposal->total_amount ?? null,
            'discount_amount' => $proposal->discount_amount ?? null,
        ];

        if ($statusLabel) {
            $data['status'] = $statusLabel;
        }

        return EmailTemplate::sendEmailTemplate($templateName, [$recipient], $data);
    }

    public function getProposalsQuery($user)
    {
        return SalesProposal::with(['customer', 'author', 'items'])
            ->where(function ($query) use ($user) {
                if ($user->type === 'superadmin' || $user->type === 'company' || $user->can('manage-any-sales-proposals')) {
                    $query->where('created_by', creatorId());
                } elseif ($user->can('manage-own-sales-proposals')) {
                    $query->where('created_by', creatorId())
                        ->where(function ($q) use ($user) {
                            $q->where('creator_id', $user->id)
                                ->orWhere('customer_id', $user->id);
                        });
                    if ($user->type === 'client') {
                        $query->where('status', '!=', 'draft');
                    }
                } else {
                    $query->whereRaw('1 = 0');
                }
            });
    }

    public function getAggregatedStats($baseQuery): array
    {
        $stats = (clone $baseQuery)->withoutEagerLoads()
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(total_amount) as total_value,
                SUM(CASE WHEN due_date < ? AND status NOT IN ("accepted", "rejected") THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN status = "accepted" AND converted_to_invoice IS NULL THEN 1 ELSE 0 END) as accepted_active_count,
                SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft_count,
                SUM(CASE WHEN status = "draft" THEN total_amount ELSE 0 END) as draft_value,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN status = "sent" THEN total_amount ELSE 0 END) as sent_value,
                SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted_count,
                SUM(CASE WHEN status = "accepted" THEN total_amount ELSE 0 END) as accepted_value,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN status = "rejected" THEN total_amount ELSE 0 END) as rejected_value
            ', [now()])
            ->first();

        return [
            'total_count' => (int) ($stats->total_count ?? 0),
            'total_value' => (float) ($stats->total_value ?? 0),
            'overdue_count' => (int) ($stats->overdue_count ?? 0),
            'accepted_active_count' => (int) ($stats->accepted_active_count ?? 0),
            'draft_count' => (int) ($stats->draft_count ?? 0),
            'draft_value' => (float) ($stats->draft_value ?? 0),
            'sent_count' => (int) ($stats->sent_count ?? 0),
            'sent_value' => (float) ($stats->sent_value ?? 0),
            'accepted_count' => (int) ($stats->accepted_count ?? 0),
            'accepted_value' => (float) ($stats->accepted_value ?? 0),
            'rejected_count' => (int) ($stats->rejected_count ?? 0),
            'rejected_value' => (float) ($stats->rejected_value ?? 0),
        ];
    }

    public function getBoardData($baseQuery): array
    {
        $boardData = [];
        foreach (['draft', 'sent', 'accepted', 'rejected'] as $status) {
            $query = (clone $baseQuery)->where('status', $status);
            if ($status === 'accepted') {
                $query->whereNull('converted_to_invoice');
            }
            $boardData[$status] = $query->orderBy('created_at', 'desc')->limit(8)->get();
        }
        return $boardData;
    }



    public function hasRecurringBillingItems(?array $items): bool
    {
        if (empty($items)) {
            return false;
        }

        foreach ($items as $item) {
            if (($item['section'] ?? '') === 'mrc' && !empty($item['product_id'])) {
                return true;
            }
        }

        return false;
    }

    public function calculateProposalTotals(?array $items, bool $isTaxEnabled = true, array $options = []): array
    {
        $subtotal = 0.0;
        $tax = 0.0;
        $itemDiscountTotal = 0.0;

        $otcSubtotal = 0.0;
        $mrcSubtotal = 0.0;

        if (!empty($items)) {
            foreach ($items as $item) {
                if (empty($item['product_id']) || (int) $item['product_id'] <= 0) {
                    continue;
                }

                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $price = max(0, (float) ($item['unit_price'] ?? 0));
                $lineTotal = $qty * $price;

                $discType = $item['discount_type'] ?? 'percentage';
                if ($discType === 'fixed') {
                    $discAmount = min($lineTotal, max(0, (float) ($item['discount_amount'] ?? 0)));
                } else {
                    $discRate = max(0, min(100, (float) ($item['discount_percentage'] ?? 0)));
                    $discAmount = ($lineTotal * $discRate) / 100;
                }

                $taxRate = 0.0;
                if ($isTaxEnabled) {
                    $taxRate = (float) ($item['tax_percentage'] ?? 0);
                    if (!empty($item['taxes']) && is_array($item['taxes'])) {
                        $taxRate = array_reduce($item['taxes'], fn($sum, $t) => $sum + (float) ($t['tax_rate'] ?? $t['rate'] ?? 0), 0.0);
                    }
                }

                $netTotal = $lineTotal - $discAmount;
                $taxAmount = ($netTotal * $taxRate) / 100;

                $subtotal += $lineTotal;
                $itemDiscountTotal += $discAmount;
                $tax += $taxAmount;

                $section = strtolower($item['section'] ?? 'otc');
                if ($section === 'mrc') {
                    $mrcSubtotal += $lineTotal;
                } else {
                    $otcSubtotal += $lineTotal;
                }
            }
        }

        // Section level discount calculations if provided
        $otcDiscount = 0.0;
        $otcDiscType = $options['otc_discount_type'] ?? 'percentage';
        $otcDiscVal = max(0, (float) ($options['otc_discount_value'] ?? 0));
        if ($otcDiscVal > 0) {
            $otcDiscount = $otcDiscType === 'percentage'
                ? ($otcSubtotal * min(100, $otcDiscVal)) / 100
                : min($otcSubtotal, $otcDiscVal);
        }

        $mrcDiscount = 0.0;
        $mrcDiscType = $options['mrc_discount_type'] ?? 'percentage';
        $mrcDiscVal = max(0, (float) ($options['mrc_discount_value'] ?? 0));
        if ($mrcDiscVal > 0) {
            $mrcDiscount = $mrcDiscType === 'percentage'
                ? ($mrcSubtotal * min(100, $mrcDiscVal)) / 100
                : min($mrcSubtotal, $mrcDiscVal);
        }

        $sectionDiscountTotal = $otcDiscount + $mrcDiscount;
        $finalDiscount = $sectionDiscountTotal > 0 ? $sectionDiscountTotal : $itemDiscountTotal;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($tax, 2),
            'discount_amount' => round($finalDiscount, 2),
            'total_amount' => round(max(0, $subtotal + $tax - $finalDiscount), 2)
        ];
    }

    public function createProposal(Request $request): SalesProposal
    {
        return DB::transaction(function () use ($request) {
            $isTaxEnabled = filter_var($request->input('is_tax_enabled', true), FILTER_VALIDATE_BOOLEAN);
            $totals = $this->calculateProposalTotals($request->items, $isTaxEnabled, $request->all());

            $hasRecurring = $this->hasRecurringBillingItems($request->items);
            $isRecurring = $hasRecurring ? 1 : 0;
            $isPrepaid = ($hasRecurring && filter_var($request->input('is_prepaid', false), FILTER_VALIDATE_BOOLEAN)) ? 1 : 0;

            $proposal = new SalesProposal();
            $proposal->proposal_number = SalesProposal::generateProposalNumber($request->invoice_date ?? $request->proposal_date);
            $proposal->reference = $request->reference;
            $proposal->subject = $request->subject;
            $proposal->proposal_date = $request->invoice_date ?? $request->proposal_date;
            $proposal->due_date = $request->due_date ?? $proposal->proposal_date;
            $proposal->status = 'draft';

            $mode = $request->input('customer_mode', 'existing');
            if ($mode === 'new') {
                $proposal->customer_id = null;
                $proposal->customer_name = $request->customer_name;
                $proposal->customer_email = $request->customer_email;
                $proposal->customer_phone = $request->customer_phone;
                $proposal->customer_address = $request->customer_address;
            } else {
                $proposal->customer_id = $request->customer_id;
                $proposal->customer_name = null;
                $proposal->customer_email = null;
                $proposal->customer_phone = null;
                $proposal->customer_address = null;
            }

            $proposal->warehouse_id = $request->type === 'product' ? $request->warehouse_id : null;
            $proposal->type = $request->type ?? 'product';
            $proposal->is_recurring = $isRecurring;
            $proposal->is_prepaid = $isPrepaid;
            $proposal->is_tax_enabled = $isTaxEnabled ? 1 : 0;
            $proposal->otc_discount_type = $request->input('otc_discount_type', 'percentage');
            $proposal->otc_discount_value = (float) $request->input('otc_discount_value', 0);
            $proposal->mrc_discount_type = $request->input('mrc_discount_type', 'percentage');
            $proposal->mrc_discount_value = (float) $request->input('mrc_discount_value', 0);
            $proposal->payment_terms = $request->payment_terms;
            $proposal->notes = $request->notes;
            $proposal->subtotal = $totals['subtotal'];
            $proposal->tax_amount = $totals['tax_amount'];
            $proposal->discount_amount = $totals['discount_amount'];
            $proposal->total_amount = $totals['total_amount'];
            $proposal->creator_id = Auth::id();
            $proposal->created_by = creatorId();
            $proposal->save();

            $this->saveProposalItems($proposal->id, $request->items, $isTaxEnabled);
            $this->saveProposalPageContents($proposal->id, $request->proposal_content);

            return $proposal;
        });
    }

    public function updateProposal(SalesProposal $salesProposal, Request $request): SalesProposal
    {
        return DB::transaction(function () use ($salesProposal, $request) {
            $isTaxEnabled = filter_var($request->input('is_tax_enabled', true), FILTER_VALIDATE_BOOLEAN);
            $totals = $this->calculateProposalTotals($request->items, $isTaxEnabled, $request->all());

            $hasRecurring = $this->hasRecurringBillingItems($request->items);
            $isRecurring = $hasRecurring ? 1 : 0;
            $isPrepaid = ($hasRecurring && filter_var($request->input('is_prepaid', false), FILTER_VALIDATE_BOOLEAN)) ? 1 : 0;

            $salesProposal->subject = $request->subject;
            $salesProposal->reference = $request->reference;
            $salesProposal->proposal_date = $request->invoice_date;
            $salesProposal->due_date = $request->due_date;

            $mode = $request->input('customer_mode', 'existing');
            if ($mode === 'new') {
                $salesProposal->customer_id = null;
                $salesProposal->customer_name = $request->customer_name;
                $salesProposal->customer_email = $request->customer_email;
                $salesProposal->customer_phone = $request->customer_phone;
                $salesProposal->customer_address = $request->customer_address;
            } else {
                $salesProposal->customer_id = $request->customer_id;
                $salesProposal->customer_name = null;
                $salesProposal->customer_email = null;
                $salesProposal->customer_phone = null;
                $salesProposal->customer_address = null;
            }

            $salesProposal->warehouse_id = $salesProposal->type === 'product' ? $request->warehouse_id : null;
            $salesProposal->is_recurring = $isRecurring;
            $salesProposal->is_prepaid = $isPrepaid;
            $salesProposal->is_tax_enabled = $isTaxEnabled ? 1 : 0;
            $salesProposal->otc_discount_type = $request->input('otc_discount_type', 'percentage');
            $salesProposal->otc_discount_value = (float) $request->input('otc_discount_value', 0);
            $salesProposal->mrc_discount_type = $request->input('mrc_discount_type', 'percentage');
            $salesProposal->mrc_discount_value = (float) $request->input('mrc_discount_value', 0);
            $salesProposal->payment_terms = $request->payment_terms;
            $salesProposal->notes = $request->notes;
            $salesProposal->subtotal = $totals['subtotal'];
            $salesProposal->tax_amount = $totals['tax_amount'];
            $salesProposal->discount_amount = $totals['discount_amount'];
            $salesProposal->total_amount = $totals['total_amount'];
            $salesProposal->save();

            $salesProposal->items()->delete();
            $this->saveProposalItems($salesProposal->id, $request->items, $isTaxEnabled);
            $this->saveProposalPageContents($salesProposal->id, $request->proposal_content);

            return $salesProposal;
        });
    }

    public function convertProposalToSalesOrder(SalesProposal $salesProposal, ?array $customItems = null, ?int $warehouseId = null, array $options = []): SalesOrder
    {
        return DB::transaction(function () use ($salesProposal, $customItems, $warehouseId, $options) {
            $targetWarehouseId = $warehouseId ?: $salesProposal->warehouse_id;

            // Handle Customer resolution (existing or new)
            $customerId = $salesProposal->customer_id;
            $billingAddress = $salesProposal->customer_address;
            $shippingAddress = $salesProposal->customer_address;

            if (!empty($options['customer_type'])) {
                if ($options['customer_type'] === 'new' && !empty($options['customer_name'])) {
                    $customerService = app(CustomerService::class);
                    $data = [
                        'customer_name' => $options['customer_name'],
                        'company_name' => $options['customer_name'],
                        'contact_person_name' => $options['customer_name'],
                        'customer_email' => $options['customer_email'] ?? $salesProposal->customer_email,
                        'contact_person_email' => $options['customer_email'] ?? $salesProposal->customer_email,
                        'customer_phone' => $options['customer_phone'] ?? $salesProposal->customer_phone,
                        'contact_person_mobile' => $options['customer_phone'] ?? $salesProposal->customer_phone,
                        'tax_number' => $options['tax_number'] ?? null,
                        'payment_terms' => $options['payment_terms'] ?? null,
                        'billing_name' => $options['billing_name'] ?? $options['customer_name'],
                        'billing_address' => $options['billing_address_line_1'] ?? $options['billing_address'] ?? $options['customer_address'] ?? $salesProposal->customer_address,
                        'billing_address_line_2' => $options['billing_address_line_2'] ?? null,
                        'billing_city' => $options['billing_city'] ?? $options['customer_city'] ?? null,
                        'billing_state' => $options['billing_state'] ?? $options['customer_state'] ?? null,
                        'billing_zip_code' => $options['billing_zip_code'] ?? $options['billing_postal_code'] ?? $options['customer_zip_code'] ?? null,
                        'billing_country' => $options['billing_country'] ?? $options['customer_country'] ?? null,
                        'shipping_name' => $options['shipping_name'] ?? $options['customer_name'],
                        'shipping_address' => $options['shipping_address_line_1'] ?? $options['shipping_address'] ?? $options['customer_address'] ?? $salesProposal->customer_address,
                        'shipping_address_line_2' => $options['shipping_address_line_2'] ?? null,
                        'shipping_city' => $options['shipping_city'] ?? $options['customer_city'] ?? null,
                        'shipping_state' => $options['shipping_state'] ?? $options['customer_state'] ?? null,
                        'shipping_zip_code' => $options['shipping_zip_code'] ?? $options['shipping_postal_code'] ?? $options['customer_zip_code'] ?? null,
                        'shipping_country' => $options['shipping_country'] ?? $options['customer_country'] ?? null,
                    ];

                    $newCustomer = $customerService->createCustomer($data);
                    $customerId = $newCustomer->user_id ?? $newCustomer->id;
                    $billingAddress = $newCustomer->billing_address ?? $billingAddress;
                    $shippingAddress = $newCustomer->shipping_address ?? $shippingAddress;
                } elseif ($options['customer_type'] === 'existing' && !empty($options['customer_id'])) {
                    $customerId = (int) $options['customer_id'];
                    $custUser = User::find($customerId);
                    if ($custUser) {
                        $billingAddress = $custUser->address ?? $billingAddress;
                        $shippingAddress = $custUser->address ?? $shippingAddress;
                    }
                }
            }

            $productItems = [];
            if ($customItems !== null) {
                $productItems = array_values(array_filter($customItems, function ($item) {
                    return !empty($item['product_id']) && (int) $item['product_id'] > 0;
                }));
            } else {
                $dbItems = $salesProposal->items()->with(['taxes', 'product'])->get();
                foreach ($dbItems as $item) {
                    $type = $item->product_type ?: ($item->product?->type ?? 'product');
                    $productItems[] = [
                        'product_id' => $item->product_id,
                        'product_type' => $type,
                        'description' => $item->description,
                        'quantity' => (int) ($item->quantity ?? 1),
                        'unit_price' => (float) ($item->unit_price ?? 0),
                        'discount_type' => $item->discount_type ?? 'percentage',
                        'discount_percentage' => (float) ($item->discount_percentage ?? 0),
                        'discount_amount' => (float) ($item->discount_amount ?? 0),
                        'tax_percentage' => (float) ($item->tax_percentage ?? 0),
                        'tax_amount' => (float) ($item->tax_amount ?? 0),
                        'total_amount' => (float) ($item->total_amount ?? 0),
                        'taxes' => $item->taxes ? $item->taxes->map(fn($t) => [
                            'tax_name' => $t->tax_name,
                            'tax_rate' => $t->tax_rate,
                        ])->toArray() : [],
                    ];
                }
            }

            if (empty($productItems)) {
                throw new \Exception(__('Proposal does not have any items to convert to sales order.'));
            }

            // Validate stock only for physical product items (services have unlimited quantity)
            foreach ($productItems as $item) {
                $productId = (int) $item['product_id'];
                $qty = (int) ($item['quantity'] ?? 1);
                $product = ProductServiceItem::with('warehouseStocks')->find($productId);

                $isService = ($item['product_type'] ?? '') === 'service' || ($product && $product->type === 'service');

                if ($product && !$isService) {
                    $availableStock = $targetWarehouseId
                        ? ($product->warehouseStocks->where('warehouse_id', $targetWarehouseId)->first()?->quantity ?? 0)
                        : $product->warehouseStocks->sum('quantity');

                    if ($qty > $availableStock) {
                        throw new \Exception(__("Requested quantity (:qty) exceeds available stock (:stock) for ':name'.", [
                            'qty' => $qty,
                            'stock' => $availableStock,
                            'name' => $product->name,
                        ]));
                    }
                }
            }

            $subtotal = 0.0;
            $taxAmount = 0.0;
            $discountAmount = 0.0;

            foreach ($productItems as &$pItem) {
                $qty = max(1, (int) ($pItem['quantity'] ?? 1));
                $price = max(0, (float) ($pItem['unit_price'] ?? 0));
                $lineTotal = $qty * $price;

                $discType = $pItem['discount_type'] ?? 'percentage';
                if ($discType === 'fixed') {
                    $dAmount = min($lineTotal, max(0, (float) ($pItem['discount_amount'] ?? 0)));
                    $dPct = $lineTotal > 0 ? ($dAmount / $lineTotal) * 100 : 0;
                } else {
                    $dPct = max(0, min(100, (float) ($pItem['discount_percentage'] ?? 0)));
                    $dAmount = ($lineTotal * $dPct) / 100;
                }

                $taxRate = (float) ($pItem['tax_percentage'] ?? 0);
                if (!empty($pItem['taxes']) && is_array($pItem['taxes'])) {
                    $taxRate = array_reduce($pItem['taxes'], fn($sum, $t) => $sum + (float) ($t['tax_rate'] ?? $t['rate'] ?? 0), 0.0);
                }

                $afterDisc = max(0, $lineTotal - $dAmount);
                $tAmount = ($afterDisc * $taxRate) / 100;
                $totAmount = max(0, $afterDisc + $tAmount);

                $pItem['quantity'] = $qty;
                $pItem['unit_price'] = $price;
                $pItem['discount_type'] = $discType;
                $pItem['discount_percentage'] = round($dPct, 4);
                $pItem['discount_amount'] = round($dAmount, 2);
                $pItem['tax_percentage'] = round($taxRate, 4);
                $pItem['tax_amount'] = round($tAmount, 2);
                $pItem['total_amount'] = round($totAmount, 2);

                $subtotal += $lineTotal;
                $discountAmount += $dAmount;
                $taxAmount += $tAmount;
            }
            unset($pItem);

            $totalAmount = max(0, $subtotal + $taxAmount - $discountAmount);

            // Assignment handling
            $assignedGroupId = null;
            $assignmentStatus = SalesOrder::ASSIGNMENT_UNASSIGNED;
            if (!empty($options['assigned_group_id'])) {
                $assignedGroupId = (int) $options['assigned_group_id'];
                $assignmentStatus = SalesOrder::ASSIGNMENT_GROUP_ASSIGNED;
            }

            $salesOrder = SalesOrder::create([
                'name' => !empty($options['order_name']) ? $options['order_name'] : ($salesProposal->subject ?: ($salesProposal->proposal_number ?? 'From Proposal')),
                'proposal_id' => $salesProposal->id,
                'status' => SalesOrder::STATUS_CONFIRMED,
                'delivery_status' => SalesOrder::DELIVERY_STATUS_PENDING,
                'assignment_status' => $assignmentStatus,
                'assigned_group_id' => $assignedGroupId,
                'customer_id' => $customerId,
                'warehouse_id' => $targetWarehouseId,
                'order_date' => now()->toDateString(),
                'expected_delivery_date' => $salesProposal->due_date ? $salesProposal->due_date->format('Y-m-d') : now()->addDays(15)->format('Y-m-d'),
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress,
                'description' => $salesProposal->subject,
                'notes' => $salesProposal->notes,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'discount_amount' => round($discountAmount, 2),
                'total_amount' => round($totalAmount, 2),
                'confirmed_at' => now(),
                'creator_id' => Auth::id() ?: ($salesProposal->creator_id ?: creatorId()),
                'created_by' => creatorId(),
            ]);

            foreach ($productItems as $item) {
                $orderItem = SalesOrderItem::create([
                    'order_id' => $salesOrder->id,
                    'product_id' => $item['product_id'],
                    'product_type' => $item['product_type'] ?? 'product',
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'percentage',
                    'discount_percentage' => $item['discount_percentage'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_percentage' => $item['tax_percentage'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'final_price' => $item['total_amount'] ?? 0,
                    'total_amount' => $item['total_amount'] ?? 0,
                    'description' => $item['description'] ?? null,
                    'creator_id' => $salesOrder->creator_id,
                    'created_by' => $salesOrder->created_by,
                ]);

                if (!empty($item['taxes']) && is_array($item['taxes'])) {
                    foreach ($item['taxes'] as $tax) {
                        SalesOrderItemTax::create([
                            'item_id' => $orderItem->id,
                            'tax_name' => $tax['tax_name'] ?? $tax['name'] ?? 'Tax',
                            'tax_rate' => (float) ($tax['tax_rate'] ?? $tax['rate'] ?? 0),
                        ]);
                    }
                }
            }

            // Sync user assignments or group members
            if (!empty($assignedGroupId)) {
                $group = UserGroup::find($assignedGroupId);
                if ($group) {
                    $groupUserIds = $group->users()->pluck('users.id')->toArray();
                    $salesOrder->assignedUsers()->sync($groupUserIds);
                }
            } elseif (!empty($options['assigned_user_ids']) && is_array($options['assigned_user_ids'])) {
                $validIds = User::whereIn('id', $options['assigned_user_ids'])
                    ->where('created_by', creatorId())
                    ->pluck('id')
                    ->toArray();
                $salesOrder->assignedUsers()->sync($validIds);
            } elseif (!empty($options['assigned_user_id'])) {
                $validIds = User::whereIn('id', [(int) $options['assigned_user_id']])
                    ->where('created_by', creatorId())
                    ->pluck('id')
                    ->toArray();
                $salesOrder->assignedUsers()->sync($validIds);
            }

            $salesProposal->update([
                'converted_to_sales_order' => true,
                'sales_order_id' => $salesOrder->id,
            ]);

            return $salesOrder;
        });
    }

    public function convertProposalToInvoice(SalesProposal $salesProposal, ?array $customItems = null, ?int $warehouseId = null): SalesInvoice
    {
        return DB::transaction(function () use ($salesProposal, $customItems, $warehouseId) {
            $targetWarehouseId = $warehouseId ?: $salesProposal->warehouse_id;

            $productItems = [];
            if ($customItems !== null) {
                $productItems = array_values(array_filter($customItems, function ($item) {
                    return !empty($item['product_id']) && (int) $item['product_id'] > 0;
                }));
            } else {
                $dbItems = $salesProposal->items()->with(['taxes', 'product'])->get();
                foreach ($dbItems as $item) {
                    $type = $item->product_type ?: ($item->product?->type ?? 'product');
                    $productItems[] = [
                        'product_id' => $item->product_id,
                        'product_type' => $type,
                        'description' => $item->description,
                        'quantity' => (int) ($item->quantity ?? 1),
                        'unit_price' => (float) ($item->unit_price ?? 0),
                        'discount_type' => $item->discount_type ?? 'percentage',
                        'discount_percentage' => (float) ($item->discount_percentage ?? 0),
                        'discount_amount' => (float) ($item->discount_amount ?? 0),
                        'tax_percentage' => (float) ($item->tax_percentage ?? 0),
                        'tax_amount' => (float) ($item->tax_amount ?? 0),
                        'total_amount' => (float) ($item->total_amount ?? 0),
                        'taxes' => $item->taxes ? $item->taxes->map(fn($t) => [
                            'tax_name' => $t->tax_name,
                            'tax_rate' => $t->tax_rate,
                        ])->toArray() : [],
                    ];
                }
            }

            if (empty($productItems)) {
                throw new \Exception(__('Proposal does not have any items to convert to invoice.'));
            }

            // Validate stock only for physical product items (services have unlimited quantity)
            foreach ($productItems as $index => $item) {
                $productId = (int) $item['product_id'];
                $qty = (int) ($item['quantity'] ?? 1);
                $product = \Automas\ProductService\Models\ProductServiceItem::with('warehouseStocks')->find($productId);

                $isService = ($item['product_type'] ?? '') === 'service' || ($product && $product->type === 'service');

                if ($product && !$isService) {
                    $availableStock = $targetWarehouseId
                        ? ($product->warehouseStocks->where('warehouse_id', $targetWarehouseId)->first()?->quantity ?? 0)
                        : $product->warehouseStocks->sum('quantity');

                    if ($qty > $availableStock) {
                        throw new \Exception(__("Requested quantity (:qty) exceeds available stock (:stock) for ':name'.", [
                            'qty' => $qty,
                            'stock' => $availableStock,
                            'name' => $product->name,
                        ]));
                    }
                }
            }

            $subtotal = 0.0;
            $taxAmount = 0.0;
            $discountAmount = 0.0;

            foreach ($productItems as &$pItem) {
                $qty = max(1, (int) ($pItem['quantity'] ?? 1));
                $price = max(0, (float) ($pItem['unit_price'] ?? 0));
                $lineTotal = $qty * $price;

                $discType = $pItem['discount_type'] ?? 'percentage';
                if ($discType === 'fixed') {
                    $dAmount = min($lineTotal, max(0, (float) ($pItem['discount_amount'] ?? 0)));
                    $dPct = $lineTotal > 0 ? ($dAmount / $lineTotal) * 100 : 0;
                } else {
                    $dPct = max(0, min(100, (float) ($pItem['discount_percentage'] ?? 0)));
                    $dAmount = ($lineTotal * $dPct) / 100;
                }

                $taxRate = (float) ($pItem['tax_percentage'] ?? 0);
                if (!empty($pItem['taxes']) && is_array($pItem['taxes'])) {
                    $taxRate = array_reduce($pItem['taxes'], fn($sum, $t) => $sum + (float) ($t['tax_rate'] ?? $t['rate'] ?? 0), 0.0);
                }

                $afterDisc = max(0, $lineTotal - $dAmount);
                $tAmount = ($afterDisc * $taxRate) / 100;
                $totAmount = max(0, $afterDisc + $tAmount);

                $pItem['quantity'] = $qty;
                $pItem['unit_price'] = $price;
                $pItem['discount_type'] = $discType;
                $pItem['discount_percentage'] = round($dPct, 4);
                $pItem['discount_amount'] = round($dAmount, 2);
                $pItem['tax_percentage'] = round($taxRate, 4);
                $pItem['tax_amount'] = round($tAmount, 2);
                $pItem['total_amount'] = round($totAmount, 2);

                $subtotal += $lineTotal;
                $discountAmount += $dAmount;
                $taxAmount += $tAmount;
            }
            unset($pItem);

            $totalAmount = max(0, $subtotal + $taxAmount - $discountAmount);

            $invoice = new SalesInvoice();
            $invoice->invoice_date = now()->format('Y-m-d');
            $invoice->due_date = $salesProposal->due_date ? $salesProposal->due_date->format('Y-m-d') : now()->addDays(15)->format('Y-m-d');
            $invoice->customer_id = $salesProposal->customer_id;
            $invoice->customer_name = $salesProposal->customer_name;
            $invoice->customer_email = $salesProposal->customer_email;
            $invoice->customer_phone = $salesProposal->customer_phone;
            $invoice->customer_address = $salesProposal->customer_address;
            $invoice->warehouse_id = $targetWarehouseId;
            $invoice->type = 'product';
            $invoice->payment_terms = $salesProposal->payment_terms;
            $invoice->notes = $salesProposal->notes;
            $invoice->subtotal = round($subtotal, 2);
            $invoice->tax_amount = round($taxAmount, 2);
            $invoice->discount_amount = round($discountAmount, 2);
            $invoice->discount_type = 'percentage';
            $invoice->total_amount = round($totalAmount, 2);
            $invoice->paid_amount = 0;
            $invoice->balance_amount = round($totalAmount, 2);
            $invoice->status = 'draft';
            $invoice->creator_id = Auth::id() ?: ($salesProposal->creator_id ?: creatorId());
            $invoice->created_by = creatorId();
            $invoice->save();

            foreach ($productItems as $item) {
                $invoiceItem = new SalesInvoiceItem();
                $invoiceItem->invoice_id = $invoice->id;
                $invoiceItem->product_id = $item['product_id'];
                $invoiceItem->description = $item['description'] ?? null;
                $invoiceItem->product_type = $item['product_type'] ?? 'product';
                $invoiceItem->quantity = $item['quantity'] ?? 1;
                $invoiceItem->unit_price = $item['unit_price'] ?? 0;
                $invoiceItem->discount_type = $item['discount_type'] ?? 'percentage';
                $invoiceItem->discount_percentage = $item['discount_percentage'] ?? 0;
                $invoiceItem->discount_amount = $item['discount_amount'] ?? 0;
                $invoiceItem->tax_percentage = $item['tax_percentage'] ?? 0;
                $invoiceItem->tax_amount = $item['tax_amount'] ?? 0;
                $invoiceItem->total_amount = $item['total_amount'] ?? 0;
                $invoiceItem->creator_id = $invoice->creator_id;
                $invoiceItem->created_by = $invoice->created_by;
                $invoiceItem->save();

                if (!empty($item['taxes']) && is_array($item['taxes'])) {
                    foreach ($item['taxes'] as $tax) {
                        $invoiceTax = new SalesInvoiceItemTax();
                        $invoiceTax->item_id = $invoiceItem->id;
                        $invoiceTax->tax_name = $tax['tax_name'] ?? $tax['name'] ?? '';
                        $invoiceTax->tax_rate = (float) ($tax['tax_rate'] ?? $tax['rate'] ?? 0);
                        $invoiceTax->save();
                    }
                }
            }

            $salesProposal->update([
                'converted_to_invoice' => true,
            ]);

            return $invoice;
        });
    }

    public function saveProposalItems(int $proposalId, ?array $items, bool $isTaxEnabled = true): void
    {
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            if (empty($item['product_id']) || (int) $item['product_id'] <= 0) {
                continue;
            }

            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $price = max(0, (float) ($item['unit_price'] ?? 0));
            $discType = $item['discount_type'] ?? 'percentage';
            $lineTotal = $qty * $price;

            if ($discType === 'fixed') {
                $discAmt = min($lineTotal, max(0, (float) ($item['discount_amount'] ?? 0)));
                $discRate = $lineTotal > 0 ? round(($discAmt / $lineTotal) * 100, 4) : 0;
            } else {
                $discRate = max(0, min(100, (float) ($item['discount_percentage'] ?? 0)));
                $discAmt = ($lineTotal * $discRate) / 100;
            }

            $taxRate = 0.0;
            if ($isTaxEnabled) {
                $taxRate = (float) ($item['tax_percentage'] ?? 0);
                if (!empty($item['taxes']) && is_array($item['taxes'])) {
                    $taxRate = array_reduce($item['taxes'], fn($sum, $t) => $sum + (float) ($t['tax_rate'] ?? $t['rate'] ?? 0), 0.0);
                }
            }

            $afterDisc = max(0, $lineTotal - $discAmt);
            $taxAmt = $isTaxEnabled ? (($afterDisc * $taxRate) / 100) : 0;
            $totalAmt = max(0, $afterDisc + $taxAmt);

            $proposalItem = new SalesProposalItem();
            $proposalItem->proposal_id = $proposalId;
            $proposalItem->product_id = $item['product_id'];
            $proposalItem->section = $item['section'] ?? 'otc';
            $proposalItem->product_type = $item['product_type'] ?? 'product';
            $proposalItem->description = $item['description'] ?? $item['product_description'] ?? null;
            $proposalItem->quantity = $qty;
            $proposalItem->unit_price = $price;
            $proposalItem->discount_type = $discType;
            $proposalItem->discount_percentage = $discRate;
            $proposalItem->discount_amount = $discAmt;
            $proposalItem->tax_percentage = $taxRate;
            $proposalItem->tax_amount = $taxAmt;
            $proposalItem->total_amount = $totalAmt;
            $proposalItem->save();

            if ($isTaxEnabled && !empty($item['taxes']) && is_array($item['taxes'])) {
                foreach ($item['taxes'] as $tax) {
                    $itemTax = new SalesProposalItemTax();
                    $itemTax->item_id = $proposalItem->id;
                    $itemTax->tax_name = $tax['tax_name'] ?? 'Tax';
                    $itemTax->tax_rate = (float) ($tax['tax_rate'] ?? $tax['rate'] ?? 0);
                    $itemTax->save();
                }
            }
        }
    }

    public function saveProposalPageContents(int $proposalId, $contents): void
    {
        if (!Schema::hasTable('sales_proposal_contents')) {
            return;
        }

        try {
            if (Schema::hasColumn('sales_proposal_contents', 'proposal_id')) {
                SalesProposalContent::where('proposal_id', $proposalId)->delete();
            }
        } catch (\Throwable $th) {
            // Silently catch
        }

        if (empty($contents)) {
            return;
        }

        $items = is_string($contents) ? json_decode($contents, true) : $contents;
        if (!is_array($items)) {
            return;
        }

        $proposal = SalesProposal::find($proposalId);
        $authorUserId = $proposal?->creator_id ?? Auth::id();
        $authorUser = $authorUserId ? User::find($authorUserId) : null;

        $employee = null;
        if (class_exists(\Automas\Hrm\Models\Employee::class)) {
            try {
                $employee = \Automas\Hrm\Models\Employee::with('designation')->where('user_id', $authorUserId)->first();
            } catch (\Throwable $e) {
                // Ignore if HRM table deleted
            }
        }

        $userShortcodes = [
            'user_name' => $authorUser?->name ?? '',
            'creator_name' => $authorUser?->name ?? '',
            'user_email' => $authorUser?->email ?? '',
            'creator_email' => $authorUser?->email ?? '',
            'user_phone' => $employee?->emergency_contact_number ?? $authorUser?->mobile_no ?? $authorUser?->phone ?? '',
            'creator_phone' => $employee?->emergency_contact_number ?? $authorUser?->mobile_no ?? $authorUser?->phone ?? '',
            'user_id' => $employee?->employee_id ?? ($authorUser?->id ? (string) $authorUser->id : ''),
            'creator_designation' => $employee?->designation?->name ?? $authorUser?->designation ?? '',
            'user_designation' => $employee?->designation?->name ?? $authorUser?->designation ?? '',
        ];

        $replaceUserCodes = function ($text) use ($userShortcodes) {
            if (empty($text) || !is_string($text)) {
                return $text;
            }
            foreach ($userShortcodes as $k => $v) {
                if ($v !== '') {
                    $text = preg_replace('/\{\s*' . preg_quote($k, '/') . '\s*\}/i', $v, $text);
                }
            }
            return $text;
        };

        $index = 1;
        foreach ($items as $item) {
            if (is_array($item)) {
                $pageType = $item['page_type'] ?? 'content';
                $order = isset($item['order']) ? (int) $item['order'] : $index;
                $title = $item['title'] ?? null;
                $html = $item['content'] ?? null;
                $bg = $item['background_image'] ?? null;
                $serialized = json_encode($item);
            } else {
                $order = $index;
                $title = null;
                $html = (string) $item;
                $pageType = 'content';
                $bg = null;
                $serialized = (string) $item;
            }

            if ($title !== null) {
                $title = $replaceUserCodes($title);
            }
            if ($html !== null) {
                $html = $replaceUserCodes($html);
            }

            SalesProposalContent::create([
                'proposal_id' => $proposalId,
                'title' => $title,
                'content' => $html ?? $serialized,
                'page_type' => $pageType,
                'background_image' => $bg,
                'order' => $order,
                'creator_id' => Auth::id(),
                'created_by' => creatorId(),
            ]);
            $index++;
        }
    }
}
