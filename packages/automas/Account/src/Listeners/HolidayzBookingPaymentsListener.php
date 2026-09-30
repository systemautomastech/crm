<?php

namespace Automas\Account\Listeners;

use Automas\Account\Models\BankAccount;
use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\Holidayz\Events\HolidayzBookingPayments;

class HolidayzBookingPaymentsListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(HolidayzBookingPayments $event)
    {
        if (Module_is_active('Account', $event->booking->created_by)) {
            $bankAccount = BankAccount::where('payment_gateway', $event->booking->payment_method)->where('created_by', $event->booking->created_by)->first();
            if ($bankAccount) {
                $this->bankTransactionsService->createHolidayzBookingPayment($event->booking, $bankAccount->id);
                $this->journalService->createHolidayzBookingPaymentJournal($event->booking, $bankAccount->id);
            }
        }
    }
}
