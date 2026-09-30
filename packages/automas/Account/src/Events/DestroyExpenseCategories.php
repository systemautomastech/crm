<?php

namespace Automas\Account\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Automas\Account\Models\ExpenseCategories;

class DestroyExpenseCategories
{
    use Dispatchable;

    public function __construct(
        public ExpenseCategories $expenseCategories
    ) {}
}
