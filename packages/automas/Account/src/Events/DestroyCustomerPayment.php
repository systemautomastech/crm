<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Account\Models\CustomerPayment;

class DestroyCustomerPayment
{
    use Dispatchable;

    public function __construct(
        public CustomerPayment $customerPayment
    ) {}
}
