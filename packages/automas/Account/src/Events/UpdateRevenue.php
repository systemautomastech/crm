<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Automas\Account\Models\Revenue;

class UpdateRevenue
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Revenue $revenue
    ) {}
}
