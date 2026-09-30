<?php

namespace Automas\Quotation\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Automas\Quotation\Models\SalesQuotation;

class UpdateQuotation
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public SalesQuotation $quotation
    ) {}
}