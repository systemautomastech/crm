<?php

namespace Automas\Taskly\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Taskly\Models\ProjectMilestone;

class DestroyProjectMilestone
{
    use Dispatchable;

    public function __construct(
        public ProjectMilestone $milestone
    ) {}
}
