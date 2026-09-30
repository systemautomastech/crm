<?php

namespace Automas\Lead\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Automas\Lead\Models\LeadTask;
use Inertia\Inertia;
use Automas\Lead\Http\Requests\StoreLeadTaskRequest;
use Automas\Lead\Http\Requests\UpdateLeadTaskRequest;
use Automas\Lead\Models\Lead;
use Automas\Lead\Models\LeadActivityLog;
use Automas\Lead\Events\CreateLeadTask;
use Automas\Lead\Events\UpdateLeadTask;
use Automas\Lead\Events\DestroyLeadTask;

class LeadTaskController extends Controller
{
    // public function index()
    // {
    //     if(Auth::user()->can('manage-lead-tasks')){
    //         $tasks = LeadTask::with(['lead'])
    //             ->where(function($q) {
    //                 if(Auth::user()->can('manage-any-lead-tasks')) {
    //                     $q->where('created_by', creatorId());
    //                 } elseif(Auth::user()->can('manage-own-lead-tasks')) {
    //                     $q->where(function($subQ) {
    //                         $subQ->where('creator_id', Auth::id())
    //                              ->orWhereIn('lead_id', function($leadQ) {
    //                                  $leadQ->select('lead_id')
    //                                        ->from('user_leads')
    //                                        ->where('user_id', Auth::id());
    //                              });
    //                     });
    //                 } else {
    //                     $q->whereRaw('1 = 0');
    //                 }
    //             })
    //             ->when(request('name'), fn($q) => $q->where('name', 'like', '%' . request('name') . '%'))
    //             ->when(request('priority'), fn($q) => $q->where('priority', request('priority')))
    //             ->when(request('status'), fn($q) => $q->where('status', request('status')))
    //             ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
    //             ->paginate(request('per_page', 10))
    //             ->withQueryString();

    //         return Inertia::render('Lead/Tasks/Index', [
    //             'tasks' => $tasks,
    //         ]);
    //     }
    //     else{
    //         return back()->with('error', __('Permission denied'));
    //     }
    // }

    public function index()
    {
        if (Auth::user()->can('manage-lead-tasks')) {
            $tasks = LeadTask::with(['lead'])
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-lead-tasks')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-lead-tasks')) {
                        $q->where(function ($subQ) {
                            $subQ->where('creator_id', Auth::id())
                                ->orWhereIn('lead_id', function ($leadQ) {
                                    $leadQ->select('lead_id')
                                        ->from('user_leads')
                                        ->where('user_id', Auth::id());
                                });
                        });
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('name'), fn($q) => $q->where('name', 'like', '%' . request('name') . '%'))
                ->when(request('priority'), fn($q) => $q->where('priority', request('priority')))
                ->when(request('status'), fn($q) => $q->where('status', request('status')))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('Lead/Tasks/LeadTasks', [
                'tasks' => $tasks,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }
    public function store(StoreLeadTaskRequest $request)
    {
        if (Auth::user()->can('create-lead-tasks')) {
            $usr = Auth::user();
            $validated = $request->validated();

            $leadTask = new LeadTask();
            $leadTask->lead_id = $validated['lead_id'];
            $leadTask->name = $validated['name'];
            $leadTask->date = $validated['date'];
            $leadTask->time = $validated['time'];
            $leadTask->priority = $validated['priority'];
            $leadTask->status = $validated['status'];
            $leadTask->created_by = creatorId();
            $leadTask->creator_id = Auth::id();
            $leadTask->save();
            CreateLeadTask::dispatch($request, $leadTask);
            LeadActivityLog::create(
                [
                    'user_id' => $usr->id,
                    'lead_id' => $validated['lead_id'],
                    'log_type' => 'Create Task',
                    'remark' => json_encode(['title' => $leadTask->name]),
                ]
            );

            return back()->with('success', __('The task has been created successfully.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateLeadTaskRequest $request, LeadTask $task)
    {
        if (Auth::user()->can('edit-lead-tasks')) {

            $validated = $request->validated();

            $task->name = $validated['name'];
            $task->date = $validated['date'];
            $task->time = $validated['time'];
            $task->priority = $validated['priority'];
            $task->status = $validated['status'];
            $task->save();

            UpdateLeadTask::dispatch($request, $task);

            return back()->with('success', __('The task details are updated successfully.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function destroy(LeadTask $task)
    {
        if (Auth::user()->can('delete-lead-tasks')) {
            $lead_id = $task->lead_id;
            DestroyLeadTask::dispatch($task);
            $task->delete();
            return redirect()->route('lead.leads.show', $lead_id)->with('success', __('The task has been deleted.'));
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }
}
