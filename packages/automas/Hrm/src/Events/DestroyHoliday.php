<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\Holiday;

class DestroyHoliday
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public Holiday $holiday
    )
    {}
}