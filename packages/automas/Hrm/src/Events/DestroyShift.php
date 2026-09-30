<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\Shift;

class DestroyShift
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public Shift $shift
    )
    {}
}