<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Automas\Account\Models\Customer;

class CreateCustomer
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public Customer $customer
    ) {}
}