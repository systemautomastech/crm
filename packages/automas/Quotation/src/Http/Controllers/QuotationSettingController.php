<?php

namespace Automas\Quotation\Http\Controllers;

use App\Http\Controllers\Controller;
use Automas\Quotation\Http\Requests\QuotationSetting\StoreQuotationSettingRequest;
use Automas\Quotation\Http\Requests\QuotationSetting\UpdateQuotationSettingRequest;
use Automas\Quotation\Models\QuotationDefaultPage;
use Automas\Quotation\Models\QuotationSetting;
use Automas\Quotation\Models\QuotationSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class QuotationSettingController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('manage-quotation-system-setup')) {
            return redirect()->route('dashboard')->with('error', __('Permission denied'));
        }

        $creatorId = function_exists('creatorId') ? creatorId() : Auth::id();
        $this->syncFixedPages($creatorId);

        $settings = QuotationSetting::getSettings($creatorId);

        $defaultPages = QuotationDefaultPage::with('author:id,name,email')
            ->where('created_by', $creatorId)
            ->where(function ($query) use ($creatorId) {
                $query->where('creator_id', Auth::id())
                    ->orWhere('creator_id', $creatorId);
            })
            ->orderBy('sort_order')
            ->get();

        $subjects = QuotationSubject::where('created_by', $creatorId)
            ->latest()
            ->get();

        return Inertia::render('Quotation/Settings/Index', [
            'settings' => $settings,
            'defaultPages' => $defaultPages,
            'subjects' => $subjects,
        ]);
    }

    private function syncFixedPages(int $creatorId): void
    {
        if (!$creatorId)
            return;

        // 1. OTC Page
        $otc = QuotationDefaultPage::where('created_by', $creatorId)
            ->where('page_type', 'otc')
            ->first();

        if (!$otc) {
            $maxOrder = QuotationDefaultPage::where('created_by', $creatorId)->max('sort_order') ?? 0;
            QuotationDefaultPage::create([
                'title' => 'One-Time Charges (OTC)',
                'content' => '',
                'page_type' => 'otc',
                'sort_order' => $maxOrder + 1,
                'is_active' => true,
                'created_by' => $creatorId,
                'creator_id' => $creatorId,
            ]);
        }

        // 2. MRC Page
        $mrc = QuotationDefaultPage::where('created_by', $creatorId)
            ->where('page_type', 'mrc')
            ->first();

        if (!$mrc) {
            $maxOrder = QuotationDefaultPage::where('created_by', $creatorId)->max('sort_order') ?? 0;
            QuotationDefaultPage::create([
                'title' => 'Monthly Recurring Charges (MRC)',
                'content' => '',
                'page_type' => 'mrc',
                'sort_order' => $maxOrder + 1,
                'is_active' => true,
                'created_by' => $creatorId,
                'creator_id' => $creatorId,
            ]);
        }
    }

    public function store(StoreQuotationSettingRequest $request)
    {
        if (!Auth::user()->can('manage-quotation-system-setup')) {
            return back()->with('error', __('Permission denied'));
        }

        $creatorId = function_exists('creatorId') ? creatorId() : Auth::id();
        $validatedData = $request->validated();
        $settingsData = $request->input('settings', $validatedData ?: $request->except(['_token', '_method']));

        QuotationSetting::setSettings($settingsData, $creatorId);

        return redirect()->back()->with('success', __('Settings saved successfully.'));
    }

    public function update(UpdateQuotationSettingRequest $request)
    {
        if (!Auth::user()->can('manage-quotation-system-setup')) {
            return back()->with('error', __('Permission denied'));
        }

        $creatorId = function_exists('creatorId') ? creatorId() : Auth::id();
        $validatedData = $request->validated();
        $settingsData = $request->input('settings', $validatedData ?: $request->except(['_token', '_method']));

        QuotationSetting::setSettings($settingsData, $creatorId);

        return redirect()->back()->with('success', __('Settings updated successfully.'));
    }
}
