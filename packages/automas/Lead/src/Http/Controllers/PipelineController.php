<?php

namespace Automas\Lead\Http\Controllers;

use Automas\Lead\Models\Pipeline;
use Automas\Lead\Http\Requests\StorePipelineRequest;
use Automas\Lead\Http\Requests\UpdatePipelineRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Lead\Events\CreatePipeline;
use Automas\Lead\Events\UpdatePipeline;
use Automas\Lead\Events\DestroyPipeline;
use App\Models\User;
use Automas\Lead\Models\LeadStage;
use Automas\Lead\Models\DealStage;


class PipelineController extends Controller
{
    public function index()
    {
        if(Auth::user()->can('manage-pipelines')){
            $pipelines = Pipeline::select('id', 'name', 'is_default', 'created_at')
                ->where(function($q) {
                    if(Auth::user()->can('manage-any-pipelines')) {
                        $q->where('created_by', creatorId());
                    } elseif(Auth::user()->can('manage-own-pipelines')) {
                        $q->where('creator_id', Auth::id());
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->latest()
                ->get();

            return Inertia::render('Lead/SystemSetup/Pipelines/Index', [
                'pipelines' => $pipelines,

            ]);
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StorePipelineRequest $request)
    {
        if(Auth::user()->can('create-pipelines')){
            $validated = $request->validated();
            $isFirst = Pipeline::where('created_by', creatorId())->count() === 0;

            $pipeline             = new Pipeline();
            $pipeline->name       = $validated['name'];
            $pipeline->is_default = $isFirst;
            $pipeline->creator_id = Auth::id();
            $pipeline->created_by = creatorId();
            $pipeline->save();

            // Create default Lead Stages
            $defaultLeadStages = ['Draft', 'Sent', 'Open', 'Revised', 'Declined', 'Accepted'];
            foreach ($defaultLeadStages as $index => $stageName) {
                LeadStage::create([
                    'name'              => $stageName,
                    'pipeline_id'       => $pipeline->id,
                    'order'             => $index + 1,
                    'is_final_accepted' => $stageName === 'Accepted',
                    'is_final_rejected' => $stageName === 'Declined',
                    'creator_id'        => Auth::id(),
                    'created_by'        => creatorId(),
                ]);
            }

            // Create default Deal Stages
            $defaultDealStages = ['Initial Contact', 'Qualification', 'Meeting', 'Proposal', 'Close'];
            foreach ($defaultDealStages as $index => $stageName) {
                DealStage::create([
                    'name'       => $stageName,
                    'pipeline_id'=> $pipeline->id,
                    'order'      => $index + 1,
                    'creator_id' => Auth::id(),
                    'created_by' => creatorId(),
                ]);
            }

            CreatePipeline::dispatch($request, $pipeline);

            return redirect()->route('lead.pipelines.index')->with('success', __('The pipeline has been created successfully.'));
        }
        else{
            return redirect()->route('lead.pipelines.index')->with('error', __('Permission denied'));
        }
    }

    public function setDefault(Pipeline $pipeline)
    {
        if (Auth::user()->can('edit-pipelines') && $pipeline->created_by == creatorId()) {
            Pipeline::where('created_by', creatorId())->update(['is_default' => false]);
            $pipeline->is_default = true;
            $pipeline->save();

            User::where('created_by', creatorId())->update(['default_pipeline' => $pipeline->id]);

            return back()->with('success', __('Default pipeline updated successfully.'));
        }
        else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function update(UpdatePipelineRequest $request, Pipeline $pipeline)
    {
        if(Auth::user()->can('edit-pipelines')){
            $validated = $request->validated();
            $pipeline->name = $validated['name'];

            $pipeline->save();

            UpdatePipeline::dispatch($request, $pipeline);

            return back()->with('success', __('The pipeline details are updated successfully.'));
        }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(Pipeline $pipeline)
    {
        if(Auth::user()->can('delete-pipelines')){
                if (LeadStage::where('pipeline_id', $pipeline->id)->exists() || DealStage::where('pipeline_id', $pipeline->id)->exists()) {
                    return back()->with('error', __('Pipeline cannot be deleted because it has stages associated with it.'));
                }
                // Set default_pipeline to null for all users who have this pipeline as default
                User::where('default_pipeline', $pipeline->id)->update(['default_pipeline' => null]);

                DestroyPipeline::dispatch($pipeline);
                $pipeline->delete();

                return back()->with('success', __('The pipeline has been deleted.'));
            }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }


}