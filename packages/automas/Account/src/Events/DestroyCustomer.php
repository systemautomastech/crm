<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\Account\Models\Customer;

class DestroyCustomer
{
    use Dispatchable;

    public function __construct(
        public Customer $customer
    ) {}
}