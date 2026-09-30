<?php

namespace Automas\Taskly\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Automas\Taskly\Models\TaskStage;

class UpdateTaskStage
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public TaskStage $taskStage
    ) {}
}