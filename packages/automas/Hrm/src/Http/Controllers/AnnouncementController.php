<?php

namespace Automas\Hrm\Http\Controllers;

use Illuminate\Http\Request;
use Automas\Hrm\Models\Announcement;
use Automas\Hrm\Http\Requests\StoreAnnouncementRequest;
use Automas\Hrm\Http\Requests\UpdateAnnouncementRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Automas\Hrm\Models\AnnouncementCategory;
use Automas\Hrm\Models\Department;
use Automas\Hrm\Events\CreateAnnouncement;
use Automas\Hrm\Events\DestroyAnnouncement;
use Automas\Hrm\Events\UpdateAnnouncement;
use Automas\Hrm\Models\Employee;

class AnnouncementController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage-announcements')) {
            $announcements = Announcement::query()
                ->with(['announcementCategory', 'departments', 'approvedBy'])
                ->where(function ($q) {
                    if (Auth::user()->can('manage-any-announcements')) {
                        $q->where('created_by', creatorId());
                    } elseif (Auth::user()->can('manage-own-announcements')) {
                        // Get current user's employee record to find their department
                        $employee = Employee::where('user_id', Auth::id())->first();
                        if ($employee && $employee->department_id) {
                            // Show announcements that are assigned to user's department
                            $q->whereHas('departments', function ($query) use ($employee) {
                                $query->where('department_id', $employee->department_id)->where('status', 'active');
                            });
                        } else {
                            // If user has no department, show no announcements
                            $q->whereRaw('1 = 0');
                        }
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                })
                ->when(request('title'), function ($q) {
                    $q->where(function ($query) {
                        $query->where('title', 'like', '%' . request('title') . '%');
                        $query->orWhere('description', 'like', '%' . request('title') . '%');
                    });
                })
                ->when(request('priority') !== null && request('priority') !== '', fn($q) => $q->where('priority', request('priority')))
                ->when(request('status') !== null && request('status') !== '', fn($q) => $q->where('status', request('status')))
                ->when(request('announcement_category_id') !== null && request('announcement_category_id') !== '', fn($q) => $q->where('announcement_category_id', request('announcement_category_id')))
                ->when(request('sort'), fn($q) => $q->orderBy(request('sort'), request('direction', 'asc')), fn($q) => $q->latest())
                ->paginate(request('per_page', 10))
                ->withQueryString();

            return Inertia::render('Hrm/Announcements/Index', [
                'announcements' => $announcements,
                'announcementcategories' => AnnouncementCategory::where('created_by', creatorId())->select('id', 'announcement_category as name')->get(),
                'departments' => Department::where('created_by', creatorId())->select('id', 'department_name as name')->get(),
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function store(StoreAnnouncementRequest $request)
    {
        if (Auth::user()->can('create-announcements')) {
            $validated = $request->validated();

            $announcement = new Announcement();
            $announcement->title = $validated['title'];
            $announcement->description = $validated['description'];
            $announcement->start_date = $validated['start_date'];
            $announcement->end_date = $validated['end_date'];
            $announcement->priority = $validated['priority'];
            $announcement->announcement_category_id = $validated['announcement_category_id'];

            $announcement->creator_id = Auth::id();
            $announcement->created_by = creatorId();
            $announcement->save();

            // Sync departments with creator info
            if (isset($validated['departments'])) {
                $departmentData = [];
                foreach ($validated['departments'] as $departmentId) {
                    $departmentData[$departmentId] = [
                        'creator_id' => Auth::id(),
                        'created_by' => creatorId(),
                    ];
                }
                $announcement->departments()->sync($departmentData);
            }
            CreateAnnouncement::dispatch($request, $announcement);

            return redirect()->route('hrm.announcements.index')->with('success', __('The announcement has been created successfully.'));
        } else {
            return redirect()->route('hrm.announcements.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        if (Auth::user()->can('edit-announcements')) {
            $validated = $request->validated();



            $announcement->title = $validated['title'];
            $announcement->description = $validated['description'];
            $announcement->start_date = $validated['start_date'];
            $announcement->end_date = $validated['end_date'];
            $announcement->priority = $validated['priority'];
            $announcement->announcement_category_id = $validated['announcement_category_id'];

            $announcement->save();            

            // Sync departments with creator info
            if (isset($validated['departments'])) {
                $departmentData = [];
                foreach ($validated['departments'] as $departmentId) {
                    $departmentData[$departmentId] = [
                        'creator_id' => Auth::id(),
                        'created_by' => creatorId(),
                    ];
                }
                $announcement->departments()->sync($departmentData);
            }

            UpdateAnnouncement::dispatch($request, $announcement);

            return redirect()->back()->with('success', __('The announcement details are updated successfully.'));
        } else {
            return redirect()->route('hrm.announcements.index')->with('error', __('Permission denied'));
        }
    }

    public function updateStatus(Request $request, Announcement $announcement)
    {
        if (Auth::user()->can('manage-announcements-status')) {
            $request->validate([
                'status' => 'required|in:draft,active,inactive'
            ]);

            $announcement->status = $request->status;
            $announcement->approved_by = Auth::id();
            $announcement->save();

            return redirect()->back()->with('success', __('The announcement status has been updated successfully.'));
        } else {
            return redirect()->route('hrm.announcements.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(Announcement $announcement)
    {
        if (Auth::user()->can('delete-announcements')) {
            DestroyAnnouncement::dispatch($announcement);
            $announcement->delete();

            return redirect()->back()->with('success', __('The announcement has been deleted.'));
        } else {
            return redirect()->route('hrm.announcements.index')->with('error', __('Permission denied'));
        }
    }

}
