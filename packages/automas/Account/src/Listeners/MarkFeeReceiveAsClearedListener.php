<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\LegalCaseManagement\Events\MarkFeeReceiveAsCleared;

class MarkFeeReceiveAsClearedListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(MarkFeeReceiveAsCleared $event)
    {

        if(Module_is_active('Account'))
        {
            $this->bankTransactionsService->feeReceiveAsCleared($event->feeReceive);
            $this->journalService->feeReceiveAsClearedJournal($event->feeReceive);
        }
    }
}
