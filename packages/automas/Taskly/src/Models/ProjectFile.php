<?php

namespace Automas\Taskly\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id',
        'bug_id',
        'task_id',
        'file_name',
        'file_path',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function bug()
    {
        return $this->belongsTo(ProjectBug::class, 'bug_id');
    }

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }
}