<?php

namespace Automas\Taskly\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\Taskly\Models\TaskStage;

class DestroyTaskStage
{
    use Dispatchable;

    public function __construct(
        public TaskStage $taskStage,
    ) {}
}