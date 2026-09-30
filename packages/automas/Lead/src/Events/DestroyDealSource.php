<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Deal;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyDealSource
{
    use Dispatchable;

    public function __construct(
        public Deal $deal,
    ) {}
}