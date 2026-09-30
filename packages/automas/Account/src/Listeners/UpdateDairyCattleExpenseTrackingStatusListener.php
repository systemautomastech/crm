<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\DairyCattleManagement\Events\UpdateDairyCattleExpenseTrackingStatus;

class UpdateDairyCattleExpenseTrackingStatusListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(UpdateDairyCattleExpenseTrackingStatus $event)
    {
        if(Module_is_active('Account'))
        {
            $this->bankTransactionsService->dairyCattleExpenseTracking($event->expenseTracking);
            $this->journalService->dairyCattleExpenseTrackingJournal($event->expenseTracking);
        }
    }
}
