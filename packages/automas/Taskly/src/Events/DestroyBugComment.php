<?php

namespace Automas\Taskly\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Taskly\Models\BugComment;

class DestroyBugComment
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BugComment $comment
    ) {}
}
