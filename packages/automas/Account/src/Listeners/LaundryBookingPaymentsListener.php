<?php

namespace Automas\Account\Listeners;

use Automas\Account\Models\BankAccount;
use Automas\Account\Services\BankTransactionsService;
use Automas\Account\Services\JournalService;
use Automas\LaundryManagement\Events\LaundryBookingPayments;

class LaundryBookingPaymentsListener
{
    protected $journalService;
    protected $bankTransactionsService;

    public function __construct(JournalService $journalService, BankTransactionsService $bankTransactionsService)
    {
        $this->journalService = $journalService;
        $this->bankTransactionsService = $bankTransactionsService;
    }

    public function handle(LaundryBookingPayments $event)
    {
        if (Module_is_active('Account', $event->booking->created_by)) {
            $bankAccount = BankAccount::where('payment_gateway', $event->booking->payment_method)->where('created_by', $event->booking->created_by)->first();
            if ($bankAccount) {
                $this->bankTransactionsService->laundryPaymentStatus($event->booking, $bankAccount->id);
                $this->journalService->laundryPaymentStatusJournal($event->booking, $bankAccount->id);
            }
        }
    }
}
