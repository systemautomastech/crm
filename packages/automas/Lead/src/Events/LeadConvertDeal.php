<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Lead;
use Automas\Lead\Models\Deal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class LeadConvertDeal
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Lead $lead,
    ) {}
}