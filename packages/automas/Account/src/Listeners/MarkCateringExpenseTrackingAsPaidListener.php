<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\CateringManagement\Events\MarkCateringExpenseTrackingAsPaid;

class MarkCateringExpenseTrackingAsPaidListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(MarkCateringExpenseTrackingAsPaid $event)
    {
        if(Module_is_active('Account'))
        {
            $this->bankTransactionsService->cateringExpenseTracking($event->cateringexpensetracking);
            $this->journalService->cateringExpenseTrackingJournal($event->cateringexpensetracking);
        }
    }
}
