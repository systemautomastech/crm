<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\LeadStage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class UpdateLeadStage
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public LeadStage $leadStage
    ) {}
}