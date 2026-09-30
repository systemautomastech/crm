<?php

namespace Automas\Quotation\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\Quotation\Models\SalesQuotation;

class AcceptSalesQuotation
{
    use Dispatchable;
    public function __construct(
        public SalesQuotation $quotation
        ) {}
}