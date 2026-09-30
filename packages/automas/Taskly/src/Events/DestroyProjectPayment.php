<?php

namespace Automas\Taskly\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Taskly\Models\ProjectPayment;

class DestroyProjectPayment
{
    use Dispatchable;

    public function __construct(
        public ProjectPayment $projectPayment
    ) {}
}
