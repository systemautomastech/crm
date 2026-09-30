<?php

namespace Automas\Taskly\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Taskly\Models\TaskComment;

class DestroyTaskComment
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TaskComment $comment
    ) {}
}
