<?php

namespace Automas\DoubleEntry\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\DoubleEntry\Models\BalanceSheet;

class FinalizeBalanceSheet
{
    use Dispatchable;

    public function __construct(
        public BalanceSheet $balanceSheet
    ) {}
}
