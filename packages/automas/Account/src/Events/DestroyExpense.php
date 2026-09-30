<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Account\Models\Expense;

class DestroyExpense
{
    use Dispatchable;

    public function __construct(
        public Expense $expense
    ) {}
}
