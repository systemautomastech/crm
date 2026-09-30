<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Account\Models\AccountType;

class DestroyAccountType
{
    use Dispatchable;

    public function __construct(
        public AccountType $accounttype
    ) {}
}
