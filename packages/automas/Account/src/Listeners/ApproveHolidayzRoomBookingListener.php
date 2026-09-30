<?php

namespace Automas\Account\Listeners;

use Automas\Account\Models\BankAccount;
use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\Holidayz\Events\ApproveHolidayzRoomBooking;

class ApproveHolidayzRoomBookingListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(ApproveHolidayzRoomBooking $event)
    {
        if (Module_is_active('Account')) {
            $bankAccountId = $event->booking->bank_account_id ?? null;
            if ($bankAccountId) {
                $this->bankTransactionsService->createHolidayzBookingPayment($event->booking, $bankAccountId);
                $this->journalService->createHolidayzBookingPaymentJournal($event->booking, $bankAccountId);
            }
        }
    }
}
