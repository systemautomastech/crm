<?php

namespace Automas\Taskly\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use Automas\Taskly\Models\Project;
use Automas\Taskly\Models\ProjectTask;
use Automas\Taskly\Models\ProjectBug;
use Automas\Taskly\Models\ProjectMilestone;
use Automas\Taskly\Models\ProjectPayment;
use Automas\Taskly\Models\ProjectPaymentItem;

class ProjectReportController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->can('manage-project-report')) {
            $allProjects = Project::query()
                ->where('created_by', creatorId())
                ->when($request->get('name'), fn($q) => $q->where('name', 'like', '%' . $request->get('name') . '%'))
                ->when($request->get('status'), fn($q) => $q->where('status', $request->get('status')))
                ->when($request->get('date_from'), fn($q) => $q->whereDate('start_date', '>=', $request->get('date_from')))
                ->when($request->get('date_to'), fn($q) => $q->whereDate('end_date', '<=', $request->get('date_to')))
                ->get();

            $allProjectIds = $allProjects->pluck('id');

            $stats = [
                'total'           => $allProjects->count(),
                'ongoing'         => $allProjects->where('status', 'Ongoing')->count(),
                'onhold'          => $allProjects->where('status', 'Onhold')->count(),
                'finished'        => $allProjects->where('status', 'Finished')->count(),
                'overdue'         => $allProjects->filter(
                    fn($p) => $p->end_date && $p->end_date < now() && $p->status !== 'Finished'
                )->count(),
                'total_budget'    => (float) $allProjects->sum('budget'),
                'total_collected' => (float) ProjectPayment::whereIn('project_id', $allProjectIds)
                    ->where('status', 'posted')
                    ->sum('total_amount'),
            ];

            $projects = Project::query()
                ->where('created_by', creatorId())
                ->with('teamMembers')
                ->select('id', 'name', 'start_date', 'end_date', 'status', 'budget')
                ->when($request->get('name'), fn($q) => $q->where('name', 'like', '%' . $request->get('name') . '%'))
                ->when($request->get('status'), fn($q) => $q->where('status', $request->get('status')))
                ->when($request->get('date_from'), fn($q) => $q->whereDate('start_date', '>=', $request->get('date_from')))
                ->when($request->get('date_to'), fn($q) => $q->whereDate('end_date', '<=', $request->get('date_to')))
                ->when(
                    $request->get('sort'),
                    fn($q) => $q->orderBy($request->get('sort'), $request->get('direction', 'asc')),
                    fn($q) => $q->latest()
                )
                ->paginate($request->get('per_page', 10))
                ->withQueryString();

            return Inertia::render('Taskly/Report/Index', [
                'stats'    => $stats,
                'projects' => $projects->through(fn($project) => [
                    'id'                 => $project->id,
                    'name'               => $project->name,
                    'start_date'         => $project->start_date?->format('Y-m-d'),
                    'end_date'           => $project->end_date?->format('Y-m-d'),
                    'status'             => $project->status,
                    'budget'             => $project->budget,
                    'team_members'       => $project->teamMembers->map(fn($m) => [
                        'id'     => $m->id,
                        'name'   => $m->name,
                        'avatar' => $m->avatar ?? null,
                    ])->toArray(),
                    'tasks_count'        => $this->getTasksCount($project->id),
                    'bugs_count'         => $this->getBugsCount($project->id),
                    'milestones_count'   => $this->getMilestonesCount($project->id),
                    'total_payment'      => $this->getTotalPayment($project->id),
                    'is_overdue'         => $project->end_date && $project->end_date < now() && $project->status !== 'Finished',
                    'budget_used_pct'    => $this->getBudgetUsedPct($project->id, $project->budget),
                ]),
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    public function show($id)
    {
        if (Auth::user()->can('view-project-report')) {
            $project = Project::where('created_by', creatorId())
                ->with([
                    'tasks.taskStage',
                    'tasks.subtasks',
                    'bugs.bugStage',
                    'teamMembers',
                    'milestones',
                ])->findOrFail($id);

            $now = now()->format('Y-m-d');

            // ── Task charts ────────────────────────────────────────────
            $taskStatusData = $project->tasks
                ->groupBy(fn($t) => $t->taskStage?->name ?? 'No Stage')
                ->map(fn($tasks, $name) => [
                    'name'  => $name,
                    'value' => $tasks->count(),
                    'color' => $tasks->first()->taskStage?->color ?? '#6b7280',
                ])->values()->toArray();

            $taskPriorityData = $project->tasks
                ->groupBy('priority')
                ->map(fn($tasks, $p) => ['name' => $p ?: 'None', 'value' => $tasks->count()])
                ->values()->toArray();

            // ── Bug charts ─────────────────────────────────────────────
            $bugStatusData = $project->bugs
                ->groupBy(fn($b) => $b->bugStage?->name ?? 'No Stage')
                ->map(fn($bugs, $name) => [
                    'name'  => $name,
                    'value' => $bugs->count(),
                    'color' => $bugs->first()->bugStage?->color ?? '#6b7280',
                ])->values()->toArray();

            $bugPriorityData = $project->bugs
                ->groupBy('priority')
                ->map(fn($bugs, $p) => ['name' => $p ?: 'None', 'value' => $bugs->count()])
                ->values()->toArray();

            // ── Project stats ──────────────────────────────────────────
            $totalTasks     = $project->tasks->count();
            $completedTasks = $project->tasks->filter(fn($t) => $t->taskStage?->complete)->count();
            $totalBugs      = $project->bugs->count();
            $resolvedBugs   = $project->bugs->filter(fn($b) => $b->bugStage?->complete)->count();

            $overdueTasks = $project->tasks->filter(function ($task) use ($now) {
                if (!$task->duration || !$task->taskStage || $task->taskStage->complete) return false;
                if (strpos($task->duration, ' - ') === false) return false;
                return trim(explode(' - ', $task->duration)[1]) < $now;
            })->count();

            $daysLeft = $project->end_date
                ? (int) now()->diffInDays($project->end_date, false)
                : null;

            $projectStats = [
                'total_tasks'         => $totalTasks,
                'completed_tasks'     => $completedTasks,
                'in_progress_tasks'   => $totalTasks - $completedTasks,
                'team_members'        => $project->teamMembers->count(),
                'total_bugs'          => $totalBugs,
                'resolved_bugs'       => $resolvedBugs,
                'open_bugs'           => $totalBugs - $resolvedBugs,
                'overdue_tasks'       => $overdueTasks,
                'total_milestones'    => $project->milestones->count(),
                'complete_milestones' => $project->milestones->where('status', 'Complete')->count(),
                'days_left'           => $daysLeft,
            ];

            // ── Task list ──────────────────────────────────────────────
            $tasksData = $project->tasks->map(function ($task) use ($now) {
                $userIds = $task->assigned_to
                    ? (is_array($task->assigned_to) ? $task->assigned_to : json_decode($task->assigned_to, true))
                    : [];
                $assignedUsers = User::whereIn('id', $userIds)
                    ->get(['id', 'name', 'avatar'])
                    ->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'avatar' => $u->avatar ?? null])
                    ->values()->toArray();

                $startDate = $endDate = null;
                $isOverdue = false;
                if ($task->duration && strpos($task->duration, ' - ') !== false) {
                    [$s, $e]   = explode(' - ', $task->duration);
                    $startDate = trim($s);
                    $endDate   = trim($e);
                    $isOverdue = $endDate < $now && !($task->taskStage?->complete);
                }

                $totalSub = $task->subtasks->count();
                $doneSub  = $task->subtasks->where('is_completed', true)->count();

                return [
                    'id'             => $task->id,
                    'title'          => $task->title,
                    'priority'       => $task->priority,
                    'stage'          => $task->taskStage?->name ?? 'No Stage',
                    'stage_color'    => $task->taskStage?->color ?? '#6b7280',
                    'is_complete'    => (bool) ($task->taskStage?->complete),
                    'assigned_users' => $assignedUsers,
                    'start_date'     => $startDate,
                    'end_date'       => $endDate,
                    'is_overdue'     => $isOverdue,
                    'subtask_total'  => $totalSub,
                    'subtask_done'   => $doneSub,
                    'subtask_pct'    => $totalSub > 0 ? round(($doneSub / $totalSub) * 100) : null,
                ];
            })->values()->toArray();

            // ── Bug list ───────────────────────────────────────────────
            $bugsData = $project->bugs->map(function ($bug) {
                $userIds = $bug->assigned_to
                    ? (is_array($bug->assigned_to) ? $bug->assigned_to : json_decode($bug->assigned_to, true))
                    : [];
                $assignedUsers = User::whereIn('id', $userIds)
                    ->get(['id', 'name', 'avatar'])
                    ->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'avatar' => $u->avatar ?? null])
                    ->values()->toArray();

                return [
                    'id'             => $bug->id,
                    'title'          => $bug->title,
                    'priority'       => $bug->priority,
                    'stage'          => $bug->bugStage?->name ?? 'No Stage',
                    'stage_color'    => $bug->bugStage?->color ?? '#6b7280',
                    'is_resolved'    => (bool) ($bug->bugStage?->complete),
                    'assigned_users' => $assignedUsers,
                    'created_at'     => $bug->created_at->format('Y-m-d'),
                ];
            })->values()->toArray();

            // ── Team productivity ──────────────────────────────────────
            $usersData = $project->teamMembers->map(function ($user) use ($project) {
                $uid = (string) $user->id;

                $assignedTasks = $project->tasks->filter(function ($task) use ($uid) {
                    $ids = is_array($task->assigned_to)
                        ? $task->assigned_to
                        : json_decode($task->assigned_to, true);
                    return $ids && in_array($uid, array_map('strval', $ids));
                });

                $assignedBugs = $project->bugs->filter(function ($bug) use ($uid) {
                    $ids = is_array($bug->assigned_to)
                        ? $bug->assigned_to
                        : json_decode($bug->assigned_to, true);
                    return $ids && in_array($uid, array_map('strval', $ids));
                });

                return [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'avatar'         => $user->avatar ?? null,
                    'assigned_tasks' => $assignedTasks->count(),
                    'done_tasks'     => $assignedTasks->filter(fn($t) => $t->taskStage?->complete)->count(),
                    'assigned_bugs'  => $assignedBugs->count(),
                    'resolved_bugs'  => $assignedBugs->filter(fn($b) => $b->bugStage?->complete)->count(),
                ];
            });

            // ── Milestones + billed amount ─────────────────────────────
            $milestoneIds     = $project->milestones->pluck('id');
            $milestonePayments = ProjectPaymentItem::whereIn('milestone_id', $milestoneIds)
                ->selectRaw('milestone_id, SUM(total_amount) as billed')
                ->groupBy('milestone_id')
                ->pluck('billed', 'milestone_id');

            $milestonesData = $project->milestones->map(function ($milestone) use ($milestonePayments, $now) {
                return [
                    'id'            => $milestone->id,
                    'name'          => $milestone->title,
                    'progress'      => $milestone->progress ?? 0,
                    'cost'          => (float) ($milestone->cost ?? 0),
                    'amount_billed' => (float) ($milestonePayments[$milestone->id] ?? 0),
                    'status'        => $milestone->status,
                    'start_date'    => $milestone->start_date?->format('Y-m-d'),
                    'end_date'      => $milestone->end_date?->format('Y-m-d'),
                    'is_overdue'    => $milestone->end_date
                        && $milestone->end_date->format('Y-m-d') < $now
                        && $milestone->status !== 'Complete',
                ];
            });

            // ── Financial ─────────────────────────────────────────────
            $payments        = ProjectPayment::where('project_id', $project->id)
                ->orderBy('payment_date', 'desc')
                ->get();
            $totalInvoiced   = (float) $payments->sum('total_amount');
            $totalCollected  = (float) $payments->where('status', 'posted')->sum('total_amount');

            $financialData = [
                'budget'               => (float) ($project->budget ?? 0),
                'milestone_cost_total' => (float) $project->milestones->sum('cost'),
                'total_invoiced'       => $totalInvoiced,
                'total_collected'      => $totalCollected,
                'outstanding'          => round($totalInvoiced - $totalCollected, 2),
                'payment_list'         => $payments->map(fn($p) => [
                    'id'           => $p->id,
                    'number'       => $p->payment_number,
                    'date'         => $p->payment_date?->format('Y-m-d'),
                    'due_date'     => $p->due_date?->format('Y-m-d'),
                    'total_amount' => (float) $p->total_amount,
                    'status'       => $p->status,
                    'is_overdue'   => $p->due_date && $p->due_date < now() && $p->status !== 'posted',
                ])->values()->toArray(),
            ];

            return Inertia::render('Taskly/Report/View', [
                'project'          => [
                    'id'          => $project->id,
                    'name'        => $project->name,
                    'description' => $project->description,
                    'start_date'  => $project->start_date?->format('Y-m-d'),
                    'end_date'    => $project->end_date?->format('Y-m-d'),
                    'status'      => $project->status,
                    'budget'      => $project->budget,
                    'is_overdue'  => $project->end_date
                        && $project->end_date->format('Y-m-d') < $now
                        && $project->status !== 'Finished',
                ],
                'taskStatusData'   => $taskStatusData,
                'taskPriorityData' => $taskPriorityData,
                'bugStatusData'    => $bugStatusData,
                'bugPriorityData'  => $bugPriorityData,
                'projectStats'     => $projectStats,
                'usersData'        => $usersData,
                'milestonesData'   => $milestonesData,
                'tasksData'        => $tasksData,
                'bugsData'         => $bugsData,
                'financialData'    => $financialData,
            ]);
        } else {
            return back()->with('error', __('Permission denied'));
        }
    }

    // ── Private helpers ────────────────────────────────────────────────

    private function getTasksCount($projectId): string
    {
        $total     = ProjectTask::where('project_id', $projectId)->count();
        $completed = ProjectTask::where('project_id', $projectId)
            ->whereHas('taskStage', fn($q) => $q->where('complete', 1))
            ->count();

        return "{$completed}/{$total}";
    }

    private function getBugsCount($projectId): string
    {
        $total     = ProjectBug::where('project_id', $projectId)->count();
        $completed = ProjectBug::where('project_id', $projectId)
            ->whereHas('bugStage', fn($q) => $q->where('complete', 1))
            ->count();

        return "{$completed}/{$total}";
    }

    private function getTotalPayment($projectId): float
    {
        return (float) ProjectPayment::where('project_id', $projectId)
            ->where('status', 'posted')
            ->sum('total_amount');
    }

    private function getMilestonesCount($projectId): string
    {
        $total     = ProjectMilestone::where('project_id', $projectId)->count();
        $completed = ProjectMilestone::where('project_id', $projectId)
            ->where('status', 'Complete')
            ->count();

        return "{$completed}/{$total}";
    }

    private function getBudgetUsedPct($projectId, $budget): int
    {
        if (!$budget || $budget <= 0) return 0;
        $used = (float) ProjectPayment::where('project_id', $projectId)
            ->where('status', 'posted')
            ->sum('total_amount');

        return (int) min(100, round(($used / $budget) * 100));
    }
}
