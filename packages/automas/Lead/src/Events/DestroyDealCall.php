<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\DealCall;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyDealCall
{
    use Dispatchable;

    public function __construct(
        public DealCall $dealCall
    ) {}
}