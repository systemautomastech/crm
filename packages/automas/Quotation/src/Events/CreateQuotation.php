<?php

namespace Automas\Quotation\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\Quotation\Models\SalesQuotation;
use Illuminate\Http\Request;

class CreateQuotation
{

    use Dispatchable;

    public function __construct(
        public Request $request,
        public SalesQuotation $quotation
    ) {}
}