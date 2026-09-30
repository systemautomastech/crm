<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyUserLead
{
    use Dispatchable;

    public function __construct(
        public Lead $lead,
    ) {}
}