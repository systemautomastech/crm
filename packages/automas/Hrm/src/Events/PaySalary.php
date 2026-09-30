<?php

namespace Automas\Hrm\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Automas\Hrm\Models\PayrollEntry;

class PaySalary
{
    use Dispatchable, SerializesModels;

    public function __construct( 
        public Request $request,
        public PayrollEntry $payrollEntry
    )
    {}
}