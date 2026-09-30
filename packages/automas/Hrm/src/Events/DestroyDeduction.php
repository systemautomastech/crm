<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\Deduction;

class DestroyDeduction
{
    use Dispatchable, SerializesModels;

    public function __construct(
          public Deduction $deduction
    )
    {}
}