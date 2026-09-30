<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Automas\Lead\Models\LeadStage;

class LeadMoved
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Lead $lead,
        public LeadStage $oldStage
    ) {}
}