<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Automas\Account\Models\ChartOfAccount;

class DestroyChartOfAccount
{
    use Dispatchable;

    public function __construct(
        public ChartOfAccount $chartofaccount
    ) {}
}
