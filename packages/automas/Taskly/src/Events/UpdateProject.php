<?php

namespace Automas\Taskly\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Automas\Taskly\Models\Project;

class UpdateProject
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Project $project
    ) {}
}
