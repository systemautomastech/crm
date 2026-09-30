<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Lead;
use Automas\Lead\Models\LeadFile;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyLeadFile
{
    use Dispatchable;

    public function __construct(
        public Lead $lead,
    ) {}
}