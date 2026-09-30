<?php

namespace Automas\DoubleEntry\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\DoubleEntry\Models\BalanceSheetNote;

class DestroyBalanceSheetNote
{
    use Dispatchable;

    public function __construct(
        public BalanceSheetNote $balanceSheetNote
    ) {}
}
