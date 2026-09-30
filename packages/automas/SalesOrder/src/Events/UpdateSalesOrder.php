<?php

namespace Automas\SalesOrder\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Automas\SalesOrder\Models\SalesOrder;

class UpdateSalesOrder
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public SalesOrder $salesOrder
    ) {}
}