<?php

namespace Automas\SalesOrder\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\SalesOrder\Models\SalesOrder;

class DestroySalesOrder
{
    use Dispatchable;

    public function __construct(
        public SalesOrder $salesOrder
    ) {}
}