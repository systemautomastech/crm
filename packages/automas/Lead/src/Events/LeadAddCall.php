<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Lead;
use Automas\Lead\Models\LeadCall;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class LeadAddCall
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Lead $lead,
    ) {}
}