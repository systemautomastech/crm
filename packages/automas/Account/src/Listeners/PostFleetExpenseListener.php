<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\Fleet\Events\PostFleetExpense;

class PostFleetExpenseListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(PostFleetExpense $event)
    {
        if(Module_is_active('Account'))
        {
            $this->bankTransactionsService->createPostFleetExpense($event->expense);
            $this->journalService->createPostFleetExpenseJournal($event->expense);
        }
    }
}
