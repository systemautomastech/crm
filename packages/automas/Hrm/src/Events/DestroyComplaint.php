<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\Complaint;

class DestroyComplaint
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public Complaint $complaint
    )
    {}
}