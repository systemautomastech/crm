<?php

namespace Automas\Hrm\Http\Controllers;

use Automas\Hrm\Models\ComplaintType;
use Automas\Hrm\Http\Requests\StoreComplaintTypeRequest;
use Automas\Hrm\Http\Requests\UpdateComplaintTypeRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Hrm\Events\CreateComplaintType;
use Automas\Hrm\Events\DestroyComplaintType;
use Automas\Hrm\Events\UpdateComplaintType;


class ComplaintTypeController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-complaint-types')){
            $complainttypes = ComplaintType::select('id', 'complaint_type', 'created_at')
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-complaint-types')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-complaint-types')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->latest()
                ->get();

            return Inertia::render('Hrm/SystemSetup/ComplaintTypes/Index', [
                'complainttypes' => $complainttypes,

            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreComplaintTypeRequest $request)
    {
        if(Auth::user()->can('create-complaint-types')){
            $validated = $request->validated();



            $complainttype = new ComplaintType();
            $complainttype->complaint_type = $validated['complaint_type'];

            $complainttype->creator_id = Auth::id();
            $complainttype->created_by = creatorId();
            $complainttype->save();

            CreateComplaintType::dispatch($request, $complainttype);

            return redirect()->route('hrm.complaint-types.index')->with('success', __('The complaint type has been created successfully.'));
        }
        else{
            return redirect()->route('hrm.complaint-types.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateComplaintTypeRequest $request, ComplaintType $complainttype)
    {
        if(Auth::user()->can('edit-complaint-types')){
            $validated = $request->validated();



            $complainttype->complaint_type = $validated['complaint_type'];

            $complainttype->save();

            UpdateComplaintType::dispatch($request, $complainttype);

            return back()->with('success', __('The complaint type details are updated successfully.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(ComplaintType $complainttype)
    {
        if(Auth::user()->can('delete-complaint-types')){
            DestroyComplaintType::dispatch($complainttype);
            $complainttype->delete();

            return back()->with('success', __('The complaint type has been deleted.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }


}