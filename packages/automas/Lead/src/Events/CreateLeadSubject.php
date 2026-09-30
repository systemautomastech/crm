<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\LeadSubject;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class CreateLeadSubject
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public LeadSubject $subject
    ) {}
}
