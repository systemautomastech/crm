<?php

namespace Automas\SmartReports\Services\ReportGenerators;

use Automas\Taskly\Models\Project;

class ProjectProgressReportGenerator
{
    public function getReportData(array $filters)
    {
        $data = $this->getData($filters);

        // Attach milestones and tasks for each project
        $detailedData = $data->map(function ($project) {
            $milestones = \Automas\Taskly\Models\ProjectMilestone::where('project_id', $project->id)
                ->orderBy('start_date')
                ->get(['id', 'title', 'cost', 'start_date', 'end_date', 'summary', 'status', 'progress']);

            $tasks = \Automas\Taskly\Models\ProjectTask::where('project_id', $project->id)
                ->leftJoin('task_stages', 'task_stages.id', '=', 'project_tasks.stage_id')
                ->select(
                    'project_tasks.id',
                    'project_tasks.title',
                    'project_tasks.priority',
                    'project_tasks.assigned_to',
                    'project_tasks.duration',
                    'project_tasks.description',
                    'project_tasks.milestone_id',
                    'project_tasks.stage_id',
                    'task_stages.name as stage_name',
                    'task_stages.complete as is_complete',
                    'task_stages.color as stage_color'
                )
                ->orderBy('project_tasks.created_at', 'desc')
                ->get()
                ->map(function ($task) {
                    $assignedUsers = [];
                    if ($task->assigned_to) {
                        $decoded = json_decode($task->assigned_to, true);
                        $userIds = is_array($decoded) ? $decoded : explode(',', $task->assigned_to);
                        $userIds = array_filter(array_map('trim', $userIds));
                        $assignedUsers = \App\Models\User::whereIn('id', $userIds)
                            ->pluck('name')
                            ->toArray();
                    }
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'priority' => $task->priority,
                        'assigned_to_names' => implode(', ', $assignedUsers),
                        'duration' => $task->duration,
                        'description' => $task->description,
                        'milestone_id' => $task->milestone_id,
                        'stage_name' => $task->stage_name ?? 'Unassigned',
                        'stage_color' => $task->stage_color ?? '#6b7280',
                        'is_complete' => (bool) $task->is_complete,
                    ];
                });

            $projectArray = $project->toArray();
            $projectArray['milestones'] = $milestones;
            $projectArray['tasks'] = $tasks;
            return $projectArray;
        });

        return [
            'data'    => $detailedData,
            'summary' => $this->getSummary($data),
        ];
    }

    public function getData(array $filters)
    {
        $query = Project::query()
            ->leftJoin('project_tasks', 'projects.id', '=', 'project_tasks.project_id')
            ->leftJoin('task_stages', 'task_stages.id', '=', 'project_tasks.stage_id')
            ->where('projects.created_by', creatorId())
            ->select(
                'projects.id',
                'projects.name as project_name',
                'projects.description',
                'projects.budget',
                'projects.start_date',
                'projects.end_date',
                'projects.status',
                'projects.created_at',
                \DB::raw('COUNT(DISTINCT project_tasks.id) as total_tasks'),
                \DB::raw('COUNT(DISTINCT CASE WHEN task_stages.complete = 1 THEN project_tasks.id END) as completed_tasks'),
                \DB::raw('COUNT(DISTINCT CASE WHEN task_stages.complete = 0 OR task_stages.id IS NULL THEN project_tasks.id END) as pending_tasks'),
                \DB::raw('ROUND(
                    COUNT(DISTINCT CASE WHEN task_stages.complete = 1 THEN project_tasks.id END) * 100.0 /
                    NULLIF(COUNT(DISTINCT project_tasks.id), 0)
                , 2) as progress_percentage'),
                \DB::raw('(SELECT COUNT(*) FROM project_milestones WHERE project_milestones.project_id = projects.id) as total_milestones'),
                \DB::raw("(SELECT COUNT(*) FROM project_milestones WHERE project_milestones.project_id = projects.id AND project_milestones.status = 'Complete') as completed_milestones"),
                \DB::raw('(SELECT COALESCE(SUM(cost), 0) FROM project_milestones WHERE project_milestones.project_id = projects.id) as milestone_cost'),
                \DB::raw('DATEDIFF(projects.end_date, CURDATE()) as days_remaining'),
                \DB::raw("CASE
                    WHEN projects.end_date < CURDATE() AND projects.status != 'Finished' THEN 'Delayed'
                    WHEN projects.end_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND projects.status != 'Finished' THEN 'At Risk'
                    ELSE 'On Track'
                END as health_status")
            )
            ->groupBy(
                'projects.id', 'projects.name', 'projects.description',
                'projects.budget', 'projects.start_date', 'projects.end_date',
                'projects.status', 'projects.created_at'
            );

        if (!empty($filters['start_date'])) {
            $query->where('projects.start_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('projects.start_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->whereIn('projects.status', $filters['status']);
        }

        if (!empty($filters['project_ids'])) {
            $query->whereIn('projects.id', $filters['project_ids']);
        }

        return $query->orderBy('projects.start_date', 'desc')->get();
    }

    public function getSummary($data)
    {
        $total          = $data->count();
        $totalTasks     = $data->sum('total_tasks');
        $completedTasks = $data->sum('completed_tasks');

        return [
            'total_projects'       => $total,
            'ongoing_projects'     => $data->where('status', 'Ongoing')->count(),
            'onhold_projects'      => $data->where('status', 'Onhold')->count(),
            'finished_projects'    => $data->where('status', 'Finished')->count(),
            'on_track_projects'    => $data->where('health_status', 'On Track')->count(),
            'at_risk_projects'     => $data->where('health_status', 'At Risk')->count(),
            'delayed_projects'     => $data->where('health_status', 'Delayed')->count(),
            'total_budget'         => round($data->sum('budget'), 2),
            'total_milestone_cost' => round($data->sum('milestone_cost'), 2),
            'total_tasks'          => $totalTasks,
            'completed_tasks'      => $completedTasks,
            'avg_progress'         => $total > 0 ? round($data->avg('progress_percentage'), 2) : 0,
            'overall_completion'   => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0,
        ];
    }

    public function getFilterOptions()
    {
        $projects = Project::where('created_by', creatorId())
            ->orderBy('name')->get(['id', 'name', 'status'])
            ->map(fn($p) => ['value' => $p->id, 'label' => $p->name]);

        return [
            'projects' => $projects,
            'statuses' => [
                ['value' => 'Ongoing',  'label' => 'Ongoing'],
                ['value' => 'Onhold',   'label' => 'On Hold'],
                ['value' => 'Finished', 'label' => 'Finished'],
            ],
        ];
    }
}