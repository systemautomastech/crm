<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\PettyCashManagement\Events\ApprovePettyCash;

class ApprovePettyCashListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(ApprovePettyCash $event)
    {
        if(Module_is_active('Account'))
        {
            $this->bankTransactionsService->approvePettyCash($event->pettycash);
            $this->journalService->approvePettyCashJournal($event->pettycash);
        }
    }
}
