<?php

namespace Automas\Pos\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Automas\Pos\Models\PosBillingCounter;

class CreatePosBillingCounter
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Request $request,
        public PosBillingCounter $counter
    ) {}
}