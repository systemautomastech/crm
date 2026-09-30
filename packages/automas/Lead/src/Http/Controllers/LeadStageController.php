<?php

namespace Automas\Lead\Http\Controllers;

use Automas\Lead\Models\LeadStage;
use Automas\Lead\Http\Requests\StoreLeadStageRequest;
use Automas\Lead\Http\Requests\UpdateLeadStageRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Automas\Lead\Models\Pipeline;
use Automas\Lead\Events\CreateLeadStage;
use Automas\Lead\Events\UpdateLeadStage;
use Automas\Lead\Events\DestroyLeadStage;

class LeadStageController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-lead-stages')){
            $leadstages = LeadStage::select('id', 'name', 'order', 'pipeline_id', 'is_final_accepted', 'is_final_rejected', 'created_at')
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-lead-stages')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-lead-stages')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->latest()
                ->get();

            return Inertia::render('Lead/SystemSetup/LeadStages/Index', [
                'leadstages' => $leadstages,
                'pipelines' => Pipeline::where('created_by', creatorId())->select('id', 'name')->orderBy('id', 'desc')->get(),
            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreLeadStageRequest $request)
    {
        if(Auth::user()->can('create-lead-stages')){
            $validated = $request->validated();

            $maxOrder = LeadStage::where('pipeline_id', $validated['pipeline_id'])
                ->where('created_by', creatorId())
                ->max('order') ?? 0;

            $isFinalAccepted = false;
            $isFinalRejected = false;

            if (isset($validated['final_type'])) {
                $isFinalAccepted = $validated['final_type'] === 'accepted';
                $isFinalRejected = $validated['final_type'] === 'rejected';
            } elseif (isset($validated['is_final_accepted']) || isset($validated['is_final_rejected'])) {
                $isFinalAccepted = (bool)($validated['is_final_accepted'] ?? false);
                $isFinalRejected = (bool)($validated['is_final_rejected'] ?? false);
            }

            if ($isFinalAccepted) {
                LeadStage::where('pipeline_id', $validated['pipeline_id'])
                    ->where('created_by', creatorId())
                    ->update(['is_final_accepted' => false]);
                $isFinalRejected = false;
            } elseif ($isFinalRejected) {
                LeadStage::where('pipeline_id', $validated['pipeline_id'])
                    ->where('created_by', creatorId())
                    ->update(['is_final_rejected' => false]);
            }

            $leadstage                    = new LeadStage();
            $leadstage->name              = $validated['name'];
            $leadstage->pipeline_id       = $validated['pipeline_id'];
            $leadstage->order             = $maxOrder + 1;
            $leadstage->is_final_accepted = $isFinalAccepted;
            $leadstage->is_final_rejected = $isFinalRejected;
            $leadstage->creator_id        = Auth::id();
            $leadstage->created_by        = creatorId();
            $leadstage->save();

            CreateLeadStage::dispatch($request, $leadstage);

            return redirect()->route('lead.lead-stages.index')->with('success', __('The lead stage has been created successfully.'));
        }
        else{
            return redirect()->route('lead.lead-stages.index')->with('error', __('Permission denied'));
        }
    }

    public function setFinalAccepted(LeadStage $leadstage)
    {
        if (Auth::user()->can('edit-lead-stages') && $leadstage->created_by == creatorId()) {
            if ($leadstage->is_final_accepted) {
                $leadstage->is_final_accepted = false;
                $leadstage->save();

                return back()->with('success', __('Final accepted stage removed.'));
            }

            LeadStage::where('pipeline_id', $leadstage->pipeline_id)
                ->where('created_by', creatorId())
                ->update(['is_final_accepted' => false]);

            $leadstage->is_final_rejected = false;
            $leadstage->is_final_accepted = true;
            $leadstage->save();

            return back()->with('success', __('Final accepted stage updated successfully.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function setFinalRejected(LeadStage $leadstage)
    {
        if (Auth::user()->can('edit-lead-stages') && $leadstage->created_by == creatorId()) {
            if ($leadstage->is_final_rejected) {
                $leadstage->is_final_rejected = false;
                $leadstage->save();

                return back()->with('success', __('Final rejected stage removed.'));
            }

            LeadStage::where('pipeline_id', $leadstage->pipeline_id)
                ->where('created_by', creatorId())
                ->update(['is_final_rejected' => false]);

            $leadstage->is_final_accepted = false;
            $leadstage->is_final_rejected = true;
            $leadstage->save();

            return back()->with('success', __('Final rejected stage updated successfully.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateLeadStageRequest $request, LeadStage $leadstage)
    {
        if(Auth::user()->can('edit-lead-stages')){
            $validated = $request->validated();

            $oldPipelineId = $leadstage->pipeline_id;
            $newPipelineId = $validated['pipeline_id'];
            $oldOrder = $leadstage->order;

            if ($oldPipelineId != $newPipelineId) {
                $maxOrder = LeadStage::where('pipeline_id', $newPipelineId)
                    ->where('created_by', creatorId())
                    ->max('order') ?? 0;
                
                $leadstage->order = $maxOrder + 1;
                
                LeadStage::where('pipeline_id', $oldPipelineId)
                    ->where('created_by', creatorId())
                    ->where('order', '>', $oldOrder)
                    ->decrement('order');
            }

            if (isset($validated['final_type'])) {
                $isFinalAccepted = $validated['final_type'] === 'accepted';
                $isFinalRejected = $validated['final_type'] === 'rejected';
            } else {
                $isFinalAccepted = isset($validated['is_final_accepted']) ? (bool)$validated['is_final_accepted'] : $leadstage->is_final_accepted;
                $isFinalRejected = isset($validated['is_final_rejected']) ? (bool)$validated['is_final_rejected'] : $leadstage->is_final_rejected;
            }

            if ($isFinalAccepted) {
                LeadStage::where('pipeline_id', $newPipelineId)
                    ->where('created_by', creatorId())
                    ->where('id', '!=', $leadstage->id)
                    ->update(['is_final_accepted' => false]);
                $isFinalRejected = false;
            } elseif ($isFinalRejected) {
                LeadStage::where('pipeline_id', $newPipelineId)
                    ->where('created_by', creatorId())
                    ->where('id', '!=', $leadstage->id)
                    ->update(['is_final_rejected' => false]);
            }

            $leadstage->name              = $validated['name'];
            $leadstage->pipeline_id       = $newPipelineId;
            $leadstage->is_final_accepted = $isFinalAccepted;
            $leadstage->is_final_rejected = $isFinalRejected;
            $leadstage->save();

            UpdateLeadStage::dispatch($request, $leadstage);

            return back()->with('success', __('The lead stage details are updated successfully.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(LeadStage $leadstage)
    {
        if(Auth::user()->can('delete-lead-stages')){

            DestroyLeadStage::dispatch($leadstage);
            $leadstage->delete();

            return back()->with('success', __('The lead stage has been deleted.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function updateOrder(Request $request)
    {
        if(Auth::user()->can('edit-lead-stages')){
            $request->validate([
                'stage_ids' => 'required|array',
                'pipeline_id' => 'required|integer'
            ]);

            foreach($request->stage_ids as $index => $stageId) {
                LeadStage::where('id', $stageId)
                    ->where('pipeline_id', $request->pipeline_id)
                    ->where('created_by', creatorId())
                    ->update(['order' => $index + 1]);
            }
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }
}