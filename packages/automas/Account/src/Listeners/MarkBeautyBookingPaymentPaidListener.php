<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\BeautySpaManagement\Events\MarkBeautyBookingPaymentPaid;

class MarkBeautyBookingPaymentPaidListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(MarkBeautyBookingPaymentPaid $event)
    {

        if(Module_is_active('Account'))
        {
            $bankAccountId = $event->booking->bank_account_id ?? null;
            if($bankAccountId) {
                $this->bankTransactionsService->createBeautyBookingPayment($event->booking, $bankAccountId);
                $this->journalService->createBeautyBookingPaymentJournal($event->booking, $bankAccountId);
            }
        }
    }
}
