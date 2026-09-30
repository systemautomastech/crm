<?php

namespace Automas\Lead\Events;

use Automas\Lead\Models\Deal;
use Automas\Lead\Models\DealEmail;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;

class DealAddEmail
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Deal $deal,
        public DealEmail $deal_email,
    ) {}
}