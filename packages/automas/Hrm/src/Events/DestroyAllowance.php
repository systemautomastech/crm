<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\Allowance;

class DestroyAllowance
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public Allowance $allowance
    )
    {}
}