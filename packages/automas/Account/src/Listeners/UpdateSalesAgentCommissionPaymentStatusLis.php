<?php

namespace Automas\Account\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;

class UpdateSalesAgentCommissionPaymentStatusLis
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
            $this->bankTransactionsService->createUpdateSalesAgentCommissionPayment($event->payment);
            $this->journalService->createUpdateSalesAgentCommissionPaymentJournal($event->payment);
        }
    }
}
