<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\Department;

class DestroyDepartment
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public Department $department
    )
    {}
}