<?php

namespace Automas\Hrm\Http\Controllers;

use Automas\Hrm\Models\AllowanceType;
use Automas\Hrm\Http\Requests\StoreAllowanceTypeRequest;
use Automas\Hrm\Http\Requests\UpdateAllowanceTypeRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Hrm\Events\CreateAllowanceType;
use Automas\Hrm\Events\DestroyAllowanceType;
use Automas\Hrm\Events\UpdateAllowanceType;


class AllowanceTypeController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-allowance-types')) {
            $allowancetypes = AllowanceType::select('id', 'name', 'description', 'created_at')
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-allowance-types')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-allowance-types')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->latest()
                ->get();

            return Inertia::render('Hrm/SystemSetup/AllowanceTypes/Index', [
                'allowancetypes' => $allowancetypes,

            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreAllowanceTypeRequest $request)
    {
        if (Auth::user()->can('create-allowance-types')) {
            $validated = $request->validated();



            $allowancetype = new AllowanceType();
            $allowancetype->name = $validated['name'];
            $allowancetype->description = $validated['description'];

            $allowancetype->creator_id = Auth::id();
            $allowancetype->created_by = creatorId();
            $allowancetype->save();

            CreateAllowanceType::dispatch($request, $allowancetype);

            return redirect()->route('hrm.allowance-types.index')->with('success', __('The allowance type has been created successfully.'));
        } else {
            return redirect()->route('hrm.allowance-types.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateAllowanceTypeRequest $request, AllowanceType $allowancetype)
    {
        if (Auth::user()->can('edit-allowance-types')) {
            $validated = $request->validated();



            $allowancetype->name = $validated['name'];
            $allowancetype->description = $validated['description'];

            $allowancetype->save();

            UpdateAllowanceType::dispatch($request, $allowancetype);

            return redirect()->route('hrm.allowance-types.index')->with('success', __('The allowance type details are updated successfully.'));
        } else {
            return redirect()->route('hrm.allowance-types.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(AllowanceType $allowancetype)
    {
        if (Auth::user()->can('delete-allowance-types')) {
            DestroyAllowanceType::dispatch($allowancetype);
            $allowancetype->delete();

            return redirect()->route('hrm.allowance-types.index')->with('success', __('The allowance type has been deleted.'));
        } else {
            return redirect()->route('hrm.allowance-types.index')->with('error', __('Permission denied'));
        }
    }
}
