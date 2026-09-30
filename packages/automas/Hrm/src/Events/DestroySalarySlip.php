<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\PayrollEntry;

class DestroySalarySlip
{
    use Dispatchable, SerializesModels;

    public function __construct(public PayrollEntry $payrollEntry)
    {}
}