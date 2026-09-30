<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\Account\Models\BankTransfer;

class ProcessBankTransfer
{
    use Dispatchable;

    public function __construct(
        public BankTransfer $bankTransfer
    ) {}
}