<?php

namespace Automas\Hrm\Http\Controllers;

use Automas\Hrm\Models\CompanyPolicy;
use Automas\Hrm\Http\Requests\StoreCompanyPolicyRequest;
use Automas\Hrm\Http\Requests\UpdateCompanyPolicyRequest;
use Automas\Hrm\Models\Branch;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Hrm\Events\CreateCompanyPolicy;
use Automas\Hrm\Events\UpdateCompanyPolicy;
use Automas\Hrm\Events\DestroyCompanyPolicy;

class CompanyPolicyController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-company-policies')) {
            $companyPolicies = CompanyPolicy::query()
                ->with(['branch'])
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-company-policies')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-company-policies')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('title'), function ($q) {
                    $q->where(function ($query) {
                        $query->where('title', 'like', '%' . request('title') . '%')
                            ->orWhere('description', 'like', '%' . request('title') . '%')
                            ->orWhereHas('branch', function ($subQuery) {
                                $subQuery->where('branch_name', 'like', '%' . request('title') . '%');
                            });
                    });
                })
                ->when(request('branch_id'), fn($q) => $q->where('branch_id', request('branch_id')))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('Hrm/CompanyPolicies/Index', [
                'companyPolicies' => $companyPolicies,
                'branches' => Branch::where('created_by', creatorId())->select('id', 'branch_name')->get(),
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreCompanyPolicyRequest $request)
    {
        if (Auth::user()->can('create-company-policies')) {
            $validated = $request->validated();

            $companyPolicy = new CompanyPolicy();
            $companyPolicy->branch_id = $validated['branch_id'] ?? null;
            $companyPolicy->title = $validated['title'];
            $companyPolicy->description = $validated['description'] ?? null;
            $companyPolicy->attachment = basename($validated['attachment']) ?? null;
            $companyPolicy->creator_id = Auth::id();
            $companyPolicy->created_by = creatorId();
            $companyPolicy->save();

            CreateCompanyPolicy::dispatch($request, $companyPolicy);

            return redirect()->back()->with('success', __('The company policy has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateCompanyPolicyRequest $request, CompanyPolicy $companyPolicy)
    {
        if (Auth::user()->can('edit-company-policies')) {
            $validated = $request->validated();

            $companyPolicy->branch_id = $validated['branch_id'] ?? null;
            $companyPolicy->title = $validated['title'];
            $companyPolicy->description = $validated['description'] ?? null;
            $companyPolicy->attachment = basename($validated['attachment']) ?? null;
            $companyPolicy->save();

            UpdateCompanyPolicy::dispatch($request, $companyPolicy);

            return redirect()->back()->with('success', __('The company policy details are updated successfully.'));
        } else {
            return redirect()->route('hrm.company-policies.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(CompanyPolicy $companyPolicy)
    {
        if (Auth::user()->can('delete-company-policies')) {
            DestroyCompanyPolicy::dispatch($companyPolicy);
            $companyPolicy->delete();

            return redirect()->back()->with('success', __('The company policy has been deleted.'));
        } else {
            return redirect()->route('hrm.company-policies.index')->with('error', __('Permission denied'));
        }
    }
}
