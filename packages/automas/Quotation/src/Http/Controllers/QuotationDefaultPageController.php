<?php

namespace Automas\Quotation\Http\Controllers;

use App\Http\Controllers\Controller;
use Automas\Quotation\Http\Requests\QuotationDefaultPage\StoreQuotationDefaultPageRequest;
use Automas\Quotation\Http\Requests\QuotationDefaultPage\UpdateQuotationDefaultPageRequest;
use Automas\Quotation\Models\QuotationDefaultPage;
use Automas\Quotation\Models\QuotationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuotationDefaultPageController extends Controller
{
    private function getQuotationVariables(): array
    {
        return [
            'App Name' => 'app_name',
            'Company Name' => 'company_name',
            'Company Logo' => 'company_logo',
            'Quotation Logo' => 'quotation_logo',
            'Company Email' => 'company_email',
            'Company Phone' => 'company_phone',
            'Company Address' => 'company_address',
            'Company Website' => 'company_website',
            'User Name' => 'user_name',
            'User Email' => 'user_email',
            'User Phone' => 'user_phone',
            'Subject' => 'quotation_subject',
            'Quotation Number' => 'quotation_number',
            'Quotation Date' => 'quotation_date',
            'Due Date' => 'due_date',
            'Customer Name' => 'customer_name',
            'Customer Email' => 'customer_email',
            'Customer Phone' => 'customer_phone',
            'Customer Address' => 'customer_address',
            'Total Amount' => 'total_amount',
            'Sub Total' => 'sub_total',
            'Total Tax' => 'total_tax',
            'Total Discount' => 'total_discount',
        ];
    }

    private function authorizePage(QuotationDefaultPage $defaultPage): bool
    {
        $creatorId = creatorId() ?? Auth::id();
        return $defaultPage->created_by == $creatorId && ($defaultPage->creator_id == Auth::id() || $defaultPage->creator_id == $creatorId);
    }

    public function create()
    {
        if (!Auth::user()->can('manage-quotation-system-setup')) {
            return redirect()->route('quotations.index')->with('error', __('Permission denied'));
        }

        $creatorId = creatorId() ?? Auth::id();
        $settings = QuotationSetting::getSettings($creatorId);
        $maxOrder = QuotationDefaultPage::where('created_by', $creatorId)->max('sort_order') ?? 0;

        return Inertia::render('Quotation/Settings/DefaultPages/Create', [
            'settings' => $settings,
            'nextSortOrder' => $maxOrder + 1,
            'variables' => $this->getQuotationVariables(),
        ]);
    }

    public function store(StoreQuotationDefaultPageRequest $request)
    {
        if (!Auth::user()->can('manage-quotation-system-setup')) {
            return redirect()->route('quotations.index')->with('error', __('Permission denied'));
        }

        $creatorId = creatorId() ?? Auth::id();
        $validated = $request->validated();

        $page = QuotationDefaultPage::create(array_merge($validated, [
            'created_by' => $creatorId,
            'creator_id' => Auth::id(),
            'page_type' => $request->input('page_type', 'general'),
            'content' => $request->input('content', ''),
            'background_image' => $request->input('background_image'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 1),
        ]));

        $status = $page ? 'success' : 'error';
        $message = $page ? __('Default page created successfully.')
            : __('Failed to create default page.');

        return redirect()->route('quotation-setup.index')->with($status, $message);
    }

    public function edit(QuotationDefaultPage $defaultPage)
    {
        if (!Auth::user()->can('manage-quotation-system-setup')) {
            return redirect()->route('quotations.index')->with('error', __('Permission denied'));
        }

        if (!$this->authorizePage($defaultPage)) {
            return redirect()->route('quotation-setup.index')->with('error', __('Unauthorized access.'));
        }

        $creatorId = creatorId() ?? Auth::id();
        $settings = QuotationSetting::getSettings($creatorId);

        return Inertia::render('Quotation/Settings/DefaultPages/Edit', [
            'settings' => $settings,
            'defaultPage' => $defaultPage,
            'variables' => $this->getQuotationVariables(),
        ]);
    }

    public function update(UpdateQuotationDefaultPageRequest $request, QuotationDefaultPage $defaultPage)
    {
        if (!Auth::user()->can('manage-quotation-system-setup')) {
            return redirect()->route('quotations.index')->with('error', __('Permission denied'));
        }

        if (!$this->authorizePage($defaultPage)) {
            return redirect()->route('quotation-setup.index')->with('error', __('Unauthorized access.'));
        }

        $creatorId = creatorId() ?? Auth::id();
        $isFixed = in_array($defaultPage->page_type, ['otc', 'mrc']);

        $data = [
            'sort_order' => $request->input('sort_order', $defaultPage->sort_order),
            'is_active' => $isFixed ? true : ($request->has('is_active') ? $request->boolean('is_active') : $defaultPage->is_active),
        ];

        if (!$isFixed) {
            $validated = $request->validated();
            $data = array_merge($validated, $data, [
                'created_by' => $creatorId,
                'creator_id' => Auth::id(),
                'title' => $request->input('title', $defaultPage->title),
                'page_type' => $request->input('page_type', $defaultPage->page_type),
                'content' => $request->has('content') ? $request->input('content', '') : $defaultPage->content,
                'background_image' => $request->has('background_image') ? $request->input('background_image') : $defaultPage->background_image,
            ]);
        }

        $updated = $defaultPage->update($data);

        $status = $updated ? 'success' : 'error';
        $message = $updated ? __('Default page updated successfully.')
            : __('Failed to update default page.');

        return redirect()->route('quotation-setup.index')->with($status, $message);
    }

    public function destroy(QuotationDefaultPage $defaultPage)
    {
        if (!Auth::user()->can('manage-quotation-system-setup')) {
            return back()->with('error', __('Permission denied'));
        }

        if (in_array($defaultPage->page_type, ['otc', 'mrc'])) {
            return redirect()->back()->with('error', __('Fixed system pages cannot be deleted.'));
        }

        if (!$this->authorizePage($defaultPage)) {
            return redirect()->back()->with('error', __('Unauthorized access.'));
        }

        $deleted = $defaultPage->delete();

        $status = $deleted ? 'success' : 'error';
        $message = $deleted ? __('Default page deleted successfully.')
            : __('Failed to delete default page.');

        return redirect()->back()->with($status, $message);
    }

    public function reorder(Request $request)
    {
        if (!Auth::user()->can('manage-quotation-system-setup')) {
            return response()->json(['error' => __('Permission denied')], 403);
        }

        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer|exists:quotation_default_pages,id',
            'orders.*.sort_order' => 'required|integer|min:0',
        ]);

        $creatorId = creatorId() ?? Auth::id();

        DB::transaction(function () use ($request, $creatorId) {
            QuotationDefaultPage::where('created_by', $creatorId)
                ->update([
                    'sort_order' => DB::raw('100000 + id')
                ]);

            foreach ($request->orders as $item) {
                QuotationDefaultPage::where('id', $item['id'])
                    ->where('created_by', $creatorId)
                    ->update([
                        'sort_order' => (int) $item['sort_order'],
                    ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => __('Sort order updated successfully.'),
        ]);
    }
}
