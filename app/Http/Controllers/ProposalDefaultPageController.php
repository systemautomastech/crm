<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProposalDefaultPage\StoreDefaultPageRequest;
use App\Http\Requests\ProposalDefaultPage\UpdateDefaultPageRequest;
use App\Models\ProposalDefaultPage;
use App\Models\ProposalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProposalDefaultPageController extends Controller
{
    public function create()
    {
        if (!Auth::user()->can('manage-proposal-system-setup')) {
            return redirect()->route('sales-proposals.index')
                ->with('error', __('Permission denied'));
        }

        $settings = ProposalSetting::getSettings(creatorId());
        $maxOrder = ProposalDefaultPage::where('created_by', creatorId())
            ->max('sort_order') ?? 0;

        $data = [
            'settings' => $settings,
            'nextSortOrder' => $maxOrder + 1,
            'variables' => $this->variables(),
        ];

        return Inertia::render('SalesProposalSetup/DefaultPages/Create', $data);
    }

    public function store(StoreDefaultPageRequest $request)
    {
        if (!Auth::user()->can('manage-proposal-system-setup')) {
            return redirect()->route('sales-proposals.index')
                ->with('error', __('Permission denied'));
        }

        $validated = $request->validated();
        $data = array_merge($validated, [
            'created_by' => creatorId(),
            'creator_id' => Auth::id(),
        ]);

        $defaultPage = ProposalDefaultPage::create($data);

        $status = $defaultPage ? 'success' : 'error';
        $message = $defaultPage ? __('Default page created successfully.')
            : __('Failed to create default page.');

        return redirect()->route('proposal-setup.index')->with($status, $message);
    }

    public function edit(ProposalDefaultPage $defaultPage)
    {
        if (!Auth::user()->can('manage-proposal-system-setup')) {
            return redirect()->route('sales-proposals.index')
                ->with('error', __('Permission denied'));
        }

        if (!$this->authorizePage($defaultPage)) {
            return redirect()->route('proposal-setup.index')
                ->with('error', __('Unauthorized access.'));
        }

        $settings = ProposalSetting::getSettings(creatorId());

        $data = [
            'settings' => $settings,
            'defaultPage' => $defaultPage,
            'variables' => $this->variables(),
        ];

        return Inertia::render('SalesProposalSetup/DefaultPages/Edit', $data);
    }

    public function update(UpdateDefaultPageRequest $request, ProposalDefaultPage $defaultPage)
    {
        if (!Auth::user()->can('manage-proposal-system-setup')) {
            return redirect()->route('sales-proposals.index')
                ->with('error', __('Permission denied'));
        }

        if (!$this->authorizePage($defaultPage)) {
            return redirect()->route('proposal-setup.index')
                ->with('error', __('Unauthorized access.'));
        }

        $isFixed = in_array($defaultPage->page_type, ['otc', 'mrc']);

        $data = [
            'sort_order' => $request->input('sort_order', $defaultPage->sort_order),
            'is_active' => $isFixed ? true : ($request->has('is_active') ? $request->boolean('is_active') : $defaultPage->is_active),
        ];

        if (!$isFixed) {
            $data = array_merge($data, [
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

        return redirect()->route('proposal-setup.index')->with($status, $message);
    }

    public function destroy(ProposalDefaultPage $defaultPage)
    {
        if (!Auth::user()->can('manage-proposal-system-setup')) {
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
        if (!Auth::user()->can('manage-proposal-system-setup')) {
            return response()->json(['error' => __('Permission denied')], 403);
        }

        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer|exists:proposal_default_pages,id',
            'orders.*.sort_order' => 'required|integer|min:0',
        ]);

        $creatorId = creatorId();

        DB::transaction(function () use ($request, $creatorId) {
            ProposalDefaultPage::where('created_by', $creatorId)
                ->update([
                    'sort_order' => DB::raw('100000 + id')
                ]);

            foreach ($request->orders as $item) {
                ProposalDefaultPage::where('id', $item['id'])
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

    private function authorizePage(ProposalDefaultPage $page): bool
    {
        if ($page->created_by == creatorId()) {
            if ($page->creator_id == Auth::id() || Auth::id() == creatorId()) {
                return true;
            }
        }
        return false;
    }

    private function variables(): array
    {
        return [
            'App Name' => 'app_name',
            'Company Name' => 'company_name',
            'Company Logo' => 'company_logo',
            'Proposal Logo' => 'proposal_logo',
            'Company Email' => 'company_email',
            'Company Phone' => 'company_phone',
            'Company Address' => 'company_address',
            'Company Website' => 'company_website',
            'User Name' => 'user_name',
            'User Email' => 'user_email',
            'User Phone' => 'user_phone',
            'Proposal Number' => 'proposal_number',
            'Proposal Date' => 'proposal_date',
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
}
