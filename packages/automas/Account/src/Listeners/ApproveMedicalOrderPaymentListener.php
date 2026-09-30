<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\MedicalLabManagement\Events\ApproveMedicalOrderPayment;

class ApproveMedicalOrderPaymentListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(ApproveMedicalOrderPayment $event)
    {
        if(Module_is_active('Account'))
        {
            $this->bankTransactionsService->medicalOrderPayment($event->medicalOrderPayment);
            $this->journalService->medicalOrderPaymentJournal($event->medicalOrderPayment);
        }
    }
}
