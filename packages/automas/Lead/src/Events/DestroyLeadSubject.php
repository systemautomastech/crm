<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\LeadSubject;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyLeadSubject
{
    use Dispatchable;

    public function __construct(
        public LeadSubject $subject
    ) {}
}
