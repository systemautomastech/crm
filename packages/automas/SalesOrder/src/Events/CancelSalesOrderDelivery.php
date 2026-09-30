<?php

namespace Automas\SalesOrder\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\SalesOrder\Models\SalesOrderDelivery;

class CancelSalesOrderDelivery
{
    use Dispatchable;

    public function __construct(
        public SalesOrderDelivery $delivery
    ) {}
}
