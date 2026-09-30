<?php

namespace Automas\Account\Listeners;

use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\ParkingManagement\Events\UpdateParkingPaymentStatus;

class UpdateParkingPaymentStatusListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(UpdateParkingPaymentStatus $event)
    {
        if(Module_is_active('Account'))
        {
            $bankAccountId = $event->payment->bank_account_id ?? null;
            if($bankAccountId) {
                $this->bankTransactionsService->parkingBookingPayments($event->booking, $bankAccountId);
                $this->journalService->parkingBookingPaymentsJournal($event->booking, $bankAccountId);
            }
        }
    }
}
