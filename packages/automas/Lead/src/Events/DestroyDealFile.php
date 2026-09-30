<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Deal;
use Automas\Lead\Models\DealFile;
use Illuminate\Foundation\Events\Dispatchable;

class DestroyDealFile
{
    use Dispatchable;

    public function __construct(
        public Deal $deal,
    ) {}
}