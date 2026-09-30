<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\Resignation;

class DestroyResignation
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public Resignation $resignation
    )
    {}
}