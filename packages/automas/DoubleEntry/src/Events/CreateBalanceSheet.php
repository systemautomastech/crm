<?php

namespace Automas\DoubleEntry\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Automas\DoubleEntry\Models\BalanceSheet;

class CreateBalanceSheet
{
    use Dispatchable;

    public function __construct(
        public Request $request,
        public BalanceSheet $balanceSheet
    ) {}
}
