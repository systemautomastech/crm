<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\Account\Models\Vendor;

class DestroyVendor
{
    use Dispatchable;

    public function __construct(
        public Vendor $vendor
    ) {}
}