<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateLead
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Lead $lead
    ) {}
}