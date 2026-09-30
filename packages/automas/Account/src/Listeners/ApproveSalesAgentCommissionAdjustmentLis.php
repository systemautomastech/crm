<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;

class ApproveSalesAgentCommissionAdjustmentLis
{
    protected $journalService;
    protected $bankTransactionsService;


    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle($event)
    {
        if(Module_is_active('Account'))
        {
            $this->journalService->createCommissionAdjustmentJournal($event->adjustment);
            $this->bankTransactionsService->createCommissionAdjustmentBankTransaction($event->adjustment);
        }
    }
}
