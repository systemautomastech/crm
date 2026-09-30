<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\DealTask;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyDealTask
{
    use Dispatchable;

    public function __construct(
        public DealTask $dealTask
    ) {}
}