<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\Account\Models\VendorPayment;

class DestroyVendorPayment
{
    use Dispatchable;

    public function __construct(
        public VendorPayment $vendorPayment
    ) {}
}