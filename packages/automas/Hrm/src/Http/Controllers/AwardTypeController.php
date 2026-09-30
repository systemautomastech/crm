<?php

namespace Automas\Hrm\Http\Controllers;

use Automas\Hrm\Models\AwardType;
use Automas\Hrm\Http\Requests\StoreAwardTypeRequest;
use Automas\Hrm\Http\Requests\UpdateAwardTypeRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Hrm\Events\CreateAwardType;
use Automas\Hrm\Events\DestroyAwardType;
use Automas\Hrm\Events\UpdateAwardType;


class AwardTypeController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-award-types')){
            $awardtypes = AwardType::select('id', 'name', 'description', 'created_at')
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-award-types')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-award-types')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->latest()
                ->get();

            return Inertia::render('Hrm/SystemSetup/AwardTypes/Index', [
                'awardtypes' => $awardtypes,

            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreAwardTypeRequest $request)
    {
        if(Auth::user()->can('create-award-types')){
            $validated = $request->validated();



            $awardtype = new AwardType();
            $awardtype->name = $validated['name'];
            $awardtype->description = $validated['description'];

            $awardtype->creator_id = Auth::id();
            $awardtype->created_by = creatorId();
            $awardtype->save();

            CreateAwardType::dispatch($request, $awardtype);

            return redirect()->route('hrm.award-types.index')->with('success', __('The award type has been created successfully.'));
        }
        else{
            return redirect()->route('hrm.award-types.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateAwardTypeRequest $request, AwardType $awardtype)
    {
        if(Auth::user()->can('edit-award-types')){
            $validated = $request->validated();



            $awardtype->name = $validated['name'];
            $awardtype->description = $validated['description'];

            $awardtype->save();

            UpdateAwardType::dispatch($request, $awardtype);

            return back()->with('success', __('The award type details are updated successfully.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(AwardType $awardtype)
    {
        if(Auth::user()->can('delete-award-types')){
            DestroyAwardType::dispatch($awardtype);
            $awardtype->delete();

            return back()->with('success', __('The award type has been deleted.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }


}