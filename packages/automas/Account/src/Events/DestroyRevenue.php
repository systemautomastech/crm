<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Account\Models\Revenue;

class DestroyRevenue
{
    use Dispatchable;

    public function __construct(
        public Revenue $revenue
    ) {}
}
