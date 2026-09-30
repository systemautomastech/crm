<?php

namespace Automas\Quotation\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\Quotation\Models\SalesQuotation;
use App\Models\SalesInvoice;

class ConvertSalesQuotation
{
    use Dispatchable;

    public function __construct(
        public SalesQuotation $quotation,
        public SalesInvoice $invoice
    ) {}
}