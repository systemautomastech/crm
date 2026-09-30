<?php

namespace Automas\Taskly\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\Taskly\Models\BugStage;

class DestroyBugStage
{
    use Dispatchable;

    public function __construct(
        public BugStage $bugStage,
    ) {}
}